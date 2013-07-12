<?php

Yii::import( 'ext.dpsmailer.behaviors.dpsStdMailerBehavior' );

/**
 * save in file
 * @example
array(
	'class' => 'ext.dpsmailer.behaviors.dpsFileMailerBehavior',
	'sDir' => '/path/to/save/eml',
)
 */
class dpsFileMailerBehavior extends dpsStdMailerBehavior {

	/**
	 * @var string path to save emails directory
	 */
	public $sDir;

	/**
	 * @var string garbage collection criteria check old file ( strtotime )
	 */
	public $sOldFileCriteria = '-1 month';

	/**
	 * @var int logic from Yii::CDbCache
	 */
	public $iGcProbability = 100;

	/**
	 * @var int for tests now time
	 */
	public $iTime;

	/**
	 * @var bool garbage collection executed?
	 */
	protected $bGcExec = false;

	/**
	 * Yii init component
	 */
	public function init() {
		if( empty( $this->sDir ) ) {
			$this->sDir = Yii::app()->runtimePath . DIRECTORY_SEPARATOR . 'dpsMailer';
		}
		if( !isset( $this->iTime ) ) {
			$this->iTime = time();
		}
		parent::init();
	}

	/**
	 * main method
	 * @param dpsEmailEvent $oEmailEvent
	 */
	public function send( dpsEmailEvent $oEmailEvent ) {

		// garbage collection
		if( $this->needGc() ) {
			$this->gc();
			$this->bGcExec = true;
		}

		// prepare file
		$sFileName = $this->createFileName( $oEmailEvent );
		if( !is_dir( dirname( $sFileName ) ) ) {
			mkdir( dirname( $sFileName ), 0777, true );
		}

		$sContent = $this->getEmlContent( $oEmailEvent );

		// save
		file_put_contents( $sFileName, $sContent );
	}

	/**
	 * eml content by email event
	 * @param dpsEmailEvent $oEmailEvent
	 * @return string
	 */
	public function getEmlContent( dpsEmailEvent $oEmailEvent ) {
		return $this->getHeaderByEmails( 'To', $oEmailEvent->aTo ) .
			'Subject: ' . $this->encode( $oEmailEvent->sSubject ) . "\n" .
			$this->getHeaders( $oEmailEvent ) . "\n" .
			"\n" .
			$this->getBody( $oEmailEvent );
	}

	/**
	 * create file and dir for save
	 * @param dpsEmailEvent $oEmail
	 * @param int $i
	 * @return string
	 */
	protected function createFileName( dpsEmailEvent $oEmail, $i = 0 ) {

		$sFileName = date( 'Y-m-d/H-i-s_' ) . $i . '.eml';

		$sFileName = $this->sDir . DIRECTORY_SEPARATOR . $sFileName;
		if( file_exists( $sFileName ) ) {
			return $this->createFileName( $oEmail, ++$i );
		} else {
			return $sFileName;
		}
	}

	/**
	 * need run garbage collection
	 * @return bool
	 */
	protected function needGc() {
		return !$this->bGcExec && mt_rand( 1, 1000000 ) < $this->iGcProbability;
	}

	/**
	 * garbage collection
	 */
	public function gc( $sDir = null ) {

		// prepare
		if( is_null( $sDir ) ) {
			$sDir = $this->sDir;
			$bIsRootDir = true;
		}

		// time for check file old
		$iOldTime = strtotime( $this->sOldFileCriteria, $this->iTime );

		// loop by files, unlink old files and dirs
		$aFiles = glob( $sDir . DIRECTORY_SEPARATOR . '*' );
		$bLostFiles = false;
		foreach ( $aFiles as $sFile ) {
			if( $sFile == '.' || $sFile == '..' ) {
				continue;
			}
			if( is_dir( $sFile ) ) {
				$this->gc( $sFile );
				continue;
			}

			if( filemtime( $sFile ) < $iOldTime ) {
				@unlink( $sFile );
			} else {
				$bLostFiles = false;
			}
		}

		// remove root dir if empty
		if( empty( $bIsRootDir ) && !$bLostFiles ) {
			@rmdir( $sDir );
		}
	}
}