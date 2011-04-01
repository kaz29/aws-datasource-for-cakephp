<?php
class ImageTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->Model =& ClassRegistry::init(
      array(array(
        'class' => 'Image', 
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
      'Owner' => 'amazon',
      'Filter' => array(
        array(
          'Name' => 'architecture',
          'Value' => 'x86_64'
        ),
        array(
          'Name' => 'root-device-type',
          'Value' => 'ebs'
        ),
        array(
          'Name' => 'virtualization-type',
          'Value' => 'paravirtual'
        ),
        array(
          'Name' => 'name',
          'Value' => 'CloudFormation-redmine_1.1.1_1.0_9008a391-64bit-20110227-0758'
        ),
      )
    );
    $result = $this->Model->find('all', array('conditions'=>$conditions));
    $this->assertEqual(1, count($result));
    $this->assertEqual('ami-c403a8c5', $result[0][$this->Model->alias]['imageId']);
    $this->assertEqual('CloudFormation-redmine_1.1.1_1.0_9008a391-64bit-20110227-0758', $result[0][$this->Model->alias]['name']);
  }
}