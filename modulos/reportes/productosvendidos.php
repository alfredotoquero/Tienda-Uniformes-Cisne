<?php
if($_POST["accion"]=="busqueda"){
    $fecha = explode(" - ",$_POST["txtFechas"]);
	$fechainicial = $fecha[0];
	$fechafinal = $fecha[1];
}else{
    $fechainicial = date("Y-m-d");
    $fechafinal = date("Y-m-d");
}
$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
?>
<script>
$(document).ready(function(e){
    //Date range picker
	$('#txtFechas').daterangepicker({
		buttonClasses: ['btn', 'btn-sm'],
		applyClass: 'btn-default',
		cancelClass: 'btn-primary',
		locale: {
            format: 'YYYY-MM-DD'
        }
	});
});
</script>
<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
		<!-- btn to toggle sidenav on small screen -->
		<a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
		</a>
		<!-- Page title -->
		<div class="navbar-text nav-title flex" id="pageTitle">Productos Vendidos</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<div class="row">
			</div>
		</div>

		<div class="box-body">
            <form name="formBusqueda" id="formBusqueda" method="post">
            <input type="hidden" name="accion" value="busqueda">
            <div class="row mb-4">
                <div class="col-xs-12 col-md-1 offset-md-6">Periodo:</div>
                <div class="col-xs-12 col-md-3">
                    <input type="text" name="txtFechas" id="txtFechas" class="form-control" value="<? echo $fechainicial." - ".$fechafinal; ?>">
                </div>
                <div class="col-xs-12 col-md-2">
                    <a href="javascript:;" onClick="formBusqueda.submit();" class="btn btn-primary btn-block">Filtrar</a>
                </div>
            </div>
            </form>
            <div class="table-responsive">
                <?
                $productosvendidos = mysqli_query($con,"select producto,color,talla,sum(cantidad) as cantidad,sum(total) as total from vproductosvendidos where idsucursal = '".$vendedor["idsucursal"]."' and (date(fecha) >= '".$fechainicial."' and date(fecha) <= '".$fechafinal."') group by idproducto,idcolor,idtalla order by producto,color,talla");
				if (mysqli_num_rows($productosvendidos)>0) {
					?>
					<table class="table table-striped b-t">
						<thead>
							<tr>
								<th>Producto</th>
								<th>Color</th>
								<th>Talla</th>
								<th>Cantidad</th>
								<th>Total</th>
							</tr>
						</thead>
						<tbody>
							<?
							$total = 0;
							while ($productovendido = mysqli_fetch_assoc($productosvendidos)) {
								?>
								<tr>
                                    <td><? echo $productovendido["producto"]; ?></td>
                                    <td><? echo $productovendido["color"]; ?></td>
                                    <td><? echo $productovendido["talla"]; ?></td>
                                    <td><? echo $productovendido["cantidad"]; ?></td>
                                    <td>$<? echo number_format($productovendido["total"],2); ?></td>
								</tr>
								<?
								$total += $productovendido["total"];
							}
							?>
						</tbody>
						<tfoot>
							<tr>
								<th colspan="4"></th>
								<th>$<? echo number_format($total,2); ?></th>
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