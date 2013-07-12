<?php

/**
 * base for tests
 */
class dpsMailerTestCase extends CTestCase {

	protected $sTmpDir;

	public function setUp() {
		$this->sTmpDir = Yii::app()->dpsMailerTestConfig->sTmpPath;

		if( !is_dir( $this->sTmpDir ) ) {
			mkdir( $this->sTmpDir, 0777, true );
		}
		parent::setUp();
	}

}