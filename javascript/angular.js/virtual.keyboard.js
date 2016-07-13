// Copyright Up and Running

    .directive('bstopKey', function () {
        return {
            restrict: 'E',
            scope: {
                key: '@',
                model: '='
            },
            link: function (scope, elm, attrs) {
                scope.$watch('model', function () {
                    scope.checked = false;
                    if (scope.model) {
                        scope.checked = (scope.key == scope.model);
                    }
                });
            },
            template: '<label ng-class="{\'active\' : checked}" for="key_{{ key }}">{{ key }}' +
                '<input type="radio" id="key_{{ key }}" name="key" ng-checked="checked" ng-model="model" value="{{ key }}" />' +
                '</label>'
        }
    })

    .directive('bstopKeyboard', function () {
        return {
            restrict: 'A',
            link: function (scope, elm, attrs) {
            },
            template: '<div id="screen-keyboard" class="well" >' +
                '<p class="krow1"><bstop-key ng-repeat="letter in keys[1]" key="{{ letter }}" model="shortcut.key" /> </p>' +
                '<p class="krow2"><bstop-key ng-repeat="letter in keys[2]" key="{{ letter }}" model="shortcut.key" /> </p>' +
                '<p class="krow3"><bstop-key ng-repeat="letter in keys[3]" key="{{ letter }}" model="shortcut.key" /> </p>' +
                '</div>',
            controller: function ($scope) {
                $scope.keys = {};
                $scope.keys[1] = ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p'];
                $scope.keys[2] = ['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l'];
                $scope.keys[3] = ['z', 'x', 'c', 'v', 'b', 'n', 'm'];
            }
        }
    })

    .directive('onKeyup', function ($document) {
        return function (scope, elm, attrs) {
            function applyKeyup(evt) {
                scope.$apply(function () {
                    if (keyupFn) {
                        keyupFn.call(scope, evt);
                    }
                });
            };

            var allowedKeys = scope.$eval(attrs.keys);
            var keyupFn = scope.$eval(attrs.onKeyup);
            $document.unbind('keyup');
            $document.bind('keyup', function (evt) {
                // fire only if the key was pressed out of an input field
                var field = evt.target.tagName.toLowerCase();
                if (!(field == 'textarea') && !(field == "input" && evt.target.type.toLowerCase() == 'text')) {
                    // if no key restriction specified, always fire
                    if (!allowedKeys || allowedKeys.length == 0) {
                        applyKeyup(evt);
                    } else {
                        angular.forEach(allowedKeys, function (key) {
                            if (key == evt.which) {
                                applyKeyup(evt);
                            }
                        });
                    }
                }
            });
        };
    })
