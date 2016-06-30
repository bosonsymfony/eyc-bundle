
/**
 * EyCBundle/Resources/public/adminApp/filters/eycEstructuraFilter.js
 */
angular.module('app')
        .filter('eycEstructuraFilter',
                function () {
                    return function (input) {
                        input = input || '';
                        var out = "";
                        for (var i = 0; i < input.length; i++) {
                            out = input.charAt(i) + out;
                        }
                        return out;
                    }
                }
        );