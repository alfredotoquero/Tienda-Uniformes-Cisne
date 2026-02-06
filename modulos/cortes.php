<?

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$cortes = mysqli_query($con,"select * from tcortessucursales where idsucursal='".$vendedor["idsucursal"]."' order by idcorte desc limit 15");
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
    if( !regex.test(key) ) {
        theEvent.returnValue = false;
        if(theEvent.preventDefault) theEvent.preventDefault();
    }

    // enter
    if (theEvent.keyCode == 13) {
        agregarPago();
    }
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
		<div class="navbar-text nav-title flex" id="pageTitle">Cortes - Sucursal <? echo mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$vendedor["idsucursal"]."'"))["nombre"]; ?></div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<div class="row">
			</div>
		</div>

		<div class="box-body" id="listaCortes">
            <div class="table-responsive">
				<?
				if (mysqli_num_rows($cortes)>0) {
					?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th># Corte</th>
								<th>Vendedor</th>
								<th>Fecha Inicial</th>
								<th>Fondo Inicial</th>
								<th>Ventas</th>
								<th>Devoluciones</th>
								<th>Total</th>
								<th>Status</th>
								<th style="width:50px;"></th>
							</tr>
						</thead>
						<tbody>
							<?
							while ($corte = mysqli_fetch_assoc($cortes)) {
								?>
								<tr id="<? echo $corte["idcorte"]; ?>">
                                    <td><? echo $corte["folio"]; ?></td>
                                    <td><? echo $vendedor["nombre"]; ?></td>
                                    <td><? echo fecha_formateada($corte["fechainicial"]); ?></td>
									<?
									$formaspago = array();
									$fondoinicial = $corte["fondoinicial"];
									if ($corte["status"]=="A") {
										$devoluciones = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as devoluciones from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from tdevoluciones where idcorte='".$corte["idcorte"]."')"))["devoluciones"];
										$ventas = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as ventas from ttickets where idcorte='".$corte["idcorte"]."'"))["ventas"];
										$total = $fondoinicial + $ventas - $devoluciones;
	
										// $efectivo = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=1"))["monto"];
										// $efectivousd = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=2"))["monto"];
										// $tarjeta = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=3"))["monto"];
										// $transferencia = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=5"))["monto"];
										// $cheque = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in p(select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago=6"))["monto"];

										
										$result = mysqli_query($con, "select * from tcatformaspago");
										while ($row = mysqli_fetch_array($result)) {
											$monto = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket in (select idticket from ttickets where idcorte = '".$corte["idcorte"]."') and idformapago='".$row["idformapago"]."'"));
											$formaspago[] = array_merge($row, $monto);
										}
									}else {
										$ventas = $corte["ventas"];
										$total = $corte["fondofinal"];
	
										// $efectivo = $corte["efectivo"];
										// $efectivousd = $corte["efectivousd"];
										// $tarjeta = $corte["tarjeta"];
										// $transferencia = $corte["transferencia"];
										// $cheque = $corte["cheque"];
										$devoluciones = $corte["devoluciones"];
										$ticketfinal = $corte["ticketfinal"];
										$ticketinicial = $corte["ticketinicial"];

										$result = mysqli_query($con,"select a.*, b.total as monto from tcatformaspago a left join tcortesucursal_formaspago b on b.idformapago = a.idformapago and b.idcorte = '".$corte["idcorte"]."'");
										while ($row = mysqli_fetch_array($result)) {
											$formaspago[] = $row;
										}
									}
									?>
                                    <td>$<? echo number_format($fondoinicial,2); ?></td>
									<td>
									<?
										foreach ($formaspago as $formapago) {
											echo $formapago["nombre"] . ": $" . number_format($formapago["monto"],2) . "<br>";
										}
										echo "Total de Ventas: $" . number_format($ventas,2);
									?>
									</td>
                                    <!-- <td><? //echo "Efectivo: $" . number_format($efectivo,2) . "<br>Efectivo USD: $" . number_format($efectivousd,2) . "<br>Tarjeta: $" . number_format($tarjeta,2) . "<br>Transferencia: $" . number_format($transferencia,2) . "<br>Cheque: $" . number_format($cheque,2)."<br>Total de Ventas: $" . number_format($ventas,2); ?></td> -->
                                    <td style="color: red;"><? echo (($devoluciones>0) ? "$" . number_format($devoluciones,2) : ""); ?></td>
                                    <td>$<? echo number_format($total,2); ?></td>
                                    <td><? echo ($corte["status"]=="A" ? "Activo" : ($corte["status"]=="T" ? "Terminado" : "")); ?></td>
									<td>
                                        <div class="btn-group dropdown">
											<button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
											<ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
												<a href="javascript:;" onClick="imprimirCorte(<? echo $corte["idcorte"]; ?>)"><li class="dropdown-item">Imprimir</li></a>
												<a href="?modulo1=cortes&modulo2=detallev&idcorte=<? echo $corte["idcorte"]; ?>"><li class="dropdown-item">Detalle de ventas</li></a>
												<a href="?modulo1=cortes&modulo2=detalleg&idcorte=<? echo $corte["idcorte"]; ?>"><li class="dropdown-item">Detalle de devoluciones</li></a>
												<li class="divider"></li>
												<a href="/modulos/cortes/cortegeneral.php?idcorte=<? echo $corte["idcorte"]; ?>" target="_blank"><li class="dropdown-item">Impresión del Corte</li></a>
												<a href="/modulos/cortes/detallecorte.php?idcorte=<? echo $corte["idcorte"]; ?>" target="_blank"><li class="dropdown-item">Detalle del Corte</li></a>
											</ul>
										</div>
                                        <!-- <a href="?modulo1=cortes&modulo2=detalle&idcorte=<? echo $corte["idcorte"]; ?>" class="btn white">Detalle</a> -->
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
