<?php
/**
 * Amazon S3 Datasource
 *
 * @package       aws_source
 */

App::import('Vendor', 'aws-sdk-for-php', array('file'=>'sdk.class.php'));
App::import('Vendor', 'aws-sdk-for-php', array('file'=>'services/ec2.class.php'));

/**
 * Amazon S3 Datasource Class
 *
 * @package aws_source
 * @author Kaz Watanabe
 **/
class S3Source extends DataSource
{
/**
 * Description of datasource
 *
 * @var string
 * @access public
 */
	var $description = "Amazon S3 Data Source";

  private $s3 = null;
  private $response_maps = array(
    'buckets' => array(
      'result' => 'Buckets',
    ),
    'objects' => array(
      'result' => 'Contents',
    ),
  );
  
  private $_fields = array(
    'objects' => array(
      'id' => array(
        'type' => 'string',
        'null' => false,
        'default' => null,
        'length' => 512,
        'key' => 'primary',
      ),
      'bucket'=> array(
        'type' => 'string',
        'null' => false,
      ),
      'filename'=> array(
        'type' => 'string',
        'null' => false,
      ),
      'body'=> array(
        'type' => 'binary',
        'null' => false,
      ),
    ),
    'buckets' => array(
      'id' => array(
        'type' => 'string',
        'null' => false,
        'default' => null,
        'length' => 512,
        'key' => 'primary',
      ),
      'bucket'=> array(
        'type' => 'string',
        'null' => false,
      ),
      'region'=> array(
        'type' => 'string',
        'null' => false,
      ),
      'acl'=> array(
        'type' => 'string',
        'null' => false,
      ),
      'option'=> array(
        'type' => 'string',
        'null' => false,
      ),
    ),
  );
  
	public function __construct($config)
	{
		parent::__construct($config);

    if ( !isset($config['key']) || !isset($config['secret_key']) ) {
			throw new Exception('No account key or secret key.');
    }

    $this->s3 = new AmazonS3($config['key'], $config['secret_key']);
	}

	public function listSources($data = null) 
	{
    return array('buckets', 'objects');
	}
	
	public function describe($model) 
	{
		$fields = parent::describe($model);
		$table = $this->fullTableName($model, false);
		if ($fields === null) {
		  $fields = Set::extract($this->_fields, $model->table);
    }
    
    return $fields ;
	}
	
	public function fullTableName($model, $quote = true)
	{
		return $model->table;
	}
	
	public function read(&$model, $queryData = array(), $recursive = null) 
	{
    if ($model->findQueryType === 'count') {
      $api_result = $this->map_read_request($model, $queryData, $recursive);
      if ( is_array($api_result) ) {
        return array(array(array('count' => count($api_result)))) ;
      } else {
        return array(array(array('count' => 0))) ;
      }
    } else {
      $api_result = $this->map_read_request($model, $queryData, $recursive);
      if ( !is_object($api_result) || !isset($api_result->status) ) {
        return false ;
      }
      if ( !$api_result->isOK() ) {
        return false ;
      }
      if ( !isset($this->response_maps[$model->table]) ) {
        return false ;
      }

      $body =& $this->simpleXMLObjectToArray($api_result->body) ;
      return $this->createResult(&$model, $this->response_maps[$model->table], $body, $queryData);
    }
	}

	function create(&$model, $fields = null, $values = null)
	{
	  $data = array_combine($fields, $values) ;
	  $model->data = array();
	  
	  switch($model->table) {
	  case 'objects':
      $api_result = $this->s3->create_object(
        Set::extract($data,'bucket'),
        Set::extract($data,'filename'),
        array(
          'body' => Set::extract($data,'body'),
        )
      );

  	  if ( !$api_result->isOK() || $api_result->status != 200 ) {
	      return false;
  	  }
  	  
  	  $model->id = Set::extract($data,'filename');
  	  $model->data = array(
  	    $model->alias => array(
  	      'bucket' => Set::extract($data,'bucket'),
  	      'filename' => Set::extract($data,'filename'),
  	    ),
  	  );
      break ;
    case 'buckets':
      $api_result = $this->s3->create_bucket(
        Set::extract($data,'bucket'),
        Set::extract($data,'region'),
        Set::extract($data,'acl'),
        Set::extract($data,'option')
      );

  	  if ( !$api_result->isOK() || $api_result->status != 200 ) {
	      return false;
  	  }

  	  $model->id = Set::extract($data,'bucket');
  	  $model->data = array(
  	    $model->alias => array(
  	      'bucket' => Set::extract($data,'bucket'),
  	      'region' => Set::extract($data,'region'),
  	      'acl' => Set::extract($data,'acl'),
  	      'option' => Set::extract($data,'option'),
  	    ),
  	  );

      break ;
    }

    return $model->data;
	}
  
