
/**
 * EyCBundle/Resources/public/adminApp/services/eycEstructuraSvc.js
 */

angular.module('app')
    .factory('eycEstructuraSvc',
    ['$resource',
        function ($resource) {

            return {
                //acceder a la funcionalidad de listar estructuras
                listaEstructuras: $resource(Routing.generate('eyc_estructura_list', {}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                }),
                //acceder a la funcionalidad de listar estructuras sin filtros
                listaEstructuras2: $resource(Routing.generate('eyc_estructura_list2', {}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                }),
                //acceder a la funcionalidad de mostrar árbol de instancias de estructuras
                arbolEstrOp: $resource(Routing.generate('eyc_estructura_arbol_estructura_op', {}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                }),
                //acceder a la funcionalidad de listar instancias raiz
                instanciasRaiz: $resource(Routing.generate('eyc_estructura_instancias_hijas2', {}, true) + ':id', null, {
                    'query': {
                        isArray: false
                    }
                })
            };
        }
    ]
);