!function(t,o){"use strict";"function"==typeof define&&define.amd?define(["jquery"],o):"object"==typeof exports?module.exports=o(require("jquery")):t.bootbox=o(t.jQuery)}(this,function o(s,u){"use strict";var p={dialog:"<div class='bootbox modal' tabindex='-1' role='dialog'><div class='modal-dialog'><div class='modal-content'><div class='modal-body'><div class='bootbox-body'></div></div></div></div></div>",header:"<div class='modal-header'><h4 class='modal-title'></h4></div>",footer:"<div class='modal-footer'></div>",closeButton:"<button type='button' class='bootbox-close-button close' data-dismiss='modal' aria-hidden='true'>&times;</button>",form:"<form class='bootbox-form'></form>",inputs:{text:"<input class='bootbox-input bootbox-input-text form-control' autocomplete=off type=text />",textarea:"<textarea class='bootbox-input bootbox-input-textarea form-control'></textarea>",email:"<input class='bootbox-input bootbox-input-email form-control' autocomplete='off' type='email' />",select:"<select class='bootbox-input bootbox-input-select form-control'></select>",checkbox:"<div class='checkbox'><label><input class='bootbox-input bootbox-input-checkbox' type='checkbox' /></label></div>",date:"<input class='bootbox-input bootbox-input-date form-control' autocomplete=off type='date' />",time:"<input class='bootbox-input bootbox-input-time form-control' autocomplete=off type='time' />",number:"<input class='bootbox-input bootbox-input-number form-control' autocomplete=off type='number' />",password:"<input class='bootbox-input bootbox-input-password form-control' autocomplete='off' type='password' />"}},c={locale:"en",backdrop:"static",animate:!0,className:null,closeButton:!0,show:!0,container:"body"},b={};function d(t,o,e){t.stopPropagation(),t.preventDefault(),s.isFunction(e)&&!1===e.call(o,t)||o.modal("hide")}function f(t,e){var a=0;s.each(t,function(t,o){e(t,o,a++)})}function m(t,o,e){return s.extend(!0,{},t,function(t,o){var e=t.length,a={};if(e<1||2<e)throw new Error("Invalid argument length");return 2===e||"string"==typeof t[0]?(a[o[0]]=t[0],a[o[1]]=t[1]):a=t[0],a}(o,e))}function e(t,o,e,a){return O(m({className:"bootbox-"+t,buttons:C.apply(null,o)},a,e),o)}function C(){for(var t,o,e={},a=0,n=arguments.length;a<n;a++){var r=arguments[a],l=r.toLowerCase(),i=r.toUpperCase();e[l]={label:(t=i,void 0,o=h[c.locale],o?o[t]:h.en[t])}}return e}function O(t,o){var e={};return f(o,function(t,o){e[o]=!0}),f(t.buttons,function(t){if(e[t]===u)throw new Error("button key "+t+" is not allowed (options are "+o.join("\n")+")")}),t}b.alert=function(){var t;if((t=e("alert",["ok"],["message","callback"],arguments)).callback&&!s.isFunction(t.callback))throw new Error("alert requires callback property to be a function when provided");return t.buttons.ok.callback=t.onEscape=function(){return!s.isFunction(t.callback)||t.callback.call(this)},b.dialog(t)},b.confirm=function(){var t;if((t=e("confirm",["cancel","confirm"],["message","callback"],arguments)).buttons.cancel.callback=t.onEscape=function(){return t.callback.call(this,!1)},t.buttons.confirm.callback=function(){return t.callback.call(this,!0)},!s.isFunction(t.callback))throw new Error("confirm requires a callback");return b.dialog(t)},b.prompt=function(){var o,t,e,a,n,r,l;if(a=s(p.form),t={className:"bootbox-prompt",buttons:C("cancel","confirm"),value:"",inputType:"text"},r=(o=O(m(t,arguments,["title","callback"]),["cancel","confirm"])).show===u||o.show,o.message=a,o.buttons.cancel.callback=o.onEscape=function(){return o.callback.call(this,null)},o.buttons.confirm.callback=function(){var e;switch(o.inputType){case"text":case"textarea":case"email":case"select":case"date":case"time":case"number":case"password":e=n.val();break;case"checkbox":var t=n.find("input:checked");e=[],f(t,function(t,o){e.push(s(o).val())})}return o.callback.call(this,e)},o.show=!1,!o.title)throw new Error("prompt requires a title");if(!s.isFunction(o.callback))throw new Error("prompt requires a callback");if(!p.inputs[o.inputType])throw new Error("invalid prompt type");switch(n=s(p.inputs[o.inputType]),o.inputType){case"text":case"textarea":case"email":case"date":case"time":case"number":case"password":n.val(o.value);break;case"select":var i={};if(l=o.inputOptions||[],!s.isArray(l))throw new Error("Please pass an array of input options");if(!l.length)throw new Error("prompt with select requires options");f(l,function(t,o){var e=n;if(o.value===u||o.text===u)throw new Error("given options in wrong format");o.group&&(i[o.group]||(i[o.group]=s("<optgroup/>").attr("label",o.group)),e=i[o.group]),e.append("<option value='"+o.value+"'>"+o.text+"</option>")}),f(i,function(t,o){n.append(o)}),n.val(o.value);break;case"checkbox":var c=s.isArray(o.value)?o.value:[o.value];if(!(l=o.inputOptions||[]).length)throw new Error("prompt with checkbox requires options");if(!l[0].value||!l[0].text)throw new Error("given options in wrong format");n=s("<div/>"),f(l,function(t,e){var a=s(p.inputs[o.inputType]);a.find("input").attr("value",e.value),a.find("label").append(e.text),f(c,function(t,o){o===e.value&&a.find("input").prop("checked",!0)}),n.append(a)})}return o.placeholder&&n.attr("placeholder",o.placeholder),o.pattern&&n.attr("pattern",o.pattern),o.maxlength&&n.attr("maxlength",o.maxlength),a.append(n),a.on("submit",function(t){t.preventDefault(),t.stopPropagation(),e.find(".btn-primary").click()}),(e=b.dialog(o)).off("shown.bs.modal"),e.on("shown.bs.modal",function(){n.focus()}),!0===r&&e.modal("show"),e},b.dialog=function(t){t=function(t){var a,n;if("object"!=typeof t)throw new Error("Please supply an object of options");if(!t.message)throw new Error("Please specify a message");return(t=s.extend({},c,t)).buttons||(t.buttons={}),a=t.buttons,n=function(t){var o,e=0;for(o in t)e++;return e}(a),f(a,function(t,o,e){if(s.isFunction(o)&&(o=a[t]={callback:o}),"object"!==s.type(o))throw new Error("button with key "+t+" must be an object");o.label||(o.label=t),o.className||(o.className=n<=2&&e===n-1?"btn-primary":"btn-default")}),t}(t);var e=s(p.dialog),o=e.find(".modal-dialog"),a=e.find(".modal-body"),n=t.buttons,r="",l={onEscape:t.onEscape};if(s.fn.modal===u)throw new Error("$.fn.modal is not defined; please double check you have included the Bootstrap JavaScript library. See http://getbootstrap.com/javascript/ for more details.");if(f(n,function(t,o){r+="<button data-bb-handler='"+t+"' type='button' class='btn "+o.className+"'>"+o.label+"</button>",l[t]=o.callback}),a.find(".bootbox-body").html(t.message),!0===t.animate&&e.addClass("fade"),t.className&&e.addClass(t.className),"large"===t.size?o.addClass("modal-lg"):"small"===t.size&&o.addClass("modal-sm"),t.title&&a.before(p.header),t.closeButton){var i=s(p.closeButton);t.title?e.find(".modal-header").prepend(i):i.css("margin-top","-10px").prependTo(a)}return t.title&&e.find(".modal-title").html(t.title),r.length&&(a.after(p.footer),e.find(".modal-footer").html(r)),e.on("hidden.bs.modal",function(t){t.target===this&&e.remove()}),e.on("shown.bs.modal",function(){e.find(".btn-primary:first").focus()}),"static"!==t.backdrop&&e.on("click.dismiss.bs.modal",function(t){e.children(".modal-backdrop").length&&(t.currentTarget=e.children(".modal-backdrop").get(0)),t.target===t.currentTarget&&e.trigger("escape.close.bb")}),e.on("escape.close.bb",function(t){l.onEscape&&d(t,e,l.onEscape)}),e.on("click",".modal-footer button",function(t){var o=s(this).data("bb-handler");d(t,e,l[o])}),e.on("click",".bootbox-close-button",function(t){d(t,e,l.onEscape)}),e.on("keyup",function(t){27===t.which&&e.trigger("escape.close.bb")}),s(t.container).append(e),e.modal({backdrop:!!t.backdrop&&"static",keyboard:!1,show:!1}),t.show&&e.modal("show"),e},b.setDefaults=function(){var t={};2===arguments.length?t[arguments[0]]=arguments[1]:t=arguments[0],s.extend(c,t)},b.hideAll=function(){return s(".bootbox").modal("hide"),b};var h={bg_BG:{OK:"Ок",CANCEL:"Отказ",CONFIRM:"Потвърждавам"},br:{OK:"OK",CANCEL:"Cancelar",CONFIRM:"Sim"},cs:{OK:"OK",CANCEL:"Zrušit",CONFIRM:"Potvrdit"},da:{OK:"OK",CANCEL:"Annuller",CONFIRM:"Accepter"},de:{OK:"OK",CANCEL:"Abbrechen",CONFIRM:"Akzeptieren"},el:{OK:"Εντάξει",CANCEL:"Ακύρωση",CONFIRM:"Επιβεβαίωση"},en:{OK:"OK",CANCEL:"Cancel",CONFIRM:"OK"},es:{OK:"OK",CANCEL:"Cancelar",CONFIRM:"Aceptar"},et:{OK:"OK",CANCEL:"Katkesta",CONFIRM:"OK"},fa:{OK:"قبول",CANCEL:"لغو",CONFIRM:"تایید"},fi:{OK:"OK",CANCEL:"Peruuta",CONFIRM:"OK"},fr:{OK:"OK",CANCEL:"Annuler",CONFIRM:"D'accord"},he:{OK:"אישור",CANCEL:"ביטול",CONFIRM:"אישור"},hu:{OK:"OK",CANCEL:"Mégsem",CONFIRM:"Megerősít"},hr:{OK:"OK",CANCEL:"Odustani",CONFIRM:"Potvrdi"},id:{OK:"OK",CANCEL:"Batal",CONFIRM:"OK"},it:{OK:"OK",CANCEL:"Annulla",CONFIRM:"Conferma"},ja:{OK:"OK",CANCEL:"キャンセル",CONFIRM:"確認"},lt:{OK:"Gerai",CANCEL:"Atšaukti",CONFIRM:"Patvirtinti"},lv:{OK:"Labi",CANCEL:"Atcelt",CONFIRM:"Apstiprināt"},nl:{OK:"OK",CANCEL:"Annuleren",CONFIRM:"Accepteren"},no:{OK:"OK",CANCEL:"Avbryt",CONFIRM:"OK"},pl:{OK:"OK",CANCEL:"Anuluj",CONFIRM:"Potwierdź"},pt:{OK:"OK",CANCEL:"Cancelar",CONFIRM:"Confirmar"},ru:{OK:"OK",CANCEL:"Отмена",CONFIRM:"Применить"},sq:{OK:"OK",CANCEL:"Anulo",CONFIRM:"Prano"},sv:{OK:"OK",CANCEL:"Avbryt",CONFIRM:"OK"},th:{OK:"ตกลง",CANCEL:"ยกเลิก",CONFIRM:"ยืนยัน"},tr:{OK:"Tamam",CANCEL:"İptal",CONFIRM:"Onayla"},zh_CN:{OK:"OK",CANCEL:"取消",CONFIRM:"确认"},zh_TW:{OK:"OK",CANCEL:"取消",CONFIRM:"確認"}};return b.addLocale=function(t,e){return s.each(["OK","CANCEL","CONFIRM"],function(t,o){if(!e[o])throw new Error("Please supply a translation for '"+o+"'")}),h[t]={OK:e.OK,CANCEL:e.CANCEL,CONFIRM:e.CONFIRM},b},b.removeLocale=function(t){return delete h[t],b},b.setLocale=function(t){return b.setDefaults("locale",t)},b.init=function(t){return o(t||s)},b});