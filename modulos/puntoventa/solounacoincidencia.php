<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

$idsucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))["idsucursal"];
$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal = '".$idsucursal."'"))["idalmacen"];

// conseguir todos los productos para tienda (tproductos -> tienda = 1)
$productos = mysqli_query($con,"call pbusquedacodigobarras(".$idalmacen.",'".$_POST["producto"]."')");

if (mysqli_num_rows($productos)==1) {
    $producto = mysqli_fetch_assoc($productos);
    echo "OK|" . $producto["idproducto"];
}

?>