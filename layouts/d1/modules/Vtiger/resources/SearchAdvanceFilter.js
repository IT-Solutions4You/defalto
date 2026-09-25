/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

//Search Advance Filter useful for adavance search 
/** @var Vtiger_SearchAdvanceFilter_Js */
Vtiger_AdvanceFilter_Js('Vtiger_SearchAdvanceFilter_Js', {}, {

    /**
     * Function to get the advance filter values
     * Keep outgoing connectors between populated groups and clear the final connector.
     *
     * @params cleanGroupConditions <Boolean> - states whether to clean group conditions or not -- default true
     *   clears the connector after the last populated group
     */
    getValues: function (cleanGroupConditions) {

        if (typeof cleanGroupConditions == 'undefined') {
            cleanGroupConditions = true;
        }

        var values = this._super();

        if (!cleanGroupConditions) {
            return values;
        }

        const groupKeys = Object.keys(values).filter(function (key) {
            return !jQuery.isEmptyObject(values[key]['columns']);
        });

        // Empty intermediate groups must not discard the preceding group's connector.
        if (groupKeys.length) {
            delete values[groupKeys[groupKeys.length - 1]]['condition'];
        }

        return values;
    }
});

