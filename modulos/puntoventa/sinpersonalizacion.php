<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// Si todos los productos son sin personalizacion, se envia un OK
// if (mysqli_num_rows(mysqli_query($con,"select * from trticketproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and tienepersonalizacion=1"))>0) {
//     echo "ERROR";
// }
// if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and tienepersonalizacion=1"))>0) {
//     echo "ERROR";
// }
if (mysqli_num_rows(mysqli_query($con,"select * from trcuentaproductopersonalizadostmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))>0) {
    echo "ERROR";
}

echo "OK";
?>