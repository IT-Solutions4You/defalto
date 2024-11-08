/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Settings_Vtiger_Index_Js("Settings_InventoryItem_Index_Js", {}, {
    registerEvents: function() {
        this._super();
        this.registerSortableVisibleFields();
    },

    registerSortableVisibleFields: function() {
        let self = this;

        $(document).ready(function() {
            // Initialize select2 on page load
            $('#selectedFields').select2({
                placeholder: 'Select fields',
                allowClear: true
            });

            self.initSortable();

            // Handle adding new options
            $('#selectedFields').on('select2:select', function(e) {
                // Get the newly selected option
                let addedOption = e.params.data;
                // Check if the option is already in the select box to avoid duplicates
                let existingOption = $('#selectedFields option[value="' + addedOption.id + '"]');
                if (existingOption.length === 0) {
                    // If the option isn't in the select box, create a new option
                    let newOption = new Option(addedOption.text, addedOption.id, true, true);
                    $('#selectedFields').append(newOption);
                    // Trigger the select2 change to ensure it's updated
                    $(this).trigger('change.select2');
                    self.initSortable();
                }
            });
        });
    },

    initSortable: function() {
        let self = this;

        // Destroy the current sortable functionality first
        $("#selectedFields").next(".select2-container").find(".select2-selection__rendered").sortable("destroy");

        // Reapply sortable after every change
        $("#selectedFields").next(".select2-container").find(".select2-selection__rendered")
            .sortable({
                containment: "parent",
                update: function(event, ui) {
                    let sortedSelectedValues = [];
                    $(".select2-selection__choice").each(function() {
                        sortedSelectedValues.push($(this).attr("title").trim());
                    });
                    let allOptions = $('#selectedFields option').clone();
                    $('#selectedFields').empty();
                    sortedSelectedValues.forEach(function(value) {
                        allOptions.filter(function() {
                            return $(this).text().trim() === value;
                        }).prop('selected', true).appendTo('#selectedFields');
                    });
                    allOptions.each(function() {
                        if (!sortedSelectedValues.includes($(this).text().trim())) {
                            $(this).prop('selected', false).appendTo('#selectedFields');
                        }
                    });
                    $('#selectedFields').trigger('change.select2');
                    self.initSortable();
                }
            });
    }
});