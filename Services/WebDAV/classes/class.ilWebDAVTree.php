<?php



use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Exception\BadRequest;

class ilWebDAVTree
{
    /**
     * Returns the ref_id of the given webdav path. Path starts without php-script
     * 
     * Examples
     * 
     *  Path starts at a ref: <client_name>/ref_<ref_id>/folder1/folder2
     *  Path starts at root:  <client_name>/ilias/foo_container1/course1/
     * @param string $uri
     */
    public static function getRefIdForWebDAVPath($a_uri)
    {
        global $DIC;

        $a_uri = strtolower(trim($a_uri, '/'));
        
        /* After this funciton, the array SHOULD look like this:
         * $splitted_path[0] = '<client_name>'
         * $splitted_path[1] = 'ref_<ref_id>' or <ilias_root_name>
         * $splitted_path[2] = '<rest>/<of>/<the>/<path>
         */
        $splitted_path = explode('/', $a_uri, 3);
        
        // Early exist for bad request
        if(count($splitted_path) < 2)
        {
            throw new BadRequest();
        }
        
        if($splitted_path[1] == 'ilias')
        {
            $ref_id = $DIC->repositoryTree()->getNodePathForTitlePath($splitted_path[2], 0);
        }
        else if(substr($splitted_path[1], 0, 4) == 'ref_') 
        {
            // Make a 'ref_1234' to a '1234'
            $start_node = (int)explode('_',$splitted_path[1])[1];
            if(count($splitted_path) > 2 && $start_node > 0)
            {
                $ref_id = $DIC->repositoryTree()->getNodePathForTitlePath($splitted_path[2], $start_node);
            }
            else if(count($splitted_path) == 2)
            {
                return $start_node;
            }
            else 
            {
                throw new NotFound();
            }
        }
        else
        {
            throw new NotFound();
        }
        
        return $ref_id;
    }
}