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
    f.serie,
    f.folio,
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
$base = $ruta_server . "/emisores/" . $factura["rfc_emisor"] . "/facturas/" . $factura["uuid"];

if (!file_exists($base . ".pdf") || !file_exists($base . ".xml")) {
    http_response_code(404);
    exit("No se encontraron los archivos de la factura");
}

$nombre_zip = $factura["serie"] . "-" . $factura["folio"] . ".zip";
$tmp = tempnam(sys_get_temp_dir(), "factura_");

$zip = new ZipArchive();
if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    exit("No se pudo generar el archivo ZIP");
}

$zip->addFile($base . ".pdf", $factura["uuid"] . ".pdf");
$zip->addFile($base . ".xml", $factura["uuid"] . ".xml");
$zip->close();

header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"" . $nombre_zip . "\"");
header("Content-Length: " . filesize($tmp));
header("Pragma: no-cache");
readfile($tmp);
unlink($tmp);
exit;
