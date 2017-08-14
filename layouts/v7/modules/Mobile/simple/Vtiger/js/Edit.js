/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

mobileapp.controller('VtigerEditController', function ($scope, $api, $mdToast, $animate, $filter) {
    var url = jQuery.url();
    $scope.module = url.param('module');
    $scope.record = url.param('record');
    $scope.describeObject = null;
    $scope.fields = null;
    $scope.createable = null;
    $scope.updateable = null;
    $scope.deleteable = null;
    $scope.fieldsData = null;
    $scope.editdata = [];
    $scope.fruitsobj = {
        fruitsList: loadFruits(),
        selectedFruits: [],
        selectedItem: 'Kiwi',
        searchText: null
                //querySearch: querySearch
   };
    $scope.numberChips = [];
    $scope.numberChips2 = [];
    $scope.numberBuffer = '';
    /*$scope.testDate = new Date();*/
    $api('describe', {module: $scope.module}, function (e, r) {
        $scope.describeObject = r.describe;
        $scope.fields = $scope.describeObject.fields;
        console.log($scope.fields);
        $scope.createable = $scope.describeObject.createable;
        $scope.updateable = $scope.describeObject.updateable;
        $scope.deleteable = $scope.describeObject.deleteable;
        if($scope.record){
            $scope.loadFields();
        }
        else{
            $scope.createRecord();
        }
    });
    
    $scope.createRecord = function(){
        var processedData = [];
        for (var index in $scope.fields) {
            var value = '';
            processedData.push({
                label: $scope.fields[index].label, // Actual field Label
                valuelabel: value, // Value to be shown on UI
                value: value, // Value to be stored on backend
                name: $scope.fields[index].name, // Backend name for the field - forms will use this value
                fieldType: $scope.fields[index].type.name, // Type of the field
                fieldFormat: $scope.fields[index].type.format, // Format of the field ex: date -> dd/mm/yyyy
                editable: $scope.fields[index].editable, // Returns true if field is editable
                mandatory: $scope.fields[index].mandatory, // Returns true if field is mandatory
                picklist: $scope.fields[index].type.picklistValues, // Picklist values for type picklist others will get null
                dateFieldValue: new Date(), // Creates date object for md-datepicker
                referenceModules: $scope.fields[index].type.refersTo // Reference module names for reference fields
            });
        }
        $scope.fieldsData = processedData;
    };
    
    $scope.gobacktoUrl = function () {
        window.history.back();
    };
    
    $scope.loadFields = function () {
        $api('fetchRecord', {module: $scope.module, record: $scope.record, view_mode:'web'}, function (e, r) {
            var processedData = [];
            for (var index in $scope.fields) {
                var value = r.record[$scope.fields[index].name];
                var field = $scope.fields[index];
                if(field && (field.type.name == 'reference' || field.type.name == 'owner' || field.type.name == 'ownergroup')){
                    value = value.label;
                }
                processedData.push({
                    label: $scope.fields[index].label, // Actual field Label
                    valuelabel: value, // Value to be shown on UI
                    value: value, // Value to be stored on backend
                    name: $scope.fields[index].name, // Backend name for the field - forms will use this value
                    fieldType: $scope.fields[index].type.name, // Type of the field
                    fieldFormat: $scope.fields[index].type.format, // Format of the field ex: date -> dd/mm/yyyy
                    editable: $scope.fields[index].editable, // Returns true if field is editable
                    mandatory: $scope.fields[index].mandatory, // Returns true if field is mandatory
                    picklist: $scope.fields[index].type.picklistValues, // Picklist values for type picklist others will get null
                    dateFieldValue: new Date(value), // Creates date object for md-datepicker
                    referenceModules: $scope.fields[index].type.refersTo // Reference module names for reference fields
                });
            }
            $scope.fieldsData = processedData;
        });
    };

    $scope.setdateString = function (val) {
        spformat = $filter('ngdateformat')(val.fieldFormat);
        newDateString = val.dateFieldValue.toString(spformat);
        val.value = newDateString;
        val.valuelabel = newDateString;
    };

    $scope.saveThisRecord = function () {
        $scope.editdata = {};
        for (var index in $scope.fieldsData) {
            $scope.editdata[$scope.fieldsData[index].name] = $scope.fieldsData[index].value;
        }
        $api('saveRecord', {module: $scope.module, record: $scope.record, values: $scope.editdata}, function (e, r) {
            if (r) {
                var toast = $mdToast.simple().content('Record Saved Successfully!').position($scope.getToastPosition()).hideDelay(1000);
                $mdToast.show(toast);
            } else {
                var toast = $mdToast.simple().content('Some thing went wrong ! \n Save is not Succesfull.').position($scope.getToastPosition()).hideDelay(1000);
                $mdToast.show(toast);
            }
        });
    };

    $scope.toastPosition = {
        bottom: true,
        top: false,
        left: false,
        right: true
    };

    $scope.getToastPosition = function () {
        return Object.keys($scope.toastPosition)
                .filter(function (pos) {
                    return $scope.toastPosition[pos];
                }).join('');
    };

    $scope.getMatchedReferenceFields = function (query) {
        arr = loadContacts();
        var results = query ? arr.filter(createFilterFor(query)) : [];
        return results;
    };
    function createFilterFor(query) {
        var lowercaseQuery = angular.lowercase(query);
        return function filterFn(option) {
            var lowercaseOption = angular.lowercase(option.label);
            return (lowercaseOption.indexOf(lowercaseQuery) === 0);
        };
    }


    $scope.querySearch2 = function (query) {
        arr = loadFruits();
        var results = query ? arr.filter(createFilterFor2(query)) : [];
        return results;
    };
    function createFilterFor2(query) {
        var lowercaseQuery = angular.lowercase(query);
        return function filterFn(fruit) {
            return (fruit.value.indexOf(lowercaseQuery) === 0);
        };
    }
});

loadFruits = function () {
    fruits = [
        'Apple',
        'Banana',
        'Bilberry',
        'Blackcurrant',
        'Cantaloupe',
        'Cherry',
        'Date',
        'Dragonfruit',
        'Gooseberry',
        'Grape',
        'Grapefruit',
        'Guava',
        'Jackfruit',
        'Kiwi fruit',
        'Kiwano',
        'Kumquat',
        'Lemon',
        'Lime',
        'Mango',
        'Marion berry',
        'Cantaloupe',
        'Honeydew',
        'Water melon',
        'Nectarine',
        'Olive',
        'Orange',
        'Papaya',
        'Peach',
        'Pear',
        'Pineapple',
        'Pomegranate',
        'Quince',
        'Raspberry',
        'Rambutan',
        'Redcurrant',
        'Strawberry',
        'Squash'
    ];
    arry = [];
    for (var fruit in fruits) {
        arry.push({
            value: fruits[fruit].toLowerCase(),
            display: fruits[fruit]
        });
    }

    return arry;
};

loadContacts = function () {
    return [
        {id: '63', label: 'Mary Smith'},
        {id: '65', label: 'Linda Williams'},
        {id: '68', label: 'Elizabeth Brown'},
        {id: '71', label: 'Maria Miller'},
        {id: '72', label: 'Susan Wilson'},
        {id: '66', label: 'Barbara Jones'},
        {id: '73', label: 'Margaret Moore'},
        {id: '74', label: 'Dorothy Taylor'}
    ];
};