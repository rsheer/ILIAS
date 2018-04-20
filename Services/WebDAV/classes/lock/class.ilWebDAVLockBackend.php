<?php
require_once 'libs/composer/vendor/autoload.php';

/**
 * TODO: Implement this class
 *
 * @author faheer
 */
class ilWebDAVLockBackend extends sabre\DAV\Locks\Backend\AbstractBackend
{
    /** @var $db ilDB */
    protected $db;
    
    
    public function locks()
    {
        global $DIC;
        
        $db;
    }
    public function getLocks($uri, $returnChildLocks)
    {
        
    }

    public function unlock($uri, sabre\DAV\Locks\LockInfo $lockInfo)
    {
        
    }

    public function lock($uri, sabre\DAV\Locks\LockInfo $lockInfo)
    {
        
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
}