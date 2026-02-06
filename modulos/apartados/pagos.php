<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));

$pagos = mysqli_query($con,"select * from ttickets where idcuenta='".$_GET["idcuenta"]."' order by idticket desc");
?>

<script>

</script>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Apartados - Historial de pagos</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
            <div class="p-2">
				<div class="row">
					<div class="col">
						<?
						$apartado = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$_GET["idcuenta"]."'"));
						echo "<b># Cuenta:</b> " . $apartado["idcuenta"] . "<br><b>Fecha:</b> " . fecha_formateada2($apartado["fecha"]) . "</b>";
						?>
					</div>
					<div class="col text-right" >
                        <a href="?modulo1=apartados" class="btn btn-danger waves-effect pull-right">Regresar</a>
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
								<th>Sucursal</th>
								<th>Vendedor</th>
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
                                    <td><? echo $sucursal["nombre"]; ?></td>
                                    <td><? echo $vendedor["nombre"]; ?></td>
                                    <td>$<? echo number_format($pago["total"],2); ?></td>
                                    <td><? echo fecha_formateada($pago["fecha"]); ?></td>
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
