# AWS(Amazon Web Services) datasource for CakePHP

## Requirements
PHP5, 
aws-sdk-for-php

## Installation

this repository should be installed in the same way as any other plugin.

To install the driver for use in a single application:

	cd my/app/plugins
	git clone git://github.com/kaz29/cakephp-aws.git aws_source

To install the driver for use in any/multiple application(s)

	# where ROOT is the name of the directory parent to the base index.php of CakePHP.
	cd ROOT/plugins
	git clone git://github.com/kaz29/cakephp-aws.git aws_source
	
## Sample Code

To use this DB driver, install (obviously) and define a db source such as follows:

	<?php
	// app/config/database.php
	class DATABASE_CONFIG {

		var $ec2 = array(
			'driver' => 'AwsDatasource.Ec2Source',
			'key' => 'Your AWS Access Key',
			'secret_key' => 'Your AWS Secret Access Key',
		);
		var $s3 = array(
			'driver' => 'AwsDatasource.S3Source',
			'key' => 'Your AWS Access Key',
			'secret_key' => 'Your AWS Secret Access Key',
		);


## Author
Kazuhiro Watabane ([kaz29](http://twitter.com/kaz29))
