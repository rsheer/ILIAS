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
    
    
    protected $db_mock;
    protected $db_manager;
    protected $fake_row;
    protected $query_result_dummy;
    protected $fake_token_id = '123456789';
    
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        
        $this->db_mock = Mockery::mock(ilDB::class);
        $this->db_manager = new ilWebDAVDBManager($this->db_mock);
        $this->fake_row = array('token' => $this->fake_token_id, 'expires' => time());
        $this->query_result_dummy = 'QueryResultDummy';
    }
    
    /**
     * @test
     */
    public function SelectOnValidTokenBringsLock()
    {
        // Given
        $result_dummy = 'ResultDummy';
        $this->fake_row['expires'] = time() + 3600; // <- still valid lock
        $this->db_mock->shouldReceive([
            'query' => $this->query_result_dummy,
            'quote' => $this->fake_token_id,
            'fetchAssoc' => $this->fake_row
        ]);
        
        // When
        $return_row = $this->db_manager->getLockObjectWithTokenFromDB($this->fake_token_id);
        
        // Then
        $this->assertTrue($return_row['token'] == $this->fake_row['token']);
    }
    
    /**
     * @test
     */
    public function SelectDoesNotBringExpiredLocks()
    {
        // Given
        $this->fake_row['expires'] = time() - 1;              // <- expired lock
        $this->db_mock->shouldReceive([
            'query' => $this->query_result_dummy,
            'quote' => $this->fake_token_id,
            'fetchAssoc' => $this->fake_row
        ]);
        
        
        // When
        $return_row = $this->db_manager->getLockObjectWithTokenFromDB($this->fake_token_id);
        
        // Then
        $this->assertEquals($return_row, false);
        
    }
    
}
