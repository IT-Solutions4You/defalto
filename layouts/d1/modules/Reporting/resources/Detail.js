/*
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Reporting_Detail_Js */
Vtiger_Detail_Js('Reporting_Detail_Js', {}, {
    registerEvents() {
        this._super();
        Reporting_Table_Js.getInstance().registerEvents(this.getContainer());
        this.registerChart();
    },
    registerChart() {
        const self = this;

        self.showChart();

        if (!app.event.required('reporting-detail-chart')) {
            return;
        }

        app.event.on('post.relatedListLoad.click', function () {
            self.showChart();
        });
        app.event.on('post.summarywidget.load', function () {
            self.showChart();
        });
    },
    showChart() {
        const containers = this.getContainer().find('.reportingChart:not(.chartCreated)'),
            palette = ['#0d6efd', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'];

        containers.each(function () {
            const container = $(this),
                canvas = container.find('canvas'),
                dataElement = container.siblings('.reportingChartData');

            if (!canvas.length || !dataElement.length || 'undefined' === typeof Chart) {
                return;
            }

            const chartData = JSON.parse(dataElement.val());

            $.each(chartData.data.datasets, function (index, dataset) {
                if ('pie' === chartData.type || 'doughnut' === chartData.type) {
                    dataset.backgroundColor = palette.slice(0, dataset.data.length);
                } else {
                    dataset.backgroundColor = palette[index % palette.length] + '40';
                    dataset.borderColor = palette[index % palette.length];
                    dataset.borderWidth = 2;
                }
            });

            container.addClass('chartCreated');
            app.helper.showChart(canvas, chartData);
        });
    },
});
