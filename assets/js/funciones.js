var enviandoformulario = false;

const App = {

    modulos: {

        pagos: function () {
            $(".formBusqueda").trigger("submit");
        }
        
    }

};

$(function () {
    $(".formBusqueda").on("submit", function (e) {
        e.preventDefault();

        cargarContenedor(
            $("#archivo",this).val(),
            Object.fromEntries(new FormData(this)),
            $("#contenedor",this).val()
        );
    });

    const modulo = $(".modulo").data("modulo");
    if (App.modulos[modulo]) {
        App.modulos[modulo]();
    }
});

function conexionServidor(form){
    const formData = new FormData(form);

    fetch("/assets/php/controladores/" + $("#controlador",form).val() + ".php", {
        method: "POST",
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        
    })
    .catch(err => {
        console.error("Error:", err);
    });
}

function cargarContenedor(archivo,datos,contenedor){
    if (!archivo || !contenedor) {
        console.warn("Archivo o contenedor no definido");
        return;
    }
    
    $("#" + contenedor).load(archivo,datos);
}

function filtrarPedidosPago(idvendedor){
    cargarContenedor(
        "/modulos/pagos/listaPedidos.php",
        {
            cliente: $("#slcCliente","#formAgregarPago").val(),
            idvendedor: idvendedor
        },
        "listaPedidos"
    );
}

function descargarXML_Pago(idpago){
    $.ajax({
        url: "/assets/php/controladores/pagos.php",
        method: "POST",
        data: {
            proceso: "verXML",
            idpago: idpago
        },
        dataType: "json",
        success: function(res) {
            if (res.success) {
                const blob = new Blob([atob(res.xml)], { type: "application/xml" });
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = res.uuid + ".xml";
                a.click();
                URL.revokeObjectURL(url);
            } else {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: res.message
                });
            }
        },
        error: function() {
            Swal.fire({
                type: "error",
                title: "Error",
                text: "No se pudo conectar con el servidor"
            });
        }
    });
}

function descargarPago(idpago){
    $.ajax({
        url: "/assets/php/controladores/pagos.php",
        method: "POST",
        data: {
            proceso: "descargarPago",
            idpago: idpago
        },
        dataType: "json",
        success: function(res) {
            if (res.success) {
                const zip = new JSZip();
                zip.file(res.uuid + ".xml", res.xml, { base64: true });
                zip.file(res.uuid + ".pdf", res.pdf, { base64: true });
                const blob = zip.generate({ type: "blob" });
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = res.uuid + ".zip";
                a.click();
                URL.revokeObjectURL(url);
            } else {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: res.message
                });
            }
        },
        error: function() {
            Swal.fire({
                type: "error",
                title: "Error",
                text: "No se pudo conectar con el servidor"
            });
        }
    });
}

function timbrarPago(idpago){
    Swal.fire({
        title: "¿Timbrar complemento de pago?",
        text: "Se generará y timbrará el complemento de pago ante el SAT.",
        type: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, timbrar",
        cancelButtonText: "Cancelar"
    }).then(function(result) {
        if (!result.value) return;

        $.ajax({
            url: "/assets/php/controladores/pagos.php",
            method: "POST",
            data: {
                proceso: "generarComplementoPago",
                idpago: idpago
            },
            dataType: "json",
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        type: "success",
                        title: "Timbrado exitoso",
                        text: res.message
                    }).then(function() {
                        App.modulos.pagos();
                    });
                } else {
                    Swal.fire({
                        type: "error",
                        title: "Error",
                        text: res.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: "No se pudo conectar con el servidor"
                });
            }
        });
    });
}

function verPDF_Pago(idpago){
    $.ajax({
        url: "/assets/php/controladores/pagos.php",
        method: "POST",
        data: {
            proceso: "verPDF",
            idpago: idpago
        },
        dataType: "json",
        success: function(res) {
            if (res.success) {
                const byteChars = atob(res.pdf);
                const byteNums = new Uint8Array(byteChars.length);
                for (let i = 0; i < byteChars.length; i++) {
                    byteNums[i] = byteChars.charCodeAt(i);
                }
                const blob = new Blob([byteNums], { type: "application/pdf" });
                const url = URL.createObjectURL(blob);
                fancy(url, "80%", "90%");
            } else {
                Swal.fire({
                    type: "error",
                    title: "Error",
                    text: res.message
                });
            }
        },
        error: function() {
            Swal.fire({
                type: "error",
                title: "Error",
                text: "No se pudo conectar con el servidor"
            });
        }
    });
}

