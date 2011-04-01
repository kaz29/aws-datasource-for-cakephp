<?php
class AddressTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'Address', 
        'ds'=>'ec2_test',
      ))
    );

    $this->instanceId = Configure::read('AWSDataSource.testAddress.imageId');
    $this->assertNotNull($this->instanceId);
    $this->publicIp = Configure::read('AWSDataSource.testAddress.publicIp');
    $this->assertNotNull( $this->publicIp);
	}

  function endTest() 
  {
    unset($this->Model);
  }
  
  function testFind()
  {
    $conditions = array(
      'region' => 'ap-northeast-1',
    );
	  $result = $this->Model->find('all', array('conditions'=>$conditions));
    $this->assertTrue(count($result)>0);
    $this->assertTrue(isset($result[0][$this->Model->alias]['publicIp']));
  }
  
  function testAssociate()
  {
    $params = array(
      'params' => array(
        $this->instanceId,
        $this->publicIp,
      ),
    );
    $result = $this->Model->associate_address($params);
    $this->assertFalse(is_array($result));
    $this->assertTrue($result);
  }
  
  function testDeassociate()
  {
    $params = array(
      'params' => array(
        $this->instanceId,
        $this->publicIp,
      ),
    );
    $result = $this->Model->associate_address($params);
    $this->assertFalse(is_array($result));
    $this->assertTrue($result);
  }
}