(function() {

  function config($stateProvider, $locationProvider, $urlRouterProvider, $ocLazyLoadProvider) {

    $urlRouterProvider.otherwise('/monitor');
    // $urlRouterProvider.when('/setting', '/setting/list');
    // $urlRouterProvider.when('/user', '/user/user');

    $locationProvider.hashPrefix('');

    $ocLazyLoadProvider.config({
      debug: false
    });

    $stateProvider
      // login
      .state('login', {
        url: '/login',
        templateUrl: 'com/views/login.html',
        controller: 'LoginCtrl as loginCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/loginController.js'
            ]);
          }
        }
      })

      // layout
      .state('main', {
        abstract: true,
        url: '/',
        templateUrl: 'com/views/main.html',
        controller: 'MainCtrl as mainCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/mainController.js'
            ]);
          }
        }
      })

      // monitor
      .state('main.monitor', {
        url: 'monitor',
        params: {
          searchText : ""
        },
        templateUrl: 'com/views/monitor/view.html',
        controller: 'ViewCtrl as viewCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/viewController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })
      .state('main.monitorSingle', {
        url: 'monitorSingle',
        params: {
          searchText : ""
        },
        templateUrl: 'com/views/monitor/viewSingle.html',
        controller: 'ViewCtrl as viewCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/viewController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })
      .state('main.fan', {
        url: 'fan',
        params: {
          searchText : ""
        },
        templateUrl: 'com/views/monitor/fan.html',
        controller: 'FanCtrl as fanCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/fanController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })
      .state('main.fanSingle', {
        url: 'fanSingle',
        params: {
          searchText : ""
        },
        templateUrl: 'com/views/monitor/fanSingle.html',
        controller: 'FanCtrl as fanCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/fanController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })
      .state('main.rmg', {
        url: 'rmg',
        params: {
          searchText : ""
        },
        templateUrl: 'com/views/monitor/rmg.html',
        controller: 'RmgCtrl as rmgCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/rmgController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })
      .state('main.rmgSingle', {
        url: 'rmgSingle',
        params: {
          searchText : ""
        },
        templateUrl: 'com/views/monitor/rmgSingle.html',
        controller: 'RmgCtrl as rmgCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/rmgController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })

      // Dashboard
      .state('main.dashboard', {
        url: 'dashboard',
        templateUrl: 'com/views/monitor/dashboard.html',
        controller: 'DashCtrl as dashCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/monitor/dashboardController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                name: 'dark-new',
                files: [
                  'library/js/dark-new.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })


      // setting
      .state('main.setting', {
        url: 'setting',
        templateUrl: 'com/views/setting/list.html',
        controller: 'ListCtrl as listCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/setting/listController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })

      // sys setting
      .state('main.sysset', {
        url: 'sysset',
        templateUrl: 'com/views/setting/sysset.html',
        controller: 'SysCtrl as sysCtrl',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/setting/sysController.js',
              {
                name: 'ui.select',
                files: [
                  'library/js/libs/select.js'
                ]
              },
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })

      // user
      .state('main.user', {
        url: 'user',
        controller: 'UserCtrl as userCtrl',
        templateUrl: 'com/views/user/user.html',
        resolve: {
          loadScript: function ($ocLazyLoad) {
            return $ocLazyLoad.load([
              'com/controllers/user/userController.js',
              {
                serie: true,
                name: 'daterangepicker',
                files: [
                  'library/js/libs/daterangepicker.js',
                  'library/js/pixeladmin/directives/angular-daterangepicker.js',
                ]
              }
            ]);
          }
        }
      })

  }

  function run($rootScope, $state, DataTransfer) {
    $rootScope.$state = $state;
    $rootScope.reflashTime = 1000;
    $rootScope.hdlimit = 10;
    $rootScope.setups = { 
      sortColumn: '', reverse: '', searchText: {}, 
      range: { startDate: moment().subtract(2, 'days'), endDate: moment() }
    };

    DataTransfer.get('reflashTime').then(function (response) {
      console.log(response);
      $rootScope.reflashTime = response.times;
    })
    
    $rootScope.daterangepickerLocale = {
      "format": "YYYY-MM-DD",
      "separator": " - ",
      "applyLabel": "套用",
      "cancelLabel": "取消",
      "fromLabel": "從",
      "toLabel": "至",
      "customRangeLabel": "自訂範圍",
      "weekLabel": "W",
      "daysOfWeek": [ "日","一","二","三","四","五","六" ],
      "monthNames": [ "一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月" ],
      "firstDay": 1
    };


    $rootScope.$on('$stateChangeStart', function(event, toState, toParams, fromState, fromParams) {
      if (window.Pace && typeof window.Pace.restart === 'function') {
        window.Pace.restart();
      }

      DataTransfer.get('session').then(function (response) {
        if (response.uid) {
          $rootScope.authenticated = true;
          $rootScope.uid = response.uid;
          $rootScope.name = response.name;
          $rootScope.email = response.email;
          $rootScope.auth = response.auth;
        }else{
          var nextUrl = toState.url;
          if (nextUrl == '/login') {

          }else{
            $state.go("login");
          }
        }
      });

      DataTransfer.get('spaceCheck').then((resp) => {
        $rootScope.freeSpace = resp.trim();
        
        DataTransfer.get('getSysSet?key=hdlimit').then((resp) => {
          if(resp!=""){
            $rootScope.hdlimit = resp;
          }

          DataTransfer.get('spaceCheckByte').then((resp) => {
            if(resp){
              alert("請注意。你的硬碟空間已低於 "+$rootScope.hdlimit+" G。");
            }
          });
        });
        
      });


    });

  }


  function searchFilter($filter) {
    return function(items, searchfilter) {
      var isSearchFilterEmpty = true;
      angular.forEach(searchfilter, function(searchstring) {   
        if(typeof searchstring !== 'undefined' && searchstring.length > 0){
          isSearchFilterEmpty= false;
        }
      });
      if(!isSearchFilterEmpty){
        var result = [];  
        angular.forEach(items, function(item) {  
          var isFound = false;
          angular.forEach(item, function(term,key) {                         
            if(term != null &&  !isFound){
              term = term.toString();
              term = term.toLowerCase();
              angular.forEach(searchfilter, function(searchstring) {      
                searchstring = searchstring.toLowerCase();
                if(searchstring !="" && term.indexOf(searchstring) !=-1 && !isFound){
                  result.push(item);
                  isFound = true;
                }
              });
            }
          });
        });
        return result;
      }else{
        return items;
      }
    }
  };

  angular.module('mainApp')
    .config(['$stateProvider', '$locationProvider', '$urlRouterProvider', '$ocLazyLoadProvider', config])
    .run(['$rootScope', '$state', 'DataTransfer', run])
    .filter('searchFilter', searchFilter);

})();