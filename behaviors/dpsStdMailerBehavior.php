<?php

/**
 * php mail() send
 * @example
array(
	'class' => 'ext.dpsmailer.behaviors.dpsStdMailerBehavior',
)
 */
class dpsStdMailerBehavior extends dpsSendBehavior {

	/**
	 * @var string
	 */
	public $sCharset = 'utf-8';

	/**
	 * @var string
	 */
	protected $sMimeBoundary;

	/**
	 * main method
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function send( dpsEmailEvent $oEmailEvent ) {

		// define "to"
		$sToEmail = current( array_keys( $oEmailEvent->aTo ) );
		$sToName = $oEmailEvent->aTo[ $sToEmail ];

		mail(
			$this->formatEmail( $sToEmail, $sToName ),
			$this->encode( $oEmailEvent->sSubject ),
			$this->getBody( $oEmailEvent ),
			$this->getHeaders( $oEmailEvent ),
			$this->getMailAdditionalParams( $oEmailEvent )
		);
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return string
	 */
	protected function getBody( dpsEmailEvent $oEmailEvent ) {

		// no attachments - no problems :)
		if( empty( $oEmailEvent->aAttachments ) ) {
			return $oEmailEvent->sBody;
		}

		// body block
		$sContent = "--" . $this->getMimeBoundary() . "\n" .
		$this->getBodyTypeHeaders( $oEmailEvent ) . "\n\n" .
		$oEmailEvent->sBody . "\n\n";

		// attachments
		foreach( $oEmailEvent->aAttachments as $sFilePath => $sFileName ) {
			if( empty( $sFileName ) ) {
				$sCid = "Content-ID: <" . $this->getOwner()->getCid( $sFilePath ) . ">\n";
				$sFileName = basename( $sFilePath );
				$sDisposition = 'inline';
			} else {
				$sCid = "";
				$sDisposition = 'attachment';
			}

			$sData = chunk_split(base64_encode( file_get_contents( $sFilePath ) ) );

			$sContent .= "--" . $this->getMimeBoundary() . "\n" .
			"Content-Type: application/octet-stream; name=\"" . $sFileName . "\"\n" .
			$sCid .
			"Content-Disposition: " . $sDisposition . "; filename=\"" . $sFileName . "\"\n" .
			"Content-Transfer-Encoding: base64\n\n" . $sData . "\n";
		}

		// end boundary
		$sContent .= "--" . $this->getMimeBoundary() . "--";

		return $sContent;
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return string
	 */
	protected function getHeaders( dpsEmailEvent $oEmailEvent ) {
		return
			$this->getHeaderByEmails( 'From', $oEmailEvent->aFrom ) .
			$this->getHeaderByEmails( 'Reply-To', $oEmailEvent->aFrom ) .
			$this->getHeaderByEmails( 'Cc', $oEmailEvent->aCc ) .
			$this->getHeaderByEmails( 'Bcc', $oEmailEvent->aBcc ) .
			$this->getBodyHeaders( $oEmailEvent );
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return string
	 */
	protected function getBodyHeaders( dpsEmailEvent $oEmailEvent ) {

		$sRet = "MIME-Version: 1.0\n";
		if( empty( $oEmailEvent->aAttachments ) ) {
			$sRet .= $this->getBodyTypeHeaders( $oEmailEvent );
		} else {
			$sRet .= "Content-Type: multipart/mixed;\n" .
				" boundary=\"" . $this->getMimeBoundary() . "\"";
		}
		return $sRet;
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return string
	 */
	protected function getBodyTypeHeaders( dpsEmailEvent $oEmailEvent ) {

		return "Content-Type: " . $this->getContentType( $oEmailEvent ) . "; charset=" . $this->sCharset . "\n" .
			"Content-Transfer-Encoding: 8bit";
	}

	/**
	 * for set mail() 5 argument
	 * @param dpsEmailEvent $oEmailEvent
	 * @return null
	 */
	protected function getMailAdditionalParams( dpsEmailEvent $oEmailEvent ) {
		return null;
	}

	/**
	 * email encode
	 * @param string $sStr
	 * @return string
	 */
	protected function encode( $sStr = '' ) {
		if( !$sStr ) {
			return $sStr;
		}
		return "=?" . $this->sCharset . "?B?" . base64_encode( $sStr ) . "?= ";
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return string
	 */
	protected function getContentType( dpsEmailEvent $oEmailEvent ) {
		return $oEmailEvent->bHtml ? 'text/html' : 'text/plain';
	}

	/**
	 * for get from, to, cc, bcc headers
	 * @param string $sHeader
	 * @param array $aEmails
	 * @return string
	 */
	protected function getHeaderByEmails( $sHeader, $aEmails = array() ) {

		if( empty( $aEmails ) ) {
			return '';
		}

		$aHeader = array();
		foreach ( $aEmails as $sEmail => $sName ) {
			$aHeader[] = $this->formatEmail( $sEmail, $sName );
		}
		return $sHeader . ': ' . implode( ', ', $aHeader ) . "\n";
	}

	/**
	 * format email for header
	 * @param string $sEmail
	 * @param string $sName
	 * @return string
	 */
	protected function formatEmail( $sEmail, $sName = '' ) {

		if( empty( $sEmail ) ) {
			return '';
		}
		if( empty( $sName ) ) {
			return $sEmail;
		}
		return $this->encode( $sName ) . "<" . trim( $sEmail ) . ">";
	}

	/**
	 * @return string
	 */
	protected function getMimeBoundary() {
		if( empty( $this->sMimeBoundary ) ) {
			$this->sMimeBoundary = md5( time() * time() );
		}
		return $this->sMimeBoundary;
	}
}