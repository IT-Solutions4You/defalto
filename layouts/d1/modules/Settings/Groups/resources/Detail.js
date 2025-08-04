/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger.Class("Settings_Groups_Detail_Js", {}, {

    init: function () {
        this.addComponents();
    },

    addComponents: function () {
        this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
    },

});