<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

if ($_POST["accion"]=="entregar") {
	mysqli_query($con,"update trcuentaproductos set status='E' where idcuenta='".$_GET["idcuenta"]."'");
	mysqli_query($con,"update tcuentas set status='E' where idcuenta='".$_GET["idcuenta"]."'");
}

$apartadosproductos = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."'");

$pagos = mysqli_query($con,"select * from ttickets where idcuenta='".$_GET["idcuenta"]."' order by idticket desc");

?>

<script>
function entregarProductos(){
	Swal.fire({
		title: "ATENCION: ",
		text: "¿Estás seguro de que quieres entregar los productos?",
		type: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		cancelButtonText: "No",
		confirmButtonText: "Sí"
		}).then((result) => {
		if (result.value) {
			Swal.fire({
				title: 'Entrega',
				html: 'Productos entregados',
				type: "success",
				inputAttributes: {
					autocapitalize: 'off'
				},
				showCancelButton: false,
				confirmButtonText: 'Aceptar',
				closeOnConfirm: false,
				allowOutsideClick: false
			}).then((result) => {
				// $("#accion").val("cobrar");
				$("#formApartado").submit();
			});
		}
	});
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
	  <div class="navbar-text nav-title flex" id="pageTitle">Apartados - Desglose</div>
	</div>
</div>

<form name="formApartado" id="formApartado" action="" method="post">
	<input type="hidden" name="accion" id="accion" value="entregar">
</form>

<div class="padding">
	<div class="box">
		<div class="box-header">
            <div class="p-2">
				<div class="row">
					<div class="col">
						<?
						// $apartado = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"));
						$apartado = mysqli_fetch_assoc(mysqli_query($con,"select * from ttickets where idcuenta='".$_GET["idcuenta"]."' order by idticket limit 1"));
						echo "<b># Ticket:</b> " . $apartado["folio"] . "<br><b>Fecha:</b> " . fecha_formateada2($apartado["fecha"]) . "<br><b>Status:</b> " . ($apartado["status"]=="E" ? "Entregado" : "Sin Entregar");
						?>
					</div>
					<div class="col text-right" >
                        <a href="?modulo1=apartados" class="btn btn-danger waves-effect pull-right">Regresar</a>
						<?
						if (mysqli_num_rows(mysqli_query($con,"select * from tcuentas where status!='E' and abonado=total and idcuenta='".$_GET["idcuenta"]."'"))>0) {
							?>
							<a href="javascript:;" onClick="entregarProductos();" class="btn btn-primary waves-effect waves-light">Entregar Productos</a>&nbsp;&nbsp;
							<?
						}
						?>
                    </div>
                </div>
            </div>
        </div>

		<div class="box-body">
            <div class="table-responsive" id="listaProductos">
				<?
				if (mysqli_num_rows($apartadosproductos)>0) {
					?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th>Cantidad</th>
								<th>Nombre</th>
								<th>Descuento</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							<?
							$abonado = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"))["abonado"];
							$dineroproductosentregados = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from trcuentaproductos where idcuenta='".$_GET["idcuenta"]."' and status='E'"))["total"];
							while ($apartadosproducto = mysqli_fetch_assoc($apartadosproductos)) {
								?>
								<tr>
                                    <? $nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$apartadosproducto["idproducto"]."'"))["nombre"]; ?>
                                    <td><? echo $apartadosproducto["cantidad"]; ?></td>
                                    <td><? echo $nombre; ?></td>
                                    <td><? echo $apartadosproducto["descuento"]; ?>%</td>
                                    <td>$<? echo number_format($apartadosproducto["total"],2); ?></td>
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

<div class="padding">
	<div class="box">
		<div class="box-header">
            <div class="p-2">
				<div class="row">
					<div class="col">
						<center><h2>Historial de Pagos</h2></center>
					</div>
                </div>
            </div>
		</div>

		<div class="box-body">
            <div class="table-responsive">
				<?
				if (mysqli_num_rows($pagos)>0) {
					?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th># Ticket</th>
								<th>Sucursal</th>
								<th>Vendedor</th>
								<th>Notas</th>
								<th width="100">Abono</th>
								<th width="200">Fecha</th>
							</tr>
						</thead>
						<tbody>
							<?
							while ($pago = mysqli_fetch_assoc($pagos)) {
								?>
								<tr>
                                    <? 
                                    $sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$pago["idsucursal"]."'")); 
                                    $vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$pago["idvendedor"]."'")); 
                                    ?>
                                    <td><? echo $pago["folio"]; ?></td>
                                    <td><? echo $sucursal["nombre"]; ?></td>
                                    <td><? echo $vendedor["nombre"]; ?></td>
                                    <td><? echo $pago["notas"]; ?></td>
                                    <td>$<? echo number_format($pago["total"],2); ?></td>
                                    <td><? echo fecha_formateada($pago["fecha"]); ?></td>
								</tr>
								<?
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td><b>Saldo</b></td>
								<?
								$total = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"))["total"];
								$saldo = $total - $abonado;
								?>
								<td>$<? echo number_format($saldo,2); ?></td>
							</tr>
						</tfoot>
					</table>
                    <?
                }
                ?>
			</div>
		</div>
	</div>
</div>
