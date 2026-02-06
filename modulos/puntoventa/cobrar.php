<?
unset($_SESSION["authToken"]);
$_SESSION["authToken"]=sha1(uniqid(microtime(), true));
?>

<script>
function pagar(tipoPago) {
    // validar monto
    // pagar
    // recalcular
    // validar
    // actualizar "por pagar"
    // limpiar txtMonto

    if(montoValido()){
        switch (tipoPago) {
            case 'MXN':
                $("#conMXN").val(Number($("#conMXN").val()) + Number($("#txtMonto").val()));
                
                break;
            case 'USD':
                $("#conUSD").val(Number($("#conUSD").val()) + Number($("#txtMonto").val()));
    
                break;
            case 'Tarjeta':
                $("#conTarjeta").val(Number($("#conTarjeta").val()) + Number($("#txtMonto").val()));
    
                break;
        
            default:
                break;
        }
    
        var totalPagado = Number($("#conMXN").val()) + (Number($("#conUSD").val()) * 18) + Number($("#conTarjeta").val());
        var total = Number($("#total").val());
    
        var porPagar = total - totalPagado;
        var cambio = (porPagar < 0 ? Math.abs(porPagar) : 0);
        porPagar = (porPagar <= 0 ? 0 : porPagar);
    
        $("#lblRestante").html("$" + formatMoney(porPagar));
        if (cambio>0) {
            $("#lblCambio").html("Cambio: $" + formatMoney(cambio));
        }
    
        // si ya se ha pagado, se puede proceder
        // NOTA: ¿cómo se debe continuar? 1. que continue automaticamente, 2. que aparezca un botón para picarle y continuar
    
        $("#txtMonto").val("");
    }else{
        alert("Debes introducir un monto válido");
    }
}

function montoValido() {
    if ($("#txtMonto").val()=="") {
        return false;
    }else if(Number($("#txtMonto").val())<=0){
        return false;
    }
    return true;
}

function deshacer() {
    // regresar los tres tipos a cero, y el total por pagar se iguala al total
    $("#conMXN").val("");
    $("#conUSD").val("");
    $("#conTarjeta").val("");
    $("#lblRestante").html($("#lblTotal").html());
    $("#lblCambio").html("");
}

function formatMoney(n, c, d, t) {
  var c = isNaN(c = Math.abs(c)) ? 2 : c,
    d = d == undefined ? "." : d,
    t = t == undefined ? "," : t,
    s = n < 0 ? "-" : "",
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
    j = (j = i.length) > 3 ? j % 3 : 0;

  return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

$(document).ready(function (e) {
    $('#txtMonto').on('input blur paste', function(){
        $(this).val($(this).val().replace(/\D/g, ''));
    });
});
</script>

<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
	  <!-- btn to toggle sidenav on small screen -->
	  <a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z"/></svg>
	  </a>
	  <!-- Page title -->
	  <div class="navbar-text nav-title flex" id="pageTitle">Cobrar</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<!-- <div class="box-header">

		</div> -->

		<div class="box-body">
            <form action="" name="formCobrar" id="formCobrar" method="post" action="">
                <div class="row">
                    <div class="col-12">
                        <input type="hidden" name="subtotal" id="subtotal" value="<? echo $_POST["subtotal"]; ?>">
                        <input type="hidden" name="iva" id="iva" value="<? echo $_POST["iva"]; ?>">
                        <input type="hidden" name="accion" id="accion" value="<? echo $_POST["accion"]; ?>">
                        <input type="hidden" name="total" id="total" value="<? echo $_POST["total"]; ?>">
                        <input type="hidden" name="txtDescuento" id="txtDescuento" value="<? echo $_POST["txtDescuento"]; ?>">

                        <input type="hidden" name="conMXN" id="conMXN" value="">
                        <input type="hidden" name="conUSD" id="conUSD" value="">
                        <input type="hidden" name="conTarjeta" id="conTarjeta" value="">
                        <input type="text" name="txtMonto" id="txtMonto" class="form-control" placeholder="Introduce un monto">
                    </div>
                </div>
                <div class="row" style="margin-top:30px;">
                    <div class="col">
                        <button type="button" class="btn btn-primary waves-light waves-effect" onClick="pagar('MXN');" style="margin-left:15px;">Efectivo MXN</button>
                        <button type="button" class="btn btn-primary waves-light waves-effect" onClick="pagar('USD');" style="margin-left:15px;">Efectivo USD</button>
                        <button type="button" class="btn btn-primary waves-light waves-effect" onClick="pagar('Tarjeta');" style="margin-left:15px;">Tarjeta</button>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-danger waves-light waves-effect pull-right" onClick="deshacer();" style="margin-left:15px;">Deshacer</button>
                    </div>
                </div>
                <div class="row" style="margin-top:30px;">
                    <div class="col">
                        <b><label for="" class="pull-right" style="margin-left:15px;">Total: <span id="lblTotal">$<? echo number_format($_POST["total"],2); ?></span></label></b>
                    </div>
                </div>
                <div class="row" style="margin-top:30px;">
                    <div class="col">
                        <b><label for="" class="pull-right" style="margin-left:15px;">Por pagar: <span id="lblRestante">$<? echo number_format($_POST["total"],2); ?></span></label></b>
                    </div>
                </div>
                <div class="row" style="margin-top:30px;">
                    <div class="col">
                        <b><label for="" class="pull-right" style="margin-left:15px;"><span id="lblCambio"></span></label></b>
                    </div>
                </div>
            </form>
		</div>
	</div>
</div>