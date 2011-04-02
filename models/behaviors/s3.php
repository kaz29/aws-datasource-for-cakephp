<?php
/**
 * Amazon S3 Behavior
 *
 * @package       aws_source
 */
 
/**
 * Amazon S3 Behavior Class
 *
 * @package aws_source
 * @author Kaz Watanabe
 **/
class S3Behavior extends ModelBehavior
{
  private $_settings = array(
	  'ds' => 'default'
  ) ;
  private $S3Object = null;
  
  public function setup(&$Model, $config = array())
  {
    $this->settings[$Model->alias] = array_merge($this->_settings, $config);
	  $this->S3Object =& ClassRegistry::init(
      array(array(
        'class' => $Model->alias.'S3Object', 
        'table' => 'objects',
        'ds' => $this->settings[$Model->alias]['ds'],
      ))
    );
  }
  
  public function putToS3(&$Model, $bucket, $filename, $data)
  {
    $data = array(
      'bucket' => $bucket,
      'filename' => $filename,
      'body' => $data,
      'option' => array(
        'acl' => AmazonS3::ACL_PUBLIC,
      )
    );
    
    $this->S3Object->create();
    $this->S3Object->set($data);
    return $this->S3Object->save();
  }   
}
