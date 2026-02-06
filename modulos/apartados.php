<?

if ($_POST["accion"] == "apartar") {
	$idcuenta = $_POST["idcuenta"];
	// $vendedor = mysqli_fetch_assoc(mysqli_query($con, "select * from tvendedores where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'"));
	$corte = mysqli_fetch_assoc(mysqli_query($con, "select * from tcortessucursales where idsucursal = '" . $vendedor["idsucursal"] . "' and status='A'"));

	// // si el vendedor no tiene un corte activo, se inserta uno nuevo (con fondo inicial = 1000, de momento)
	// if (mysqli_num_rows(mysqli_query($con,"select * from tcortessucursales where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and status='A'"))>0) {
	// 	// tiene corte activo
	// 	$idcorte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."' and status='A'"))["idcorte"];
	// }else {
	// 	// no tiene corte activo
	// 	mysqli_query($con,"insert into tcortessucursales (idsucursal,idvendedor,fechainicial,fondoinicial) values ('".$vendedor["idsucursal"]."','".$vendedor["idvendedor"]."','".date("Y-m-d H:i:s")."',1000)");
	// 	$idcorte = mysqli_insert_id($con);
	// }

	// obtener el folio de la tabla de sucursales, aumentarle 1 y actualizarlo en la tabla de sucursales
	$folio = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal='" . $vendedor["idsucursal"] . "'"))["folio"];

	// tapartadospagos
	if (mysqli_query($con, "insert into ttickets (idcuenta,idsucursal,idcorte,idvendedor,folio,total,fecha,status,notas) values ('" . $idcuenta . "','" . $vendedor["idsucursal"] . "','" . $corte["idcorte"] . "','" . $vendedor["idvendedor"] . "','" . $folio . "','" . ($_POST["abono"]) . "','" . date("Y-m-d H:i:s") . "','A','" . $_POST["notas"] . "')")) {
		$abonoinsertado = true;

		$idticket = mysqli_insert_id($con);

		mysqli_query($con, "update tsucursales set folio=folio+1 where idsucursal='" . $vendedor["idsucursal"] . "'");

		mysqli_query($con, "update tcuentas set abonado=abonado+" . $_POST["abono"] . " where idcuenta='" . $idcuenta . "'");

		$acumulado = 0;
		// pasar los pagos de la tabla de pagos temporal a la permanente (tformaspagoticket)
		$pagostmp = mysqli_query($con, "select idformapago,sum(monto) as monto from tformaspagotickettmp where idvendedor = '" . $vendedor["idvendedor"] . "' group by idformapago order by idformapago desc");
		while ($pagotmp = mysqli_fetch_assoc($pagostmp)) {
			$pesos = mysqli_fetch_assoc(mysqli_query($con, "select * from tcatformaspago where idformapago='" . $pagotmp["idformapago"] . "'"))["pesos"];
			// si el acumulado + el monto supera la cantidad a cobrar (en este caso, el abono), se reducirá el monto. NOTA: Si esto pasa, siempre será en la ultima iteración del ciclo
			$acumulado += $pagotmp["monto"] * $pesos;
			$monto = $pagotmp["monto"];
			if ($acumulado > $_POST["abono"]) {
				$monto -= ($acumulado - $_POST["abono"]) / $pesos;
			}

			mysqli_query($con, "insert into tformaspagoticket (idticket,idvendedor,idformapago,monto,montorecibido,archivo) values ('" . $idticket . "','" . $vendedor["idvendedor"] . "','" . $pagotmp["idformapago"] . "','" . $monto . "','" . $pagotmp["monto"] . "','".$pagotmp["archivo"]."')");
		}

		// // subir el archivo del deposito 
		// if ($_FILES['flComprobante']["tmp_name"] != NULL) {

		// 	$direccion = "./imagenes/depositos/" . $idticket . "/";

		// 	mkdir($direccion, 0777, true);

		// 	$direccion .= basename($_FILES["flComprobante"]["name"]);

		// 	move_uploaded_file($_FILES['flComprobante']['tmp_name'], $direccion);
		// }

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

?>
		<script>
			$(document).ready(function(e) {
				imprimirTicket(<?php echo $idticket; ?>, <?php echo $_POST["cambio"]; ?>, 3, 2);
			});
		</script>
<?
	} else {
		$abonoinsertado = false;
	}
}

