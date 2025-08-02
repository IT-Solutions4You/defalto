/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Users_Field_Js */
Vtiger_Field_Js('Users_Field_Js', {}, {})

/** @var Users_Picklist_Field_Js */
Vtiger_Field_Js('Users_Picklist_Field_Js', {}, {

    /**
     * Function to get the pick list values
     * @return <object> key value pair of options
     */
    getPickListValues: function () {
        return this.get('picklistvalues');
    },

    /**
     * Function to get the ui
     * @return - select element and chosen element
     */
    getUi: function () {
        let html = '<select  class="select2 inputElement inlinewidth" data-width="80%" name="' + this.getName() + '" id="field_' + this.getModuleName() + '_' + this.getName() + '">',
            pickListValues = this.getPickListValues(),
            selectedOption = app.htmlDecode(this.getValue());

        if (jQuery.trim(selectedOption).length === 0) {
            selectedOption = '&nbsp;';
        }

        html += '';

        for (let option in pickListValues) {
            if (jQuery.trim(option).length === 0) {
                option = '&nbsp;';
            }

            html += '<option value="' + option + '" ';

            if (option == selectedOption) {
                html += ' selected ';
            }

            if (option == '&nbsp;' && (this.getName() == 'currency_decimal_separator' || this.getName() == 'currency_grouping_separator')) {
                html += '>' + app.vtranslate('Space') + '</option>';
            }

            html += '>' + pickListValues[option] + '</option>';
        }

        html += '</select>';

        return this.addValidationToElement(jQuery(html));
    }
});

/** @var Users_Multipicklist_Field_Js */
Vtiger_Multipicklist_Field_Js('Users_Multipicklist_Field_Js', {}, {

    getUi: function () {
        let html = this._super()

        html.data('width', '80%');

        return html;
    }
});