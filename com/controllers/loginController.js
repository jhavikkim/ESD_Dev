(function() {

  function LoginCtrl(DataTransfer, $scope, $state) {
    
    var self = this;

    self.credentials = { UserID: '', password: '' };
    self.loginFail = false;

    self.signIn = function() {
      if ($scope.signInForm.$invalid) { return; }
      DataTransfer.post('login', {
        credentials: { userID: self.credentials.userID, password: hex_sha512(self.credentials.password) }
      }).then(function (response) {
        if (response.status == "成功") {
          self.loginFail = false;
         // $state.go('main.monitor');
          $state.go('main.rmg');
        }else{
          self.loginFail = true;
        }
      })
    };
  }

  angular.module('mainApp')
    .controller('LoginCtrl', [ 'DataTransfer', '$scope', '$state', LoginCtrl ]);

})();
