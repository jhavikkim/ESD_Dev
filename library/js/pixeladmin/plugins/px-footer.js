var _createClass=function(){function i(t,e){for(var n=0;n<e.length;n++){var i=e[n];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(t,i.key,i)}}return function(t,e,n){return e&&i(t.prototype,e),n&&i(t,n),t}}();function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}var PxFooter=function(i){"use strict";var t="pxFooter",o="px.footer",e="."+o,n=i.fn[t],s="px-content",r="px-footer-bottom",u="px-footer-fixed",a={RESIZE:"resize"+e,SCROLL:"scroll"+e,NAV_EXPANDED:"expanded.px.nav",NAV_COLLAPSED:"collapsed.px.nav",DROPDOWN_OPENED:"dropdown-opened.px.nav",DROPDOWN_CLOSED:"dropdown-closed.px.nav"},c=function(){function n(t){_classCallCheck(this,n),this.uniqueId=pxUtil.generateUniqueId(),this.element=t,this.parent=this._getParent(t),this._setListeners(),this.update()}return _createClass(n,[{key:"update",value:function(){this.parent===document.body&&(this._curScreenSize=window.PixelAdmin.getScreenSize(),this._updateBodyMinHeight());var t=i(this.element.parentNode).find("> ."+s)[0];pxUtil.hasClass(this.element,r)||pxUtil.hasClass(this.element,u)?t.style.paddingBottom=i(this.element).outerHeight()+20+"px":t.style.paddingBottom=t.setAttribute("style",(t.getAttribute("style")||"").replace(/\s*padding-bottom:\s*\d+px\s*;?/i))}},{key:"destroy",value:function(){this._unsetListeners(),i(this.element).removeData(o),i(document.body).css("min-height","");var t=i(this.element.parentNode).find("> ."+s)[0];t.style.paddingBottom=t.setAttribute("style",(t.getAttribute("style")||"").replace(/\s*padding-bottom:\s*\d+px\s*;?/i))}},{key:"_getParent",value:function(t){for(var e=t.parentNode;"ui-view"===e.nodeName.toLowerCase();)e=e.parentNode;return e}},{key:"_updateBodyMinHeight",value:function(){document.body.style.minHeight&&(document.body.style.minHeight=null),"lg"!==this._curScreenSize&&"xl"!==this._curScreenSize||!pxUtil.hasClass(this.element,r)||i(document.body).height()>=document.body.scrollHeight||(document.body.style.minHeight=document.body.scrollHeight+"px")}},{key:"_setListeners",value:function(){i(window).on(this.constructor.Event.RESIZE+"."+this.uniqueId,i.proxy(this.update,this)).on(this.constructor.Event.SCROLL+"."+this.uniqueId,i.proxy(this._updateBodyMinHeight,this)).on(this.constructor.Event.NAV_EXPANDED+"."+this.uniqueId+" "+this.constructor.Event.NAV_COLLAPSED+"."+this.uniqueId,".px-nav",i.proxy(this._updateBodyMinHeight,this)),this.parent===document.body&&i(".px-nav").on(this.constructor.Event.DROPDOWN_OPENED+"."+this.uniqueId+" "+this.constructor.Event.DROPDOWN_CLOSED+"."+this.uniqueId,".px-nav-dropdown",i.proxy(this._updateBodyMinHeight,this))}},{key:"_unsetListeners",value:function(){i(window).off(this.constructor.Event.RESIZE+"."+this.uniqueId+" "+this.constructor.Event.SCROLL+"."+this.uniqueId).off(this.constructor.Event.NAV_EXPANDED+"."+this.uniqueId+" "+this.constructor.Event.NAV_COLLAPSED+"."+this.uniqueId),i(".px-nav").off(this.constructor.Event.DROPDOWN_OPENED+"."+this.uniqueId+" "+this.constructor.Event.DROPDOWN_CLOSED+"."+this.uniqueId)}}],[{key:"_jQueryInterface",value:function(e){return this.each(function(){var t=i(this).data(o);if(t||(t=new n(this),i(this).data(o,t)),"string"==typeof e){if(!t[e])throw new Error('No method named "'+e+'"');t[e]()}})}},{key:"NAME",get:function(){return t}},{key:"DATA_KEY",get:function(){return o}},{key:"Event",get:function(){return a}},{key:"EVENT_KEY",get:function(){return e}}]),n}();return i.fn[t]=c._jQueryInterface,i.fn[t].Constructor=c,i.fn[t].noConflict=function(){return i.fn[t]=n,c._jQueryInterface},c}(jQuery);