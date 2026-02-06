<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../2cnytm029mp3r/cm293uc5904uh.php");
include("../vm39845um223u/qxom385u3mfg3.php");

// validar que para esta cotizacion a convertir, hay al menos un desglose creado
if (mysqli_num_rows(mysqli_query($con,"select * from trpedidoproductostmp where idusuario='".$_SESSION["4dm1npl4y3r4sc1sn3usr"]."'"))>0) {
    echo "OK";
}


?>
