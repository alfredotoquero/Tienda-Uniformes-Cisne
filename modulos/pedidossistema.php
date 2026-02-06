<?
if ($_POST["accion"] == "abonar") {
	if ($_SESSION["authToken"] == $_POST["authToken"]) {
		$idpedido = $_POST["idpedido"];
		$vendedor = mysqli_fetch_assoc(mysqli_query($con, "select * from tvendedores where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'"));
		$corte = mysqli_fetch_assoc(mysqli_query($con, "select * from tcortessucursales where idsucursal = '" . $vendedor["idsucursal"] . "' and status='A'"));

		// obtener el folio de la tabla de sucursales, aumentarle 1 y actualizarlo en la tabla de sucursales
		$folio = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["folio"];
		$abono = (float) str_replace(",", "", $_POST["abono"]);
		// tapartadospagos
		if (mysqli_query($con, "insert into ttickets (idpedido,idsucursal,idcorte,idvendedor,folio,total,fecha,status,notas) values ('" . $idpedido . "','" . $vendedor["idsucursal"] . "','" . $corte["idcorte"] . "','" . $vendedor["idvendedor"] . "','" . $folio . "','" . ($abono) . "','" . date("Y-m-d H:i:s") . "','A','" . $_POST["notas"] . "')")) {
			$abonoinsertado = true;

			$idticket = mysqli_insert_id($con);

			mysqli_query($con, "update tsucursales set folio=folio+1 where idsucursal='" . $vendedor["idsucursal"] . "'");

			$acumulado = 0;
			// pasar los pagos de la tabla de pagos temporal a la permanente (tformaspagoticket)
			$pagostmp = mysqli_query($con, "select idformapago,sum(monto) as monto from tformaspagotickettmp where idvendedor = '" . $vendedor["idvendedor"] . "' group by idformapago order by idformapago desc");
			while ($pagotmp = mysqli_fetch_assoc($pagostmp)) {
				$pesos = mysqli_fetch_assoc(mysqli_query($con, "select * from tcatformaspago where idformapago='" . $pagotmp["idformapago"] . "'"))["pesos"];
				// si el acumulado + el monto supera la cantidad a cobrar (en este caso, el abono), se reducirá el monto. NOTA: Si esto pasa, siempre será en la ultima iteración del ciclo
				$acumulado += $pagotmp["monto"] * $pesos;
				$monto = $pagotmp["monto"];
				if ($acumulado > $abono) {
					$monto -= ($acumulado - $abono) / $pesos;
				}

				mysqli_query($con, "insert into tformaspagoticket (idticket,idvendedor,idformapago,monto,montorecibido) values ('" . $idticket . "','" . $vendedor["idvendedor"] . "','" . $pagotmp["idformapago"] . "','" . $monto . "','" . $pagotmp["monto"] . "')");

				mysqli_query($con, "insert into tformaspagopedido (idpedido,idvendedor,idformapago,monto,montorecibido,fecha) values ('" . $idpedido . "','" . $vendedor["idvendedor"] . "','" . $pagotmp["idformapago"] . "','" . $monto . "','" . $pagotmp["monto"] . "','" . date("Y-m-d H:i:s") . "')");
			}

			mysqli_query($con, "update tpedidos set abonado = abonado + " . $abono . " where idpedido='" . $idpedido . "'");

			// activar el pedido al abonar
			if (mysqli_num_rows(mysqli_query($con, "select * from tpedidos where pendiente=0 and idpedido='" . $idpedido . "'")) > 0) {
				mysqli_query($con, "update tpedidos set pendiente=1 where idpedido='" . $idpedido . "'");
			}

			$copiasticket = 2;
			if (mysqli_num_rows(mysqli_query($con, "select * from tpedidos where idpedido='" . $idpedido . "' and total=abonado")) > 0) {
				mysqli_query($con, "update tpedidos set statuspago=1 where idpedido='" . $idpedido . "'");
				$copiasticket = 1;
			}

			mysqli_query($con, "insert into tticketspedidos (idvendedor,idpedido,total,fecha,status) values ('" . $vendedor["idvendedor"] . "','" . $idpedido . "','" . $abono . "','" . date("Y-m-d H:i:s") . "','A')");
			?>
			<script>
				$(document).ready(function (e) {
					imprimirTicket(<?php echo $idticket; ?>, <?php echo $_POST["cambio"]; ?>, 4,<? echo $copiasticket; ?>);
				});
			</script>
			<?
		} else {
			$abonoinsertado = false;
		}
	} else {
		$abonoinsertado = false;
	}
}

