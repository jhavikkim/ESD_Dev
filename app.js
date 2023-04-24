(function() {

  angular.module('mainApp', [
    'ui.router',
    'ui.router.state.events',
    'oc.lazyLoad',
    'ui.bootstrap',
    'perfect_scrollbar',
    'px-navbar',
    'px-nav'
  ]).directive('onFinishRender', function ($timeout) {
    return {
        restrict: 'A',
        link: function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {
                    scope.$emit(attr.onFinishRender);
                });
            }
        }
    }
});

})();