<?php

/**
 * save in memory
 * @example
array(
	'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
)
 */
class dpsMemoryMailerBehavior extends dpsSendBehavior {

	/**
	 * @var array index by "To" header
	 */
	protected $aIndexToEmail = array();

	/**
	 * @var int -1 = infinity buffer
	 */
	public $iBufferSize = -1;

	/**
	 * save data in index by add to buffer
	 * @param dpsEmailEvent $oEmailEvent
	 * @return int
	 */
	public function addToBuffer( dpsEmailEvent $oEmailEvent ) {
		$iPos = parent::addToBuffer( $oEmailEvent );
		foreach ( $oEmailEvent->aTo as $sEmail => $sName ) {
			$this->aIndexToEmail[ $sEmail ][ ] = $iPos;
		}
		return $iPos;
	}

	/**
	 * clear index by flush buffer
	 */
	public function flushBuffer() {
		parent::flushBuffer();
		$this->aIndexToEmail = array();
	}

	/**
	 * @param string $sToEmail
	 * @param bool $bFlushBuffer
	 * @return dpsEmailEvent|null
	 */
	public function getLastEmail( $sToEmail = '', $bFlushBuffer = true ) {

		$aEmails = $this->getLastEmails( $sToEmail, $bFlushBuffer );

		if( empty( $aEmails ) ) {
			return null;
		}
		
		// last array element
		return $aEmails[ count( $aEmails ) - 1 ];
	}

	/**
	 * @param string $sToEmail
	 * @param bool $bFlushBuffer
	 * @return dpsEmailEvent[]
	 */
	public function getLastEmails( $sToEmail = '', $bFlushBuffer = true ) {

		// find emails
		if ( $sToEmail ) {
			$aEmails = array();
			if ( isset( $this->aIndexToEmail[ $sToEmail ] ) ) {
				$aIndex = $this->aIndexToEmail[ $sToEmail ];
				foreach( $aIndex as $iIndex ) {
					$aEmails[] = $this->aBuffer[ $iIndex ];
				}
			}
		} else {
			$aEmails = $this->aBuffer;
		}


		if( $bFlushBuffer ) {
			$this->flushBuffer();
		}

		// return
		if ( empty( $aEmails ) ) {
			return array();
		}
		return $aEmails;
	}
}