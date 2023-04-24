(function() {
  function SysCtrl(DataTransfer, $rootScope, $scope, $state, $filter) {
    var self = this;

    self.setData = { useSoundAlarm: false, useLedAlarm: false, useOverwrite:false, hdlimit: $rootScope.hdlimit} ;
    self.freeSpace = $rootScope.freeSpace;

    function getSoundAlarm() {
      DataTransfer.get('getSysSet?key=useSoundAlarm').then((resp) => {
        self.setData.useSoundAlarm = (resp=='1');
      });
    }

    function getLedAlarm() {
      DataTransfer.get('getSysSet?key=useLedAlarm').then((resp) => {
        self.setData.useLedAlarm = (resp=='1');
      });
    }

    function getOverwrite() {
      DataTransfer.get('getSysSet?key=useOverwrite').then((resp) => {
        self.setData.useOverwrite = (resp=='1');
      });
    }

    function getHdlimit() {
      DataTransfer.get('getSysSet?key=hdlimit').then((resp) => {
        if(resp!=""){
          self.setData.hdlimit = resp;
        }
      });
    }

    getSoundAlarm();
    getLedAlarm();
    getOverwrite();
    getHdlimit();
    // DataTransfer.post('setSysSet', {key: 'soundAlarm', val: '1'}).then((resp) => {
    //   console.log(resp);
    // });

    self.saveSys = function() {
      if(isNaN(self.setData.hdlimit)){
        alert('容量容許值必須是數字');
        location.reload();
        return false;
      }
      
      DataTransfer.post('setSysSet', {key: 'useSoundAlarm', val: self.setData.useSoundAlarm});
      DataTransfer.post('setSysSet', {key: 'useLedAlarm', val: self.setData.useLedAlarm});
      DataTransfer.post('setSysSet', {key: 'useOverwrite', val: self.setData.useOverwrite});
      DataTransfer.post('setSysSet', {key: 'sensorStatus', val: 1});
      DataTransfer.post('setSysSet', {key: 'ledStatus', val: 1});
      DataTransfer.post('setSysSet', {key: 'hdlimit', val: self.setData.hdlimit});
      if(self.setData.hdlimit!=""){
        $rootScope.hdlimit = self.setData.hdlimit;
      }

      if(!self.setData.useLedAlarm){
        DataTransfer.get('setLedOff');
      }
      alert('儲存完成');

      location.reload();
    }
  }

  angular.module('mainApp')
    .controller('SysCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', SysCtrl ]);

})();