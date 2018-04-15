/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(function () {

// una de las formas de manipular el css mediante jQuery
    $("#index-panel-izquierdo button").css({'width': '13em'});
    // manejo de eventos con jQuery
    $("#index-maquinas").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/maquina.html");
    });
    $("#index-turno").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/turno.html");
    });
    $("#index-usuario").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/usuario.html");
    });
    $("#index-operarios").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/operario.html");
    });
    $("#index-jefe_mantenimiento").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/jefe_mantenimiento.html");
    });
    $("#index-supervisor").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/supervisor.html");
    });
    $("#index-tipo_operacion").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/tipo_operacion.html");
    });
    $("#index-operacion_produccion").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/operacion_produccion.html");
    });
    $("#index-reportes").button().on("click", function () {
        cargarPagina("#index-contenido", "vista/html/reportes.html");
    });
    /**
     * Demostración de lectura de archivos de Excel y descarga controlada
     */
    
    // inicializa el temporizador de cierre de sesión
    initIdleTimeout();
    // subir archivos  (definir uploader)
    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'index-restaurar', // id del botón que permite adjuntar

        url: 'controlador/fachada.php',
        multipart_params: {
            clase: "UtilConexion",
            oper: "restore"
        },
        filters: {
            max_file_size: '10mb',
            mime_types: [
                {title: "Archivos de imágenes", extensions: "jpg,gif,png"},
                {title: "Portable document file", extensions: "pdf"},
                {title: "Zip files", extensions: "zip"},
                {title: "Hojas de cálculo", extensions: "xlsx,xls"},
                {title: "Backup", extensions: "backup"}

            ]
        },
        // Flash settings
        flash_swf_url: '/plupload/js/Moxie.swf',
        // Silverlight settings
        silverlight_xap_url: '/plupload/js/Moxie.xap',
        init: {
            PostInit: function () {
                // algo que se deba hacer luego de inicializar el plugin
            },
            StateChanged: function (up) { // Estado actual del proceso de carga
                if (up.state === plupload.STARTED) {
                    $.blockUI({message: getMensaje('Subiendo adjuntos')});
                } else if (up.state === plupload.STOPPED) {
                    $.unblockUI();
                    if (!okUploader) {
                        if (confirm('Problemas en la carga. ¿Enviar de todas maneras?') === false) {
                            return;
                        }
                    }

                }
            },
            FilesAdded: function (up, files) {
                okUploader = true;
                // luego de aceptar la selección de archivos se muestran 
                // o se sube automáticamente: 
                uploader.start();
                plupload.each(files, function (file) {
                    console.log(file.name + plupload.formatSize(file.size) + file.id);
                });
            },
            UploadProgress: function (up, file) {
                // se va informando del porcentaje de subida de archivos
                console.log(file.percent + "%");
            },
            FileUploaded: function (up, file, info) {
                // Verificar estado cuando la carga de un archivo ha finalizado (conservar ‘’)
                var respuesta = jQuery.parseJSON(info.response) + '';
                if (respuesta.result) {
                    okUploader = false;
                    console.log('result=' + respuesta.result);
                } else if (respuesta.error) {
                    okUploader = false;
                    console.log('result=' + respuesta.error.message);
                } else {
                    console.log('Archivo <b>' + file.name + '</b> procesado');
                }
            },
            UploadComplete: function (up, file, info) {  // termina la carga de todos los archivos
                /////////////////////     adjuntos.length = 0;
                up.splice();
            },
            Error: function (up, err) {
                okUploader = false;
                // No se tiene acceso al servidor. El tipo de archivo no es soportado *************
                console.log("Error #" + err.code + ": " + err.message);
            }
        }
    });
    uploader.init();
});

