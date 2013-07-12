<?php

/**
 * for render email views
 */
class dpsEmailController extends CController {

	/**
	 * @var string
	 */
	protected $sViewPath;

	/**
	 * @var dpsEmailEvent
	 */
	protected $oEmailEvent;

	/**
	 * @var dpsMailer
	 */
	protected $oMailer;

	/**
	 * @var bool
	 */
	protected $bRenderLayout = false;

	/**
	 * @param $sSubject
	 */
	public function setSubject( $sSubject ) {
		$this->getEmailEvent()->sSubject = $sSubject;
	}

	/**
	 * @param $sLayout
	 */
	public function setLayout( $sLayout ) {
		$this->getEmailEvent()->sLayout = $sLayout;
		$this->layout = $sLayout;
	}

	/**
	 * attach file in email
	 * @param string $sFilePath /path/to/file
	 * @param string $sFileName name for recipient
	 * @return string
	 */
	public function attach( $sFilePath, $sFileName = '' ) {
		if( empty( $sFileName ) ) {
			$sFileName = pathinfo( $sFilePath, PATHINFO_BASENAME );
		}
		$this->getEmailEvent()->aAttachments[ $sFilePath ] = $sFileName;
	}

	/**
	 * embed media in email
	 * @param string $sFilePath /path/to/mediafile
	 * @return string
	 */
	public function embed( $sFilePath ) {
		$this->getEmailEvent()->aAttachments[ $sFilePath ] = '';
		return 'cid:' . $this->getMailer()->getCid( $sFilePath );
	}

	/**
	 * @param string $sViewPath
	 * @return $this
	 */
	public function setViewPath( $sViewPath ) {
		$this->sViewPath = $sViewPath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getViewPath() {
		return $this->sViewPath;
	}

	/**
	 * @param string $sLayout
	 * @return bool|mixed|string
	 */
	public function getLayoutFile( $sLayout ) {
		if( empty( $sLayout ) ) {
			return false;
		}
		$sReturn = $this->resolveViewFile( $sLayout, $this->getViewPath(), Yii::app()->getViewPath() );
		if( $sReturn !== false ) {
			$this->bRenderLayout = true;
		}
		return $sReturn;
	}

	/**
	 * @param string $sViewFile
	 * @param array $aViewData
	 * @param bool $bReturn
	 * @return string
	 */
	public function renderFile( $sViewFile, $aViewData = null, $bReturn = false ) {

		// append email view data in layout
		if(
			$this->bRenderLayout &&
			is_array( $aViewData ) &&
			count( $aViewData ) ==  1 &&
			isset( $aViewData[ 'content' ] )
		) {
			$aAppendData = $this->getEmailEvent()->aViewData;
			if( isset( $aAppendData[ 'content' ] ) ) {
				unset( $aAppendData[ 'content' ] );
			}
			$aViewData = array_merge( $aViewData, $aAppendData );
			$this->bRenderLayout = false;
		}

		return parent::renderFile( $sViewFile, $aViewData, $bReturn );
	}

	/**
	 * @return dpsEmailEvent
	 */
	public function getEmailEvent( ){
		return $this->oEmailEvent;
	}

	/**
	 * @param dpsEmailEvent $oEmailEvent
	 * @return dpsEmailController
	 */
	public function setEmailEvent( dpsEmailEvent $oEmailEvent ){
		$this->oEmailEvent = $oEmailEvent;
		return $this;
	}

	/**
	 * @return dpsMailer
	 */
	public function getMailer( ){
		return $this->oMailer;
	}

	/**
	 * @param dpsMailer $oMailer
	 * @return dpsEmailController
	 */
	public function setMailer( dpsMailer $oMailer ){
		$this->oMailer = $oMailer;
		return $this;
	}
}