
/**
 * EyCBundle/Resources/public/adminApp/services/eycEstructuraRelacionesSvc.js
 */

angular.module('app')
    .factory('eycEstructuraRelacionesSvc',
    ['$resource','$http',
        function ($resource, $http) {

            //acceder a la funcionalidad de listar relaciones de estructuras
            function getHijos(id) {
                return $resource(Routing.generate('eyc_estructura_hijas', {id:id}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                })
            }
            //acceder a la funcionalidad de listar instancias hijas de una estructura
            function getOpHijos(id) {
                return $resource(Routing.generate('eyc_estructura_instancias_hijas', {id:id}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                })
            }
            //acceder a la funcionalidad de listar padre de una estructura
            function getPadre(id) {
                return $resource(Routing.generate('eyc_estructura_padre', {id:id}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                })
            }
            //acceder a la funcionalidad de listar campos de estructura
            function getCampos(ide) {
                return $resource(Routing.generate('eyc_estructura_campos', {ide:ide}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                })
            }
            //acceder a la funcionalidad de listar instancias de una estructura
            function getInstancias(ide) {
                return $resource(Routing.generate('eyc_estructura_instancias', {ide:ide}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                })
            }
            return {
                getHijos: getHijos,
                getCampos: getCampos,
                getInstancias: getInstancias,
                getPadre: getPadre,
                getOpHijos: getOpHijos
            };
        }
    ]
);