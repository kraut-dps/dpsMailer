dpsMailer
=========

Yii extension for sending emails

###Features
* comfortable interface for testing logics of emails sending 
* email templates just like page templates can have layouts 
* possibility to send emails via Swift Mailer [ http://swiftmailer.org/ ] and PhpMailer [ https://github.com/Synchro/PHPMailer ]


###Installation

Extract files in the directory protected/extensions/dpsmailer of the Yii project.

###Config

Put the below code in the configuration file in the 'components' section:

```php
'dpsMailer' => array(
	'class' => 'ext.dpsmailer.components.dpsMailer',
	'sViewPath' => '[/path/to/mail/views]',
	'aFrom' => array( '[from@example.com]' => '[from]' ),
	'aBehaviors' => array(
		'swift' => array(
			'class' => 'ext.dpsmailer.behaviors.dpsSwiftMailerBehavior',
			'sLibPath'=> '[/path/to/Swift/Swift-5.0.0/lib]',
			'sTransport' => 'Swift_SmtpTransport',
			'aOptions' => array(
				'Host'			=> 'smtp.gmail.com',
				'Port'			=> 465,
				'Encryption'	=> 'ssl',
				'Username'		=> '[username]',
				'Password'		=> '[password]',
			),
		),
	),
)
```


###Usage

Sending email: 
```php
Yii::app()->dpsMailer->sendByView(
	array( 'to@example.com' => 'Username' ),
	'emailTpl', // template email view 
	array(
		'sUsername' => 'Username',
		'sLogoPicPath' => '/path/to/logo.gif',
		'sFilePath' => '/path/to/attachment.txt',
	)
);
```

Email view:
```php
<?
// /path/to/mail/views/emailTpl.php

/** @var dpsEmailController $this */

$this->setSubject( 'For ' . $sUsername );
$this->setLayout( 'emailLayoutTpl' );
$this->attach( $sFilePath ); ?>


Hello <?= $sUsername ?>!
```


Email layout:
```php
<?
// /path/to/mail/views/emailLayoutTpl.php

/** @var dpsEmailController $this */ ?>


<img src="<?= $this->embed( $sLogoPicPath ) ?>"/>

<?= $content ?>
```

###Extended variants of the usage:

####Saving emails as files
```php
// in config

'aBehaviors' => array(
	'file' => array(
		'class' => 'ext.dpsmailer.behaviors.dpsFileMailerBehavior',
		'sDir'	=> '[/path/for/save/eml]'
	),
),
```

####Saving emails in the memory
```php
// in config

'aBehaviors' => array(
	'memory' => array(
		'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
	),
),
```

Getting return email objects from tests:
```php
$oEmailEvent = Yii::app()->dpsMailer->getBehavior( 'memory' )->getLastEmail( 'to@example.com' );
$oEmailEvent = Yii::app()->dpsMailer->getBehavior( 'memory' )->getLastEmails();
```

####Saving emails in cache 
It allows addressing to the sent emails from another php process, for example from functional tests.

```php
// in config

'aBehaviors' => array(
	'cache' => array(
		'class' => 'ext.dpsmailer.behaviors.dpsCacheMailerBehavior',
	),
),
```

####Dynamic definition of the behavior 
```php
Yii::app()->dpsMailer->setSendersEnable( array() );

// dynamically we add behavior of emails saving in the memory
Yii::app()->dpsMailer->attachBehavior( array(
	'preview' => array(
		'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior'
	),
) );

Yii::app()->dpsMailer->sendByView( ... );

// Getting the object with email data 
$oEmailEvent = Yii::app()->dpsMailer->getBehavior( 'preview' )->getLastEmail();
```

####Balancing between behaviors 
```php
// in config

'dpsMailer' => array(
		…,
		'aBehaviors' => array(
			'mailer1' => array( ... ),
			'mailer2' => array( ... ),
			'mailer3' => array( ... ),
		),
		'cBalance' =>function( $aSenders, dpsEmailEvent $oEmailEvent ) {
			// distributing load (having information about 'senders' load, we can make full balancer)
			$iNum = mt_rand( 1, 3 );
			$aSenders[ 'mailer1' ] = $iNum == 1;
			$aSenders[ 'mailer2' ] = $iNum == 2;
			$aSenders[ 'mailer3' ] = $iNum == 3;
			return $aSenders;
		},
),

```

####Sending emails from CConsoleApplication:
```php
// in console config
return array(
	...,
	'behaviors' => array ( 'ext.dpsmailer.components.dpsRenderConsoleBehavior' ),
	'components' => array( ... ),
	...
);
```

####Sending an email with extra parameters:
 ```php

// special object for extra parameters passing 
$oForceEmailEvent = new dpsEmailEvent();
$oForceEmailEvent->aCc = array( 'cc@example.com' => '' );

Yii::app()->dpsMailer->sendByView(
	array( 'to@example.com' => 'to' ),
	'emailTpl',
	array( 'sUsername' => 'username' ), 
	$oForceEmailEvent // object with extra parameters
);
```
