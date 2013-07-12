<?php
/**
 * @var dpsEmailController $this
 * @var string $sUsername имя пользователя
 */

$this->getEmailEvent()->sSubject = 'Для ' . $sUsername;
?>
Привет <strong><?= $sUsername ?></strong>!