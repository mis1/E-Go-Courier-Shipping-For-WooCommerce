<?php
namespace Mes\EGo;

include_once 'constants.php';
include_once AUTOLOAD_PATH;
include_once PLUGIN_DIR . 'egoservice-impl.php';

include_once 'expect.php';


use phpmock\phpunit\PHPMock;

interface WP_Error{
    public function get_error_message();
    public function get_error_code();
    
}
class EGoService_Impl_Test extends \PHPUnit_Framework_TestCase
{
	use \Expect;
    use PHPMock;
    public function setUp()
    {
    	$this->obj = new EGoService_Impl();

    	$this->mock_wp_remote_get = $this->getFunctionMock(__NAMESPACE__, 'wp_remote_get');
        $this->mock_wp_remote_post = $this->getFunctionMock(__NAMESPACE__, 'wp_remote_post');
        $this->mock_wp_remote_retrieve_body = $this->getFunctionMock(__NAMESPACE__, 'wp_remote_retrieve_body');
        $this->mock_is_wp_error = $this->getFunctionMock(__NAMESPACE__, 'is_wp_error');
        $this->mock_wp_error = $this->gm('WP_Error', ['get_error_message', 'get_error_code']);  
        $this->post_code = 4000;
        $this->url = EGoService_Impl::SUBURB_API . '?postcode=' . urlencode($this->post_code);
    }
    function dp_suburb_api_responses(){
        $multiple_suburbs = <<<HERDOC
BRISBANE, 4000
BRISBANE ADELAIDE S, 4000
PETRIE TERRACE, 4000
SPRING HILL, 4000
HERDOC;
        return [
            [$multiple_suburbs],
            ['BRISBANE, 4000']
        ];
    }

    /**
     * @dataProvider dp_suburb_api_responses
     */
    public function test_get_suburb($response)
    {
        $suburb = 'brisbane';
    	$this->fe($this->mock_wp_remote_get, 1, $this->url, $response);
        $this->fe($this->mock_is_wp_error, 1, $response, false);     
        $this->fe($this->mock_wp_remote_retrieve_body, 1, $response, $response);     
          
        $ret = $this->obj->get_suburb($this->post_code);
      	$this->assertEquals($suburb, strtolower($ret));
    }

    public function test_get_suburb_request_failure()
    {
        $this->oe($this->mock_wp_error, 'get_error_message',1,null,'error message');
        $this->oe($this->mock_wp_error, 'get_error_code',1,null,123);
        $this->fe($this->mock_wp_remote_get, 1, null, $this->mock_wp_error);
        $this->fe($this->mock_is_wp_error, 1, null, true);     
        $this->fe($this->mock_wp_remote_retrieve_body, 0);     
        
        
        $ret = $this->obj->get_suburb($this->post_code);
        $this->assertFalse($ret);
        $this->assert_last_error(['error message, 123']);
    }

    function dp_suburb_api_invalid_responses(){
        return [
            [''],
            ['BRISBANE']
        ];
    }
    /**
     * @dataProvider dp_suburb_api_invalid_responses
     */
    public function test_get_suburb_invalid_response($response)
    {
        $this->fe($this->mock_is_wp_error, 1, null, false);     
        $this->fe($this->mock_wp_remote_retrieve_body, 1, null, $response);          
        $ret = $this->obj->get_suburb(null);
        $this->assertFalse($ret);
        if (!empty($response))
            $this->assert_last_error(['invalid response', $response]);
    }
    
    function dp_calculator_api_invalid_params(){
        return [
            [[], 'incomplete input'],
            [['height'=>1, 'width'=>1], 'incomplete input'],
            [['pickup'=>43, 'pickupsuburb'=>'a', 'deliverypostcode'=>23, 'deliverysuburb'=>32, 'height'=>324, 'width'=>32, 'depth'=>23, 'weight'=>32, 'type'=>43], 'incomplete input'],
            [['pickuppostcode'=>43, 'pickupsuburb'=>'a', 'deliverypostcode'=>23, 'deliverysuburb'=>32, 'height'=>324, 'width'=>32, 'depth'=>23, 'weight'=>[32,33], 'type'=>43, 'items'=>2], 'invalid input']
        ];
    }
    
    /**
     * @dataProvider dp_calculator_api_invalid_params
     */
    public function test_calculate_cost_invalid_params($params, $error_msg)
    {
        $this->fe($this->mock_wp_remote_post, 0);
        $ret = $this->obj->calculate_cost($params);
        $this->assertFalse($ret);
        $this->assert_last_error([$error_msg]);
    }
    function get_valid_parms(){
        return ['pickuppostcode'=>2000, 'pickupsuburb'=>'sydney city', 'deliverypostcode'=>4000, 'deliverysuburb'=>'brisbane', 'height'=>35, 'width'=>35, 'depth'=>65, 'weight'=>2, 'type'=>'Carton', 'items'=>1];
    }
    function dp_calculator_api_params(){
        $cost = 22.02;
        $response =<<<HEREDOC
error=OK
eta=Overnight
price=$cost
HEREDOC;
        return [
            [$this->get_valid_parms(), $response, $cost]
        ];
    }

    /**
     * @dataProvider dp_calculator_api_params
     */
    public function test_calculate_cost($params, $response, $cost)
    {
        $callback = $this->callback(
            function($args){
                return count($args) == 2 &&
                    FALSE !== strpos($args['body'], 'pickuppostcode=2000&pickupsuburb=sydney+city')  &&
                    $args['headers'] === ['Content-Type'=>'application/x-www-form-urlencoded'];
            });
        $this->fe($this->mock_wp_remote_post, 1, [EGoService_Impl::CALCULATOR_API,$callback], $response);
        $this->fe($this->mock_is_wp_error, 1, $response, false);
        $this->fe($this->mock_wp_remote_retrieve_body, 1, $response, $response);

        $ret = $this->obj->calculate_cost($params);
        $this->assertEquals($cost, $ret);
    }
    function dp_calculator_api_invalid_responses(){
        $response1 =<<<HEREDOC
error=OK
HEREDOC;
        $response2 =<<<HEREDOC
error=OK
eta=
price=45
HEREDOC;
        $response3 =<<<HEREDOC
error=OK
eta=dfsadsf
price=
HEREDOC;
        return [
            [''],
            [$response1],
            [$response2],
            [$response3]
        ];     
    }

    /**
     * @dataProvider dp_calculator_api_invalid_responses
     */
    public function test_calculate_cost_invalid_responses($response)
    {
        $this->fe($this->mock_is_wp_error, 1, null, false);
        $this->fe($this->mock_wp_remote_retrieve_body, 1, null, $response);

        $ret = $this->obj->calculate_cost($this->get_valid_parms());
        $this->assertFalse($ret);
        if (!empty($response))
            $this->assert_last_error(['invalid response', $response]);
    }
    private function assert_last_error($expected_messages){
        $last_error = $this->obj->get_last_error();
        foreach($expected_messages as $message)
            $this->assertContains($message, $last_error);
    }
}