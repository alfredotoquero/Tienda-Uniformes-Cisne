<div class="modulo" data-modulo="pagos">
<!-- Header -->
<div class="content-header white  box-shadow-0" id="content-header">
	<div class="navbar navbar-expand-lg">
		<!-- btn to toggle sidenav on small screen -->
		<a class="d-lg-none mx-2" data-toggle="modal" data-target="#aside">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512">
				<path d="M80 304h352v16H80zM80 248h352v16H80zM80 192h352v16H80z" />
			</svg>
		</a>
		<!-- Page title -->
		<div class="navbar-text nav-title flex" id="pageTitle">Pagos</div>
	</div>
</div>

<div class="padding">
	<div class="box">
		<div class="box-header">
			<form class="formBusqueda">
                <input type="hidden" name="contenedor" id="contenedor" value="listaPagos">
                <input type="hidden" name="archivo" id="archivo" value="/modulos/pagos/lista.php">
				<div class="row" style="margin-bottom: 20px;">
					<div class="col-12 col-sm-2">
						<input type="text" class="form-control" name="txtFechaInicial" id="txtFechaInicial" value="<?= date("Y-m-d") ?>" placeholder="Fecha Inicial">
					</div>
					<div class="col-12 col-sm-2">
						<input type="text" class="form-control" name="txtFechaFinal" id="txtFechaFinal" value="<?= date("Y-m-d") ?>" placeholder="Fecha Final">
					</div>
					<div class="col-12 col-sm-1">
						<button type="submit" class="btn btn-primary waves-effect waves-light">Filtrar</button>
					</div>
					<div class="col-12 col-sm-auto ml-sm-auto">
						<a href="/modulos/pagos/agregar.php" data-fancybox data-type="ajax" data-options='{"touch":false}' class="btn btn-primary waves-effect waves-light">Agregar</a>
					</div>
				</div>
			</form>
		</div>

		<div class="box-body pt-0" id="listaPagos"></div>
    </div>
</div>
</div>
<script>
$(document).ready(function(){
    // Inicializar datepickers
    $("#txtFechaInicial").datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        language: "es"
    }).on("changeDate", function(e){
        // Cuando cambia la fecha inicial, actualizar la fecha mínima del datepicker final
        $("#txtFechaFinal").datepicker("setStartDate", e.date);
    });

    $("#txtFechaFinal").datepicker({
        format: "yyyy-mm-dd",
        autoclose: true,
        todayHighlight: true,
        language: "es"
    }).on("changeDate", function(e){
        // Cuando cambia la fecha final, actualizar la fecha máxima del datepicker inicial
        $("#txtFechaInicial").datepicker("setEndDate", e.date);
    });

    // Validación adicional en el submit del formulario
    $(".formBusqueda").on("submit", function(e){
        var fechaInicial = new Date($("#txtFechaInicial").val());
        var fechaFinal = new Date($("#txtFechaFinal").val());

        if(fechaFinal < fechaInicial){
            e.preventDefault();
            alert("La fecha final no puede ser menor a la fecha inicial");
            return false;
        }
    });
});
</script>