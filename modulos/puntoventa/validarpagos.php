<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

$error = 0;

// si los pagos de tarjeta exceden el total a pagar, se manda error
// si hay al menos un pago agregado que esté de más, se manda error
$pagos = mysqli_query($con,"select * from tformaspagotickettmp where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' order by idformapago desc, monto desc");
$total = $_POST["total"];
$acumulado = 0;
while($pago = mysqli_fetch_assoc($pagos)){
    $valorpago = $pago["monto"]*mysqli_fetch_assoc(mysqli_query($con,"select * from tcatformaspago where idformapago='".$pago["idformapago"]."'"))["pesos"];
    $acumulado += $valorpago;
    if ($acumulado>$total) {
        if ($pago["idformapago"]==3) { //tarjeta
            $error = 1;
        } else {
            // if ($numpagoactual<$numpagos or $acumulado-$valorpago>=$total) {
            if ($acumulado-$valorpago>=$total) {
                $error = 2;
                break;
            }
        }
    }
}


if ($error==1) {//los pagos de tarjeta exceden el total a pagar
    echo "ERROR1";
} else if ($error==2){//hay pagos de más
    echo "ERROR2";
}else{
    echo "OK";
}
?>