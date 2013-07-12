<?php

Yii::import( 'ext.dpsmailer.tests.dpsMailerTestCase' );

/**
 * save in memory test
 */
class dpsMemoryMailerTest extends dpsMailerTestCase {

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
						'memory' => array(
							'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
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

		$oMailer->sendByView(
			array( 'to1@example.com' => 'получатель1' ),
			'mailExample-utf-8',
			array( 'sUsername' => 'получатель1' )
		);

		$oMailer->sendByView(
			array( 'to2@example.com' => 'получатель2' ),
			'mailExample-utf-8',
			array( 'sUsername' => 'получатель2' )
		);


		$oForceEvent = new dpsEmailEvent();
		$oForceEvent->aCc = $oMailer->formatEmail( array( 'cc2@example.com' => 'копия2' ) );
		$oForceEvent->aBcc = $oMailer->formatEmail( array( 'bcc2@example.com' => 'скрытая копия2' ) );

		$oMailer->sendByView(
			array( 'to2@example.com' => 'получатель2' ),
			'mailExample-utf-8',
			array( 'sUsername' => 'получатель2' ),
			$oForceEvent
		);

		$oMailer->sendByView(
			array( 'to3@example.com' => 'получатель3' ),
			'mailExample-utf-8',
			array( 'sUsername' => 'получатель3' )
		);

		$oLastEmailEvent = $oMailer->getBehavior()->getLastEmail( 'to1@example.com', false );

		$this->assertEquals( 'Для получатель1', $oLastEmailEvent->sSubject );
		$this->assertContains( 'получатель1', $oLastEmailEvent->sBody );

		$this->assertEquals( true, $oLastEmailEvent->bHtml );

		$this->assertEquals( array( 'from@example.com' => 'отправитель' ), $oLastEmailEvent->aFrom );

		$this->assertEquals(
			array( 'to1@example.com' => 'получатель1' ),
			$oLastEmailEvent->aTo
		);

		$this->assertEmpty(
			$oLastEmailEvent->aCc
		);

		$this->assertEmpty(
			$oLastEmailEvent->aBcc
		);


		$oLastEmailEvent = $oMailer->getBehavior()->getLastEmail( 'to2@example.com', false );

		$this->assertEquals( 'Для получатель2', $oLastEmailEvent->sSubject );
		$this->assertContains( 'получатель2', $oLastEmailEvent->sBody );

		$this->assertEquals( true, $oLastEmailEvent->bHtml );

		$this->assertEquals( array( 'from@example.com' => 'отправитель' ), $oLastEmailEvent->aFrom );

		$this->assertEquals(
			array( 'to2@example.com' => 'получатель2' ),
			$oLastEmailEvent->aTo
		);

		$this->assertEquals(
			array( 'cc2@example.com' => 'копия2' ),
			$oLastEmailEvent->aCc
		);

		$this->assertEquals(
			array( 'bcc2@example.com' => 'скрытая копия2' ),
			$oLastEmailEvent->aBcc
		);


		$oLastEmailEvent = $oMailer->getBehavior()->getLastEmail( '', false );

		$this->assertEquals( 'Для получатель3', $oLastEmailEvent->sSubject );
		$this->assertContains( 'получатель3', $oLastEmailEvent->sBody );

		$this->assertEquals( true, $oLastEmailEvent->bHtml );

		$this->assertEquals( array( 'from@example.com' => 'отправитель' ), $oLastEmailEvent->aFrom );

		$this->assertEquals(
			array( 'to3@example.com' => 'получатель3' ),
			$oLastEmailEvent->aTo
		);

		$this->assertEmpty(
			$oLastEmailEvent->aCc
		);

		$this->assertEmpty(
			$oLastEmailEvent->aBcc
		);
	}
}
