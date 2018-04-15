/**
 * "El sabio puede cambiar de opinión, el necio nunca..."
 * @returns {undefined}
 */
$(function () {

    'use strict';


    var args = new Object();
    var altura = 400;
    $("#operproduccion-fechaI").datepicker(initDatePicker);
    $("#operproduccion-fechaF").datepicker(initDatePicker);
    $("#ordenproduccion-fechaS").datepicker(initDatePicker);
    $("#reportdlg-actividad_fechaI").datepicker(initDatePicker);
    $("#reportdlg-actividad_fechaF").datepicker(initDatePicker);
    $("#reportdlg-produc_fecha").datepicker(initDatePicker);
    $("#fechaI-info-diario-operario").datepicker(initDatePicker);
    $("#fechaF-info-diario-operario").datepicker(initDatePicker);
    $("#fechaI-info-oper-utilizado").datepicker(initDatePicker);
    $("#fechaI-info-histo-pro").datepicker(initDatePicker);
    $("#fechaF-info-histo-pro").datepicker(initDatePicker);


    $('#reportes-slider').css("width", "99.9%").sliderNav({
        // Los items deben ser iguales a los id de las sublistas, si no el plugin lanza el error: TypeError: $(...).offset(...) is undefined
        // items: ['Formatos', 'Reportes', 'Suplementos'],  // así se oculta el scroll vertical
        items: [], // así se muestra el scroll vertical
        debug: false,
        height: altura,
        arrows: true
    });
    $('#slide-abajo').css('top', '3px');

    $("#reportes-generar").button().on("click", function () {
        var tiempo; // se usará enseguida para "engañar" al observador de inactividad del usuario..
        // guardar en un array todos los ID de los checkbox seleccionados en el div reportes-opciones
        var seleccionados = $('#reportes-opciones input:checkbox:checked').map(function () {
            return $(this).attr('id');
        }).get();

        console.log(seleccionados);

        if (seleccionados.length === 0) {
            alert('Debe seleccionar los reportes a imprimir');
            return;
        }

        // solicitar la descargar de un reporte con las opciones seleccionadas
        $.fileDownload('controlador/fachada.php', {
            httpMethod: "POST",
            data: {
                clase: 'Reporte',
                oper: 'generarReporte',
                'opciones': seleccionados,
                'args': args
            },
            prepareCallback: function () {
                // El siguiente intervalo "engaña" al contador de tiempo de cierre de sesión, forzando un "supuesto" mousemove
                // cada 3 segundos. Esto es necesario porque la generación de archivos puede tomar un tiempo considerable
                tiempo = setInterval(function () {
                    $("#index-pie_pagina").mousemove();
                }, 30000);
                console.log('Preparando la descarga...');
                $.blockUI({message: getMensaje('Descargando script')});
            },
            successCallback: function (url) {
                clearInterval(tiempo);  // <-- fin del engaño  -------------------
                console.log('¡Todo bien!');
                $.unblockUI();
            },
            failCallback: function (respuesta, url) {
                clearInterval(tiempo);  // <-- fin del engaño  -------------------
                console.log('¡Houston tenemos problemas!');
                $.unblockUI();
                console.log('OJO: ' + respuesta);
                if (respuesta) {
                    respuesta = jQuery.parseJSON(respuesta);
                    alert('El intento de descarga reporta el siguiente error:<br>' + respuesta.mensaje);
                } else {
                    alert('Sucedió un error inesperado intentando la descarga');
                }
            }
        });

    })

    $('#reportes-opciones input:checkbox').on('change', function () {
        var funcion = $(this).attr('data-args');  // recupera el nombre de la función a ejecutar

        if (funcion) { //&& typeof (funcion) === "function") {     // ¿existe la función?
            window[funcion](this, args);   // se ejecuta la función Javascript correspondiente
        } else {
            console.log("Error. No ha definido la función <" + funcion +
                    "> que se requiere para solicitar datos de parametrización.")
        }
    });


});


function infoMensualTurnoMaquinas(casilla, args) {
    var listaOperarios = getElementos({'clase': 'Operario', 'oper': 'getSelect', 'json': true});
    var listaMaquinas = getElementos({'clase': 'Maquina', 'oper': 'getSelect', 'json': true});
    $('#reportdlg-mes_turno-maquina').html(listaMaquinas);
    $('#reportdlg-mes_turno-operario').html(listaOperarios);
    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 80;
        var anchoContenedor = 500;
        // console.log($(this).attr('id') + ' < chequeado');

        $("#reportdlg-info_mes_turno-maquina").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });

        var frm = $('#reportdlg-info_mes_turno-maquina').dialog({
            autoOpen: true,
            width: anchoContenedor + 20,
            height: 290,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'maquina': $("#reportdlg-mes_turno-maquina").val(),
                            'operario': $("#reportdlg-mes_turno-operario").val(),
                            'fecha_inicio': $("#operproduccion-fechaI").val(),
                            'fecha_fin': $("#operproduccion-fechaF").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });
    } else {
        $('#reportdlg-info_mes_turno-maquina').dialog("close");
    }
    console.log($("#operproduccion-fechaI").val);
}

