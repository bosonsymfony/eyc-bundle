/**
 * EyCBundle/Resources/public/adminApp/controllers/Estructura/estructuraCtrl.js
 */
angular.module('app')
        .controller('estructuraCtrl',
                ['$scope', 'estructuraSvc', '$mdDialog',
                    function ($scope, estructuraSvc, $mdDialog) {

                        var bookmark;

                        $scope.selected = [];

                        $scope.filter = {
                            options: {
                                debounce: 500
                            }
                        };

                        $scope.query = {
                            filter: '',
                            limit: '15',
                            order: 'id',
                            page: 1
                        };

                        function getEntities(query) {
                            $scope.promise = estructuraSvc.entities.get(query || $scope.query, success).$promise;
                        }

                        function success(entities) {
                            $scope.entities = entities;
                            $scope.selected = [];
                        }

                        $scope.onPaginate = function (page, limit) {
                            getEntities(angular.extend({}, $scope.query, {page: page, limit: limit}));
                        };

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

                        $scope.deleteSelected = function (event) {
                            $mdDialog.show({
                                clickOutsideToClose: true,
                                controller: 'estructuraDeleteCtrl',
                                focusOnOpen: false,
                                targetEvent: event,
                                locals: {
                                    entities: $scope.selected
                                },
                                templateUrl: $scope.$urlAssets + 'bundles/eyc/adminApp/views/Estructura/delete-dialog.html'
                            }).then(getEntities);
                        };

                        $scope.addEntity = function (event) {
                            $mdDialog.show({
                                clickOutsideToClose: true,
                                controller: 'estructuraCreateCtrl',
                                focusOnOpen: false,
                                targetEvent: event,
                                templateUrl: $scope.$urlAssets + 'bundles/eyc/adminApp/views/Estructura/save-dialog.html'
                            }).then(getEntities);
                        };

                        $scope.editEntity = function (event) {
                            estructuraSvc.entities.query({id: $scope.selected[0].id},
                                    function (response) {
                                        $mdDialog.show({
                                            clickOutsideToClose: true,
                                            controller: 'estructuraUpdateCtrl',
                                            focusOnOpen: false,
                                            targetEvent: event,
                                            templateUrl: $scope.$urlAssets + 'bundles/eyc/adminApp/views/Estructura/update-dialog.html',
                                            locals: {
                                                object: response
                                            }
                                        }).then(getEntities);
                                    }, function (error) {
                                        alert(error);
                                    }
                            );
                        }
                    }
                ]
        )
        .controller('estructuraDeleteCtrl',
                ['$scope', '$mdDialog', 'entities', '$q', 'estructuraSvc',
                    function ($scope, $mdDialog, entities, $q, estructuraSvc) {

                        $scope.cancel = $mdDialog.cancel;

                        function deleteEntity(entity, index) {
                            var deferred = estructuraSvc.entities.remove({id: entity.id});

                            deferred.$promise.then(function () {
                                entities.splice(index, 1);
                            });

                            return deferred.$promise;
                        }

                        function onComplete() {
                            $mdDialog.hide();
                        }

                        $scope.delete = function () {
                            $q.all(entities.forEach(deleteEntity)).then(onComplete);
                        }
                    }
                ]
        )
        .controller('estructuraCreateCtrl',
                ['$scope', '$mdDialog', 'estructuraSvc',
                    function ($scope, $mdDialog, estructuraSvc) {

                        var update = false;

                        var hide = true;

                        $scope.cancel = function () {
                            if (update) {
                                return $mdDialog.hide();
                            } else {
                                return $mdDialog.cancel();
                            }
                        };

                        function success(response) {
                            if (hide) {
                                $mdDialog.hide();
                            } else {
                                update = true;
                                clean();
                            }
                        }

                        function clean() {
                            $scope.entity = {};
                        }

                        function error(errors) {
                            $scope.errors = errors.data;
                        }

                        function addEntity() {

                            if ($scope.form.$valid) {
                                estructuraSvc.entities.save($scope.entity, success, error);
                            }
                        }

                        $scope.accept = function () {
                            hide = true;
                            addEntity();
                        };

                        $scope.apply = function () {
                            hide = false;
                            addEntity();
                        };

                        $scope.errors = {};
                    }
                ]
        )
        .controller('estructuraUpdateCtrl',
                ['$scope', '$mdDialog', 'estructuraSvc', 'object',
                    function ($scope, $mdDialog, estructuraSvc, object) {

                        $scope.entity = {
                            'eycbundle_estructura[nombre]': object.nombre,
                            'eycbundle_estructura[raiz]': object.raiz,
                            'eycbundle_estructura[_token]': object.token
                        };


                        $scope.cancel = function () {
                            return $mdDialog.cancel();
                        };

                        function success(response) {
                            $mdDialog.hide();
                        }

                        function error(errors) {
                            $scope.errors = errors.data;
                        }

                        function updateEntity() {
                            if ($scope.form.$valid) {
                                estructuraSvc.entities.save({id: object.id}, angular.extend({}, $scope.entity, {_method: 'PUT'}), success, error);
                            }
                        }

                        $scope.accept = function () {
                            updateEntity();
                        };
                    }
                ]
        );