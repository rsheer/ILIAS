<?php

class ilWebDAVDBManager
{
    /**
     * @var ilDB
     */
    protected $db;
    
    private $lock_table = 'dav_lock';
    
    public function __construct($db)
    {
        $this->db = $db;       
    }
    
    public function checkIfLockExistsInDB($token)
    {
         $select_query = "SELECT count(*) AS cnt FROM $this->locks_table WHERE token = " . $this->db->quote($ilias_lock->getToken(), 'text');
         $select_result = $this->db->query($select_query);
         $row = $this->db->fetchAssoc($select_query);
         if(isset($row)){
             return true;
         }
         return false;
    }
    
    /**
     * Returns lock Object from given tocken
     * @param string $token
     * @return array|boolean
     */
    public function getLockObjectWithTokenFromDB($token, $return_as_assoc = false)
    {
        $query = "SELECT * FROM $this->lock_table WHERE token = " . $this->db->quote($token, 'text');
        
        $select_result = $this->db->query($query);
        $row = $this->db->fetchAssoc($select_result);
        
        if($row && $row['expires'] > time())
        {
            
            return $return_as_assoc ? $row : ilWebDAVLockObject::createFromAssocArray($row);
        }
        
        return false;
    }
    
    public function getLockObjectWithObjIdFromDB($obj_id, $only_valid = true)
    {
        $query = "SELECT * FROM $this->lock_table WHERE obj_id = " . $this->db->quote($obj_id, 'integer');
        $select_result = $this->db->query($query);
        $row = $this->db->fetchAssoc($select_result);
        
        if($row && (!only_valid || $row['expires'] > time()))
        {
            return ilWebDAVLockObject::createFromAssocArray($row);
        }
        
        return false;
    }
    
    public function saveLockToDB(ilWebDAVLockObject $ilias_lock)
    {
        $this->db->insert($this->lock_table, array(
            'token' => array('text', $ilias_lock->getToken()),
            'obj_id' => array('integer', $ilias_lock->getObjId()),
            'ilias_owner' => array('integer', $ilias_lock->getIliasOwner()), 
            'dav_owner' => array('text', $ilias_lock->getDavOwner()),
            'expires' => array('timestamp', $ilias_lock->getExpires()),
            'depth' => array('integer', $ilias_lock->getDepth()),
            'type' => array('text', $ilias_lock->getType()),
            'scope' => array('integer', $ilias_lock->getScope())
        ));
    }
    
    /**
     * Removes one specific lock 
     * 
     * @param integer $token
     * @return array with affected lock (if there was a lock)
     */
    public function removeLockWithTokenFromDB($token)
    {
        return $this->db->manipulate("DELETE FROM $this->lock_table WHERE id = ".$ilDB->quote($token, "integer"));
    }
    
    /**
     * Removes all locks from DB that are expired (expires < time())
     * 
     * @return array with all affected locks
     */
    public function purgeExpiredLocksFromDB()
    {
        return $this->db->manipulate("DELETE FROM $this->lock_table WHERE expires < " . $this->db->quote(time(), 'timestamp'));
    }
}