angular.module('app')
    .controller('eycNivelEstructuralCtrl',
    ['$http', '$mdEditDialog', '$q', '$timeout', 'eycEstructuraSvc', 'eycEstructuraRelacionesSvc', '$scope', '$mdDialog',
        '$mdMedia', function ($http, $mdEditDialog, $q, $timeout, eycEstructuraSvc, eycEstructuraRelacionesSvc, $scope, $mdDialog, $mdMedia) {
        'use strict';

        $scope.selected = [];

        $scope.query = {
            filter: '',
            order: 'id',
            limit: 5,
            page: 1
        };

        $scope.selectedRel = [];

        $scope.selectedCampo = [];

        $scope.query2 = {
            order: 'id',
            limit: 5,
            page: 1
        };

        $scope.query3 = {
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
        }, {
            name: 'Raiz',
            orderBy: 'raiz'
        }];

        //listar nomencladores
        $scope.nomencladores = [];
        $http({
            method: 'GET', url: Routing.generate('eyc_nomenclador_list', {}, true)
        }).then(function (response) {
            $scope.nomencladores = response.data.data;
        }, function (response) {
            $scope.nomencladores = response.data || "Request failed";
        });

        $scope.deselect = function (item) {
            $scope.relaciones = [];
            $scope.relaciones.count = 0;
            if ($scope.selected.length == 1) {
                $scope.visRel = {
                    visibility: 'visible'
                };

                var idE = $scope.selected[0].id;
                getHijosEst(idE);

            } else {
                $scope.visRel = {
                    visibility: 'hidden'
                };
            }

            //eliminar relacion entre estructuras
            $scope.eliminarRelacion = function (selectedRel, ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: ev,
                    clickOutsideToClose: false,
                    template: '<md-toolbar class="md-panel-toolbar">' +
                    '<div class="md-toolbar-tools">' +
                    '<h3>Eliminar relación</h3>' +
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
                    '<p>¿Está seguro que desea eliminar la relación entre estas estructuras?</p>' +
                    '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-click="delete()">Aceptar</md-button>' +
                    '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</md-content>',
                    locals: {
                        items: $scope.relaciones.data,
                        sel: $scope.selected,
                        consulta: $scope.query2,
                        count: $scope.relaciones
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, items, consulta, count, sel, toastr) {

                    $scope.cancel = function () {
                        $mdDialog.cancel();
                        $mdDialog.hide();
                    };
                    $scope.cerrar = function () {
                        $mdDialog.hide();
                    };
                    $scope.delete = function () {
                        for (var i = 0; i < selectedRel.length; i++) {
                            var idR = selectedRel[i].id;

                            var data2 = {
                                _method: "DELETE",
                                hija: idR
                            };
                            $http({
                                method: 'POST',
                                url: Routing.generate('eyc_estructura_delete_hijas', {id_padre: sel[0].id}, true),
                                data: data2
                            }).then(function (response) {
                                var texto = response.data;
                                var codigo = texto.substring(0, 3);
                                console.log("Response: " + codigo);
                                if (codigo == "200") {
                                    toastr.success(texto.substring(11));
                                } else if (codigo == '201') {
                                    toastr.success(texto.substring(11));
                                } else {
                                    toastr.error(texto.substring(11));
                                }
                                var index = getSelectedRelIndex(idR);
                                items.splice(index, 1);
                                'Completado'
                            }, function (response) {
                                "Request failed";
                            });
                        }

                        selectedRel.length = 0;
                        consulta.page = 1;
                        count = [];
                        count.count = 0;
                        $mdDialog.hide();

                        getHijosEst(sel[0].id)
                        function getSelectedRelIndex(id) {
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
            $scope.estructuras = [];

            //listar estructuras sin filtro
            $http({
                method: 'GET', url: Routing.generate('eyc_estructura_list2', {}, true)
            }).then(function (response) {
                $scope.estructuras = response.data;
            }, function (response) {
                $scope.estructuras = response.data || "Request failed";
            });

            //crear relacion entre estructuras
            $scope.crearRelacion = function (ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: ev,
                    clickOutsideToClose: false,
                    template: '<md-toolbar class="md-panel-toolbar">' +
                    '<div class="md-toolbar-tools">' +
                    '<h3>Crear relación entre estructuras</h3>' +
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
                    '<form name="estructura" method="post" action="">' +
                    '<md-input-container class="md-block">' +
                    '<label class="required">Listado de estructuras</label>' +
                    '<md-select required="required" ng-model="idR" >' +
                    '<md-option ng-repeat="estructura in listadoEstructura" required="required" value="{{estructura.id}}">' +
                    '{{estructura.nombre}}' +
                    '</md-option>' +
                    '</md-select>' +
                    '<div ng-messages="errors.tipoCampo">' +
                    '<div ng-repeat="message in errors.tipoCampo" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>' +
                    '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-disabled="!estructura.$valid" ng-click="crear()">Crear</md-button>' +
                    '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</md-content>',
                    locals: {
                        items: $scope.estructuras.data,
                        rel: $scope.relaciones.data,
                        sel: $scope.selected
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, toastr, items, sel, rel) {
                    var aux = [];

                    function getSelectedRelIndex(id) {
                        for (var i = 0; i < rel[0].estructurasHijas.length; i++) {
                            if (rel[0].estructurasHijas[i].id === id) {
                                return i;
                            }
                        }
                        return -1;
                    };
                    console.log(rel);
                    if (rel != undefined) {
                        for (var i = 0; i < items.length; i++) {
                            if (!items[i].raiz) {
                                var id = items[i].id;
                                if (getSelectedRelIndex(id) == -1 && id != sel[0].id) {
                                    aux.push(items[i]);
                                }
                            }
                        }
                    } else {
                        for (var i = 0; i < items.length; i++) {
                            if (!items[i].raiz) {
                                var id = items[i].id;
                                if (id != sel[0].id) {
                                    aux.push(items[i]);
                                }
                            }
                        }
                    }
                    $scope.listadoEstructura = aux;

                    $scope.cancel = function () {
                        $mdDialog.cancel();
                        $mdDialog.hide();
                    };
                    $scope.cerrar = function () {
                        $mdDialog.hide();
                    };
                    $scope.crear = function () {
                        var idE = sel[0].id;
                        var data = {
                            idR: $scope.idR
                        };
                        $http({
                            method: 'POST',
                            url: Routing.generate('eyc_estructura_add_hijas', {id: idE}, true),
                            data: data
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            console.log("Response: " + codigo);
                            $mdDialog.hide();
                            getHijosEst(sel[0].id)
                            if (codigo == "200") {
                                toastr.success(texto.substring(10));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(10));
                            } else {
                                toastr.error(texto.substring(10));
                            }
                            //$http({
                            //    method: 'GET', url: Routing.generate('eyc_estructura_buscar_estructura',{id:$scope.idR},true)
                            //}).then(function (response) {
                            //    rel.push({
                            //        id: response.data[0].id,
                            //        nombre: response.data[0].nombre
                            //    });
                            //
                            //}, function (response) {
                            //    "Request failed";
                            //});
                        }, function (response) {
                            "Request failed";
                        });
                    };
                }
            };
            console.log(item.nombre, 'was deselected');
        };

        $scope.deselect2 = function (item) {
            console.log(item.nombre, 'was deselected');
        };

        $scope.visRel = {
            visibility: 'hidden'
        };

        $scope.log2 = function (item) {
            console.log(item.nombre, 'was selected');
        }

        //paginar relaciones de estructuras
        $scope.onPaginate2 = function (page, limit) {
            getHijosEst($scope.selected[0].id, angular.extend({}, $scope.query2, {page: page, limit: limit}));
        };
        //ordenar relaciones de estructuras
        $scope.onReorder2 = function (order) {
            getHijosEst($scope.selected[0].id, angular.extend({}, $scope.query2, {order: order}));
        };
        //listar relaciones de estructuras
        function getHijosEst(id, query) {
            $scope.relaciones = [];
            $scope.relaciones.count = 0;
            $scope.promise2 = eycEstructuraRelacionesSvc.getHijos(id).get(query || $scope.query2, success2).$promise;
        };

        function success2(relaciones) {
            $scope.relaciones = relaciones;
            $scope.selectedRel = [];
        };

        //$scope.relaciones = [];
        $scope.log = function (item) {
            $scope.relaciones = [];
            $scope.relaciones.count = 0;
            if ($scope.selected.length == 1) {
                $scope.visRel = {
                    visibility: 'visible'
                };

                var idE = $scope.selected[0].id;
                getHijosEst(idE);
            } else {
                $scope.visRel = {
                    visibility: 'hidden'
                };
            }
            //eliminar relaciones de estructuras
            $scope.eliminarRelacion = function (selectedRel, ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: ev,
                    clickOutsideToClose: false,
                    template: '<md-toolbar class="md-panel-toolbar">' +
                    '<div class="md-toolbar-tools">' +
                    '<h3>Eliminar relación</h3>' +
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
                    '<p>¿Esta seguro que desea eliminar la relación entre estas estructuras?</p>' +
                    '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-click="delete()">Aceptar</md-button>' +
                    '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</md-content>',
                    locals: {
                        items: $scope.relaciones.data,
                        sel: $scope.selected,
                        consulta: $scope.query2,
                        count: $scope.relaciones
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, items, consulta, count, sel, toastr) {

                    $scope.cancel = function () {
                        $mdDialog.cancel();
                        $mdDialog.hide();
                    };
                    $scope.cerrar = function () {
                        $mdDialog.hide();
                    };
                    $scope.delete = function () {
                        for (var i = 0; i < selectedRel.length; i++) {
                            var idR = selectedRel[i].id;

                            var data2 = {
                                _method: "DELETE",
                                hija: idR
                            };
                            $http({
                                method: 'POST',
                                url: Routing.generate('eyc_estructura_delete_hijas', {id_padre: sel[0].id}, true),
                                data: data2
                            }).then(function (response) {
                                var texto = response.data;
                                var codigo = texto.substring(0, 3);
                                console.log("Response: " + codigo);
                                if (codigo == "200") {
                                    toastr.success(texto.substring(11));
                                } else if (codigo == '201') {
                                    toastr.success(texto.substring(11));
                                } else {
                                    toastr.error(texto.substring(11));
                                }
                                var index = getSelectedRelIndex(idR);
                                items.splice(index, 1);
                                'Completado'
                            }, function (response) {
                                "Request failed";
                            });
                        }

                        selectedRel.length = 0;
                        consulta.page = 1;
                        count = [];
                        count.count = 0;
                        $mdDialog.hide();

                        getHijosEst(sel[0].id)
                        function getSelectedRelIndex(id) {
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
            $scope.estructuras = [];
            //listar estructuras sin filtro
            $http({
                method: 'GET', url: Routing.generate('eyc_estructura_list2', {}, true)
            }).then(function (response) {
                $scope.estructuras = response.data;
            }, function (response) {
                $scope.estructuras = response.data || "Request failed";
            });
            //crear relaciones de estructuras
            $scope.crearRelacion = function (ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: ev,
                    clickOutsideToClose: false,
                    template: '<md-toolbar class="md-panel-toolbar">' +
                    '<div class="md-toolbar-tools">' +
                    '<h3>Crear relación entre estructuras</h3>' +
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
                    '<form name="estructura" method="post" action="">' +
                    '<md-input-container class="md-block">' +
                    '<label class="required">Listado de estructuras</label>' +
                    '<md-select required="required" ng-model="idR" >' +
                    '<md-option ng-repeat="estructura in listadoEstructura" required="required" value="{{estructura.id}}">' +
                    '{{estructura.nombre}}' +
                    '</md-option>' +
                    '</md-select>' +
                    '<div ng-messages="errors.tipoCampo">' +
                    '<div ng-repeat="message in errors.tipoCampo" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>' +
                    '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-disabled="!estructura.$valid" ng-click="crear()">Crear</md-button>' +
                    '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</md-content>',
                    locals: {
                        items: $scope.estructuras.data,
                        rel: $scope.relaciones.data,
                        sel: $scope.selected
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, toastr, items, sel, rel) {
                    var aux = [];

                    function getSelectedRelIndex(id) {
                        for (var i = 0; i < rel[0].estructurasHijas.length; i++) {
                            if (rel[0].estructurasHijas[i].id === id) {
                                return i;
                            }
                        }
                        return -1;
                    };
                    console.log(rel);
                    if (rel != undefined) {
                        for (var i = 0; i < items.length; i++) {
                            if (!items[i].raiz) {
                                var id = items[i].id;
                                if (getSelectedRelIndex(id) == -1 && id != sel[0].id) {
                                    aux.push(items[i]);
                                }
                            }
                        }
                    } else {
                        for (var i = 0; i < items.length; i++) {
                            if (!items[i].raiz) {
                                var id = items[i].id;
                                if (id != sel[0].id) {
                                    aux.push(items[i]);
                                }
                            }
                        }
                    }
                    $scope.listadoEstructura = aux;

                    $scope.cancel = function () {
                        $mdDialog.cancel();
                        $mdDialog.hide();
                    };
                    $scope.cerrar = function () {
                        $mdDialog.hide();
                    };
                    $scope.crear = function () {
                        var idE = sel[0].id;
                        var data = {
                            idR: $scope.idR
                        };
                        $http({
                            method: 'POST',
                            url: Routing.generate('eyc_estructura_add_hijas', {id: idE}, true),
                            data: data
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            console.log("Response: " + codigo);
                            $mdDialog.hide();
                            getHijosEst(sel[0].id)
                            if (codigo == "200") {
                                toastr.success(texto.substring(10));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(10));
                            } else {
                                toastr.error(texto.substring(10));
                            }
                            //$http({
                            //    method: 'GET', url: Routing.generate('eyc_estructura_buscar_estructura',{id:$scope.idR},true)
                            //}).then(function (response) {
                            //    rel.push({
                            //        id: response.data[0].id,
                            //        nombre: response.data[0].nombre
                            //    });
                            //
                            //}, function (response) {
                            //    "Request failed";
                            //});
                        }, function (response) {
                            "Request failed";
                        });
                    };
                }
            };
            console.log(item.nombre, 'was selected');
        };

        $scope.loadStuff = function () {
            /*
             $scope.promise = $timeout(function () {

             }, 2000);
             */
        };

        $scope.filter = {
            options: {
                debounce: 500
            }
        };
        var bookmark;

        //listar estructuras
        function getEntities(query) {
            $scope.promise = eycEstructuraSvc.listaEstructuras.get(query || $scope.query, success).$promise;
        }

        //getEntities();

        function success(listaEstructuras) {
            $scope.listaEstructuras = listaEstructuras;
            $scope.selected = [];
        }
        //paginar estructuras
        $scope.onPaginate = function (page, limit) {
            getEntities(angular.extend({}, $scope.query, {page: page, limit: limit}));
        };
        //ordenar estructuras
        $scope.onReorder = function (order) {
            getEntities(angular.extend({}, $scope.query, {order: order}));
        };

        $scope.removeFilter = function () {
            $scope.filter.show = false;
            $scope.query.filter = '';

            if ($scope.filter.form.$dirty) {
                $scope.filter.form.$setPristine();
            }
        };
        //filtrar estructuras por nombre
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

            getEntities();
        });

        //$scope.listaEstructuras = [];
        //$http({
        //    method: 'GET', url: Routing.generate('eyc_estructura_list',{},true)
        //}).then(function (response) {
        //    $scope.listaEstructuras = response.data;
        //}, function (response) {
        //    $scope.listaEstructuras = response.data || "Request failed";
        //});

        //adicionar estructuras
        $scope.adicionarEstructura = function (ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Adicionar estructura</h3>' +
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
                '<form name="estructura" method="post" action="">' +
                '<md-input-container class="md-block">' +
                '<label class="required">*Nombre</label>' +
                '<input type="text" name="nombre" ng-model="nombreEstructura" required="required" maxlength="255" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/"/>' +
                '<span class="messages" ng-show="estructura.nombre.$dirty">' +
                '<span ng-show="estructura.nombre.$error.required">El campo es obligatorio.</span>' +
                '<span ng-show="estructura.nombre.$error.pattern">Formato de nombre incorrecto.</span>' +
                '</span>' +
                '<div ng-messages="errors.nombreEstructura">' +
                '<div ng-repeat="message in errors.nombreCampo" ng-bind="message"></div>' +
                '</div>' +
                '</md-input-container>' +
                '<md-input-container class="md-block">' +
                '<md-checkbox ng-model="raizEstructura" class="md-primary" aria-label="Raíz">' +
                'Raíz' +
                '</md-checkbox>' +
                '</md-input-container>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-disabled="!estructura.$valid || disab ||estructura.nombre.$error.pattern" ng-click="add(); des();">Adicionar</md-button>' +
                '<md-button class="md-raised" ng-disabled="!estructura.$valid || disab ||estructura.nombre.$error.pattern" ng-click="aplicar(); des();">Aplicar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.listaEstructuras.data
                },
                controller: DialogController
            });
            function DialogController($scope, $mdDialog, items, toastr) {
                $scope.disab = false;
                $scope.raizEstructura = false;
                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.des = function () {
                    console.log($scope.disab);
                    $scope.disab = true;
                    console.log($scope.disab);
                };
                $scope.cerrar = function () {
                    $mdDialog.hide();
                };
                $scope.add = function () {

                    var data = {
                        nombre: $scope.nombreEstructura,
                        raiz: $scope.raizEstructura
                    };
                    $http({
                        method: 'POST', url: Routing.generate('eyc_estructura_create', {}, true), data: data
                    }).then(function (response) {

                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        console.log("Response: " + codigo);
                        if (codigo == "200") {
                            toastr.success(texto.substring(10));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(10));
                        } else {
                            toastr.error(texto.substring(10));
                        }
                        //$http({
                        //    method: 'GET', url: Routing.generate('eyc_estructura_list',{},true)
                        //}).then(function (response) {
                        //    $scope.idEstructuras = response.data[response.data.length - 1].id;
                        //    items.push({
                        //        id: $scope.idEstructuras,
                        //        nombre: $scope.nombreEstructura,
                        //        raiz: $scope.raizEstructura
                        //    });
                        //
                        //}, function (response) {
                        //    "Request failed";
                        //});
                        $mdDialog.hide();
                        getEntities();
                    }, function (response) {
                        "Request failed";
                    });
                };
                $scope.aplicar = function () {

                    var data = {
                        nombre: $scope.nombreEstructura,
                        raiz: $scope.raizEstructura
                    };
                    $http({
                        method: 'POST', url: Routing.generate('eyc_estructura_create', {}, true), data: data
                    }).then(function (response) {
                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        console.log("Response: " + codigo);
                        if (codigo == "200") {
                            toastr.success(texto.substring(10));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(10));
                        } else {
                            toastr.error(texto.substring(10));
                        }
                        //$http({
                        //    method: 'GET', url: Routing.generate('eyc_estructura_list',{},true)
                        //}).then(function (response) {
                        //    $scope.idEstructuras = response.data[response.data.length - 1].id;
                        //    items.push({
                        //        id: $scope.idEstructuras,
                        //        nombre: $scope.nombreEstructura,
                        //        raiz: $scope.raizEstructura
                        //    });
                        //    $scope.nombreEstructura = "";
                        //    $scope.raizEstructura = false;
                        //    $scope.disab = false;
                        //}, function (response) {
                        //    "Request failed";
                        //});

                        $scope.nombreEstructura = "";
                        $scope.raizEstructura = false;
                        $scope.disab = false;
                        getEntities();
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };
        //eliminar estructuras
        $scope.eliminarEstructura = function (selected, ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Eliminar estructura</h3>' +
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
                '<p ng-if="!elimRel">¿Está seguro que desea eliminar el(los) elemento(s) seleccionado(s)?¿Está seguro que desea eliminar el(los) elemento(s) seleccionado(s)?</p>' +
                '<p ng-if="elimRel">¿Está seguro que desea eliminar la(las) estructura(s) y las relaciones de la misma?</p>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-click="delete(); des();">Aceptar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.listaEstructuras.data,
                    relaciones: $scope.relaciones,
                    visib: $scope.visRel,
                    consulta: $scope.query,
                    sel: $scope.selected
                },
                controller: DialogController
            }).then(getEntities);
            function DialogController($scope, $mdDialog, relaciones, items, visib, sel, consulta, toastr) {

                $scope.disab = false;

                if(relaciones.data != undefined){
                    $scope.elimRel = true;
                }else{
                    $scope.elimRel = false;
                }

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
                    visib = {
                        visibility: 'hidden'
                    };
                    for (var i = 0; i < selected.length; i++) {
                        var index = getSelectedIndex(selected[i].id);
                        items.splice(index, 1);
                        relaciones = [];
                        relaciones.count = 0;
                        $http({
                            method: 'DELETE', url: Routing.generate('eyc_estructura_delete', {id: selected[i].id}, true)
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            console.log("Response: " + codigo);
                            if (codigo == "200") {
                                toastr.success(texto.substring(11));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(11));
                            } else {
                                toastr.error(texto.substring(11));
                            }
                            getHijosEst(-1);
                            'Completado'
                        }, function (response) {
                            "Request failed";
                        });
                    }
                    ponerOculto();
                    selected.length = 0;
                    consulta.page = 1;

                    $mdDialog.hide();
                    function getSelectedIndex(id) {
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

        function ponerOculto() {
            $scope.visRel = {
                visibility: 'hidden'
            };
        }
        //editar estructuras
        $scope.editarEstructura = function (selected, ev) {
            var parentEl = angular.element(document.body);
            $mdDialog.show({
                parent: parentEl,
                targetEvent: ev,
                clickOutsideToClose: false,
                template: '<md-toolbar class="md-panel-toolbar">' +
                '<div class="md-toolbar-tools">' +
                '<h3>Modificar estructura</h3>' +
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
                '<form name="estructura" method="post" action="">' +
                '<md-input-container class="md-block">' +
                '<label class="required">*Nombre</label>' +
                '<input type="text" name="nombre" ng-model="nombreEstructura" required="required" maxlength="255" ng-pattern="/^[A-Za-záéíóúñÑÁÉÍÓÚ]+([A-Za-z0-9áéíóúñÑÁÉÍÓÚ ]*)$/"/>' +
                '<span class="messages" ng-show="estructura.nombre.$dirty">' +
                '<span ng-show="estructura.nombre.$error.required">El campo es obligatorio.</span>' +
                '<span ng-show="estructura.nombre.$error.pattern">Formato de nombre incorrecto.</span>' +
                '</span>' +
                '<div ng-messages="errors.nombreEstructura">' +
                '<div ng-repeat="message in errors.nombreCampo" ng-bind="message"></div>' +
                '</div>' +
                '</md-input-container>' +
                '<md-input-container class="md-block">' +
                '<md-checkbox ng-model="raizEstructura" class="md-primary" aria-label="Raíz">' +
                'Raíz' +
                '</md-checkbox>' +
                '</md-input-container>' +
                '<div class="md-actions">' +
                '<md-button class="md-primary md-raised" ng-disabled="!estructura.$valid || disab ||estructura.nombre.$error.pattern" ng-click="modificar(); des();">Aceptar</md-button>' +
                '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                '</div>' +
                '</form>' +
                '</div>' +
                '</md-content>',
                locals: {
                    items: $scope.listaEstructuras.data
                },
                controller: DialogController
            }).then(getEntities);
            function DialogController($scope, $mdDialog, items, toastr) {
                $scope.idEstructura = selected[selected.length - 1].id;
                $scope.nombreEstructura = selected[selected.length - 1].nombre;
                $scope.raizEstructura = selected[selected.length - 1].raiz;
                $scope.disab = false;

                $scope.cerrar = function () {
                    $mdDialog.hide();
                };

                $scope.des = function () {
                    $scope.disab = true;
                };
                $scope.cancel = function () {
                    $mdDialog.cancel();
                    $mdDialog.hide();
                };
                $scope.modificar = function () {
                    var data = {
                        _method: 'PUT',
                        nombre: $scope.nombreEstructura,
                        raiz: $scope.raizEstructura
                    };
                    $http({
                        method: 'POST',
                        url: Routing.generate('eyc_estructura_update', {id: $scope.idEstructura}, true),
                        data: data
                    }).then(function (response) {
                        var texto = response.data;
                        var codigo = texto.substring(0, 3);
                        console.log("Response: " + codigo);
                        if (codigo == "200") {
                            toastr.success(texto.substring(9));
                        } else if (codigo == '201') {
                            toastr.success(texto.substring(9));
                        } else {
                            toastr.error(texto.substring(9));
                        }
                        for (var i = 0; i < items.length; i++) {
                            if (items[i].id === selected[selected.length - 1].id) {
                                items[i].nombre = $scope.nombreEstructura;
                                items[i].raiz = $scope.raizEstructura;
                                $mdDialog.hide();
                            }
                        }
                    }, function (response) {
                        "Request failed";
                    });
                };
            }
        };

        function getSelectedIndex(id) {
            for (var i = 0; i < $scope.listaEstructuras.length; i++) {
                if ($scope.listaEstructuras[i].id === id) {
                    return i;
                }
            }
            return -1;
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
        $scope.aux2 = "";
        $scope.mostrarCapa2 = function (selected) {
            //$scope.listaCampos = [];
            $scope.aux = $scope.oculto;
            $scope.aux2 = $scope.visRel;
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
            $scope.visRel = {
                visibility: 'hidden'
            };
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
            $scope.idEstructura = selected[0].id;
            //$scope.listaCampos = [];

            //$http({
            //    method: 'GET', url: Routing.generate('eyc_estructura_campos',{ide:$scope.idEstructura },true)
            //}).then(function (response) {
            //    var auxiliar = '404 GET: El recurso solicitado con el identificador ' + $scope.idEstructura + ' no posee campos asociados.';
            //
            //    if (response.data != auxiliar) {
            //        $scope.listaCampos = response.data;
            //    }
            //}, function (response) {
            //    "Request failed";
            //});

            //listar campos de estructuras
            function getCamposEst(ide, query) {
                $scope.promise3 = eycEstructuraRelacionesSvc.getCampos(ide).get(query || $scope.query3, success3).$promise;
            };

            function success3(campos) {
                $scope.listaCampos = campos;
                $scope.selectedCampo = [];
            };

            //ordenar campos de estructuras
            $scope.onReorder3 = function (order) {
                getCamposEst($scope.selected[0].id, angular.extend({}, $scope.query3, {order: order}));
            };
            //paginar campos de estructuras
            $scope.onPaginate3 = function (page, limit) {
                getCamposEst($scope.selected[0].id, angular.extend({}, $scope.query3, {page: page, limit: limit}));
            };

            getCamposEst($scope.selected[0].id);
            //adicionar campos de estructuras
            $scope.adicionarCampo = function (ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: ev,
                    clickOutsideToClose: false,
                    template: '<md-toolbar class="md-panel-toolbar">' +
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
                    '<md-select ng-disabled="checkDisab" ng-model="nomVinCampo" >' +
                    '<md-option ng-repeat="id in listaIDS track by $index " value="{{id.id}}">' +
                    '{{id.nombre}}' +
                    '</md-option>' +
                    '</md-select>' +
                    '<div ng-messages="errors.nomVinCampo">' +
                    '<div ng-repeat="message in errors.nomVinCampo" ng-bind="message"></div>' +
                    '</div>' +
                    '</md-input-container>' +
                    '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-disabled="!campo.$valid || disab ||campo.nombre.$error.pattern" ng-click="add(); des();">Aceptar</md-button>' +
                    '<md-button class="md-raised" ng-disabled="!campo.$valid || disab ||campo.nombre.$error.pattern" ng-click="aplicar(); des();">Aplicar</md-button>' +
                    '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</md-content>',
                    locals: {
                        items: $scope.listaCampos.data,
                        nomencladores: $scope.nomencladores,
                        id: $scope.idEstructura
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, items, id, nomencladores, toastr) {
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
                    console.log("nomecladores");
                    console.log(nomencladores);
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
                            url: Routing.generate('eyc_estructura_campo_create', {ide: id}, true),
                            data: data
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            console.log("Response: " + codigo);
                            if (codigo == "200") {
                                toastr.success(texto.substring(10));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(10));
                            } else {
                                toastr.error(texto.substring(10));
                            }
                            $scope.idCampo = -1;
                            $http({
                                method: 'GET', url: Routing.generate('eyc_estructura_campos', {ide: id}, true)
                            }).then(function (response) {
                                var auxiliar = ' 404 GET: El recurso solicitado con el identificador ' + id + ' no posee campos asociados.';
                                if (response.data != auxiliar) {
                                    $scope.idCampo = response.data.data[response.data.data.length - 1].id;
                                }

                                items.push({
                                    id: $scope.idCampo,
                                    nombre: $scope.nombreCampo,
                                    tipoDato: $scope.tipoCampo,
                                    descripcion: $scope.descripCampo,
                                    vinculado: $scope.vinCampo,
                                    nomenclador: $scope.nomVinCampo
                                });
                                $mdDialog.hide();
                                getCamposEst(id);
                            }, function (response) {
                                "Request failed";
                            });

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
                            url: Routing.generate('eyc_estructura_campo_create', {ide: id}, true),
                            data: data
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            console.log("Response: " + codigo);
                            if (codigo == "200") {
                                toastr.success(texto.substring(10));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(10));
                            } else {
                                toastr.error(texto.substring(10));
                            }
                            $scope.idCampo = null;
                            $http({
                                method: 'GET', url: Routing.generate('eyc_estructura_campos', {ide: id}, true)
                            }).then(function (response) {
                                var auxiliar = ' 404 GET: El recurso solicitado con el identificador ' + id + ' no posee campos asociados.';
                                if (response.data != auxiliar) {
                                    $scope.idCampo = response.data.data[response.data.data.length - 1].id;
                                }

                                items.push({
                                    id: $scope.idCampo,
                                    nombre: $scope.nombreCampo,
                                    tipoDato: $scope.tipoCampo,
                                    descripcion: $scope.descripCampo,
                                    vinculado: $scope.vinCampo,
                                    nomencladorvin: $scope.nomVinCampo
                                });
                                $scope.nombreCampo = '';
                                $scope.tipoCampo = '';
                                $scope.descripCampo = '';
                                $scope.vinCampo = false;
                                $scope.nomVinCampo = '';
                                $scope.disab = false;
                                getCamposEst(id);
                            }, function (response) {
                                "Request failed";
                            });

                        }, function (response) {
                            "Request failed";
                        });
                    };
                }
            };
            //editar campos de estructuras
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
                    '<md-select ng-disabled="checkDisab" ng-model="nomVinCampo" >' +
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
                        nomencladores: $scope.nomencladores,
                        id: $scope.idEstructura
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, items, id, nomencladores, toastr) {
                    $scope.idCampo = selectedCampo[selectedCampo.length - 1].id;
                    $scope.nombreCampo = selectedCampo[selectedCampo.length - 1].nombre;
                    $scope.tipoCampo = selectedCampo[selectedCampo.length - 1].tipoDato;
                    $scope.descripCampo = selectedCampo[selectedCampo.length - 1].descripcion;
                    $scope.vinCampo = selectedCampo[selectedCampo.length - 1].vinculado;
                    $scope.nomVinCampo = selectedCampo[selectedCampo.length - 1].nomenclador;

                    $scope.datos = ["bool", "integer", "double", "string", "date"];
                    $scope.checkDisab =  !$scope.vinCampo;
                    $scope.listaIDS = [];
                    for (var i = 0; i < nomencladores.length; i++) {
                        var nom = {
                            nombre: nomencladores[i].nombre,
                            id: nomencladores[i].id
                        };
                        $scope.listaIDS.push(nom);
                    }

                    $scope.toggle = function () {
                        if (!$scope.vinCampo) {
                            $scope.checkDisab = false;
                            $scope.tipoCampo = 'integer';
                        } else {
                            $scope.nomVinCampo = "";
                            $scope.checkDisab = true;
                        }
                    };

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
                            url: Routing.generate('eyc_estructura_campo_update', {idc: $scope.idCampo}, true),
                            data: data
                        }).then(function (response) {
                            var texto = response.data;
                            var codigo = texto.substring(0, 3);
                            console.log("Response: " + codigo);
                            if (codigo == "200") {
                                toastr.success(texto.substring(9));
                            } else if (codigo == '201') {
                                toastr.success(texto.substring(9));
                            } else {
                                toastr.error(texto.substring(9));
                            }
                            if ($scope.nomVinCampo == '') {
                                $scope.nomVinCampo = 0;
                            }
                            for (var i = 0; i < items.length; i++) {
                                if (items[i].id === selectedCampo[selectedCampo.length - 1].id) {
                                    items[i].nombre = $scope.nombreCampo;
                                    items[i].tipoDato = $scope.tipoCampo;
                                    items[i].descripcion = $scope.descripCampo;
                                    items[i].vinculado = $scope.vinCampo;
                                    items[i].nomenclador = $scope.nomVinCampo;
                                    $mdDialog.hide();
                                    getCamposEst(id);
                                }
                            }
                        }, function (response) {
                            "Request failed";
                        });
                    };
                }
            };

            //eliminar campos de estructuras
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
                    '<p>¿Confirma que desea eliminar el(los) campo(s)?</p>' +
                    '<div class="md-actions">' +
                    '<md-button class="md-primary md-raised" ng-click="delete()">Aceptar</md-button>' +
                    '<md-button class="md-primary" ng-click="cancel()">Cancelar</md-button>' +
                    '</div>' +
                    '</form>' +
                    '</div>' +
                    '</md-content>',
                    locals: {
                        items: $scope.listaCampos.data,
                        consulta: $scope.query3,
                        id: $scope.idEstructura
                    },
                    controller: DialogController
                });
                function DialogController($scope, $mdDialog, items, consulta, id, toastr) {

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
                                url: Routing.generate('eyc_estructura_campo_delete', {id: selectedCampo[i].id}, true)
                            }).then(function (response) {
                                var texto = response.data;
                                var codigo = texto.substring(0, 3);
                                console.log("Response: " + codigo);
                                if (codigo == "200") {
                                    toastr.success(texto.substring(11));
                                } else if (codigo == '201') {
                                    toastr.success(texto.substring(11));
                                } else {
                                    toastr.error(texto.substring(11));
                                }
                                'Completado'
                            }, function (response) {
                                "Request failed";
                            });
                        }
                        selectedCampo.length = 0;
                        consulta.page = 1;
                       
                        getCamposEst(id);
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

        };
        $scope.mostrarCapa1 = function (selectedCampo) {
            selectedCampo.length = 0;
            $scope.query3.page = 1;
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
            $scope.oculto = $scope.aux;
            $scope.visRel = $scope.aux2;
        };

    }
    ]
);