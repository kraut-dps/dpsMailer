<?php

/**
 * send by Swift
 * @example
array(
	'class' => 'ext.dpsmailer.behaviors.dpsSwiftMailerBehavior',
	'sLibPath'=> /path/to/lib/Swift',
	'sTransport' => 'Swift_SmtpTransport',
	'aOptions' => array(
		'Host'			=> 'smtp.gmail.com',
		'Port'			=> 465,
		'Encryption'	=> 'ssl',
		'Username'		=> '[username]',
		'Password'		=> '[password]',
	)
)
 */
class dpsSwiftMailerBehavior extends dpsSendBehavior {

	/**
	 * @var string path to lib Swift
	 */
	public $sLibPath;

	/**
	 * Swift_FailoverTransport,
	 * Swift_MailTransport,
	 * Swift_NullTransport,
	 * Swift_SendmailTransport,
	 * Swift_SmtpTransport,
	 * Swift_SpoolTransport
	 * @var string swift transport
	 */
	public $sTransport = 'Swift_SmtpTransport';

	/**
	 * @var array swift options
	 */
	public $aOptions = array();

	/**
	 * @var Swift_Mailer instance
	 */
	protected $oSwiftMailer;

	/**
	 * init Swift instance
	 */
	public function init() {
		require $this->sLibPath . '/classes/Swift.php';
		Yii::registerAutoloader( array( 'Swift', 'autoload' ) );
		require $this->sLibPath . '/swift_init.php';

		$oTransport = call_user_func( array( $this->sTransport, 'newInstance' ) );

		foreach( $this->aOptions as $sOption => $mValue ) {

			$sMethod = 'set' . ucfirst( $sOption );
			call_user_func( array( $oTransport, $sMethod ), $mValue );
		}

		$this->oSwiftMailer = Swift_Mailer::newInstance( $oTransport );
	}

	/**
	 * main method
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function send( dpsEmailEvent $oEmailEvent ) {

		$oMessage = $this->prepareSend( $oEmailEvent );

		$this->oSwiftMailer->send( $oMessage );
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return Swift_Mime_SimpleMessage
	 */
	protected function prepareSend( dpsEmailEvent $oEmailEvent ) {

		// general
		/** @var Swift_Message $oMessage */
		$oMessage = Swift_Message::newInstance()
			->setSubject( $oEmailEvent->sSubject )
			->setFrom( $oEmailEvent->aFrom )
			->setReplyTo( $oEmailEvent->aFrom )
			->setTo( $oEmailEvent->aTo )
			->setBody( $oEmailEvent->sBody )
			->setContentType( $oEmailEvent->bHtml ? 'text/html' : 'text/plain' );
		if( !empty( $oEmailEvent->aCc ) ) {
			$oMessage->setCc( $oEmailEvent->aCc );
		}
		if( !empty( $oEmailEvent->aBcc) ) {
			$oMessage->setBcc( $oEmailEvent->aBcc );
		}

		// attachments
		if( !empty( $oEmailEvent->aAttachments ) ) {
			foreach( $oEmailEvent->aAttachments as $sFilePath => $sFileName ) {
				if( empty( $sFileName ) ) {
					$oEmbed = Swift_Attachment::fromPath( $sFilePath )
						->setId( $this->getOwner()->getCid( $sFilePath ) );
					$oMessage->embed( $oEmbed );
				} else {
					$oAttachment = Swift_Attachment::fromPath( $sFilePath )
						->setFilename( $sFileName );
					$oMessage->attach( $oAttachment );
				}
			}
		}

		return $oMessage;
	}
}