function pxSparklineDirective(u){"use strict";return{restrict:"A",require:"ngModel",link:function(i,t,a,l){function e(){var e=angular.extend({type:"line"},u(a.options)(i)||{}),n=angular.isString(l.$viewValue)?l.$viewValue.replace(/(^,)|(,$)/g,""):l.$viewValue,r=angular.isArray(n)?n:n.split(",");t.pxSparkline(r,e)}i.$watch(a.ngModel,function(){return e()}),i.$watch(a.options,function(){return e()}),t.on("$destroy",function(){return t.pxSparkline("destroy")})}}}angular.module("px-sparkline",[]).directive("pxSparkline",["$parse",pxSparklineDirective]);