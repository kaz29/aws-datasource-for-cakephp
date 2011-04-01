<?php
class Ec2SoucreTestCase extends CakeTestCase {  
	function startTest() 
	{
	}

  function endTest() 
  {
  }
  	
	function testRegion()
	{
	  $Region =& ClassRegistry::init(
      array(array(
        'class' => 'Region', 
        'ds'=>'ec2_test',
      ))
    );
    
	  $result = $Region->find('all');
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
	}
}