<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../2cnytm029mp3r/cm293uc5904uh.php");
include("../vm39845um223u/qxom385u3mfg3.php");


// validar que la cantidad no se excede de lo que le resta a la partida
$total = 0;
$partida = mysqli_fetch_assoc(mysqli_query($con,"select * from trcotizacionproductostmp where idtmp='".$_POST["idpartida"]."'"));

// se consiguen todas las cantidades de los desgloses anteriores de esta partida
$desgloses = mysqli_query($con,"select * from trpedidoproductostmp where idcotizacionproducto='".$partida["idtmp"]."'");
while ($desglose = mysqli_fetch_assoc($desgloses)) {
    $total += intval($desglose["cantidad"]);
}

// si total + la cantidad que quieres introducir es mayor que la cantidad de la partida, entonces no es una cantidad valida
if ($total+intval($_POST["cantidad"])>intval($partida["cantidad"])) {
    echo "Total: " . $total . " cantidad: " . $_POST["cantidad"] . " partida: " . $partida["cantidad"];
} else {
    echo "OK";
}

?>
