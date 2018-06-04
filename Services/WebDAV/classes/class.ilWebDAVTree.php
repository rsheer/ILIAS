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
        
        // Since we already know our client, we only have to check the requested root for our path
        // if second string = 'ilias', the request was for the ilias root
        if($splitted_path[1] == 'ilias')
        {
            self::
            $ref_id = self::getRefIdForGivenRootAndPath(0, $splitted_path[2]);
        }
        // if the first 4 letters are 'ref_', we are searching for a ref ID in the tree
        else if(substr($splitted_path[1], 0, 4) == 'ref_') 
        {
            // Make a 'ref_1234' to a '1234'
            // Since we already tested for 'ref_', we can be sure there is at least 1 '_' character
            $start_node = (int)explode('_',$splitted_path[1])[1];
            if(count($splitted_path) > 2 && $start_node > 0)
            {
                $ref_id = self::getRefIdForGivenRootAndPath($start_node, $splitted_path[2]);
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
        // if there was no 'ilias' and no 'ref_' in the second string, this was a bad request...
        else
        {
            throw new BadRequest();
        }
        
        return $ref_id;
    }
    
    public static function getRefIdForGivenRootAndPath(int $start_node, string $path_from_startnode)
    {
        global $DIC;
        return $DIC->repositoryTree()->getNodePathForTitlePath($path_from_startnode, $start_node);
    }
}