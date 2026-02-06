<?
ini_set("session.gc_maxlifetime","7200");
ini_set('session.cookie_domain', 'tienda.uniformescisne.mx');
session_name("v3nd3d0rpl4y3r4spvc1sn3");
session_start();

include("../../2cnytm029mp3r/cm293uc5904uh.php");
include("../../vm39845um223u/qxom385u3mfg3.php");

require("../../assets/plugins/fpdf/fpdf.php");

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

// Creación del objeto de la clase heredada
$pdf = new PDF('P','mm','Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(20,20);
$pdf->SetFont('Helvetica','',9);
$pdf->SetFillColor(182,182,182);

$pdf->Cell(176,0,utf8_decode('Reporte de Existencias'),0,0,'L');
$pdf->Ln(5);

//AQUI INICIA EL CICLO DE PRODUCTOS
$vendedor = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor='".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"));
$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal = '".$vendedor["idsucursal"]."'"));
$productos = mysqli_query($con,"select * from tproductos where idproducto in (select idproducto from trproductosucursales where idsucursal='".$vendedor["idsucursal"]."') order by nombre");
while($producto = mysqli_fetch_assoc($productos)){

    $data = array();
    $alineacion = array();

    $pdf->Ln(5);
    $pdf->SetFont('Helvetica','B',9);
    $pdf->Cell(176,5,utf8_decode($producto["nombre"]),'',0,'L');
    $pdf->Ln(2);

    $pdf->SetFont('Helvetica','B',8);
    $pdf->Ln(5);
    $pdf->Cell(36,5,utf8_decode('COLOR/TALLA'),'B',0,'L');

    $arrayAlineaciones = array();
    $arrayAlineaciones[] = 'L';
    $arrayTamanos = array();
    $arrayTamanos[] = 36;
    $tallas = mysqli_query($con,"select * from tcattallas where idtalla in (select idtalla from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen='".$sucursal["idalmacen"]."' group by idtalla) order by posicion");
    $tamano = floor(140/mysqli_num_rows($tallas));
    while($talla = mysqli_fetch_assoc($tallas)){
        $pdf->Cell($tamano,5,utf8_decode($talla["nombre"]),'B',0,'C');
        $arrayTamanos[] = $tamano;
        $arrayAlineaciones[] = 'C';
    }
    $pdf->Ln(5);

    $pdf->tablewidths = $arrayTamanos;

    $pdf->SetFont('Helvetica','',7);

    $colores = mysqli_query($con,"select * from tcatcolores where idcolor in (select idcolor from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idalmacen='".$sucursal["idalmacen"]."' group by idcolor)");
    while($color = mysqli_fetch_assoc($colores)){
        $arrayExistencias = array();
        $arrayExistencias[] = utf8_decode($color["nombre"]);
        mysqli_data_seek($tallas,0);
        while ($talla = mysqli_fetch_assoc($tallas)) {
            $existencia = mysqli_fetch_assoc(mysqli_query($con,"select * from tproductoexistencias where idproducto='".$producto["idproducto"]."' and idtalla='".$talla["idtalla"]."' and idcolor='".$color["idcolor"]."' and idalmacen='".$sucursal["idalmacen"]."'"));
            $arrayExistencias[] = $existencia["existencia"];
        }
        $data[] = $arrayExistencias;
        $alineacion[] = $arrayAlineaciones;
    }

    $pdf->morepagestable($data,$alineacion);

}

$pdf->Output();
?>