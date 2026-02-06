<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include($_SERVER["DOCUMENT_ROOT"]."/assets/php/classes/Pagos.php");

$p = new Pagos();
$clientes = $p->getClientesPedidos(array(
    "idvendedor" => $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]
));

$formaspago = $p->getFormasPago();
?>
<div class="p-4" style="width:50%">
    <div class="row">
        <div class="col-12 text-center">
            <h3>Agregar pago</h3>
        </div>
    </div>
    <hr>
    <form id="formAgregarPago" name="formAgregarPago">
        <input type="hidden" name="controlador" id="controlador" value="pagos">
        <input type="hidden" name="proceso" id="proceso" value="agregarPago">
        <div class="row">
            <div class="col-12 col-sm-5">
                <label for="">Cliente</label>
                <select name="slcCliente" id="slcCliente" class="form-control select2" onchange="filtrarPedidosPago(<?= $_SESSION['v3nd3d0rpl4y3r4spvc1sn3usr'] ?>)">
                    <option value="0">--SELECCIONAR--</option>
                    <?
                    foreach($clientes["clientes"] as $tmp){
                    ?>
                    <option value="<?= $tmp["cliente"] ?>"><?= $tmp["cliente"] ?></option>
                    <?
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 col-sm-5">
                <label for="">Forma de pago</label>
                <select name="slcFormaPago" id="slcFormaPago" class="form-control">
                    <option value="0">--SELECCIONAR--</option>
                    <?
                    foreach($formaspago["formaspago"] as $tmp){
                    ?>
                    <option value="<?= $tmp["idformapago"] ?>"><?= $tmp["nombre"] ?></option>
                    <?
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 col-sm-2">
                <label for="">Fecha</label>
                <input type="text" id="txtFecha" name="txtFecha" class="form-control">
            </div>
        </div>
        <hr>
        <div id="listaPedidos">
            <div class="alert alert-warning mb-0">
				Debes seleccionar un cliente primero
			</div>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
    $("#txtFecha").datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        endDate: new Date()
    });

    $(".select2","#formAgregarPago").select2({
        dropdownParent: $('#formAgregarPago')
    });
});
</script>