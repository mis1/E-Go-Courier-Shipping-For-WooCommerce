<?php
namespace Mes\EGo;

include_once 'constants.php';
include_once AUTOLOAD_PATH;
include_once PLUGIN_DIR . 'egoservice.php';
include_once PLUGIN_DIR . 'ego-calculator.php';
include_once PLUGIN_DIR . 'item.php';

include_once 'expect.php';


use phpmock\phpunit\PHPMock;


class EGo_Calculator_Test extends \PHPUnit_Framework_TestCase
{
	use \Expect;
    use PHPMock;
    public function setUp()
    {
    	
    	$this->mock_ego_service = $this->gm('EGoService', ['get_suburb', 'calculate_cost']);
    	$this->obj = new EGo_Calulator($this->mock_ego_service);
    }

    public function test_calculate_cost()
    {
        $pickuppostcode = 2000;
        $pickupsuburb = 'sydney city';
        $deliverypostcode = 4000;
        $deliverysuburb = 'brisbane';
        $cost = 22.02;        
    	$boxes = [new Box(40,35,65,2)];

        $this->oe($this->mock_ego_service, 'get_suburb', 1, $deliverypostcode, 'brisbane');
        $callback = $this->callback(
            function($params) use ($pickuppostcode, $pickupsuburb, $deliverypostcode, $deliverysuburb, $boxes){
                return $params['pickuppostcode'] === $pickuppostcode &&
                $params['pickupsuburb'] === $pickupsuburb &&
                $params['deliverypostcode'] === $deliverypostcode &&
                $params['deliverysuburb'] === $deliverysuburb;

                        ;
            });
        $this->oe($this->mock_ego_service, 'calculate_cost', 1, $callback, $cost);

        $ret = $this->obj->calculate_cost($boxes, $pickuppostcode, $pickupsuburb, $deliverypostcode);

      	$this->assertEquals($cost, $ret);
    }
}