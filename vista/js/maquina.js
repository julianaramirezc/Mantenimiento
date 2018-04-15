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

    var clase = 'Maquina';  // la clase que implementa el CRUD para este grid
    var idPager = 'maquina-pager';  // la barra de navegación del grid ubicada en la parte inferior

    // las columnas de un grid se definen como un array de objetos con múltiples atributos
    var columnas = [
        
        {'label': 'Id', name: 'id_maquina', index: 'id_maquina', align:"center", width: 70, sortable: true, editable: true, 
            editrules: {required: true,custom:true,custom_func:validarMaquina},
            editoptions: {dataInit: asignarAncho}
        },
        
        {'label': 'Descripción', name: 'descripcion', index: 'descripcion', align:"center", width: 130, sortable: true, editable: true, 
            editrules: {required: true,custom:true,custom_func:validarMaquina},
            editoptions: {dataInit: asignarAncho}
        },
        {'label': 'Tipo', name: 'tipo_maquina', index: 'tipo_maquina', align:"center", width: 100, sortable: true, editable: true, edittype: 'select',
            editrules: {required: true,custom:true,custom_func:validarMaquina},
            editoptions: {
                dataInit: asignarAncho,
               value: tipoMaquina
            }
        },
        {'label': 'Color', name: 'color', index: 'color', align:"center", width: 100, sortable: true, editable: true, 
            editrules: {required: true},
            editoptions: {dataInit: asignarAncho}
        },
    ];

    // inicializa el grid
    var grid = jQuery('#maquina-grid').jqGrid({
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
        sortname: 'id_maquina',
        sortorder: "asc",
        height: altoGrid,
        width: anchoGrid,
        pager: "#" + idPager,
        viewrecords: true,
        caption: "Máquinas",
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
    
    function validarMaquina(valor, columna) {
        if (columna === 'Id') {
            if (valor.length <=2) {
                return [false, columna + ": Longitud errónea, mínimo 3"];
            }
            else if (valor.length >= 10) {
                return [false, columna + ": Longitud errónea, máximo 10"];
            }
        } 
        if (columna === 'Descripción') {
            if (valor.length <=2) {
                return [false, columna + ": Longitud errónea, mínimo 3"];
            }
            else if (valor.length >= 30) {
                return [false, columna + ": Longitud errónea, máximo 30"];
            }
        } 
        
        if (columna === 'Tipo') {
            if (valor == 0) {
                return [false, columna + ": Debe Seleccionar el tipo"];
            }
        }
        
        else if (columna === 'OtroCampoXxxxxxxxx') {
            // y así sucesivamente
        }
        return [true, ""];
    }

});


