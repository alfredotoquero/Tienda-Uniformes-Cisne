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
	  <div class="navbar-text nav-title flex" id="pageTitle">Detalle de Devoluciones</div>
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
                    <a href="?modulo1=cortes#<? echo $_GET["idcorte"]; ?>" class="btn btn-danger waves-effect pull-right">Regresar</a>
                </div>
            </div>
        </div>

	
        <div class="box-body" id="listaCuentas">
            <div class="table-responsive">
                <?
                // se deben mostrar todas las cosas con respecto a los gastos (por lo pronto, sólo devoluciones)
                // devoluciones hechas durante este corte
                // datos de devoluciones: vendedor total fecha
                $corte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idcorte='".$_GET["idcorte"]."' and idsucursal='".$vendedor["idsucursal"]."'"));
                // $cuentas = mysqli_query($con,"select * from tcuentas where idcuenta in (select idcuenta from tdevoluciones where fecha>='".$corte["fechainicial"]."') order by idcuenta desc");
                // $tickets = mysqli_query($con,"select * from ttickets where idcuenta in (select idcuenta from tdevoluciones where fecha>='".$corte["fechainicial"]."') and idcuenta in (select idcuenta from tcuentas where idcorte='".$_GET["idcorte"]."') order by idcuenta desc");
                $tickets = mysqli_query($con,"select * from ttickets where idcuenta in (select idcuenta from trcuentaproductos where idcuentaproducto in (select idcuentaproducto from tdevoluciones where idcorte='".$_GET["idcorte"]."')) order by idcuenta desc");
                // conseguir las cuentas que tienen devoluciones hecas en este corte
                if (mysqli_num_rows($tickets)>0) {
                    ?>
                    <table class="table table-striped b-t">
                        <thead>
                            <tr>
                                <th>Ticket</th>
                                <th>Vendedor</th>
                                <th>Total</th>
                                <th>Fecha</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?
                            while ($ticket = mysqli_fetch_assoc($tickets)) {
                                // total es el total devuelto, no el total de la cuenta
                                $total = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from trcuentaproductos where idcuenta='".$ticket["idcuenta"]."' and idcuentaproducto in (select idcuentaproducto from tdevoluciones)"))["total"];
                                ?>
                                <tr>
                                    <?
                                    $nombrevendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$ticket["idvendedor"]."'"))["nombre"];
                                    ?>
                                    <td><? echo $ticket["folio"]; ?></td>
                                    <td><? echo $nombrevendedor; ?></td>
                                    <td>$<? echo number_format($total,2); ?></td>
                                    <td><? echo fecha_formateada($ticket["fecha"]); ?></td>
                                    <td>
                                        <!-- <div class="btn-group dropdown">
                                            <button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
                                            <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;"> -->
                                                <!-- <a href="javascript:;" onClick="imprimir(<? echo $cuenta["idcuenta"]; ?>)"><li class="dropdown-item">Imprimir</li></a> -->
                                                <!-- <a href="?modulo1=cortes&modulo2=detalleg&modulo3=detallecuenta&idcuenta=<? echo $cuenta["idcuenta"]; ?>&idcorte=<? echo $_GET["idcorte"]; ?>"><li class="dropdown-item">Ver detalle</li></a> -->
                                                <a href="?modulo1=cortes&modulo2=detalleg&modulo3=detallecuenta&idcuenta=<? echo $ticket["idcuenta"]; ?>&idcorte=<? echo $_GET["idcorte"]; ?>&idticket=<? echo $ticket["idticket"]; ?>" class="btn white">Ver detalle</a>
                                            <!-- </ul>
                                        </div> -->
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
