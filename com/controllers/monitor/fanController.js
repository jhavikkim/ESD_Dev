(function() {


  function FanCtrl(DataTransfer, $rootScope, $scope, $state, $filter, $interval) {

    var self = this;

    if($state.params.searchText) {
      self.searchText = $state.params.searchText;
    }

    self.searchLocation = new Array();

    self.locationData = new Array();
    self.modelData = new Array();
    self.viewData = new Array();

    $rootScope.viewContentId = 0;
    $rootScope.fanContentSpeed = 0;

    self.fanData = {};
    self.sensorData = {};
    self.itemData = {};

    // 取得 DOM 模型並 echarts 初始化

    var balanceChartDom = document.getElementById("balance-chart");
    var balanceChart = echarts.init(balanceChartDom);
    
    self.balanceChartData = { time: new Array(), value: new Array() };
    function getLocationList() {
      DataTransfer.post('fanlocationList', {}).then(function (response) {
        self.locationData = response;
      })
    }

    function getModelList() {
      DataTransfer.post('modelList', {}).then(function (response) {
        self.modelData = response;
      })
    }

    function getViewList() {
      DataTransfer.post('fanViewList', {}).then(function (response) {
        self.viewData = response;
        //console.log(response);
      })
    }

    getLocationList();
    getModelList();
    getViewList();
    
    var numberFields = document.querySelectorAll("input[id=FanSpeed]"),
      len = numberFields.length,
      numberField = null;
  
    for (var i = 0; i < len; i++) {
      numberField = numberFields[i];
      numberField.onclick = function() {
        this.setAttribute("step", "10");
      };
      numberField.onkeyup = function(e) {
        //console.log(e.keyCode)
        if (e.keyCode === 38 || e.keyCode === 40) {
          this.setAttribute("step", ".1");
        }
      };
    } 

    self.cleanAlarmLed = function(uid, rmIP, port, rmID) {
      DataTransfer.post('cleanAlarmLed', { uid: uid, rmIP: rmIP, port: port, rmID: rmID}).then(function (response) {
        //console.log(response);
      })
    }
    self.setPower = function(uid, port, rmIP, rmID, isfan, option) {
        DataTransfer.post('setPower', { uid: uid, port: port, rmIP: rmIP, rmID: rmID, isfan: isfan, option: option }).then(function (response) {
        //console.log(response);
      })
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
        $('#daterangepicker-fan, #daterangepicker-btn').html(start.format('YYYY-MM-DD') + ' ~ ' + end.format('YYYY-MM-DD'));
        if(($("#modal-modify").data('bs.modal') || {}).isShown){getbalanceChartData()};
      }
    };

    self.rangeCallback(moment($rootScope.setups.range.startDate), moment($rootScope.setups.range.endDate));

    self.downloadFanHistoryCSV = function(name, uid, rmIP, port) {
      DataTransfer.post('downloadFanHistoryCSV', {
        startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
        endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
      }).then(function(response) {
        var anchor = angular.element('<a/>');
        anchor.attr({
          href: 'data:attachment/csv;charset=big5,' + encodeURI(response),
          target: '_blank',
          download: 'FanHistory-'+'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
        })[0].click();
      });
    }

    self.downloadBalanceCSV = function(name, uid, rmIP, port) {
      //console.log(name, uid, rmIP, port);
      DataTransfer.post('downloadBalanceCSV', { 
        uid: uid,
        rmIP: rmIP,
        port: port,
        name: name,
        startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
        endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
      }).then(function(response) {
        var anchor = angular.element('<a/>');
        anchor.attr({
          href: 'data:attachment/csv;charset=big5,' + encodeURI(response),
          target: '_blank',
          download: name+'_BalanceHistory_' + '_' + rmIP +'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
        })[0].click();
      });
    }

    $('#modal-fan').on('shown.bs.modal', function (e) {
    	DataTransfer.post('viewFanContent', {}).then(function (response) {
        self.fanData = response;
      })
    })

    var autoGetItemData = '';
    

    function viewSensorContent() {
      DataTransfer.post('viewSensorContent', { uid: $rootScope.viewContentId } ).then(function (response) {
        //console.log(response);
        self.sensorData = response;
      })
    }

    var autoGetMainData = $interval(getViewList, $rootScope.reflashTime);
    $scope.$on('$destroy', function() {
      $interval.cancel(autoGetMainData);
      $interval.cancel(autoGetItemData);
      //$interval.cancel(autoGetTodayData);
    });
    
    // 至資料庫取得區間資料並將圖表設定套用至圖表中
    function getbalanceChartData(){
      DataTransfer.post('balanceRange', { 
        uid: $rootScope.viewContentId,
        startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
        endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
      }).then(function (response) {
        //console.log(response);
        self.balanceChartData.time = response.time;
        self.balanceChartData.value = response.value;
        balanceChart.resize();
        setbalanceChartData();
      })
    }

    // 每月圖表設定
    function setbalanceChartData(){
      // 重新初始化
      echarts.dispose(balanceChartDom);
      balanceChart = echarts.init(balanceChartDom);

      // todayChart 建立圖表參數
      var balanceChartOption = {
        color: ['#1976e2'],
        xAxis: {
            type: 'category',
            data: self.balanceChartData.time
        },
        yAxis: {
          show: false,
          type: 'value',
          max: 6,
          min: -6,
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
            data: self.balanceChartData.value,
            type: 'line',
            step: 'Balance',
            animation: false
        }],
        grid: {
          top: 10,
          bottom: 60,
          right: 20
        },
        legend: {
          data: ['Balance']
        },
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'line',
            animation: true,
            label: {
              backgroundColor: '#505765'
            }
          }
        },
      };

      // balanceChart 套用圖表參數與資料
      if (balanceChartOption && typeof balanceChartOption === "object") {
        balanceChart.setOption(balanceChartOption, true);
      }

    }

    $('#modal-modify').on('shown.bs.modal', function (e) {
      getbalanceChartData();
      $scope.autoGetBalanceData = $interval(getbalanceChartData, 10000);
    });

    $('#modal-modify').on('hidden.bs.modal', function (e) {
      $interval.cancel($scope.autoGetBalanceData);
    })

    self.downloadRangeCSV = function(name, uid, rmID, port) {
      DataTransfer.post('downloadRangeCSV', { 
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
          download: name+'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
        })[0].click();
      });
    }
    
    self.getListContent = function(uid, isFan) {
      $rootScope.viewContentId = uid;
      DataTransfer.post('listContent', { uid: uid } ).then(function (response) {
        self.itemData = response;
        //console.log(self.itemData)
        if(isFan ==2 ){        
          $('#modal-modify').modal('show');
        }else{
          $('#fanModal-modify').modal('show');
        }
      })
    }

    self.modifyDFanContent = function() {
      if (self.itemData.Speed == undefined){
        alert("Please Review the Speed Value")
      }else if(self.itemData.Timer == undefined){
        alert("Please Review the CLeaning Time Value")
      }else {

        if ((self.itemData.Speed % 10) !== 0){
          alert("風速必須為 10, 20, 30, ...100 %")
        }else {
          DataTransfer.post('modifyDFanContent', self.itemData ).then(function (response) {
            if(response!=='rmIP重複'){
              $('#modal-modify').modal('hide');
              $('#fanModal-modify').modal('hide');
            }else{
              $('#modal-edit-alert').modal('show');
            }
            //console.log(response);
          })
        }
      }
    }

    self.modifyContent = function() {
      //console.log(self.itemData)
      // console.log(self.itemData.subid)
      DataTransfer.post('modifyContent', self.itemData ).then(function (response) {
        DataTransfer.post('SetNCNO', {
          rmIP: self.itemData.rmIP,
          uid: self.itemData.uid,
          rmID: self.itemData.rmID,
          port: self.itemData.port,
          rmType: self.itemData.rmType,
          subid: self.itemData.subid,
          isNC: self.itemData.isNC
          });
          
        //console.log(response)
        if(response!=='rmIP重複'){
          $('#fanModal-modify').modal('hide');
        }
      });
    }


    self.orderByColume = function(sortColumn){
      (self.sortColumn === sortColumn) ? self.reverse = !self.reverse : self.reverse = false;
      self.sortColumn = sortColumn;
    }

  }

  angular.module('mainApp')
    .controller('FanCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', '$interval', FanCtrl ]);

})();