function validarFormulario(formulario) {
	var error = false;
	$(".requerido", "#" + formulario).each(function () {
		var elemento = $(this).prop("tagName").toLowerCase();
		if (elemento == "input") {
			var tipo = $(this).attr("type");
			if (
				tipo == "text" &&
				($(this).val() == "" || $(this).val().trim().length == 0)
			) {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (
				tipo == "password" &&
				($(this).val() == "" || $(this).val().trim().length == 0)
			) {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "number" && jQuery.type($(this).val()) != "number") {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "email" && !validarCorreo($(this).val())) {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "file" && $(this).val() == "") {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			} else if (tipo == "radio" && $(this).val() == "") {
				swalFocus(
					"Error",
					$(this).data("mensajeerror"),
					"error",
					$(this).attr("name")
				);
				error = true;
				return false;
			}
		} else if (elemento == "select" && $(this).val() == 0) {
			swalFocus(
				"Error",
				$(this).data("mensajeerror"),
				"error",
				$(this).attr("name")
			);
			error = true;
			return false;
		} else if (elemento == "textarea" && $(this).val() == "") {
			swalFocus(
				"Error",
				$(this).data("mensajeerror"),
				"error",
				$(this).attr("name")
			);
			error = true;
			return false;
		}
	});
	if (!error) {
		enviarFormulario(formulario);
	}
}

function validarCorreo(valor) {
	if (
		/^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i.test(
			valor
		)
	) {
		return true;
	} else {
		return false;
	}
}

function enviarFormulario(formulario) {
	if (!enviandoformulario) {
		$.ajax({
			type: "POST",
			url:
				"/assets/php/controladores/" +
				$("#controlador", "#" + formulario).val() +
				".php",
			data: new FormData($("#" + formulario)[0]),
			processData: false,
			contentType: false,
			dataType: "json",
			beforeSend: function () {
				$("#divLoader").css("display", "inline");
				enviandoformulario = true;
				$("#btnValidar").prop("disabled", true);
			},
			success: function (data) {
				$("#divLoader").css("display", "none");
				enviandoformulario = false;
				if (data != "null") {
					if (data.respuesta == "OK") {
						tipo = data.tipo;
						if (tipo == "reload") {
							location.href = location.href;
						} else if (tipo == "href") {
							location.href = $("#href", "#" + formulario).val();
						} else if (tipo == "mensaje") {
							Swal.fire(data.titulo, data.mensaje, "success");
						} else if (tipo == "mensajehref") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								location.href = $("#href", "#" + formulario).val();
							});
						} else if (tipo == "mensajecerrarfancy") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								$.fancybox.close();
							});
						} else if (tipo == "mensajereload") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								location.href = location.href;
							});
						} else if (tipo == "mensajecargar") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								$.fancybox.close();
								cargarDatosContenedor(data.formulario);
								// ESTO SE PUEDE PROGRAMAR MEJOR: AGREGUÉ UN CODIGO PARA RECARGAR UN ELEMENTO ESPECIFICO (UN SELECT) QUE NO ESTÁ EN EL CONTENEDOR Y NO SE PUEDE AGREGAR FACILMENTE
								if (data.pantalla == "solicitudes") {
									recargarSelect(data.idproveedor);
								}
							});
						} else if (tipo == "mensajecargar2") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								cargarDatosContenedor(data.formulario);
								if (data.formulario == "formRecepcion") {
									cargarDatosContenedor("formBusqueda");
								}
								if (data.reiniciarform) {
									$("#" + formulario).trigger("reset");
									$("#" + formulario).toggle();
									$("#" + data.btn).show();
								}
								if (data.razon == 1) {
									$("#idrazonsocial").val("");
									$("#accion").val("agregarrazon");
								}
							});
						} else if (tipo == "cerrarfancycargar") {
							$.fancybox.close();
							cargarDatosContenedor(data.formulario);
							// SI ESTOY EN LA PANTALLA DE INDICAR ESPECIFICACIONES Y AGREGUÉ/EDITÉ UNA PARTIDA, SE REINICIAN UNA SERIE DE INPUTS
							if (data.pantalla == "cotizacion") {
								$("#slcOrigen").val(0);
								$("#slcCategoria").val(0);
								$("#slcProducto").val(0);
								$("#txtProducto").val("");
								$("#txtCantidad").val("");
								$("#txtPrecio").val("");

								mostrar2(0);
								$("#slcProducto").select2();

								// mostrar opciones de totalizacion
								$("#divTotalizacion").show();

								// cuando vienes de editar
								$("#divAgregar").show();
								$("#divEditar").hide();
								$("#slcOrigen").prop("disabled", false);
								$("#slcProducto").prop("disabled", false);
								$("#idpartida").val("");
								$("#idproducto").val("");
							}
						} else if (tipo == "mensajeventana") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								window.open(data.archivo, "_blank");
								location.href = location.href;
							});
						} else if (tipo == "mensajecargarabrirfancy") {
							Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
								cargarDatosContenedor(data.formulario);
								fancy(data.rutafancy);
							});
						}else if(tipo == "abrir_nuevo_fancy"){
							$.fancybox.close();
							fancy(data.url);
						}
					} else if (data.respuesta == "ERROR") {
						Swal.fire("Error", data.mensaje, "error");
						$("#btnValidar").prop("disabled", false);
					} else if (data.respuesta == "EXCEPTION") {
						Swal.fire("Excepción", data.mensaje, "error");
						$("#btnValidar").prop("disabled", false);
					} else {
						Swal.fire(
							"Error",
							"Ocurrió un error al procesar la solicitud.",
							"error"
						);
						$("#btnValidar").prop("disabled", false);
					}
				} else {
					Swal.fire(
						"Error",
						"Ocurrió un error al procesar la solicitud (Controlador: " +
						$("#controlador", "#" + formulario).val() +
						").",
						"error"
					);
					$("#btnValidar").prop("disabled", false);
				}
			},
		});
	}
}

