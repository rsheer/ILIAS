<?php

use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;

/**
 * This class represents the used ilias client. For example if your clients
 * name is "my_ilias" and you are currently in the directory with the ref_id=123,
 * the path would look like this: ilias.mysite.com/webdav.php/my_ilias/ref_123/
 * 
 * The call would look like this:
 * -> webdav.php <- creates the request handler and initialize ilias
 * -> ilWebDAVRequestHandler <- setup the webdav server
 * -> ilObjMountPointDAV <- This represents the "root" node and is needed for sabreDAV
 * -> ilMountPointDAV <- This class represents the used client (for example here it is my_ilias)
 * -> child of ilContainerDAV
 * 
 * @author faheer
 *
 */
class ilClientNodeDAV implements Sabre\DAV\ICollection
{
    /** @var $access ilAccessHandler */
    protected $access;
    
    protected $name_of_repository_root;
    
    /**
     * @param string $client_name
     */
    public function __construct(string $client_name)
    {
        global $DIC;
        
        ilLoggerFactory::getLogger('WebDAV')->debug("ClientNodeDAV -> constructor with client '$client_name'");
        
        // TODO: Add real access checker!
        $this->access = new access_mocking();//$DIC->access();
        $this->client_name = $client_name;
        $this->name_of_repository_root = 'ILIAS';
    }
    
    public function setName($name)
    {}

    public function getChildren()
    {
        ilLoggerFactory::getLogger('WebDAV')->debug("ClientNodeDAV -> get children -> return RepositoryRoot");
        return array($this->getRepositoryRootPoint());
    }

    public function getName()
    {
        return $this->client_name;
    }

    public function getLastModified()
    {
        return strtotime('2000-01-01');
    }

    public function getChild($name)
    {
        ilLoggerFactory::getLogger('WebDAV')->debug("ClientNodeDAV -> get child '$name'");
        if($name == $this->name_of_repository_root)
        {
            ilLoggerFactory::getLogger('WebDAV')->debug("ClientNodeDAV -> get child -> return RepositoryRoot");
            return $this->getRepositoryRootPoint();
        }
        else 
        {
            ilLoggerFactory::getLogger('WebDAV')->debug("ClientNodeDAV -> get child -> return MountPointByReference");
            return $this->getMountPointByReference($name);
        }
        
    }

    protected function getMountPointByReference($name)
    {
        $ref_id = $this->getRefIdFromName($name);
        
        if($ref_id > 0)
        {
            if($this->access->checkAccess('read', '', $ref_id))
            {
                return ilObjectDAV::_createDAVObjectForRefId($ref_id);
            }

            throw new Forbidden("No permission for object with reference ID $ref_id ");
        }
        
        throw new BadRequest("Invalid parameter $ref_parts");
    }
    
    protected function getRepositoryRootPoint()
    {
        // TODO: check for read access for repo-root
        return new ilObjRepositoryRootDAV($this->name_of_repository_root);
    }
    
    /**
     * Either the given name is the name of the repository root of ILIAS
     * or it is a reference to a node in the ILIAS-repo
     * 
     * Returns true if name=name of repository root or if given reference
     * exists and user has read permissions to this reference
     *  
     */
    public function childExists($name)
    {
        ilLoggerFactory::getLogger('WebDAV')->debug("ClientNodeDAV -> check if '$name' exists");
        if($name == $this->name_of_repository_root)
        {
            return true;
        }
        
        $ref_id = $this->getRefIdFromName($name);
        if($ref_id > 0)
        {
            return ilObject::_exists($ref_id, true);
        }
        return false;
    }

    /**
     * Gets ref_id from name. Name should look like this: ref_<ref_id>
     * 
     * @param string $name
     * 
     */
    protected function getRefIdFromName($name)
    {
        $ref_parts = explode('_', $name);
        if(count($ref_parts) == 2)
        {
            $ref_id = (int)$ref_parts[1];
            return $ref_id;
        }
        
        return 0;
    }
    
    protected function checkIfRefIdIsValid($ref_id)
    {
        if($ref_id > 0 && ilObject::_exists($ref_id, true) && ilObjectDAV::_isDAVableObject($ref_id, true))
        {
            return $ref_id;
        }
    }
    
    public function createDirectory($name)
    {
        throw new Forbidden();
    }

    public function delete()
    {
        throw new Forbidden();
    }

    public function createFile($name, $data = null)
    {
        throw new Forbidden();
    }
}