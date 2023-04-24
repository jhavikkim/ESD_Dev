function pxExpandingInputDirective(p,a){"use strict";var r=["onExpand","onExpanded","onCollapse","onCollapsed"];return{restrict:"E",transclude:!0,template:'<div class="expanding-input" ng-transclude></div>',replace:!0,link:function(t,i,e){var n=e.instance?a(e.instance).assign:angular.noop;p(function(){i.pxExpandingInput(),r.forEach(function(n){e[n]&&i.on(n.replace(/on([A-Z])/g,function(n,t){return t.toLowerCase()})+".px.expanding-input",a(e[n])(t))}),void 0!==e.expand&&t.$watch(e.expand,function(n){i.pxExpandingInput(n?"expand":"collapse")}),n(t,$.fn.pxExpandingInput.bind(i)),i.on("$destroy",function(){return i.off().pxExpandingInput("destroy")})})}}}function pxExpandingInputControlDirective(){"use strict";return{restrict:"A",link:function(n,t){t.addClass("expanding-input-control")}}}function pxExpandingInputContentDirective(){"use strict";return{restrict:"E",transclude:!0,template:'<div class="expanding-input-content" ng-transclude></div>',replace:!0}}angular.module("px-expanding-input",[]).directive("pxExpandingInput",["$timeout","$parse",pxExpandingInputDirective]).directive("pxExpandingInputControl",pxExpandingInputControlDirective).directive("pxExpandingInputContent",pxExpandingInputContentDirective);