function solicitudServidor(
	controlador,
	accion,
	datos,
	validacion,
	tipo = "reload",
	href = "",
	delay = ""
) {
	if (validacion != "") {
		Swal.fire({
			title: "Atención",
			text: validacion,
			icon: "warning",
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: "Aceptar",
			cancelButtonText: "Cancelar",
		}).then((result) => {
			if (result.value) {
				conexionServidor(controlador, accion, datos, tipo, href, delay);
			}
		});
	} else {
		conexionServidor(controlador, accion, datos, tipo, href, delay);
	}
}

function conexionServidor(
	controlador,
	accion,
	datos,
	tipo,
	href,
	delay,
	omitirloader = 0
) {
	$.ajax({
		type: "POST",
		url: "/assets/php/controladores/" + controlador + ".php",
		data: "accion=" + accion + "&" + datos,
		dataType: "json",
		beforeSend: function () {
			if (!omitirloader) {
				$("#divLoader").css("display", "inline");
			}
			enviandoformulario = true;
		},
		success: function (data) {
			if (!omitirloader) {
				$("#divLoader").css("display", "none");
			}
			enviandoformulario = false;
			if (data.respuesta == "OK") {
				tipo = data.tipo != "" && data.tipo != undefined ? data.tipo : tipo;
				if (tipo == "reload") {
					location.href = location.href;
				} else if (tipo == "href") {
					location.href = href;
				} else if (tipo == "mensaje") {
					Swal.fire(data.titulo, data.mensaje, "success");
				} else if (tipo == "mensajehref") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						location.href = href;
					});
				} else if (tipo == "mensajereload") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						location.href = location.href;
					});
				} else if (tipo == "mensajecargar") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						$.fancybox.close();
						cargarDatosContenedor(data.formulario);
						// ESTO SE PUEDE PROGRAMAR MEJOR: AGREGUÉ UN CODIGO PARA RECARGAR UN ELEMENTO ESPECIFICO (UN SELECT) QUE NO ESTÁ EN EL CONTENEDOR Y NO SE PUEDE AGREGAR FACILMENTE
						if (data.pantalla == "solicitudes") {
							recargarSelect(data.idproveedor);
						}
					});
				} else if (tipo == "mensajecargar2") {
					Swal.fire(data.titulo, data.mensaje, "success").then((result) => {
						cargarDatosContenedor(data.formulario);
					});
					// AGREGUÉ UN NUEVO TIPO, QUE NO NECESITA MOSTRAR NINGUN MENSAJE TIPO SWAL, PERO SI NECESITA RECARGAR UN CONTENEDOR (SIN CARGAR TODA LA PAGINA)
				} else if (tipo == "cargar") {
					if (delay) {
						setTimeout(function () {
							cargarDatosContenedor(data.formulario);
						}, delay);
					} else {
						cargarDatosContenedor(data.formulario);
					}
					if (data.pantalla == "notificaciones") {
						buscarNotificaciones("campana");
					}
				} else if (tipo == "fancyArchivo") {
					$.fancybox.open({
						src: data.pdf_fancy,
						type: "iframe",
						opts: {
							closeExisting: true,
						},
					});

				}
			} else if (data.respuesta == "ERROR") {
				Swal.fire("Error", data.mensaje, "error");
			} else if (data.respuesta == "EXCEPTION") {
				Swal.fire("Excepción", data.mensaje, "error");
			} else {
				Swal.fire(
					"Error",
					"Ocurrió un error al procesar la solicitud.",
					"error"
				);
			}
		},
	});
}

function cargarDatosContenedor(formulario) {
	if ($("#" + formulario).length > 0) {
		$.ajax({
			type: "POST",
			url: $("#archivo", "#" + formulario).val(),
			data: new FormData($("#" + formulario)[0]),
			processData: false,
			contentType: false,
			beforeSend: function () {
				$("#divLoader").css("display", "inline");
				enviandoformulario = true;
			},
			success: function (data) {
				$("#divLoader").css("display", "none");
				enviandoformulario = false;
				$("#" + $("#contenedor", "#" + formulario).val()).html(data);
			},
		});
	}
	console.log("cargar datos contenedor");
}

// forma generalizada de hacer focus en un input usando sweetalert
function swalFocus(titulo, mensaje, tipo, input) {
	Swal.fire({
		title: titulo,
		text: mensaje,
		icon: tipo,
		didClose: () => {
			$("#" + input).focus();
		},
	});
}