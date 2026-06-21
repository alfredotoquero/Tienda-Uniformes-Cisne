<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/2cnytm029mp3r/cm293uc5904uh.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/qxom385u3mfg3.php");

$idfactura = isset($_GET["idfactura"]) ? intval($_GET["idfactura"]) : 0;

if (!$idfactura) {
    http_response_code(400);
    exit("Parámetro inválido");
}

$idfactura = mysqli_real_escape_string($con, $idfactura);

$query = "
SELECT
    f.uuid,
    e.rfc AS rfc_emisor
FROM
    tfacturas f
LEFT JOIN
    temisores e ON e.idemisor = f.idemisor
WHERE
    f.idfactura = '$idfactura'";

$factura = mysqli_fetch_assoc(mysqli_query($con, $query));

if (!$factura) {
    http_response_code(404);
    exit("Factura no encontrada");
}

$ruta_server = $_SERVER["DOCUMENT_ROOT"] . "/../1.uniformescisne.mx";
$ruta = $ruta_server . "/emisores/" . $factura["rfc_emisor"] . "/facturas/" . $factura["uuid"] . ".pdf";

if (!file_exists($ruta)) {
    http_response_code(404);
    exit("No se encontró el PDF de la factura");
}

header("Content-Type: application/pdf");
header("Content-Length: " . filesize($ruta));
header("Content-Disposition: inline; filename=\"" . $factura["uuid"] . ".pdf\"");
readfile($ruta);
exit;
