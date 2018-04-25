<?php

/**
 * 
 * @author faheer
 *
 */
class ilWebDAVLockObject extends ActiveRecord
{
    protected $token;
    protected $obj_id;
    protected $node_id;
    protected $ilias_owner;
    protected $dav_owner;
    protected $expires;
    protected $depth;
    protected $type;
    protected $scope;
}