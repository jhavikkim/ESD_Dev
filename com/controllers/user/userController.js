(function() {

  function UserCtrl(DataTransfer, $rootScope, $scope, $state, $filter) {

    var self = this;

    self.searchText = {};
    self.usersData = new Array();
    self.userData = {};
    userUid = 0;

    self.newUser = { name: '', userID:'', email: '', phone: '', password: '00000000', auth: '0' };

    if($rootScope.auth<2){
      $state.go("main.monitor");
    }

    function getUserList() {
      DataTransfer.post('userList', {}).then(function (response) {
        self.usersData = response;
      })
    }
    getUserList();

    self.getUserContent = function(uid) {
      DataTransfer.post('userContent', { uid: uid } ).then(function (response) {
        self.userData = response;
        $('#modal-modify').modal('show');
      })
    }

    self.modifyUser = function() {
      DataTransfer.post('modifyUser', self.userData ).then(function (response) {
        $('#modal-modify').modal('hide');
        getUserList();
      })
    }

    self.resetPassword = function() {
      DataTransfer.post('resetPassword', { uid: userUid } ).then(function (response) {
        $('#modal-reset-alert').modal('hide');
        $('#modal-modify').modal('hide');
      })
    }
    self.deleteUser = function() {
      DataTransfer.post('deleteUser', { uid: userUid } ).then(function (response) {
        $('#modal-delete-alert').modal('hide');
        $('#modal-modify').modal('hide');
        getUserList();
      })
    }

    self.addUser = function() {
      DataTransfer.post('addUser', self.newUser ).then(function (response) {
        console.log(response);
        $('#modal-add').modal('hide');
        getUserList();
      })
    }

    self.resetPasswordAlert = function(uid) {
      $('#modal-reset-alert').modal('show');
      userUid = uid;
    }
    self.deleteUserAlert = function(uid) {
      $('#modal-delete-alert').modal('show');
      userUid = uid;
    }


  }

  angular.module('mainApp')
    .controller('UserCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', UserCtrl ]);

})();
