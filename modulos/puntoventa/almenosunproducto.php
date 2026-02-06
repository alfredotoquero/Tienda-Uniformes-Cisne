<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// validar que al menos un producto ha sido agregado 
// if (mysqli_num_rows(mysqli_query($con,"select * from trticketproductostmp where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))>0) {
//     echo "OK";
// }else {
//     echo "NO";
// }
if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductostmp where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))>0) {
    echo "OK";
}else {
    echo "NO";
}

?>