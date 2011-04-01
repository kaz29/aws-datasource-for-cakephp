<?php
class InstanceTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'Instance', 
        'ds'=>'ec2_test',
      ))
    );

    $this->instanceId = Configure::read('AWSDataSource.testInstance.instanceId');
    $this->regionId = Configure::read('AWSDataSource.testInstance.regionId');
    $this->assertNotNull($this->instanceId);
    $this->assertNotNull($this->regionId);
	}

  function endTest() 
  {
    unset($this->Model);
  }
  
  function testFind()
  {
    $conditions = array(
      'region' => $this->regionId,
    );
	  $result = $this->Model->find('all', array('conditions'=>$conditions));
    $this->assertTrue(count($result)>0);
    $this->assertTrue(Set::extract($result, "0.{$this->Model->alias}.instancesSet.item.instanceId"));
  }
  
  function _testStartInstances()
  {
    $params = array(
      'params' => array(
        $this->instanceId,
      ),
      'region' => $this->regionId 
    );
    $result = $this->Model->start_instances($params);
    $this->assertEqual($this->instanceId, Set::extract($result,'Instance.instanceId'));
    $this->assertTrue(Set::extract($result,'Instance.currentState.code'));
    $this->assertTrue(Set::extract($result,'Instance.currentState.name'));
    $this->assertTrue(Set::extract($result,'Instance.previousState.code'));
    $this->assertTrue(Set::extract($result,'Instance.previousState.name'));
  }

  function _testStopInstances()
  {
    $params = array(
      'params' => array(
        $this->instanceId,
      ),
      'region' => $this->regionId 
    );
    $result = $this->Model->stop_instances($params);
    $this->assertTrue(Set::extract($result,'Instance.currentState.code'));
    $this->assertTrue(Set::extract($result,'Instance.currentState.name'));
    $this->assertTrue(Set::extract($result,'Instance.previousState.code'));
    $this->assertTrue(Set::extract($result,'Instance.previousState.name'));
  }

  function _testRunInstances()
  {
    $imageId = Configure::read('AWSDataSource.testInstance.imageId');
    $this->assertNotNull($imageId);
    $params = array(
      'params' => array(
        $imageId,
        1,
        1,
        array(
          'InstanceType' => 't1.micro',
        )
      ),
      'region' => $this->regionId 
    );
    $result = $this->Model->run_instances($params);
    $this->assertTrue(Set::extract($result,'Instance.instanceId'));
    $this->assertEqual($imageId, Set::extract($result,'Instance.imageId'));
    $this->assertEqual(0, Set::extract($result,'Instance.instanceState.code'));
    $this->assertEqual('pending', Set::extract($result,'Instance.instanceState.name'));
  }

  function _testTerminateInstances()
  {
    $instanceId = Configure::read('AWSDataSource.testInstance.terminateInstanceId');
    $this->assertNotNull($instanceId);
    $params = array(
      'params' => array(
        $instanceId,
      ),
      'region' => $this->regionId 
    );
    $result = $this->Model->terminate_instances($params);
    $this->assertTrue(Set::extract($result,'Instance.currentState.code'));
    $this->assertTrue(Set::extract($result,'Instance.currentState.name'));
    $this->assertTrue(Set::extract($result,'Instance.previousState.code'));
    $this->assertTrue(Set::extract($result,'Instance.previousState.name'));
  }
}