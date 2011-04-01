<?php
class AvailabilityZoneTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'AvailabilityZone', 
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
	  $expected = array(
	    array(
	      $this->Model->alias => array(
	        'zoneName' => 'ap-northeast-1a',
	        'zoneState' => 'available',
	        'regionName' => 'ap-northeast-1',
	        'messageSet' => array(
	         
	        )
	      ),
	    ),
	    array(
	      $this->Model->alias => array(
	        'zoneName' => 'ap-northeast-1b',
	        'zoneState' => 'available',
	        'regionName' => 'ap-northeast-1',
	        'messageSet' => array(
	         
	        )
	      ),
	    ),
	  );
	  $this->assertEqual($expected, $result) ;
	  
    $conditions = array(
      'region' => 'ap-northeast-1',
      'ZoneName' => 'ap-northeast-1a',
    );
	  $result = $this->Model->find('all', array('conditions'=>$conditions));
	  $expected = array(
	    array(
	      $this->Model->alias => array(
	        'zoneName' => 'ap-northeast-1a',
	        'zoneState' => 'available',
	        'regionName' => 'ap-northeast-1',
	        'messageSet' => array(
	         
	        )
	      ),
	    ),
	  );
	  $this->assertEqual($expected, $result) ;
  }
}