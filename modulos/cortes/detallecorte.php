<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.com.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

function limpiarString($string){
 
    $string = trim($string);
 
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    /*$string = str_replace(
        array("\", "¨", "º", "-", "~",
             "#", "@", "|", "!", """,
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", " "),
        '',
        $string
    );*/
 
 
    return $string;
}

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

class PDF extends FPDF{

    public $tablewidths;
    public $footerset;

    function _beginpage($orientation, $size, $rotation) {
        $this->page++;
        if(!isset($this->pages[$this->page])) // solves the problem of overwriting a page if it already exists
            $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
        // Check page size and orientation
        if($orientation=='')
            $orientation = $this->DefOrientation;
        else
            $orientation = strtoupper($orientation[0]);
        if($size=='')
            $size = $this->DefPageSize;
        else
            $size = $this->_getpagesize($size);
        if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
        {
            // New size or orientation
            if($orientation=='P')
            {
                $this->w = $size[0];
                $this->h = $size[1];
            }
            else
            {
                $this->w = $size[1];
                $this->h = $size[0];
            }
            $this->wPt = $this->w*$this->k;
            $this->hPt = $this->h*$this->k;
            $this->PageBreakTrigger = $this->h-$this->bMargin;
            $this->CurOrientation = $orientation;
            $this->CurPageSize = $size;
        }
        if($orientation!=$this->DefOrientation || $size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
            $this->PageInfo[$this->page]['size'] = array($this->wPt, $this->hPt);
        if($rotation!=0)
        {
            if($rotation%90!=0)
                $this->Error('Incorrect rotation value: '.$rotation);
            $this->CurRotation = $rotation;
            $this->PageInfo[$this->page]['rotation'] = $rotation;
        }
    }

    // Pie de página
    function Footer(){
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial','I',8);
        // Print centered page number
        $this->Cell(0,10,utf8_decode('Página '.$this->PageNo().' de {nb}'),0,0,'C');
    }

    function morepagestable($datas, $alineaciones, $lineheight=3) {
        // some things to set and 'remember'
        $l = $this->lMargin;
        $startheight = $h = $this->GetY();
        $startpage = $currpage = $maxpage = $this->page;

        // calculate the whole width
        $fullwidth = 0;
        foreach($this->tablewidths AS $width) {
            $fullwidth += $width;
        }

        // Now let's start to write the table
        $numalineacion = 0;
        foreach($datas AS $row => $data) {
            $this->page = $currpage;
            // write the horizontal borders
            $this->Line($l,$h,$fullwidth+$l,$h);
            // write the content and remember the height of the highest col
            foreach($data AS $col => $txt) {
                $this->page = $currpage;
                $this->SetXY($l,$h+1);
                $this->MultiCell($this->tablewidths[$col],$lineheight,$txt,0,$alineaciones[$numalineacion][$col]);
                $l += $this->tablewidths[$col];

                if(!isset($tmpheight[$row.'-'.$this->page]))
                    $tmpheight[$row.'-'.$this->page] = 0;
                if($tmpheight[$row.'-'.$this->page] < $this->GetY()) {
                    $tmpheight[$row.'-'.$this->page] = $this->GetY();
                }
                if($this->page > $maxpage)
                    $maxpage = $this->page;
            }

            // get the height we were in the last used page
            $h = $tmpheight[$row.'-'.$maxpage];
            // set the "pointer" to the left margin
            $l = $this->lMargin;
            // set the $currpage to the last page
            $currpage = $maxpage;

            $numalineacion++;
        }
        
        $this->SetY($h+1);
        $this->page = $maxpage;
    }
}

$corte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idcorte = '".$_GET["idcorte"]."'"));

// Creación del objeto de la clase heredada
$pdf = new PDF('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10,10);
$pdf->SetFont('Helvetica','',10);
$pdf->SetFillColor(182,182,182);

//$pdf->Image("../../assets/images/formato".$usuario["formato"].".jpg",0,0,220,280);

$pdf->Cell(0,0,utf8_decode(fecha_formateada(explode(" ",$corte["fechafinal"])[0])),0,0,'R');

$pdf->SetFont('Helvetica','B',9);
$pdf->Ln(10);
$pdf->Cell(20,4,utf8_decode("Corte #".$corte["folio"]),'B',0,'L');

$tickets = mysqli_query($con,"select * from ttickets where idcorte = '".$corte["idcorte"]."'");
while($ticket = mysqli_fetch_assoc($tickets)){
    
    $pdf->Ln(10);
    $pdf->SetFont('Helvetica','B',9);
    $pdf->Cell(0,4,utf8_decode("Ticket #".$ticket["folio"]." | ".date("Y-m-d h:i a",strtotime($ticket["fecha"]))),'',0,'L');

    $efectivo = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=1"))["monto"];
    $efectivousd = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=2"))["monto"];
    $tarjeta = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=3"))["monto"];
    $transferencia = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=5"))["monto"];
    $cheque = mysqli_fetch_assoc(mysqli_query($con,"select sum(monto) as monto from tformaspagoticket where idticket='".$ticket["idticket"]."' and idformapago=6"))["monto"];

    $cuenta = mysqli_fetch_assoc(mysqli_query($con,"select * from tcuentas where idcuenta='".$ticket["idcuenta"]."'"));

    if($cuenta["tipocuenta"]=="A"){
        if(mysqli_num_rows(mysqli_query($con,"select * from ttickets where idcuenta = '".$ticket["idcuenta"]."' and idticket < '".$ticket["idticket"]."' and status = 'A'"))==0){
            $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$ticket["idcuenta"]."'");
            while($partida = mysqli_fetch_assoc($partidas)){

                $pdf->Ln(4);
                $pdf->SetFont('Helvetica','',8);

                $articulos += $partida["cantidad"];
                $cantidad = $partida["cantidad"];
                $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
                $nombre = limpiarString($partida["producto"]." (TALLA: ".$partida["talla"]." COLOR: ".$partida["color"].")");
                $importe = "$".number_format($partida["total"],2);
                
                $pdf->Cell(6,4,$cantidad,'',0,'L');
                $pdf->Cell(165,4,$nombre,'',0,'L');
                $pdf->Cell(25,4,$importe,'',0,'R');

                $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
                if(mysqli_num_rows($personalizaciones)>0){
                    $pdf->Ln(4);
                    $pdf->SetFont('Helvetica','',6);
                    while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                        $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                        $pdf->Cell(0,4," - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"],'',0,'L');
                    }
                }
            }
        }else{
            $folio = mysqli_fetch_assoc(mysqli_query($con,"select folio from ttickets where idcuenta = '".$cuenta["idcuenta"]."' order by idticket asc limit 1"))["folio"];
            $pdf->Cell(0,4,utf8_decode("Abono a apartado #".$folio),'',0,'L');
        }
    }else if($cuenta["tipocuenta"]==""){
        $pdf->Cell(0,4,utf8_decode("Abono a pedido #".$ticket["idpedido"]),'',0,'L');
    }else{
        $partidas = mysqli_query($con,"select * from vrcuentaproductos where idcuenta='".$ticket["idcuenta"]."'");
        while($partida = mysqli_fetch_assoc($partidas)){

            $pdf->Ln(4);
            $pdf->SetFont('Helvetica','',8);

            $articulos += $partida["cantidad"];
            $cantidad = $partida["cantidad"];
            $producto = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductos where idproducto = '".$partida["idproducto"]."'"));
            $nombre = limpiarString($partida["producto"]." (TALLA: ".$partida["talla"]." COLOR: ".$partida["color"].")");
            $importe = "$".number_format($partida["total"],2);
                
            $pdf->Cell(6,4,$cantidad,'',0,'L');
            $pdf->Cell(165,4,$nombre,'',0,'L');
            $pdf->Cell(25,4,$importe,'',0,'R');

            $personalizaciones = mysqli_query($con,"select * from trcuentaproductopersonalizados where idcuentaproducto='".$partida["idcuentaproducto"]."'");
            if(mysqli_num_rows($personalizaciones)>0){
                $pdf->Ln(4);
                $pdf->SetFont('Helvetica','',6);
                while($personalizacion = mysqli_fetch_assoc($personalizaciones)){
                    $categoria = mysqli_fetch_assoc(mysqli_query($con,"select * from tcatpersonalizaciones where idpersonalizacion='".$personalizacion["idpersonalizacion"]."'"));

                    $pdf->Cell(0,4," - " . $categoria["nombre"] . ": " . $personalizacion["personalizacion"],'',0,'L');
                }
            }
        }
    }

    $pdf->Ln(4);
    $pdf->SetFont('Helvetica','',8);
    $pdf->Cell(0,4,utf8_decode("TOTAL: $".$ticket["total"]),'',0,'R');
    
    $pdf->Ln(8);
    $pdf->Cell(0,4,utf8_decode("Forma(s) de Pago"),'',0,'R');
    $pdf->Ln(4);
    $formaspago = mysqli_query($con,"select * from tformaspagoticket where idticket = '".$ticket["idticket"]."'");
    while($formapago = mysqli_fetch_assoc($formaspago)){
        $metodo = mysqli_fetch_assoc(mysqli_query($con,"select nombre from tcatformaspago where idformapago = '".$formapago["idformapago"]."'"))["nombre"];
        $pdf->Cell(0,4,utf8_decode(limpiarString($metodo)." $".number_format($formapago["montorecibido"],2)),'',0,'R');
        $pdf->Ln(4);
    }

}

$pdf->Output("I","Corte#".$corte["folio"].".pdf");
?>
