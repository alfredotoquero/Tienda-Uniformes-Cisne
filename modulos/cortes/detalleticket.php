<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>

<script>

function formatMoney(n, c, d, t) {
  var c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
    j = (j = i.length) > 3 ? j % 3 : 0;

  return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
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
	  <div class="navbar-text nav-title flex" id="pageTitle">Detalle del Ticket</div>
	</div>
</div>

<div class="padding">
	
	<div class="box">
        <?
        $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$_GET["idcuenta"]."'");

        if (mysqli_num_rows($partidas)>0) {
            ?>
            <div class="box-header">
                <div class="row">
                    <div class="col">
                        <? 
                        $pedido = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"));
                        $ticket = mysqli_fetch_assoc(mysqli_query($con,"select * from ttickets where idticket='".$_GET["idticket"]."'"));
                        $vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$pedido["idvendedor"]."'"));
                        $sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$pedido["idsucursal"]."'"));					
                        echo "<b># Ticket:</b> " . $ticket["folio"] . "<br><b>Vendedor:</b> " . $vendedor["nombre"] . "<br><b>Sucursal:</b> " . $sucursal["nombre"] . "<br><b>Fecha:</b> " . fecha_formateada2($pedido["fecha"]) . "</b>"; 
                        ?>
                    </div>

                    <div class="col text-right">
                        <a href="?modulo1=cortes&modulo2=detallev&idcorte=<? echo $_GET["idcorte"]; ?>#<? echo $_GET["idticket"]; ?>" class="btn btn-danger waves-effect waves-light">Regresar</a>
                    </div>
                </div>
            </div>

            <div class="box-body" >
                <div class="table-responsive">
                    <table class="table m-0">
                        <thead>
                            <tr>
                                <th width="30">Cant.</th>
                                <th>Producto</th>
                                <th width="200">Talla</th>
                                <th width="200">Color</th>
                                <th width="100">P.U.</th>
                                <th width="100">Descuento</th>
                                <th width="100">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            $subtotal = 0;
                            while($partida = mysqli_fetch_assoc($partidas)){
                                // calcular y guardar subtotal, iva y total de cada partida
                                // se agrega costo adicional por cada personalizacion seleccionada

                                $precio = $partida["total"]/$partida["cantidad"];
                                $cantidad = $partida["cantidad"];
                                $subtotalp = $partida["subtotal"];
                                $ivap = $partida["iva"];
                                $totalp = $subtotalp+$ivap;

                                $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
                                $subtotal += (float)($partida["cantidad"]*($precio*((100-$partida["descuento"])/100)/1.08));
                            ?>
                            <tr id="trpartida<? echo $partida["idcuentaproducto"]; ?>">
                                <td align="center"><? echo $partida["cantidad"]; ?></td>
                                <td>
                                <?
                                echo $producto["nombre"];

                                // personalizaciones
                                $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
                                while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                                    $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                                    echo "<br> - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"];
                                }
                                ?>
                                </td>
                                <td><? echo $partida["talla"]; ?></td>
                                <td><? echo $partida["color"]; ?></td>
                                <td>$<? echo number_format($precio,2); ?></td>
                                
                                <td>$<? echo number_format($precio*($partida["descuento"]/100),2) ?></td>
                                <td>$<? echo number_format($totalp,2); ?></td>

                            </tr>
                            <?
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <br>
                <?
                }

                $pagos = mysqli_query($con,"select * from tformaspagoticket where idticket='".$_GET["idticket"]."' order by idformapagoticket desc");
                $link = mysqli_num_rows(mysqli_query($con,"select * from tformaspagoticket where idticket='".$_GET["idticket"]."' and idformapago in (5,6,7) and archivo!=''"))>0;
                ?>
                <div class="row">
                    <div class="col-xs-12 col-md-4 offset-md-8">
                        <div class="table-responsive">
                            <?
                            if (mysqli_num_rows($pagos)>0) {
                                ?>
                                <table class="table m-0 table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Forma de Pago</th>
                                            <th>Monto</th>
                                            <?
                                            if ($link) {
                                                ?>
                                                <th>Enlace documento</th>
                                                <?
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?
                                        while ($pago = mysqli_fetch_assoc($pagos)) {
                                            ?>
                                            <tr>
                                                <? 
                                                $formapago = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatformaspago where idformapago='".$pago["idformapago"]."'")); 
                                                ?>
                                                <td><? echo $formapago["nombre"]; ?></td>
                                                <td>$<? echo number_format($pago["monto"],2); ?></td>
                                                <?
                                                if (in_array($pago["idformapago"],array(5,6,7)) && $pago["archivo"]!="") {
                                                    ?>
                                                    <td><a href="/imagenes/depositos/<? echo $_GET["idticket"]; ?>/<? echo $pago["archivo"]; ?>" target="_blank">Ver</a></td>
                                                    <?
                                                }
                                                ?>
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

                <?
                // si no hay devoluciones, no se muestra la tabla
                $partidas = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and idcuentaproducto in (select idcuentaproducto from tdevoluciones where idcuenta = '".$_GET["idcuenta"]."')");
                if(mysqli_num_rows($partidas)>0){
                    ?>
                    <b>DEVOLUCIONES</b><br><br>
                    <div class="table-responsive">
                        <table class="table m-0">
                            <thead>
                                <tr>
                                    <th width="30">Cant.</th>
                                    <th>Producto</th>
                                    <th width="100">P.U.</th>
                                    <th width="100">Descuento</th>
                                    <th width="100">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $subtotal = 0;
                                while($partida = mysqli_fetch_assoc($partidas)){
                                    // calcular y guardar subtotal, iva y total de cada partida
                                    // se agrega costo adicional por cada personalizacion seleccionada

                                    $precio = $partida["total"]/$partida["cantidad"];
                                    $cantidad = $partida["cantidad"];
                                    $subtotalp = $partida["subtotal"];
                                    $ivap = $partida["iva"];
                                    $totalp = $partida["total"];

                                    $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
                                    $subtotal += (float)($partida["cantidad"]*($precio*((100-$partida["descuento"])/100)/1.08));
                                ?>
                                <tr id="trpartida<? echo $partida["idcuentaproducto"]; ?>">
                                    <td align="center"><? echo $partida["cantidad"]; ?></td>
                                    <td>
                                    <?
                                    echo $producto["nombre"];

                                    // personalizaciones
                                    $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
                                    while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                                        $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                                        echo "<br> - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"];
                                    }
                                    ?>
                                    </td>
                                    <!-- <td>$<? echo number_format($producto["precio"]*((100-$partida["descuento"])/100),2); ?></td> -->
                                    <td>$<? echo number_format($precio,2); ?></td>
                                    
                                    <td>$<? echo number_format($precio*($partida["descuento"]/100),2) ?></td>
                                    <td>$<? echo number_format($totalp,2); ?></td>

                                </tr>
                                <?
                                }
                                // $iva = ($subtotal) * 0.08;
                                // $total = (float)($subtotal) + (float)$iva;
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?
                }
                ?>
            </div>

	</div>
</div>