  public function delete(&$model, $id = null)
  {
    $result = false ;
    switch($model->table) {
    case 'objects':
      $api_result = $this->s3->delete_object(
        Set::extract($model->data,"{$model->alias}.bucket"),
        $model->id,
        Set::extract($model->data,"{$model->alias}.option")
      );

  	  if ( !$api_result->isOK() || $api_result->status != 204 ) {
	      return false;
  	  }
  	  
  	  $result = true ;
      break ;
    case 'buckets':
      $api_result = $this->s3->delete_bucket(
        $model->id,
        Set::extract($model->data,"{$model->alias}.force"),
        Set::extract($model->data,"{$model->alias}.option")
      );

  	  if ( !$api_result->isOK() || $api_result->status != 204 ) {
	      return false;
  	  }
  	  
  	  $result = true ;
      break ;
    }
    
    return $result ;
  }
	
  public function query()
  {
		$this->error = false;
		$args = func_get_args();    
		if (count($args) >= 2) {
			$method = $args[0];
			$params = $args[1];
			$model =& $args[2];
		} else {
			return false;
		}

    if ( $method === 'select_region' ) {
	    $this->s3->set_region($params[0]);
      return true;
    }
    
    if ( !empty($region) ) {
	    $this->s3->set_region($region);
    }
    
    if ( !method_exists($this->s3, $method) ) {
			return false;
    }

    $api_result = call_user_func_array(array($this->s3, $method), $params);    
	  if ( !is_object($api_result) || !isset($api_result->status) ) {
      return false ;
	  }

	  if ( !$api_result->isOK() ) {
	    return $this->simpleXMLObjectToArray($api_result->body) ;
	  }
    
    return (string)$api_result->body;
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
	  $method = "list_{$model->table}";
	  if ( !method_exists($this->s3, $method) ) {
	    return false;
	  }
	  
	  if (isset($queryData['conditions']['region'])) {
	    $this->s3->set_region($queryData['conditions']['region']);
	    unset($queryData['conditions']['region']) ;
	  }
	  
	  switch ( $model->table ) {
	  case 'objects':
      $bucket = null;
      if ( array_key_exists("{$model->alias}.id", $queryData['conditions']) ) {
        $option = array(
          'pcre' => '/'.$queryData['conditions']["{$model->alias}.id"].'/i',
        );
  	    return $this->s3->get_object_list(Set::extract($model->data, "{$model->alias}.bucket"), $option);
      } else {
  	    $bucket = Set::extract($queryData, 'conditions.bucket');
  	    unset($queryData['conditions']['bucket']);
	    
  	    $limit = (int)Set::extract($queryData,'limit');
  	    if ( $limit > 0 ) {
  	      $queryData['conditions']['max-keys'] = $limit;
  	    }
	    
  	    if ( empty($bucket) ) {
  	      $bucket = Set::extract($queryData, 'conditions.id');
  	      unset($queryData['conditions']['id']);
  	    }

  	    return $this->s3->{$method}($bucket,$queryData['conditions']);
  	  }
	    break ;
	  default:
      $bucket = null;
      if ( array_key_exists("{$model->alias}.id", $queryData['conditions']) ) {
        $bucket = "/".$queryData['conditions']["{$model->alias}.id"]."/";
      }

	    if ( !empty($bucket) ) {
  	    return $this->s3->get_bucket_list($bucket);
	    } else {
  	    return $this->s3->{$method}($queryData['conditions']);
  	  }
	    break ;
	  }
	}
	
	private function createResult(&$model, $map, &$body, $queryData)
	{
	  $base = Set::extract($body,$map['result']);
	  if ( !is_array($base) ) {
	    return array($base);
    }

	  $result = array();
	  switch($model->table) {
	  case 'objects':
      if ( isset($base[0]) ) {
    	  foreach( $base as $key => $value ) {
          $result[] = array($model->alias => $value);
    	  }
      } else {
        $result[] = array($model->alias => $base);
      }
	    break ;
	  default:
  	  $propname = Inflector::classify($model->table);
      if ( isset($base[$propname][0]) ) {
    	  foreach( $base[$propname] as $key => $value ) {
          $result[] = array($model->alias => $value);
    	  }
      } else {
        $result[] = array($model->alias => $base[$propname]);
      }
	    break ;
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
	
} // END class S3Source extends DataSource