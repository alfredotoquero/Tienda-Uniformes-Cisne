<?php
$con=mysqli_connect("db.avanistudiodesign.com","playerascisneusr","pl4y3r4sc1sn3usr") or trigger_error(mysqli_error($con),E_USER_ERROR);
mysqli_select_db($con,"playerascisnedb");

$cortes = mysqli_query($con, "select * from tcortessucursales where status = 'T'");
foreach($cortes as $corte) {
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','1','".$corte["arqueo_efectivo"]."')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','2','".$corte["arqueo_efectivousd"]."')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','3','".$corte["arqueo_tarjeta"]."')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','4','0')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','5','".$corte["arqueo_transferencia"]."')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','6','".$corte["arqueo_cheque"]."')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','7','0')");
    mysqli_query($con,"insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('".$corte["idcorte"]."','8','0')");
    echo "Corte ".$corte["idcorte"]." done. <br>";
}
echo "<br><br>Done.";
?>