if ($_GET["accion"] == "cancelar") {
	// regresar existencias
	$partidas = mysqli_query($con, "select * from trcuentaproductos where idcuenta='" . $_GET["idcuenta"] . "' and idproducto in (select idproducto from tproductos where tipo!='S')");
	$idalmacen = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "')"))["idalmacen"];
	while ($partida = mysqli_fetch_assoc($partidas)) {
		mysqli_query($con, "update tproductoexistencias set existencia=existencia+" . $partida["cantidad"] . " where idproducto='" . $partida["idproducto"] . "' and idcolor='" . $partida["idcolor"] . "' and idtalla='" . $partida["idtalla"] . "' and idalmacen='" . $idalmacen . "'");

		// kardex
		$existencias = mysqli_fetch_assoc(mysqli_query($con, "select sum(existencia) as existencia from tproductoexistencias where idproducto = '" . $partida["idproducto"] . "' and idcolor = '" . $partida["idcolor"] . "' and idtalla = '" . $partida["idtalla"] . "'"))["existencia"];

		$existenciasalmacen = mysqli_fetch_assoc(mysqli_query($con, "select existencia from tproductoexistencias where idproducto = '" . $partida["idproducto"] . "' and idcolor = '" . $partida["idcolor"] . "' and idtalla = '" . $partida["idtalla"] . "' and idalmacen = '" . $idalmacen . "'"))["existencia"];

		mysqli_query($con, "insert into tproductomovimientos (idusuario, idproducto, idtalla, idcolor, idalmacen, origenmovimiento, tipomovimiento, idmovimiento, cantidad, existencias, existenciasalmacen, fecha) values ( '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "', '" . $partida["idproducto"] . "', '" . $partida["idtalla"] . "', '" . $partida["idcolor"] . "', '" . $idalmacen . "', 'A', 'E', '" . $_GET["idcuenta"] . "', '" . $partida["cantidad"] . "', '" . $existencias . "', '" . $existenciasalmacen . "', '" . $fecha . "' )");
	}

	mysqli_query($con, "update tcuentas set status='C' where idcuenta='" . $_GET["idcuenta"] . "'");
	mysqli_query($con, "update trcuentaproductos set status='C' where idcuenta='" . $_GET["idcuenta"] . "'");
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));

$apartados = mysqli_query($con, "select * from tcuentas where tipocuenta='A' and status='P' and idsucursal = '" . $vendedor["idsucursal"] . "' order by fecha desc");
?>

