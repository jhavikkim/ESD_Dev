!function(t){"use strict";if(!t.fn.modal)throw new Error("modal.js required.");var r=t.fn.modal.Constructor.prototype.show,n=t.fn.modal.Constructor.prototype.hide;t.fn.modal.Constructor.prototype.show=function(o){r.call(this,o),this.isShown&&t("html").addClass("modal-open")},t.fn.modal.Constructor.prototype.hide=function(o){n.call(this,o),this.isShown||t("html").removeClass("modal-open")}}(jQuery);