<?php

/**
 * email full data structure
 */
class dpsEmailEvent {

	/**
	 * @var string
	 */
	public $sSubject;

	/**
	 * true = html, false = text
	 * @var bool
	 */
	public $bHtml = true;

	/**
	 * @var string
	 */
	public $sBody;

	/**
	 * @var string
	 */
	public $sLayout;

	/**
	 * @var string
	 */
	public $sView;

	/**
	 * @var array
	 */
	public $aViewData;

	/**
	 * array( '/path/to/file' => '' ) - embed
	 * array( '/path/to/file' => 'nameForEmail.txt' ) - attachment
	 * @var array
	 */
	public $aAttachments;

	/**
	 * @var array ( sEmail => sName )
	 */
	public $aFrom;

	/**
	 * @var array ( sEmail => sName )
	 */
	public $aTo;

	/**
	 * @var array ( sEmail => sName )
	 */
	public $aCc;

	/**
	 * @var array ( sEmail => sName )
	 */
	public $aBcc;

	/**
	 * timestamp
	 * @var int
	 */
	public $iCreateTime;
}