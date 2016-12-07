/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

Settings_Vtiger_List_Js("Settings_CronTasks_List_Js",{
   
	triggerEditEvent: function(editUrl) {
       
        app.request.post({"url":editUrl}).then(function(err, data) {
			if(data) {
                app.helper.showModal(data);
            }
           var listViewInstance = Settings_CronTasks_List_Js.getInstance();
            listViewInstance.registerSaveEvent();
		});
	}
},{
    
    registerSaveEvent : function() {
		var thisInstance = this;
        jQuery('#cronJobSaveAjax').on('submit',function(e){
            e.preventDefault();
            var form = jQuery(e.currentTarget);
            app.helper.showProgress();
            app.helper.hideModal();
            var params = form.serializeFormData();
            
            app.request.post({"data":params}).then(function(err,data){

                if(err === null) {
                    app.helper.hideProgress();
                    thisInstance.loadListViewRecords();
                }else{
                    app.helper.showErrorNotification({'message':err.message});
                }
            });			
			e.preventDefault();
		});
	},
    
    loadListViewRecords : function(urlParams) {
        var thisInstance = this;
        var aDeferred = jQuery.Deferred();
        var defParams = this.getDefaultParams();
        if(typeof urlParams === "undefined") {
            urlParams = {};
        }
        if(typeof urlParams.search_params === "undefined") {
            urlParams.search_params = JSON.stringify(thisInstance.getListSearchParams(false));
        }
        urlParams = jQuery.extend(defParams, urlParams);
        app.helper.showProgress();
		
        app.request.get({data:urlParams}).then(function(err, res){
            aDeferred.resolve(res);
            var container = thisInstance.getListViewContainer();
			container.html(res);
            thisInstance.registerSortableEvent(); 
            app.helper.hideProgress();
            app.event.trigger('post.listViewFilter.click');
		});
        return aDeferred.promise();
    },
    
    
    registerSortableEvent : function() {
		var thisInstance = this;
		var sequenceList = {};
		var tbody = jQuery('tbody');
		
		tbody.sortable({
			'helper' : function(e,ui){
				//while dragging helper elements td element will take width as contents width
				//so we are explicity saying that it has to be same width so that element will not
				//look like distrubed
				ui.children().each(function(index,element){
					element = jQuery(element);
					element.width(element.width());
				});
                return ui;
			},
			'containment' : tbody,
			'revert' : true,
			update: function(e, ui ) {
				jQuery('tbody tr').each(function(i){
					sequenceList[++i] = jQuery(this).data('id');
                    
				});
				var params = {
					sequencesList : JSON.stringify(sequenceList),
					module : app.getModuleName(),
					parent : app.getParentModuleName(),
					action : 'UpdateSequence'
				};
				app.request.post({"data":params}).then(function(err,data) {
                    if(err === null){
						thisInstance.loadListViewRecords(); 
                    }
				});
			}
		});
	},

	registerEvents : function() {
		this.registerSortableEvent();
		this.registerPostListLoadListener();
	}
});