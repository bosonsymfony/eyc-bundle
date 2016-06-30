angular.module('app')
    .controller('eycNomencladorCtrl',
    ['$http', '$mdEditDialog', '$q', '$timeout', '$scope', 'eycNomencladorSvc', '$mdDialog',
        '$mdMedia', function ($http, $mdEditDialog, $q, $timeout, $scope, eycNomencladorSvc, $mdDialog, $mdMedia) {
        'use strict';

        $scope.selected = [];

        $scope.expRegDecimal = /^-?[0-9]+([.][0-9]+)?$/;

        $scope.query = {
            filter: '',
            order: 'id',
            limit: 5,
            page: 1
        };

        $scope.columns = [{
            name: 'ID',
            orderBy: 'id'
        }, {
            descendFirst: true,
            name: 'Nombre',
            orderBy: 'nombre'
        }];


        $scope.oculto = {
            visibility: 'hidden'
        };

        $scope.idAux = '';
        $scope.nombAux = '';

        getnomenclador();

        //listar instancias de nomencladores
        function getInstNom(id, query) {
            $scope.listaInstancia.count = 0;
            $scope.listaInstancia.valores = [];
            $scope.promise = eycNomencladorSvc.getInstancias(id).get(query || $scope.query, success2).$promise;
        };
        function success2(instancias) {
            //$scope.listaInst = instancias;
            $scope.listaInstancia = instancias;
            $scope.selected = [];
        };

        //listar nomencladores
        function getnomenclador() {
            $scope.nomencladores = [];
            $scope.promise = eycNomencladorSvc.getnomenclador().get(success3).$promise;
        };
        function success3(nomencladores){
            $scope.nomencladores = nomencladores.data;
        };
        //paginar tabla instancia de nomencladores
        $scope.onPaginate = function (page, limit) {
            getInstNom($scope.idAux, angular.extend({}, $scope.query, {page: page, limit: limit}));
        };

        //ordenar tabla de instancias de nomencladores
        $scope.onReorder = function (order) {
            getInstNom($scope.idAux, angular.extend({}, $scope.query, {order: order}));
        };

        $scope.listaCampos = [];
        $scope.showSelected = function (node, selected) {
            $scope.listaInstancia = [];
            $scope.listaInstancia.valores = [];
            $scope.listaCampos = [];
            $scope.selected.length = 0;
            $scope.query.page = 1;
            if (selected) {
                $scope.sinCampos = true;
                $scope.oculto = {
                    visibility: 'visible'
                };
                $scope.idNomenclador = null;
                var data = {
                    criterio: node.nombre
                };
                $scope.listaCampos = [];

                //obtener nomenclador dado un nombre
                $http({
                    method: 'POST', url: Routing.generate('eyc_nomenclador_buscar_nombre', {}, true), data: data
                }).then(function (response) {
                    $scope.idNomenclador = response.data[0].id;
                    $scope.idAux = response.data[0].id;
                    $scope.nombAux = response.data[0].nombre;

                    //mostrar campos de un nomenclador
                    $http({
                        method: 'GET',
                        url: Routing.generate('eyc_nomenclador_campos2', {id: $scope.idNomenclador}, true)
                    }).then(function (response) {
                        var auxiliar = '404 GET: El recurso solicitado con el identificador ' + $scope.idNomenclador + ' no existe.';
                        if (response.data != auxiliar) {
                            $scope.listaCampos = response.data;
                            console.log("campos "+response.data.count);
                            if(response.data.count > 0){
                                $scope.sinCampos = false;
                            }
                        }
                    }, function (response) {
                        "Request failed";
                    });
                    //mostrar instancias de un nomenclador
                    getInstNom($scope.idNomenclador);

                }, function (response) {
                    "Request failed";
                });
            } else {
                $scope.oculto = {
                    visibility: 'hidden'
                };
                $scope.listaCampos = [];
                $scope.listaInstancia = [];
                $scope.listaInstancia.valores = [];
            }
        };

        function buscarNom(id) {
            for (var i = 0; i < $scope.nomencladores.length; i++) {
                if ($scope.nomencladores[i].id == id) {
                    return i;
                }
            }
            return -1;
        }

        //eliminar nomenclador
        $scope.eliminarNomenclador = function (node1, ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Eliminar nomenclador</h3>' +
                '<span flex=""></span>' +
                '<md-button class="md-icon-button" ng-click="cerrar()" >' +
                '<i class="icon mdi mdi-close i-24"></i>' +
                '<md-tooltip md-direction="left">' +
                'Cerrar ventana' +
                '</md-tooltip>' +
                '</md-button>' +
                '</div>' +
                '</md-toolbar>' +
                '<div>' +
                '<form>' +
                '<p>¿Está seguro que desea eliminar el elemento seleccionado?</p>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-disabled="disab" ng-click="delete(); des();">Aceptar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.nomencladores,
                    oculto: $scope.oculto
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, oculto, toastr) {
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

                $scope.delete = function () {
                    $scope.idNomenclador = '';
                    var data = {
                        criterio: node1.nombre
                    };
                    for (var i = 0; i < items.length; i++) {

                        if (items[i].nombre == node1.nombre) {
                            items.splice(i, 1);
                        }
                    }
                    $http({
                        method: 'POST', url: Routing.generate('eyc_nomenclador_buscar_nombre', {}, true), data: data
                    }).then(function (response) {
                        $scope.idNomenclador = response.data[0].id;
                        console.log("id: " + response.data[0]);

                        $http({
                            method: 'DELETE',
                            url: Routing.generate('eyc_nomenclador_delete', {id: $scope.idNomenclador}, true)
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
                            CambiarEstioloOculto('hidden');
                            $mdDialog.hide();
                            getInstNom(-1);

                        }, function (response) {
                            "Request failed";
                        });
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };

        function CambiarEstioloOculto(tipo) {
            $scope.oculto = {
                visibility: tipo
            };
        };

        $scope.selectedCampo = [];

        $scope.query2 = {
            order: 'id',
            limit: 5,
            page: 1
        };

        $scope.sombra = {
            visibility: 'hidden',
            backgroundColor: 'black'
        };
        $scope.capa1 = {
            zIndex: '1'
        };
        $scope.capa2 = {
            zIndex: '2',
            position: 'absolute',
            top: '0px',
            visibility: 'hidden'
        };

        $scope.aux = "";
        $scope.mostrarCapa2 = function (node1) {
            $scope.selected.length = 0;
            $scope.query.page = 1;
            $scope.aux = $scope.oculto;
            if (document.body) {
                var ancho = (document.body.clientWidth);
                var alto = (document.body.clientHeight);
            }
            else {
                var ancho = (window.innerWidth);
                var alto = (window.innerHeight);
            }
            $scope.capa1 = {
                zIndex: '2',
                visibility: 'hidden'
            };
            $scope.oculto = {
                zIndex: '2'
            };
            console.log("ancho ven: " + (ancho * 0.85) + 'px');
            $scope.vistaCampo = {
                width: (ancho * 0.85) + 'px'
            }
            $scope.capa2 = {
                zIndex: '1',
                position: 'absolute',
                top: '30px',
                left: '20px',
                visibility: 'visible'
            };

            $scope.idNomenclador = null;
            var data = {
                criterio: node1.nombre
            };

            //obtener nomenclador dado el nombre
            $http({
                method: 'POST', url: Routing.generate('eyc_nomenclador_buscar_nombre', {}, true), data: data
            }).then(function (response) {
                $scope.idNomenclador = response.data[0].id;

            //listar campos de un nomenclador
                function getCamposNom(id, query) {
                    $scope.promise = eycNomencladorSvc.getCampos(id).get(query || $scope.query2, success).$promise;
                };
                function success(campos) {
                    $scope.listaCampos = campos;
                    $scope.selectedCampo = [];
                };
            //paginar campos de un nomenclador
                $scope.onPaginate2 = function (page, limit) {
                    getCamposNom($scope.idNomenclador, angular.extend({}, $scope.query2, {page: page, limit: limit}));
                };
                //ordenar campos de un nomenclador
                $scope.onReorder2 = function (order) {
                    getCamposNom($scope.idNomenclador, angular.extend({}, $scope.query2, {order: order}));
                };

                getCamposNom($scope.idNomenclador);
                $scope.checkDis = true;

                //adicionar campo de un nomenclador
                $scope.adicionarCampo = function (ev) {
                    var parentEl = angular.element(document.body);
                    var plantilla = '<md-toolbar class="md-panel-toolbar">' +
                        '<div class="md-toolbar-tools">' +
                        '<h3>Adicionar campo</h3>' +
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
                        '<form name="campo" method="post" action="">' +
                        '<md-input-container class="md-block">' +
                        '<label class="required">*Nombre</label>' +
                        '<input type="text" name="nombre" ng-model="nombreCampo" required="required" maxlength="255" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/"/>' +
                        '<span class="messages" ng-show="campo.nombre.$dirty">' +
                        '<span ng-show="campo.nombre.$error.required">El campo es obligatorio.</span>' +
                        '<span ng-show="campo.nombre.$error.pattern">Formato de nombre incorrecto.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.nombreCampo">' +
                        '<div ng-repeat="message in errors.nombreCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<label class="required">*Tipo de dato</label>' +
                        '<md-select ng-disabled="!checkDisab" name="tipo" required="required" ng-model="tipoCampo" >' +
                        '<md-option ng-repeat="dato in datos" required="required" value="{{dato}}">' +
                        '{{dato}}' +
                        '</md-option>' +
                        '</md-select>' +
                        '<span class="messages" ng-show="campo.tipo.$dirty">' +
                        '<span ng-show="campo.tipo.$error.required">El campo es obligatorio.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.tipoCampo">' +
                        '<div ng-repeat="message in errors.tipoCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<label >Descripción</label>' +
                        '<textarea ng-model="descripCampo" md-maxlength="150" rows="5" md-select-on-focus></textarea>' +
                        '<div ng-messages="errors.descripCampo">' +
                        '<div ng-repeat="message in errors.descripCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<md-checkbox ng-model="vinCampo" class="md-primary" aria-label="Vinculado" ng-click="toggle()">' +
                        'Vinculado' +
                        '</md-checkbox>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<label >NomencladorVin</label>' +
                        '<md-select ng-disabled="checkDisab" ng-model="nomVinCampo">' +
                        '<md-option ng-repeat="id in listaIDS track by $index " value="{{id.id}}">' +
                        '{{id.nombre}}' +
                        '</md-option>' +
                        '</md-select>' +
                        '<div ng-messages="errors.nomVinCampo">' +
                        '<div ng-repeat="message in errors.nomVinCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>';

                    plantilla += '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-disabled="!campo.$valid || disab ||campo.nombre.$error.pattern" ng-click="add(); des();">Aceptar</md-button>' +
                    '<md-button class="md-raised" ng-disabled="!campo.$valid || disab ||campo.nombre.$error.pattern" ng-click="aplicar(); des();">Aplicar</md-button>' +
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
                            items: $scope.listaCampos.data,
                            id: $scope.idNomenclador,
                            nomencladores: $scope.nomencladores,
                            nombreIns: $scope.nombAux
                        },
                        controller: DialogController
                    });
                    function DialogController($scope, $mdDialog, items, id, nombreIns, nomencladores, toastr) {
                        $scope.datos = ["bool", "integer", "double", "string", "date"];
                        $scope.vinCampo = false;
                        $scope.disab = false;
                        $scope.checkDisab = true;

                        $scope.des = function () {
                            $scope.disab = true;
                        };

                        $scope.toggle = function () {
                            if (!$scope.vinCampo) {
                                $scope.checkDisab = false;
                                $scope.tipoCampo = 'integer';
                            } else {
                                $scope.nomVinCampo = "";
                                $scope.checkDisab = true;
                            }
                        };

                        $scope.cancel = function () {
                            $mdDialog.cancel();
                            $mdDialog.hide();
                        };
                        $scope.cerrar = function () {
                            $mdDialog.hide();
                        };
                        $scope.listaIDS = [];
                        for (var i = 0; i < nomencladores.length; i++) {
                            var nom = {
                                nombre: nomencladores[i].nombre,
                                id: nomencladores[i].id
                            };
                            $scope.listaIDS.push(nom);
                        }
                        $scope.add = function () {
                            if ($scope.descripCampo == null) {
                                $scope.descripCampo = "";
                            }
                            var data = {
                                nombre: $scope.nombreCampo,
                                tipodato: $scope.tipoCampo,
                                descripcion: $scope.descripCampo,
                                vinculado: $scope.vinCampo,
                                nomencladorvin: $scope.nomVinCampo
                            };
                            $http({
                                method: 'POST',
                                url: Routing.generate('eyc_nomenclador_campo_create', {idn: id}, true),
                                data: data
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

                                $mdDialog.hide();
                                getCamposNom(id);
                            }, function (response) {
                                "Request failed";
                            });
                        };
                        $scope.aplicar = function () {
                            if ($scope.descripCampo == null) {
                                $scope.descripCampo = "";
                            }
                            var data = {
                                nombre: $scope.nombreCampo,
                                tipodato: $scope.tipoCampo,
                                descripcion: $scope.descripCampo,
                                vinculado: $scope.vinCampo,
                                nomencladorvin: $scope.nomVinCampo
                            };
                            $http({
                                method: 'POST',
                                url: Routing.generate('eyc_nomenclador_campo_create', {idn: id}, true),
                                data: data
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
                                $scope.checkDisab = true;
                                $scope.nombreCampo = '';
                                $scope.tipoCampo = '';
                                $scope.descripCampo = '';
                                $scope.vinCampo = false;
                                $scope.nomVinCampo = '';
                                $scope.disab = false;
                                getCamposNom(id);
                            }, function (response) {
                                "Request failed";
                            });
                        };
                    }
                };

                //editar campo de un nomenclador
                $scope.editarCampo = function (selectedCampo, ev) {
                    var parentEl = angular.element(document.body);
                    $mdDialog.show({
                        parent: parentEl,
                        targetEvent: ev,
                        clickOutsideToClose: false,
                        template: '<md-toolbar class="md-panel-toolbar">' +
                        '<div class="md-toolbar-tools">' +
                        '<h3>Modificar campo</h3>' +
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
                        '<form name="campo" method="post" action="">' +
                        '<md-input-container class="md-block">' +
                        '<label class="required">*Nombre</label>' +
                        '<input type="text" name="nombre" ng-model="nombreCampo" required="required" maxlength="255" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/"/>' +
                        '<span class="messages" ng-show="campo.$dirty">' +
                        '<span ng-show="campo.nombre.$error.required">El campo es obligatorio.</span>' +
                        '<span ng-show="campo.nombre.$error.pattern">Formato de nombre incorrecto.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.nombreCampo">' +
                        '<div ng-repeat="message in errors.nombreCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<label class="required">*Tipo de dato</label>' +
                        '<md-select ng-disabled="!checkDisab" name="tipo" required="required" ng-model="tipoCampo" >' +
                        '<md-option ng-repeat="dato in datos" required="required" value="{{dato}}">' +
                        '{{dato}}' +
                        '</md-option>' +
                        '</md-select>' +
                        '<span class="messages" ng-show="campo.$dirty">' +
                        '<span ng-show="campo.tipo.$error.required">El campo es obligatorio.</span>' +
                        '</span>' +
                        '<div ng-messages="errors.tipoCampo">' +
                        '<div ng-repeat="message in errors.tipoCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<label >Descripción</label>' +
                        '<textarea ng-model="descripCampo" md-maxlength="150" rows="5" md-select-on-focus></textarea>' +
                        '<div ng-messages="errors.descripCampo">' +
                        '<div ng-repeat="message in errors.descripCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<md-checkbox ng-model="vinCampo" class="md-primary" aria-label="Vinculado" ng-click="toggle()">' +
                        'Vinculado' +
                        '</md-checkbox>' +
                        '</md-input-container>' +
                        '<md-input-container class="md-block">' +
                        '<label >NomencladorVin</label>' +
                        '<md-select ng-disabled="checkDisab" ng-model="nomVinCampo">' +
                        '<md-option ng-repeat="id in listaIDS track by $index " value="{{id.id}}">' +
                        '{{id.nombre}}' +
                        '</md-option>' +
                        '</md-select>' +
                        '<div ng-messages="errors.nomVinCampo">' +
                        '<div ng-repeat="message in errors.nomVinCampo" ng-bind="message"></div>' +
                        '</div>' +
                        '</md-input-container>' +
                        '<div class="md-actions">' +
                        '<md-button class="md-primary md-raised" ng-disabled="!campo.$valid || disab ||campo.nombre.$error.pattern" ng-click="modificar(); des();">Aceptar</md-button>' +
                        '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                        '</div>' +
                        '</form>' +
                        '</div>' +
                        '</md-content>',
                        locals: {
                            items: $scope.listaCampos.data,
                            id: $scope.idNomenclador,
                            nomencladores: $scope.nomencladores
                        },
                        controller: DialogController
                    });
                    function DialogController($scope, $mdDialog, items, id, nomencladores, toastr) {
                        $scope.idCampo = selectedCampo[selectedCampo.length - 1].id;
                        $scope.nombreCampo = selectedCampo[selectedCampo.length - 1].nombre;
                        $scope.tipoCampo = selectedCampo[selectedCampo.length - 1].tipoDato;
                        $scope.descripCampo = selectedCampo[selectedCampo.length - 1].descripcion;
                        $scope.vinCampo = selectedCampo[selectedCampo.length - 1].vinculado;
                        $scope.nomVinCampo = selectedCampo[selectedCampo.length - 1].nomencladorVin;
                        $scope.checkDisab = !$scope.vinCampo;

                        $scope.datos = ["bool", "integer", "double", "string", "date"];

                        $scope.toggle = function () {
                            if (!$scope.vinCampo) {
                                $scope.checkDisab = false;
                                $scope.tipoCampo = 'integer';
                            } else {
                                $scope.nomVinCampo = "";
                                $scope.checkDisab = true;
                            }
                        };

                        $scope.listaIDS = [];
                        for (var i = 0; i < nomencladores.length; i++) {
                            var nom = {
                                nombre: nomencladores[i].nombre,
                                id: nomencladores[i].id
                            };
                            $scope.listaIDS.push(nom);
                        }
                        $scope.disab = false;

                        $scope.des = function () {
                            $scope.disab = true;
                        };
                        $scope.cerrar = function () {
                            $mdDialog.hide();
                        };
                        $scope.cancel = function () {
                            $mdDialog.cancel();
                            $mdDialog.hide();
                        };
                        $scope.modificar = function () {
                            if ($scope.descripCampo == null) {
                                $scope.descripCampo = "";
                            }

                            var data = {
                                _method: 'PUT',
                                nombre: $scope.nombreCampo,
                                tipodato: $scope.tipoCampo,
                                descripcion: $scope.descripCampo,
                                vinculado: $scope.vinCampo,
                                nomencladorvin: $scope.nomVinCampo
                            };
                            $http({
                                method: 'POST',
                                url: Routing.generate('eyc_nomenclador_campo_update', {idc: $scope.idCampo}, true),
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
                                console.log("Response: " + codigo);
                                for (var i = 0; i < items.length; i++) {
                                    if (items[i].id === selectedCampo[selectedCampo.length - 1].id) {
                                        items[i].nombre = $scope.nombreCampo;
                                        items[i].tipoDato = $scope.tipoCampo;
                                        items[i].descripcion = $scope.descripCampo;
                                        items[i].vinculado = $scope.vinCampo;
                                        items[i].nomencladorvin = $scope.nomVinCampo;
                                        $mdDialog.hide();
                                        getCamposNom(id);
                                    }
                                }
                            }, function (response) {
                                "Request failed";
                            });
                        };
                    }
                };

                //eliminar campo de un nomenclador
                $scope.eliminarCampo = function (selectedCampo, ev) {
                    var parentEl = angular.element(document.body);
                    $mdDialog.show({
                        parent: parentEl,
                        targetEvent: ev,
                        clickOutsideToClose: false,
                        template: '<md-toolbar class="md-panel-toolbar">' +
                        '<div class="md-toolbar-tools">' +
                        '<h3>Eliminar campo</h3>' +
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
                            items: $scope.listaCampos.data,
                            id: $scope.idNomenclador,
                            consulta: $scope.query2
                        },
                        controller: DialogController
                    });
                    function DialogController($scope, $mdDialog, items, id, consulta, toastr) {

                        $scope.cancel = function () {
                            $mdDialog.cancel();
                            $mdDialog.hide();
                        };
                        $scope.cerrar = function () {
                            $mdDialog.hide();
                        };
                        $scope.delete = function () {
                            for (var i = 0; i < selectedCampo.length; i++) {
                                var index = getSelectedCampoIndex(selectedCampo[i].id);
                                items.splice(index, 1);
                                $http({
                                    method: 'DELETE',
                                    url: Routing.generate('eyc_nomenclador_campo_delete', {id: selectedCampo[i].id}, true)
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
                                });
                            }
                            selectedCampo.length = 0;
                            consulta.page = 1;
                            
                            getCamposNom(id);
							$mdDialog.hide();
                            function getSelectedCampoIndex(id) {
                                for (var i = 0; i < items.length; i++) {
                                    if (items[i].id === id) {
                                        return i;
                                    }
                                }
                                return -1;
                            };
                        };
                    }
                };
            }, function (response) {
                "Request failed";
            });
        };
        $scope.mostrarCapa1 = function (selectedCampo) {

            selectedCampo.length = 0;
            $scope.query2.page = 1;
            $scope.capa2 = {
                zIndex: '2',
                position: 'absolute',
                top: '0px',
                visibility: 'hidden'
            };
            $scope.capa1 = {
                zIndex: '1',
                visibility: 'visible'
            };
            $scope.sombra = {
                visibility: 'hidden'
            };
            $scope.oculto = $scope.aux;

            //mostrar campos sin filtro de un nomenclador
            $http({
                method: 'GET',
                url: Routing.generate('eyc_nomenclador_campos2', {id: $scope.idNomenclador}, true)
            }).then(function (response) {
                var auxiliar = '404 GET: El recurso solicitado con el identificador ' + $scope.idNomenclador + ' no existe.';
                if (response.data != auxiliar) {
                    $scope.listaCampos = response.data;
                }
            }, function (response) {
                "Request failed";
            });

            //mostrar instancias de un nomenclador

            getInstNom($scope.idAux);
            //$http({
            //    method: 'GET', url: Routing.generate('eyc_nomenclador_instancias', {id: $scope.idAux}, true)
            //}).then(function (response) {
            //    var auxiliar = '404 GET: El recurso solicitado con el identificador ' + $scope.idAux + ' no existe.';
            //
            //    function organizarInstancias(id) {
            //        var instancias = {
            //            IDNOP: id,
            //            valor: []
            //        };
            //        for (var i = 0; i < datosIns.length; i++) {
            //            if (datosIns[i].IDNOP == id) {
            //                instancias.valor.push(datosIns[i].valor);
            //            }
            //        }
            //        return instancias;
            //    };
            //
            //    function buscarIDsIns() {
            //        var ids = [];
            //        for (var i = 0; i < datosIns.length; i++) {
            //            if (ids.indexOf(datosIns[i].IDNOP) == -1) {
            //                ids.push(datosIns[i].IDNOP);
            //            }
            //        }
            //        return ids;
            //    };
            //
            //    function completarCamposVacios(longVal, longCam, valoresIns) {
            //        var cant = longCam - longVal;
            //        var agregar = "";
            //        for (var i = 0; i < valoresIns.valores.length; i++) {
            //            for (var j = 0; j < cant; j++) {
            //                valoresIns.valores[i].valor.push(agregar);
            //                agregar += " ";
            //            }
            //        }
            //        return valoresIns;
            //    };
            //    if (response.data != auxiliar) {
            //        $scope.datos = response.data;
            //        var datosIns = $scope.datos.valores;
            //        var ids = buscarIDsIns();
            //        var valoresIns = {
            //            valores: []
            //        };
            //        for (var i = 0; i < ids.length; i++) {
            //            valoresIns.valores.push(organizarInstancias(ids[i]));
            //        }
            //        var valoresInsNuevo = completarCamposVacios(valoresIns.valores[0].valor.length, $scope.listaCampos.length, valoresIns);
            //        console.log(valoresInsNuevo.valores);
            //        $scope.listaInstancia = valoresInsNuevo;
            //    }
            //}, function (response) {
            //    "Request failed";
            //});
        };


        $scope.listadoNomencladores2 = {
            width: '0px',
            marginRight: '10px',
            overflow: 'hidden',
            float: 'left'
        };

        $scope.listadoNomencladores = {
            width: '20%',
            float: 'left',
            marginRight: '10px',
            fontSize: '14px'
        };

        $scope.barraTablaNomenc = {
            width: '75%'
        };

        $scope.tablaNomc = {
            width: '75%'
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
                width: '20%',
                float: 'left'
            };
            $scope.barraTablaNomenc = {
                width: '75%'
            };

            $scope.tablaNomc = {
                width: '75%'
            };
        };

        //adicionar nomenclador
        $scope.adicionarNomenclador = function (ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Adicionar nomenclador</h3>' +
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
                '<form name="form" method="post" action="">' +
                '<md-input-container class="md-block">' +
                '<label class="required">*Nombre</label>' +
                '<input type="text" name="nombre" ng-model="nombreNomenclador" required="required" maxlength="255" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/">' +
                '<span class="messages" ng-show="form.nombre.$dirty">' +
                '<span ng-show="form.nombre.$error.required">El campo es obligatorio.</span>' +
                '<span ng-show="form.nombre.$error.pattern">Formato de nombre incorrecto.</span>' +
                '</span>' +
                '<div ng-messages="errors.nombre">' +
                '<div ng-repeat="message in error.nombre" ng-bind="message"></div>' +
                '</div>' +
                '</md-input-container>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-disabled="!form.$valid || disab ||form.nombreNomenclador.$error.pattern" ng-click="add(); des();">Aceptar</md-button>' +
                '<md-button class="md-raised" ng-disabled="!form.$valid || disab ||form.nombreNomenclador.$error.pattern" ng-click="aplicar(); des();">Aplicar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.nomencladores
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, toastr) {
                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };

                $scope.disab = false;

                $scope.des = function () {
                    $scope.disab = true;
                };
                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.add = function () {
                    var data = {
                        nombre: $scope.nombreNomenclador
                    };
                    $http({
                        method: 'POST', url: Routing.generate('eyc_nomenclador_create', {}, true), data: data
                    }).then(function (response) {
                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        console.log("Response: " + codigo);
                        console.log("Response2: " +texto);
                        if (codigo == "200") {
                            toastr.success(texto.substring(10));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(10));
                        } else {
                            toastr.error(texto.substring(10));
                        }

                        $mdDialog.hide();
                        getnomenclador();
                    }, function (response) {
                        "Request failed";
                    });
                };
                $scope.aplicar = function () {
                    var data = {
                        nombre: $scope.nombreNomenclador
                    };
                    $http({
                        method: 'POST', url: Routing.generate('eyc_nomenclador_create', {}, true), data: data
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
                        console.log("Response: " + codigo);
                        console.log("Response2: " +texto);
                        $scope.nombreNomenclador = '';
                        $scope.disab = false;
                        getnomenclador();
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };

        //editar nomenclador
        $scope.editarNomenclador = function (node1, ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Modificar nomenclador</h3>' +
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
                '<form name="form" method="post" action="">' +
                '<md-input-container class="md-block">' +
                '<label class="required">*Nombre</label>' +
                '<input type="text" name="nombre" ng-model="nombreNomenclador" required="required" maxlength="255" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/">' +
                '<span class="messages" ng-show="form.nombre.$dirty">' +
                '<span ng-show="form.nombre.$error.required">El campo es obligatorio.</span>' +
                '<span ng-show="form.nombre.$error.pattern">Formato de nombre incorrecto.</span>' +
                '</span>' +
                '<div ng-messages="errors.nombre">' +
                '<div ng-repeat="message in errors.nombre" ng-bind="message"></div>' +
                '</div>' +
                '</md-input-container>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-disabled="!form.$valid || disab ||form.nombreNomenclador.$error.pattern" ng-click="modificar(); des();">Aceptar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.nomencladores
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, toastr) {
                $scope.nombreNomenclador = node1.nombre;
                $scope.disab = false;

                $scope.des = function () {
                    $scope.disab = true;
                };

                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.modificar = function () {
                    $scope.idNomenclador = null;
                    var data = {
                        criterio: node1.nombre
                    };
                    $http({
                        method: 'POST', url: Routing.generate('eyc_nomenclador_buscar_nombre', {}, true), data: data
                    }).then(function (response) {
                        $scope.idNomenclador = response.data[0].id;
                        var data2 = {
                            _method: 'PUT',
                            nombre: $scope.nombreNomenclador
                        };
                        $http({
                            method: 'POST',
                            url: Routing.generate('eyc_nomenclador_update', {id: $scope.idNomenclador}, true),
                            data: data2
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
                            console.log("Response: " + codigo);
                            var index = ObtenerPosNomenclador(node1.nombre)
                            items[index].nombre = $scope.nombreNomenclador;
                        }, function (response) {
                            "Request failed";
                        });
                        $mdDialog.hide();
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };

        function ObtenerPosNomenclador(nombre) {
            for (var i = 0; i < $scope.nomencladores.length; i++) {
                if ($scope.nomencladores[i].nombre === nombre) {
                    return i;
                }
            }
            return -1;
        };

        $scope.listaInstancia = [];


        $scope.deselect = function (item) {
            console.log(item.nombre, 'was deselected');
        };

        $scope.log = function (item) {
            console.log(item.nombre, 'was selected');
        };

        $scope.loadStuff = function () {
            /*
             $scope.promise = $timeout(function () {

             }, 2000);
             */
        };

        $scope.adicionarInst = function () {

                    $http({
                        method: 'GET',
                        url: Routing.generate('eyc_nomenclador_campos_vinculados', {id: $scope.idAux}, true)
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
        };

        $scope.editarInst = function(selected){
            $http({
                method: 'GET',
                url: Routing.generate('eyc_nomenclador_campos_vinculados', {id: $scope.idAux}, true)
            }).then(function (response) {
                if (response.data != undefined) {
                    if(response.data != []){
                        $scope.comboIns = response.data;
                        $scope.editarInstancia(selected);
                    }
                }
            }, function (response) {
                "Request failed";
            });
        };
        $scope.model = {};

        //adicionar instancia de nomenclador
        $scope.adicionarInstancia = function (ev) {
            var parentEl = angular.element(document.body);
            var plantilla = '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Adicionar instancia de nomenclador</h3>' +
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
                '</md-input-container>'

            function eliminarCaracterInvalidos(palabra) {
                var nueva = '';
                for (var i = 0; i < palabra.length; i++) {
                    if (palabra.charAt(i) != " ") {
                        if(palabra.charAt(i) == "é"){
                            nueva += 'e';
                        }else if(palabra.charAt(i) == "í"){
                            nueva += 'i';
                        }else if(palabra.charAt(i) == "á"){
                            nueva += 'a';
                        }else if(palabra.charAt(i) == "ó"){
                            nueva += 'o';
                        }else if(palabra.charAt(i) == "ú"){
                            nueva += 'u';
                        }else if(palabra.charAt(i) == "ñ"){
                            nueva += 'n';
                        }else{
                            nueva += palabra.charAt(i);
                        }
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
                    if ($scope.listaCampos.data[i].nomencladorVin == 0) {
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
               console.log($scope.comboIns);
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
                    '<span ng-show="instancia.' + nuevoNombre + '.$error.pattern">El campo debe ser una fecha.</span>' +
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
                    comboIns: $scope.comboIns
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, campos, nombre,comboIns, idAux, toastr) {

                function obtenerIdCampo(nombre) {
                    for (var i = 0; i < campos.length; i++) {
                        if (campos[i].nombre == nombre) {
                            return campos[i].id;
                        }
                    }
                    return -1;
                }

                $scope.comboIns = comboIns;

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
                        camposnom: $scope.nombInst,
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
                        console.log(valorCam);
                        data.valores.push({id: obtenerIdCampo(campos[i].nombre), valor: valorCam})
                    }
                    console.log(data);
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_nomenclador_instancia_create', {idn: idAux}, true),
                        data: data
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

                        console.log(response.data);
                        $mdDialog.hide();
                        getInstNom(idAux);
                    }, function (response) {
                        "Request failed";
                    });
                };

                $scope.aplicar = function () {

                    var data = {
                        camposnom: $scope.nombInst,
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
                        console.log(valorCam);
                        data.valores.push({id: obtenerIdCampo(campos[i].nombre), valor: valorCam})
                    }
                    console.log(data);
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_nomenclador_instancia_create', {idn: idAux}, true),
                        data: data
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
                        console.log(response.data);

                        for (var i = 0; i < campos.length; i++) {
                            var nuevo = eliminarCaracterInvalidos(campos[i].nombre.toLowerCase());
                            $scope.model[nuevo] = null;
                        }
                        $scope.nombInst = null;
                        $scope.disab = false;
                        getInstNom(idAux);
                    }, function (response) {
                        "Request failed";
                    });
                };
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
        //filtar de instancia de nomenclador por nombre
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

            getInstNom($scope.idAux);
        });

        //eliminar instancia de nomenclador
        $scope.eliminarInstancia = function (selected, ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Eliminar instancia de nomenclador</h3>' +
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
                    id: $scope.idAux
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, id, consulta, toastr) {

                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.delete = function () {
                    for (var i = 0; i < selected.length; i++) {
                        var index = getSelectedIndex(selected[i].IDNOP);
                        console.log(selected);
                        console.log(selected.length);
                        items.splice(index, 1);
                        $http({
                            method: 'DELETE',
                            url: Routing.generate('eyc_nomenclador_instancia_delete', {idnop: selected[i].IDNOP}, true)
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
                    getInstNom(id);
                };
            }
        };

        //editar instancia de nomenclador
        $scope.editarInstancia = function (selected, ev) {
            var parentEl = angular.element(document.body);
            var plantilla = '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Modificar instancia de nomenclador</h3>' +
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

            function eliminarCaracterInvalidos(palabra) {
                var nueva = '';
                for (var i = 0; i < palabra.length; i++) {
                    if (palabra.charAt(i) != " ") {
                        if(palabra.charAt(i) == "é"){
                            nueva += 'e';
                        }else if(palabra.charAt(i) == "í"){
                            nueva += 'i';
                        }else if(palabra.charAt(i) == "á"){
                            nueva += 'a';
                        }else if(palabra.charAt(i) == "ó"){
                            nueva += 'o';
                        }else if(palabra.charAt(i) == "ú"){
                            nueva += 'u';
                        }else if(palabra.charAt(i) == "ñ"){
                            nueva += 'n';
                        }else{
                            nueva += palabra.charAt(i);
                        }
                    }
                }
                return nueva;
            };
            for (var i = 0; i < $scope.listaCampos.data.length; i++) {
                var nuevoNombre = eliminarCaracterInvalidos($scope.listaCampos.data[i].nombre.toLowerCase());
                if ($scope.listaCampos.data[i].tipoDato === 'bool') {
                    plantilla += '<md-checkbox id="id' + (i + 1) + '" class="md-primary" ng-model="model.' + nuevoNombre + '">' +
                    $scope.listaCampos.data[i].nombre + '</md-checkbox>' +
                    '<div ng-messages="errors.nuevoNombre">' +
                    '<div ng-repeat="message in errors.nuevoNombre" ng-bind="message"></div>' +
                    '</div>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'integer') {
                    if ($scope.listaCampos.data[i].nomencladorVin == 0) {
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
                    '<input id="id' + (i + 1) + '" ng-model="model.' + nuevoNombre + '" required="required" maxlength="255">' +
                    '<div ng-messages="errors.nuevoNombre">' +
                    '<div ng-repeat="message in errors.nuevoNombre" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'date') {
                    plantilla += '<label>' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<md-datepicker ng-required="true" md-placeholder="Entre la fecha" id="id' + (i + 1) + '" ng-model="model.' + nuevoNombre + '">' + '</md-datepicker>' +
                    '<div ng-messages="errors.nuevoNombre">' +
                    '<div ng-repeat="message in errors.nuevoNombre" ng-bind="message"></div>' +
                    '</div>';
                } else if ($scope.listaCampos.data[i].tipoDato === 'double') {
                    plantilla += '<md-input-container class="md-block">' +
                    '<label class="required">' + $scope.listaCampos.data[i].nombre + '</label>' +
                    '<input id="id' + (i + 1) + '" ng-model="model.' + nuevoNombre + '" required="required" ng-pattern="/^-?[0-9]+([.][0-9]+)?$/">' +
                    '<div ng-messages="errors.nuevoNombre">' +
                    '<div ng-repeat="message in errors.nuevoNombre" ng-bind="message"></div>' +
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
                    nombre: $scope.nombAux,
                    comboIns: $scope.comboIns,
                    idAux: $scope.idAux
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, campos, nombre,comboIns, idAux, toastr) {
                console.log(selected);
                $scope.model = {};
                $scope.disab = false;
                $scope.nombInst = selected[0].NomNOP;
                $scope.des = function () {
                    $scope.disab = true;
                };
                $scope.comboIns = comboIns;

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
                    if (eliminarCaracterInvalidos(selected[0].valores[i]) == undefined || eliminarCaracterInvalidos(selected[0].valores[i]) == "") {
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
                            console.log("aux:" + aux);
                            var aux2 = aux[0] + '/' + aux[1] + '/' + (parseInt(aux[2]));
                            var fecha = new Date(aux2);
                            console.log(fecha);
                            $scope.model[nuevo] = fecha;
                        } else if (campos[i].tipoDato === 'integer') {
                            $scope.model[nuevo] = parseInt(selected[0].valores[i]);
                        } else if (campos[i].tipoDato === 'double') {
                            $scope.model[nuevo] = parseFloat(selected[0].valores[i]);
                        } else if (campos[i].tipoDato === 'bool') {
                            if (selected[0].valores[i] == 'true') {
                                $scope.model[nuevo] = true;
                            } else {
                                $scope.model[nuevo] = false;
                            }
                        } else {
                            $scope.model[nuevo] = selected[0].valores[i];
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
                        camposnom: $scope.nombInst,
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
                    console.log(data);
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_nomenclador_instancia_update', {idnop: selected[0].IDNOP}, true),
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

                        //console.log("Response: " + codigo);
                        //$http({
                        //    method: 'GET', url: Routing.generate('eyc_nomenclador_instancias',{id:idAux},true)
                        //}).then(function (response) {
                        //    var auxiliar = '404 GET: El recurso solicitado con el identificador ' + idAux + ' no existe.';
                        //
                        //    function completarCamposVacios(longVal, longCam, valoresIns) {
                        //        var cant = longCam - longVal;
                        //        var agregar = "";
                        //        for (var i = 0; i < valoresIns.valores.length; i++) {
                        //            for (var j = 0; j < cant; j++) {
                        //                valoresIns.valores[i].valor.push(agregar);
                        //                agregar += " ";
                        //            }
                        //        }
                        //        return valoresIns;
                        //    };
                        //    function organizarInstancias(id) {
                        //        var instancias = {
                        //            idI: id,
                        //            valor: []
                        //        };
                        //        for (var i = 0; i < datosIns.length; i++) {
                        //            if (datosIns[i].IDNOP == id) {
                        //                instancias.valor.push(datosIns[i].valor);
                        //            }
                        //        }
                        //        return instancias;
                        //    };
                        //
                        //    function buscarIDsIns() {
                        //        var ids = [];
                        //        for (var i = 0; i < datosIns.length; i++) {
                        //            if (ids.indexOf(datosIns[i].IDNOP) == -1) {
                        //                ids.push(datosIns[i].IDNOP);
                        //            }
                        //        }
                        //        return ids;
                        //    };
                        //
                        //    if (response.data != auxiliar) {
                        //        $scope.datos = response.data;
                        //        var datosIns = $scope.datos.valores;
                        //        var ids = buscarIDsIns();
                        //        var valoresIns = {
                        //            valores: []
                        //        };
                        //        for (var i = 0; i < ids.length; i++) {
                        //            valoresIns.valores.push(organizarInstancias(ids[i]));
                        //        }
                        //        var valoresInsNuevo = completarCamposVacios(valoresIns.valores[0].valor.length, campos.length, valoresIns);
                        //
                        //        $scope.listaInstancia = valoresInsNuevo;
                        //
                        //        var index = getSelectedIndex(selected[0].idI);
                        //        console.log(index);
                        //        console.log(items.valores);
                        //        console.log(items.valores[index]);
                        //        for (var i = 0; i < $scope.listaInstancia.valores[index].valor.length; i++) {
                        //            items.valores[index].valor[i] = $scope.listaInstancia.valores[index].valor[i];
                        //        }
                        //    }
                        //}, function (response) {
                        //    "Request failed";
                        //});
                    }, function (response) {
                        "Request failed";
                    });
                    //var datosIns = $scope.listaInstancia.valores;
                    //
                    //function organizarInstancias(id) {
                    //    var instancias = {
                    //        idI: id,
                    //        valor: []
                    //    };
                    //    for (var i = 0; i < datosIns.length; i++) {
                    //        if (datosIns[i].IDNOP == id) {
                    //            instancias.valor.push(datosIns[i].valor);
                    //        }
                    //    }
                    //    return instancias;
                    //};
                    //
                    //function buscarIDsIns() {
                    //    var ids = [];
                    //    for (var i = 0; i < datosIns.length; i++) {
                    //        if (ids.indexOf(datosIns[i].IDNOP) == -1) {
                    //            ids.push(datosIns[i].IDNOP);
                    //        }
                    //    }
                    //    return ids;
                    //};
                    //getInstNom(idAux);
                    //var datosIns = $scope.datos.valores;
                    //var ids = buscarIDsIns();
                    //var valoresIns = {
                    //    valores: []
                    //};
                    //for (var i = 0; i < ids.length; i++) {
                    //    valoresIns.valores.push(organizarInstancias(ids[i]));
                    //}
                    //var valoresInsNuevo = completarCamposVacios(valoresIns.valores[0].valor.length, campos.length, valoresIns);
                    //
                    //$scope.listaInstancia = valoresInsNuevo;
                    //
                    //var index = getSelectedIndex(selected[0].idI);
                    ////console.log(index);
                    ////console.log(items.valores);
                    ////console.log(items.valores[index]);
                    //for (var i = 0; i < $scope.listaInstancia.valores[index].valor.length; i++) {
                    //    items.valores[index].valor[i] = $scope.listaInstancia.valores[index].valor[i];
                    //}
                    $mdDialog.hide();
                    getInstNom(idAux);
                };
            }
        };

        function getSelectedIndex(id) {
            for (var i = 0; i < $scope.listaInstancia.valores.length; i++) {
                if ($scope.listaInstancia.valores[i].IDNOP === id) {
                    return i;
                }
            }
            return -1;
        };

    }
    ]
)
;