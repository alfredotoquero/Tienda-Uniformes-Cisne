<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

// obtener los tickets
// $pedidos = mysqli_query($con,"select * from tcuentas where idcuenta in (select idcuenta from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from trcuentaproductopersonalizados)) order by idcuenta desc");
$pedidos = mysqli_query($con,"select * from ttickets where idcuenta in (select idcuenta from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from trcuentaproductopersonalizados)) order by idticket desc");
?>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
		<!-- btn to toggle sidenav on small screen -->
		<a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
		</a>
		<!-- Page title -->
		<div class="navbar-text nav-title flex" id="pageTitle">Pedidos Personalizados</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<!-- <div class="row">
				<div class="col">
					Lista de los tickets generados
				</div>
			</div> -->
		</div>

		<div class="box-body" id="listaPedidos">
            <div class="table-responsive">
				<?
				if (mysqli_num_rows($pedidos)>0) {
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
							while ($pedido = mysqli_fetch_assoc($pedidos)) {
								?>
								<tr>
									<td><? echo $pedido["idticket"]; ?></td>
									<?
									$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$pedido["idvendedor"]."'"));
									$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal='".$pedido["idsucursal"]."'"));
									?>
									<td><? echo $vendedor["nombre"]; ?></td>
									<td><? echo $sucursal["nombre"]; ?></td>
									<td>$<? echo number_format($pedido["total"],2); ?></td>
									<td><? echo fecha_formateada($pedido["fecha"]); ?></td>
									<td>
										<!-- <div class="btn-group dropdown">
											<button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
											<ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
                                                <li class="dropdown-item"><a href="?modulo1=pedidos&modulo2=ver&idcuenta=<? echo $pedido["idcuenta"]; ?>">Ver desglose</a></li>
											</ul>
										</div> -->
                                                <a href="?modulo1=pedidos&modulo2=ver&idcuenta=<? echo $pedido["idcuenta"]; ?>" class="btn white">Ver desglose</a>
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
