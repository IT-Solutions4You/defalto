/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var ITS4YouEmails_List_Js */
Vtiger_List_Js('ITS4YouEmails_List_Js', {}, {
    registerEvents: function () {
        this._super();
    },
    getDefaultParams: function () {
        let container = this.getListViewContainer(),
            pageNumber = container.find('#pageNumber').val(),
            module = this.getModuleName(),
            parent = app.getParentModuleName(),
            cvId = this.getCurrentCvId(),
            orderBy = container.find('[name="orderBy"]').val(),
            sortOrder = container.find('[name="sortOrder"]').val(),
            appName = container.find('#appName').val();

        return {
            'module': module,
            'parent': parent,
            'targetModule': container.find('[name="targetModule"]').val(),
            'page': pageNumber,
            'view': "List",
            'viewname': cvId,
            'orderby': orderBy,
            'sortorder': sortOrder,
            'app': appName,
            'search_params': JSON.stringify(this.getListSearchParams()),
            'tag_params': JSON.stringify(this.getListTagParams()),
            'nolistcache': (container.find('#noFilterCache').val() == 1) ? 1 : 0,
            'starFilterMode': container.find('.starFilter li.active a').data('type'),
            'list_headers': container.find('[name="list_headers"]').val(),
            'tag': container.find('[name="tag"]').val(),
        };
    }
});