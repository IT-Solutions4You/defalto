/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger.Class('Project_ExportChart_Js', {}, {
    registerEvents() {
        const container = document.querySelector('.project-chart[data-print="true"]');

        if (container && !container.projectChartController) {
            new Project_Chart_Js().registerEvents(container);
        }
    }
});

jQuery(function () {
    new Project_ExportChart_Js().registerEvents();
});
