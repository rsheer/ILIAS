<?php


class ilWebDAVLockBackend extends Sabre\DAV\Locks\Backend\AbstractBackend
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

    public function unlock($uri, \Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        
    }

    public function lock($uri, \Sabre\DAV\Locks\LockInfo $lockInfo)
    {
        
    }

    
}