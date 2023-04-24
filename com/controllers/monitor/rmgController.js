(function() {

    function RmgCtrl(DataTransfer, $rootScope, $scope , $state, $filter, $interval) {

        var self = this;

        if($state.params.searchText) {
            self.searchText = $state.params.searchText;
        }

        self.searchLocation = new Array();
        self.viewData = new Array();
        self.locationData = new Array();
        self.rmData = {};
        
        $rootScope.viewContentId = 0;
        
        function getLocationList() {
            DataTransfer.post('rmglocationList', {}).then(function (response) {
                self.locationData = response;
            })
        }
        
        function getViewList() {
            DataTransfer.post('rmgViewList', {}).then(function (response) {
                self.viewData = response;
                //console.log(response)
            })
        }
        getLocationList();
        getViewList();
        var autoGetMainData = $interval(getViewList, $rootScope.reflashTime);
        
        $('#modal-rmg').on('shown.bs.modal', function (e) {
            $interval.cancel(autoGetMainData);
        })

        $('#modal-rmg').on('hidden.bs.modal', function (e) {
            autoGetMainData = $interval(getViewList, $rootScope.reflashTime);
        })

        $scope.$on('$destroy', function() {
            $interval.cancel(autoGetMainData);
        })

        self.closeRMGAlarm = function(uid,rmip,port,rmID, $event){
            port = port.split("-");
            var buzzerState = $event.target.value;
            // console.log(uid,port,rmID,rmip, buzzerState);
            DataTransfer.post('closeRMGAlarm', { uid: uid , rmip : rmip, channel:port[0] ,rmgid : port[1], rmID: rmID, buzzerState: buzzerState } ).then(function (response) {
                //console.log(response)
            if(buzzerState == 1) {
                $event.target.value = 0;
                $event.target.textContent = '開啟警報功能';
            } else {
                $event.target.value = 1;
                $event.target.textContent = '關閉警報功能';
            }
            });
        }

        self.getRMGContent = function(uid){
            DataTransfer.post('getRMGContent', { uid: uid } ).then(function (response) {
                self.rmData = response;
                console.log(response)
                $('#modal-rmg').modal('show');
            });
            
        }

        self.modifyRMGContent = function(){
            console.log(self.rmData)
            var postData = {
                rmuid:self.rmData.rmuid,
                mute:self.rmData.mute,
                //rmID:self.rmData.rmID,
                channeldata:new Array()
            };

            for(i=0;i<self.rmData.rmglist.length;i++){
                postData.channeldata.push({
                    rmguid:self.rmData.rmglist[i].uid,
                    alarm:self.rmData.rmglist[i].alert_max,
                    pause:self.rmData.rmglist[i].pause,
                    name:self.rmData.rmglist[i].name,
                    location:self.rmData.rmglist[i].location,
                })
            }
            console.log(postData)
            DataTransfer.post('modifyRMGContent', postData ).then(function (response) {
                //console.log(response)
            });

            $('#modal-rmg').modal('hide');
        }

        self.ranges = {
            '今日': [ moment(), moment() ],
            '昨日': [ moment().subtract(1, 'days'), moment().subtract(1, 'days') ],
            '最近3日': [ moment().subtract(2, 'days'), moment() ]
        };
        
        self.rangeCallback = function(start, end) {
            $rootScope.setups.range.startDate = moment(start);
            $rootScope.setups.range.endDate = moment(end);
            $('#rmgdaterangepicker-btn').html(start.format('YYYY-MM-DD') + ' ~ ' + end.format('YYYY-MM-DD'));
        };
    
        self.rangeCallback(moment($rootScope.setups.range.startDate), moment($rootScope.setups.range.endDate));

        self.getRmgReport = function() {
            var port = self.rmData.port.split("-");
            DataTransfer.post('getRmgReport', { 
                rmID:self.rmData.rmID,
                rmguid:port[1],
                startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
                endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
            }).then(function (response) {
                var anchor = angular.element('<a/>');
                anchor.attr({
                href: 'data:attachment/csv;charset=big5,' + encodeURI(response),
                target: '_blank',
                download: "current_history_rmg_"+self.rmData.rmID +'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
                })[0].click();
            })
        };
        self.getRmgAlarmReport = function() {
            var port = self.rmData.port.split("-");
            DataTransfer.post('getRmgAlarmReport', { 
                rmuid:self.rmData.rmuid,
                rmguid:port[1],
                startDate: $rootScope.setups.range.startDate.format('YYYY-MM-DD'), 
                endDate: $rootScope.setups.range.endDate.format('YYYY-MM-DD')
            }).then(function (response) {
                var anchor = angular.element('<a/>');
                anchor.attr({
                href: 'data:attachment/csv;charset=big5,' + encodeURI(response),
                target: '_blank',
                download: "RmgAlarmReport"+self.rmData.rmip.replaceAll(".", "_")+"_"+port[1]+'('+$rootScope.setups.range.startDate.format('YYYYMMDD')+'-'+$rootScope.setups.range.endDate.format('YYYYMMDD')+').csv'
                })[0].click();
            })
        };

    }

    angular.module('mainApp')
    .controller('RmgCtrl', [ 'DataTransfer', '$rootScope', '$scope', '$state', '$filter', '$interval', RmgCtrl ]);
})();