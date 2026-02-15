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
                    <option value="<?= $tmp["idcliente"]."-".$tmp["cliente"] ?>"><?= $tmp["cliente"] ?></option>
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
        <hr>
        <div class="row">
            <div class="col-12 col-sm-auto ml-auto text-center">
                <button type="button" class="btn btn-success" onclick="registrarPago()">
                    <i class="fas fa-save"></i> Registrar Pago
                </button>
            </div>
        </div>
    </form>
</div>
<style>
.swal2-container {
    z-index: 999999 !important;
}
</style>
<script>
// Usar setTimeout para asegurar que el fancybox ya esté renderizado
setTimeout(function(){
    $("#txtFecha").datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        endDate: new Date()
    });

    $("#slcCliente").select2({
        dropdownParent: $('body'),
        width: '100%'
    }).on('select2:open', function() {
        // Ajustar z-index cuando se abre el dropdown (mayor que fancybox)
        $('.select2-container--open').css('z-index', 999999);
        $('.select2-dropdown').css('z-index', 999999);
        $('.select2-drop').css('z-index', 999999);
    });
}, 100);

function registrarPago() {
    // Validar campos obligatorios
    var cliente = $("#slcCliente").val();
    var formaPago = $("#slcFormaPago").val();
    var fecha = $("#txtFecha").val();

    if (cliente == "0" || cliente == "") {
        Swal.fire({
            type: 'warning',
            title: 'Atención',
            text: 'Debes seleccionar un cliente'
        });
        return;
    }

    if (formaPago == "0" || formaPago == "") {
        Swal.fire({
            type: 'warning',
            title: 'Atención',
            text: 'Debes seleccionar una forma de pago'
        });
        return;
    }

    if (fecha == "") {
        Swal.fire({
            type: 'warning',
            title: 'Atención',
            text: 'Debes ingresar una fecha'
        });
        return;
    }

    // Recolectar pedidos con pago capturado > 0
    var pedidos = [];
    var tieneFacturados = false;
    var tieneNoFacturados = false;
    $("#listaPedidos input[name^='txtPago']").each(function() {
        var pagoRecibido = parseFloat($(this).val()) || 0;

        if (pagoRecibido > 0) {
            // Extraer el idpedido del formato txtPago[123]
            var nombre = $(this).attr('name');
            var idpedido = nombre.match(/\[(\d+)\]/)[1];
            var idfactura = $(this).data("idfactura");
            var esFacturado = idfactura != null && idfactura !== "" && parseInt(idfactura) > 0;

            pedidos.push({
                idpedido: idpedido,
                monto: pagoRecibido,
                idfactura: esFacturado ? idfactura : 0
            });

            if (esFacturado) {
                tieneFacturados = true;
            } else {
                tieneNoFacturados = true;
            }
        }
    });

    // Validar que haya al menos un pedido con pago capturado
    if (pedidos.length == 0) {
        Swal.fire({
            type: 'warning',
            title: 'Atención',
            text: 'Debes capturar al menos un pedido con pago recibido mayor a $0'
        });
        return;
    }

    // Validar que todos los pedidos sean del mismo tipo (facturado o no facturado)
    if (tieneFacturados && tieneNoFacturados) {
        Swal.fire({
            type: 'warning',
            title: 'Atención',
            text: 'No se puede registrar un pago para pedidos facturados y sin facturar al mismo tiempo'
        });
        return;
    }

    var complemento = tieneFacturados ? 1 : 0;

    // Calcular monto total del pago
    var montoTotal = 0;
    pedidos.forEach(function(p) {
        montoTotal += p.monto;
    });

    var montoFormateado = "$" + montoTotal.toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Confirmar antes de registrar
    Swal.fire({
        type: 'question',
        title: 'Confirmar pago',
        text: '¿Deseas registrar un pago por ' + montoFormateado + '?',
        showCancelButton: true,
        confirmButtonText: 'Sí, registrar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (!result.value) return;

        // Preparar datos para enviar
        var datos = {
            controlador: 'pagos',
            proceso: 'agregarPago',
            cliente: cliente,
            idformapago: formaPago,
            fecha: fecha,
            pedidos: pedidos,
            complemento: complemento
        };

        // Enviar al backend
        $.ajax({
            url: '/assets/php/controladores/pagos.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            beforeSend: function() {
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Registrando pago',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function(response) {
                if (response.success) {
                    // Imprimir tickets
                    if (response.tickets && response.tickets.length > 0) {
                        response.tickets.forEach(function(ticket) {
                            imprimirTicket(ticket.idticket, 0, 4, ticket.copias);
                        });
                    }

                    Swal.fire({
                        type: 'success',
                        title: 'Éxito',
                        text: response.message || 'Pago registrado correctamente'
                    }).then(() => {
                        $.fancybox.close();
                        $(".formBusqueda").trigger("submit");
                    });
                } else {
                    Swal.fire({
                        type: 'error',
                        title: 'Error',
                        text: response.message || 'Error al registrar el pago'
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud: ' + error
                });
            }
        });

    });
}
</script>