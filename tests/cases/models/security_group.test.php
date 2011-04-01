<?php
class SecurityGroupTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'SecurityGroup', 
        'ds'=>'ec2_test',
      ))
    );
	}

  function endTest() 
  {
    unset($this->Model);
  }
  
  function testFind()
  {
	  $result = $this->Model->find('all');
    $this->assertTrue(count($result) > 0);
    
    $conditions = array(
      'GroupName' => 'test001',
    );
    
    $result = $this->Model->find('all', array('conditions'=>$conditions));
    $this->assertTrue(count($result) == 1);
    
    $result = $this->Model->find('first', array('conditions'=>$conditions));
    $this->assertTrue(count($result) == 1);
  }
}