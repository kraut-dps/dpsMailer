<?php

Yii::import( 'ext.dpsmailer.tests.dpsMailerTestCase' );

/**
 * test PhpMailer send
 */
class dpsPhpMailerTest extends dpsMailerTestCase {

	/**
	 * @param $aConfig
	 * @return dpsMailer
	 */
	public function getMailer( $aConfig = array() ) {
		$oMailer = Yii::createComponent(
			CMap::mergeArray(
				array(
					'class' => 'ext.dpsmailer.components.dpsMailer',
					'aBehaviors' => array(
						'phpMailer' => array(
							'class' => 'ext.dpsmailer.behaviors.dpsPhpMailerBehavior4Test',
							'sLibPath'=> Yii::app()->dpsMailerTestConfig->sPhpMailerLibPath,
						),
					),
					'sViewPath' => Yii::getPathOfAlias( 'ext.dpsmailer.tests.views' ),
					'aFrom' => array( 'from@example.com' => 'отправитель' ),
				),
				$aConfig
			)
		);
		$oMailer->init();
		return $oMailer;
	}

	public function testSend() {

		$oMailer = $this->getMailer();

		$oForceEvent = new dpsEmailEvent();
		$oForceEvent->aCc = $oMailer->formatEmail( array( 'cc@example.com' => 'копия' ) );
		$oMailer->sendByView(
			array( 'to@example.com' => 'получатель' ),
			'mailExample-utf-8',
			array( 'sUsername' => 'получатель' ),
			$oForceEvent
		);

		$oPhpMailer = dpsPhpMailerBehavior4Test::$oPhpMailer4Test;

		$this->assertAttributeEquals( 'Для получатель', 'Subject', $oPhpMailer );
		$this->assertAttributeContains( 'получатель', 'Body', $oPhpMailer );

		$this->assertAttributeEquals( 'text/html', 'ContentType', $oPhpMailer );

		$this->assertAttributeEquals( 'from@example.com', 'From', $oPhpMailer );
		$this->assertAttributeEquals( 'отправитель', 'FromName', $oPhpMailer );

		$this->assertAttributeEquals(
			array( array( 'to@example.com', 'получатель' ) ),
			'to',
			$oPhpMailer
		);

		$this->assertAttributeEquals(
			array( array( 'cc@example.com', 'копия' ) ),
			'cc',
			$oPhpMailer
		);
	}
}

Yii::import( 'ext.dpsmailer.behaviors.dpsSendBehavior' );
Yii::import( 'ext.dpsmailer.behaviors.dpsPhpMailerBehavior' );

/**
 * replace send in dpsPhpMailerBehavior
 */
class dpsPhpMailerBehavior4Test extends dpsPhpMailerBehavior {

	/**
	 * @var PHPMailer
	 */
	static public $oPhpMailer4Test;

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function send( dpsEmailEvent $oEmailEvent ) {

		$this->prepareSend( $oEmailEvent );
		self::$oPhpMailer4Test = $this->oPhpMailer;
	}
}