/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_List_Js("Inventory_List_Js", {},
    {

        showQuickPreviewForId: function (recordId, appName, templateId) {
            var self = this;
            var vtigerInstance = Vtiger_Index_Js.getInstance();
            vtigerInstance.showQuickPreviewForId(recordId, self.getModuleName(), app.getAppName(), templateId);
        },

        registerEvents: function () {
            this._super();
        }

    });
