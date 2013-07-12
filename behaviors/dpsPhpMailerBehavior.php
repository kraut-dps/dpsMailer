<?php

/**
 * send by PHPMailer
 * @example
array(
	'class' => 'ext.dpsmailer.behaviors.dpsFileMailerBehavior',
	'sLibPath' => '/path/to/lib/PhpMailer',
	'aOptions' => array(
		'Mailer'		=> 'smtp',
		'Host'			=> 'smtp.gmail.com',
		'CharSet'		=> 'utf-8',
		'SMTPAuth'		=> true,
		'SMTPSecure'	=> 'ssl',
		'Port'			=> 465,
		'Username'		=> '[username]',
		'Password'		=> '[password]',
	)
)
 */
class dpsPhpMailerBehavior extends dpsSendBehavior {

	/**
	 * @var string path to lib PHPMailer
	 */
	public $sLibPath;

	/**
	 * @var array PhpMailer options
	 */
	public $aOptions = array();

	/**
	 * @var PHPMailer instance
	 */
	protected $oPhpMailer;

	/**
	 * init PHPMailer instance
	 */
	public function init() {
		require_once $this->sLibPath . '/class.phpmailer.php';
		$this->oPhpMailer = new PHPMailer;
		foreach ( $this->aOptions as $sOpt => $mValue ) {
			$this->oPhpMailer->$sOpt = $mValue;
		}
	}

	/**
	 * main method
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function send( dpsEmailEvent $oEmailEvent ) {

		$this->prepareSend( $oEmailEvent );

		$this->oPhpMailer->Send();

		// clear for next run
		$this->oPhpMailer->ClearAllRecipients();
		$this->oPhpMailer->ClearAttachments();
	}

	/**
	 * fill PHPMailer instance by email data
	 * @param dpsEmailEvent $oEmailEvent
	 */
	protected function prepareSend( dpsEmailEvent $oEmailEvent ) {

		// content
		$this->oPhpMailer->Subject = $oEmailEvent->sSubject;
		$this->oPhpMailer->Body = $oEmailEvent->sBody;
		$this->oPhpMailer->IsHTML( $oEmailEvent->bHtml );

		// from, to, cc, bcc
		foreach ( $oEmailEvent->aFrom as $sEmail => $sName ) {
			$this->oPhpMailer->SetFrom( $sEmail, $sName );
			$this->oPhpMailer->AddReplyTo( $sEmail, $sName );
			break;
		}
		foreach ( $oEmailEvent->aTo as $sEmail => $sName ) {
			$this->oPhpMailer->AddAddress( $sEmail, $sName );
		}
		if( !empty( $oEmailEvent->aCc ) ) {
			foreach ( $oEmailEvent->aCc as $sEmail => $sName ) {
				$this->oPhpMailer->AddCC( $sEmail, $sName );
			}
		}
		if( !empty( $oEmailEvent->aBcc ) ) {
			foreach ( $oEmailEvent->aBcc as $sEmail => $sName ) {
				$this->oPhpMailer->AddBCC( $sEmail, $sName );
			}
		}

		// attachments and embeds
		if( !empty( $oEmailEvent->aAttachments ) ) {
			foreach ( $oEmailEvent->aAttachments as $sFilePath => $sFileName ) {
				if( empty( $sFileName ) ) {
					$this->oPhpMailer->AddEmbeddedImage( $sFilePath, $this->getOwner()->getCid( $sFilePath ) );
				} else {
					$this->oPhpMailer->AddAttachment( $sFilePath, $sFileName );
				}
			}
		}
	}
}