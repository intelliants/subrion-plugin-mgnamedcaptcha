<?php
//##copyright##

$iaCaptcha = $iaCore->factoryPlugin('mgmathcaptcha', iaCore::FRONT, 'captcha');

// output captcha
$iaCaptcha->render();
die();