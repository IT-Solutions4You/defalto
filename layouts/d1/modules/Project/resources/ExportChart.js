/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
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
