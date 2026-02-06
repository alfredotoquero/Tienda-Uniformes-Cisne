<?
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/c91ktn24g7if5u.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/2cnytm029mp3r/cm293uc5904uh.php");
include_once($_SERVER["DOCUMENT_ROOT"] . "/vm39845um223u/qxom385u3mfg3.php");

$vendedor = mysqli_fetch_assoc(mysqli_query($con, "select * from tvendedores where idvendedor = '" . $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"] . "'"));
$corte = mysqli_query($con, "select * from tcortessucursales where idsucursal = '" . $vendedor["idsucursal"] . "' and status = 'A'");
$sucursal = mysqli_fetch_assoc(mysqli_query($con, "select * from tsucursales where idsucursal = '" . $vendedor["idsucursal"] . "'"));

function fecha_formateada($fecha)
{
	$fecha = explode(" ", $fecha);
	$hora = $fecha[1];
	$fecha = $fecha[0];

	$fecha = explode("-", $fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia . "/";

	switch ($mes) {
		case "01":
			$fecha .= "Ene";
			break;
		case "02":
			$fecha .= "Feb";
			break;
		case "03":
			$fecha .= "Mar";
			break;
		case "04":
			$fecha .= "Abr";
			break;
		case "05":
			$fecha .= "May";
			break;
		case "06":
			$fecha .= "Jun";
			break;
		case "07":
			$fecha .= "Jul";
			break;
		case "08":
			$fecha .= "Ago";
			break;
		case "09":
			$fecha .= "Sep";
			break;
		case "10":
			$fecha .= "Oct";
			break;
		case "11":
			$fecha .= "Nov";
			break;
		case "12":
			$fecha .= "Dic";
			break;
	}

	$fecha .= "/" . $ano;

	if ($hora != "") {
		$fecha .= "<br>" . date("h:i a", strtotime($hora));
	}

	return $fecha;
}

function fecha_formateada2($fecha)
{
	$fecha = explode(" ", $fecha);
	$hora = $fecha[1];
	$fecha = $fecha[0];

	$fecha = explode("-", $fecha);
	$dia = $fecha[2];
	$mes = $fecha[1];
	$ano = $fecha[0];

	$fecha = $dia . "/";

	switch ($mes) {
		case "01":
			$fecha .= "Ene";
			break;
		case "02":
			$fecha .= "Feb";
			break;
		case "03":
			$fecha .= "Mar";
			break;
		case "04":
			$fecha .= "Abr";
			break;
		case "05":
			$fecha .= "May";
			break;
		case "06":
			$fecha .= "Jun";
			break;
		case "07":
			$fecha .= "Jul";
			break;
		case "08":
			$fecha .= "Ago";
			break;
		case "09":
			$fecha .= "Sep";
			break;
		case "10":
			$fecha .= "Oct";
			break;
		case "11":
			$fecha .= "Nov";
			break;
		case "12":
			$fecha .= "Dic";
			break;
	}

	$fecha .= "/" . $ano;

	if ($hora != "") {
		$fecha .= " " . date("h:i a", strtotime($hora));
	}

	return $fecha;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title><? echo $sucursal["nombre"]; ?> | Punto de Venta </title>
	<meta name="description" content="Responsive, Bootstrap, BS4" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- for ios 7 style, multi-resolution icon of 152x152 -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
	<link rel="apple-touch-icon" href="/assets/images/logo.svg">
	<meta name="apple-mobile-web-app-title" content="Flatkit">
	<!-- for Chrome on Android, multi-resolution icon of 196x196 -->
	<meta name="mobile-web-app-capable" content="yes">
	<link rel="shortcut icon" sizes="196x196" href="/assets/images/logo.svg">

	<!-- style -->

	<link rel="stylesheet" href="/libs/font-awesome/css/font-awesome.min.css" type="text/css" />

	<!-- build:css /assets/css/app.min.css -->
	<link rel="stylesheet" href="/libs/bootstrap/dist/css/bootstrap.min.css" type="text/css" />
	<link rel="stylesheet" href="/assets/css/app.css" type="text/css" />
	<link rel="stylesheet" href="/assets/css/style.css" type="text/css" />
	<link href="assets/plugins/select2/dist/css/select2.css" rel="stylesheet" type="text/css">
	<link href="assets/plugins/select2/dist/css/select2-bootstrap.css" rel="stylesheet" type="text/css">

	<script src="assets/plugins/moment/moment.js"></script>


	<!-- Datepicker and Wickedpicker CSS -->
	<link href="libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
	<link href="assets/plugins/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
	<link href="libs/wickedpicker/dist/wickedpicker.min.css" rel="stylesheet">

	<!-- jQuery -->
	<!--<script src="/libs/jquery/dist/jquery.min.js"></script>
  <script src="/assets/plugins/jquery-ui/jquery-ui.js"></script>-->
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


	<!-- Datepicker and Wickedpicker JS -->
	<script src="libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
	<script src="assets/plugins/bootstrap-daterangepicker/daterangepicker.js"></script>
	<script src="libs/wickedpicker/dist/wickedpicker.min.js"></script>
	<script src="assets/plugins/select2/dist/js/select2.min.js" type="text/javascript"></script>

	<!--  -->
	<link rel="stylesheet" href="assets/css/sweetalert2.min.css">
	<script src="assets/js/sweetalert2.min.js"></script>

	<!-- Optional: include a polyfill for ES6 Promises for IE11 and Android browser -->
	<script src="assets/js/sweetalert2.all.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/promise-polyfill"></script>
	<!--  -->


	<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-lite.css" rel="stylesheet">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-lite.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css" />
	<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>

	<!-- KNOB JS -->
	<!--[if IE]>
  <script type="text/javascript" src="assets/plugins/jquery-knob/excanvas.js"></script>
  <![endif]-->
	<script src="assets/plugins/jquery-knob/jquery.knob.js"></script>

	<!-- <link rel="stylesheet" href="assets/css/jquery.toast.min.css">
  <script src="assets/js/jquery.toast.min.js"></script> -->

	<script src="assets/js/webprint.js"></script>

	<script>
		// $(document).ready(function(e){
		// 	setInterval(function(){ buscarNotificaciones(); }, 30000);
		// });

		var impresorassistema;
		var populatePrinters = function(printers) {
			impresorassistema = printers;
			<? if ($_GET["modulo1"] == "configuracion") { ?>
				cargarImpresoras();
			<? } ?>
		};

		webprint = new WebPrint(true, {
			relayHost: "127.0.0.1",
			relayPort: "8080",
			listPrinterCallback: populatePrinters,
			readyCallback: function() {
				webprint.requestPorts();
				webprint.requestPrinters();
			}
		});

		/**
		3 tipos de impresión de ticket
		1 = cobrar
		2 = apartar, primer abono
		3 = apartar, abonos posteriores

		impresiones = cantidad de "copias" del ticket, por default 1
		 */
		function imprimirTicket(idticket, cambio, tipo, impresiones = 1) {
			$("#divImpresion").html("");
			$.ajax({
				type: "POST",
				url: "modulos/puntoventa/imprimirticket.php",
				data: "idticket=" + idticket + "&cambio=" + cambio + "&tipo=" + tipo,
				async: false,
				cache: false,
				success: function(data) {
					$("#divImpresion").html(data);
					impresiones = impresiones - 1;
					if (impresiones > 0) {
						imprimirTicket(idticket, cambio, tipo, impresiones);
					}
				}
			});
		}

		function imprimirCorte(idcorte) {
			$("#divImpresion").html("");
			$.ajax({
				type: "POST",
				url: "modulos/puntoventa/imprimircorte.php",
				data: "idcorte=" + idcorte,
				async: false,
				cache: false,
				success: function(data) {
					$("#divImpresion").html(data);
				}
			});
		}


		function fancy(url, w, h) {
			$.fancybox.open({
				src: url,
				type: 'iframe',
				opts: {
					afterShow: function(instance, current) {
						$(this.content).attr("tabindex", 1).focus();
					},
					modal: false,
					iframe: {
						css: {
							width: w,
							height: h
						}
					}
				}
			});
		}

		function validate(evt) {
			var theEvent = evt || window.event;

			// Handle paste
			if (theEvent.type === 'paste') {
				key = event.clipboardData.getData('text/plain');
			} else {
				// Handle key press
				var key = theEvent.keyCode || theEvent.which;
				key = String.fromCharCode(key);
			}
			var regex = /[0-9]|\./;
			if (!regex.test(key)) {
				theEvent.returnValue = false;
				if (theEvent.preventDefault) theEvent.preventDefault();
			}

			// enter
			if (theEvent.keyCode == 13) {
				// agregarPago();
			}
		}

		function validarCantidad(valor) {
			var num = Number(valor);
			if (num == 0) {
				alert("Debes introducir una cantidad válida");
				return false;
			}
			return true;
		}

		// function buscarNotificaciones(){
		//   	$.ajax({
		// 		type:"POST",
		// 		url:"/ajax/buscarnotificaciones.php",
		// 		data:"idvendedor=<? echo $_SESSION["v3nd3d0rpl4y3r4spvc1sn3usr"]; ?>",
		// 		success:function(data){
		// 			var datos = data.split("|");
		// 			if(datos[0]=="OK"){
		// 				for(var i=1;i<datos.length;i++){
		// 					$.toast({
		// 						text: datos[i], // Text that is to be shown in the toast
		// 						showHideTransition: 'fade', // fade, slide or plain
		// 						allowToastClose: true, // Boolean value true or false
		// 						hideAfter: false, // false to make it sticky or number representing the miliseconds as time after which toast needs to be hidden
		// 						stack: 18, // false if there should be only one toast at a time or a number representing the maximum number of toasts to be shown at a time
		// 						position: 'bottom-right', // bottom-left or bottom-right or bottom-center or top-left or top-right or top-center or mid-center or an object representing the left, right, top, bottom values
		// 						bgColor: '#444444',  // Background color of the toast
		// 						textColor: '#eeeeee',  // Text color of the toast
		// 						textAlign: 'left',  // Text alignment i.e. left, right or center
		// 						loader: true,  // Whether to show loader or not. True by default
		// 						loaderBg: '#9EC600',  // Background color of the toast loader
		// 					});
		// 				}
		// 			}
		// 		}
		//   	});
		// }
	</script>
</head>

<body>

	<div id="divImpresion"></div>
	<div class="app" id="app">

		<!-- ############ LAYOUT START-->

		<!-- ############ Aside START-->
		<div id="aside" class="app-aside fade box-shadow-x nav-expand white" aria-hidden="true">
			<div class="sidenav modal-dialog dk white">
				<!-- sidenav top -->
				<div class="navbar lt">
					<!-- brand -->
					<a href="/home.php" class="navbar-brand">
						<span class="hidden-folded d-inline">Punto de Venta</span>
					</a>
					<!-- / brand -->
				</div>

				<!-- Flex nav content -->
				<div class="flex hide-scroll">
					<div class="scroll">
						<div class="nav-border b-primary" data-nav>
							<ul class="nav bg">

								<!-- <li class="nav-header">
		                  <div class="py-3">
		                      <a href="?modulo1=cotizaciones&modulo2=agregar" class="btn btn-sm success theme-accent btn-block">
		                        <i class="fa fa-fw fa-plus"></i>
		                        <span class="hidden-folded d-inline">************</span>
		                      </a>
		                  </div>
		              </li> -->
								<li class="nav-header hidden-folded mt-2">
									<span class="text-sm"><b>Sucursal</b></span><br>
									<span class="text-sm" style="color: white;"><? echo $sucursal["nombre"]; ?></span><br>
									<span class="text-sm"><b>Vendedor</b></span><br>
									<span class="text-sm" style="color: white;"><? echo $vendedor["nombre"]; ?></span>
								</li>
								<li class="nav-header hidden-folded mt-2">
									<span class="text-xs">Punto de Venta</span>
								</li>
								<li>
									<a href="?modulo1=puntoventa">
										<span class="nav-icon">
											<i class="fa fa-receipt"></i>
										</span>
										<span class="nav-text">Punto de Venta</span>
									</a>
								</li>
								<li>
									<a href="?modulo1=apartados">
										<span class="nav-icon">
											<i class="fa fa-file-invoice"></i>
										</span>
										<span class="nav-text">Apartados</span>
									</a>
								</li>
								<li>
									<a href="?modulo1=pedidos">
										<span class="nav-icon">
											<i class="fa fa-asterisk"></i>
										</span>
										<span class="nav-text">Pedidos Personalizados</span>
									</a>
								</li>
								<li>
									<a href="?modulo1=pedidossistema">
										<span class="nav-icon">
											<i class="fa fa-file-alt"></i>
										</span>
										<span class="nav-text">Pedidos Sistema</span>
									</a>
								</li>
								<li>
									<a href="?modulo1=pagos">
										<span class="nav-icon">
											<i class="fas fa-money-bill"></i>
										</span>
										<span class="nav-text">Pagos</span>
									</a>
								</li>
								<li class="nav-header hidden-folded mt-2">
									<span class="text-xs">Inventario</span>
								</li>
								<li>
									<a href="?modulo1=productos">
										<span class="nav-icon">
											<i class="fa fa-store"></i>
										</span>
										<span class="nav-text">Productos</span>
									</a>
								</li>

								<li>
									<a href="?modulo1=movimientos">
										<span class="nav-icon">
											<i class="fa fa-exchange-alt"></i>
										</span>
										<span class="nav-text">Movimientos al inventario</span>
									</a>
								</li>

								<li class="nav-header hidden-folded mt-2">
									<span class="text-xs">Reportes</span>
								</li>
								<li>
									<a href="?modulo1=reportes&modulo2=productosvendidos">
										<span class="nav-icon">
											<i class="fa fa-chart-area"></i>
										</span>
										<span class="nav-text">Productos Vendidos</span>
									</a>
								</li>

								<li class="nav-header hidden-folded mt-2">
									<span class="text-xs">Administración</span>
								</li>
								<li>
									<a href="?modulo1=cortes">
										<span class="nav-icon">
											<i class="fa fa-money-bill"></i>
										</span>
										<span class="nav-text">Cortes</span>
									</a>
								</li>
								<li>
									<a href="?modulo1=configuracion">
										<span class="nav-icon">
											<i class="fa fa-cogs"></i>
										</span>
										<span class="nav-text">Configuración</span>
									</a>
								</li>

								<li class="nav-header hidden-folded mt-2">
									<span class="text-xs">Usuario</span>
								</li>
								<li>
									<a href="vm39845um223u/b56bn627tb30td.php" onclick="return confirm('¿Deseas cerrar sesión?');">
										<span class="nav-icon">
											<i class="fa fa-sign-out-alt"></i>
										</span>
										<span class="nav-text">Cerrar Sesión</span>
									</a>
								</li>

							</ul>
						</div>
					</div>
				</div>

				<!-- sidenav bottom -->
				<!-- <div class="no-shrink lt">
		    <div class="nav-fold">
		    	<a class="d-flex p-2-3" data-toggle="dropdown">
		    	    <span class="avatar w-28 grey hide">J</span>
		    	    <img src="/assets/images/a0.jpg" alt="..." class="w-28 circle">
		    	</a>
		    	<div class="dropdown-menu  w pt-0 mt-2 animate fadeIn">
		    	  <div class="row no-gutters b-b mb-2">
		    	    <div class="col-4 b-r">
		    	      <a href="app.user.html" class="py-2 pt-3 d-block text-center">
		    	        <i class="fa text-md fa-phone-square text-muted"></i>
		    	        <small class="d-block">Call</small>
		    	      </a>
		    	    </div>
		    	    <div class="col-4 b-r">
		    	      <a href="app.message.html" class="py-2 pt-3 d-block text-center">
		    	        <i class="fa text-md fa-comment text-muted"></i>
		    	        <small class="d-block">Chat</small>
		    	      </a>
		    	    </div>
		    	    <div class="col-4">
		    	      <a href="app.inbox.html" class="py-2 pt-3 d-block text-center">
		    	        <i class="fa text-md fa-envelope text-muted"></i>
		    	        <small class="d-block">Email</small>
		    	      </a>
		    	    </div>
		    	  </div>
		    	  <a class="dropdown-item" href="profile.html">
		    	    <span>Profile</span>
		    	  </a>
		    	  <a class="dropdown-item" href="setting.html">
		    	    <span>Settings</span>
		    	  </a>
		    	  <a class="dropdown-item" href="app.inbox.html">
		    	    <span class="float-right"><span class="badge info">6</span></span>
		    	    <span>Inbox</span>
		    	  </a>
		    	  <a class="dropdown-item" href="app.message.html">
		    	    <span>Message</span>
		    	  </a>
		    	  <div class="dropdown-divider"></div>
		    	  <a class="dropdown-item" href="docs.html">
		    	    Need help?
		    	  </a>
		    	  <a class="dropdown-item" href="signin.html">Sign out</a>
		    	</div>
		    	<div class="hidden-folded flex p-2-3 bg">
		    		<div class="d-flex p-1">
		    			<a href="app.inbox.html" class="flex text-nowrap">
		    				<i class="fa fa-bell text-muted mr-1"></i>
		    				<span class="badge badge-pill theme">20</span>
		    			</a>
		    			<a href="/vm39845um223u/b56bn627tb30td.php" class="px-2" data-toggle="tooltip" title="Logout">
		    				<i class="fa fa-power-off text-muted"></i>
		    			</a>
		    		</div>
		    	</div>
		    </div>
		  </div> -->
			</div>
		</div>
		<!-- ############ Aside END-->

		<!-- ############ Content START-->
		<div id="content" class="app-content box-shadow-0" role="main">

			<?
			switch ($_GET["modulo1"]) {
				case "puntoventa":
					switch ($_GET["modulo2"]) {
						case 'cobrar':
							include("modulos/puntoventa/cobrar.php");
							break;
						case 'apartar':
							include("modulos/puntoventa/apartar.php");
							break;
						default:
							include("modulos/puntoventa.php");
							break;
					}
					break;

				case "apartados":
					switch ($_GET["modulo2"]) {
						case 'ver':
							include("modulos/apartados/ver.php");
							break;
						case 'pagos':
							include("modulos/apartados/pagos.php");
							break;
						default:
							include("modulos/apartados.php");
							break;
					}
					break;

				case "pedidos":
					switch ($_GET["modulo2"]) {
						case 'ver':
							include("modulos/pedidospersonalizados/ver.php");
							break;
						default:
							include("modulos/pedidospersonalizados.php");
							break;
					}
					break;

				case "pedidossistema":
					switch ($_GET["modulo2"]) {
						case 'ver':
							include("modulos/pedidossistema/ver.php");
							break;
						default:
							include("modulos/pedidossistema.php");
							break;
					}
					break;
				
				case "pagos":
					include("modulos/pagos.php");
					break;

				case "movimientos":
					switch ($_GET["modulo2"]) {
						case "agregar":
							include("modulos/movimientos/agregar.php");
							break;
						case "ver":
							include("modulos/movimientos/ver.php");
							break;
						default:
							include("modulos/movimientos.php");
							break;
					}
					break;

				case "productos":
					switch ($_GET["modulo2"]) {
						case "kardex":
							include("modulos/productos/kardex.php");
							break;
						default:
							include("modulos/productos.php");
							break;
					}
					break;

				case "reportes":
					switch ($_GET["modulo2"]) {
						case 'productosvendidos':
							include("modulos/reportes/productosvendidos.php");
							break;
					}
					break;

				case "cortes":
					switch ($_GET["modulo2"]) {
						case "detallev":
							switch ($_GET["modulo3"]) {
								case "detallecuenta":
									include("modulos/cortes/detalleticket.php");
									break;
								default:
									include("modulos/cortes/detalleventas.php");
									break;
							}
							break;
						case "detalleg":
							switch ($_GET["modulo3"]) {
								case "detallecuenta":
									include("modulos/cortes/detalleticketgastos.php");
									break;
								default:
									include("modulos/cortes/detallegastos.php");
									break;
							}
							break;
						case "devolucion":
							include("modulos/cortes/devolucion.php");
							break;
						case "cambiarproductos":
							switch ($_GET["modulo3"]) {
								case "seleccionarcambio":
									include("modulos/cortes/cambiar/seleccionarcambio.php");
									break;
								default:
									include("modulos/cortes/cambiarproductos.php");
									break;
							}
							break;
						case "detallecuenta":
							include("modulos/cortes/detalleticket.php");
							break;
						default:
							include("modulos/cortes.php");
							break;
					}
					break;
				case "notificaciones":
					include("modulos/notificaciones.php");
					break;
				case "configuracion":
					include("modulos/configuracion.php");
					break;

				default:
					include("modulos/puntoventa.php");
					break;
			}
			?>

			<!-- Footer -->
			<div class="content-footer white " id="content-footer">
				<div class="d-flex p-3">
					<span class="text-sm text-muted flex">&copy; Copyright. Playeras Cisne</span>
					<div class="text-sm text-muted">Version 1.1.3</div>
				</div>
			</div>
		</div>
		<!-- ############ Content END-->

		<!-- ############ LAYOUT END-->
	</div>

	<!-- build:js scripts/app.min.js -->
	<!-- Bootstrap -->
	<script src="../libs/popper.js/dist/umd/popper.min.js"></script>
	<script src="../libs/bootstrap/dist/js/bootstrap.min.js"></script>
	<!-- core -->
	<script src="../libs/pace-progress/pace.min.js"></script>
	<script src="../libs/pjax/pjax.js"></script>

	<script src="scripts/lazyload.config.js"></script>
	<script src="scripts/lazyload.js"></script>
	<script src="scripts/plugin.js"></script>
	<script src="scripts/nav.js"></script>
	<script src="scripts/scrollto.js"></script>
	<script src="scripts/toggleclass.js"></script>
	<script src="scripts/theme.js"></script>
	<!-- <script src="scripts/ajax.js"></script> -->
	<script src="scripts/app.js"></script>
	<script src="assets/js/funciones.js"></script>
	<!-- endbuild -->
</body>

</html>