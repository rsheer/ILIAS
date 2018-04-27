<?php
use Sabre\DAV\Locks;
use Sabre\DAV\Exception\NotImplemented;
require_once 'libs/composer/vendor/autoload.php';

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
    
    public function getLocks($uri, $returnChildLocks)
    {
        throw new NotImplemented();
        $obj_id = $this->getObjIdForPath();
        return array();
    }

    public function unlock($uri, Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        throw new NotImplemented();
    }

    public function lock($uri, Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        file_put_contents('lock_info.txt', print_r($lockInfo, TRUE));
        
        throw new NotImplemented();
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
        return array();
    }
    
    public function converIliasLockToSabreDavLock(array $a_ilias_lock)
    {
        $sabre_lock = array(
            'owner' => $a_ilias_lock['dav_owner'],
            'timeout' => $a_ilias_lock['expires'],
            'scope' => $a_ilias_lock['scope'],
            'depth' => $a_ilias_lock['depth'],
            'uri' => $this->getNodePathForNodeId($a_ilias_lock['node_id']),
            'created' => $a_ilias_lock['created'],
            'token' => $a_ilias_lock['token']
        );
        
        return $sabre_lock;
    }
    
    public function converLockInfoToIliassLock(Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        $node_id = $this->getObjIdForPath($lockInfo->uri);
        $obj_id = ilObject::_lookupObjectId($node_id);
        
        $ilias_lock = array(
            'ilias_owner',
            'dav_owner',
            'expires',
            'scope',
            'depth',
            'node_id',
            'obj_id',
            'token' => $lockInfo->token,
            'type' => 'w'
        );
    }
}