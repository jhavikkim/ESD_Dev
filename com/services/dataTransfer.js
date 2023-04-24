(function() {

  function DataTransfer($http) {

    var serviceBase = 'php/';

    var obj = {};

    obj.toast = function (data) {
      toaster.pop(data.status, "", data.message, 10000, 'trustedHtml');
    };
    obj.get = function (q) {
      return $http.get(serviceBase + q).then(function (response) {
        return response.data;
      });
    };
    obj.post = function (q, object) {
      return $http.post(serviceBase + q, object).then(function (response) {
        return response.data;
      });
    };
    obj.put = function (q, object) {
      return $http.put(serviceBase + q, object).then(function (response) {
        return response.data;
      });
    };
    obj.delete = function (q) {
      return $http.delete(serviceBase + q).then(function (response) {
        return response.data;
      });
    };

    return obj;
  }

  angular.module('mainApp')
    .factory('DataTransfer', [ '$http', DataTransfer ]);

})();