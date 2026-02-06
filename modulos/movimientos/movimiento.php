<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

// echo "texto";

function fecha_formateada($fecha){
	$fecha = explode("-",$fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia." de ";

	switch($mes){
		case "01": $fecha.= "Enero"; break;
		case "02": $fecha.= "Febrero"; break;
		case "03": $fecha.= "Marzo"; break;
		case "04": $fecha.= "Abril"; break;
		case "05": $fecha.= "Mayo"; break;
		case "06": $fecha.= "Junio"; break;
		case "07": $fecha.= "Julio"; break;
		case "08": $fecha.= "Agosto"; break;
		case "09": $fecha.= "Septiembre"; break;
		case "10": $fecha.= "Octubre"; break;
		case "11": $fecha.= "Noviembre"; break;
		case "12": $fecha.= "Diciembre"; break;
	}

	$fecha .= " de ".$ano;
	return $fecha;
}

require('../../assets/plugins/fpdf/fpdf.php');


$movimiento = mysqli_fetch_assoc(mysqli_query($con,"select * from vmovimientos where idmovimientoinventario='".$_GET["idmovimientoinventario"]."'"));

$mialmacen = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal in (select idsucursal from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."')"))["idalmacen"];

// Creación del objeto de la clase heredada
$pdf = new FPDF('P','mm','Letter');
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(182,182,182);

$pdf->Image("../../assets/images/formatoplayeras.jpg",0,0,220,280);

$pdf->SetY(45);
$pdf->Cell(0,0,utf8_decode(fecha_formateada(explode(" ",$movimiento["fecha"])[0])),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(25,4,utf8_decode("Movimiento #" . $movimiento["idmovimientoinventario"]),'',0,'L');

$pdf->Ln(8);
$pdf->SetFont('Helvetica','',9);
$pdf->Cell(0,0,utf8_decode("Tipo de movimiento: " . $movimiento["movimiento"]),0,0,'L');

if ($movimiento["idtipomovimiento"]=="2") {
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("De: " . $movimiento["almacen"]),0,0,'L');
} else if ($movimiento["idtipomovimiento"]=="1") {
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("A: " . $movimiento["almacen"]),0,0,'L');
}else {
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("De: " . $movimiento["almacen"]),0,0,'L');
	$pdf->Ln(4);
	$pdf->SetFont('Helvetica','',9);
	$pdf->Cell(0,0,utf8_decode("A: " . $movimiento["almacensecundario"]),0,0,'L');
}

$pdf->Ln(8);
$pdf->SetFont('Helvetica','B',9);
$pdf->Cell(20,5,utf8_decode('Cantidad'),'',0,'C');
$pdf->Cell(106,5,utf8_decode('Producto'),'',0,'L');
$pdf->Cell(35,5,utf8_decode('Talla'),'',0,'R');
$pdf->Cell(35,5,utf8_decode('Color'),'',0,'R');

$partidas = mysqli_query($con,"select * from vmovimientoproductos where idmovimientoinventario = '".$movimiento["idmovimientoinventario"]."' order by idmovimientoinventario");
while($partida = mysqli_fetch_assoc($partidas)){
	$pdf->Ln(5);

	$pdf->SetFont('Helvetica','',9);
	
	$posy2 = $pdf->GetY();
	$pdf->Line(10,$posy2 + 1,206,$posy2 + 1);
	
	$pdf->Ln(2);
	
	$pdf->Cell(20,5,utf8_decode($partida["cantidad"]),0,0,'C');
	$pdf->Cell(106,5,$partida["producto"],0,0,'L');
	$pdf->Cell(35,5,$partida["talla"],0,0,'R');
	$pdf->Cell(35,5,$partida["color"],0,0,'R');
}

$nombre = "asd";

$pdf->Output("I",$nombre.".pdf");
?>
