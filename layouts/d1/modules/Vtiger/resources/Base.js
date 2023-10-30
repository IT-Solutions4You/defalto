/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
Vtiger.Class('Vtiger_Base_Js', {},{

    _components : {},

    addComponents : function() {},

    init : function() {
        this.addComponents();
    },

    intializeComponents : function() {
        for(var componentName in this._components) {
            var componentInstance = this._components[componentName];
            componentInstance.registerEvents();
        }
    },
});