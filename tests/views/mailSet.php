<?php
/**
 * @var dpsEmailController $this
 * @var string $sUsername
 */

$this->getEmailEvent()->sSubject = 'subject_tpl';
$this->getEmailEvent()->bHtml = false;
$this->getEmailEvent()->sLayout = 'layouts/mainAlt';

$this->getEmailEvent()->aFrom = array( 'from_tpl@example.com' => 'from_tpl' );
$this->getEmailEvent()->aTo = array( 'to_tpl@example.com' => 'to_tpl' );
$this->getEmailEvent()->aCc = array( 'cc_tpl@example.com' => 'cc_tpl' );
$this->getEmailEvent()->aBcc = array( 'bcc_tpl@example.com' => 'bcc_tpl' );


?>
Привет <?= $sUsername ?>!