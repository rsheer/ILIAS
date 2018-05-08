<?php

use function Sabre\HTTP\decodePath;

require_once 'libs/composer/vendor/autoload.php';

// Include all needed classes for a webdav-request
include_once "Services/WebDAV/classes/auth/class.ilWebDAVAuthentication.php";
include_once "Services/WebDAV/classes/dav/class.ilObjectDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjContainerDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjFileDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjCategoryDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjCourseDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjGroupDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjFolderDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilMountPointDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilClientNodeDAV.php";
include_once "Services/WebDAV/classes/dav/class.ilObjRepositoryRootDAV.php";


class ilWebDAVRequestHandler
{
    private static $instance;
    
    public static function getInstance()
    {
        return self::$instance ? self::$instance : self::$instance = new ilWebDAVRequestHandler();
    }

    public function handleRequest()
    {
        global $DIC;
        try
        {
            ilLoggerFactory::getLogger('WebDAV')->debug('New WebDAV Session with user: ' . $DIC->user()->getLogin());
            $root_dir = $this->getRootDir();
            
            $server = new Sabre\DAV\Server($root_dir);
            
            $this->setPlugins($server);
            
            ilLoggerFactory::getLogger('WebDAV')->debug('Ready to execute server');
            $server->exec();
        }
        // TODO: Remove after developement and debugging
        catch(Exception $e)
        {
            file_put_contents('WebDAV_Exception.txt',  'Message: '.$e->getMessage()."\n", FILE_APPEND);
            file_put_contents('WebDAV_Exception.txt',  'File: '.$e->getFile()."\n", FILE_APPEND);
            file_put_contents('WebDAV_Exception.txt',  'Line: '.$e->getLine()."\n", FILE_APPEND);
            file_put_contents('WebDAV_Exception.txt',  'Code: '.$e->getCode()."\n\n", FILE_APPEND);
        }
    }
    
    protected function getRootDir()
    {
        return new ilMountPointDAV();
    }
    
    protected function setPlugins($server)
    {
        global $DIC;
        
         // Set browser plugin (used for testing)
         $server->addPlugin(new Sabre\DAV\Browser\Plugin());

         // Set authentication plugin
         /*$webdav_auth = new ilWebDAVAuthentication();
         $cal = new Sabre\DAV\Auth\Backend\BasicCallBack(array($webdav_auth, 'authenticate'));
         $plugin = new Sabre\DAV\Auth\Plugin($cal);
         $server->addPlugin($plugin);*/
         
         // TODO: Implement lock plugin. Code would look like this:
         $lock_backend = new ilWebDAVLockBackend();
         $lock_plugin = new Sabre\DAV\Locks\Plugin($lock_backend);
         $server->addPlugin($lock_plugin);
    }
}

class access_mocking
{
    public function checkAccess($acces, $cmd, $ref)
    {
        return true;
    }
}