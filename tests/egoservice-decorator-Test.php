<?php
namespace Mes\EGo;

include_once 'constants.php';
include_once AUTOLOAD_PATH;
include_once PLUGIN_DIR . 'egoservice.php';
include_once PLUGIN_DIR . 'egoservice-decorator.php';

include_once 'expect.php';


use phpmock\phpunit\PHPMock;


class EGoService_Decorator_Test extends \PHPUnit_Framework_TestCase
{
	use \Expect;
    use PHPMock;
    public function setUp()
    {
    	
    	$this->mock_ego_service = $this->gm('EGoService', ['get_suburb', 'calculate_cost']);
    	$this->obj = new EGoService_Decorator($this->mock_ego_service);

    	$this->mock_get_transient = $this->getFunctionMock(__NAMESPACE__, "get_transient");
        $this->mock_set_transient = $this->getFunctionMock(__NAMESPACE__, "set_transient");
        
    }

    public function test_get_suburb_cached()
    {
        $post_code = 4000;
        $suburb = 'brisbane';
        
    	$this->fe($this->mock_get_transient, 1, [$this->equalTo(EGoService_Decorator::TRANSIENT_KEY_SUBURBS)], [$post_code=>$suburb]);
        
        $ret = $this->obj->get_suburb($post_code);
      	$this->assertEquals($suburb, $ret);
    }

    public function test_get_suburb_not_cached()
    {
    	$post_code = 4000;
        $suburb = 'brisbane';
  
        $this->fe($this->mock_get_transient, 1, null, false);
        $this->fe($this->mock_set_transient, 1, [$this->equalTo(EGoService_Decorator::TRANSIENT_KEY_SUBURBS), $this->equalTo([$post_code=>$suburb]), $this->equalTo(EGoService_Decorator::TRANSIENT_CACHE_DURATION)]);
        
        $this->oe($this->mock_ego_service, 'get_suburb', 1, [$this->equalTo($post_code)], $suburb);

        $ret = $this->obj->get_suburb($post_code);
      	$this->assertEquals($suburb, $ret);
    	
    }
    public function test_calculate_cost()
	{
		$post_code = 4000;
		$cost  = 100.56;
		$this->oe($this->mock_ego_service, 'calculate_cost', 1, [$this->equalTo($post_code)], $cost);
    	$ret = $this->obj->calculate_cost($post_code);
      	$this->assertEquals($cost, $ret);
    }

}