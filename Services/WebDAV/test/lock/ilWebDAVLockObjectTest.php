<?php
use\PHPUnit\Framework\TestCase;
use\Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;


class ilWebDAVLockObjectTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
    }
    
    public function testCreateInfoFromSabreLock()
    {

    }
    
}
