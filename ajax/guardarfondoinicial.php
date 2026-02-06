<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../2cnytm029mp3r/cm293uc5904uh.php");
include("../vm39845um223u/qxom385u3mfg3.php");

$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
// guardar fondo inicial
// recuperar el folio de la sucursal del vendedor
$folio = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$vendedor["idsucursal"]."'"))["foliocorte"];
// idsucursal,idvendedor,fechainicial,fondoinicial,status
mysqli_query($con,"insert into tcortessucursales (idsucursal,folio,idvendedor,fechainicial,fondoinicial,status) values ('".$vendedor["idsucursal"]."','".($folio+1)."','".$vendedor["idvendedor"]."','".$_POST["fecha"]."','".$_POST["fondoinicial"]."','A')");
$idcorte = mysqli_insert_id($con);

// actualizar el folio de la sucursal
mysqli_query($con,"update tsucursales set foliocorte = foliocorte+1 where idsucursal='".$vendedor["idsucursal"]."'");


if ($idcorte>0) {
    echo "OK";
}
?>