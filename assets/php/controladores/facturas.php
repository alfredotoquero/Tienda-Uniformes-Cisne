<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/2cnytm029mp3r/cm293uc5904uh.php");

$arrayerror = [
    "respuesta" => "ERROR",
    "mensaje"   => "Acción no permitida."
];

try {
    switch ($_POST["accion"]) {

        case "verPDF":
            $idfactura = intval($_POST["idfactura"]);
            if (!$idfactura) throw new Exception("Parámetro inválido");

            $respuesta = [
                "respuesta" => "OK",
                "tipo"      => "fancyArchivo",
                "pdf_fancy" => "/modulos/facturas/verPDF.php?idfactura=" . $idfactura
            ];
            break;

        case "reenviar":
            if ($_POST["authToken"] != $_SESSION["authToken"]) {
                $respuesta = $arrayerror;
                break;
            }

            $idfactura       = intval($_POST["idfactura"]);
            $correo          = $_POST["txtCorreo"];
            $correoAdicional = isset($_POST["txtCorreoAdicional"]) ? trim($_POST["txtCorreoAdicional"]) : "";

            if (!$idfactura) throw new Exception("Parámetro inválido");

            $idfactura = mysqli_real_escape_string($con, $idfactura);

            $query = "
            SELECT
                f.idfactura,
                f.uuid,
                f.serie,
                f.folio,
                f.total,
                f.timbrado,
                e.rfc AS rfc_emisor,
                s.idtienda
            FROM
                tfacturas f
            LEFT JOIN
                temisores e ON e.idemisor = f.idemisor
            LEFT JOIN
                ttickets t ON t.idfactura = f.idfactura
            LEFT JOIN
                tsucursales s ON s.idsucursal = t.idsucursal
            WHERE
                f.idfactura = '$idfactura'
            LIMIT 1";

            $factura = mysqli_fetch_assoc(mysqli_query($con, $query));
            if (!$factura || empty($factura["idfactura"])) {
                throw new Exception("No se encontró la factura");
            }

            $ruta_server = $_SERVER["DOCUMENT_ROOT"] . "/../1.uniformescisne.mx";
            $base        = $ruta_server . "/emisores/" . $factura["rfc_emisor"] . "/facturas/" . $factura["uuid"];

            if (!file_exists($base . ".pdf")) throw new Exception("No se encontró el PDF de la factura");
            if (!file_exists($base . ".xml")) throw new Exception("No se encontró el XML de la factura");

            $pdf_b64 = base64_encode(file_get_contents($base . ".pdf"));
            $xml_b64 = base64_encode(file_get_contents($base . ".xml"));

            $folio = $factura["serie"] . "-" . $factura["folio"];
            $fecha = $factura["timbrado"];
            $total = $factura["total"];

            include($ruta_server . "/assets/plantillas/correo/envioFactura.php");
            include($ruta_server . "/assets/plantillas/correo/base.php");

            include_once($_SERVER["DOCUMENT_ROOT"] . "/assets/php/classes/Correos.php");
            $claseCorreos = new Correos();

            $correos = array_values(array_filter(array_map('trim', explode(',', $correo))));
            if (!empty($correoAdicional)) {
                $correosExtra = array_values(array_filter(array_map('trim', explode(',', $correoAdicional))));
                $correos = array_merge($correos, $correosExtra);
            }

            $resultado = $claseCorreos->enviarCorreo([
                "idtienda" => $factura["idtienda"],
                "asunto"   => "Envío de factura",
                "mensaje"  => $cuerpo,
                "correos"  => $correos,
                "adjuntos" => [
                    ["nombre" => $factura["uuid"] . ".xml", "archivo" => $xml_b64],
                    ["nombre" => $factura["uuid"] . ".pdf", "archivo" => $pdf_b64]
                ]
            ]);

            if ($resultado["result"] == "success") {
                $respuesta = [
                    "respuesta" => "OK",
                    "tipo"      => "mensajecerrarfancy",
                    "titulo"    => "Factura reenviada",
                    "mensaje"   => "La factura se ha enviado correctamente."
                ];
            } else {
                throw new Exception($resultado["mensaje"]);
            }
            break;

        default:
            $respuesta = $arrayerror;
            break;
    }

} catch (Exception $e) {
    $respuesta = [
        "respuesta" => "ERROR",
        "mensaje"   => $e->getMessage()
    ];
} finally {
    echo json_encode($respuesta, JSON_FORCE_OBJECT);
}
