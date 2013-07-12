<?php

/**
 * CConsoleApplication behavior for work render logic
 * @method CApplication getOwner()
 */
class dpsRenderConsoleBehavior extends CBehavior {

	/**
	 * copy paste from CWebApplication
	 * @return IApplicationComponent
	 */
	public function getViewRenderer() {
		return $this->getOwner()->getComponent( 'viewRenderer' );
	}

	/**
	 * copy paste from CWebApplication
	 * @return CClientScript the client script manager
	 */
	public function getClientScript() {
		return $this->getOwner()->getComponent( 'clientScript' );
	}

	/**
	 * @return null
	 */
	public function getTheme() {
		return null;
	}

	/**
	 * @return string
	 */
	public function getViewPath() {
		return $this->getOwner()->getBasePath() . '/views';
	}

	/**
	 * @return string
	 */
	public function getLayoutPath() {
		return $this->getViewPath() . '/layouts';
	}
}
