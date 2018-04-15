/* 
 * Permite la actualización de la información de órdenes de producción
 * Demostración de las posibilidades más usuales de un elemento jqGrid
 */

$(function () {

    $(window).resize(); // forzar un resize para detectar el ancho del contenedor (ver index.js)
    var anchoGrid = anchoContenedor; // se asigna a una variable local el ancho del contenedor
    var altoGrid = $(window).height() - 350;

    if (altoGrid < 200) {
        altoGrid = 200;
    }

    var clase = 'Usuario';  // la clase que implementa el CRUD para este grid
    var idPager = 'usuario-pager';  // la barra de navegación del grid ubicada en la parte inferior

    // las columnas de un grid se definen como un array de objetos con múltiples atributos
    var columnas = [
        {'label': 'Identificador', name: 'id_usuario', index: 'id_usuario',align: 'center', width: 100, sortable: true, editable: true, editrules: {required: true},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Dirección', name: 'direccion', index: 'direccion', width: 100,align: 'center', sortable: true, editable: true, editrules: {required: true},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Teléfono', name: 'telefono', index: 'telefonos', width: 100,align: 'center', sortable: true, editable: true, editrules: {required: true},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Correo', name: 'correo', index: 'correos', width: 160,align: 'center', sortable: true, editable: true, editrules: {required: true},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Rol', name: 'rol', index: 'rol', width: 100, sortable: true,align: 'center', editable: true ,editrules: {required: true},
            editoptions: {dataInit: asignarAncho}
        },
    ];

    // inicializa el grid
    var grid = jQuery('#usuario-grid').jqGrid({
        url: 'controlador/fachada.php',
        datatype: "json",
        mtype: 'POST',
        postData: {
            clase: clase,
            oper: 'select'
        },
        rowNum: 10,
        rowList: [10, 20, 30],
        colModel: columnas,
        autowidth: false,
        shrinkToFit: false,
        sortname: 'id_usuario', 
        sortorder: "asc",
        height: altoGrid,
        width: anchoGrid,
        pager: "#" + idPager,
        viewrecords: true,
        caption: "Usuarios",
        multiselect: false,
        multiboxonly: true,
        hiddengrid: false,
        gridComplete: function () {
            // hacer algo...
        },
        loadError: function (jqXHR, textStatus, errorThrown) {
            alert('Error. No se tiene acceso a los datos de órdenes de producción.')
            console.log('textStatus: ' + textStatus);
            console.log(errorThrown);
            console.log(jqXHR.responseText);
        },
        editurl: "controlador/fachada.php?clase=" + clase
    });

    // inicializa los elementos de la barra de navegación del grid
    grid.jqGrid('navGrid', "#" + idPager, {
        refresh: true,
        edit: true,
        add: true,
        del: true,
        view: false,
        search: true,
        closeOnEscape: false
    }, {// edit
        width: 480,
        modal: true,
        afterSubmit: respuestaServidor
    }, {// add
        width: 480,
        modal: true,
        afterSubmit: respuestaServidor
    }, {// del
        width: 335,
        modal: true, // jqModal: true,
        afterSubmit: respuestaServidor
    }, {// búsqueda
        multipleSearch: true,
        multipleGroup: true}, {}
    );

    /**
     * Asigna ancho a un elemento del grid
     * @param {type} elemento El nombre del elemento 
     * @returns {undefined}
     */
    function asignarAncho(elemento) {
        $(elemento).width(260);
    }

    /**
     * Validación personalizada de los campos de un jqGrid
     * @param {type} valor el dato contenido en un campo
     * @param {type} columna nombre con que está etiquetada la columna
     * @returns {Array} un array indicando si la validación fue exitosa o no
     */
    function validarUsuario(valor, columna) 
    {
        if (columna === 'Identificador') {
            if (valor.length < 1 && valor.lenght >15) {
                return [false, "Longitud errónea - Entre 1 y 15 caracteres"];
            }
        }
    }

});