$tipocambio = mysqli_fetch_assoc(mysqli_query($con, "select pesos from tcatformaspago where idformapago = 2"))["pesos"];

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));
?>

<script>
	function abonar(idpedido, restante, total) {
		$("#restante").val(restante);
		$("#total").val(total);
		$("#idpedido").val(idpedido);
		Swal.fire({
			title: 'Ingresa un abono',
			// input: 'text',
			html: '<input type="text" id="txtAbono" name="txtAbono" class="form-control" onkeypress="validate(event);">',
			inputAttributes: {
				autocapitalize: 'off'
			},
			showCancelButton: true,
			confirmButtonText: 'Guardar',
			closeOnConfirm: false,
			allowOutsideClick: false,
			focusConfirm: false,
			preConfirm: () => { return validarAbono($("#txtAbono").val(), $("#restante").val()) }
		}).then((result) => {
			if (result.value) {
				$("#abono").val($("#txtAbono").val());
				cobrar();
			}
		});
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
		if (!regex.test(key)) {
			theEvent.returnValue = false;
			if (theEvent.preventDefault) theEvent.preventDefault();
		}

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

	function cobrar() {
		var total = $("#abono").val();
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
				<label for="" style="float:right;">Total USD: $' + formatMoney(total /<? echo $tipocambio; ?>) + '</label><br> \
		</div>\
		</div> \
		<div class="row"> \
			<div class="col">\
				<label for="" style="float:right;">Saldo: <span id="lblRestante">$' + formatMoney(total) + '</span></label><br> \
			</div>\
		</div> \
		<div class="row"> \
			<div class="col">\
				<label for="" style="float:right;">Saldo USD: <span id="lblRestanteUSD">$' + formatMoney(total /<? echo $tipocambio; ?>) + '</span></label><br>\
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
				<select name="slcFormaPago" id="slcFormaPago" class="form-control" onChange="$(\'#txtMonto\').focus()" tabindex=2>\
					<?
					$result = mysqli_query($con, "select * from tcatformaspago");
					while ($row = mysqli_fetch_array($result)) {
						echo '<option value="' . $row['idformapago'] . '">' . $row['nombre'] . '</option>';
					}
					?> \
				</select >\
			</div >\
		<div class="col-6">\
			<input type="text" name="txtMonto" id="txtMonto" class="form-control" placeholder="Monto" onkeypress="validate2(event);" tabindex=1>\
		</div>\
		</div >\
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
		preConfirm: () => { return validarCobro(total) }
		}).then((result) => {
		if (result.value) {
			$("#formPedido").submit();
		}
	});
	cargarPagos();
}

// al validar el cobro, se debe verificar no haya pagos de más y que los pagos de tarjeta no excedan lo total a pagar
function validarCobro(total) {
	var correcto = false;
	var num = $("#lblRestante").html().replace(/[^\d.]/g, "");
	if (num!="0.00") {
		alert("No se ha cubierto el total a pagar");
	}else{
		$.ajax({
			type: "POST",
			url: "/modulos/puntoventa/validarpagos.php",
			data: "total=" + total,
			async: false,
			success: function (data){
				if (data!="OK") {
					if (data=="ERROR1") {
						alert("ATENCION: Los pagos de tarjeta exceden el total");
					}else if (data=="ERROR2"){
						alert("ATENCION: Hay pagos de más");
					}
				}else{
					correcto = true;
				}
			}
		});

	}
	return correcto;
}

function agregarPago() {
	// validar campos
	if ($("#slcFormaPago").val()=="0") {
		alert("ATENCION: debes seleccionar una forma de pago");
		$("#slcFormaPago").focus();
	} else if ($("#txtMonto").val()==""){
		alert("ATENCION: debes escribir un monto");
		$("#txtMonto").focus();
	}else{
		$.ajax({
			type:"POST",
			url:"/modulos/puntoventa/listaPagos.php",
			data: "accion=agregar&formapago=" + $("#slcFormaPago").val() + "&monto=" + $("#txtMonto").val(),
			success: function(data){
				$("#listaPagos").html(data);
				$("#txtMonto").val("");
				$("#txtMonto").focus();
				$("#slcFormaPago").val("1");
			}
		});
	}
}

