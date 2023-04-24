(function() {

  function DashCtrl(DataTransfer, $rootScope, $scope, $state, $filter, $interval) {
 
    var self = this;
    
    self.connectData = new Array();
    self.weeklyData = new Array();
    self.monthlyData = new Array();
    self.realTimeData = new Array();
    self.alertLists = new Array();
    self.viewdashboardData = new Array();

    // function getConnectList() {
    //   DataTransfer.post('connectList', {}).then(function (response) {
    //     self.connectData = response;
    //     console.log(response);
    //   })
    // }

    function getWeeklyList() {
      DataTransfer.post('weeklyList', {}).then(function (response) {
        self.weeklyData = response;
        var weeklyOnline = [];
        var weeklyAlarm = [];
        var weeklyOffline = [];
        var itemsProcessed = 0;
        
        response.forEach(function(value){
          itemsProcessed++;
          weeklyOnline.push(value.rmDefault + value.rmiDefault + value.rmgDefault);
          weeklyAlarm.push(value.rmAlarm + value.rmiAlarm + value.rmgAlarm);
          weeklyOffline.push(value.rmOff + value.rmiOff + value.rmgOff);

          if(itemsProcessed === response.length) {
            weeklyStatus_chart(weeklyOnline, weeklyAlarm, weeklyOffline);
          }
        });
      })
    }
    function getMonthlyList() {
        DataTransfer.post('monthlyList', {}).then(function (response) {
            self.monthlyData = response;
            var onlineData = response[0].Normal;
            var alarmData = response[0].Alarm;
            var offlineData = response[0].Offline;
            monthlyChart(onlineData, alarmData, offlineData);
            // console.log(response);
        })
    }

    function getAlertList() {
      DataTransfer.post('alertList', {}).then(function (response) {
          self.alertLists = response;
          if(self.alertLists.length > 3) {
            alertList_marquee();
          }
      })
    }


    function getRealtimeList() {
      DataTransfer.post('realtimeList', {}).then(function (response) {
        self.realTimeData = response;
        var rmTotal = response[0].rmDefault + response[0].rmAlarm + response[0].rmOff;
        var rmiTotal = response[0].rmiDefault + response[0].rmiAlarm + response[0].rmiOff;
        var rmgTotal = response[0].rmgDefault + response[0].rmgAlarm + response[0].rmgOff;
        $scope.rmInUseRate = ((response[0].rmDefault + response[0].rmAlarm) / rmTotal * 100).toFixed(0);
        $scope.rmiInUseRate = ((response[0].rmiDefault + response[0].rmiAlarm) / rmiTotal * 100).toFixed(0);
        $scope.rmgInUseRate = ((response[0].rmgDefault + response[0].rmgAlarm) / rmgTotal * 100).toFixed(0);
        var inuseRate= [$scope.rmInUseRate, $scope.rmiInUseRate, $scope.rmgInUseRate];

        inUse_chart(inuseRate);
        realTime_chart(response);

        // console.log(response);
      })
    }

    function getDailyList() {
      DataTransfer.post('realtimeList', {}).then(function (response) {
        self.realTimeData = response;
        var onlineSum = response[0].rmDefault + response[0].rmiDefault + response[0].rmgDefault;
        var alarmSum = response[0].rmAlarm + response[0].rmiAlarm + response[0].rmgAlarm;
        var offSum = response[0].rmOff + response[0].rmiOff + response[0].rmgOff;
        dailyStatus_chart(onlineSum, alarmSum, offSum);

        // console.log(response);
      })
    }

    // function getViewDashboardList() {
    //     DataTransfer.post('viewDashboardList', {}).then(function (response) {
    //         self.viewdashboardData = response;
    //     })
    // }

    function inUse_chart(inuseRate) {
      var app = {};

      var inUseChartDom = document.getElementById('inUse-chart');
      var inUseChart = echarts.init(inUseChartDom, 'dark-new');
      var option;
      
      const posList = [
        'left',
        'right',
        'top',
        'bottom',
        'inside',
        'insideTop',
        'insideLeft',
        'insideRight',
        'insideBottom',
        'insideTopLeft',
        'insideTopRight',
        'insideBottomLeft',
        'insideBottomRight'
      ];
      app.configParameters = {
        rotate: {
          min: -90,
          max: 90
        },
        align: {
          options: {
            left: 'left',
            center: 'center',
            right: 'right'
          }
        },
        verticalAlign: {
          options: {
            top: 'top',
            middle: 'middle',
            bottom: 'bottom'
          }
        }, 
        position: {
          options: posList.reduce(function (map, pos) {
            map[pos] = pos;
            return map;
          }, {})
        },
        distance: {
          min: 0,
          max: 100
        }
      };
      app.config = {
        rotate: 90,
        align: 'left',
        verticalAlign: 'middle',
        position: 'insideBottom',
        distance: 10,
        onChange: function () {
          const labelOption = {
            rotate: app.config.rotate,
            align: app.config.align,
            verticalAlign: app.config.verticalAlign,
            position: app.config.position,
            distance: app.config.distance
          };
          inUseChartDom.setOption({
            series: [
              {
                label: labelOption
              },
              {
                label: labelOption
              },
              {
                label: labelOption
              },
              {
                label: labelOption
              }
            ]
          });
        }
      };
      const labelOption = {
        show: true,
        position: app.config.position,
        distance: app.config.distance,
        align: app.config.align,
        verticalAlign: app.config.verticalAlign,
        rotate: app.config.rotate,
        formatter: '{c}  {name|{a}}',
        fontSize: 16,
        rich: {
          name: {}
        }
      };
      option = {
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow'
          },
          formatter: '{b1}: {c1}%'
        },
        legend: {
          data: ['Devices', 'In use']
        },
        grid: {
          bottom: 50
        },
        xAxis: [
          {
            type: 'category',
            axisTick: { show: false },
            data: ['ESD', 'Ion', 'GRD']
          }
        ],
        yAxis: [
          {
            type: 'value'
          }
        ],
        series: [
          {
            name: 'Devices',
            type: 'bar',
            barGap: '-100%',
            barCategoryGap: '80%',
            data: [100,100,100],
            itemStyle: {
              color: "#DEDDDD"
            }
          },
          {
            name: 'In use',
            type: 'bar',
            data: inuseRate,
            itemStyle: {
              color: "#285D8D"
            }
          }
        ]
      };
      
      option && inUseChart.setOption(option);

      window.addEventListener('resize',function(){
          inUseChart.resize();
      });
      
    }

    function realTime_chart(response) {
      var alarmData = [response[0].rmgAlarm, response[0].rmiAlarm, response[0].rmAlarm];
      var onlineData = [response[0].rmgDefault,  response[0].rmiDefault, response[0].rmDefault];
      var offData = [(0-response[0].rmgOff),(0 -response[0].rmiOff), (0-response[0].rmOff)];

      var realTimechartDom = document.getElementById('realTime-chart');
      var realTimeChart = echarts.init(realTimechartDom, 'dark-new');
      var option;

      let yAxisData = ['GRD', 'ION', 'ESD'];
      var emphasisStyle = {
        itemStyle: {
          shadowBlur: 10,
          shadowColor: 'rgba(0,0,0,0.3)'
        }
      };
      option = {
        legend: {
          data: ['Alarm', 'Online', 'Off'],
          left: 'center'
        },
        tooltip: {},
        yAxis: {
          data: yAxisData,
          axisLine: { onZero: true },
          splitLine: { show: false },
          splitArea: { show: false }
        },
        xAxis: {},
        grid: {
          bottom: 50
        },
        series: [
          {
            name: 'Alarm',
            type: 'bar',
            stack: 'one',
            emphasis: emphasisStyle,
            data: alarmData,
            showBackground: true,
            itemStyle: {
              color: "rgb(255,0,0)"
            }
          },
          {
            name: 'Online',
            type: 'bar',
            stack: 'two',
            emphasis: emphasisStyle,
            data: onlineData,
            showBackground: true,
            itemStyle: {
              color: "#00B050"
            }
          },
          {
            name: 'Off',
            type: 'bar',
            stack: 'two',
            emphasis: emphasisStyle,
            data: offData,
            showBackground: true,
            itemStyle: {
              color: "#A5A5A5"
            }
          }
        ]
      };
      realTimeChart.on('brushSelected', function (params) {
        var brushed = [];
        var brushComponent = params.batch[0];
        for (var sIdx = 0; sIdx < brushComponent.selected.length; sIdx++) {
          var rawIndices = brushComponent.selected[sIdx].dataIndex;
          brushed.push('[Series ' + sIdx + '] ' + rawIndices.join(', '));
        }
        realTimeChart.setOption({
          title: {
            backgroundColor: '#333',
            text: 'realTimeRate',
            bottom: 0,
            right: '10%',
            width: 100,
            textStyle: {
              fontSize: 12,
              color: '#fff'
            }
          }
        });
      });

      option && realTimeChart.setOption(option);

      window.addEventListener('resize',function(){
        realTimeChart.resize();
      });
    }

    function dailyStatus_chart(onlineSum, alarmSum, offSum) {
  
      var dailychartDom = document.getElementById('daily-chart');
      var dailyChart = echarts.init(dailychartDom, 'dark-new');
      var option;
      
      option = {
        tooltip: {
          trigger: 'item'
        },
        legend: {
          top: 10,
          left: 'center',
          data: ['Online', 'Alarm', 'Offline']
        },
        grid: {
          bottom: 50
        },
        series: [
          {
            name: 'Daily status',
            type: 'pie',
            radius: '50%',
            data: [
              { value: onlineSum, name: 'Online' },
              { value: alarmSum, name: 'Alarm' },
              { value: offSum, name: 'Offline' }
            ],
            color: ['#00B050', 'rgb(255,0,0)',' #A5A5A5'],
            emphasis: {
              itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
              }
            }
          }
        ]
      };
      
      option && dailyChart.setOption(option);

      window.addEventListener('resize',function(){
        dailyChart.resize();
      });
      
    }

    function weeklyStatus_chart(weeklyOnline, weeklyAlarm, weeklyOffline) {
      var weeklyChartDom = document.getElementById('weekly-chart');
      var weeklyChart = echarts.init(weeklyChartDom, 'dark-new');
      var option;

      option = {
        angleAxis: {
          type: 'category',
          data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
        },
        radiusAxis: {},
        polar: {
          center: ["50%", "53%"]
        },
        tooltip: {
          show: true
        },
        series: [
          {
            type: 'bar',
            data: weeklyOnline,
            coordinateSystem: 'polar',
            name: 'Online',
            stack: 'a',
            emphasis: {
              focus: 'series'
            },
            itemStyle: {
              color: "#00B050"
            }
          },
          {
            type: 'bar',
            data: weeklyAlarm,
            coordinateSystem: 'polar',
            name: 'Alarm',
            stack: 'a',
            emphasis: {
              focus: 'series'
            },
            itemStyle: {
              color: "rgb(255,0,0)"
            }
          },
          {
            type: 'bar',
            data: weeklyOffline,
            coordinateSystem: 'polar',
            name: 'Offline',
            stack: 'a',
            emphasis: {
              focus: 'series'
            },
            itemStyle: {
              color: "#A5A5A5"
            }
          }
        ],
        legend: {
          show: true,
          top: 0,
          data: ['Online', 'Alarm', 'Offline']
        }
      };

      option && weeklyChart.setOption(option);

      window.addEventListener('resize',function(){
        weeklyChart.resize();
      });

    }

    function monthlyChart(onlineData, alarmData, offlineData) {
      var monthlyChartDom = document.getElementById('monthly-chart');
      var monthlyChart = echarts.init(monthlyChartDom, 'dark-new');
      var option;
      
      option = {
        tooltip: {
          trigger: 'item'
        },
        legend: {
          top: 10,
          left: 'center',
          data: ['Online', 'Alarm', 'Offline']
        },
        grid: {
          bottom: 50
        },
        series: [
          {
            name: 'Monthly status',
            type: 'pie',
            radius: '50%',
            data: [
              { value: onlineData, name: 'Online' },
              { value: alarmData, name: 'Alarm' },
              { value: offlineData, name: 'Offline' }
            ],
            color: ['#00B050', 'rgb(255,0,0)',' #A5A5A5'],
            emphasis: {
              itemStyle: {
                shadowBlur: 10,
                shadowOffsetX: 0,
                shadowColor: 'rgba(0, 0, 0, 0.5)'
              }
            },
            label: {
                show: true,
                position: 'inside',
                formatter: '{d}%'
            }
          }
        ]
      };
      
      option && monthlyChart.setOption(option);

      window.addEventListener('resize',function(){
        monthlyChart.resize();
      });
      
    };

    function alertList_marquee() {
        // 先取得 marquee_wrapper
        // 接著把 wrapper 中的 item 項目再重覆加入 wrapper 中(等於有兩組內容)
        // 再來取得 marquee 的高來決定每次跑馬燈移動的距離
        // 設定跑馬燈移動的速度及輪播的速度
        var $marqueeWrapper = $('.marquee_wrapper'),
          $marqueeItem = $marqueeWrapper.append($marqueeWrapper.html()).children(),
          _height =  -92,
          scrollSpeed = 600,
          timer,
          speed = 2000 + scrollSpeed;
       
        // 幫左邊 $marqueeItem 加上 hover 事件
        // 當滑鼠移入時停止計時器；反之則啟動
        $marqueeItem.hover(function(){
          clearTimeout(timer);
        }, function(){
          timer = setTimeout(showad, speed);
        });

       
        // 控制跑馬燈移動的處理函式
        function showad(){
          var _now = $marqueeWrapper.position().top / _height;
          _now = (_now + 1) % $marqueeItem.length;
       
          // $marqueeWrapper 移動
          $marqueeWrapper.animate({
            top: _now * _height
          }, scrollSpeed, function(){
            // 如果已經移動到第二組時...則馬上把 top 設 0 來回到第一組
            // 藉此產生不間斷的輪播
            if(_now == $marqueeItem.length / 2){
              $marqueeWrapper.css('top', 0);
            }
          });
       
          // 再啟動計時器
          timer = setTimeout(showad, speed);
        }
       
        // 啟動計時器
        timer = setTimeout(showad, speed);
    }

    function currentTime()
    {
        let time = new Date();   // creating object of Date class
        let dayName=time.getDay();
        let month=time.getMonth();
        let year=time.getFullYear();
        let date=time.getDate();
        let hour = time.getHours();
        let min = time.getMinutes();
        let sec = time.getSeconds();

        var am_pm = "上午";
        if(hour==12)
        am_pm = "下午";
        if (hour > 12) {
        hour -= 12;
        am_pm = "下午";
        }
        if (hour == 0) {
        hour = 12;
        am_pm = "上午";
        }

        hour = hour < 10 ? "0" + hour : hour;
        min = min < 10 ? "0" + min : min;
        sec = sec < 10 ? "0" + sec : sec;

      //value of current time
      let currentTime = hour + ":" + min + ":" + sec +" ";

      // value of present day(Day, Month, Year)
      var months=["January","February","March","April","May","June","July","August","September","October","November","December"];
      var week=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
      var week_ch=["星期日","星期一","星期二","星期三","星期四","星期五","星期六"];

      var presentDay=year+", "+month+"月"+date+"日, "+week_ch[dayName];

      const clock = document.getElementById("time");
      const meridiem = document.getElementById("meridiem");
      const dayIntro=document.getElementById("dayName");
      
      if(clock != null) {
        clock.innerHTML = currentTime;
        meridiem.innerHTML = am_pm
        dayIntro.innerHTML = presentDay;
      }
      
      clearInterval(currentTime);
    }

    setInterval(currentTime, 1000);
    currentTime();

    // getConnectList();
    getDailyList();
    getWeeklyList();
    getMonthlyList();
    getRealtimeList();
    getAlertList();
    //getViewDashboardList();

    var autoGetDailyData = $interval(getDailyList, 10000);
    var autoGetWeeklyData = $interval(getWeeklyList, 10000);
    var autoGetMonthlyData = $interval(getMonthlyList, 10000);
    var autoGetAlertListData = $interval(getAlertList, 10000);
    
    var autoGetRealtimeData = $interval(getRealtimeList, $rootScope.reflashTime);

    $scope.$on('$destroy', function() {
      $interval.cancel(autoGetDailyData);
      $interval.cancel(autoGetWeeklyData);
      $interval.cancel(autoGetMonthlyData);
      $interval.cancel(autoGetAlertListData);
      $interval.cancel(autoGetRealtimeData);
  });

  $scope.$on('ngRepeatFinished', function(ngRepeatFinishedEvent) {
    if(self.alertLists.length > 3) {
      alertList_marquee();
    }
});

  }

  angular.module('mainApp')
    .controller('DashCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', '$interval', DashCtrl ]);

})();
