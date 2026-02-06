<?

$tipocambiousd = mysqli_fetch_assoc(mysqli_query($con, "select * from tcatformaspago where idformapago=2"))["pesos"];
// $vendedor = mysqli_fetch_assoc(mysqli_query($con, "select * from tvendedores where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'"));

if ($_POST["accion"] == "cobrar") {
    if (mysqli_num_rows(mysqli_query($con, "select * from trcuentaproductostmp where idvendedor='" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'")) > 0) {

        $fecha = date("Y-m-d H:i:s");

        $idcorte = mysqli_fetch_assoc(mysqli_query($con, "select * from tcortessucursales where idsucursal='" . $vendedor["idsucursal"] . "' and status='A' order by idcorte desc limit 1"))["idcorte"];

        // obtener el folio de la tabla de sucursales, aumentarle 1 y actualizarlo en la tabla de sucursales
        $folio = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["folio"];
        mysqli_query($con, "update tsucursales set folio=folio+1 where idsucursal='" . $vendedor["idsucursal"] . "'");

        // tcuentas 
        mysqli_query($con, "insert into tcuentas (idsucursal,idcorte,idvendedor,tipocuenta,descuento,subtotal,iva,total,abonado,fecha,status) values ('" . $vendedor["idsucursal"] . "','" . $idcorte . "','" . $vendedor["idvendedor"] . "','D','" . $_POST["txtDescuento"] . "','" . $_POST["subtotal"] . "','" . $_POST["iva"] . "','" . $_POST["total"] . "','" . $_POST["total"] . "','" . $fecha . "','E')");
        $idcuenta = mysqli_insert_id($con);
        // ttickets
        if (mysqli_query($con, "insert into ttickets (idcuenta,idsucursal,idcorte,idvendedor,folio,descuento,subtotal,iva,total,fecha,status) values ('" . $idcuenta . "','" . $vendedor["idsucursal"] . "','" . $idcorte . "','" . $vendedor["idvendedor"] . "','" . ($folio) . "','" . $_POST["txtDescuento"] . "','" . $_POST["subtotal"] . "','" . $_POST["iva"] . "','" . $_POST["total"] . "','" . $fecha . "','A')")) {
            $cuentacobrada = true;

            $idticket = mysqli_insert_id($con);

?>
            <script>
                $(document).ready(function(e) {
                    imprimirTicket(<?php echo $idticket; ?>, <?php echo $_POST["cambio"]; ?>, 1);
                });
            </script>
            <?

            $idalmacen = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["idalmacen"];
            // trcuentaproductos
            // $cuentaproductos = mysqli_query($con,"select * from trcuentaproductostmp where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idproducto in (select idproducto from tproductos where tipo!='S')");
            $cuentaproductos = mysqli_query($con, "select * from trcuentaproductostmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'");
            while ($cuentaproducto = mysqli_fetch_assoc($cuentaproductos)) {
                $personalizaciones = mysqli_query($con, "select * from trcuentaproductopersonalizadostmp where idcuentaproductotmp='" . $cuentaproducto["idtmp"] . "'");
                $conpersonalizacion = (mysqli_num_rows($personalizaciones) > 0 ? true : false);

                // añadir el producto
                mysqli_query($con, "insert into trcuentaproductos (idcuenta,idproducto,idcolor,idtalla,cantidad,idpersonalizacion,personalizacion,descuento,subtotal,iva,total,status) values ('" . $idcuenta . "','" . $cuentaproducto["idproducto"] . "','" . $cuentaproducto["idcolor"] . "','" . $cuentaproducto["idtalla"] . "','" . $cuentaproducto["cantidad"] . "','" . $cuentaproducto["idpersonalizacion"] . "','" . $cuentaproducto["personalizacion"] . "','" . $cuentaproducto["descuento"] . "','" . $cuentaproducto["subtotal"] . "','" . $cuentaproducto["iva"] . "','" . $cuentaproducto["total"] . "','" . ($conpersonalizacion == 0 ? "E" : "P") . "')");
                $idcuentaproducto = mysqli_insert_id($con);

                // añadir todas las personalizaciones de la partida a la tabla permanente
                while ($personalizacion = mysqli_fetch_assoc($personalizaciones)) {
                    mysqli_query($con, "insert into trcuentaproductopersonalizados (idcuentaproducto,idpersonalizacion,personalizacion) values ('" . $idcuentaproducto . "','" . $personalizacion["idpersonalizacion"] . "','" . $personalizacion["personalizacion"] . "')");
                    $conpersonalizacion = true;
                }

                // descontar las existencias del producto con idcolor y talla seleccionados
                mysqli_query($con, "update tproductoexistencias set existencia=existencia-" . $cuentaproducto["cantidad"] . " where idproducto='" . $cuentaproducto["idproducto"] . "' and idcolor='" . $cuentaproducto["idcolor"] . "' and idtalla='" . $cuentaproducto["idtalla"] . "' and idalmacen='" . $idalmacen . "'");

                // agregar accion a tproductomovimientos
                $existencias = mysqli_fetch_assoc(mysqli_query($con, "select sum(existencia) as existencia from tproductoexistencias where idproducto='" . $cuentaproducto["idproducto"] . "' and idcolor='" . $cuentaproducto["idcolor"] . "' and idtalla='" . $cuentaproducto["idtalla"] . "'"))["existencia"];
                $existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con, "select sum(existencia) as existencia from tproductoexistencias where idproducto='" . $cuentaproducto["idproducto"] . "' and idcolor='" . $cuentaproducto["idcolor"] . "' and idtalla='" . $cuentaproducto["idtalla"] . "' and idalmacen='" . $idalmacen . "'"))["existencia"];
                mysqli_query($con, "insert into tproductomovimientos (idusuario,idproducto,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('" . $vendedor["idvendedor"] . "','" . $cuentaproducto["idproducto"] . "','" . $cuentaproducto["idtalla"] . "','" . $cuentaproducto["idcolor"] . "','" . $idalmacen . "','V','S','" . $idticket . "','" . $cuentaproducto["cantidad"] . "','" . $existencias . "','" . $existenciasalmacen . "','" . $fecha . "')");

                if ($existenciasalmacen < 0) {
                    $p = mysqli_fetch_assoc(mysqli_query($con, "select * from tproductos where idproducto = '" . $cuentaproducto["idproducto"] . "'"));
                    mysqli_query($con, "insert into tsolicitudescompra (idproveedor,idproducto,idtalla,idcolor,cantidad,fecha,notas) values ('" . $p["idproveedor"] . "','" . $p["idproducto"] . "','" . $cuentaproducto["idtalla"] . "','" . $cuentaproducto["idcolor"] . "','" . $cuentaproducto["cantidad"] . "','" . $fecha . "','Venta sin existencia Ticket #" . $folio . "')");
                }

                $query = "
                select
                    *
                from
                    ttarjetasregalo
                where
                    codigo = '".$cuentaproducto["codigo"]."'
                ";

                $query2 = "
                select
                    *
                from
                    ttarjetasregalo
                where
                    codigo = '".$cuentaproducto["codigo"]."'
                    and activa = 0
                ";

                if ($cuentaproducto["codigo"] != "" && mysqli_num_rows(mysqli_query($con,$query))==0) {
                    $query = "
                    insert
                    into
                        ttarjetasregalo
                    (
                        codigo,
                        activa,
                        vigencia
                    )
                    values
                    (
                        '" . $cuentaproducto["codigo"] . "',
                        '1',
                        '" . date("Y-m-d", strtotime("+1 year")) . "'
                    )";
                    mysqli_query($con, $query);
                } else if ($cuentaproducto["codigo"] != "" && mysqli_num_rows(mysqli_query($con,$query2))==1) {
                    $query = "
                    update
                        ttarjetasregalo
                    set
                        activa = 1,
                        vigencia = '".date("Y-m-d", strtotime("+1 year"))."'
                    where
                        codigo = '".$cuentaproducto["codigo"]."'
                    ";
                }
            }

            $i = 1;
            $acumulado = 0;
            // pasar los pagos de la tabla de pagos temporal a la permanente (tformaspagoticket)
            $pagostmp = mysqli_query($con, "select archivo,idformapago,sum(monto) as monto from tformaspagotickettmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "' group by idformapago order by idformapago desc");
            while ($pagotmp = mysqli_fetch_assoc($pagostmp)) {
                $pesos = mysqli_fetch_assoc(mysqli_query($con, "select * from tcatformaspago where idformapago='" . $pagotmp["idformapago"] . "'"))["pesos"];
                // si el acumulado + el monto supera la cantidad a cobrar (en este caso, el total), se reducirá el monto. NOTA: Si esto pasa, siempre será en la ultima iteración del ciclo
                $acumulado += $pagotmp["monto"] * $pesos;
                $monto = $pagotmp["monto"];
                if ($acumulado > $_POST["total"]) {
                    $monto -= ($acumulado - $_POST["total"]) / $pesos;
                }

                mysqli_query($con, "insert into tformaspagoticket (idticket,idvendedor,idformapago,monto,montorecibido,archivo) values ('" . $idticket . "','" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "','" . $pagotmp["idformapago"] . "','" . $monto . "','" . $pagotmp["monto"] . "','".$pagotmp["archivo"]."')");
            }

            // si hay archivos de deposito, cheque o transferencia, crear la carpeta y subirlos
            $cantidadarchivos = mysqli_fetch_assoc(mysqli_query($con, "select count(*) as total from tformaspagotickettmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "' and archivo!='' order by idformapago desc"))["total"];
            if ($cantidadarchivos > 0) {
                $rutabase = $_SERVER["DOCUMENT_ROOT"]."/imagenes/depositos/" . $idticket . "/";

                mkdir($rutabase, 0777, true);

                for ($i = 1; $i <= $cantidadarchivos; $i++) {

                    $direccion = $rutabase . basename($_FILES["flComprobante" . $i]["name"]);

                    move_uploaded_file($_FILES['flComprobante' . $i]['tmp_name'], $direccion);

                }
            }

            $pagostmp = mysqli_query($con, "select * from tformaspagotickettmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "' and codigo != '' order by idformapago desc");
            while ($pagotmp = mysqli_fetch_assoc($pagostmp)) {
                $query = "
                update
                    ttarjetasregalo
                set
                    activa = 0,
                    fecha_ultimo_uso = '".$fecha."'
                where
                    codigo = '" . $pagotmp["codigo"] . "'";
                mysqli_query($con, $query);
            }
        } else {
            $cuentacobrada = false;

            mysqli_query($con, "update tsucursales set folio=folio-1 where idsucursal='" . $vendedor["idsucursal"] . "'");

            mysqli_query($con, "delete from tcuentas where idcuenta = '" . $idcuenta . "'");
        }
    }
}

if ($_POST["accion"] == "apartar") {
    if (mysqli_num_rows(mysqli_query($con, "select * from trcuentaproductostmp where idvendedor='" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'")) > 0) {

        $idcorte = mysqli_fetch_assoc(mysqli_query($con, "select * from tcortessucursales where idsucursal='" . $vendedor["idsucursal"] . "' and status='A'"))["idcorte"];

        // obtener el folio de la tabla de sucursales, aumentarle 1 y actualizarlo en la tabla de sucursales
        $folio = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["folio"];
        mysqli_query($con, "update tsucursales set folio=folio+1 where idsucursal='" . $vendedor["idsucursal"] . "'");

        // tcuentas
        mysqli_query($con, "insert into tcuentas (idsucursal,idcorte,idvendedor,tipocuenta,nombrecuenta,descuento,subtotal,iva,total,abonado,fecha,status) values ('" . $vendedor["idsucursal"] . "','" . $idcorte . "','" . $vendedor["idvendedor"] . "','A','" . $_POST["nombreapartado"] . "','" . $_POST["txtDescuento"] . "','" . $_POST["subtotal"] . "','" . $_POST["iva"] . "','" . $_POST["total"] . "','" . $_POST["abono"] . "','" . date("Y-m-d H:i:s") . "','P')");
        $idcuenta = mysqli_insert_id($con);
        // ttickets
        $iva = $_POST["abono"] * 0.08;
        $subtotal = $_POST["abono"] - $iva;
        if (mysqli_query($con, "insert into ttickets (idcuenta,idsucursal,idcorte,idvendedor,folio,descuento,subtotal,iva,total,fecha,status,notas) values ('" . $idcuenta . "','" . $vendedor["idsucursal"] . "','" . $idcorte . "','" . $vendedor["idvendedor"] . "','" . ($folio) . "','" . $_POST["txtDescuento"] . "','" . $subtotal . "','" . $iva . "','" . $_POST["abono"] . "','" . date("Y-m-d H:i:s") . "','A','" . $_POST["notas"] . "')")) {

            $cuentaapartada = true;

            $idticket = mysqli_insert_id($con);

            // // subir el archivo del deposito 
            // if ($_FILES['flComprobante']["tmp_name"] != NULL) {

            //     $direccion = $_SERVER["DOCUMENT_ROOT"]."/imagenes/depositos/" . $idticket . "/";

            //     mkdir($direccion, 0777, true);

            //     $direccion .= basename($_FILES["flComprobante"]["name"]);

            //     move_uploaded_file($_FILES['flComprobante']['tmp_name'], $direccion);

            // }

            ?>
            <script>
                $(document).ready(function(e) {
                    imprimirTicket(<?php echo $idticket; ?>, <?php echo $_POST["cambio"]; ?>, 2, 2);
                });
            </script>
    <?

            $idalmacen = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["idalmacen"];
            // trcuentaproductos
            // $cuentaproductos = mysqli_query($con,"select * from trcuentaproductostmp where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and idproducto in (select idproducto from tproductos where tipo!='S')");
            $cuentaproductos = mysqli_query($con, "select * from trcuentaproductostmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'");
            while ($cuentaproducto = mysqli_fetch_assoc($cuentaproductos)) {
                mysqli_query($con, "insert into trcuentaproductos (idcuenta,idproducto,idcolor,idtalla,cantidad,idpersonalizacion,personalizacion,descuento,subtotal,iva,total,status) values ('" . $idcuenta . "','" . $cuentaproducto["idproducto"] . "','" . $cuentaproducto["idcolor"] . "','" . $cuentaproducto["idtalla"] . "','" . $cuentaproducto["cantidad"] . "','" . $cuentaproducto["idpersonalizacion"] . "','" . $cuentaproducto["personalizacion"] . "','" . $cuentaproducto["descuento"] . "','" . $cuentaproducto["subtotal"] . "','" . $cuentaproducto["iva"] . "','" . $cuentaproducto["total"] . "','P')");
                // descontar las existencias del producto con idcolor y talla seleccionados
                if (mysqli_num_rows(mysqli_query($con, "select * from tproductos where idproducto='" . $cuentaproducto["idproducto"] . "' and tipo!='S'"))) {
                    mysqli_query($con, "update tproductoexistencias set existencia=existencia-" . $cuentaproducto["cantidad"] . " where idproducto='" . $cuentaproducto["idproducto"] . "' and idcolor='" . $cuentaproducto["idcolor"] . "' and idtalla='" . $cuentaproducto["idtalla"] . "' and idalmacen='" . $idalmacen . "'");

                    // agregar accion a tproductomovimientos
                    $existencias = mysqli_fetch_assoc(mysqli_query($con, "select sum(existencia) as existencia from tproductoexistencias where idproducto='" . $cuentaproducto["idproducto"] . "' and idcolor='" . $cuentaproducto["idcolor"] . "' and idtalla='" . $cuentaproducto["idtalla"] . "'"))["existencia"];
                    $existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con, "select sum(existencia) as existencia from tproductoexistencias where idproducto='" . $cuentaproducto["idproducto"] . "' and idcolor='" . $cuentaproducto["idcolor"] . "' and idtalla='" . $cuentaproducto["idtalla"] . "' and idalmacen='" . $idalmacen . "'"))["existencia"];
                    mysqli_query($con, "insert into tproductomovimientos (idusuario,idproducto,idtalla,idcolor,idalmacen,origenmovimiento,tipomovimiento,idmovimiento,cantidad,existencias,existenciasalmacen,fecha) values ('" . $vendedor["idvendedor"] . "','" . $cuentaproducto["idproducto"] . "','" . $cuentaproducto["idtalla"] . "','" . $cuentaproducto["idcolor"] . "','" . $idalmacen . "','A','S','" . $idticket . "','" . $cuentaproducto["cantidad"] . "','" . $existencias . "','" . $existenciasalmacen . "','" . date("Y-m-d H:i:s") . "')");
                }
            }

            $acumulado = 0;
            // pasar los pagos de la tabla de pagos temporal a la permanente (tformaspagoticket)
            $pagostmp = mysqli_query($con, "select idformapago,sum(monto) as monto,archivo from tformaspagotickettmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "' group by idformapago order by idformapago desc");
            while ($pagotmp = mysqli_fetch_assoc($pagostmp)) {
                $pesos = mysqli_fetch_assoc(mysqli_query($con, "select * from tcatformaspago where idformapago='" . $pagotmp["idformapago"] . "'"))["pesos"];
                // si el acumulado + el monto supera la cantidad a cobrar (en este caso, el abono), se reducirá el monto. NOTA: Si esto pasa, siempre será en la ultima iteración del ciclo
                $acumulado += $pagotmp["monto"] * $pesos;
                $monto = $pagotmp["monto"];
                if ($acumulado > $_POST["abono"]) {
                    $monto -= ($acumulado - $_POST["abono"]) / $pesos;
                }

                mysqli_query($con, "insert into tformaspagoticket (idticket,idvendedor,idformapago,monto,montorecibido,archivo) values ('" . $idticket . "','" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "','" . $pagotmp["idformapago"] . "','" . $monto . "','" . $pagotmp["monto"] . "','" . $pagotmp["archivo"] . "')");
            }

            // si hay archivos de deposito, cheque o transferencia, crear la carpeta y subirlos
            $cantidadarchivos = mysqli_fetch_assoc(mysqli_query($con, "select count(*) as total from tformaspagotickettmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "' and archivo!='' order by idformapago desc"))["total"];
            if ($cantidadarchivos > 0) {
                $rutabase = $_SERVER["DOCUMENT_ROOT"]."/imagenes/depositos/" . $idticket . "/";

                mkdir($rutabase, 0777, true);

                for ($i = 1; $i <= $cantidadarchivos; $i++) {

                    $direccion = $rutabase . basename($_FILES["flComprobante" . $i]["name"]);

                    move_uploaded_file($_FILES['flComprobante' . $i]['tmp_name'], $direccion);

                }
            }
        } else {
            $cuentaapartada = false;

            mysqli_query($con, "update tsucursales set folio=folio-1 where idsucursal='" . $vendedor["idsucursal"] . "'");

            mysqli_query($con, "delete from tcuentas where idcuenta = '" . $idcuenta . "'");
        }
    }
}

if ($_POST["accion"] == "corte") {
    // llenar la tabla tcortes con los datos mostrados en el swal del corte
    $corte = mysqli_fetch_assoc(mysqli_query($con, "select * from tcortessucursales where idsucursal='" . $vendedor["idsucursal"] . "' and status = 'A' order by idcorte desc limit 1"));
    $fondoinicial = $corte["fondoinicial"];
    $feria = $_POST["feria"];

    // $arqueo_tarjeta = $_POST["tarjeta"];
    // $arqueo_efectivodlls = $_POST["efectivodlls"];
    // $arqueo_efectivo = $_POST["efectivo"];
    // $arqueo_cheque = $_POST["cheque"];
    // $arqueo_transferencia = $_POST["transferencia"];

    $ticketfinal = mysqli_fetch_assoc(mysqli_query($con, "select * from ttickets where idcorte='" . $corte["idcorte"] . "' order by idticket desc limit 1"))["folio"];
    $ticketinicial = mysqli_fetch_assoc(mysqli_query($con, "select * from ttickets where idcorte='" . $corte["idcorte"] . "' order by idticket limit 1"))["folio"];
    $devoluciones = mysqli_fetch_assoc(mysqli_query($con, "select sum(total) as devoluciones from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from tdevoluciones where idcorte='" . $corte["idcorte"] . "')"))["devoluciones"];
    $gastos = mysqli_fetch_assoc(mysqli_query($con, "select sum(monto) as gastos from tgastos where idcorte='" . $corte["idcorte"] . "'"))["gastos"];
    $gastos += $devoluciones;
    $ventas = mysqli_fetch_assoc(mysqli_query($con, "select sum(total) as ventas from ttickets where idcorte='" . $corte["idcorte"] . "'"))["ventas"];
    $fondofinal = $fondoinicial + $ventas - $gastos;

    // gastos 
    // $efectivo = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=1"))["monto"];
    // $efectivousd = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=2"))["monto"];
    // $tarjeta = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=3"))["monto"];
    // $transferencia = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=5"))["monto"];
    // $cheque = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=6"))["monto"];

    //formas de pago
    $formaspago = mysqli_query($con, "select * from tcatformaspago");
    while ($row = mysqli_fetch_array($formaspago)) {
        $monto = mysqli_fetch_assoc(mysqli_query($con, "select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '" . $corte["idcorte"] . "') and idformapago='" . $row["idformapago"] . "'"))["monto"];
        mysqli_query($con, "insert into tcortesucursal_formaspago (idcorte, idformapago, total) values ('" . $corte["idcorte"] . "','" . $row["idformapago"] . "','" . (($monto > 0) ? $monto : 0) . "')");
    }

    $formaspago = mysqli_query($con, "select * from tcatformaspago");
    while ($row = mysqli_fetch_array($formaspago)) {
        $monto = $_POST["formapago".$row["idformapago"]];
        mysqli_query($con, "insert into tcortesucursal_formaspago_arqueo (idcorte, idformapago, total) values ('" . $corte["idcorte"] . "','" . $row["idformapago"] . "','" . (($monto > 0) ? $monto : 0) . "')");
    }

    // $gastos = 
    mysqli_query($con, "update tcortessucursales set ticketinicial='" . $ticketinicial . "', ticketfinal='" . $ticketfinal . "', ventas='" . $ventas . "', devoluciones='" . $devoluciones . "', gastos='" . $gastos . "', feria = '" . $feria . "', fondofinal='" . $fondofinal . "', tipocambio = '" . $tipocambiousd . "', fechafinal = '" . date("Y-m-d H:i:s") . "', status='T'  where idcorte='" . $corte["idcorte"] . "'");
    ?>
    <script>
        $(document).ready(function(e) {
            imprimirCorte(<?php echo $corte["idcorte"]; ?>);
        });
    </script>
<?
}

if ($_POST["accion"] == "guardarfondoinicial") {
    if ($_POST["jl2041xm398"] == $_SESSION["authToken"]) {
        // guardar fondo inicial
        // recuperar el folio de la sucursal del vendedor
        $folio = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["foliocorte"];
        // idsucursal,idvendedor,fechainicial,fondoinicial,status
        mysqli_query($con, "insert into tcortessucursales (idsucursal,folio,idvendedor,fechainicial,fondoinicial,status) values ('" . $vendedor["idsucursal"] . "','" . ($folio + 1) . "','" . $vendedor["idvendedor"] . "','" . $_POST["txtFecha"] . "','" . $_POST["txtFondoInicial"] . "','A')");
        $idcorte = mysqli_insert_id($con);

        // actualizar el folio de la sucursal
        mysqli_query($con, "update tsucursales set foliocorte = foliocorte+1 where idsucursal='" . $vendedor["idsucursal"] . "'");


        // if ($idcorte>0) {
        //     echo "OK";
        // }
    }
}


// se reinicia el descuento
$_SESSION["v3nd3d0rpl4y3r4spvc1sn3descuento"] = 0;

mysqli_query($con, "delete from trcuentaproductostmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'");
mysqli_query($con, "delete from trcuentaproductopersonalizadostmp where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'");

$corte = mysqli_query($con, "select * from tcortessucursales where idsucursal='" . $vendedor["idsucursal"] . "' and status = 'A' order by idcorte desc limit 1");
$descuento = mysqli_fetch_assoc(mysqli_query($con, "select * from tvendedores where idvendedor='" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'"))["descuento"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));

?>

<script>
    function asignarCantidad(idpartida, cantidad, accion) {
        if (validarExistencias(0, 0, 0, idpartida, cantidad, accion)) {
            $.ajax({
                type: "POST",
                url: "/modulos/puntoventa/listaProductos.php",
                data: "accion=" + accion + "&idpartida=" + idpartida + "&cantidad=" + cantidad,
                success: function(data) {
                    $("#listaProductos").html(data);
                }
            });
        }
    }

    function restarProducto(idpartida) {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaProductos.php",
            data: "accion=restar&idpartida=" + idpartida,
            success: function(data) {
                $("#listaProductos").html(data);
            }
        });
    }

    function limpiar() {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/almenosunproducto.php",
            success: function(data) {
                if (data == "OK") {
                    Swal.fire({
                        title: '¿Estás seguro de que quieres eliminar los productos?',
                        type: 'warning',
                        inputAttributes: {
                            autocapitalize: 'off'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No',
                        closeOnConfirm: false,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                type: "POST",
                                url: "/modulos/puntoventa/listaProductos.php",
                                data: "accion=eliminarproductos",
                                success: function(data) {
                                    $("#listaProductos").html(data);
                                }
                            });
                        }
                    });
                }
            }
        });
    }

    function aplicarDescuento() {
        // validar que el descuento introducido es menor o igual al que el vendedor tiene asignado
        if (Number($("#txtDescuento").val() >= 0)) {
            if ($("#txtDescuento").val() <= <? echo $descuento; ?>) {
                // aplicar el descuento
                $.ajax({
                    type: "POST",
                    url: "/modulos/puntoventa/listaProductos.php",
                    data: "accion=aplicardescuento&descuento=" + $("#txtDescuento").val(),
                    success: function(data) {
                        $("#listaProductos").html(data);
                    }
                });

            } else {
                alert("ATENCIÓN: El descuento es mayor del permitido");
                $("#txtDescuento").focus();
            }
        }
    }

    function aplicarDescuentoIndividual(idpartida, descuento) {
        descuento = (descuento > 0 ? descuento : "");
        Swal.fire({
            title: 'Escribe un descuento (%)',
            // input: 'text',
            html: '<input type="text" id="txtDescuentoIndividual" name="txtDescuentoIndividual" class="form-control" value="' + descuento + '">',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            closeOnConfirm: false,
            allowOutsideClick: false,
            focusConfirm: false
        }).then((result) => {
            if (result.value) {
                if (Number($("#txtDescuentoIndividual").val() >= 0)) {
                    if ($("#txtDescuentoIndividual").val() <= <? echo $descuento; ?>) {
                        // aplicar el descuento
                        $.ajax({
                            type: "POST",
                            url: "/modulos/puntoventa/listaProductos.php",
                            data: "accion=aplicardescuentoindividual&idpartida=" + idpartida + "&descuento=" + $("#txtDescuentoIndividual").val(),
                            success: function(data) {
                                $("#listaProductos").html(data);
                            }
                        });
                    } else {
                        alert("ATENCIÓN: El descuento es mayor del permitido");
                    }
                }
            }
        });
    }

    function aplicarCantidad(idpartida) {
        Swal.fire({
            title: 'Escribe una cantidad',
            // input: 'text',
            html: '<input type="text" id="txtCantidad" name="txtCantidad" class="form-control">',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            closeOnConfirm: false,
            allowOutsideClick: false,
            focusConfirm: false
        }).then((result) => {
            if (result.value) {
                if (Number($("#txtCantidad").val() >= 0)) {
                    asignarCantidad(idpartida, $("#txtCantidad").val(), 'asignarcantidad');
                }
            }
        });
    }

    function cobrar(num) {
        if (num == "1") {
            var total = ($("#total").val() < 0 ? 0 : $("#total").val());
            $("#accion").val("cobrar");
        } else {
            $("#accion").val("apartar");
            var total = $("#abono").val();
        }
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/almenosunproducto.php",
            success: function(data) {
                if (data == "OK") {
                    Swal.fire({
                        title: 'Cobrar',
                        html: ' \
                        <div class="row"> \
                            <div class="col">\
                                <label for="" style="float:right;">Total: <span id="lblTotalCobrar">$' + formatMoney(total) + '</span></label><br> \
                            </div>\
                        </div> \
                        <div class="row"> \
                            <div class="col">\
                                <label for="" style="float:right;">Total USD: $' + formatMoney(total / <? echo $tipocambiousd; ?>) + '</label><br> \
                        </div>\
                        </div> \
                        <div class="row"> \
                            <div class="col">\
                                <label for="" style="float:right;">Saldo: <span id="lblRestante">$' + formatMoney(total) + '</span></label><br> \
                            </div>\
                        </div> \
                        <div class="row"> \
                            <div class="col">\
                                <label for="" style="float:right;">Saldo USD: <span id="lblRestanteUSD">$' + formatMoney(total / <? echo $tipocambiousd; ?>) + '</span></label><br>\
                            </div>\
                        </div>\
                        <div class="row"> \
                            <div class="col text-left">\
                                <label for="">Pagos</label><br>\
                            </div>\
                        </div>\
                        <hr>\
                        <div class="row">\
                            <div class="col-6">\
                                <select name="slcFormaPago" id="slcFormaPago" class="form-control" onChange="formaPago(this.value);" tabindex=2>\
                                    <?
                                    $result = mysqli_query($con, "select * from tcatformaspago");
                                    while ($row = mysqli_fetch_array($result)) {
                                        echo '<option value="' . $row['idformapago'] . '">' . $row['nombre'] . '</option>\ ';
                                    }
                                    ?> \
                                </select>\
                            </div>\
                            <div class="col-6">\
                                <input type="text" name="txtMonto" id="txtMonto" class="form-control" placeholder="Monto" onkeypress="verificarPago(event);" tabindex=1>\
                                <input type="text" name="txtCodigo" id="txtCodigo" class="form-control" placeholder="Código" onkeypress="verificarCodigo(event);" tabindex=1 style="display: none;" disabled>\
                            </div>\
                        </div>\
                        <div class="row mt-3">\
                            <div class="col-12">\
                                <input type="file" name="flComprobante" id="flComprobante" class="form-control" tabindex=3 style="display: none;">\
                            </div>\
                        </div>\
                        <div id="listaPagos">\
                        </div>\
                        <hr>\
                        <div class="row">\
                            <div class="col">\
                            </div>\
                        </div>\
                        ',
                        inputAttributes: {
                            autocapitalize: 'off'
                        },
                        showCancelButton: true,
                        showConfirmButton: true,
                        cancelButtonText: "Cancelar",
                        confirmButtonText: "Cobrar",
                        closeOnConfirm: false,
                        allowOutsideClick: false,
                        focusConfirm: false,
                        preConfirm: () => {
                            return validarCobro(total)
                        }
                    }).then((result) => {
                        if (result.value) {
                            // copiarInput("flComprobante");
                            // $("#flComprobante").show();
                            // $("#formPuntoVenta").append($("#flComprobante"));

                            $("#formPuntoVenta").submit();
                        }
                    });
                    cargarPagos();
                }
            }
        });
    }
    // <a href="javascript:;" class="btn btn-primary waves-effect waves-light" onClick="agregarPago();">Pagar</a>\

    // al validar el cobro, se debe verificar no haya pagos de más y que los pagos de tarjeta no excedan lo total a pagar
    function validarCobro(total) {
        var correcto = false;
        var num = $("#lblRestante").html().replace(/[^\d.]/g, "");
        var numusd = $("#lblRestante").html().replace(/[^\d.]/g, "");
        if (num != "0.00" && numusd != "0.00" && total > 0) {
            alert("No se ha cubierto el total a pagar");
        } else {
            $.ajax({
                type: "POST",
                url: "/modulos/puntoventa/validarpagos.php",
                data: "total=" + total,
                async: false,
                success: function(data) {
                    if (data != "OK") {
                        if (data == "ERROR1") {
                            alert("ATENCION: Los pagos de tarjeta exceden el total");
                        } else if (data == "ERROR2") {
                            alert("ATENCION: Hay pagos de más");
                        }
                    } else {
                        correcto = true;
                    }
                }
            });
        }
        return correcto;
    }

    var i = 1;

    function agregarPago() {
        // validar campos
        if ($("#slcFormaPago").val() == "0") {
            alert("ATENCION: debes seleccionar una forma de pago");
            $("#slcFormaPago").focus();
        } else if ($("#txtMonto").val() == "") {
            alert("ATENCION: debes escribir un monto");
            $("#txtMonto").focus();
        } else {
            $.ajax({
                type: "POST",
                url: "/modulos/puntoventa/listaPagos.php",
                data: "accion=agregar&formapago=" + $("#slcFormaPago").val() + "&monto=" + $("#txtMonto").val() + "&comprobante=" + $("#flComprobante").val().split(/(\\|\/)/g).pop(),
                success: function(data) {

                    var fileInput = $("#flComprobante");
                    var cloneInput = fileInput.clone();
                    cloneInput.attr('name', "flComprobante" + i);
                    cloneInput.attr('id', "flComprobante" + i);
                    $("#formPuntoVenta").append(cloneInput);
                    $("#flComprobante" + i).hide();

                    console.log("name:" + $("#flComprobante" + i).attr("name") + " nombre archivo: " + $("#flComprobante" + i).val().split(/(\\|\/)/g).pop());

                    $("#listaPagos").html(data);
                    $("#txtMonto").val("");
                    $("#flComprobante").val("");
                    $("#flComprobante").hide();
                    $("#flComprobante").prop("disabled", true);
                    $("#txtMonto").focus();
                    $("#slcFormaPago").val("1");

                    i += 1;
                }
            });
        }
    }

    // function agregarCodigo() {
    //     // validar campos
    //     if ($("#slcFormaPago").val() == "0") {
    //         alert("ATENCION: debes seleccionar una forma de pago");
    //         $("#slcFormaPago").focus();
    //     } else if ($("#txtCodigo").val() == "") {
    //         alert("ATENCION: debes escribir un código");
    //         $("#txtCodigo").focus();
    //     } else {
    //         $.ajax({
    //             type: "POST",
    //             url: "/modulos/puntoventa/listaPagos.php",
    //             data: "accion=agregar&formapago=" + $("#slcFormaPago").val() + "&codigo=" + $("#txtCodigo").val(),
    //             success: function(data) {
	// 				$("#listaPagos").html(data);
	// 				$("#txtCodigo").val("");
	// 				$("#txtCodigo").hide();
	// 				$("#txtMonto").prop("disabled",false);
	// 				$("#txtMonto").show();
	// 				$("#txtMonto").focus();
	// 				$("#slcFormaPago").val("1");
    //             }
    //         });
    //     }
    // }

    function eliminarPago(idpartida) {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaPagos.php",
            data: "accion=eliminar&idpartida=" + idpartida,
            success: function(data) {
                $("#listaPagos").html(data);
            }
        });
    }

    function cargarPagos() {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaPagos.php",
            data: "accion=mostrar",
            success: function(data) {
                $("#listaPagos").html(data);
            }
        });
    }

    function formaPago(value) {
        if (value == 7 || value == 6 || value == 5) {
            $("#flComprobante").show();
            $("#flComprobante").prop("disabled", false);
        } else {
            $("#flComprobante").hide();
            $("#flComprobante").prop("disabled", true);
        }

        if (value == 4) {
            $("#txtMonto").hide();
            $("#txtMonto").prop("disabled", true);
            $("#txtCodigo").show();
            $("#txtCodigo").prop("disabled", false);
            $("#txtCodigo").focus();
        } else {
            $("#txtMonto").show();
            $("#txtMonto").prop("disabled", false);
            $("#txtCodigo").hide();
            $("#txtCodigo").prop("disabled", true);
            $("#txtMonto").focus();
        }
    }

    function corte() {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/datoscorte.php",
            success: function(data) {
                var datos = data.split("|");
                var fondoinicial = datos[0];
                var ventas = datos[1];
                var devoluciones = datos[2];
                var totalfinal = datos[3];
                Swal.fire({
                    title: 'Corte',
                    html: '\
                <b>A continuacion se muestra el desglose del corte</b><br><br>\
                <div class="row"> \
                    <div class="col">\
                        <label for="" style="float:left;">Fondo Inicial: $' + formatMoney(fondoinicial) + '</label><br>\
                    </div>\
                </div> \
                <div class="row"> \
                    <div class="col">\
                        <label for="" style="float:left;">Ventas: $' + formatMoney(ventas) + '</label><br>\
                    </div>\
                </div> \
                <div class="row"> \
                    <div class="col">\
                        <label for="" style="float:left;">Devoluciones: $' + formatMoney(devoluciones) + '</label><br>\
                    </div>\
                </div> \
                <div class="row"> \
                    <div class="col">\
                        <label for="" style="float:left;">Total: $' + formatMoney(totalfinal) + '</label><br>\
                    </div>\
                </div> \
                <?
                $result = mysqli_query($con, "select * from tcatformaspago");
                while ($row = mysqli_fetch_array($result)) { ?>
                    <div class="row">\
                        <div class="col">\
                            <label for="" style="float:left;"><?= $row['nombre'] ?> </label><input type="text" id="txtFormapagoFancy<?=$row['idformapago']?>" name="txtFormapagoFancy<?=$row['idformapago']?>" class="form-control mt-3" placeholder="<?= $row['nombre']?>"><br>\
                        </div>\
                    </div>\
                    <?
                }
                ?>
                <div class="row mt-2"> \
                    <div class="col">\
                        <label for="" style="float:left;">Feria: </label><input type="text" id="txtFeria" name="txtFeria" class="form-control mt-3" placeholder="Feria"><br>\
                    </div>\
                </div> \
                ',
                    // type: "success",
                    inputAttributes: {
                        autocapitalize: 'off'
                    },
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Generar Corte',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    closeOnConfirm: false,
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.value) {
                        $("#accion").val("corte");
                        $("#feria").val($("#txtFeria").val());
                        <?
                        $result = mysqli_query($con, "select * from tcatformaspago");
                        while ($row = mysqli_fetch_array($result)) { ?>
                            $("#formapago<?= $row["idformapago"] ?>").val($("#txtFormapagoFancy<?= $row["idformapago"] ?>").val());
                            <?
                        }
                        ?>
                        // $("#efectivodlls").val($("#txtEfectivoDLLS").val());
                        // $("#efectivo").val($("#txtEfectivo").val());
                        // $("#cheque").val($("#txtCheque").val());
                        // $("#transferencia").val($("#txtTransferencia").val());
                        $("#formPuntoVenta").submit();
                    }
                });
            }
        });
        return 0;
    }

    function apartar() {
        // si hay al menos un producto con personalizacion, no se puede apartar
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/sinpersonalizacion.php",
            success: function(data) {
                if (data == "OK") {
                    $.ajax({
                        type: "POST",
                        url: "/modulos/puntoventa/almenosunproducto.php",
                        success: function(data) {
                            if (data == "OK") {
                                Swal.fire({
                                    title: 'Escribe el nombre del apartado',
                                    html: '<input type="text" id="txtNombre" name="txtNombre" class="form-control">',
                                    inputAttributes: {
                                        autocapitalize: 'off'
                                    },
                                    showCancelButton: true,
                                    confirmButtonText: 'Guardar',
                                    closeOnConfirm: false,
                                    allowOutsideClick: false,
                                    focusConfirm: false
                                }).then((result) => {
                                    if (result.value) {

                                        $("#nombreapartado").val($("#txtNombre").val());
                                        Swal.fire({
                                            title: 'Ingresa un abono',
                                            html: '<input type="text" id="txtAbono" name="txtAbono" class="form-control" onkeypress="validate(event);">',
                                            inputAttributes: {
                                                autocapitalize: 'off'
                                            },
                                            showCancelButton: true,
                                            confirmButtonText: 'Guardar',
                                            closeOnConfirm: false,
                                            allowOutsideClick: false,
                                            focusConfirm: false,
                                            preConfirm: () => {
                                                return validarAbono($("#txtAbono").val(), $("#total").val())
                                            }
                                        }).then((result) => {
                                            if (result.value) {
                                                $("#abono").val($("#txtAbono").val());
                                                // antes de cobrar, se pide si se quiere añadir alguna nota (opcionalmente)
                                                Swal.fire({
                                                    title: 'Añade información adicional',
                                                    html: '<input type="text" id="txtNotas" name="txtNotas" class="form-control" placeholder="Notas">\
                                                <input type="text" id="txtTelefono" name="txtTelefono" class="form-control mt-3" placeholder="Teléfono">',
                                                    inputAttributes: {
                                                        autocapitalize: 'off'
                                                    },
                                                    showCancelButton: true,
                                                    confirmButtonText: 'Guardar',
                                                    cancelButtonText: 'Omitir',
                                                    closeOnConfirm: false,
                                                    allowOutsideClick: false,
                                                    focusConfirm: false
                                                }).then((result) => {
                                                    // antes de cobrar, se pide si se quiere añadir alguna nota (opcionalmente)
                                                    if (result.value) {
                                                        $("#notas").val($("#txtNotas").val());
                                                        $("#telefono").val($("#txtTelefono").val());
                                                    }
                                                    cobrar(2);
                                                });
                                            }
                                        });
                                    }
                                });
                            }
                        }
                    });
                } else {
                    alert("ATENCION: no puedes apartar si hay al menos un producto personalizado");
                }
            }
        });
    }

    function validarAbono(abono, total) {
        if (abono <= 0) {
            alert("Debes poner algún abono");
            return false;
        } else if (Number(abono) > Number(total)) {
            alert("El abono excede el total a pagar");
            return false;
        }
        return true;
    }

    function formatMoney(n, c, d, t) {
        var c = isNaN(c = Math.abs(c)) ? 2 : c,
            d = d == undefined ? "." : d,
            t = t == undefined ? "," : t,
            s = n < 0 ? "-" : "",
            i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
            j = (j = i.length) > 3 ? j % 3 : 0;

        return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
    }

    function verificarPago(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }

        // enter
        if (theEvent.keyCode == 13) {
            agregarPago();
        }
    }

    // function verificarCodigo(evt) {
    //     var theEvent = evt || window.event;

    //     // Handle paste
    //     if (theEvent.type === 'paste') {
    //         key = event.clipboardData.getData('text/plain');
    //     } else {
    //         // Handle key press
    //         var key = theEvent.keyCode || theEvent.which;
    //         key = String.fromCharCode(key);
    //     }
    //     var regex = /[A-Za-z0-9 -]|\./;
    //     if (!regex.test(key)) {
    //         theEvent.returnValue = false;
    //         if (theEvent.preventDefault) theEvent.preventDefault();
    //     }

    //     // enter
    //     if (theEvent.keyCode == 13) {
    //         agregarCodigo();
    //     }
    // }

    function validate(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
            // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if (!regex.test(key)) {
            theEvent.returnValue = false;
            if (theEvent.preventDefault) theEvent.preventDefault();
        }

    }

    function introducirProducto(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
            // } else {
            // // Handle key press
            //     var key = theEvent.keyCode || theEvent.which;
            //     key = String.fromCharCode(key);
        }

        code = theEvent.keyCode || theEvent.which;
        if (code == 13) {
            // alert("enter");
            // al dar enter, se debe validar si sólo hay una coincidencia con lo que esta escrito
            $.ajax({
                type: "POST",
                url: "/modulos/puntoventa/solounacoincidencia.php",
                data: "producto=" + $("#txtProducto").val(),
                success: function(data) {
                    // si es así, entonces eso se introduce la partida
                    var datos = data.split("|");
                    if (datos[0] == "OK") {
                        var idproducto = datos[1];
                        var productointroducido = false;
                        $.ajax({
                            type: "POST",
                            url: "/modulos/puntoventa/obtenercolorestallas.php",
                            data: "idproducto=" + idproducto,
                            success: function(data) {
                                var datos = data.split("|");
                                if (datos[0] == "UNCOLORYTALLA") { // tiene solo un color y una talla
                                    var color = datos[1].split(",");
                                    var talla = datos[2].split(",");
                                    var idcolor = color[0].split("-")[0];
                                    var idtalla = talla[0].split("-")[0];
                                    validarExistencias(idtalla, idcolor, idproducto, 0);
                                    $.ajax({
                                        type: "POST",
                                        url: "/modulos/puntoventa/listaProductos.php",
                                        data: "accion=agregar&idproducto=" + idproducto + "&idcolor=" + idcolor + "&idtalla=" + idtalla,
                                        success: function(data) {
                                            $("#txtProducto").val("");
                                            $("#listaProductos").html(data);
                                        }
                                    });
                                    productointroducido = true;
                                } else if (datos[0] == "TARJETAREGALO") {
                                    //aquí se solicita el código
                                    var color = datos[1].split(",");
                                    var talla = datos[2].split(",");
                                    var idcolor = color[0].split("-")[0];
                                    var idtalla = talla[0].split("-")[0];
                                    if (validarExistencias(idtalla, idcolor, idproducto, 0)) {

                                        Swal.fire({
                                            title: 'Escribe el código de la tarjeta',
                                            html: '<input type="text" id="txtCodigoTarjeta" name="txtCodigoTarjeta" class="form-control">',
                                            inputAttributes: {
                                                autocapitalize: 'off'
                                            },
                                            showCancelButton: true,
                                            confirmButtonText: 'Guardar',
                                            closeOnConfirm: false,
                                            allowOutsideClick: false,
                                            focusConfirm: false
                                        }).then((result) => {
                                            if (result.value) {
                                                var codigotarjeta = $("#txtCodigoTarjeta").val();
                                                $.ajax({
                                                    type: "POST",
                                                    url: "/modulos/puntoventa/listaProductos.php",
                                                    data: "accion=agregar&idproducto=" + idproducto + "&idcolor=" + idcolor + "&idtalla=" + idtalla + "&codigo=" + codigotarjeta,
                                                    success: function(data) {
                                                        $("#txtProducto").val("");
                                                        $("#listaProductos").html(data);
                                                    }
                                                });
                                                productointroducido = true;
                                            }
                                        });
                                    }
                                } else if (datos[0] == "OK") {
                                    var colores = datos[1].split(",");
                                    var tallas = datos[2].split(",");
                                    var j;
                                    var opcionesc = "";
                                    var opcionest = "";
                                    var flagc = (colores.length - 1 == 1) ? true : false;
                                    var flagt = (tallas.length - 1 == 1) ? true : false;
                                    for (j = 0; j < colores.length - 1; j++) {
                                        opcionesc += '<option value="' + colores[j].split("-")[0] + '" ' + ((flagc) ? 'selected' : '') + '>' + colores[j].split("-")[1] + '</option>';
                                    }
                                    for (j = 0; j < tallas.length - 1; j++) {
                                        opcionest += '<option value="' + tallas[j].split("-")[0] + '" ' + ((flagt) ? 'selected' : '') + '>' + tallas[j].split("-")[1] + '</option>';
                                    }
                                    // swal
                                    Swal.fire({
                                        title: 'Seleccionar color y talla',
                                        html: ' \
                                    <div class="row" style="' + ((flagc) ? 'display:none;' : '') + '">\
                                        <div class="col">\
                                            <select name="slcColor" id="slcColor" class="form-control">\
                                                <option value="0">--Selecciona un color--</option>' + opcionesc +
                                            '</select>\
                                        </div>\
                                    </div>\
                                    <div class="row" style="margin-top:15px;' + ((flagt) ? 'display:none;' : '') + '">\
                                        <div class="col">\
                                            <select name="slcTalla" id="slcTalla" class="form-control">\
                                                <option value="0">--Selecciona una talla--</option>' + opcionest +
                                            '</select>\
                                        </div>\
                                    </div>\
                                    ',
                                        inputAttributes: {
                                            autocapitalize: 'off'
                                        },
                                        showCancelButton: true,
                                        showConfirmButton: true,
                                        // cancelButtonText: "Cancelar",
                                        // confirmButtonText: "Cobrar",
                                        closeOnConfirm: false,
                                        allowOutsideClick: false,
                                        focusConfirm: false,
                                        preConfirm: () => {
                                            return validarExistencias($("#slcTalla").val(), $("#slcColor").val(), idproducto, 0)
                                        }
                                    }).then((result) => {
                                        if (result.value) {
                                            // 
                                            $.ajax({
                                                type: "POST",
                                                url: "/modulos/puntoventa/listaProductos.php",
                                                data: "accion=agregar&idproducto=" + idproducto + "&idcolor=" + $("#slcColor").val() + "&idtalla=" + $("#slcTalla").val(),
                                                success: function(data) {
                                                    $("#txtProducto").val("");
                                                    $("#listaProductos").html(data);
                                                }
                                            });
                                            productointroducido = true;
                                        }
                                    });
                                } else {
                                    // no tiene colores ni tallas
                                    validarExistencias(0, 0, idproducto, 0);
                                    $.ajax({
                                        type: "POST",
                                        url: "/modulos/puntoventa/listaProductos.php",
                                        data: "accion=agregar&idproducto=" + idproducto,
                                        success: function(data) {
                                            $("#txtProducto").val("");
                                            $("#listaProductos").html(data);
                                        }
                                    });
                                    productointroducido = true;
                                }
                                if (productointroducido) {
                                    $(".ui-menu-item").hide();
                                }
                            }
                        });
                    } else {
                        alert("No se encontraron coincidencias");
                    }
                }
            });
        }
    }


    function agregarGasto() {
        Swal.fire({
            title: 'Introducir gasto de caja',
            html: 'Monto<br><input type="text" id="txtMontoGasto" name="txtMontoGasto" class="form-control"><br><br>Motivo<textarea class="form-control" name="txtMotivoGasto" id="txtMotivoGasto">',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            closeOnConfirm: true,
            allowOutsideClick: false,
            focusConfirm: false,
            preConfirm: () => {
                return validarGasto($("#txtMontoGasto").val(), $("#txtMotivoGasto").val())
            }
        }).then((result) => {
            if (result.value) {
                var monto = $("#txtMontoGasto").val();
                var motivo = $("#txtMotivoGasto").val();
                $.ajax({
                    type: "POST",
                    url: "/modulos/puntoventa/agregargasto.php",
                    data: "monto=" + monto + "&motivo=" + motivo,
                    success: function(data) {
                        if (data == "OK") {
                            Swal.fire("ATENCIÓN", "Se ha introducido el gasto correctamente.", "success");
                        } else {
                            Swal.fire("ATENCIÓN", "No se pudo introducir el gasto, inténtalo nuevamente.", "error");
                        }
                    }
                });
            }
        });
    }

    function validarGasto(monto, motivo) {
        if (Number(monto) == 0 || motivo == "") {
            return false;
        } else {
            return true;
        }
    }

    function inputsPersonalizacion(idpartida) {
        // se agrega una personalizacion nueva
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaProductos.php",
            data: "accion=agregarpersonalizacion&idpartida=" + idpartida,
            success: function(data) {
                $("#listaProductos").html(data);
            }
        });
    }

    function eliminarPersonalizacion(idpersonalizaciontmp) {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaProductos.php",
            data: "accion=eliminarpersonalizacion&idpersonalizaciontmp=" + idpersonalizaciontmp,
            success: function(data) {
                $("#listaProductos").html(data);
            }
        });
    }

    function actualizarSelect(idpersonalizaciontmp, valor) {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaProductos.php",
            data: "accion=actualizarselect&idpersonalizaciontmp=" + idpersonalizaciontmp + "&idpersonalizacion=" + valor,
            success: function(data) {
                $("#listaProductos").html(data);
            }
        });
    }

    function actualizarTextarea(idpersonalizaciontmp, valor) {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaProductos.php",
            data: "accion=actualizartext&idpersonalizaciontmp=" + idpersonalizaciontmp + "&personalizacion=" + valor,
            async: false,
            success: function(data) {
                $("#listaProductos").html(data);
            }
        });
    }

    $(document).ready(function(e) {
        $.ajax({
            type: "POST",
            url: "/modulos/puntoventa/listaProductos.php",
            success: function(data) {
                $("#listaProductos").html(data);
            }
        });

        $('#txtFecha').daterangepicker({
            singleDatePicker: true,
            locale: {
                format: 'YYYY-MM-DD'
            }
        });


    });

    // function validarFondoInicial() {
    //     $.ajax({
    //         type:"POST",
    //         url:"/ajax/guardarfondoinicial.php",
    //         data: "fondoinicial=" + $("#txtFondoInicial").val() + "&fecha=" + $("#txtFecha").val(),
    //         success: function(data){
    //             if (data=="OK") {
    //                 window.location = location.href;
    //                 // console.log("Fondo inicial guardado");
    //             }else{
    //                 Swal.fire("Error","No se puede iniciar el corte","error");
    //             }
    //         }
    //     });
    // }

    <?
    if (isset($cuentacobrada)) {
        if ($cuentacobrada) {
    ?>
            Swal.fire({
                title: 'Cambio',
                html: '\
            MXN: <label for="">$' + <? echo number_format($_POST["cambio"], 2) ?> + '</label><br>\
            USD: <label for="">$' + <? echo number_format($_POST["cambio"] / $tipocambiousd, 2) ?> + '</label>\
            ',
                type: "success",
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                closeOnConfirm: false,
                timer: 3000
            });
        <?
        } else {
        ?>
            Swal.fire("Error", "Ocurrió un error al generar el cobro", "error");
        <?
        }
    }
    if (isset($cuentaapartada)) {
        if ($cuentaapartada) {
        ?>
            Swal.fire({
                title: 'Cambio',
                html: '\
            MXN: <label for="">$' + <? echo number_format($_POST["cambio"], 2) ?> + '</label><br>\
            USD: <label for="">$' + <? echo number_format($_POST["cambio"] / $tipocambiousd, 2) ?> + '</label>\
            ',
                type: "success",
                inputAttributes: {
                    autocapitalize: 'off'
                },
                showCancelButton: false,
                confirmButtonText: 'Aceptar',
                closeOnConfirm: false,
                timer: 3000
            });
        <?
        } else {
        ?>
            Swal.fire("Error", "Ocurrió un error al generar el cobro", "error");
    <?
        }
    }
    ?>
