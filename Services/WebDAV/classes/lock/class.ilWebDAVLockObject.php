<?php
require_once('Services/ActiveRecord/Connector/class.arConnectorSession.php');

/**
 * 
 * @author faheer
 *
 */
class ilWebDAVLockObject extends ActiveRecord
{
    const TABLE_NAME = 'dav_lock';
    
    /**
     * @return string
     */
    static function returnDbTableName() {
        return self::TABLE_NAME;
    }
    
    /**
     * @var string
     *
     * @con_is_primary true
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     255
     */
    protected $token;
    
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected $obj_id;
    
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected $node_id;
    
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     */
    protected $ilias_owner;
    
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  string
     * @con_length     200
     */
    protected $dav_owner;
    
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     
     */
    protected $expires;
    
    /**
     * @var int
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected $depth;
    
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     1
     */
    protected $type;
    
    /**
     * @var string
     *
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     1
     */
    protected $scope;
    
    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->type;
    }
    
    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }
    
    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }
    
    /**
     * @param int $ilias_owner
     */
    public function setIliasOwner($ilias_owner)
    {
        $this->ilias_owner = $ilias_owner;
    }
    
    /**
     * @return int
     */
    public function getIliasOwner()
    {
        return $this->ilias_owner;
    }
    
    /**
     * @param string $dav_owner
     */
    public function setDavOwner($dav_owner)
    {
        $this->dav_owner = $dav_owner;
    }
    
    /**
     * @return string
     */
    public function getDavOwner()
    {
        return $this->dav_owner;
    }
    
    /**
     * @param int $expires
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }
    
    /**
     * @return int
     */
    public function getExpire()
    {
        return $this->expires;
    }
    
    /**
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $dept;
    }
    
    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }
    
    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    
    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }
    
    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }
    
    
    /**
     * Inits an ILIAS lock object from a sabreDAV lock object
     * 
     * IMPORTANT: This method just initializes the object. It does not
     * create any record in the database!
     * 
     * @param Sabre\DAV\Locks\LockInfo $lock_info
     */
    public function initFromLockInfo(Sabre\DAV\Locks\LockInfo $lock_info)
    {
        global $DIC;

        $ref_id = ilWebDAVTree::getRefIdForWebDAVPath($lock_info->uri);
        ilLoggerFactory::getLogger('WebDAV')->debug("Got ref $ref_id for path $lock_info->uri");
        // TODO: Check permission!
        //if($DIC->access()->checkAccess('write', '', $ref_id))
        if(true)
        {
            $this->token = $lock_info->token;
            $this->obj_id = 55;//ilObject::_lookupObjectId($ref_id);
            $this->ilias_owner = 1234;//$DIC->user()->getId();
            $this->dav_owner = $lock_info->owner;
            $this->expires = time() + 3600;
            $this->depth = $lock_info->depth;
            $this->type = 'w';
            $this->scope = $lock_info->scope;
        }
        else
        {
            throw new Sabre\DAV\Exception\Forbidden();
        }
    }
    
    public function getAsSabreDavLock($uri)
    {
        global $DIC;
        
        $sabre_lock = new Sabre\DAV\Locks\LockInfo();
        $sabre_lock->created;
        $sabre_lock->depth = $this->depth;
        $sabre_lock->owner = $this->dav_owner;
        $sabre_lock->scope = $this->scope;
        $sabre_lock->timeout = $this->expires - time();
        $sabre_lock->token = $this->token;
        $sabre_lock->uri = $uri;
    }
}