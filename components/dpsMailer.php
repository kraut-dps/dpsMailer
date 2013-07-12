<?php

Yii::import( 'ext.dpsmailer.components.dpsEmailEvent' );
Yii::import( 'ext.dpsmailer.components.dpsEmailController' );
Yii::import( 'ext.dpsmailer.behaviors.dpsSendBehavior' );

/**
 * main component dpsMailer
 * @example
array(
	'class' => 'ext.dpsmailer.components.dpsMailer',
	'sViewPath' => '/path/to/mail/views',
	'aFrom' => array( 'from@example.com' => 'from' ),
	'aBehaviors' => array(
		'swift' => array(
			'class' => 'ext.dpsmailer.behaviors.dpsSwiftMailerBehavior',
			'sLibPath'=> '/path/to/Swift/Swift-5.0.0/lib',
			'sTransport' => 'Swift_SmtpTransport',
			'aOptions' => array(
				'Host'			=> 'smtp.gmail.com',
				'Port'			=> 465,
				'Encryption'		=> 'ssl',
				'Username'		=> '[username]',
				'Password'		=> '[password]',
			),
		),
	),
)
 */
class dpsMailer extends CComponent {

	/**
	 * @var array
	 */
	public $aBehaviors = array();

	/**
	 * @var string absolute disk path
	 */
	public $sViewPath;

	/**
	 * @var string relative about sViewPath path by default, '' - no layout
	 */
	public $sLayout = '';

	/**
	 * @var string relative about sViewPath path by default, '' - no layout
	 */
	public $sSupportLayout = '';

	/**
	 * @var array ( sEmail => sName ) by default
	 */
	public $aFrom;

	/**
	 * @var array ( sEmail => sName ) for set support emails
	 */
	public $aToSupport;

	/**
	 * 1 arg = array of behaviors, array( 'behavior1' => true, 'behavior2' => true, ... )
	 * 2 arg = dpsEmailEvent
	 * need return array of behaviors in format such as 1 arg, true - enabled, false - disabled
	 * @var callable for set balance of behaviors
	 */
	public $cBalance;

	/**
	 * @var int for tests now time
	 */
	public $iTime;

	/**
	 * @var array of send behavior nicks
	 */
	protected $aSendBehaviorNicks;

	/**
	 * Yii CComponent init
	 */
	public function init() {
		$this->attachBehaviors( $this->aBehaviors );
		if( empty( $this->sViewPath ) ) {
			$this->sViewPath = Yii::getPathOfAlias( 'application.views' );
		}
		if( !isset( $this->iTime ) ) {
			$this->iTime = time();
		}
	}

	/**
	 * simple send email by view template
	 * @param string|array $mTo string email or array( email => name )
	 * @param string $sView about $this->sViewPath relative path
	 * @param array $aViewData data for fill view
	 * @param dpsEmailEvent $oForceEvent for force set email params
	 * @param dpsEmailEvent $oBeforeRenderEvent for force set email params before render
	 */
	public function sendByView(
		$mTo,
		$sView,
		$aViewData = array(),
		$oForceEvent = null,
		$oBeforeRenderEvent = null
	) {

		// prepare email event
		$oEmailEvent = $this->getNewEvent();
		$this->prepareEvent( $oEmailEvent );
		$oEmailEvent->aTo = $this->formatEmail( $mTo );

		// force replace email event params before render
		if ( !empty( $oBeforeRenderEvent ) ) {
			$this->replaceEventParams( $oEmailEvent, $oBeforeRenderEvent );
		}

		$this->render( $oEmailEvent, $sView, $aViewData );

		// force replace email event params
		if ( !empty( $oForceEvent ) ) {
			$this->replaceEventParams( $oEmailEvent, $oForceEvent );
		}

		$this->validateEvent( $oEmailEvent );

		$this->execBalance( $oEmailEvent );

		$this->onSend( $oEmailEvent );
	}

	/**
	 * send email to support
	 * @param string $sView about $this->sViewPath relative path
	 * @param array $aViewData data for fill view
	 * @param dpsEmailEvent $oForceEvent for force set email params
	 * @param dpsEmailEvent $oBeforeRenderEvent for force set email params before render
	 */
	public function sendSupportByView(
		$sView,
		$aViewData = array(),
		$oForceEvent = null,
		$oBeforeRenderEvent = null
	) {
		// set support layout
		if( is_null( $oBeforeRenderEvent ) ) {
			$oBeforeRenderEvent = new dpsEmailEvent();
		}
		$oBeforeRenderEvent->sLayout = $this->sSupportLayout;


		$this->sendByView( $this->aToSupport, $sView, $aViewData, $oForceEvent, $oBeforeRenderEvent );
	}

	/**
	 * format mixed data to array( email => name )
	 * @param $mEmail
	 * @throws CException
	 * @return array
	 */
	public function formatEmail( $mEmail ) {

		if ( is_string( $mEmail ) ) {
			return array( $mEmail => '' );
		} elseif ( is_array( $mEmail ) ) {
			return ( array ) $mEmail;
		} else {
			throw new CException( 'Error format email' );
		}
	}

