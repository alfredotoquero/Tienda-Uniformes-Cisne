<?
include("../../vm39845um223u/c91ktn24g7if5u.php");
include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

function fecha_formateada($fecha){
	$fecha = explode(" ",$fecha);
	$hora = $fecha[1];
	$fecha = $fecha[0];
	
	$fecha = explode("-",$fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia."/";

	switch($mes){
		case "01": $fecha.= "Ene"; break;
		case "02": $fecha.= "Feb"; break;
		case "03": $fecha.= "Mar"; break;
		case "04": $fecha.= "Abr"; break;
		case "05": $fecha.= "May"; break;
		case "06": $fecha.= "Jun"; break;
		case "07": $fecha.= "Jul"; break;
		case "08": $fecha.= "Ago"; break;
		case "09": $fecha.= "Sep"; break;
		case "10": $fecha.= "Oct"; break;
		case "11": $fecha.= "Nov"; break;
		case "12": $fecha.= "Dic"; break;
	}

	$fecha .= "/".$ano;
	
	if($hora!=""){
		$fecha .= "<br>".date("h:i a",strtotime($hora));
	}
	
	return $fecha;
}

// if ($_POST["tipo"]=="nombre") {
//     $apartados = mysqli_query($con,"select * from tapartados where status='".$_POST["vigencia"]."' and nombre like '%".$_POST["nombre"]."%' order by fecha desc");
// }

// if ($_POST["tipo"]=="vigencia") {
//     $apartados = mysqli_query($con,"select * from tapartados where status='".$_POST["vigencia"]."' order by fecha desc");
// }

if ($_POST["accion"]=="filtrar") {
    // $apartados = mysqli_query($con,"select * from tapartados where status='".$_POST["vigencia"]."' and nombre like '%".$_POST["nombre"]."%' order by fecha desc");
    $vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
    $apartados = mysqli_query($con,"select * from tcuentas where status='".$_POST["vigencia"]."' and tipocuenta='A' and nombrecuenta like '%".$_POST["nombre"]."%' and idsucursal = '".$vendedor["idsucursal"]."' order by fecha desc");
}

if (mysqli_num_rows($apartados)>0) {
    ?>
    <div class="table-responsive">
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
                    ?>
                    <tr>
                        <td><? echo $apartado["nombrecuenta"]; ?></td>
                        <td>$<? echo number_format($apartado["total"],2); ?></td>
                        <?
                        $abonado = $apartado["abonado"];
                        $restante = $apartado["total"] - $abonado;
                        ?>
                        <td>$<? echo number_format($abonado,2); ?></td>
                        <td>$<? echo number_format($restante,2); ?></td>
                        <td><? echo fecha_formateada($apartado["fecha"]); ?></td>
                        <td>
                            <a href="javascript:;" class="btn white" style="margin-right:15px;" onclick="toggleDetalle(<? echo $apartado["idcuenta"]; ?>)"><i id="btnApartado<? echo $apartado["idcuenta"]; ?>" class="fas fa-plus"></i></a> 
                        </td>
                        <td>
                            <div class="btn-group dropdown">
                                <button type="button" class="btn white" data-toggle="dropdown" aria-expanded="false">Opciones <span class="caret"></span></button>
                                <ul class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 33px, 0px); top: 0px; left: 0px; will-change: transform;">
                                    <a href="?modulo1=apartados&modulo2=ver&idcuenta=<? echo $apartado["idcuenta"]; ?>"><li class="dropdown-item">Ver desglose</li></a>
                                    <!-- <a href="?modulo1=apartados&modulo2=pagos&idcuenta=<? echo $apartado["idcuenta"]; ?>"><li class="dropdown-item">Ver historial de pagos</li></a> -->
                                    <? if($apartado["abonado"]!=$apartado["total"]) {?>
                                        <li class="divider"></li>
                                        <a href="javascript:;" onClick="abonar(<? echo $apartado["idcuenta"]; ?>);"><li class="dropdown-item">Abonar</li></a>
                                    <? } ?>
                                    <? if($apartado["status"]!="C" and $restante!=0) {?>
                                        <li class="divider"></li>
                                        <a href="?modulo1=apartados&accion=cancelar&idcuenta=<? echo $apartado["idcuenta"]; ?>" onClick="return confirm('ATENCION: ¿Estás seguro de que quieres cancelar este apartado?');"><li class="dropdown-item">Cancelar</li></a>
                                    <? } ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <tr id="divApartado<? echo $apartado["idcuenta"]; ?>" style="display:none;">
                        <td colspan="7">
                        <?php
                        $apartadosproductos = mysqli_query($con,"select * from trcuentaproductos where idcuenta='".$apartado["idcuenta"]."'");
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
                                $dineroproductosentregados = mysqli_fetch_assoc(mysqli_query($con,"select sum(total) as total from trcuentaproductos where idcuenta='".$apartado["idcuenta"]."' and status='E'"))["total"];
                                while ($apartadosproducto = mysqli_fetch_assoc($apartadosproductos)) {
                                    ?>
                                    <tr>
                                        <? $nombre = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$apartadosproducto["idproducto"]."'"))["nombre"]; ?>
                                        <td><? echo $apartadosproducto["cantidad"]; ?></td>
                                        <td><? echo $nombre; ?></td>
                                        <td>$<? echo number_format($apartadosproducto["total"],2); ?></td>
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
    </div>
    <?
}else {
    ?>
    <div class="box-body">
        <center><b><p>--No hay apartados con el criterio especificado--</p></b></center>
    </div>
    <?
}
?>