</script>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
    <div class="navbar navbar-expand-lg">
        <!-- btn to toggle sidenav on small screen -->
        <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512">
                <path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z" />
            </svg>
        </a>
        <!-- Page title -->
        <div class="navbar-text nav-title flex" id="pageTitle">Punto de Venta</div>
    </div>
</div>
<?
if (mysqli_num_rows($corte) == 0 || $_POST["accion"] == "corte") {
    $corteanterior = mysqli_fetch_assoc(mysqli_query($con, "select * from tcortessucursales where idsucursal = '" . $vendedor["idsucursal"] . "' and status = 'T' order by idcorte desc limit 1"));
?>
    <div class="padding">
        <div class="box">

            <div class="box-body">
                <form name="formFondoInicial" id="formFondoInicial" method="post" action="home.php?modulo1=puntoventa">
                    <input type="hidden" name="accion" id="accion" value="guardarfondoinicial">
                    <input type="hidden" name="jl2041xm398" value="<? echo $_SESSION["authToken"]; ?>">

                    <div class="form-group">
                        <div class="row">
                            <div class="col-2"><label for="">Fecha:</label></div>
                            <div class="col-10">
                                <input type="text" name="txtFecha" id="txtFecha" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-2"><label for="">Fondo Inicial:</label></div>
                            <div class="col-10">
                                <input type="text" name="txtFondoInicial" id="txtFondoInicial" value="<? echo $corteanterior["feria"]; ?>" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-offset-8 col-md-2">
                                <button type="submit" class="btn btn-primary btn-block waves-effect waves-light">Guardar</button>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

    </div>
<?
} else {
?>

    <div class="padding">
        <div class="box">
            <!-- <div class="box-header">

		</div> -->

            <div class="box-body">
                <!-- <form name="formPuntoVenta" id="formPuntoVenta" method="post" action=""> -->
                <form name="formPuntoVenta" id="formPuntoVenta" method="post" action="home.php?modulo1=puntoventa" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-12">
                            <input type="hidden" name="subtotal" id="subtotal">
                            <input type="hidden" name="iva" id="iva">
                            <input type="hidden" name="total" id="total">
                            <input type="hidden" name="accion" id="accion" value="">
                            <input type="hidden" name="abono" id="abono" value="">
                            <input type="hidden" name="notas" id="notas" value="">
                            <input type="hidden" name="telefono" id="telefono" value="">
                            <input type="hidden" name="nombreapartado" id="nombreapartado" value="">
                            <input type="hidden" name="feria" id="feria" value="">
                            <?
                            $result = mysqli_query($con, "select * from tcatformaspago");
                            while ($row = mysqli_fetch_array($result)) { ?>
                                <input type="hidden" name="formapago<?= $row["idformapago"] ?>" id="formapago<?= $row["idformapago"] ?>" value="">
                                <?
                            }
                            ?>
                            <!-- <input type="hidden" name="efectivodlls" id="efectivodlls" value="">
                            <input type="hidden" name="efectivo" id="efectivo" value="">
                            <input type="hidden" name="cheque" id="cheque" value="">
                            <input type="hidden" name="transferencia" id="transferencia" value=""> -->

                            <!-- cambio -->
                            <input type="hidden" name="cambio" id="cambio">
                            <input type="text" name="txtProducto" id="txtProducto" class="form-control" placeholder="Escribe un Producto" onKeypress="introducirProducto(event);">
                        </div>
                    </div>
                    <div class="row" style="margin-top:30px;">
                        <div class="col-2">
                            <label for="" class="pull-right">Descuento (%):</label>
                        </div>
                        <div class="col-1">
                            <input type="text" name="txtDescuento" id="txtDescuento" class="form-control">
                        </div>
                        <div class="col-1">
                            <button type="button" class="btn btn-primary waves-light waves-effect pull-right" onClick="aplicarDescuento();">Aplicar</button>
                        </div>
                        <!-- <div class="col-6">
                        <label for="" >Descuento (%):</label>
                        <input type="text" name="txtDescuento" id="txtDescuento" class="form-control">
                        <button type="button" class="btn btn-primary waves-light waves-effect" onClick="aplicarDescuento();">Aplicar</button>
                    </div> -->
                        <div class="col text-right">
                            <!-- <button type="button" class="btn btn-danger  waves-light waves-effect pull-right" onClick="if(confirm('¿Estás seguro de que quieres eliminar los productos?')) limpiar();" style="margin-left:15px;">Limpiar</button> -->
                            <button type="button" class="btn btn-primary waves-light waves-effect" onClick="cobrar(1);">Cobrar</button>&nbsp;&nbsp;
                            <button type="button" class="btn btn-primary waves-light waves-effect" onClick="apartar();">Apartar</button>&nbsp;&nbsp;
                            <button type="button" class="btn btn-success waves-light waves-effect" onClick="corte();">Corte</button>&nbsp;&nbsp;
                            <button type="button" class="btn btn-success waves-light waves-effect" onClick="agregarGasto();">Gasto</button>&nbsp;&nbsp;
                            <button type="button" class="btn btn-danger  waves-light waves-effect" onClick="limpiar();">Limpiar</button>
                        </div>
                    </div>
                    <div class="row" style="margin-top:30px;">
                        <div class="col">
                            <b><label for="" class="pull-right" style="margin-left:15px;">Total: <span id="lblTotal"></span></label></b>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="box" id="listaProductos">

        </div>
    </div>

    <script>
        $(document).ready(function(e) {
            console.log("idsucursal: " + <?= $_SESSION["v3nd3d0rpl4y3r4spvc1sn3sucursal"] ?>);
            var productos = [
                <?
                $idalmacen = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3sucursal"] . "'"))["idalmacen"];
                $productos = mysqli_query($con, "call pproductostienda(" . $idalmacen . ",'')");
                while ($producto = mysqli_fetch_assoc($productos)) {
                ?> {
                        value: "<? echo str_replace('"', '\"', $producto["nombre"]) . " (" . $producto["codigobarras"] . ")"; ?>",
                        id: "<? echo $producto["idproducto"]; ?>"
                    },
                <?
                }
                ?>
            ];
            $("#txtProducto").autocomplete({
                source: productos,
                minLength: 2,
                select: function(event, ui) {
                    //en el ajax superior, se manda el idproducto y se recupera una lista de todos los colores y tallas del producto, para poder mostrar el sweetalert con los selects. En caso de que el producto no tenga tallas ni productos, la inforamción recuperada estara vacia y el producto se agregara automaticamente
                    $.ajax({
                        type: "POST",
                        url: "/modulos/puntoventa/obtenercolorestallas.php",
                        data: "idproducto=" + ui.item.id,
                        success: function(data) {
                            var datos = data.split("|");
                            if (datos[0] == "UNCOLORYTALLA") { // tiene solo un color y una talla
                                var color = datos[1].split(",");
                                var talla = datos[2].split(",");
                                var idcolor = color[0].split("-")[0];
                                // var nombrecolor = color[0].split("-")[1];
                                var idtalla = talla[0].split("-")[0];
                                // var nombretalla = talla[0].split("-")[0];
                                validarExistencias(idtalla, idcolor, ui.item.id, 0);
                                $.ajax({
                                    type: "POST",
                                    url: "/modulos/puntoventa/listaProductos.php",
                                    data: "accion=agregar&idproducto=" + ui.item.id + "&idcolor=" + idcolor + "&idtalla=" + idtalla,
                                    success: function(data) {
                                        $("#txtProducto").val("");
                                        $("#listaProductos").html(data);
                                    }
                                });
                            } else if (datos[0] == "TARJETAREGALO") {
                                //aquí se solicita el código
                                var color = datos[1].split(",");
                                var talla = datos[2].split(",");
                                var idcolor = color[0].split("-")[0];
                                var idtalla = talla[0].split("-")[0];
                                if (validarExistencias(idtalla, idcolor, ui.item.id, 0)) {

                                    Swal.fire({
                                        title: 'Escribe el código de la tarjeta',
                                        html: '<input type="text" id="txtCodigoTarjeta" name="txtCodigoTarjeta" class="form-control">',
                                        inputAttributes: {
                                            autocapitalize: 'off'
                                        },
                                        showCancelButton: true,
                                        confirmButtonText: 'Guardar',
                                        closeOnConfirm: false,
                                        allowOutsideClick: false,
                                        focusConfirm: false
                                    }).then((result) => {
                                        if (result.value) {
                                            var codigotarjeta = $("#txtCodigoTarjeta").val();
                                            $.ajax({
                                                type: "POST",
                                                url: "/modulos/puntoventa/listaProductos.php",
                                                data: "accion=agregar&idproducto=" + ui.item.id + "&idcolor=" + idcolor + "&idtalla=" + idtalla + "&codigo=" + codigotarjeta,
                                                success: function(data) {
                                                    $("#txtProducto").val("");
                                                    $("#listaProductos").html(data);
                                                }
                                            });
                                            productointroducido = true;
                                        }
                                    });
                                }
                            } else if (datos[0] == "OK") { // tiene más de un color y una talla
                                var colores = datos[1].split(",");
                                var tallas = datos[2].split(",");
                                var j;
                                var opcionesc = "";
                                var opcionest = "";
                                var flagc = (colores.length - 1 == 1) ? true : false;
                                var flagt = (tallas.length - 1 == 1) ? true : false;
                                for (j = 0; j < colores.length - 1; j++) {
                                    opcionesc += '<option value="' + colores[j].split("-")[0] + '" ' + ((flagc) ? 'selected' : '') + '>' + colores[j].split("-")[1] + '</option>';
                                }
                                for (j = 0; j < tallas.length - 1; j++) {
                                    opcionest += '<option value="' + tallas[j].split("-")[0] + '" ' + ((flagt) ? 'selected' : '') + '>' + tallas[j].split("-")[1] + '</option>';
                                }
                                // swal
                                Swal.fire({
                                    title: 'Seleccionar color y talla',
                                    html: ' \
                            <div class="row" style="' + ((flagc) ? 'display:none;' : '') + '">\
                                <div class="col">\
                                    <select name="slcColor" id="slcColor" class="form-control">\
                                        <option value="0">--Selecciona un color--</option>' + opcionesc +
                                        '</select>\
                                </div>\
                            </div>\
                            <div class="row" style="margin-top:15px;' + ((flagt) ? 'display:none;' : '') + '">\
                                <div class="col">\
                                    <select name="slcTalla" id="slcTalla" class="form-control">\
                                        <option value="0">--Selecciona una talla--</option>' + opcionest +
                                        '</select>\
                                </div>\
                            </div>\
                            ',
                                    inputAttributes: {
                                        autocapitalize: 'off'
                                    },
                                    showCancelButton: true,
                                    showConfirmButton: true,
                                    // cancelButtonText: "Cancelar",
                                    // confirmButtonText: "Cobrar",
                                    closeOnConfirm: false,
                                    allowOutsideClick: false,
                                    focusConfirm: false,
                                    preConfirm: () => {
                                        return validarExistencias($("#slcTalla").val(), $("#slcColor").val(), ui.item.id, 0)
                                    }
                                }).then((result) => {
                                    if (result.value) {
                                        // 
                                        $.ajax({
                                            type: "POST",
                                            url: "/modulos/puntoventa/listaProductos.php",
                                            data: "accion=agregar&idproducto=" + ui.item.id + "&idcolor=" + $("#slcColor").val() + "&idtalla=" + $("#slcTalla").val(),
                                            success: function(data) {
                                                $("#txtProducto").val("");
                                                $("#listaProductos").html(data);
                                            }
                                        });
                                    }
                                });
                            } else {
                                // no tiene colores ni tallas
                                validarExistencias(0, 0, ui.item.id, 0);
                                $.ajax({
                                    type: "POST",
                                    url: "/modulos/puntoventa/listaProductos.php",
                                    data: "accion=agregar&idproducto=" + ui.item.id,
                                    success: function(data) {
                                        $("#txtProducto").val("");
                                        $("#listaProductos").html(data);
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });

        function validarExistencias(idtalla, idcolor, idproducto, idpartida, cantidad = 1, accion = "sumar") {
            var correcto = true;
            $.ajax({
                type: "POST",
                url: "/modulos/puntoventa/validarexistencias.php",
                data: "idcolor=" + idcolor + "&idtalla=" + idtalla + "&idproducto=" + idproducto + "&idpartida=" + idpartida + "&cantidad=" + cantidad + "&accion=" + accion,
                async: false,
                success: function(data) {
                    // if (data=="OK") {
                    //     // correcto = true;
                    // } else {cargarPagos
                    //     alert("ATENCION: No hay existencias suficientes para este producto. Se agregará a la lista y se generará una solicitud de compra para surtir la existencia.");
                    // }
                }
            });
            return correcto;
        }
    </script>

<?
}
?>