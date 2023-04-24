(function() {

  function ViewCtrl(DataTransfer, $rootScope, $scope, $state, $filter, $interval) {
    
    var self = this;
    // self.alarmPid = sessionStorage.getItem('alarmPid') || undefined;
    if($state.params.searchText) {
      self.searchText = $state.params.searchText;
    }
    
    self.searchLocation = new Array();

    self.locationData = new Array();
    self.modelData = new Array();
    self.viewData = new Array();

    $rootScope.viewContentId = 0;
    self.fanData = {};
    self.sensorData = {};
    self.itemData = {};
    self.sortColumn = "uid";
    self.reverse = false;
    self.isUpdate = 0;

    // 取得 DOM 模型並 echarts 初始化
    var todayChartDom = document.getElementById("today-chart");
    var todayChart = echarts.init(todayChartDom);

    var historyChartDom = document.getElementById("history-chart");
    var historyChart = echarts.init(historyChartDom);

    var alertChartDom = document.getElementById("alert-chart");
    var alertChart = echarts.init(alertChartDom);


    self.todayChartData = { time: new Array(), value: new Array() };
    self.historyChartData = { time: new Array(), value: new Array() };
    self.alertChartData = { time: new Array(), warning: new Array(), alert: new Array() };

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

    function getViewList() {
      DataTransfer.post('viewList', {}).then(function (response) {

        // soundItem = response.find((o)=> o.status==2 || o.status==3);
        // if(soundItem!==undefined){
        //   console.log(sessionStorage.getItem('alarmPid'))
        //   if(sessionStorage.getItem('alarmPid')==undefined && sessionStorage.getItem('alarmPid')!='OK'){
        //     sessionStorage.setItem('alarmPid', 'OK');
        //     DataTransfer.get('playsound', {}).then((resp)=>{
        //       sessionStorage.setItem('alarmPid', resp);
        //     });
        //   }
        // }else{
        //   if(sessionStorage.getItem('alarmPid')!==undefined){
        //     DataTransfer.post('killplaysound', { pid: sessionStorage.getItem('alarmPid') }).then((resp)=>{
        //       sessionStorage.removeItem('alarmPid');
        //     });
        //   }
        // }

        // DataTransfer.get('deleteLastOne',{});
        self.viewData = response;
        //console.log(response);
      })
    }

    getLocationList();
    getModelList();
    getViewList();

    self.setAlarmBuzzer = function(uid, rmID, rmIP) {
      if(sessionStorage.getItem('alarmPid')!==undefined){
        DataTransfer.post('killplaysound', { pid: sessionStorage.getItem('alarmPid') }).then((resp)=>{
          sessionStorage.removeItem('alarmPid');
        });
      }
      DataTransfer.post('setAlarmBuzzer', { uid: uid, rmID: rmID, rmIP: rmIP });
      // DataTransfer.get('deleteLastOne',{});
      // DataTransfer.get('getSysSet?key=soundAlarm').then((resp) => {
      //   console.log(resp);
      // });
      // DataTransfer.post('setSysSet', {key: 'soundAlarm', val: '1'}).then((resp) => {
      //   console.log(resp);
      // });
    }

    self.toContentModal = function(uid){
      $rootScope.viewContentId = uid;
      $('#modal-sensor').modal('show');
    }

    self.ranges = {
      '今日': [ moment(), moment() ],
      '昨日': [ moment().subtract(1, 'days'), moment().subtract(1, 'days') ],
      '最近3日': [ moment().subtract(2, 'days'), moment() ]
    };

    self.rangeCallback = function(start, end) {
      if((moment(end).diff(moment(start), "day"))>30) {
        $('#modal-alert').modal('show');
      }else{

        $rootScope.setups.range.startDate = moment(start);
        $rootScope.setups.range.endDate = moment(end);
        $('#daterangepicker-btn').html(start.format('YYYY-MM-DD') + ' ~ ' + end.format('YYYY-MM-DD'));
        $('#daterangepicker-alert').html(start.format('YYYY-MM-DD') + ' ~ ' + end.format('YYYY-MM-DD'));

        if($scope.tabsDrdnActive===2){ getHistoryChartData() };
        if($scope.tabsDrdnActive===3){ getAlertData() };

      }
      
    };

    self.rangeCallback(moment($rootScope.setups.range.startDate), moment($rootScope.setups.range.endDate));


    $('#modal-fan').on('shown.bs.modal', function (e) {
    	DataTransfer.post('viewFanContent', {}).then(function (response) {
        self.fanData = response;
      })
    })

    var autoGetItemData = '';
    var autoGetTodayData = '';
    $('#modal-sensor').on('shown.bs.modal', function (e) {
      $scope.tabsDrdnActive=1;
      viewSensorContent();
      autoGetItemData = $interval(viewSensorContent, $rootScope.reflashTime);
      getTodayChartData();
      $interval.cancel(autoGetMainData);
      getViewList();
      autoGetMainData = $interval(getViewList, $rootScope.reflashTime);
    })

    function viewSensorContent() {
      if ($rootScope.viewContentId != 0){
        DataTransfer.post('viewSensorContent', { uid: $rootScope.viewContentId } ).then(function (response) {
          //console.log(response);
          self.sensorData = response;
        })
      }
    }

    $('#modal-sensor').on('hidden.bs.modal', function (e) {
      $interval.cancel(autoGetItemData);
      $interval.cancel(autoGetTodayData);
    })

    var autoGetMainData = $interval(getViewList, $rootScope.reflashTime);
    $scope.$on('$destroy', function() {
      $interval.cancel(autoGetMainData);
      $interval.cancel(autoGetItemData);
      $interval.cancel(autoGetTodayData);
    });

    // 至資料庫取得當月資料並將圖表設定套用至圖表中
    function getTodayChartData(){
      
      if ($rootScope.viewContentId != 0){

        DataTransfer.post('sensorToday', { uid: $rootScope.viewContentId }).then(function (response) {
          self.todayChartData.time = response.time;
          self.todayChartData.value = response.value;
          todayChart.resize()
          setTodayChartData();
        })
        
      }
    }

    // 每月圖表設定
    function setTodayChartData(){
      // 重新初始化
      echarts.dispose(todayChartDom);
      todayChart = echarts.init(todayChartDom);

      // todayChart 建立圖表參數
      var todayChartOption = {
        color: ['#1976e2'],
        xAxis: {
            type: 'category',
            data: self.todayChartData.time
        },
        yAxis: {
            type: 'value'
        },
        dataZoom: [
          {
            type: 'inside',
            start: 90,
            end: 100
          }, 
          {
            start: 90,
            end: 100,
        }],
        series: [{
            data: self.todayChartData.value,
            type: 'line',
            animation: false
        }],
        grid: {
          top: 10,
          bottom: 60,
          right: 20
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'cross',
            animation: false,
            label: {
              backgroundColor: '#505765'
            }
          }
        },
      };

      // todayChart 套用圖表參數與資料
      if (todayChartOption && typeof todayChartOption === "object") {
        todayChart.setOption(todayChartOption, true);
      }

    }

    // 至資料庫取得區間資料並將圖表設定套用至圖表中
    function getHistoryChartData(){
      //let checkUID = $rootScope.viewContentId

      if ($rootScope.viewContentId != 0){
        DataTransfer.post('sensorRange', { 
          uid: $rootScope.viewContentId,
          startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
          endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
        }).then(function (response) {
          //console.log(response);
          self.historyChartData.time = response.time;
          self.historyChartData.value = response.value;
          historyChart.resize();
          setHistoryChartData();
        })
      }

    }

    // 每月圖表設定
    function setHistoryChartData(){
      // 重新初始化
      echarts.dispose(historyChartDom);
      historyChart = echarts.init(historyChartDom);

      // todayChart 建立圖表參數
      var historyChartOption = {
        color: ['#1976e2'],
        xAxis: {
            type: 'category',
            data: self.historyChartData.time
        },
        yAxis: {
            type: 'value'
        },
        dataZoom: [
          {
            type: 'inside',
            start: 90,
            end: 100
          }, 
          {
            start: 90,
            end: 100,
        }],
        series: [{
            data: self.historyChartData.value,
            type: 'line',
            animation: false
        }],
        grid: {
          top: 10,
          bottom: 60,
          right: 20
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'cross',
            animation: false,
            label: {
              backgroundColor: '#505765'
            }
          }
        },
      };

      // historyChart 套用圖表參數與資料
      if (historyChartOption && typeof historyChartOption === "object") {
        historyChart.setOption(historyChartOption, true);
      }

    }


    // 至資料庫取得當月資料並將圖表設定套用至圖表中
    function getAlertData(){
      if ($rootScope.viewContentId != 0){
        DataTransfer.post('sensorAlert', { 
          uid: $rootScope.viewContentId,
          startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
          endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
        }).then(function (response) {
          self.alertChartData.time = response.time;
          self.alertChartData.warning = response.warning;
          self.alertChartData.alert = response.alert;
          alertChart.resize();
          setAlertData();
        })
      }
    }

    // 每月圖表設定
    function setAlertData(){
      // 重新初始化
      echarts.dispose(alertChartDom);
      alertChart = echarts.init(alertChartDom);

      // alertChart 建立圖表參數
      var alertChartOption = {
        color: ['#ffd055', '#ff5353'],
        legend: {
          data: ['告警', '警示'],
          bottom: 10
        },
        xAxis: {
            type: 'category',
            data: self.alertChartData.time
        },
        yAxis: {
            type: 'value'
        },
        series: [{
          name: '告警',
          data: self.alertChartData.warning,
          type: 'bar',
          animation: false,
          stack: 'count'
        },{
          name: '警示',
          data: self.alertChartData.alert,
          type: 'bar',
          animation: false,
          stack: 'count'
        }],
        grid: {
          top: 10,
          bottom: 60,
          right: 20
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'cross',
            animation: false,
            label: {
              backgroundColor: '#505765'
            }
          }
        },
      };

      // alertChart 套用圖表參數與資料
      if (alertChartOption && typeof alertChartOption === "object") {
        alertChart.setOption(alertChartOption, true);
      }

    }

    $scope.$watch('tabsDrdnActive', function(newValue, oldValue) {
      $scope.autoGetTodayData;
      $scope.autoGetHistoryData;
      $scope.autoGetAlertData;
      
    	if (newValue===1) {	
        $interval.cancel($scope.autoGetHistoryData);
        $interval.cancel($scope.autoGetAlertData);
        getTodayChartData();
        $scope.autoGetTodayData = $interval(getTodayChartData, 10000);
      }

    	if (newValue===2) {
        $interval.cancel($scope.autoGetTodayData);
        $interval.cancel($scope.autoGetAlertData);
        getHistoryChartData();
        $scope.autoGetHistoryData = $interval(getHistoryChartData, 10000);
      }

    	if (newValue===3) {	
        $interval.cancel($scope.autoGetTodayData);
        $interval.cancel($scope.autoGetHistoryData);
        getAlertData();
        $scope.autoGetAlertData = $interval(getAlertData, 10000);
      }
    })

    self.downloadRangeCSV = function(name, uid, rmID, port) {
      //console.log(name, uid, rmID, port);
      DataTransfer.post('downloadRangeCSV', { 
        uid: uid,
        rmID: rmID,
        port: port,
        startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
        endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
      }).then(function(response) {
        //console.log(response);
        var anchor = angular.element('<a/>');
        anchor.attr({
          href: 'data:attachment/csv;charset=big5,' + encodeURI(response),
          target: '_blank',
          download: name+'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
        })[0].click();
      });
    }

    self.downloadAlarmRangeCSV = function(name, uid, rmID, port) {
      DataTransfer.post('downloadAlarmRangeCSV', { 
        uid: uid,
        rmID: rmID,
        port: port,
        startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
        endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
      }).then(function(response) {
        var anchor = angular.element('<a/>');
        anchor.attr({
          href: 'data:attachment/csv;charset=big5,' + encodeURI(response),
          target: '_blank',
          download: 'Alert-'+name+'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
        })[0].click();
      });
    }

    self.getListContent = function(uid) {
      DataTransfer.post('listContent', { uid: uid } ).then(function (response) {
        //console.log("list ",response);
        self.itemData = response;
        $('#modal-modify').modal('show');
      })
    }

    self.modifyContent = function() {
      // console.log(self.isUpdate);
      //console.log("isUpdate ", self.itemData);
      DataTransfer.post('modifyContent', self.itemData ).then(function (response) {
        //console.log(response);
        if(self.isUpdate){
          DataTransfer.post('setRMval', {
            uid: self.itemData.uid, 
            rmIP: self.itemData.rmIP,
            rmID: self.itemData.rmID,
            max: self.itemData.alert_max, 
            min: self.itemData.alert_min, 
            ch: self.itemData.port
          });
          self.isUpdate = false;
        }

        if(response!=='rmIP重複'){
          $('#modal-modify').modal('hide');
        }else{
          $('#modal-edit-alert').modal('show');
        }
      });
    }

    self.orderByColume = function(sortColumn){
      (self.sortColumn === sortColumn) ? self.reverse = !self.reverse : self.reverse = false;
      self.sortColumn = sortColumn;
    }

  }

  angular.module('mainApp')
    .controller('ViewCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', '$interval', ViewCtrl ]);

})();
