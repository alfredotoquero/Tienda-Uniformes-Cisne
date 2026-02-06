<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>

<script>
function imprimir(idcuenta) {
    return 0;
}
</script>
<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Detalle de Ventas</div>
	</div>
</div>

<div class="padding">
	<div class="box">
        <div class="box-header">
            <div class="row">
                <div class="col">
                    <?
                    $corte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idcorte='".$_GET["idcorte"]."'"));
                    $vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$corte["idvendedor"]."'"));
                    echo "<b># Corte:</b> " . $corte["folio"] . "<br><b>Vendedor:</b> " . $vendedor["nombre"] . "<br><b>Fecha:</b> " . fecha_formateada2($corte["fechainicial"]) . "<br><b>Status:</b> " . ($corte["status"]=="A" ? "Activo" : ($corte["status"]=="T" ? "Terminado" : "")) . "</b>";
                    ?>
                </div>
                <div class="col text-right" >
                    <a href="?modulo1=cortes#<? echo $corte["idcorte"]; ?>" class="btn btn-danger waves-effect pull-right">Regresar</a>
                </div>
            </div>
        </div>

	
        <div class="box-body" id="listaCuentas">
            <div class="table-responsive">
                <?
                // $cuentas = mysqli_query($con,"select * from tcuentas where idcorte='".$_GET["idcorte"]."' order by idcuenta desc");
                $tickets = mysqli_query($con,"select * from ttickets where idcorte='".$_GET["idcorte"]."' order by idticket desc");
                if (mysqli_num_rows($tickets)>0) {
                    ?>
                    <table class="table table-striped b-t">
                        <thead>
                            <tr>
                                <th># Ticket</th>
                                <th>Tipo</th>
                                <th>Detalle</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            while ($ticket = mysqli_fetch_assoc($tickets)) {
                                // desglose 
                                $efectivo = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=1"))["monto"];
                                $efectivousd = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=2"))["monto"];
                                $tarjeta = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=3"))["monto"];
                                $transferencia = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=5"))["monto"];
                                $cheque = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=6"))["monto"];
                                $tipocuenta = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$ticket["idcuenta"]."'"))["tipocuenta"];
                                $tipoimpresion = 1;
                                if($tipocuenta=="A"){
                                    if(mysqli_num_rows(mysqli_query($con,"select * from ttickets where idcuenta = '".$ticket["idcuenta"]."' and idticket < '".$ticket["idticket"]."' and status = 'A'"))==0){
                                        $tipoimpresion = 2;
                                    }else{
                                        $tipoimpresion = 3;
                                    }
                                }else if($tipocuenta==""){
                                    $tipoimpresion = 4;
                                }
                                $nombrevendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$ticket["idvendedor"]."'"))["nombre"];
                                ?>
                                <tr id="<? echo $ticket["idticket"]; ?>">
                                    <td><? echo $ticket["folio"]; ?></td>
                                    <td><? echo ($tipocuenta=="A" ? "Apartado" : (($tipocuenta=="") ? "Pedido #".$ticket["idpedido"]."<br>".mysqli_fetch_assoc(mysqli_query($con,"select cliente from vpedidos where idpedido = '".$ticket["idpedido"]."'"))["cliente"] : "Venta")); ?></td>
                                    <td>
                                    <?php
                                    if($tipoimpresion<=2){
                                        $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$ticket["idcuenta"]."' and idcuentaproducto not in (select idcuentaproducto from tdevoluciones)");
                                        while($partida = mysqli_fetch_assoc($partidas)){
                                            echo $partida["cantidad"]." ".$partida["producto"]." | Talla: ".$partida["talla"]." Color: ".$partida["color"]."<br>";
                                        }
                                    }else if($tipoimpresion==3){
                                        echo "Abono a apartado";
                                    }else{
                                        $partidas = mysqli_query($con,"select * from trcotizacionproductos where idpedido='".$ticket["idpedido"]."' group by idproducto,producto order by idcotizacionproducto");
                                        while($partida = mysqli_fetch_assoc($partidas)){
                                            $producto = mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'");
                                            if (mysqli_num_rows($producto)>0) {
                                                $nombre = mysqli_fetch_assoc($producto)["nombre"];
                                            } else {
                                                $nombre = $partida["producto"];
                                            }
                                            echo $partida["cantidad"]." ".$nombre."<br>";

                                            $dcolores = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idproducto!=0 group by idcolor");
                                            while($dcolor = mysqli_fetch_assoc($dcolores)){
                                            
                                                $color = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$dcolor["idcolor"]."'"))["nombre"];
                                                $extras = $color;
                                            
                                                $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto='".$partida["idproducto"]."' and idcolor='".$dcolor["idcolor"]."'");
                                                while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                                                    // talla
                                                    $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                                                    $extras .= " / " . $dtalla["cantidad"] . " - " . $talla;
                                                }

                                                echo " - ".$extras."<br>";
                                            }

                                            $desgloses = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto=0 and producto='".$partida["producto"]."' group by color having color!='' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                                            while ($desglose = mysqli_fetch_assoc($desgloses)) {
                                            
                                                $color = $desglose["color"];
                                                $extras = $color; 

                                                $dtallas = mysqli_query($con,"select * from trpedidoproductos where idpedido='".$ticket["idpedido"]."' and idproducto=0 and color='".$desglose["color"]."' and idcotizacionproducto = '".$partida["idcotizacionproducto"]."'");
                                                while ($dtalla = mysqli_fetch_assoc($dtallas) ) {
                                                    // talla
                                                    $talla = mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$dtalla["idtalla"]."'"))["nombre"];
                                                    $extras .= " / " . $dtalla["cantidad"] . " - " . $talla;
                                                }

                                                echo " - ".$extras."<br>";
                                            }
                                        }
                                    }
                                    ?>
                                    </td>
                                    <td><? echo $nombrevendedor; ?></td>
                                    <td>
                                    <?
                                    echo "$" . number_format($ticket["total"],2);
                                    if ($efectivo>0 or $efectivousd>0 or $tarjeta>0 or $transferencia>0 or $cheque>0) {
                                        echo "<br>";
                                        if ($efectivo>0) {
                                            echo "Efectivo: $" . number_format($efectivo,2);
                                            if ($efectivousd>0) {
                                                echo "<br>Efectivo USD: $" . number_format($efectivousd,2);
                                                if ($tarjeta>0) {
                                                    echo "<br>Tarjeta: $" . number_format($tarjeta,2);
                                                    if ($transferencia>0) {
                                                        echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                        if ($cheque>0) {
                                                            echo "<br>Cheque: $" . number_format($cheque,2);
                                                        }
                                                    }else if ($cheque>0){
                                                        echo "<br>Cheque: $" . number_format($cheque,2);
                                                    }
                                                }else if ($transferencia>0) {
                                                    echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                    if ($cheque>0) {
                                                        echo "<br>Cheque: $" . number_format($cheque,2);
                                                    }
                                                }else if ($cheque>0){
                                                    echo "<br>Cheque: $" . number_format($cheque,2);
                                                }
                                            }else if($tarjeta>0){
                                                echo "<br>Tarjeta: $" . number_format($tarjeta,2);
                                                if ($transferencia>0){
                                                    echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                    if ($cheque>0) {
                                                        echo "<br>Cheque: $" . number_format($cheque,2);
                                                    }
                                                }else if ($cheque>0){
                                                    echo "<br>Cheque: $" . number_format($cheque,2);
                                                }
                                            }else if ($transferencia>0){
                                                echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                if ($cheque>0) {
                                                    echo "<br>Cheque: $" . number_format($cheque,2);
                                                }
                                            }else if ($cheque>0){
                                                echo "<br>Cheque: $" . number_format($cheque,2);
                                            }
                                        }else if ($efectivousd>0){
                                            echo "Efectivo USD: $" . number_format($efectivousd,2);
                                            if ($tarjeta>0) {
                                                echo "<br>Tarjeta: $" . number_format($tarjeta,2);
                                                if ($transferencia>0){
                                                    echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                    if ($cheque>0) {
                                                        echo "<br>Cheque: $" . number_format($cheque,2);
                                                    }
                                                }else if ($cheque>0){
                                                    echo "<br>Cheque: $" . number_format($cheque,2);
                                                }
                                            }else if ($transferencia>0){
                                                echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                if ($cheque>0) {
                                                    echo "<br>Cheque: $" . number_format($cheque,2);
                                                }
                                            }else if ($cheque>0){
                                                echo "<br>Cheque: $" . number_format($cheque,2);
                                            }
                                        }else if ($tarjeta>0){
                                            echo "Tarjeta: $" . number_format($tarjeta,2);
                                            if ($transferencia>0){
                                                echo "<br>Transferencia: $" . number_format($transferencia,2);
                                                if ($cheque>0) {
                                                    echo "<br>Cheque: $" . number_format($cheque,2);
                                                }
                                            }else if ($cheque>0){
                                                echo "<br>Cheque: $" . number_format($cheque,2);
                                            }
                                        }else if ($transferencia>0){
                                            echo "Transferencia: $" . number_format($transferencia,2);
                                            if ($cheque>0) {
                                                echo "<br>Cheque: $" . number_format($cheque,2);
                                            }
                                        }else if ($cheque>0){
                                            echo "Cheque: $" . number_format($cheque,2);
                                        }
                                    }
                                    ?>
                                    </td>
                                    <td><? echo fecha_formateada($ticket["fecha"]); ?></td>
                                    <td>
                                        <div class="btn-group dropdown">
                                            <button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
                                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <a href="javascript:;" onClick="imprimirTicket(<? echo $ticket["idticket"]; ?>,0,<? echo $tipoimpresion; ?>)"><li class="dropdown-item">Imprimir</li></a>
                                                <? if($tipocuenta!=""){ ?>
                                                <a href="?modulo1=cortes&modulo2=detallev&modulo3=detallecuenta&idticket=<? echo $ticket["idticket"]; ?>&idcuenta=<? echo $ticket["idcuenta"]; ?>&idcorte=<? echo $_GET["idcorte"]; ?>"><li class="dropdown-item">Ver detalle</li></a>
                                                <a href="?modulo1=cortes&modulo2=devolucion&idcorte=<? echo $_GET["idcorte"]; ?>&idcuenta=<? echo $ticket["idcuenta"]; ?>&idticket=<? echo $ticket["idticket"]; ?>"><li class="dropdown-item">Devolución</li></a>
                                                <? } ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?
                            }
                            ?>
                        </tbody>
                    </table>
                    <?
                }
                ?>
            </div>
        </div>
    </div>
</div>
