angular.module('app')
    .controller('eycEstructuraCtrl',
    ['$http', '$mdEditDialog', '$q', '$timeout', '$scope', '$mdDialog', 'eycEstructuraSvc', 'eycEstructuraRelacionesSvc', '$sce',
        '$mdMedia', function ($http, $mdEditDialog, $q, $timeout, $scope, $mdDialog, eycEstructuraSvc, eycEstructuraRelacionesSvc, $mdMedia, $sce) {
        'use strict';

        $scope.selected = [];

        $scope.query = {
            filter: '',
            order: 'id',
            limit: 5,
            page: 1
        };

        //listar instancias de estructuras
        function getInstEstruc(id, query) {
            $scope.listaInstancia.valores = [];
            $scope.listaInstancia.count = 0;
            $scope.promise = eycEstructuraRelacionesSvc.getOpHijos(id).get(query || $scope.query, success).$promise;
        };

        //listar instancias de estructuras raiz
        function getInstEstrucRaiz(query) {
            $scope.listaInstancia.valores = [];
            $scope.listaInstancia.count = 0;
            $scope.promise = eycEstructuraSvc.instanciasRaiz.get(query || $scope.query, success).$promise;
        };
        function success(instancias) {
            $scope.listaInstancia = instancias;
            $scope.selected = [];
        };

        //paginar instancias de estructuras
        $scope.onPaginate = function (page, limit) {
            if ($scope.idPadre != null) {
                getInstEstruc($scope.idPadre, angular.extend({}, $scope.query, {page: page, limit: limit}));
            } else {
                getInstEstrucRaiz(angular.extend({}, $scope.query, {page: page, limit: limit}));
            }

        };

        //ordenar instancias de estructuras
        $scope.onReorder = function (order) {
            if ($scope.idPadre != null) {
                getInstEstruc($scope.idPadre, angular.extend({}, $scope.query, {order: order}));
            } else {
                getInstEstrucRaiz(angular.extend({}, $scope.query, {order: order}));
            }
        };

        $scope.filter = {
            options: {
                debounce: 500
            }
        };
        var bookmark;
        $scope.removeFilter = function () {
            $scope.filter.show = false;
            $scope.query.filter = '';

            if ($scope.filter.form.$dirty) {
                $scope.filter.form.$setPristine();
            }
        };

        //filtrar instancias de estructuras por nombre
        $scope.$watch('query.filter', function (newValue, oldValue) {
            if (!oldValue) {
                bookmark = $scope.query.page;
            }

            if (newValue !== oldValue) {
                $scope.query.page = 1;
            }

            if (!newValue) {
                $scope.query.page = bookmark;
            }

            if ($scope.idPadre != null) {
                getInstEstruc($scope.idPadre);
            } else {
                getInstEstrucRaiz();
            }

        });

        $scope.oculto = {
            visibility: 'hidden'
        };

        $scope.nombAux = '';

        $scope.listaInstancia = [];
        $scope.listaInstancia.valores = [];
        $scope.listaCampos = [];
        //$scope.expandedNodes = [];
        //$scope.expandedNodes2 = [];

        function pasarHijosaEstr(id, hijos, estructura) {
            for (var i = 0; i < estructura.length; i++) {
                if (estructura[i].id == id) {
                    estructura[i].children = hijos;
                } else {
                    if (estructura[i].children != undefined) {
                        if (estructura[i].children.length > 0) {
                            pasarHijosaEstr(id, hijos, estructura[i].children);
                        }
                    }
                }
            }
        };
        $scope.idAux = null;
        $scope.idPadre = null;
        $scope.showSelected = function (node, selected) {
            $scope.removeFilter();
            if (selected) {
                $scope.oculto = {
                    visibility: 'visible'
                };
                if (node.estructura_id != undefined) {
                    $scope.addDisab = true;
                    getHijosEstruc(node.estructura_id);
                    $scope.idPadre = node.id;
                    getInstEstruc($scope.idPadre);
                    console.log(node);
                } else {
                    $scope.idPadre = null;
                    $scope.listaInstancia.valores = [];
                    $scope.listaInstancia.count = 0;
                    $scope.addDisab = true;
                    getEstrucRaiz();
                    getInstEstrucRaiz();
                }
            } else {
                $scope.listaInstancia.valores = [];
                $scope.listaInstancia.count = 0;
                $scope.oculto = {
                    visibility: 'hidden'
                };
            }
        };

        $scope.adicionarInst = function (item) {
            console.log(item);
            $scope.listaCampos = [];
            $scope.idAux = item.id;
            if ($scope.idPadre == null) {
                $scope.idPadre = 0;
            }
            //listar campos de estructuras sin filtro
            $http({
                method: 'GET', url: Routing.generate('eyc_estructura_campos2', {ide: item.id}, true)
            }).then(function (response) {
                var auxiliar = '404 GET: El recurso solicitado con el identificador ' + item.id + ' no posee campos asociados.';
                if (response.data != auxiliar) {
                    $scope.listaCampos = response.data;
                    //listar campos de estructuras vinculados a nomencladores
                    $http({
                        method: 'GET',
                        url: Routing.generate('eyc_estructura_campos_vinculados', {id: item.id}, true)
                    }).then(function (response) {
                        if (response.data != undefined) {
                            if(response.data != []){
                                $scope.comboIns = response.data;
                                $scope.adicionarInstancia();
                            }
                        }
                    }, function (response) {
                        "Request failed";
                    });
                }
            }, function (response) {
                "Request failed";
            });
        };

        $scope.nodeAux = {};
        $scope.selectedAux = false;

        $scope.listadoNomencladores2 = {
            width: '0px',
            marginRight: '10px',
            overflow: 'hidden',
            float: 'left'
        };

        $scope.listadoNomencladores = {
            width: '25%',
            float: 'left',
            marginRight: '10px',
            fontSize: '14px'
        };

        $scope.barraTablaNomenc = {
            width: '70%'
        };

        $scope.tablaNomc = {
            width: '70%'
        };

        $scope.cambiarEstilo = function () {
            $scope.listadoNomencladores = {
                width: '0px',
                overflow: 'hidden',
                marginRight: '10px',
                float: 'left'
            };
            $scope.listadoNomencladores2 = {
                width: '3,5%',
                overflow: 'visible',
                marginRight: '10px',
                float: 'left'
            };

            $scope.barraTablaNomenc = {
                width: '93%'
            };
            $scope.tablaNomc = {
                width: '93%'
            };
        };

        $scope.cambiarEstilo2 = function () {
            $scope.listadoNomencladores2 = {
                width: '0px',
                overflow: 'hidden',
                marginRight: '10px',
                float: 'left'
            };
            $scope.listadoNomencladores = {
                width: '25%',
                float: 'left'
            };
            $scope.barraTablaNomenc = {
                width: '70%'
            };

            $scope.tablaNomc = {
                width: '70%'
            };
        };

        function obtenerPosEstr(id, estructura) {
            for (var i = 0; i < estructura.length; i++) {
                if (estructura[i].id == id) {
                    return i;
                }
            }
            return -1;
        };

        $scope.arbolEstructura = [];

        //function buscarHijos(padre, ide, i) {
        //    $http({
        //        method: 'GET', url: Routing.generate('eyc_estructura_hijas', {id: ide}, true)
        //    }).then(function (response) {
        //        var aux = '404 GET: El recurso solicitado con el identificador ' + ide + ' no existe.';
        //        if (response.data != aux) {
        //            padre[i].children = response.data[0].estructurasHijas;
        //            for (var j = 0; j < padre[i].children.length; i++) {
        //                buscarHijos(padre[i].children, padre[i].children[j].id, j);
        //                var index = obtenerPosEstr(padre[i].children[j].id, $scope.arbolEstructura);
        //                if (index != -1) {
        //                    $scope.arbolEstructura.splice(index, 1);
        //                    for (var k = 0; k < $scope.arbolEstructura.length; k++) {
        //                        if ($scope.arbolEstructura[i].children.length == 0) {
        //                            buscarHijos($scope.arbolEstructura, $scope.arbolEstructura[k].id, k);
        //                        }
        //                    }
        //                }
        //            }
        //        }
        //    }, function (response) {
        //        "Request failed";
        //    });
        //};

        //mostrar arbol de instancias de estructuras
        function getArbolEstrucOp() {
            $scope.arbolEstructura = {};
            $scope.arbolEstructura.data = [];
            $scope.promise = eycEstructuraSvc.arbolEstrOp.get(success2).$promise;
        };
        function success2(arbol) {
            var arbolEstruc = [{
                nombre: 'Estructura',
                children: arbol.data
            }];
            $scope.expandedNodes = arbolEstruc;
            $scope.arbolEstructura = arbolEstruc;
        };
        $scope.addDisab = true;

        //listar relaciones de estructuras
        function getHijosEstruc(ide) {
            $scope.estructuras = {};
            $scope.promise = eycEstructuraRelacionesSvc.getHijos(ide).get(success4).$promise;
        };
        function success4(hijos) {
            if (hijos.data != undefined) {
                $scope.estructuras2 = hijos.data[0].estructurasHijas;
                var campos = hijos.campos;
                var estruct = [];
                var cont = 0;
                for(var i = 0; i < $scope.estructuras2.length;i++){
                    if(campos[i] > 0){
                        estruct[cont]= $scope.estructuras2[i];
                        cont++;
                    }
                }
                $scope.estructuras = estruct;
                console.log($scope.estructuras);
                $scope.addDisab = false;
            } else {
                $scope.addDisab = true;
            }
        };
        //listar estructuras raiz
        function getEstrucRaiz() {
            $scope.estructuras = {};
            $scope.promise = eycEstructuraSvc.listaEstructuras2.get(success3).$promise;
        };
        function success3(estructuras) {
            $scope.estructuras = [];
            if (estructuras.data != undefined) {
                var campos = estructuras.campos;
                var cont = 0;
                for (var i = 0; i < estructuras.data.length; i++) {
                    if (estructuras.data[i].raiz == true && campos[cont] > 0) {
                        $scope.estructuras.push(estructuras.data[i]);
                        cont++;
                    }
                }
                $scope.addDisab = false;
            } else {
                $scope.addDisab = true;
            }
        };
        getArbolEstrucOp();

        function EliminarHijos(children, id) {
            for (var j = 0; j < children.length; j++) {
                if (children[j].id === id) {
                    children.splice(j, 1);
                } else {
                    if (children[j].children) {
                        EliminarHijos(children[j].children, id)
                    }
                }
            }
        };

        $scope.deselect = function (item) {
            if ($scope.selected.length == 1) {
                var data = {
                    criterio: item.nomEstruc
                };
                //obtener estructura dado el nombre
                $http({
                    method: 'POST', url: Routing.generate('eyc_estructura_buscar_estructura_nomb', {}, true), data: data
                }).then(function (response) {
                    if (response.data != []) {
                        $scope.idAux = response.data[0].id;
                    }
                }, function (response) {
                    "Request failed";
                });
            }
        };

        $scope.log = function (item) {
            if ($scope.selected.length == 1) {
                var data = {
                    criterio: item.nomEstruc
                };
                //obtener estructura dado el nombre
                $http({
                    method: 'POST', url: Routing.generate('eyc_estructura_buscar_estructura_nomb', {}, true), data: data
                }).then(function (response) {
                    if (response.data != []) {
                        $scope.idAux = response.data[0].id;
                    }
                }, function (response) {
                    "Request failed";
                });
            }
        };

        $scope.loadStuff = function () {
            /*
             $scope.promise = $timeout(function () {

             }, 2000);
             */
        };

        $scope.editarInst = function (selected) {

		
            //listar campos de estructuras
            $http({
                method: 'GET', url: Routing.generate('eyc_estructura_campos', {ide: $scope.idAux}, true)
            }).then(function (response) {
                var auxiliar = '404 GET: El recurso solicitado con el identificador ' + $scope.idAux + ' no posee campos asociados.';
                if (response.data != auxiliar) {
                    $scope.listaCampos = response.data;
                    var data = {
                        ide: selected[0].IdEop,
                        criterio: selected[0].nomEOP
                    };
                    //obtener instancia de estructuras
                    $http({
                        method: 'POST', url: Routing.generate('eyc_estructura_buscar_instancia', {}, true), data: data
                    }).then(function (response) {
                        if (response.data != []) {
                            var pasar = response.data;
                            //listar campos de estructuras vinculados a nomencladores
                            $http({
                                method: 'GET',
                                url: Routing.generate('eyc_estructura_campos_vinculados', {id: $scope.idAux}, true)
                            }).then(function (response) {
                                if (response.data != undefined) {
                                    if(response.data != []){
                                        $scope.comboIns = response.data;
                                        $scope.editarInstancia(pasar);
                                    }
                                }
                            }, function (response) {
                                "Request failed";
                            });
                        }
                    }, function (response) {
                        "Request failed";
                    });
                }
            }, function (response) {
                "Request failed";
            });
        };


        $scope.model = {};

        //adicionar instancia de estructuras
        $scope.adicionarInstancia = function (ev) {
            var parentEl = angular.element(document.body);
            var plantilla = '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Adicionar instancia de estructura</h3>' +
                '<span flex=""></span>' +
                '<md-button ng-click="cerrar()" >' +
                '<i class="icon i-20 mdi mdi-close"></i>' +
                '<md-tooltip md-direction="left">' +
                'Cerrar ventana' +
                '</md-tooltip>' +
                '</md-button>' +
                '</div>' +
                '</md-toolbar>' +
                '<md-content layout-padding>' +
                '<div>' +
                '<form name="instancia" method="post" action="">' +
                '<md-input-container class="md-block">' +
                '<label class="required">Nombre Instancia</label>' +
                '<input name="nombInst" ng-model="nombInst" required="required" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/" maxlength="255">' +
                '<span class="messages" ng-show="instancia.nombInst.$dirty">' +
                '<span ng-show="instancia.nombInst.$error.required">El campo es obligatorio.</span>' +
                '<span ng-show="instancia.nombInst.$error.pattern">El nombre de la instancia no tiene el formato correcto.</span>' +
                '</span>' +
                '</md-input-container>';
            //if (!$scope.raizAux) {
            //    plantilla += '<label class="required">Padre</label>' +
            //    '<md-select name="parent" required="required" ng-model="parentID" >' +
            //    '<md-option ng-repeat="parent in parents" required="required" value="{{parent.IdEop}}">' +
            //    '{{parent.NomEOP}}' +
            //    '</md-option>' +
            //    '</md-select>' +
            //    '<span class="messages" ng-show="instancia.parent.$dirty">' +
            //    '<span ng-show="instancia.parent.$error.required">El campo es obligatorio.</span>' +
            //    '</span>' +
            //    '</md-input-container>';
            //}

            function eliminarCaracterInvalidos(palabra) {
                var nueva = '';
                for (var i = 0; i < palabra.length; i++) {
                    if (palabra.charAt(i) != " ") {
                        nueva += palabra.charAt(i);
                    }
                }
                return nueva;
            };
            for (var i = 0; i < $scope.listaCampos.data.length; i++) {
                var nuevoNombre = eliminarCaracterInvalidos($scope.listaCampos.data[i].nombre.toLowerCase());
                if ($scope.listaCampos.data[i].tipoDato === 'bool') {
                    plantilla += '<md-checkbox id="id' + (i + 1) + '" class="md-primary" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '">' +
                    $scope.listaCampos.data[i].nombre + '</md-checkbox>' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'integer') {
                    if ($scope.listaCampos.data[i].nomenclador == 0) {
                        plantilla += '<md-input-container class="md-block">' +
                        '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                        '<input id="id' + (i + 1) + '" type="number" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required">' +
                        '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                        '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                        '<span ng-show="instancia.' + nuevoNombre + '.$error.pattern">El campo debe ser un valor entero.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                        '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>';
                    } else {
                        plantilla += '<md-input-container class="md-block">' +
                        '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                        '<md-select name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required">' +
                        '<md-option ng-repeat="dato in comboIns.' + nuevoNombre + '" required="required" value="{{dato.IDNOP}}">' +
                        '{{dato.NomNOP}}' +
                        '</md-option>' +
                        '</md-select>' +
                        '<span class="messages" ng-show="campo.tipo.$dirty">' +
                        '<span ng-show="campo.tipo.$error.required">El campo es obligatorio.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.tipoCampo">' +
                        '<div ng-repeat="message in errors.tipoCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>';
                    }
                } else if ($scope.listaCampos.data[i].tipoDato === 'string') {
                    plantilla += '<md-input-container class="md-block">' +
                    '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<input id="id' + (i + 1) + '" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required" maxlength="255">' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'date') {
                    plantilla += '<label>' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<md-datepicker ng-required="true" name="' + nuevoNombre + '" md-placeholder="Entre la fecha" id="id' + (i + 1) + '" ng-model="model.' + nuevoNombre + '">' + '</md-datepicker>' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'double') {
                    plantilla += '<md-input-container class="md-block">' +
                    '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<input id="id' + (i + 1) + '" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required" name="double" ng-pattern="/^-?[0-9]+([.][0-9]+)?$/">' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.pattern">El campo debe ser un valor real.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>';
                }
            }
            plantilla += '<div class="md-actions">' +
            '<md-button class="md-primary md-raised" ng-disabled="!instancia.$valid ||instancia.double.$error.pattern || disab" ng-click="add(); des();">Aceptar</md-button>' +
            '<md-button class="md-raised" ng-disabled="!instancia.$valid ||instancia.double.$error.pattern || disab" ng-click="aplicar(); des();">Aplicar</md-button>' +
            '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '</md-content>';
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: plantilla,
                locals: {
                    items: $scope.listaInstancia,
                    campos: $scope.listaCampos.data,
                    nombre: $scope.nombAux,
                    idAux: $scope.idAux,
                    comboIns: $scope.comboIns,
                    idPadre: $scope.idPadre
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, campos, nombre,comboIns, idAux, idPadre, toastr) {

                //$scope.parents = instanciaPadre;
                $scope.comboIns = comboIns;

                function obtenerIdCampo(nombre) {
                    for (var i = 0; i < campos.length; i++) {
                        if (campos[i].nombre == nombre) {
                            return campos[i].id;
                        }
                    }
                    return -1;
                }

                $scope.disab = false;

                $scope.des = function () {
                    $scope.disab = true;
                };
                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.add = function () {

                    var data = {
                        nombre: $scope.nombInst,
                        idop: idPadre,
                        valores: []
                    };

                    for (var i = 0; i < campos.length; i++) {
                        var nombreCam = eliminarCaracterInvalidos(campos[i].nombre.toLowerCase());
                        var valorCam = $scope.model[nombreCam];
                        if (campos[i].tipoDato === 'bool' && valorCam == undefined) {
                            valorCam = false;
                        } else if (campos[i].tipoDato === 'date') {

                            valorCam = moment(valorCam).format('YYYY-MM-DD');
                        } else if (campos[i].tipoDato === 'integer') {

                            valorCam = parseInt(valorCam);
                        }
                        //console.log(valorCam);
                        data.valores.push({id: obtenerIdCampo(campos[i].nombre), valor: valorCam})
                    }
                    console.log(data);
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_estructura_instancia_create', {ide: idAux}, true), data: data
                    }).then(function (response) {
                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        if (codigo == "200") {
                            toastr.success(texto.substring(10));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(10));
                        } else {
                            toastr.error(texto.substring(10));
                        }
                        //console.log("Response: " + codigo);

                        $mdDialog.hide();
                        if (idPadre != 0) {
                            getInstEstruc(idPadre);
                        } else {
                            getInstEstrucRaiz();
                        }
                        //getInstEstruc(idAux);
                        getArbolEstrucOp();
                    }, function (response) {
                        "Request failed";
                    });
                };

                $scope.aplicar = function () {
                    var data = {
                        nombre: $scope.nombInst,
                        idop: idPadre,
                        valores: []
                    };

                    for (var i = 0; i < campos.length; i++) {
                        var nombreCam = eliminarCaracterInvalidos(campos[i].nombre.toLowerCase());
                        var valorCam = $scope.model[nombreCam];
                        if (campos[i].tipoDato === 'bool' && valorCam == undefined) {
                            valorCam = false;
                        } else if (campos[i].tipoDato === 'date') {

                            valorCam = moment(valorCam).format('YYYY-MM-DD');
                        } else if (campos[i].tipoDato === 'integer') {

                            valorCam = parseInt(valorCam);
                        }
                        //console.log(valorCam);
                        data.valores.push({id: obtenerIdCampo(campos[i].nombre), valor: valorCam})
                    }
                    console.log(data);
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_estructura_instancia_create', {ide: idAux}, true), data: data
                    }).then(function (response) {
                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        if (codigo == "200") {
                            toastr.success(texto.substring(10));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(10));
                        } else {
                            toastr.error(texto.substring(10));
                        }
                        //console.log("Response: " + codigo);

                        for (var i = 0; i < campos.length; i++) {
                            var nuevo = eliminarCaracterInvalidos(campos[i].nombre.toLowerCase());
                            $scope.model[nuevo] = null;
                        }

                        $scope.nombInst = null;

                        $scope.disab = false;
                        if (idPadre != 0) {
                            getInstEstruc(idPadre);
                        } else {
                            getInstEstrucRaiz();
                        }
                        getArbolEstrucOp();
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };
        //eliminar instancia de estructuras
        $scope.eliminarInstancia = function (selected, ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Eliminar instancia de estructura</h3>' +
                '<span flex=""></span>' +
                '<md-button class="md-icon-button" ng-click="cerrar()" >' +
                '<i class="icon mdi mdi-close i-24"></i>' +
                '<md-tooltip md-direction="left">' +
                'Cerrar ventana' +
                '</md-tooltip>' +
                '</md-button>' +
                '</div>' +
                '</md-toolbar>' +
                '<md-content layout-padding>' +
                '<div>' +
                '<form>' +
                '<p>¿Está seguro que desea eliminar el(los) elemento(s) seleccionado(s)?</p>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-click="delete()">Aceptar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.listaInstancia.valores,
                    consulta: $scope.query,
                    id: $scope.idAux,
                    idPadre: $scope.idPadre
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, id, idPadre, consulta, toastr) {

                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.delete = function () {
                    for (var i = 0; i < selected.length; i++) {
                        var index = getSelectedIndex(selected[i].IdEop);
                        console.log(selected);
                        console.log(selected.length);
                        items.splice(index, 1);
                        $http({
                            method: 'DELETE',
                            url: Routing.generate('eyc_estructura_instancia_delete', {id: selected[i].IdEop}, true)
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            if (codigo == "200") {
                                toastr.success(texto.substring(11));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(11));
                            } else {
                                toastr.error(texto.substring(11));
                            }
                            console.log("Response: " + codigo);
                            'Completado'
                        }, function (response) {
                            "Request failed";
                        })
                    }
                    selected.length = 0;
                    consulta.page = 1;
                    $mdDialog.hide();
                    if (idPadre == 0) {
                        getInstEstrucRaiz();
                    } else if (idPadre == null) {
                        getInstEstrucRaiz();
                    } else {
                        getInstEstruc(idPadre);
                    }
                    getArbolEstrucOp();
                };
            }
        };
        //editar instancia de estructuras
        $scope.editarInstancia = function (selected, ev) {
            var parentEl = angular.element(document.body);
            var plantilla = '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Modificar instancia de estructura</h3>' +
                '<span flex=""></span>' +
                '<md-button ng-click="cerrar()" >' +
                '<i class="icon i-20 mdi mdi-close"></i>' +
                '<md-tooltip md-direction="left">' +
                'Cerrar ventana' +
                '</md-tooltip>' +
                '</md-button>' +
                '</div>' +
                '</md-toolbar>' +
                '<md-content layout-padding>' +
                '<div>' +
                '<form name="instancia" method="post" action="">' +
                '<md-input-container class="md-block">' +
                '<label class="required">Nombre Instancia</label>' +
                '<input name="nombInst" ng-model="nombInst" required="required" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/" maxlength="255">' +
                '<span class="messages" ng-show="instancia.nombInst.$dirty">' +
                '<span ng-show="instancia.nombInst.$error.required">El campo es obligatorio.</span>' +
                '<span ng-show="instancia.nombInst.$error.pattern">El nombre de la instancia no tiene el formato correcto.</span>' +
                '</span>' +
                '</md-input-container>' +
                '<md-input-container class="md-block">';
            //if (!$scope.raizAux) {
            //    plantilla += '<label class="required">Padre</label>' +
            //    '<md-select name="parent" required="required" ng-model="parentId" >' +
            //    '<md-option ng-repeat="parent in parents" required="required" value="{{parent.IdEop}}">' +
            //    '{{parent.NomEOP}}' +
            //    '</md-option>' +
            //    '</md-select>' +
            //    '<span class="messages" ng-show="instancia.parent.$dirty">' +
            //    '<span ng-show="instancia.parent.$error.required">El campo es obligatorio.</span>' +
            //    '</span>' +
            //    '</md-input-container>';
            //}
            function eliminarCaracterInvalidos(palabra) {
                var nueva = '';
                for (var i = 0; i < palabra.length; i++) {
                    if (palabra.charAt(i) != " ") {
                        nueva += palabra.charAt(i);
                    }
                }
                return nueva;
            };
            for (var i = 0; i < $scope.listaCampos.data.length; i++) {
                var nuevoNombre = eliminarCaracterInvalidos($scope.listaCampos.data[i].nombre.toLowerCase());
                if ($scope.listaCampos.data[i].tipoDato === 'bool') {
                    plantilla += '<md-checkbox id="id' + (i + 1) + '" class="md-primary" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '">' +
                    $scope.listaCampos.data[i].nombre + '</md-checkbox>' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'integer') {
                    if ($scope.listaCampos.data[i].nomenclador == 0) {
                        plantilla += '<md-input-container class="md-block">' +
                        '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                        '<input id="id' + (i + 1) + '" type="number" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required">' +
                        '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                        '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                        '<span ng-show="instancia.' + nuevoNombre + '.$error.pattern">El campo debe ser un valor entero.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                        '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>';
                    } else {
                        plantilla += '<md-input-container class="md-block">' +
                        '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                        '<md-select name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required">' +
                        '<md-option ng-repeat="dato in comboIns.' + nuevoNombre + '" required="required" value="{{dato.IDNOP}}">' +
                        '{{dato.NomNOP}}' +
                        '</md-option>' +
                        '</md-select>' +
                        '<span class="messages" ng-show="campo.tipo.$dirty">' +
                        '<span ng-show="campo.tipo.$error.required">El campo es obligatorio.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.tipoCampo">' +
                        '<div ng-repeat="message in errors.tipoCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>';
                    }
                } else if ($scope.listaCampos.data[i].tipoDato === 'string') {
                    plantilla += '<md-input-container class="md-block">' +
                    '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<input id="id' + (i + 1) + '" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required" maxlength="255">' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'date') {
                    plantilla += '<label>' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<md-datepicker ng-required="true" name="' + nuevoNombre + '" md-placeholder="Entre la fecha" id="id' + (i + 1) + '" ng-model="model.' + nuevoNombre + '">' + '</md-datepicker>' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'double') {
                    plantilla += '<md-input-container class="md-block">' +
                    '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<input id="id' + (i + 1) + '" name="' + nuevoNombre + '" ng-model="model.' + nuevoNombre + '" required="required" name="double" ng-pattern="/^-?[0-9]+([.][0-9]+)?$/">' +
                    '<span class="messages" ng-show="instancia.' + nuevoNombre + '.$dirty">' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.required">El campo es obligatorio.</span>' +
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.pattern">El campo debe ser un valor real.</span>' +
                    '</span>' +
                    '<div ng-messages="errors.model.' + nuevoNombre + '">' +
                    '<div ng-repeat="message in errors.model.' + nuevoNombre + '" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>';
                }
            }
            plantilla += '<div class="md-actions">' +
            '<md-button class="md-primary md-raised" ng-disabled="!instancia.$valid || disab" ng-click="modificar(); des();">Aceptar</md-button>' +
            '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
            '</div>' +
            '</form>' +
            '</div>' +
            '</md-content>';
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: plantilla,
                locals: {
                    items: $scope.listaInstancia,
                    campos: $scope.listaCampos.data,
                    instanciaPadre: $scope.listaInstancia2,
                    nombre: $scope.nombAux,
                    comboIns: $scope.comboIns,
                    idAux: $scope.idAux,
                    idPadre: $scope.idPadre
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items,comboIns, instanciaPadre, campos, nombre, idAux, idPadre, toastr) {
                console.log(selected);
                $scope.model = {};
                $scope.disab = false;
                $scope.comboIns = comboIns;
                $scope.nombInst = selected.NomEOP;
                $scope.des = function () {
                    $scope.disab = true;
                };
                function obtenerIdCampo(nombre) {
                    for (var i = 0; i < campos.length; i++) {
                        if (campos[i].nombre == nombre) {
                            return campos[i].id;
                        }
                    }
                    return -1;
                };

                for (var i = 0; i < campos.length; i++) {
                    var nuevo = eliminarCaracterInvalidos(campos[i].nombre.toLowerCase());
                    if (eliminarCaracterInvalidos(selected.valores[i]) == undefined || eliminarCaracterInvalidos(selected.valores[i]) == "") {
                        if (campos[i].tipoDato === 'date') {
                            $scope.model[nuevo] = new Date();
                        } else if (campos[i].tipoDato === 'integer') {
                            $scope.model[nuevo] = 0;
                        } else if (campos[i].tipoDato === 'double') {
                            $scope.model[nuevo] = 0;
                        } else if (campos[i].tipoDato === 'bool') {
                            $scope.model[nuevo] = false;
                        }
                    } else {
                        if (campos[i].tipoDato === 'date') {
                            var aux = selected[0].valores[i].split('-');
                            console.log("as: " + aux[2]);
                            var fecha = new Date();
                            fecha.setYear(aux[0]);
                            fecha.setMonth((parseInt(aux[1]) - 1), aux[2]);
                            //}
                            $scope.model[nuevo] = fecha;
                        } else if (campos[i].tipoDato === 'integer') {
                            $scope.model[nuevo] = parseInt(selected.valores[i]);
                        } else if (campos[i].tipoDato === 'double') {
                            $scope.model[nuevo] = parseFloat(selected.valores[i]);
                        } else if (campos[i].tipoDato === 'bool') {
                            if (selected.valores[i] == 'true') {
                                $scope.model[nuevo] = true;
                            } else {
                                $scope.model[nuevo] = false;
                            }
                        } else {
                            $scope.model[nuevo] = selected.valores[i];
                        }
                    }
                }

                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.modificar = function () {
                    var data = {
                        _method: 'PUT',
                        nombre: $scope.nombInst,
                        valores: []
                    };
                    for (var i = 0; i < campos.length; i++) {
                        var nombreCam = eliminarCaracterInvalidos(campos[i].nombre.toLowerCase());
                        var valorCam = $scope.model[nombreCam];
                        if (campos[i].tipoDato === 'bool' && valorCam == undefined) {
                            valorCam = false;
                        } else if (campos[i].tipoDato === 'date') {
                            valorCam = moment(valorCam).format('YYYY-MM-DD');
                        } else {
                            valorCam = valorCam.toString();
                        }
                        data.valores.push({id: obtenerIdCampo(campos[i].nombre), valor: valorCam})
                    }
                    //console.log(data);
                    console.log(selected);
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_estructura_instancia_update', {ideop: parseInt(selected.IdEop)}, true),
                        data: data
                    }).then(function (response) {
                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        if (codigo == "200") {
                            toastr.success(texto.substring(9));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(9));
                        } else {
                            toastr.error(texto.substring(9));
                        }
                        $mdDialog.hide();
                        if (idPadre != 0) {
                            getInstEstruc(idPadre);
                        } else {
                            getInstEstrucRaiz();
                        }
                        getArbolEstrucOp();
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };

        function getSelectedIndex(id) {
            for (var i = 0; i < $scope.listaInstancia.valores.length; i++) {
                if ($scope.listaInstancia.valores[i].idI === id) {
                    return i;
                }
            }
            return -1;
        };

        function getSelectedIndexEstructura(id) {
            for (var i = 0; i < $scope.listaEstructuras.length; i++) {
                if ($scope.listaEstructuras[i].id === id) {
                    return i;
                }
            }
            return -1;
        };
    }
    ]
);
