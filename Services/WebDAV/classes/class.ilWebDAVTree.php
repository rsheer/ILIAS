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
        ilLoggerFactory::getLogger('webdav')->debug("Try to get ref_id for path $a_uri");
        
        $a_uri = strtolower(trim($a_uri, '/'));
        
        /* After this funciton, the array SHOULD look like this:
         * $splitted_path[0] = '<client_name>'
         * $splitted_path[1] = 'ref_<ref_id>' or <ilias_root_name>
         * $splitted_path[2] = '<rest>/<of>/<the>/<path>
         */
        $splitted_path = explode('/', $a_uri, 3);
        ilLoggerFactory::getLogger('webdav')->debug('Path splitted into: client('.$splitted_path[0].'), repositorymountpoint('.$splitted_path[1].'), path('.$splitted_path[2].')');
        
        // Early exist for bad request
        if(count($splitted_path) < 2)
        {
            ilLoggerFactory::getLogger('webdav')->debug("Bad request -> no path and repositorymountpoint given");
            throw new BadRequest();
        }
        
        $repository_mountpoint = $splitted_path[1];
        $path_in_mountpoint = $splitted_path[2];
        ilLoggerFactory::getLogger('webdav')->debug("Repositorymountpoint = $repository_mountpoint, path = $path_in_mountpoint");
        
        // Since we already know our client, we only have to check the requested root for our path
        // if second string = 'ilias', the request was for the ilias root
        if($repository_mountpoint == 'ilias')
        {
            if($path_in_mountpoint != '')
            {
                $ref_path = self::getRefIdForGivenRootAndPath(ROOT_FOLDER_ID, $path_in_mountpoint);
                $searched_node = $ref_path[count($ref_path)-1];
                $ref_id = $searched_node['child'];
            }
            else
            {
                $ref_id = ROOT_FOLDER_ID;
            }
        }
        // if the first 4 letters are 'ref_', we are searching for a ref ID in the tree
        else if(substr($splitted_path[1], 0, 4) == 'ref_')
        {
            // Make a 'ref_1234' to a '1234'
            // Since we already tested for 'ref_', we can be sure there is at least one '_' character
            $start_node = (int)explode('_',$repository_mountpoint)[1];
            if($path_in_mountpoint != '' && $start_node > 0)
            {
                $ref_id = self::getRefIdForGivenRootAndPath($start_node, $path_in_mountpoint);
                ilLoggerFactory::getLogger('webdav')->debug("Found node: $ref_id");
            }
            else if($path_in_mountpoint == '')
            {
                $ref_id = $start_node;
            }
            else
            {
                ilLoggerFactory::getLogger('webdav')->debug("Not found -> did not found ref_id for path");
                throw new NotFound();
            }
        }
        // if there was no 'ilias' and no 'ref_' in the second string, this was a bad request...
        else
        {
            ilLoggerFactory::getLogger('webdav')->debug("Bad request -> No 'ilias' nor ref_id as mountpoint given");
            throw new BadRequest();
        }
        
        ilLoggerFactory::getLogger('webdav')->debug("Return ref_id: $ref_id");
        
        return $ref_id;
    }
    
    public static function getRefIdForGivenRootAndPath(int $start_ref, string $path_from_startnode)
    {
        return self::iterateRecursiveThroughTree(explode('/',$path_from_startnode), 0, $start_ref);
    }
    
    protected static function iterateRecursiveThroughTree($path_title_array, $current_path_element, $parent_ref_id)
    {
        global $DIC;
        
        if($path_title_array[$current_path_element] == '' || count($path_title_array) == $current_path_element)
        {
            return $parent_ref_id;
        }
        
        ilLoggerFactory::getLogger('webdav')->debug("getChildFrom $parent_ref_id");
        foreach($DIC->repositoryTree()->getChildIds($parent_ref_id) as $child_ref)
        {
            $child_obj_id = ilObject::_lookupObjectId($child_ref);
            $child_title = strtolower(ilObject::_lookupTitle($child_obj_id));
            ilLoggerFactory::getLogger('webdav')->debug("have child $child_ref($child_title) compared to " . $path_title_array[$current_path_element]);
            if($path_title_array[$current_path_element] == $child_title)
            {
                if(count($path_title_array)-1 == $current_path_element)
                {
                    return $child_ref;
                }
                else 
                {
                    return self::iterateRecursiveThroughTree($path_title_array, $current_path_element+1, $child_ref);
                }
            }
        }
        
        return -1;
    }
    
}