<?php
use Sabre\DAV\Locks;
use Sabre\DAV\Exception;
require_once 'libs/composer/vendor/autoload.php';

require_once 'Services/WebDAV/classes/lock/class.ilWebDAVLockObject.php';
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
    /** @var $db ilDB */
    protected $db;
    
    
    public function __construct()
    {
        global $DIC;
        
        $db;
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
        global $DIC;
        
        $sabre_locks = array();
        
        // Get locks on given uri
        $ref_id = ilWebDAVTree::getRefIdForWebDAVPath($uri);
        $obj_id = ilObject::_lookupObjectId($ref_id);
        $locks_on_obj = ilWebDAVLockObject::where(array('obj_id' => $obj_id));
        foreach($locks_on_obj->get() as $ilias_lock)
        {
            $sabre_locks[] = $ilias_lock->getAsSabreDavLock($uri);
        }
        
        // Get locks on childs
        if($returnChildLocks)
        {
            foreach($DIC->repositoryTree()->getChilds($ref_id) as $child_ref)
            {
                $child_obj_id = ilObject::_lookupObjectId($child_ref);
                $child_obj_locks = ilWebDAVLockObject::where(array('obj_id' => $child_obj_id));
                foreach($child_obj_locks as $child_ilias_lock)
                {
                    $sabre_locks[] = $child_ilias_lock->getAsSabreDavLock($uri . '/' . ilObject::_lookupTitle($child_obj_id));
                }
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
        global $DIC;
        
        ilLoggerFactory::getLogger('WebDAV')->debug(get_class($this). " -> try to unlock('$uri')");
        $ilias_lock = new ilWebDAVLockObject($lockInfo->token);
        if($ilias_lock != null && $ilias_lock->getOwner() == $DIC->user()->getId())
        {
            $ilias_lock->delete();
            ilLoggerFactory::getLogger('WebDAV')->debug(get_class($this). " -> unlock succeeded!('$uri')");
        }
        else 
        {
            ilLoggerFactory::getLogger('WebDAV')->warning(get_class($this). " -> unlock failed, unmatching users (uri: '$uri', lock_owner: $ilias_lock->getOwner(), user: )");
            throw new Forbidden();
        }
    }

    /**
     * {@inheritDoc}
     * @see \Sabre\DAV\Locks\Backend\BackendInterface::lock()
     */
    public function lock($uri, Sabre\DAV\Locks\LockInfo $lock_info)
    {
        ilLoggerFactory::getLogger('WebDAV')->debug(get_class($this). " -> try to lock('$uri')");
        // TODO: Permission check on the outside of this function
        $ilias_lock = new ilWebDAVLockObject();
        $ilias_lock->initFromLockInfo($lock_info);
        $ilias_lock->create();
        ilLoggerFactory::getLogger('WebDAV')->debug(get_class($this). " -> lock succeeded!('$uri')");
    }

    
    /**
     * For the moment just mocking, so ILIAS won't break. I will implement this later
     *
     * TODO: Implement this function
     *
     * @param int $obj_id
     * @return array
     */
    public function getLocksOnObjectObj(int $obj_id)
    {
        $locks_on_obj = ilWebDAVLockObject::where(array('obj_id' => $obj_id));
        return $locks_on_obj->get();
    }
}