<?php
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$corte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idsucursal='".$vendedor["idsucursal"]."' and status='A'"));

if(mysqli_query($con,"insert into tgastos (idvendedor,idcorte,monto,motivo) values ('".$vendedor["idvendedor"]."','".$corte["idcorte"]."','".$_POST["monto"]."','".$_POST["motivo"]."')")){
    echo "OK";
}else{
    echo "ERROR";
}
?>