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

$fechaS = date("Y-m-d", strtotime($_POST["txtFechaFinal"] . " +1 day"));

if (isset($_POST["txtFechaInicial"]) and $_POST["txtFechaInicial"]!="") { 
	if ($_POST["tipoFecha"]=="1") {
		$compras = mysqli_query($con,"select * from tcompras where status='".($_POST["tipo"]=="0" ? "R" : "")."' and fecha>='".$_POST["txtFechaInicial"]."' and fecha<='".$fechaS."' order by fecha desc ");
	} else if($_POST["tipoFecha"]=="2") { 
		$compras = mysqli_query($con,"select * from tcompras where status='".($_POST["tipo"]=="0" ? "R" : "")."' and fecharecepcion>='".$_POST["txtFechaInicial"]."' and fecharecepcion<='".$fechaS."' order by fecharecepcion desc ");
	}
}else {
	if ($_POST["tipoFecha"]=="1") {
		$compras = mysqli_query($con,"select * from tcompras where status='".($_POST["tipo"]=="0" ? "R" : "")."' order by fecha desc");
	} else if($_POST["tipoFecha"]=="2") {
		$compras = mysqli_query($con,"select * from tcompras where status='".($_POST["tipo"]=="0" ? "R" : "")."' order by fecharecepcion desc");
	} else {
        $compras = mysqli_query($con,"select * from tcompras where status='".($_POST["tipo"]=="0" ? "R" : "")."' order by fecha desc");
    }
} 
?>

<div class="table-responsive">
    <?
    if (mysqli_num_rows($compras)>0) {
    ?>
    <table class="table table-striped b-t">
        <thead>
            <tr>
                <th width="50">#</th>
                <th>Usuario</th>
                <th width="150">Proveedor</th>
                <th width="150">Fecha</th>
                <?
                if ($_POST["tipo"]=="0") {
                    ?>
                    <th width="150">Fecha de Recepción</th>
                    <?
                }
                ?>
                <th width="100"></th>
            </tr>
        </thead>
        <tbody>
        <?
        while ($compra = mysqli_fetch_assoc($compras)) {
            $usuario = mysqli_fetch_assoc(mysqli_query($con,"select * from tusuarios where idusuario = '". $compra["idusuario"]."' "));
            ?>
            <tr>
                <td><? echo $compra["idcompra"]; ?></td> 
                <td><? echo $usuario["nombre"]; ?></td> 
                <? $proveedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tproveedores where idproveedor='".$compra["idproveedor"]."'"))["nombre"]; ?>
                <td><? echo $proveedor; ?></td>
                <td><? echo fecha_formateada($compra["fecha"]); ?></td>
                <?
                if ($_POST["tipo"]=="0") {
                    ?>
                    <td><? echo fecha_formateada($compra["fecharecepcion"]); ?></td>
                    <?
                }
                ?>
                <td> 
                    <a href="?modulo1=compras&modulo2=ver&idcompra=<? echo $compra["idcompra"]; ?>" class="btn white">Ver Compra</a>
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
