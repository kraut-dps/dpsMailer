<?php

Yii::import( 'ext.dpsmailer.tests.dpsMailerTestCase' );

/**
 * main class tests
 */
class dpsMailerTest extends dpsMailerTestCase {

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
					'aFrom' => array( 'from_mailer@example.com' => 'from_mailer' ),
					'aToSupport' => array( 'to_support@example.com' => 'to_support' ),
					'sSupportLayout' => 'layouts/mainSupport'
				),
				$aConfig
			)
		);
		$oMailer->init();
		return $oMailer;
	}

	/**
	 * test order of set email params
	 * 1. dpsMailer property
	 * 2. set in view
	 * 3. Force EmailEvent set
	 */
	public function testOrderSet() {

		$oMailer = $this->getMailer();

		$oTestBehavior = $oMailer->getBehavior();


		// simple
		$oMailer->sendByView(
			array( 'to_call@example.com' => 'to_call' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oLastEmailEvent = $oTestBehavior->getLastEmail();

		$this->assertEquals( 'subject_tpl', $oLastEmailEvent->sSubject );
		$this->assertContains( 'Привет user_call!', $oLastEmailEvent->sBody );

		$this->assertEquals( true, $oLastEmailEvent->bHtml );

		$this->assertEquals( array( 'from_mailer@example.com' => 'from_mailer' ), $oLastEmailEvent->aFrom );

		$this->assertEquals(
			array( 'to_call@example.com' => 'to_call' ),
			$oLastEmailEvent->aTo
		);

		$this->assertEmpty(
			$oLastEmailEvent->aCc
		);

		$this->assertEmpty(
			$oLastEmailEvent->aBcc
		);


		// with set in view
		$oMailer->sendByView(
			array( 'to_call@example.com' => 'to_call' ),
			'mailSet',
			array( 'sUsername' => 'user_call' )
		);

		$oLastEmailEvent = $oTestBehavior->getLastEmail();

		$this->assertEquals( 'subject_tpl', $oLastEmailEvent->sSubject );
		$this->assertContains( 'Привет user_call!', $oLastEmailEvent->sBody );

		$this->assertEquals( false, $oLastEmailEvent->bHtml );

		$this->assertEquals( array( 'from_tpl@example.com' => 'from_tpl' ), $oLastEmailEvent->aFrom );

		$this->assertEquals(
			array( 'to_tpl@example.com' => 'to_tpl' ),
			$oLastEmailEvent->aTo
		);

		$this->assertEquals(
			array( 'cc_tpl@example.com' => 'cc_tpl' ),
			$oLastEmailEvent->aCc
		);

		$this->assertEquals(
			array( 'bcc_tpl@example.com' => 'bcc_tpl' ),
			$oLastEmailEvent->aBcc
		);


		// with force event set
		$oForceEvent = new dpsEmailEvent();
		$oForceEvent->sSubject = 'subject_force';
		$oForceEvent->bHtml = true;
		$oForceEvent->sBody = 'Привет мир!';
		$oForceEvent->aFrom = $oMailer->formatEmail( array( 'from_force@example.com' => 'from_force' ) );
		$oForceEvent->aTo = $oMailer->formatEmail( array( 'to_force@example.com' => 'to_force' ) );
		$oForceEvent->aCc = $oMailer->formatEmail( array( 'cc_force@example.com' => 'cc_force' ) );
		$oForceEvent->aBcc = array();

		$oMailer->sendByView(
			array( 'to_call@example.com' => 'to_call' ),
			'mailSet',
			array( 'sUsername' => 'user_call' ),
			$oForceEvent
		);

		$oLastEmailEvent = $oTestBehavior->getLastEmail();

		$this->assertEquals( 'subject_force', $oLastEmailEvent->sSubject );
		$this->assertContains( 'Привет мир!', $oLastEmailEvent->sBody );

		$this->assertEquals( true, $oLastEmailEvent->bHtml );

		$this->assertEquals( array( 'from_force@example.com' => 'from_force' ), $oLastEmailEvent->aFrom );

		$this->assertEquals(
			array( 'to_force@example.com' => 'to_force' ),
			$oLastEmailEvent->aTo
		);

		$this->assertEquals(
			array( 'cc_force@example.com' => 'cc_force' ),
			$oLastEmailEvent->aCc
		);

		$this->assertEmpty(
			$oLastEmailEvent->aBcc
		);

	}

	public function testToSupport() {

		$oMailer = $this->getMailer();

		$oTestBehavior = $oMailer->getBehavior();

		$oMailer->sendSupportByView(
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oLastEmailEvent = $oTestBehavior->getLastEmail();

		$this->assertEquals( 'subject_tpl', $oLastEmailEvent->sSubject );
		$this->assertContains( 'Привет user_call!', $oLastEmailEvent->sBody );
		$this->assertContains( 'support_header', $oLastEmailEvent->sBody );
		$this->assertContains( 'support_footer', $oLastEmailEvent->sBody );

		$this->assertEquals(
			array( 'to_support@example.com' => 'to_support' ),
			$oLastEmailEvent->aTo
		);
	}

	public function testBalance( ) {
		$oMailer = $this->getMailer(
			array(
				'aBehaviors' => array(
					'test' => array(
						'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
					),
					'testAlt' => array(
						'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
					),
				),
				'cBalance' => function( $aSenders, dpsEmailEvent $oEmailEvent ) {
					$sToEmail = current( array_keys( $oEmailEvent->aTo ) );
					switch( $sToEmail ) {
						case 'to_1@example.com':
							// send by "testAlt" only
							$aSenders[ 'testAlt' ] = true;
							$aSenders[ 'test' ] = false;
							break;
						case 'to_2@example.com':
							// send by "test" only
							$aSenders[ 'testAlt' ] = false;
							$aSenders[ 'test' ] = true;
							break;
						case 'to_3@example.com':
							// no send
							$aSenders[ 'testAlt' ] = false;
							$aSenders[ 'test' ] = false;
							break;
						case 'to_4@example.com':
							// send by "test" and "testAlt"
							$aSenders[ 'testAlt' ] = true;
							$aSenders[ 'test' ] = true;
							break;
					}
					return $aSenders;
				}
			)
		);

		$oMailer->sendByView(
			array( 'to_1@example.com' => 'to_1' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oMailer->sendByView(
			array( 'to_2@example.com' => 'to_2' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oMailer->sendByView(
			array( 'to_3@example.com' => 'to_3' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oMailer->sendByView(
			array( 'to_4@example.com' => 'to_4' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oTestBehavior = $oMailer->getBehavior( 'test' );

		$aEmails = $oTestBehavior->getLastEmails();

		$this->assertCount( 2, $aEmails );

		$this->assertEquals(
			array( 'to_2@example.com' => 'to_2' ),
			$aEmails[ 0 ]->aTo
		);

		$this->assertEquals(
			array( 'to_4@example.com' => 'to_4' ),
			$aEmails[ 1 ]->aTo
		);


		$oTestAltBehavior = $oMailer->getBehavior( 'testAlt' );

		$aEmails = $oTestAltBehavior->getLastEmails();

		$this->assertCount( 2, $aEmails );

		$this->assertEquals(
			array( 'to_1@example.com' => 'to_1' ),
			$aEmails[ 0 ]->aTo
		);

		$this->assertEquals(
			array( 'to_4@example.com' => 'to_4' ),
			$aEmails[ 1 ]->aTo
		);

	}

	public function filterTest() {

		$oMailer = $this->getMailer(
			array(
				'aBehaviors' => array(
					'test' => array(
						'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
						'cFilter' => function( dpsEmailEvent $oEmailEvent ) {
							$sToEmail = current( array_keys( $oEmailEvent->aTo ) );
							return $sToEmail == 'to_1@example.com';
						}
					),
					'testAlt' => array(
						'class' => 'ext.dpsmailer.behaviors.dpsMemoryMailerBehavior',
						'cFilter' => function( dpsEmailEvent $oEmailEvent ) {
							$sToEmail = current( array_keys( $oEmailEvent->aTo ) );
							return $sToEmail == 'to_2@example.com';
						}
					),
				),
			)
		);

		$oMailer->sendByView(
			array( 'to_1@example.com' => 'to_1' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oMailer->sendByView(
			array( 'to_2@example.com' => 'to_2' ),
			'mailNotSet',
			array( 'sUsername' => 'user_call' )
		);

		$oTestBehavior = $oMailer->getBehavior( 'test' );

		$aEmails = $oTestBehavior->getLastEmails();

		$this->assertCount( 1, $aEmails );

		$this->assertEquals(
			array( 'to_1@example.com' => 'to_1' ),
			$aEmails[ 0 ]->aTo
		);

		$oTestAltBehavior = $oMailer->getBehavior( 'testAlt' );

		$aEmails = $oTestAltBehavior->getLastEmails();

		$this->assertCount( 1, $aEmails );

		$this->assertEquals(
			array( 'to_2@example.com' => 'to_2' ),
			$aEmails[ 0 ]->aTo
		);
	}

	public function formatEmailTest() {
		$oMailer = $this->getMailer();

		$this->assertEquals(
			array( 'to@example.com' => '' ),
			$oMailer->formatEmail( 'to@example.com' )
		);

		$this->assertEquals(
			array( 'to@example.com' => 'to' ),
			$oMailer->formatEmail( array( 'to@example.com' => 'to' ) )
		);
	}
}
