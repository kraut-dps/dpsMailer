<?php

Yii::import( 'ext.dpsmailer.tests.dpsMailerTestCase' );

/**
 * test Swift send
 */
class dpsSwiftMailerTest extends dpsMailerTestCase {

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
							'class' => 'ext.dpsmailer.behaviors.dpsSwiftMailerBehavior4Test',
							'sLibPath' => Yii::app()->dpsMailerTestConfig->sSwiftMailerLibPath,
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
		$oForceEvent->aBcc = $oMailer->formatEmail( array( 'bcc@example.com' => 'скрытая копия' ) );
		$oMailer->sendByView(
			array( 'to@example.com' => 'получатель' ),
			'mailExample-utf-8',
			array( 'sUsername' => 'получатель' ),
			$oForceEvent
		);

		$oMessage = dpsSwiftMailerBehavior4Test::$oSwiftMessage;

		$this->assertEquals( 'Для получатель', $oMessage->getSubject() );
		$this->assertContains( 'получатель', $oMessage->getBody() );

		$this->assertEquals( 'text/html', $oMessage->getContentType() );

		$this->assertEquals( array( 'from@example.com' => 'отправитель' ), $oMessage->getFrom() );

		$this->assertEquals( array( 'to@example.com' => 'получатель' ), $oMessage->getTo() );

		$this->assertEquals( array( 'bcc@example.com' => 'скрытая копия' ), $oMessage->getBcc() );
	}
}

Yii::import( 'ext.dpsmailer.behaviors.dpsSendBehavior' );
Yii::import( 'ext.dpsmailer.behaviors.dpsSwiftMailerBehavior' );

/**
 * replace send in dpsSwiftMailerBehavior
 */
class dpsSwiftMailerBehavior4Test extends dpsSwiftMailerBehavior {

	/**
	 * @var Swift_Message
	 */
	static public $oSwiftMessage;

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function send( dpsEmailEvent $oEmailEvent ) {
		self::$oSwiftMessage = $this->prepareSend( $oEmailEvent );
	}
}