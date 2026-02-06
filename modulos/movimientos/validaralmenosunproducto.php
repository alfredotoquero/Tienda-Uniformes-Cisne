<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// validar que al menos un producto haya sido agregado y que las cantidades de los productos agregados esten en los limites de sus respectivas existencias
$error = 0;

$partidas = mysqli_query($con,"select * from tmovimientoinventarioproductostmp where idusuario='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'");
if (mysqli_num_rows($partidas)==0) {
    $error = 1;
}

while($partida = mysqli_fetch_assoc($partidas)){
    // si hay al menos un registro que su cantidad exceden las existencias, marcar error
    if (mysqli_num_rows(mysqli_query($con,"select * from tproductoexistencias where idalmacen='".$_POST["idalmacen"]."' and idproducto='".$partida["idproducto"]."' and idtalla='".$partida["idtalla"]."' and idcolor='".$partida["idcolor"]."' and existencia<'".$partida["cantidad"]."'"))>0) {
        $error = 2;
        break;
    }
}


if ($error==0) {
    echo "OK";
}else if ($error==1) {
    echo "ERROR1";
}else {
    echo "ERROR2";
}
?>