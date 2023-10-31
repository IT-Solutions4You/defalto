/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_List_Js("Inventory_List_Js", {

},
        {

            showQuickPreviewForId: function(recordId, appName, templateId) {
                var self = this;
                var vtigerInstance = Vtiger_Index_Js.getInstance();
                vtigerInstance.showQuickPreviewForId(recordId, self.getModuleName(), app.getAppName(), templateId);
            },
            
            registerEvents: function() {
                this._super();
            }

        });
