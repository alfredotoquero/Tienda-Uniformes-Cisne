<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];

// conseguir todos los productos para tienda (tproductos -> tienda = 1)
// $productos = mysqli_query($con,"select * from vproductostienda where nombre like '%".$_REQUEST["term"]."%'");
$productos = mysqli_query($con,"call pproductostienda(".$idalmacen.",'".$_REQUEST["term"]."')");

$json = array();

while($producto = mysqli_fetch_assoc($productos)) {
    array_push($json, ["value"=>$producto['nombre'],"id"=>$producto["idproducto"],"label"=>$producto['nombre'] . " (" . $producto["codigobarras"] . ")"]);
}

echo json_encode($json);

?>