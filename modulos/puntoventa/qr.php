<?php
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include($_SERVER["DOCUMENT_ROOT"]."/2cnytm029mp3r/cm293uc5904uh.php");
include($_SERVER["DOCUMENT_ROOT"]."/vm39845um223u/qxom385u3mfg3.php");

include($_SERVER["DOCUMENT_ROOT"].'/assets/php/libs/phpqrcode/qrlib.php');
include($_SERVER["DOCUMENT_ROOT"].'/assets/php/libs/phpqrcode/qrconfig.php');

QRcode::png($_GET["url"],false,3,6,1);
?>