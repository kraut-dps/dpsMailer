<?php

Yii::import( 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior' );

/**
 * save in cache
 * @example
array(
	'class' => 'ext.dpsmailer.behaviors.dpsCacheMailerBehavior',
)
 */
class dpsCacheMailerBehavior extends dpsMemoryMailerBehavior {

	/**
	 * @var string name yii component cache
	 */
	public $sCache = 'cache';

	/**
	 * @var string
	 */
	public $sCacheKey = 'dpsCacheMailerData';

	/**
	 * @var int seconds cache expire
	 */
	public $iCacheExpire = 1800;

	public function init() {
		parent::init();

		// load email events buffer from cache
		$this->loadFromCache();

		// on app end save to cache
		Yii::app()->attachEventHandler(
			'onEndRequest',
			array( $this, 'saveToCache' )
		);
	}

	/**
	 * @param string $sToEmail
	 * @param bool $bFlushBuffer
	 * @return dpsEmailEvent[]
	 */
	public function getLastEmails( $sToEmail = '', $bFlushBuffer = true ) {
		$this->loadFromCache();
		return parent::getLastEmails( $sToEmail, $bFlushBuffer );
	}


	public function loadFromCache() {
		$aData = unserialize( $this->getCacheComponent()->get( $this->sCacheKey ) );
		if( empty( $aData ) ) {
			$aData = array(
				'aBuffer' => array(),
				'aIndexToEmail' => array(),
			);
		}

		$this->aBuffer = $aData[ 'aBuffer' ];
		$this->aIndexToEmail = $aData[ 'aIndexToEmail' ];
	}


	public function saveToCache() {
		$aData = array(
			'aBuffer' => $this->aBuffer,
			'aIndexToEmail' => $this->aIndexToEmail,
		);
		$this
			->getCacheComponent()
			->set( $this->sCacheKey, serialize( $aData ), $this->iCacheExpire );
	}

	/**
	 * @return CCache
	 */
	protected function getCacheComponent() {
		return Yii::app()->getComponent( $this->sCache );
	}
}