<?php
/**
 * Amazon EC2 Datasource
 *
 * @package       aws_source
 */

App::import('Vendor', 'aws-sdk-for-php', array('file'=>'sdk.class.php'));
App::import('Vendor', 'aws-sdk-for-php', array('file'=>'services/ec2.class.php'));

/**
 * Amazon EC2 Datasource Class
 *
 * @package aws_source
 * @author Kaz Watanabe
 **/
class Ec2Source extends DataSource
{
/**
 * Description of datasource
 *
 * @var string
 * @access public
 */
	var $description = "Amazon EC2 Data Source";

  private $ec2 = null;
  
  private $response_maps = array(
    'regions' => array(
      'result' => 'regionInfo.item',
    ),
    'security_groups' => array(
      'result' => 'securityGroupInfo.item',
    ),
    'availability_zones' => array(
      'result' => 'availabilityZoneInfo.item',
    ),
    'images' => array(
      'result' => 'imagesSet.item',
    ),
    'addresses' => array(
      'result' => 'addressesSet.item',
    ),
    'instances' => array(
      'result' => 'reservationSet.item',
    ),
    'key_pairs' => array(
      'result' => 'keySet.item',
    ),
  );
  
  private $query_maps = array(
    'start_instances' => array(
      'result' => 'instancesSet.item',
    ),
    'stop_instances' => array(
      'result' => 'instancesSet.item',
    ),
    'run_instances' => array(
      'result' => 'instancesSet.item',
    ),
    'terminate_instances' => array(
      'result' => 'instancesSet.item',
    ),
    'associate_address' => array(
      'result' => 'return',
    ),
    'deassociate_address' => array(
      'result' => 'return',
    ),
  );
  
	public function __construct($config)
	{
		parent::__construct($config);

    if ( !isset($config['key']) || !isset($config['secret_key']) ) {
			throw new Exception('No account key or secret key.');
    }

    $this->ec2 = new AmazonEC2($config['key'], $config['secret_key']);
	}

	public function listSources($data = null) 
	{
    return array_keys($this->response_maps);
	}
	
	public function describe($model) 
	{
		$fields = parent::describe($model);
		$table = $this->fullTableName($model, false);
		if ($fields === null) {
		  $fields = array();
    }
    
    return $fields ;
	}
	
	public function fullTableName($model, $quote = true)
	{
		return $model->table;
	}
	
	public function read(&$model, $queryData = array(), $recursive = null) 
	{
	  $save = error_reporting(0);
	  $api_result = $this->map_read_request($model, $queryData, $recursive);
	  error_reporting($save);
	  if ( !is_object($api_result) || !isset($api_result->status) ) {
      return false ;
	  }
	  
	  if ( !$api_result->isOK() ) {
	    return false ;
	  }
	  
	  if ( !isset($this->response_maps[$model->table]) ) {
	    return false ;
	  }

    if ($model->findQueryType === 'count') {
      $response_base = $api_result->body->{$this->response_maps[$model->table]['result']};
      return array(array(array('count' => count($response_base->item)))) ;
    } else {
	    $body =& $this->simpleXMLObjectToArray($api_result->body) ;
	    return $this->createResult(&$model, $this->response_maps[$model->table], $body);
    }
	}
		
  public function query()
  {
		$this->error = false;
		$args = func_get_args();    
		if (count($args) >= 2) {
			$method = $args[0];
			$params = Set::extract($args[1],'0.params');
			$region = Set::extract($args[1],'0.region');
			$model =& $args[2];
		} else {
			return false;
		}

    if ( $method === 'select_region' ) {
	    $this->ec2->set_region($params[0]);
      return true;
    }
    
    if ( !empty($region) ) {
	    $this->ec2->set_region($region);
    }
    
    if ( !method_exists($this->ec2, $method) ) {
			return false;
    }

    $map = Set::extract($this->query_maps, $method);
    if ( empty($map) ) {
      return false ;
    }
	  $save = error_reporting(0);
    $api_result = call_user_func_array(array($this->ec2, $method), $params);    
	  error_reporting($save);
	  if ( !is_object($api_result) || !isset($api_result->status) ) {
      return false ;
	  }
	  
	  if ( !$api_result->isOK() ) {
	    return $this->simpleXMLObjectToArray($api_result->body) ;
	  }
    
	  $body =& $this->simpleXMLObjectToArray($api_result->body) ;
	  return array_shift($this->createResult(&$model, $map, $body));
	}	
		
	public function calculate(&$model, $func, $params = array()) 
	{    
		return array('count' => true);
	}
	
	public function getLog()
	{
	  return array('count'=>0, 'time'=>null, 'log'=>array());
	}
	
	private function map_read_request(&$model, $queryData = array(), $recursive = null)
	{
	  $method = "describe_{$model->table}";
	  if ( !method_exists($this->ec2, $method) ) {
	    return false;
	  }
	  
	  if (isset($queryData['conditions']['region'])) {
	    $this->ec2->set_region($queryData['conditions']['region']);
	    unset($queryData['conditions']['region']) ;
	  }
	  
	  return $this->ec2->{$method}($queryData['conditions']);
	}
	
	private function createResult(&$model, $map, &$body)
	{
	  $base = Set::extract($body,$map['result']);
	  if ( !is_array($base) ) {
	    return array($base);
    } else {
  	  $result = array();
      if ( isset($base[0]) ) {
    	  foreach( $base as $key => $value ) {
          $result[] = array($model->alias => $value);
    	  }
      } else {
        $result[] = array($model->alias => $base);
      }
    }
	  return $result;
	}
	
	private function simpleXMLObjectToArray(&$obj)
	{
	  if ( is_a($obj, 'CFSimpleXML') || is_array($obj) ) {
	    $obj = (array)$obj;
	    foreach( $obj as $key => &$value ) {
	      $this->simpleXMLObjectToArray($value) ;
	    }
	  }
	  
	  return $obj;
	}
	
} // END class Ec2Source extends DataSource