function infoMensualOperarios(casilla, args) {
    var listaOperarios = getElementos({'clase': 'Operario', 'oper': 'getSelect', 'json': true});
    $('#reportdlg-operario').html(listaOperarios);
    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 60;
        var anchoContenedor = 500;
        // console.log($(this).attr('id') + ' < chequeado');

        $("#reportdlg-info_operario").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });

        var frm = $('#reportdlg-info_operario').dialog({
            autoOpen: true,
            width: anchoContenedor + 10,
            height: 150,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'operario': $("#reportdlg-operario").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });
    } else {
        $('#reportdlg-info_operario').dialog("close");
    }
}

function infoMensualSupervisores(casilla, args) {
    var listaSupervisores = getElementos({'clase': 'Supervisor', 'oper': 'getSelect', 'json': true});
    $('#reportdlg-supervisor').html(listaSupervisores);
    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 60;
        var anchoContenedor = 500;
        // console.log($(this).attr('id') + ' < chequeado');

        $("#reportdlg-info_supervisor").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });

        var frm = $('#reportdlg-info_supervisor').dialog({
            autoOpen: true,
            width: anchoContenedor + 10,
            height: 150,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'supervisor': $("#reportdlg-supervisor").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });
    } else {
        $('#reportdlg-info_supervisor').dialog("close");
    }
}

function infoMensualJefes(casilla, args) {
    var listaJefes = getElementos({'clase': 'JefeMantenimiento', 'oper': 'getSelect', 'json': true});
    $('#reportdlg-jefe').html(listaJefes);
    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 60;
        var anchoContenedor = 500;
        // console.log($(this).attr('id') + ' < chequeado');

        $("#reportdlg-info_jefe").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });

        var frm = $('#reportdlg-info_jefe').dialog({
            autoOpen: true,
            width: anchoContenedor + 10,
            height: 150,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'jefe': $("#reportdlg-jefe").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });
    } else {
        $('#reportdlg-info_jefe').dialog("close");
    }
}

function infoDiarioOperario(casilla, args) {

    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 75;
        var anchoContenedor = 300;

        $("#reportdlg-info_diario-operario").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });

        var frm = $('#reportdlg-info_diario-operario').dialog({
            autoOpen: true,
            width: anchoContenedor + 20,
            height: 180,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'fechaI': $("#fechaI-info-diario-operario").val(),
                            'fechaF': $("#fechaF-info-diario-operario").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });


    }


}

function operProduccionUtilizadas(casilla, args) {

    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 75;
        var anchoContenedor = 300;
        
        $("#reportdlg-info_historico-pro").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });
        
        var frm = $('#reportdlg-info_historico-pro').dialog({
            autoOpen: true,
            width: anchoContenedor + 20,
            height: 180,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'fechaI': $("#fechaI-info-histo-pro").val(),
                            'fechaF': $("#fechaF-info-histo-pro").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });
        // console.log($(this).attr('id') + ' < chequeado');
 
    } else {
        $('#reportdlg-info_oper_utilizado').dialog("close");
    }
}

function infoOrden(casilla, args) {
    
    if ($(casilla).prop('checked')) {
        var anchoEtiquetas = 60;
        var anchoContenedor = 500;
        // console.log($(this).attr('id') + ' < chequeado');

        $("#reportdlg-info_orden").estiloFormulario({
            'anchoFormulario': anchoContenedor + 'px',
            'anchoEtiquetas': anchoEtiquetas + 'px',
            'anchoEntradas': (anchoContenedor - anchoEtiquetas - 40) + 'px',
            'alturaTextArea': '50px'
        });

        var frm = $('#reportdlg-info_orden').dialog({
            autoOpen: true,
            width: anchoContenedor + 10,
            height: 150,
            modal: false,
            "buttons": [
                {
                    id: "reportdlg-mes_aceptar", text: "Aceptar", click: function () {
                        // agregar al array args un objeto con los parametros adicionales
                        args[$(casilla).attr('id')] = {
                            'orden': $("#numeroOrden").val()
                        };
                        $(frm).dialog("close");
                    }
                },
                {id: "reportdlg-mes_cancelar", text: "Cancelar", icons: {primary: "ui-icon-close"}, click: function () {
                        $(frm).dialog("close");
                    }
                }
            ]
        });
    } else {
        $('#reportdlg-info_orden').dialog("close");
    }
}
//# sourceURL=reportes.js