<script>
	$(document).ready(function(e) {
		$("#txtFiltroNombre").keyup(function() {
			$.ajax({
				type: "POST",
				url: "/modulos/apartados/filtrarapartados.php",
				data: "accion=filtrar&nombre=" + $(this).val() + "&vigencia=" + $("#valorVigencia").val(),
				success: function(data) {
					$("#listaApartados").html(data);
				}
			});
		});
	});

	function filtrarPorVigencia(valor) {
		$("#valorVigencia").val(valor);
		$.ajax({
			type: "POST",
			url: "/modulos/apartados/filtrarapartados.php",
			data: "accion=filtrar&vigencia=" + valor + "&nombre=" + $("#txtFiltroNombre").val(),
			success: function(data) {
				$("#listaApartados").html(data);
			}
		});
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
					<label for="" style="float:right;">Total USD: $' + formatMoney(total / 18) + '</label><br> \
			</div>\
			</div> \
			<div class="row"> \
				<div class="col">\
					<label for="" style="float:right;">Saldo: <span id="lblRestante">$' + formatMoney(total) + '</span></label><br> \
				</div>\
			</div> \
			<div class="row"> \
				<div class="col">\
					<label for="" style="float:right;">Saldo USD: <span id="lblRestanteUSD">$' + formatMoney(total / 18) + '</span></label><br>\
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
						echo '<option value="' . $row['idformapago'] . '">' . $row['nombre'] . '</option>';
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

				$("#formApartado").submit();
			}
		});
		cargarPagos();
	}
	// <a href="javascript:;" class="btn btn-primary waves-effect waves-light" onClick="agregarPago();">Pagar</a>\

	// al validar el cobro, se debe verificar no haya pagos de más y que los pagos de tarjeta no excedan lo total a pagar
	function validarCobro(total) {
		var correcto = false;
		var num = $("#lblRestante").html().replace(/^\D+/g, "");
		if (num != "0.00") {
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
				data: "accion=agregar&formapago=" + $("#slcFormaPago").val() + "&monto=" + $("#txtMonto").val(),
				success: function(data) {
					var fileInput = $("#flComprobante");
					var cloneInput = fileInput.clone();
					cloneInput.attr('name', "flComprobante" + i);
					cloneInput.attr('id', "flComprobante" + i);
					$("#formApartado").append(cloneInput);
					$("#flComprobante" + i).hide();

					console.log("name:" + $("#flComprobante" + i).attr("name") + " nombre archivo: " + $("#flComprobante" + i).val().split(/(\\|\/)/g).pop());

					$("#listaPagos").html(data);
					$("#txtMonto").val("");
					$("#txtMonto").focus();
					$("#flComprobante").val("");
					$("#flComprobante").hide();
					$("#flComprobante").prop("disabled", true);
					$("#slcFormaPago").val("1");

					i += 1;
				}
			});
		}
	}

	// function agregarCodigo() {
	// 	// validar campos
	// 	if ($("#slcFormaPago").val() == "0") {
	// 		alert("ATENCION: debes seleccionar una forma de pago");
	// 		$("#slcFormaPago").focus();
	// 	} else if ($("#txtCodigo").val() == "") {
	// 		alert("ATENCION: debes escribir un código");
	// 		$("#txtCodigo").focus();
	// 	} else {
	// 		$.ajax({
	// 			type: "POST",
	// 			url: "/modulos/puntoventa/listaPagos.php",
	// 			data: "accion=agregar&formapago=" + $("#slcFormaPago").val() + "&codigo=" + $("#txtCodigo").val(),
	// 			success: function(data) {
	// 				$("#listaPagos").html(data);
	// 				$("#txtCodigo").val("");
	// 				$("#txtCodigo").hide();
	// 				$("#txtMonto").show();
	// 				$("#txtMonto").focus();
	// 				$("#slcFormaPago").val("1");
	// 			}
	// 		});
	// 	}
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

	function corte() {
		return 0;
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

	// function verificarCodigo(evt) {
	// 	var theEvent = evt || window.event;

	// 	// Handle paste
	// 	if (theEvent.type === 'paste') {
	// 		key = event.clipboardData.getData('text/plain');
	// 	} else {
	// 		// Handle key press
	// 		var key = theEvent.keyCode || theEvent.which;
	// 		key = String.fromCharCode(key);
	// 	}
	// 	var regex = /[A-Za-z0-9 -]|\./;
	// 	if (!regex.test(key)) {
	// 		theEvent.returnValue = false;
	// 		if (theEvent.preventDefault) theEvent.preventDefault();
	// 	}

	// 	// enter
	// 	if (theEvent.keyCode == 13) {
	// 		agregarCodigo();
	// 	}
	// }

	function abonar(idcuenta, restante, total) {
		$("#restante").val(restante);
		$("#total").val(total);
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
			preConfirm: () => {
				return validarAbono($("#txtAbono").val(), $("#restante").val())
			}
		}).then((result) => {
			if (result.value) {
				$("#abono").val($("#txtAbono").val());
				cobrar();
			}
		});
		$("#idcuenta").val(idcuenta);
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

	<?
	if (isset($abonoinsertado)) {
		if ($abonoinsertado) {
	?>
			Swal.fire({
				title: 'Cambio',
				html: '\
            MXN: <label for="">$' + <? echo number_format($_POST["cambio"], 2) ?> + '</label><br>\
            USD: <label for="">$' + <? echo number_format($_POST["cambio"] / 18, 2) ?> + '</label>\
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
			Swal.fire("Error", "Ocurrió un error al registrar el cobro", "error");
	<?
		}
	}
	?>

	function toggleDetalle(idapartado) {
		if ($("#divApartado" + idapartado).is(":visible")) {
			$("#divApartado" + idapartado).hide();
			$("#btnApartado" + idapartado).attr("css", "fas fa-plus");
		} else {
			$("#divApartado" + idapartado).show();
			$("#btnApartado" + idapartado).attr("css", "fas fa-minus");
		}
	}

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
		<div class="navbar-text nav-title flex" id="pageTitle">Apartados</div>
	</div>
</div>

<form action="" name="formApartado" id="formApartado" method="post" enctype="multipart/form-data">
	<input type="hidden" name="accion" id="accion" value="apartar">
	<input type="hidden" name="total" id="total" value="">
	<input type="hidden" name="restante" id="restante" value="">
	<input type="hidden" name="abono" id="abono" value="">
	<input type="hidden" name="cambio" id="cambio">
	<input type="hidden" name="idcuenta" id="idcuenta">
	<input type="hidden" name="notas" id="notas">
	<div id="divComprobante" style="display:none;">

	</div>
</form>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<div class="row">
				<div class="col-3">
					<input type="text" id="txtFiltroNombre" name="txtFiltroNombre" class="form-control" placeholder="Escribe un nombre de apartado">
				</div>
				<div class="col-9">
					<button type="button" class="btn btn-primary pull-right" onclick="filtrarPorVigencia('E');">Cancelados</button>
					<button type="button" class="btn btn-primary pull-right" onclick="filtrarPorVigencia('E');" style="margin-right:30px;">Entregados</button>
					<button type="button" class="btn btn-primary pull-right" onclick="filtrarPorVigencia('P');" style="margin-right:30px;">Pendientes</button>
					<input type="hidden" name="valorVigencia" id="valorVigencia" value="P">
				</div>
			</div>
		</div>

		<div class="box-body" id="listaApartados">
			<div class="table-responsive">
				<?
				if (mysqli_num_rows($apartados) > 0) {
				?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th>Nombre</th>
								<th>Total</th>
								<th>Abonado</th>
								<th>Restante</th>
								<th>Fecha</th>
								<th style="width:25px;"></th>
								<th style="width:50px;"></th>
							</tr>
						</thead>
						<tbody>
							<?
							while ($apartado = mysqli_fetch_assoc($apartados)) {
								$restante = $apartado["total"] - $apartado["abonado"];
							?>
								<tr>
									<td><? echo $apartado["nombrecuenta"]; ?></td>
									<td>$<? echo number_format($apartado["total"], 2); ?></td>
									<?
									$abonado = $apartado["abonado"];
									$restante = $apartado["total"] - $abonado;
									?>
									<td>$<? echo number_format($abonado, 2); ?></td>
									<td>$<? echo number_format($restante, 2); ?></td>
									<td><? echo fecha_formateada($apartado["fecha"]); ?></td>
									<td>
										<a href="javascript:;" class="btn white" style="margin-right:15px;" onclick="toggleDetalle(<? echo $apartado["idcuenta"]; ?>)"><i id="btnApartado<? echo $apartado["idcuenta"]; ?>" class="fas fa-plus"></i></a>
									</td>
									<td>
										<div class="btn-group dropdown">
											<button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
											<ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
												<a href="?modulo1=apartados&modulo2=ver&idcuenta=<? echo $apartado["idcuenta"]; ?>">
													<li class="dropdown-item">Ver desglose</li>
												</a>
												<!-- <a href="?modulo1=apartados&modulo2=pagos&idcuenta=<? echo $apartado["idcuenta"]; ?>"><li class="dropdown-item">Ver historial de pagos</li></a> -->
												<? if ($apartado["abonado"] != $apartado["total"]) { ?>
													<li class="divider"></li>
													<a href="javascript:;" onClick="abonar(<? echo $apartado["idcuenta"]; ?>,<? echo $restante; ?>,<? echo $apartado["total"]; ?>);">
														<li class="dropdown-item">Abonar</li>
													</a>
												<? } ?>
												<? if ($apartado["status"] != "C" and $restante != 0) { ?>
													<li class="divider"></li>
													<a href="?modulo1=apartados&accion=cancelar&idcuenta=<? echo $apartado["idcuenta"]; ?>" onClick="return confirm('ATENCION: ¿Estás seguro de que quieres cancelar este apartado?');">
														<li class="dropdown-item">Cancelar</li>
													</a>
												<? } ?>
											</ul>
										</div>
									</td>
								</tr>
								<tr id="divApartado<? echo $apartado["idcuenta"]; ?>" style="display:none;">
									<td colspan="7">
										<?php
										$apartadosproductos = mysqli_query($con, "select * from trcuentaproductos where idcuenta='" . $apartado["idcuenta"] . "'");
										?>
										<table class="table table-striped b-t">
											<thead>
												<tr>
													<th>Cantidad</th>
													<th>Nombre</th>
													<th>Total</th>
												</tr>
											</thead>
											<tbody>
												<?
												$abonado = $apartado["abonado"];
												$dineroproductosentregados = mysqli_fetch_assoc(mysqli_query($con, "select sum(total) as total from trcuentaproductos where idcuenta='" . $apartado["idcuenta"] . "' and status='E'"))["total"];
												while ($apartadosproducto = mysqli_fetch_assoc($apartadosproductos)) {
												?>
													<tr>
														<? $nombre = mysqli_fetch_assoc(mysqli_query($con, "select * from tproductos where idproducto='" . $apartadosproducto["idproducto"] . "'"))["nombre"]; ?>
														<td><? echo $apartadosproducto["cantidad"]; ?></td>
														<td><? echo $nombre; ?></td>
														<td>$<? echo number_format($apartadosproducto["total"], 2); ?></td>
													</tr>
												<?
												}
												?>
											</tbody>
										</table>
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