
/**
 * EyCBundle/Resources/public/adminApp/services/eycNomencladorSvc.js
 */
angular.module('app')
    .factory('eycNomencladorSvc',
    ['$resource','$http',
                    function ($resource) {
                        //acceder a la funcionalidad de listar campos de nomenclador
                        function getCampos(id) {
                            return $resource(Routing.generate('eyc_nomenclador_campos', {id:id}, true) + ':id', null, {
                                'query': {
                                    isArray: false
                                }
                            })
                        }
                        //acceder a la funcionalidad de listar instancias de nomenclador
                        function getInstancias(id) {
                            return $resource(Routing.generate('eyc_nomenclador_instancias', {id:id}, true) + ':id', null, {
                                'query': {
                                    isArray: false
                                }
                            })
                        }
                        //acceder a la funcionalidad de listar nomencladores
                        function getnomenclador() {
                            return $resource(Routing.generate('eyc_nomenclador_list', {}, true) + ':id', null, {
                                'query': {
                                    isArray: false
                                }
                            })
                        }
                        return {
                            getCampos: getCampos,
                            getInstancias: getInstancias,
                            getnomenclador:getnomenclador
                        };
                    }
                ]);