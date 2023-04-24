(function() {

  function MainCtrl(DataTransfer, $rootScope, $state, $uibModal, $interval) {
    
    var self = this;

    self.editPassword = { oldPassword: '', newPassword: '', rePassword:'' };

    self.cancel = function() { $uibModalInstance.dismiss(); };

    self.noticeLists = {};

    self.refresh = { times: parseInt($rootScope.reflashTime) };

    self.setRefleshTimes = function() {
      DataTransfer.post('setRefleshTimes', { times: self.refresh.times }).then(function (response) {
        $rootScope.reflashTime = response.response;
        $('#modal-edit-refresh').modal('hide');
        alert("修改完畢，請自行重新整理頁面套用新設定");
      });
    }
    
    autoGetNoticeLists = $interval(getNoticeLists, $rootScope.reflashTime);
    function getNoticeLists() {
      // call noticeList api except dashboard page
      if($state.current.name != 'main.dashboard') {
        DataTransfer.get('noticeList').then(function (response) {
          self.noticeLists = response;
          // console.log(response);
        });
      }

    }
    getNoticeLists();

    $rootScope.$on("$stateChangeSuccess", function(e,to){
      if(to.name !== 'main.dashboard') {
        getNoticeLists();
      }
    });

    self.toContentModal = function(uid,name,isfan){
      if(isfan == 0){
        $state.go("main.monitorSingle",{searchText: name}).then(function(){
          $rootScope.viewContentId = uid;
          DataTransfer.post('readNotice',  { uid: uid }).then(function (response) {
            //$('#modal-sensor').modal('show');
            self.noticeLists = response;
          });
        });
      }else if(isfan >= 1  /*isfan == 1 || isfan == 3 || isfan == 2*/){
        $state.go("main.fanSingle",{searchText: name}).then(function(){
          $rootScope.viewContentId = uid;
          DataTransfer.post('readNotice',  { uid: uid }).then(function (response) {
            //$('#modal-modify').modal('show');
            self.noticeLists = response;
          });
        });
      }else if(isfan == -1){
        $state.go("main.rmgSingle",{searchText: name}).then(function(){
          $rootScope.viewContentId = uid;
          DataTransfer.post('readNotice',  { uid: uid }).then(function (response) {
            //$('#modal-modify').modal('show');
            self.noticeLists = response;
          });
        });
      }      
      
    }

    function addDocumentClass() {
      $('html').addClass('modal-open');
    }

    function removeDocumentClass() {
      $('html').removeClass('modal-open');
    }

    self.open = function(size, templateUrl, windowClass, modalData) {
      var modalInstance = $uibModal.open({
        templateUrl: templateUrl,
        controller: 'ModalCtrl',
        controllerAs: 'modalCtrl',
        size: size,
        windowClass: windowClass,
        backdrop: 'static',
        resolve: {
          data: modalData
        }
      });

      modalInstance.result.then(removeDocumentClass, removeDocumentClass);
      addDocumentClass();
    };

    self.logout = function () {
      DataTransfer.get('logout').then(function (response) {
        $state.go('login');
      });
    }

    self.changePassword = function () {
      DataTransfer.post('changePassword', self.editPassword).then(function (response) {
        if (response.status == '錯誤') {
          self.open(null, 'ui-warning-modal.html', 'modal-alert modal-warning modal-display-top', 
            { modalStatus: response.status, modalMessage: response.message });
        }else{
          self.open(null, 'ui-success-modal.html', 'modal-alert modal-success modal-display-top', 
            { modalStatus: response.status, modalMessage: response.message });
          $('#modal-edit-password').modal('hide');
        }
      });
    }

  }

  function ModalCtrl($uibModalInstance, $scope) {
    var self = this;
    self.modalStatus = $scope.$resolve.data.modalStatus;
    self.modalMessage = $scope.$resolve.data.modalMessage;
    self.ok = function() { $uibModalInstance.close(); };
    self.cancel = function() { $uibModalInstance.dismiss('cancel'); };
  }


  angular.module('mainApp')
    .controller('MainCtrl', [ 'DataTransfer', '$rootScope', '$state', '$uibModal', '$interval', MainCtrl ])
    .controller('ModalCtrl', [ '$uibModalInstance', '$scope', ModalCtrl ]);

})();
