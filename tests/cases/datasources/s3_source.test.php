<?php
class Ec2SoucreTestCase extends CakeTestCase {  
	function startTest() 
	{
	  $this->S3Object =& ClassRegistry::init(
      array(array(
        'class' => 'S3Object', 
        'table' => 'objects',
        'ds'=>'s3_test',
      ))
    );
	  $this->Bucket =& ClassRegistry::init(
      array(array(
        'class' => 'Bucket', 
        'ds'=>'s3_test',
      ))
    );
    
    $this->regionId = Configure::read('AWSDataSource.s3test.regionId');
    $this->domainName = Configure::read('AWSDataSource.s3test.domainname');
	}

  function endTest() 
  {
    unset($this->S3Object) ;
    unset($this->Bucket) ;
  }
  
  function __testCreateBucket()
  {
    $data = array(
      'bucket' => "aws-datasource-test.{$this->domainName}",
      'region' => $this->regionId,
      'acl' => AmazonS3::ACL_PRIVATE,
    );
    
    $this->Bucket->create();
    $this->Bucket->set($data);
    $result = $this->Bucket->save();
    $expected = array(
      'Bucket' => array(
        'bucket' => "aws-datasource-test.{$this->domainName}",
        'region' => $this->regionId,
        'acl' => AmazonS3::ACL_PRIVATE,
        'option' => null,
      ),
    );
    $this->assertEqual($expected, $result) ;
    
    $result = $this->Bucket->delete("aws-datasource-test.{$this->domainName}");
    $this->assertTrue($result) ;
  }
  
	function _testFind()
	{
	  $Bucket =& ClassRegistry::init(
      array(array(
        'class' => 'Bucket', 
        'ds'=>'s3_test',
      ))
    );
    
	  $result = $Bucket->find('all');
	  $this->assertTrue(count($result)>1) ;
	  $this->assertTrue(Set::extract($result, "0.Bucket.Name"));
	  $this->assertTrue(Set::extract($result, "0.Bucket.CreationDate"));

	  $S3Object =& ClassRegistry::init(
      array(array(
        'class' => 'S3Object', 
        'table' => 'objects',
        'ds'=>'s3_test',
      ))
    );
    
    $conditions = array(
      'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
    );
	  $result = $S3Object->find('all', array('conditions'=>$conditions));
	  $this->assertTrue(count($result)>1) ;
	  $this->assertTrue(Set::extract($result, "0.S3Object.Key"));
	  $this->assertTrue(Set::extract($result, "0.S3Object.LastModified"));
	  $this->assertTrue(Set::extract($result, "0.S3Object.ETag"));
	  $this->assertTrue(Set::extract($result, "0.S3Object.Owner"));
	  $this->assertEqual('STANDARD', Set::extract($result, "0.S3Object.StorageClass"));
    $conditions = array(
      'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
    );
	  $result = $S3Object->find('all', array('conditions'=>$conditions, 'limit' => 1));
	  $this->assertTrue(count($result)==1) ;
	  $this->assertTrue(Set::extract($result, "0.S3Object.Key"));
	  $this->assertTrue(Set::extract($result, "0.S3Object.LastModified"));
	  $this->assertTrue(Set::extract($result, "0.S3Object.ETag"));
	  $this->assertTrue(Set::extract($result, "0.S3Object.Owner"));
	  $this->assertEqual('STANDARD', Set::extract($result, "0.S3Object.StorageClass"));
	}
  	
  function testCreateObject()
  {
    $data = array(
      'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
      'filename' => 'aws_datasource_test_object',
      'body' => 'FOOBAR',
    );
    
    $this->S3Object->create();
    $this->S3Object->set($data);
    $result = $this->S3Object->save();
    $expected = array(
      'S3Object' => array(
        'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
        'filename' => 'aws_datasource_test_object',
      ),
    );
    $this->assertEqual($expected, $result) ;

    $conditions = array(
      'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
      'prefix' => 'aws_datasource_test_object',
    );
	  $result = $this->S3Object->find('first', array('conditions'=>$conditions));
	  $this->assertEqual('aws_datasource_test_object', Set::extract($result, 'S3Object.Key'));
	  $this->assertEqual(6, Set::extract($result, 'S3Object.Size'));
	  $this->assertEqual('STANDARD', Set::extract($result, "S3Object.StorageClass"));
	  
	  $result = $this->S3Object->get_object(Configure::read('AWSDataSource.s3test.bucket'),'aws_datasource_test_object');
	  $this->assertEqual('FOOBAR', $result) ;

    $data = array(
      'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
      'filename' => 'aws_datasource_test_object',
      'body' => 'HOGEHOGE',
    );
    
    $this->S3Object->create();
    $this->S3Object->set($data);
    $result = $this->S3Object->save();
    $expected = array(
      'S3Object' => array(
        'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
        'filename' => 'aws_datasource_test_object',
      ),
    );
    $this->assertEqual($expected, $result) ;

    $conditions = array(
      'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
      'prefix' => 'aws_datasource_test_object',
    );
	  $result = $this->S3Object->find('first', array('conditions'=>$conditions));
	  $this->assertEqual('aws_datasource_test_object', Set::extract($result, 'S3Object.Key'));
	  $this->assertEqual(8, Set::extract($result, 'S3Object.Size'));
	  $this->assertEqual('STANDARD', Set::extract($result, "S3Object.StorageClass"));
	  
	  $result = $this->S3Object->get_object(Configure::read('AWSDataSource.s3test.bucket'),'aws_datasource_test_object');
	  $this->assertEqual('HOGEHOGE', $result) ;

    $data = array(
      'S3Object' => array(
        'bucket' => Configure::read('AWSDataSource.s3test.bucket'),
        'filename' => 'aws_datasource_test_object',
      ),
    );
    
    $this->S3Object->create();
    $this->S3Object->set($data);
    $result = $this->S3Object->delete('aws_datasource_test_object');
    $this->assertTrue($result) ;    
  }
}