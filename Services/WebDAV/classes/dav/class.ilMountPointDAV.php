<?php

use Sabre\CalDAV\Notifications\ICollection;
use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\BadRequest;
use Sabre\DAV\Exception\Forbidden;

class ilMountPointDAV implements Sabre\DAV\ICollection
{
    /** @var $access ilAccessHandler */
    protected $access;
    
    /** @var $client_id string */
    protected $client_id;

    
    public function __construct()
    {
        global $DIC;
        
        //ilLoggerFactory::getLogger('WebDAV')->debug("MountPointDAV -> constructor");
        
        $this->access = $DIC->access();
        $this->client_id = $DIC['ilias']->getClientId();
        $this->user = $DIC->user();
        $this->username = $DIC->user()->getFullname();
        //ilLoggerFactory::getLogger('WebDAV')->debug("MountPointDAV -> constructor -> current user: '$this->username'");
        
    }
    
    public function getName()
    {
        return 'MountPoint';
    }
    
    public function getChildren()
    {
        // TODO: Check for permissions
        //ilLoggerFactory::getLogger('WebDAV')->debug("MountPointDAV -> getchildren -> return ClientNodeDAV for client '$this->client_id'");
        if($this->user->getId() != ANONYMOUS_USER_ID)
        {
            return array(new ilClientNodeDAV($this->client_id));
        }
        else
        {
            throw new Forbidden('Only for logged in users');    
        }
    }

    public function getChild($name)
    {
        //ilLoggerFactory::getLogger('WebDAV')->debug("MountPointDAV -> get child '$name'");
        
        // TODO: Check for permissions AND correct client
        if($name == $this->client_id)
            return new ilClientNodeDAV($this->client_id);
        throw new NotFound();
    }

    public function childExists($name)
    {
        //ilLoggerFactory::getLogger('WebDAV')->debug("MountPointDAV -> check if child '$name' exists");
        // TODO: Check for correct client
        if($name == $this->client_id)
            return true;
        return false;
    }

    public function getLastModified()
    {
        strtotime('2000-01-01');
    }
    
    public function createDirectory($name)
    {
        throw new Forbidden("It is not possible to create a directory here");
    }

    public function createFile($name, $data = null)
    {
        throw new Forbidden("It is not possible to create a file here");
    }
    public function setName($name)
    {
        throw new Forbidden("It is not possible to change the name of the root");
    }

    public function delete()
    {
        throw new Forbidden("It is not possible to delete the root");
    }
}