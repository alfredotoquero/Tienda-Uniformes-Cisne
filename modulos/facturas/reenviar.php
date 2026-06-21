<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/2cnytm029mp3r/cm293uc5904uh.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/qxom385u3mfg3.php");

$idfactura = isset($_GET["idfactura"]) ? intval($_GET["idfactura"]) : 0;

if (!$idfactura) {
    echo '<div class="alert alert-danger">Parámetro inválido</div>';
    exit;
}

$idfactura = mysqli_real_escape_string($con, $idfactura);

$query = "
SELECT
    f.idfactura,
    f.serie,
    f.folio,
    f.total,
    f.timbrado
FROM
    tfacturas f
WHERE
    f.idfactura = '$idfactura'";

$factura = mysqli_fetch_assoc(mysqli_query($con, $query));

if (!$factura) {
    echo '<div class="alert alert-danger">Factura no encontrada</div>';
    exit;
}

$correoPrecargado = "";
$queryCorreo = "
SELECT
    c.correo,
    c.correos_adicionales
FROM
    ttickets t
LEFT JOIN
    tclientes c ON c.idcliente = t.idcliente
WHERE
    t.idfactura = '$idfactura'
LIMIT 1";
$datosCorreo = mysqli_fetch_assoc(mysqli_query($con, $queryCorreo));
if ($datosCorreo) {
    $partes = array_filter([$datosCorreo["correo"], $datosCorreo["correos_adicionales"]]);
    $correoPrecargado = implode(",", $partes);
}

unset($_SESSION["authToken"]);
$_SESSION["authToken"] = sha1(uniqid(microtime(), true));

$fechaFormateada = date("d/m/Y H:i", strtotime($factura["timbrado"]));
?>
<div id="divReenviar" style="width:500px;">
    <div class="row">
        <div class="col-12">
            <h4 class="header-title">Reenviar factura <?= $factura["serie"] . "-" . $factura["folio"] ?></h4>
        </div>
    </div>
    <hr>
    <form id="formReenviar" name="formReenviar">
        <input type="hidden" name="controlador" value="facturas">
        <input type="hidden" name="accion" value="reenviar">
        <input type="hidden" name="idusuario" value="<?= $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] ?>">
        <input type="hidden" name="idfactura" value="<?= (int)$_GET["idfactura"] ?>">
        <input type="hidden" name="authToken" value="<?= $_SESSION["authToken"] ?>">
        <div class="row mb-3">
            <div class="col-6">
                <label class="form-label">Total</label><br>
                $<?= number_format($factura["total"], 2) ?>
            </div>
            <div class="col-6">
                <label class="form-label">Fecha</label><br>
                <?= $fechaFormateada ?>
            </div>
        </div>
        <div class="mb-3">
            <label for="txtCorreoReenviar" class="form-label">Correo electrónico<span>*</span></label>
            <input type="text" class="form-control requerido" name="txtCorreo" id="txtCorreoReenviar"
                placeholder="Ingresa el correo electrónico" autocomplete="off"
                data-mensajeerror="Debes indicar el correo electrónico"
                value="<?= htmlspecialchars($correoPrecargado) ?>"
                pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*"
                title="Ingresa uno o más correos electrónicos válidos separados por coma">
            <small class="text-muted d-block mt-1">Para múltiples destinatarios, separa los correos con coma</small>
        </div>
        <div class="mb-3">
            <label for="txtCorreoAdicionalReenviar" class="form-label">Correos adicionales <small class="text-muted">(opcional)</small></label>
            <input type="text" class="form-control" name="txtCorreoAdicional" id="txtCorreoAdicionalReenviar"
                placeholder="Ingresa correos adicionales" autocomplete="off"
                pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*"
                title="Ingresa uno o más correos electrónicos válidos separados por coma">
            <small class="text-muted d-block mt-1">Correos extra separados por coma. No se guardarán en el sistema.</small>
        </div>
        <button type="button" onclick="validarFormReenviarFactura();" class="btn btn-primary">Reenviar</button>
    </form>
</div>
<script>
function validarFormReenviarFactura() {
    var regexCorreo = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
    var correos = $("#txtCorreoReenviar").val().split(",");
    for (var i = 0; i < correos.length; i++) {
        if (!regexCorreo.test(correos[i].trim())) {
            swalFocus("Error", "El correo '" + correos[i].trim() + "' no es válido", "error", "txtCorreoReenviar");
            return;
        }
    }
    var adicionales = $("#txtCorreoAdicionalReenviar").val().trim();
    if (adicionales !== "") {
        var arrAdicionales = adicionales.split(",");
        for (var i = 0; i < arrAdicionales.length; i++) {
            if (!regexCorreo.test(arrAdicionales[i].trim())) {
                swalFocus("Error", "El correo adicional '" + arrAdicionales[i].trim() + "' no es válido", "error", "txtCorreoAdicionalReenviar");
                return;
            }
        }
    }
    validarFormulario('formReenviar');
}
</script>
