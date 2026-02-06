<?
include_once($_SERVER["DOCUMENT_ROOT"]."/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/2cnytm029mp3r/cm293uc5904uh.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/vm39845um223u/qxom385u3mfg3.php");

$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$corte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idsucursal='".$vendedor["idsucursal"]."' and status = 'A' order by idcorte desc limit 1"));
$fondoinicial = $corte["fondoinicial"];
$devoluciones = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as devoluciones from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from tdevoluciones where idcorte='".$corte["idcorte"]."')"))["devoluciones"];
$ventas = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as ventas from ttickets where idcorte='".$corte["idcorte"]."'"))["ventas"];

$totalFinal = $fondoinicial + $ventas - $devoluciones;
echo $fondoinicial."|".$ventas."|".$devoluciones."|".($fondoinicial + $ventas - $devoluciones);
?>