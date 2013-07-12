in config:
...,
'components' => array(
	...,
	'dpsMailerTestConfig' => array(
		'class' => 'ext.dpsmailer.tests.dpsMailerTestConfig',
		... // см. dpsMailerTestConfig
	),
	...

run:
phpunit -c /path/to/yii/phpunit.xml /path/to/extensions/dpsmailer/tests
