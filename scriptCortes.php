<?php
$con=mysqli_connect("db.avanistudiodesign.com","playerascisneusr","pl4y3r4sc1sn3usr") or trigger_error(mysqli_error($con),E_USER_ERROR);
mysqli_select_db($con,"playerascisnedb");

$fecha = '2024-06-19 18:57:42';
$idsucursal = 9;
$idcorte = 4936;

// $query = "
// select
//     *
// from
//     tcortessucursales
// where
//     idsucursal = '".$idsucursal."' and
//     idcorte >= '".$idcorte."' and
//     status = 'T'";

$query = "
select
    *
from
    tcortessucursales
where
    idcorte = '5063'";

$result = mysqli_query($con,$query);

while($corte = mysqli_fetch_assoc($result)){
    // $query = "
    // update
    //     ttickets
    // set
    //     idcorte = '".$corte["idcorte"]."'
    // where
    //     idsucursal = '".$idsucursal."' and
    //     fecha between '".$fecha."' and '".$corte["fechafinal"]."'";
    // mysqli_query($con,$query);

    $fondoinicial = $corte["fondoinicial"];

    $ticketfinal = mysqli_fetch_assoc(mysqli_query($con, "select * from ttickets where idcorte='" . $corte["idcorte"] . "' order by idticket desc limit 1"))["folio"];
    $ticketinicial = mysqli_fetch_assoc(mysqli_query($con, "select * from ttickets where idcorte='" . $corte["idcorte"] . "' order by idticket limit 1"))["folio"];
    $devoluciones = mysqli_fetch_assoc(mysqli_query($con, "select sum(total) as devoluciones from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from tdevoluciones where idcorte='" . $corte["idcorte"] . "')"))["devoluciones"];
    $gastos = mysqli_fetch_assoc(mysqli_query($con, "select sum(monto) as gastos from tgastos where idcorte='" . $corte["idcorte"] . "'"))["gastos"];
    $gastos += $devoluciones;
    $ventas = mysqli_fetch_assoc(mysqli_query($con, "select sum(total) as ventas from ttickets where idcorte='" . $corte["idcorte"] . "'"))["ventas"];
    $fondofinal = $fondoinicial + $ventas - $gastos;

    $query = "
    delete
    from
        tcortesucursal_formaspago
    where
        idcorte = '".$corte["idcorte"]."'";
    mysqli_query($con,$query);

    $formaspago = mysqli_query($con, "select * from tcatformaspago");
    while ($row = mysqli_fetch_array($formaspago)) {
        $monto = mysqli_fetch_assoc(mysqli_query($con, "select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '" . $corte["idcorte"] . "') and idformapago='" . $row["idformapago"] . "'"))["monto"];
        mysqli_query($con, "insert into tcortesucursal_formaspago (idcorte, idformapago, total) values ('" . $corte["idcorte"] . "','" . $row["idformapago"] . "','" . (($monto > 0) ? $monto : 0) . "')");
    }

    mysqli_query($con, "update tcortessucursales set ticketinicial='" . $ticketinicial . "', ticketfinal='" . $ticketfinal . "', ventas='" . $ventas . "', devoluciones='" . $devoluciones . "', gastos='" . $gastos . "', fondofinal='" . $fondofinal . "'  where idcorte='" . $corte["idcorte"] . "'");

    $fecha = $corte["fechafinal"];
}
?>