	/**
	 * get content id ( cid ) for embed attachment
	 * @param $sFilePath
	 * @return string
	 */
	public function getCid( $sFilePath ) {
		return md5( $sFilePath ) . '@' . 'dpsMailer.generated';
	}

	/**
	 * for simple use in tests
	 * @param string $sName
	 * @return dpsMemoryMailerBehavior
	 */
	public function getBehavior( $sName = 'memory' ) {
		return $this->asa( $sName );
	}

	/**
	 * for custom email event structure class set
	 * @return dpsEmailEvent
	 */
	protected function getNewEvent() {
		return new dpsEmailEvent();
	}

	/**
	 * @param dpsEmailEvent $oEvent
	 */
	protected function prepareEvent( dpsEmailEvent $oEvent ) {
		$oEvent->aFrom = $this->aFrom;
		$oEvent->sLayout = $this->sLayout;
		$oEvent->iCreateTime = $this->iTime;
	}

	/**
	 * @param dpsEmailEvent $oEvent
	 * @param dpsEmailEvent $oForce
	 */
	protected function replaceEventParams( dpsEmailEvent $oEvent, dpsEmailEvent $oForce ) {
		if ( !empty( $oForce ) ) {
			$aForceVars = get_object_vars( $oForce );
			foreach ( $aForceVars as $sParam => $sValue ) {
				if ( !isset( $sValue ) ) {
					continue;
				}
				$oEvent->$sParam = $sValue;
			}
		}
	}

	/**
	 * for custom set class of controller
	 * @return dpsEmailController
	 */
	protected function getNewController() {
		return new dpsEmailController( 0 );
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @param string $sView
	 * @param array $aViewData
	 */
	protected function render( dpsEmailEvent $oEmailEvent, $sView, $aViewData ) {
		$oController = $this->getNewController();
		$oController->setEmailEvent( $oEmailEvent );
		$oController->setMailer( $this );
		$oController->setViewPath( $this->sViewPath );
		$oController->layout = empty( $oEmailEvent->sLayout )
			? false
			: $oEmailEvent->sLayout;
		$oEmailEvent->sView = $sView;
		$oEmailEvent->aViewData = $aViewData;
		$oEmailEvent->sBody = $oController->render( $sView, $aViewData, true );
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function onSend( dpsEmailEvent $oEmailEvent ) {
		$this->raiseEvent( 'onSend', $oEmailEvent );
	}

	/**
	 * set send behaviors enable flag
	 * @param array $aEnabledSenders array( 'behavior1' => true, 'behavior2' => false )
	 */
	public function setSendersEnable( $aEnabledSenders = array() ) {
		foreach( $this->aBehaviors as $sNick => $cCallable ) {
			if( empty( $aEnabledSenders[ $sNick ] ) ) {
				$this->disableBehavior( $sNick );
			} else {
				$this->enableBehavior( $sNick );
			}
		}
	}

	/**
	 * balance send behaviors
	 * @param $oEmailEvent
	 * @throws CException
	 */
	protected function execBalance( $oEmailEvent ) {

		// no balance callable? - return
		if( empty( $this->cBalance ) ) {
			return;
		}

		// get send behaviors in need format
		$aSenders = array();
		foreach( $this->getSendBehaviorNicks() as $sSenderNick ) {
			$aSenders[ $sSenderNick ] = true;
		}

		$mEnabledSenders = call_user_func( $this->cBalance, $aSenders, $oEmailEvent );

		// format array of behaviors enable flags
		if( is_array( $mEnabledSenders ) ) {
			$aEnabledSenders = $mEnabledSenders;
		} elseif( is_string( $mEnabledSenders ) ) {
			$aEnabledSenders = array( $mEnabledSenders => true );
		} else {
			throw new CException( 'Invalid cBalance return value' );
		}

		$this->setSendersEnable( $aEnabledSenders );
	}

	/**
	 * find behaviors with send method
	 * @return array
	 */
	protected function getSendBehaviorNicks() {

		// mini cache
		if( isset( $this->aSendBehaviorNicks ) ) {
			return $this->aSendBehaviorNicks;
		}

		$this->aSendBehaviorNicks = array();
		$aBehaviorNicks = array_keys( $this->aBehaviors );
		foreach( $aBehaviorNicks as $sNick ) {
			if( method_exists( $this->asa( $sNick ), 'send' ) ) {
				$this->aSendBehaviorNicks[] = $sNick;
			}
		}

		return $this->aSendBehaviorNicks;
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @throws CException
	 */
	protected function validateEvent( dpsEmailEvent $oEmailEvent ) {
		if( empty( $oEmailEvent->aFrom ) ) {
			throw new CException( 'Empty "from"' );
		}
		if( empty( $oEmailEvent->sSubject ) ) {
			throw new CException( 'Empty "subject"' );
		}
		if( !empty( $oEmailEvent->aAttachments ) ) {
			foreach( $oEmailEvent->aAttachments as $sFilePath => $sFileName ) {
				if( !is_file( $sFilePath ) ) {
					throw new CException( 'Attachment file not found ' . $sFilePath );
				}
			}
		}
	}
}