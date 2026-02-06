<?php
include($_SERVER["DOCUMENT_ROOT"]."/vm39845um223u/c91ktn24g7if5u.php");
include($_SERVER["DOCUMENT_ROOT"]."/2cnytm029mp3r/cm293uc5904uh.php");
include($_SERVER["DOCUMENT_ROOT"]."/vm39845um223u/qxom385u3mfg3.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/libs/num2letras.php");

function limpiarString($string)
{
 
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

$impresora = mysqli_fetch_assoc(mysqli_query($con,"select * from tvendedores where idvendedor = '".$_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]."'"))["impresora"];
$corte = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idcorte = '".$_POST["idcorte"]."'"));
$sucursal = mysqli_fetch_assoc(mysqli_query($con,"select * from tsucursales where idsucursal = '".$corte["idsucursal"]."'"));

$corteanterior = mysqli_fetch_assoc(mysqli_query($con,"select * from tcortessucursales where idsucursal = '".$corte["idsucursal"]."' and idcorte < '".$corte["idcorte"]."' and status = 'T' order by idcorte desc limit 1"));

$ticket .= date("d/m/Y",strtotime($corte["fechainicial"]))." ".date("h:i:s a",strtotime($corte["fechainicial"]))." ".str_pad($corte["folio"],7,"0",STR_PAD_LEFT);

$formaspago = array();
$result = mysqli_query($con,"select a.*, b.nombre from tcortesucursal_formaspago a left join tcatformaspago b on b.idformapago = a.idformapago where a.idcorte = '".$corte["idcorte"]."'");
while($row = mysqli_fetch_assoc($result)){
	$formaspago[$row["idformapago"]] = $row;
}

$formaspago_arqueo = array();
$result = mysqli_query($con,"select a.*, b.nombre from tcortesucursal_formaspago_arqueo a left join tcatformaspago b on b.idformapago = a.idformapago where a.idcorte = '".$corte["idcorte"]."'");
while($row = mysqli_fetch_assoc($result)){
	$formaspago_arqueo[$row["idformapago"]] = $row;
}
?>
<script>
var imgData = '';

var esc_init = "\x1B" + "\x40"; // initialize printer
var esc_p = "\x1B" + "\x70" + "\x30"; // open drawer
var gs_cut = "\x1D" + "\x56" + "\x4E"; // cut paper
var esc_a_l = "\x1B" + "\x61" + "\x30"; // align left
var esc_a_c = "\x1B" + "\x61" + "\x31"; // align center
var esc_a_r = "\x1B" + "\x61" + "\x32"; // align right
var esc_double = "\x1B" + "\x21" + "\x31"; // heading
var font_reset = "\x1B" + "\x21" + "\x02"; // styles off
var esc_ul_on = "\x1B" + "\x2D" + "\x31"; // underline on
var esc_bold_on = "\x1B" + "\x45" + "\x31"; // emphasis on
var esc_bold_off = "\x1B" + "\x45" + "\x30"; // emphasis off


<?
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/imagenes/sucursales/".$sucursal["imagen"]) && !is_null($sucursal["imagen"])){
?>
img = new Image();
img.onload = function () {
	// Create an empty canvas element
	//var canvas = document.createElement("canvas");
	var canvas = document.createElement('canvas');
	canvas.width = img.width;
	canvas.height = img.height;
	// Copy the image contents to the canvas
	var ctx = canvas.getContext("2d");
	ctx.drawImage(img, 0, 0);
	// get image slices and append commands
	var bytedata = esc_init + esc_a_c + getESCPImageSlices(ctx, canvas) + font_reset;
	//alert(bytedata);
	imgData = bytedata;

	imprimir(imgData);
};
img.src = 'http<? echo (($_SERVER["HTTPS"]!="") ? "s" : ""); ?>://<? echo $_SERVER["HTTP_HOST"]; ?>/imagenes/sucursales/<? echo $sucursal["imagen"]; ?>';
<?	
} else {
	?>
	imprimir();
	<?
}
?>

