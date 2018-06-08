<?php
use Sabre\DAV\Locks;
use Sabre\DAV\Exception;
require_once 'libs/composer/vendor/autoload.php';

require_once 'Services/WebDAV/classes/lock/class.ilWebDAVLockObject.php';
require_once 'Services/WebDAV/classes/db/class.ilWebDAVDBManager.php';
require_once 'Services/WebDAV/classes/class.ilWebDAVTree.php';

/**
 * TODO: Implement this class
 *
 * Definition of ilias lock
 *
 *
 *
 * @author faheer
 */
class ilWebDAVLockBackend extends Sabre\DAV\Locks\Backend\AbstractBackend
{
    /** @var $db_manager ilWebDAVDBManager 
     *  @var $user ilObjUser
     *  @var $access ilAccessHandler
     */
    protected $db_manager;
    protected $user;
    protected $access;
    
    public function __construct($db_manager = null, $user = null, $access = null, $tree = null)
    {
        global $DIC;
        
        $this->db_manager = $db_manager != null ? $db_manager : new ilWebDAVDBManager($DIC->database());
        $this->user = $user != null ? $user : $DIC->user();
        $this->access = $access != null ? $access : $DIC->access();
        $this->tree = $tree != null ? $tree : $DIC->repositoryTree();
    }
    
    /**
     * This function returns all locks and child locks as SabreDAV lock objects
     * It is needed for sabreDAV to see if there are any locks 
     * 
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::getLocks()
     */
    public function getLocks($uri, $returnChildLocks)
    {
        ilLoggerFactory::getLogger('webdav')->debug("Try to get locks with children = $returnChildLocks on uri = $uri");
        
        $sabre_locks = array();
        
        // Get locks on given uri
        $ref_id = ilWebDAVTree::getRefIdForWebDAVPath($uri);
        $obj_id = ilObject::_lookupObjectId($ref_id);
        $lock_on_obj = $this->getLocksOnObjectId($obj_id);
        ilLoggerFactory::getLogger('webdav')->debug("Uri $uri has " . count($lock_on_obj) . " locks");
        if($lock_on_obj != false)
        {
            ilLoggerFactory::getLogger('webdav')->debug("Found lock {$lock_on_obj->getToken()} on uri uri");
            $sabre_locks[] = $lock_on_obj->getAsSabreDavLock($uri);
        }
        
        // Get locks on childs
        if($returnChildLocks)
        {
            $sabre_locks = $this->getLocksRecursive($sabre_locks, $ref_id, $uri);
        }
        
        ilLoggerFactory::getLogger('WebDAV')->debug("Return a list with " . count($sabre_locks) . " locks objects");
        
        return $sabre_locks;
    }
    
    /**
     * Iterates recursive through the ilias tree to search for locked objects
     * @param array $sabre_locks
     * @param integer $ref_id
     * @param string $uri
     * @return array
     */
    protected function getLocksRecursive($sabre_locks, $ref_id, $uri)
    {
        ilLoggerFactory::getLogger('webdav')->debug("Entered recursive lock function with '".count($sabre_locks)."' locks ref=$ref_id, uri=$uri");
        foreach($this->tree->getChilds($ref_id) as $child_ref)
        {
            ilLoggerFactory::getLogger('webdav')->debug("Searching for locks on $child_ref");
            
            // Only get locks of DAVable objects. Because not DAVable objects won't be lockable anyway
            $child_obj_id = ilObject::_lookupObjectId($child_ref);
            if(ilObjectDAV::_isDAVableObject($child_obj_id, false))
            {
                // Get Locks of this object
                $title = ilObject::_lookupTitle($child_obj_id);
                $child_ilias_lock = $this->getLocksOnObjectId($child_obj_id);
                if($child_ilias_lock != false)
                {
                    ilLoggerFactory::getLogger('webdav')->debug("Found lock {$child_ilias_lock->getToken()} on uri " . $uri . '/' . $title);
                    $sabre_locks[] = $child_ilias_lock->getAsSabreDavLock($uri . '/' . $title);
                }
                
                // Get locks of child objects
                $sabre_locks = $this->getLocksRecursive($sabre_locks, $child_ref, $uri . $title . '/');
            }
            
        }
        
        return $sabre_locks;
    }

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::unlock()
     */
    public function unlock($uri, Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        ilLoggerFactory::getLogger('WebDAV')->warning(get_class($this). " -> try to unlock('$uri')");
        $ilias_lock = $this->db_manager->getLockObjectWithTokenFromDB($lockInfo->token);
        if($ilias_lock && $ilias_lock->getIliasOwner() == $this->user->getId())
        {
            $this->db_manager->removeLockWithTokenFromDB($lockInfo->token);
            ilLoggerFactory::getLogger('WebDAV')->debug(get_class($this). " -> unlock succeeded!('$uri')");
        }
        else 
        {
            ilLoggerFactory::getLogger('WebDAV')->debug(get_class($this). " -> unlock failed, unmatching users (uri: '$uri', lock_owner: $ilias_lock->getOwner(), user: )");
            throw new Forbidden();
        }
    }

    /**
     * Function for the sabreDAV interface
     * 
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::lock()
     */
    public function lock($uri, Sabre\DAV\Locks\LockInfo $lock_info)
    {
        ilLoggerFactory::getLogger('WebDAV')->info(get_class($this). " -> try to lock('$uri')");
        
        $ref_id = ilWebDAVTree::getRefIdForWebDAVPath($uri);
        if($this->access->checkAccess('write', '', $ref_id))
        {
            $ilias_lock = ilWebDAVLockObject::createFromSabreLock($lock_info);
            ilLoggerFactory::getLogger('WebDAV')->info(get_class($this). " -> lock object created!");
            $this->db_manager->saveLockToDB($ilias_lock);
        }
        else
        {
            ilLoggerFactory::getLogger('WebDAV')->info("no permissions to lock $uri you are logged in as: " . $this->user->getLogin());
            throw new Forbidden();
        }
        
        ilLoggerFactory::getLogger('WebDAV')->info(get_class($this). " -> lock succeeded!('$uri')");
    }

    
    /**
     * Returns lock on given object
     *
     * @param int $obj_id
     * @return array
     */
    public function getLocksOnObjectId(int $obj_id)
    {
        ilLoggerFactory::getLogger('webdav')->info(get_class($this). " -> get lock on obj_id($obj_id)");
        return $this->db_manager->getLockObjectWithObjIdFromDB($obj_id); 
    }
}