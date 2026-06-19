<?php
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/qxom385u3mfg3.php");

include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/SAT.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Tickets.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Emisores.php");

$sat = new SAT();
$t = new Tickets();
$e = new Emisores();

$ticket = $t->obtenerTicket(array(
    "idticket" => $_GET["idticket"]
))["ticket"];

if(!empty($ticket["idtienda"])){
    $regimenesfiscales = $sat->obtenerRegimenesFiscales()["regimenesfiscales"];
    $usoscfdi = $sat->obtenerUsosCFDI()["usoscfdi"];
    $metodospago = $sat->obtenerMetodosPago()["metodospago"];
    $formaspago = $sat->obtenerFormasPago()["formaspago"];

    $emisores = $e->obtenerEmisores(array())["emisores"];

    unset($_SESSION["authToken"]);
    $_SESSION["authToken"]=sha1(uniqid(microtime(), true));
    ?>
    <div id="divFacturar" style="width:500px;">
        <div class="row">
            <div class="col-12">
                <h4 class="header-title">Facturar ticket #<?= $ticket["folio"] ?></h4>
            </div>
        </div>
        <hr>
        <form id="formFacturar" name="formFacturar">
            <input type="hidden" name="controlador" id="controlador" value="tickets">
            <input type="hidden" name="accion" id="accion" value="facturar">
            <input type="hidden" name="idusuario" id="idusuario" value="<?= $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] ?>">
            <input type="hidden" name="idticket" id="idticket" value="<?= $_GET["idticket"] ?>">
            <input type="hidden" name="authToken" value="<? echo $_SESSION["authToken"]; ?>">
            <div id="divNuevaRazonSocial">
                <div class="mb-3">
                    <label id="lblRazonSocial" for="txtRazonSocial" class="form-label">Razón social<span>*</span></label>
                    <input type="text" class="form-control uppercase nuevaRazonSocial" name="txtRazonSocial" id="txtRazonSocial" placeholder="Ingresa la razón social" autocomplete="off" data-mensajeerror="Debes indicar la razón social">
                </div>
                <div class="mb-3">
                    <label for="txtRFC" class="form-label">RFC<span>*</span></label>
                    <input type="text" class="form-control uppercase nuevaRazonSocial" name="txtRFC" id="txtRFC" placeholder="Ingresa el RFC" autocomplete="off" data-mensajeerror="Debes indicar el RFC">
                </div>
                <div class="mb-3">
                    <label for="txtCodigoPostal" class="form-label">Código postal<span>*</span></label>
                    <input type="text" class="form-control nuevaRazonSocial" name="txtCodigoPostal" id="txtCodigoPostal" placeholder="Ingresa el código postal" autocomplete="off" data-mensajeerror="Debes indicar el código postal">
                </div>
                <div class="mb-3">
                    <label for="slcRegimenFiscal" class="form-label">Régimen fiscal<span>*</span></label>
                    <select class="nuevaRazonSocial select2" name="slcRegimenFiscal" id="slcRegimenFiscal" data-mensajeerror="Debes indicar el régimen fiscal">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($regimenesfiscales as $regimenfiscal) {
                            ?>
                            <option value="<?= $regimenfiscal["idregimenfiscal"]; ?>"><?= $regimenfiscal["regimenfiscal"]." - ".$regimenfiscal["descripcion"]; ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="slcUsoCFDI" class="form-label">Uso del CFDI<span>*</span></label>
                    <select class="nuevaRazonSocial select2" name="slcUsoCFDI" id="slcUsoCFDI" data-mensajeerror="Debes indicar el uso del CFDI">
                        <option value="0">--Seleccionar--</option>
                        <?
                        foreach ($usoscfdi as $usocfdi) {
                            ?>
                            <option value="<?= $usocfdi["idusocfdi"]; ?>"><?= $usocfdi["usocfdi"]." - ".$usocfdi["descripcion"]; ?></option>
                            <?
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="txtCorreo" class="form-label">Correo electrónico<span>*</span></label>
                <input type="text" class="form-control requerido" name="txtCorreo" id="txtCorreo" placeholder="Ingresa el correo electrónico" autocomplete="off" data-mensajeerror="Debes indicar el correo electrónico" pattern="[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}(\s*,\s*[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})*" title="Ingresa uno o más correos electrónicos válidos separados por coma">
                <small class="text-muted d-block mt-1">Para enviar a múltiples destinatarios, separa los correos con coma (ej: correo1@ejemplo.com, correo2@ejemplo.com)</small>
            </div>
            <div class="mb-3">
                <label for="slcMetodoPago" class="form-label">Método de pago<span>*</span></label>
                <select class="form-control requerido" name="slcMetodoPago" id="slcMetodoPago" onchange="validarMetodoPago();" data-mensajeerror="Debes indicar el método de pago">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($metodospago as $metodopago) {
                        ?>
                        <option value="<?= $metodopago["idmetodopago"]; ?>"><?= $metodopago["metodopago"]." - ".$metodopago["descripcion"]; ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="slcFormaPago" class="form-label">Forma de pago<span>*</span></label>
                <select class="requerido select2" name="slcFormaPago" id="slcFormaPago" data-mensajeerror="Debes indicar la forma de pago">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($formaspago as $formapago) {
                        ?>
                        <option value="<?= $formapago["idformapago"]; ?>"><?= $formapago["formapago"]." - ".$formapago["descripcion"]; ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="slcEmisor" class="form-label">Emisor<span>*</span></label>
                <select class="requerido select2" name="slcEmisor" id="slcEmisor" data-mensajeerror="Debes indicar un emisor">
                    <option value="0">--Seleccionar--</option>
                    <?
                    foreach ($emisores as $emisor) {
                        ?>
                        <option value="<?= $emisor["idemisor"] ?>"><?= $emisor["razon_social"]." - ".$emisor["rfc"]; ?></option>
                        <?
                    }
                    ?>
                </select>
            </div>
            <button type="button" onclick="validarFormFacturar();" class="btn btn-primary">Facturar</button>
        </form>
    </div>
    <script>
    $(document).ready(function () {
        $(".select2","#formFacturar").select2({
            dropdownParent: $('#divFacturar'),
            width: '100%'
        });

        $("#divNuevaRazonSocial").show();
        $(".nuevaRazonSocial").addClass("requerido");

        $("#slcFormaPago").val(0).trigger('change.select2').prop("disabled", true);
    });

    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('uppercase')) {
            e.target.value = e.target.value.toUpperCase();
        }
    });

    function validarFormFacturar(){
        var correos = $("#txtCorreo").val().split(",");
        var regexCorreo = /^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/;
        for(var i = 0; i < correos.length; i++){
            if(!regexCorreo.test(correos[i].trim())){
                swalFocus("Error", "El correo electrónico '"+correos[i].trim()+"' no es válido", "error", "txtCorreo");
                return;
            }
        }
        validarFormulario('formFacturar');
    }

    function validarMetodoPago(){
        if($("#slcMetodoPago").val() == 1){ //PPD
            $('#slcFormaPago option[value="21"]').prop('disabled', false);
            $("#slcFormaPago").val(21).trigger('change.select2');
            $("#slcFormaPago").prop("disabled", true);
        }else{
            $('#slcFormaPago option[value="21"]').prop('disabled', true);
            $("#slcFormaPago").val(0).trigger('change.select2');
            $("#slcFormaPago").prop("disabled", false);
        }
    }
    </script>
<?
}else{
?>
<script>
    $.fancybox.close();
    Swal.fire("Atención", "Debes asignar una tienda a la sucursal <?= $ticket["sucursal"] ?>", "warning");
</script>
<?
}
?>