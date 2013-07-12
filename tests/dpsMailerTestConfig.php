<?php

/**
 * for tests params config
 */
class dpsMailerTestConfig extends CComponent {

	/**
	 * @var string path for runtime temporary dir for tests
	 */
	public $sTmpPath;

	/**
	 * path to lib PhpMailer
	 * @var string
	 */
	public $sPhpMailerLibPath;

	/**
	 * path to lib Swift
	 * @var string
	 */
	public $sSwiftMailerLibPath;


	public function init() {
		if( empty( $this->sTmpPath ) ) {
			$this->sTmpPath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'dpsMailerTests';
		}
		if( empty( $this->sPhpMailerLibPath ) ) {
			$this->sPhpMailerLibPath = Yii::getPathOfAlias( 'application.xlib' ) . '/php-mailer.5.2.1';
		}
		if( empty( $this->sSwiftMailerLibPath ) ) {
			$this->sSwiftMailerLibPath = Yii::getPathOfAlias( 'application.xlib' ) . '/Swift-5.0.0/lib';
		}
	}
}
