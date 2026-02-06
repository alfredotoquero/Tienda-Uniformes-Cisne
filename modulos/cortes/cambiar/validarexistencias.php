<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../../vm39845um223u/qxom385u3mfg3.php");

// se debe comprobar que si el producto que se está agregando (o sumando), tiene existencias suficientes

$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];
$idproducto = $_POST["idproducto"];
$idcolor = $_POST["idcolor"];
$idtalla = $_POST["idtalla"];

$existencias = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$idproducto."' and idcolor='".$idcolor."' and idtalla='".$idtalla."' and idalmacen='".$idalmacen."'"))["existencia"];
$existencias = ($existencias==0 ? 0 : $existencias);

$cantidad = $_POST["cantidad"];

if ($cantidad>$existencias and mysqli_num_rows(mysqli_query($con,"select * from tproductos where idproducto='".$idproducto."' and tipo='S'"))==0) {
    echo "ERROR";
}else {
    echo "OK";
}
?>