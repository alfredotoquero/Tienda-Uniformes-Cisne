<?
include("../../vm39845um223u/c91ktn24g7if5u.php");
include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// se debe comprobar que si el producto que se está agregando (o sumando), tiene existencias suficientes

$idalmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];
if ($_POST["idpartida"]>0) {
    $partida = mysqli_fetch_assoc(mysqli_query($con,"select * from trcuentaproductostmp where idtmp='".$_POST["idpartida"]."'"));
    $idproducto = $partida["idproducto"];
    $idcolor = $partida["idcolor"];
    $idtalla = $partida["idtalla"];
}else {
    $idproducto = $_POST["idproducto"];
    $idcolor = $_POST["idcolor"];
    $idtalla = $_POST["idtalla"];
}

$acumulado = mysqli_fetch_assoc(mysqli_query($con,"select sum(cantidad) as total from trcuentaproductostmp where idproducto='".$idproducto."' and idcolor='".$idcolor."' and idtalla='".$idtalla."'"))["total"];
$existencias = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$idproducto."' and idcolor='".$idcolor."' and idtalla='".$idtalla."' and idalmacen='".$idalmacen."'"))["existencia"];
$existencias = ($existencias==0 ? 0 : $existencias);

$cantidadreservada = mysqli_fetch_assoc(mysqli_query($con,"select sum(cantidad) as cantidad from tmovimientoinventarioproductos where idtipomovimiento='3' and idmovimientoinventario in (select idmovimientoinventario from tmovimientosinventario where autorizacion=0) and idalmacen='".$idalmacen."'"))["cantidad"];

$cantidad = (($_POST["accion"]=="sumar") ? $acumulado + 1 : $_POST["cantidad"]) - $cantidadreservada;

if ($cantidad>$existencias and mysqli_num_rows(mysqli_query($con,"select * from tproductos where idproducto='".$idproducto."' and tipo='S'"))==0) {
    echo "ERROR";
}else {
    echo "OK";
}
?>