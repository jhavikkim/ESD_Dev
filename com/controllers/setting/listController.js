(function() {

  function ListCtrl(DataTransfer, $rootScope, $scope, $state, $filter) {

    var self = this;

    self.searchText = {};
    self.searchLocation = new Array();

    self.locationData = new Array();
    self.modelData = new Array();
    self.itemData = new Array();
    self.sensorData = {};
    self.rmData = { rmType: 'RM1', rmIP: '', ch: '', device: '', deviceID: '', isZigbee: 0, use: 0 };
    self.sortColumn = "uid";
    self.reverse = false;
    self.isUpdate = 0;

    if($rootScope.auth<2){
      //$state.go("main.monitor");
      $state.go("main.rmg");
    }

    function getLocationList() {
      DataTransfer.post('locationList', {}).then(function (response) {
        self.locationData = response;
      })
    }

    function getModelList() {
      DataTransfer.post('modelList', {}).then(function (response) {
        self.modelData = response;
      })
    }

    function getItemList() {
      DataTransfer.post('itemList', {}).then(function (response) {
        
        self.itemData = response;
      })
    }

    getLocationList();
    getModelList();
    getItemList();

    self.getListContent = function(uid) {
      DataTransfer.post('listContent', { uid: uid } ).then(function (response) {
        self.sensorData = response;
        console.log(response);
      	$('#modal-modify').modal('show');
      })
    }

    self.modifyContent = function() {
      // console.log(self.isUpdate);
      //console.log(self.sensorData);

      DataTransfer.post('modifyContent', self.sensorData ).then(function (response) {
        //console.log(response);
        if(response!=='rmIP重複'){
          self.itemData = response;
          $('#modal-modify').modal('hide');
        }else{
          $('#modal-alert').modal('show');
        }

        if(self.isUpdate){
          DataTransfer.post('setRMval', {
            uid: self.itemData.uid,
            rmIP: self.sensorData.rmIP,
            usbRelayID: self.sensorData.usbRelayID,
            rmID: self.sensorData.rmID,
            max: self.sensorData.alert_max, 
            min: self.sensorData.alert_min, 
            ch: self.sensorData.ch
          });
        }
      })
    }

    self.addRM = function() {
      console.log(self.rmData )
      DataTransfer.post('addRM', self.rmData ).then(function (response) {
        console.log(response);
        if(response!=='rmIP duplicate'){
          $('#modal-add').modal('hide');
        }
        else{
          $('#modal-alert').modal('show');
        }
        getItemList()
      })
    }

    self.orderByColume = function(sortColumn){
      (self.sortColumn === sortColumn) ? self.reverse = !self.reverse : self.reverse = false;
      self.sortColumn = sortColumn;
    }

  }

  angular.module('mainApp')
    .controller('ListCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', ListCtrl ]);

})();
