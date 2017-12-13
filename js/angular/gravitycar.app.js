var gravitycar = angular.module('gravitycar', ['ngRoute']);
gravitycar.config(['$routeProvider', '$locationProvider', function ($routeProvider, $locationProvider) {
    $locationProvider.hashPrefix('');
    $routeProvider
    .when('/:module', {templateUrl: 'js/angular/shared/templates/layouts/list.html', controller: 'ModuleListController'})
    .when('/:module/:record_id', {templateUrl: 'js/angular/shared/templates/layouts/edit.html', controller: 'ModuleDetailController'})
    .otherwise({redirectTo: '/Users'});
}]);

gravitycar.controller('ModuleListController', function($http, $routeParams) {
    this.module = $routeParams['module'];
    this.view = 'list';
    this.fields = gc.app.layoutdefs[this.module][this.view].fields;
    this.propdefs = gc.app.layoutdefs[this.module].propdefs;
    
    // vc = view controller
    var vc = this;
    $http({method: "GET", url: "rest/" + this.module}).then(function(response) {
        vc.data = response.data;
        vc.status = response.status;
    });
});


gravitycar.controller('ModuleDetailController', function($http, $routeParams) {
    this.module = $routeParams['module'];
    this.record_id = $routeParams['record_id'];
    this.view = 'detail';
    this.fields = gc.app.layoutdefs[this.module][this.view].fields;
    this.propdefs = gc.app.layoutdefs[this.module].propdefs;
    
    var vc = this;
    $http({method: "GET", url: "rest/" + this.module + '/' + this.record_id}).then(function(response) {
        vc.data = response.data;
        vc.status = response.status;
    });
    
    this.save = function() {
        var config = {
            headers: {
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(this.data),
        }
        
        var url = "rest/" + this.module;
        if (!_.isUndefined(this.record_id)) {
            url += "/" + this.record_id;
        }
        
        var self = this;
        $http.post(url, JSON.stringify(this.data), config).then(
            function(data, status, headers, config) {
                //self.data = JSON.decode(data);
        }, function (data, status, headers, config) {
            console.log(data);
            console.log(status);
            console.log(headers);
            console.log(config);
        });
    }
});