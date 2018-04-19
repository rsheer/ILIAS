<?php

use ILIAS\UI\NotImplementedException;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\BadRequest;

require_once 'Modules/File/classes/class.ilObjFile.php';

class ilObjFileDAV extends ilObjectDAV implements Sabre\DAV\IFile
{
    /**
     * Application layer object.
     *
     * @var $obj ilObjFile
     */
    protected $obj;
    
    /**
     * ilObjFileDAV represents the WebDAV-Interface to an ILIAS-Object
     * 
     * So an ILIAS is needed in the constructor. Otherwise this object would
     * be useless.
     * 
     * @param ilObjFile $a_obj
     */
    public function __construct(ilObjFile $a_obj)
    {
        parent::__construct($a_obj);
    }
    
    /**
     * Replaces the contents of the file.
     *
     * The data argument is a readable stream resource.
     *
     * After a successful put operation, you may choose to return an ETag. The
     * etag must always be surrounded by double-quotes. These quotes must
     * appear in the actual string you're returning.
     *
     * Clients may use the ETag from a PUT request to later on make sure that
     * when they update the file, the contents haven't changed in the mean
     * time.
     *
     * If you don't plan to store the file byte-by-byte, and you return a
     * different object on a subsequent GET you are strongly recommended to not
     * return an ETag, and just return null.
     *
     * @param resource|string $data
     * @return string|null
     */
    function put($data)
    {
        $this->handleFileUpload($data);
        return $this->getETag();
    }
    
    /**
     * Returns the data
     *
     * This method may either return a string or a readable stream resource
     *
     * @return mixed
     */
    function get()
    {
        // TODO: Check permission
        if($this->access->checkAccess("read", "", $this->obj->getRefId()))
        {
            $file = $this->obj->getFile();
            return (file_exists($file)) ? fopen($file,'r') : null;
        }
        throw new Forbidden("Permission denied. No read access for this file");
    }
    
    /**
     * Returns the mime-type for a file
     *
     * If null is returned, we'll assume application/octet-stream
     *
     * @return string|null
     */
    function getContentType()
    {
        return  $this->obj->guessFileType();
    }
    
    /**
     * Returns the ETag for a file
     *
     * An ETag is a unique identifier representing the current version of the file. If the file changes, the ETag MUST change.
     *
     * Return null if the ETag can not effectively be determined.
     *
     * The ETag must be surrounded by double-quotes, so something like this
     * would make a valid ETag:
     *
     *   return '"someetag"';
     *
     * @return string|null
     */
    public function getETag()
    {
        // This is not a password hash. So I think md5 should do just fine :)
        return '"' . hash_file("md5", $this->obj->getFile(), false) . '"';
    }
    
    /**
     * Returns the size of the node, in bytes
     *
     * @return int
     */
    public function getSize()
    {
        return $this->obj->getFileSize();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see ilObjectDAV::delete()
     */
    /*public function delete()
    {
        if($this->access->checkAccess('delete', '', $this->obj->getRefId()))
        {
            $this->obj->delete();
        }
        else
        {
            throw new Forbidden('You are not allowed to delete this file!');
        }
    }
    
    /**
     * Handle uploaded file. Either it is a new file upload to a directory or it is an
     * upload to replace an existing file.
     * 
     * Given data can be a resource or data (given from the sabreDAV library)
     * 
     * @param string | resource $a_data
     * @param boolean $a_has_already_a_file
     */
    public function handleFileUpload($a_data, bool $a_has_already_a_file = FALSE)
    {
        global $DIC;
        
        if($this->access->checkAccess("write", "", $this->obj->getRefId()))
        {
            $file_dest_path = $this->obj->getFile();
            
            if($a_data != NULL)
            {
                if(is_resource($a_data))
                {
                    $tmp_path = $this->fileUploadToTmpWithStream($a_data);
                }
                else if(is_string($a_data))
                {
                    $tmp_path = $this->fileUploadToTmpWithString($a_data);
                }
                
                $file_size = sizeof($tmp_path);

                $vrs = ilUtil::virusHandling($tmp_path, '', true);
                
                // If vrs[0] == false -> virus found
                if($vrs[0] == false)
                {
                    throw new Forbidden('Virus found!');
                }
                
                // As long as we dont know how to move the uploaded file securely, we do it like this...
                // Becaus the commented function call below is deprecated.
                //ilUtil::moveUploadedFile($tmp_path, $this->obj->getFileName(), $this->obj->getDirectory());
                rename($tmp_path, $this->obj->getFile());
                
                include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
                $this->obj->setFileType(ilMimeTypeUtil::lookupMimeType($file_dest_path));
                $this->obj->setFileSize($file_size);
                $this->obj->update();
            }
            else
            {
                //throw new BadRequest('Invalid put data sent');                
            }
        }
        else 
        {
            throw new Forbidden('No write access for this file');
        }
    }
    
    /**
     * Write given data (as resource) to a temporary file in the tempdir.
     * 
     * @return string Path to temporary file. Please unlink by your self.
     */
    protected function fileUploadToTmpWithStream($a_stream)
    {
        $tmp_path = $this->createPathForTmpFile();
        
        $write_stream = fopen($tmp_path,'w');
        while (!feof($a_data)) {
            if (false === ($written = fwrite($write_stream, fread($a_data, 4096)))) {
                fclose($write_stream);
                throw new Forbidden('Forbidden to write file');
            }
        }
        fclose($write_stream);
        
        return $tmp_path;
    }
    
    /**
     * Write given data (as resource) to a temporary file in the tempdir.
     * 
     * @return string Path to temporary file. Please unlink by your self.
     */
    protected function fileUploadToTmpWithString($a_str_data)
    {
        $tmp_path = $this->createPathForTmpFile();
        
        $write_stream = fopen($tmp_path,'w');
        $written_length = fwrite($write_stream, $a_str_data);
        fclose($write_stream);
        
        if($written_length === false && strlen($a_str_data) > 0)
        {
            throw new Forbidden('Forbidden to write file');
        }
        return $tmp_path;
    }
    
    /**
     * Returns
     * @return string
     */
    protected function createPathForTmpFile()
    {
        return tempnam(sys_get_temp_dir(), '');
    }
}
