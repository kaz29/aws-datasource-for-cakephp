<?php
class RegionTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'Region', 
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
	  $expected = array(
	    array(
	      'Region' => array(
	        'regionName' => 'eu-west-1',
	        'regionEndpoint' => 'ec2.eu-west-1.amazonaws.com',
	      )
	    ),
	    array(
	      'Region' => array(
	        'regionName' => 'us-east-1',
	        'regionEndpoint' => 'ec2.us-east-1.amazonaws.com',
	      )
	    ),
	    array(
	      'Region' => array(
	        'regionName' => 'ap-northeast-1',
	        'regionEndpoint' => 'ec2.ap-northeast-1.amazonaws.com',
	      )
	    ),
	    array(
	      'Region' => array(
	        'regionName' => 'us-west-1',
	        'regionEndpoint' => 'ec2.us-west-1.amazonaws.com',
	      )
	    ),
	    array(
	      'Region' => array(
	        'regionName' => 'ap-southeast-1',
	        'regionEndpoint' => 'ec2.ap-southeast-1.amazonaws.com',
	      )
	    ),
	  );
	  $this->assertEqual($expected, $result) ;
	  
    $conditions = array(
      'RegionName' => 'eu-west-1',
    );
    $result = $this->Model->find('all', array('conditions'=>$conditions));
	  $expected = array(
	    array(
	      'Region' => array(
	        'regionName' => 'eu-west-1',
	        'regionEndpoint' => 'ec2.eu-west-1.amazonaws.com',
	      )
	    ),
	  );
	  $this->assertEqual($expected, $result) ;
	  
    $conditions = array(
      'RegionName' => array('us-east-1','eu-west-1'),
    );
    $result = $this->Model->find('all', array('conditions'=>$conditions));
	  $expected = array(
	    array(
	      'Region' => array(
	        'regionName' => 'eu-west-1',
	        'regionEndpoint' => 'ec2.eu-west-1.amazonaws.com',
	      )
	    ),
	    array(
	      'Region' => array(
	        'regionName' => 'us-east-1',
	        'regionEndpoint' => 'ec2.us-east-1.amazonaws.com',
	      )
	    ),
	  );
	  $this->assertEqual($expected, $result) ;
  }
}