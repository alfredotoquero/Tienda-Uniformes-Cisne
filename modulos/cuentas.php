<?
if ($_POST["accion"]=="entregar") {
	mysqli_query($con,"update tcuentas set status = 'E' where idcuenta = '".$_POST["idcuenta"]."'");
}

if ($_POST["accion"]=="cerrar") {
	mysqli_query($con,"update tcuentas set tipocuenta = 'D' where idcuenta = '".$_POST["idcuenta"]."'");
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

// obtener las cuentas guardadas
$cuentas = mysqli_query($con,"select * from tcuentas where tipocuenta='D' and status='P' order by idcuenta desc");
?>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
		<!-- btn to toggle sidenav on small screen -->
		<a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
		</a>
		<!-- Page title -->
		<div class="navbar-text nav-title flex" id="pageTitle">Cuentas Guardadas</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<div class="row">
				<div class="col">
					Lista de las cuentas guardadas
				</div>
			</div>
		</div>

		<div class="box-body" id="listaCuentas">
            <div class="table-responsive">
				<?
				if (mysqli_num_rows($cuentas)>0) {
					?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th># Ticket</th>
								<th>Vendedor</th>
								<th>Sucursal</th>
								<th>Total</th>
								<th>Fecha</th>
								<th style="width:50px;"></th>
							</tr>
						</thead>
						<tbody>
							<?
							while ($cuenta = mysqli_fetch_assoc($cuentas)) {
								?>
								<tr>
									<td><? echo $cuenta["idcuenta"]; ?></td>
									<?
									$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$cuenta["idvendedor"]."'"));
									$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$cuenta["idsucursal"]."'"));
									?>
									<td><? echo $vendedor["nombre"]; ?></td>
									<td><? echo $sucursal["nombre"]; ?></td>
									<td>$<? echo number_format($cuenta["total"],2); ?></td>
									<td><? echo fecha_formateada($cuenta["fecha"]); ?></td>
									<td>
										<div class="btn-group dropdown">
											<button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
											<ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
												<?
												if (mysqli_num_rows(mysqli_query($con,"select * from tcuentas where idcuenta='".$cuenta["idcuenta"]."' and status!='E'"))>0) {
													?>
													<li class="dropdown-item"><a href="?modulo1=cuentas&accion=entregar&idcuenta=<? echo $cuenta["idcuenta"]; ?>">Marcar como "Entregada"</a></li>
													<?
												}
												?>
												<?
												if (mysqli_num_rows(mysqli_query($con,"select * from tcuentas where idcuenta='".$cuenta["idcuenta"]."' and tipocuenta=''"))>0) {
													?>
													<li class="dropdown-item"><a href="?modulo1=cuentas&accion=cerrar&idcuenta=<? echo $cuenta["idcuenta"]; ?>">Marcar como "Cerrada"</a></li>
													<?
												}
												?>
                                                <li class="dropdown-item"><a href="?modulo1=puntoventa&idcuenta=<? echo $cuenta["idcuenta"]; ?>" class="btn white">----</a></li>
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
