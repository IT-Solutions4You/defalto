/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_Base_Js */
Vtiger.Class('Vtiger_Base_Js', {}, {

    _components: {},

    addComponents: function () {
    },

    init: function () {
        this.addComponents();
    },

    intializeComponents: function () {
        for (var componentName in this._components) {
            var componentInstance = this._components[componentName];
            componentInstance.registerEvents();
        }
    },
});