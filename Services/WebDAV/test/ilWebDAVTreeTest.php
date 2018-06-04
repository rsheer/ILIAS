<?php
use\PHPUnit\Framework\TestCase;
use\Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

include_once 'Services/WebDAV/classes/db/class.ilWebDAVDBManager.php';

/**
 * Class ilWebDAVDBManagerTest
 *
 * @author faheer
 *
 *
 */
class ilWebDAVDBManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        

    }
    
    public function testReactionForValidRefBasedWebDAVPath()
    {
        
    }
    
    public function testReactionForValidRootBasedWebDAVPath()
    {
        
    }
    
}