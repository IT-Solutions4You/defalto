/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Reporting_Table_Js */
Vtiger.Class('Reporting_Table_Js', {
    instance: false,
    getInstance() {
        if (!this.instance) {
            this.instance = new Reporting_Table_Js();
        }

        return this.instance;
    },
}, {
    registerEvents(container) {
        const eventNamespace = '.reportingTableGroups';

        container
            .off('click' + eventNamespace, '.reportingGroupToggle')
            .on('click' + eventNamespace, '.reportingGroupToggle', function (event) {
                event.preventDefault();
                event.stopPropagation();

                Reporting_Table_Js.getInstance().toggleGroup($(this));
            });
    },
    toggleGroup(toggle) {
        const table = toggle.closest('.renderedTable'),
            groupIndex = toggle.attr('data-reporting-group'),
            rows = table.find('.reportingGroupRecord[data-reporting-group="' + groupIndex + '"]'),
            icon = toggle.find('i'),
            isExpanded = 'true' === toggle.attr('aria-expanded'),
            expandLabel = toggle.attr('data-expand-label'),
            collapseLabel = toggle.attr('data-collapse-label');

        rows.toggleClass('d-none', isExpanded);
        toggle
            .attr('aria-expanded', isExpanded ? 'false' : 'true')
            .attr('aria-label', isExpanded ? expandLabel : collapseLabel)
            .attr('title', isExpanded ? expandLabel : collapseLabel);
        icon
            .toggleClass('bi-chevron-down', !isExpanded)
            .toggleClass('bi-chevron-right', isExpanded);
    },
});