function imprimir(imagen = null){
	
	var data = "";

	// data += imgData;
	if(imagen!=null){
        data += imagen;
    }
    data += esc_init + esc_a_c + "<? echo limpiarString(strtoupper($sucursal["nombre"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["calle"]." No. ".$sucursal["numero"].", ".$sucursal["colonia"])); ?>\n";
    data += "<? echo limpiarString(strtoupper("C.P. ".$sucursal["codigopostal"].", ".$sucursal["ciudad"])); ?>\n";
    data += "<? echo limpiarString(strtoupper("Teléfono ".$sucursal["telefono"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["razonsocial"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["rfc"])); ?>\n";
    data += "<? echo limpiarString(strtoupper($sucursal["regimen"])); ?>\n";
    data += "<? echo $ticket; ?>\n\n";

    data += esc_a_c + "CORTE DE CAJA\n\n";

	data += esc_a_c + "INFORME DE VENTA\n";

    // data += esc_a_l + "EFECTIVO MXN <? //echo "$".number_format($corte["efectivo"],2); ?>\n";
    // data += esc_a_l + "EFECTIVO USD <? //echo "$".number_format($corte["efectivousd"],2); ?>\n";
    // data += esc_a_l + "TARJETA <? //echo "$".number_format($corte["tarjeta"],2); ?>\n";
    // data += esc_a_l + "TRANSFERENCIA <? //echo "$".number_format($corte["transferencia"],2); ?>\n";
    // data += esc_a_l + "CHEQUE <? //echo "$".number_format($corte["cheque"],2); ?>\n";

	<? foreach($formaspago as $formapago){ ?>
		data += esc_a_l + "<? echo strtoupper($formapago["nombre"]); ?> <? echo "$".number_format($formapago["total"],2); ?>\n";
	<? } ?>

    data += esc_a_l + "TOTAL VENTAS <? echo "$".number_format($corte["ventas"],2); ?>\n";
    data += esc_a_l + "GASTOS <? echo "$".number_format($corte["gastos"],2); ?>\n";

	
	

	data += esc_a_c + "\nINFORME DE MONTOS\n";

	<? $totalventas = $corte["feria"] + $corte["gastos"]; ?>
    // data += esc_a_l + "EFECTIVO MXN <? //echo "$".number_format($corte["arqueo_efectivo"],2); ?>\n";
    // data += esc_a_l + "EFECTIVO USD <? //echo "$".number_format($corte["arqueo_efectivousd"],2); ?>\n";
    // data += esc_a_l + "TARJETA <? //echo "$".number_format($corte["arqueo_tarjeta"],2); ?>\n";
    // data += esc_a_l + "TRANSFERENCIA <? //echo "$".number_format($corte["arqueo_transferencia"],2); ?>\n";
    // data += esc_a_l + "CHEQUE <? //echo "$".number_format($corte["arqueo_cheque"],2); ?>\n";

	<? foreach($formaspago_arqueo as $idformapago => $formaspago_arqueo){ 
		if($idformapago == 2){ //dolar
			$totalventas += $formaspago_arqueo["total"]*$corte["tipocambio"];
		} else {
			$totalventas += $formaspago_arqueo["total"];
		}
	?>
		data += esc_a_l + "<? echo strtoupper($formaspago_arqueo["nombre"]); ?> <? echo "$".number_format($formaspago_arqueo["total"],2); ?>\n";
	<? } ?>

    data += esc_a_l + "FERIA HOY <? echo "$".number_format($corte["feria"],2); ?>\n";
	data += esc_a_l + "GASTOS <? echo "$".number_format($corte["gastos"],2); ?>\n";
    data += esc_a_l + "TOTAL <? echo "$".number_format($totalventas,2); ?>\n";
	data += esc_a_l + "FERIA AYER <? echo "$".number_format($corteanterior["feria"],2); ?>\n";
	data += esc_a_l + "TOTAL VENTAS <? echo "$".number_format($totalventas-$corteanterior["feria"],2); ?>\n";
    data += esc_a_l + "DIFERENCIA <? echo "$".number_format(($totalventas-$corteanterior["feria"]) - $corte["ventas"],2); ?>\n";
                
    data += "\n\n\n\n\n\n\n\n\n\n\n" + gs_cut + "\r";

    webprint.printRaw(data,'<? echo str_replace("\\","\\\\",$impresora); ?>');
}

function getESCPImageSlices(context, canvas) {
	var width = canvas.width;
	var height = canvas.height;
	var nL = Math.round(width % 256);
	var nH = Math.round(height / 256);
	var dotDensity = 33;
	// read each pixel and put into a boolean array
	var imageData = context.getImageData(0, 0, width, height);
	imageData = imageData.data;
	// create a boolean array of pixels
	var pixArr = [];
	for (var pix = 0; pix < imageData.length; pix += 4) {
		pixArr.push((imageData[pix] == 0));
	}
	// create the byte array
	var final = [];
	// this function adds bytes to the array
	function appendBytes() {
		for (var i = 0; i < arguments.length; i++) {
			final.push(arguments[i]);
		}
	}
	// Set the line spacing to 24 dots, the height of each "stripe" of the image that we're drawing.
	appendBytes(0x1B, 0x33, 24);
	// Starting from x = 0, read 24 bits down. The offset variable keeps track of our global 'y'position in the image.
	// keep making these 24-dot stripes until we've executed past the height of the bitmap.
	var offset = 0;
	while (offset < height) {
		// append the ESCP bit image command
		appendBytes(0x1B, 0x2A, dotDensity, nL, nH);
		for (var x = 0; x < width; ++x) {
			// Remember, 24 dots = 24 bits = 3 bytes. The 'k' variable keeps track of which of those three bytes that we're currently scribbling into.
			for (var k = 0; k < 3; ++k) {
				var slice = 0;
				// The 'b' variable keeps track of which bit in the byte we're recording.
				for (var b = 0; b < 8; ++b) {
					// Calculate the y position that we're currently trying to draw.
					var y = (((offset / 8) + k) * 8) + b;
					// Calculate the location of the pixel we want in the bit array. It'll be at (y * width) + x.
					var i = (y * width) + x;
					// If the image (or this stripe of the image)
					// is shorter than 24 dots, pad with zero.
					var bit;
					if (pixArr.hasOwnProperty(i)) bit = pixArr[i] ? 0x01 : 0x00; else bit = 0x00;
					// Finally, store our bit in the byte that we're currently scribbling to. Our current 'b' is actually the exact
					// opposite of where we want it to be in the byte, so subtract it from 7, shift our bit into place in a temp
					// byte, and OR it with the target byte to get it into the final byte.
					slice |= bit << (7 - b);    // shift bit and record byte
				}
				// Phew! Write the damn byte to the buffer
				appendBytes(slice);
			}
		}
		// We're done with this 24-dot high pass. Render a newline to bump the print head down to the next line and keep on trucking.
		offset += 24;
		appendBytes(10);
	}
	// Restore the line spacing to the default of 30 dots.
	appendBytes(0x1B, 0x33, 30);
	// convert the array into a bytestring and return
	final = ArrayToByteStr(final);
	return final;
}
/**
 * @return {string}
 */
function ArrayToByteStr(array) {
	var s = '';
	for (var i = 0; i < array.length; i++) {
		s += String.fromCharCode(array[i]);
	}
	return s;
}
</script>