function eliminarPago(idpartida) {
	$.ajax({
		type:"POST",
		url:"/modulos/puntoventa/listaPagos.php",
		data: "accion=eliminar&idpartida=" + idpartida,
		success: function(data){
			$("#listaPagos").html(data);
		}
	});
}

function cargarPagos() {
	$.ajax({
		type:"POST",
		url:"/modulos/puntoventa/listaPagos.php",
		data: "accion=mostrar",
		success: function(data){
			$("#listaPagos").html(data);
		}
	});
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

function validate2(evt) {
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

<?
if (isset($abonoinsertado)) {
	if ($abonoinsertado) {
		?>
																		Swal.fire({
																			title: 'Cambio',
																			html: '\
																			MXN: <label for="">$' + <? echo number_format($_POST["cambio"], 2) ?> + '</label><br>\
																			USD: <label for="">$' + <? echo number_format($_POST["cambio"] / $tipocambio, 2) ?>  + '</label>\
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
																		Swal.fire("Error","Ocurrió un error al registrar el cobro","error");
																		<?
	}

}
?>

$(document).ready(function(e){
	if($('#txtFechaInicial, #txtFechaFinal').length){
		// check if element is available to bind ITS ONLY ON HOMEPAGE
		var currentDate = moment().format("YYYY-MM-DD");

		$('#txtFechaInicial, #txtFechaFinal').daterangepicker({
				locale: {
							format: 'YYYY-MM-DD'
				},
				opens: "left",
				"alwaysShowCalendars": true,
				// "minDate": currentDate,
				// "maxDate": moment().add('months', 1),
				autoApply: true,
				autoUpdateInput: false,
				isInvalidDate: function(arg){
						// console.log(arg);

						// Prepare the date comparision
						var thisMonth = arg._d.getMonth()+1;   // Months are 0 based
						if (thisMonth<10){
								thisMonth = "0"+thisMonth; // Leading 0
						}
						var thisDate = arg._d.getDate();
						if (thisDate<10){
								thisDate = "0"+thisDate; // Leading 0
						}
						var thisYear = arg._d.getYear()+1900;   // Years are 1900 based

						var thisCompare = thisMonth +"/"+ thisDate +"/"+ thisYear;
						// console.log(thisCompare);

						// if($.inArray(thisCompare,array)!=-1){
					// 		// console.log("      ^--------- DATE FOUND HERE");
					// 		return true;
					// }
				}
		}, function(start, end, label) {
			// console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
			// Lets update the fields manually this event fires on selection of range
			var selectedStartDate = start.format('YYYY-MM-DD'); // selected start
				var selectedEndDate = end.format('YYYY-MM-DD'); // selected end

				$checkinInput = $('#txtFechaInicial');
				$checkoutInput = $('#txtFechaFinal');

				// Updating Fields with selected dates
				$checkinInput.val(selectedStartDate);
				$checkoutInput.val(selectedEndDate);

				// Setting the Selection of dates on calender on CHECKOUT FIELD (To get this it must be binded by Ids not Calss)
				var checkOutPicker = $checkoutInput.data('daterangepicker');
				checkOutPicker.setStartDate(selectedStartDate);
				checkOutPicker.setEndDate(selectedEndDate);

				// Setting the Selection of dates on calender on CHECKIN FIELD (To get this it must be binded by Ids not Calss)
				var checkInPicker = $checkinInput.data('daterangepicker');
				checkInPicker.setStartDate(selectedStartDate);
				checkInPicker.setEndDate(selectedEndDate);

		});
	} // End Daterange Picker
});
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
		<div class="navbar-text nav-title flex" id="pageTitle">Pedidos Sistema</div>
	</div>
</div>

<form action="" name="formPedido" id="formPedido" method="post">
	<input type="hidden" name="accion" id="accion" value="abonar">
	<input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
	<input type="hidden" name="total" id="total" value="">
	<input type="hidden" name="restante" id="restante" value="">
	<input type="hidden" name="abono" id="abono" value="">
	<input type="hidden" name="cambio" id="cambio">
	<input type="hidden" name="idpedido" id="idpedido">
	<input type="hidden" name="notas" id="notas">
</form>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<form class="" action="" method="post" name="form" id="form">
				<div class="row" style="margin-bottom: 20px;">
					<div class="col-12 col-sm-3">
						<input type="text" name="txtBusqueda" id="txtBusqueda" autocomplete="off"
							value="<? echo $_POST["txtBusqueda"]; ?>" class="form-control"
							placeholder="# de Pedido, Cliente, Contacto o Usuario">
					</div>
					<div class="col-12 col-sm-2">
						<input type="text" class="form-control" name="txtFechaInicial" id="txtFechaInicial"
							value="<? echo $_POST["txtFechaInicial"]; ?>" placeholder="Fecha Inicial">
					</div>
					<div class="col-12 col-sm-2">
						<input type="text" class="form-control" name="txtFechaFinal" id="txtFechaFinal"
							value="<? echo $_POST["txtFechaFinal"]; ?>" placeholder="Fecha Final">
					</div>
					<div class="col-12 col-sm-1">
						<button type="button" class="btn btn-primary waves-effect waves-light" name="btnFiltrar"
							id="btnFiltrar" onclick="submit();">Filtrar</button>
					</div>
				</div>
			</form>
		</div>

		<div class="box-body" id="listaPedidos">
			<div class="table-responsive">
				<?
				// obtener los pedidos
				if (isset($_POST["txtBusqueda"]) && $_POST["txtBusqueda"] != "") {
					$where .= " and (idpedido like '%" . $_POST["txtBusqueda"] . "%' or cliente like '%" . $_POST["txtBusqueda"] . "%' or usuario like '%" . $_POST["txtBusqueda"] . "%' or contacto like '%" . $_POST["txtBusqueda"] . "%')";
				}
				if (isset($_POST["txtFechaInicial"]) && $_POST["txtFechaInicial"] != "") {
					$where .= " and date(fecha)>='" . $_POST["txtFechaInicial"] . "' and date(fecha)<='" . $_POST["txtFechaFinal"] . "'";
				}
				$pedidos = mysqli_query($con, "select * from vpedidos where idsucursal = '" . $vendedor["idsucursal"] . "' and total > 0 and statuspago = 0 and status = 'A'" . $where . " order by idpedido desc");
				if (mysqli_num_rows($pedidos) > 0) {
					?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th># Pedido</th>
								<th>Cliente</th>
								<th>Vendedor</th>
								<th>Total</th>
								<th>Abonado</th>
								<th>Restante</th>
								<th>Fecha</th>
								<th style="width:50px;"></th>
							</tr>
						</thead>
						<tbody>
							<?
							while ($pedido = mysqli_fetch_assoc($pedidos)) {
								?>
								<tr>
									<td><? echo $pedido["idpedido"]; ?></td>
									<td><? echo $pedido["cliente"]; ?></td>
									<td><? echo $pedido["usuario"]; ?></td>
									<td>$<? echo number_format($pedido["total"], 2); ?></td>
									<td>$<? echo number_format($pedido["abonado"], 2); ?></td>
									<td>$<? echo number_format($pedido["total"] - $pedido["abonado"], 2); ?></td>
									<td><? echo fecha_formateada($pedido["fecha"]); ?></td>
									<td>
										<div class="btn-group dropdown">
											<button type="button" class="btn white" data-toggle="dropdown"
												aria-expanded="false">Opciones <span class="caret"></span></button>
											<ul class="dropdown-menu" x-placement="bottom-start"
												style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
												<a href="javascript:;"
													onClick="fancy('modulos/pedidossistema/pedido.php?idpedido=<? echo $pedido["idpedido"]; ?>',800,500)">
													<li class="dropdown-item">Información</li>
												</a>
												<li class="divider"></li>
												<a href="javascript:;"
													onClick="abonar(<? echo $pedido["idpedido"]; ?>,<? echo ($pedido["total"] - $pedido["abonado"]); ?>,<? echo $pedido["total"]; ?>);">
													<li class="dropdown-item">Abonar</li>
												</a>
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