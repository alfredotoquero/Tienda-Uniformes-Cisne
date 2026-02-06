<?php
$con=mysqli_connect("db.avanistudiodesign.com","playerascisneusr","pl4y3r4sc1sn3usr") or trigger_error(mysqli_error($con),E_USER_ERROR);
mysqli_select_db($con,"playerascisnedb");

$cortes = mysqli_query($con, "select * from tcortessucursales where status = 'T'");
foreach($cortes as $corte) {
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,1,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=1");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,2,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=2");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,3,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=3");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,4,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=4");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,5,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=5");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,6,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=6");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,7,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=7");
    mysqli_query($con,"insert into tcortesucursal_formaspago (idcorte, idformapago, total) select c.idcorte,8,sum(monto*pesos) from tformaspagoticket a left join tcatformaspago b on a.idformapago = b.idformapago left join ttickets c on a.idticket = c.idticket where c.idcorte = '".$corte["idcorte"]."' and a.idformapago=8");
    echo "Corte ".$corte["idcorte"]." done. <br>";
}
echo "<br><br>Done.";
?>
