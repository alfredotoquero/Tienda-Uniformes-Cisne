<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../2cnytm029mp3r/cm293uc5904uh.php");
include("../vm39845um223u/qxom385u3mfg3.php");

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
?>
<div class="table-responsive">
    <input type="hidden" name="idproveedor" id="idproveedor" value="<? echo $_POST["idproveedor"]; ?>">
    <?
    $solicitudescompra = mysqli_query($con,"select * from tsolicitudescompra where status!=1 and idproveedor='".$_POST["idproveedor"]."' order by fecha desc");
    if(mysqli_num_rows($solicitudescompra)>0){
    ?>
    <table class="table table-striped b-t">
        <thead>
            <tr>
                <th width="50"></th>
                <th>Producto</th>
                <th width="250">Usuario</th>
                <th width="150">Fecha</th>
            </tr>
        </thead>
        <tbody>
        <?
        while ($solicitudcompra = mysqli_fetch_assoc($solicitudescompra)) {
            // $pedido = mysqli_fetch_assoc(mysqli_query($con,"select * from tpedidos where idpedido = '".$solicitudcompra["idpedido"]."'"));
            $usuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario = '".$solicitudcompra["idusuario"]."'"));
            
            $nombreproducto = ($solicitudcompra["idproducto"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto='".$solicitudcompra["idproducto"]."'"))["nombre"] : $solicitudcompra["producto"];
            
            $nombretalla = ($solicitudcompra["idtalla"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcattallas where idtalla='".$solicitudcompra["idtalla"]."'"))["nombre"] : $solicitudcompra["talla"];
            
            $nombrecolor = ($solicitudcompra["idcolor"]>0) ? mysqli_fetch_assoc(mysqli_query($con,"select * from tcatcolores where idcolor='".$solicitudcompra["idcolor"]."'"))["nombre"] : $solicitudcompra["color"];
            ?>
            <tr>
                <td><label class="ui-check m-0"><input type="checkbox" name="solicitudes[]" value="<? echo $solicitudcompra["idsolicitudcompra"]; ?>"><i class="dark-white"></i></label></td>
                <?
                
                ?>
                <td><? echo $solicitudcompra["cantidad"]." ".$nombreproducto . " | Talla: " . $nombretalla . ", Color: " . $nombrecolor; ?></td>
                <td><? echo $usuario["nombre"]; ?></td>
                <td><? echo fecha_formateada($solicitudcompra["fecha"]); ?></td>
            </tr>
            <?
            }
        ?>
        </tbody>
    </table>
    <?
    }else {
        ?>
        <center><p><b>--No hay productos por comprar para este proveedor--</b></p></center>
        <?
    }
    ?>
</div> 