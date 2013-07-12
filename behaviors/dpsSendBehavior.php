<?php

/**
 * base for send behavior
 * @method dpsMailer getOwner()
 */
abstract class dpsSendBehavior extends CBehavior {

	/**
	 * @var callable, one argument dpsEmailEvent, if return false - not exec send
	 */
	public $cFilter;

	/**
	 * 0 - without buffer, -1 - infinity buffer
	 * @var int
	 */
	public $iBufferSize = 0;

	/**
	 * @var dpsEmailEvent[]
	 */
	protected $aBuffer = array();

	/**
	 * yii component require
	 */
	public function init() {}

	/**
	 * @return array
	 */
	public function events() {
		return array(
			'onSend' => 'onSend',
		);
	}

	/**
	 * before attach - init
	 * @param CComponent $owner
	 */
	public function attach( $owner ) {
		$this->init();
		parent::attach( $owner );
	}

	/**
	 * main handler
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function onSend( dpsEmailEvent $oEmailEvent ) {

		// set filter? - exec
		if(
			!empty( $this->cFilter ) &&
			is_callable( $this->cFilter ) &&
			!call_user_func( $this->cFilter, $oEmailEvent )
		) {
			return;
		}

		if( $this->iBufferSize == 0 ) {
			// without buffer
			$this->send( $oEmailEvent );
		} else {

			$this->addToBuffer( $oEmailEvent );

			// buffer full? - flush
			if( $this->iBufferSize !== -1 && count( $this->aBuffer ) >= $this->iBufferSize ) {
				$this->flushBuffer();
			}
		}
	}

	/**
	 * exec buffer
	 */
	public function flushBuffer() {
		if( !empty( $this->aBuffer ) ) {
			foreach( $this->aBuffer as $oEmailEvent ) {
				$this->send( $oEmailEvent );
			}
			$this->aBuffer = array();
		}
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return int
	 */
	protected function addToBuffer( dpsEmailEvent $oEmailEvent ) {
		$iNextPos = count( $this->aBuffer );
		$this->aBuffer[ $iNextPos ] = $oEmailEvent;
		return $iNextPos;
	}

	/**
	 * empty
	 * @param dpsEmailEvent $oEmailEvent
	 */
	protected function send( dpsEmailEvent $oEmailEvent ) {
		return;
	}
}