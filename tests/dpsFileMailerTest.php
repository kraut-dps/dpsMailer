<?php

Yii::import( 'ext.dpsmailer.tests.dpsMailerTestCase' );

/**
 * test save to file
 */
class dpsFileMailerTest extends dpsMailerTestCase {

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
						'file' => array(
							'class' => 'ext.dpsmailer.behaviors.dpsFileMailerBehavior',
							'sDir' => $this->sTmpDir,
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

	/**
	 * save with different charsets
	 */
	public function testSendWithCharset() {

		$this->sendWithCharset( 'utf-8' );
		$this->sendWithCharset( 'windows-1251' );
		$this->sendWithCharset( 'koi8-r' );
	}

	/**
	 * test garbage collector
	 */
	public function testGc() {

		// prepare old files paths
		$sDirNew = $this->sTmpDir . DIRECTORY_SEPARATOR . 'new';
		$sFileNew = $sDirNew . DIRECTORY_SEPARATOR . 'tmp';
		$sDirOld = $this->sTmpDir . DIRECTORY_SEPARATOR . 'old';
		$sFileOld = $sDirOld . DIRECTORY_SEPARATOR . 'tmp';

		// create files and dirs
		mkdir( $sDirNew );
		mkdir( $sDirOld );
		touch( $sFileNew, strtotime( '-9 hour' ) );
		touch( $sDirNew, strtotime( '-9 hour' ) );
		touch( $sFileOld, strtotime( '-11 hour' ) );
		touch( $sDirOld, strtotime( '-11 hour' ) );

		// test config
		$aConfig = array(
			'aBehaviors' => array(
				'file' => array(
					'sOldFileCriteria' => '-10 hour'
				),
			),
		);

		/** @var dpsFileMailerBehavior $oFileBehavior */
		$oFileBehavior = $this->getMailer( $aConfig )->getBehavior( 'file' );

		$oFileBehavior->gc();

		// asserts
		$this->assertTrue( file_exists( $sDirNew ) );
		$this->assertTrue( file_exists( $sFileNew ) );
		$this->assertFalse( file_exists( $sDirOld ) );
		$this->assertFalse( file_exists( $sFileOld ) );

		// unlink for next run
		@unlink( $sFileNew );
		@rmdir( $sDirNew );
		@unlink( $sFileOld );
		@rmdir( $sDirOld );
	}

	/**
	 * @param $sCharset
	 */
	protected function sendWithCharset( $sCharset ) {
		$aConfig = array(
			'aBehaviors' => array(
				'file' => array(
					'sCharset' => $sCharset
				),
			),
			'aFrom' => array( 'from@example.com' => iconv( 'utf-8', $sCharset, 'отправитель' ) ),
		);
		$oMailer = $this->getMailer( $aConfig );

		$oMailer->sendByView(
			array( 'to@example.com' => iconv( 'utf-8', $sCharset, 'получатель' ) ),
			'mailExample-' . $sCharset,
			array( 'sUsername' => iconv( 'utf-8', $sCharset, 'получатель' ) )
		);

		$aFiles = glob(
			$this->sTmpDir . DIRECTORY_SEPARATOR . date( 'Y-m-d' . DIRECTORY_SEPARATOR . 'H-i' ) . '*' . 'eml'
		);

		// last file
		sort( $aFiles );
		$sFile = end( $aFiles );

		// asserts
		$sContent = file_get_contents( $sFile );
		$this->assertContains( iconv( 'utf-8', $sCharset, 'получатель' ), $sContent );
		$this->assertContains( $sCharset, $sContent );
	}
}
