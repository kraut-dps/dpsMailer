<?php
/**
 * @var dpsEmailController $this
 * @var string $sUsername
 */

$this->getEmailEvent()->sSubject = 'Для ' . $sUsername;
?>
Привет <strong><?= $sUsername ?>!</strong>