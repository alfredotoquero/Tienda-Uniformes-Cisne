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