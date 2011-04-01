<?php
class KeyPairTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'KeyPair', 
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
    $conditions = array(
      'region' => 'ap-northeast-1',
    );
	  $result = $this->Model->find('all', array('conditions'=>$conditions));
    $this->assertTrue(count($result)>0);
    $this->assertTrue(Set::extract($result, "0.{$this->Model->alias}.keyName"));
    $this->assertTrue(Set::extract($result, "0.{$this->Model->alias}.keyFingerprint"));
  }
}