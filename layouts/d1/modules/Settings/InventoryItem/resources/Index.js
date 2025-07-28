/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o <info@its4you.sk>
 *
 * This file is licensed under the GNU AGPL v3 License.
 * For the full copyright and license information, please view the LICENSE-AGPLv3.txt
 * file that was distributed with this source code.
 */

/** @var Settings_InventoryItem_Index_Js **/
Settings_Vtiger_Index_Js("Settings_InventoryItem_Index_Js", {}, {
    registerEvents: function () {
        this._super();
        this.makeColumnListSortable();
        this.registerModuleChange();
    },

    makeColumnListSortable: function () {
        const inventoryItemFieldsForm = jQuery('#inventoryItemFields'),
            selectElement = $('#selectedFields'),
            valueElement = $('input[name="columnslist"]', inventoryItemFieldsForm);

        vtUtils.makeSelect2ElementSortable(
            selectElement,
            valueElement,
            function (valueElement) {
                return JSON.parse(valueElement.val());
            },
            function (valueElement, selectedValues) {
                valueElement.val(JSON.stringify(selectedValues));
            }
        );
    },

    registerModuleChange: function () {
        const moduleElement = $('#selectedModule');

        moduleElement.on('change', function () {
            window.location.href = 'index.php?module=InventoryItem&parent=Settings&view=Index&selectedModule=' + moduleElement.val();
        });
    },
});