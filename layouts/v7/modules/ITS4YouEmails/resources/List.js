/** *******************************************************************************
 * The content of this file is subject to the ITS4YouEmails license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */
/** @var ITS4YouEmails_List_Js */
Vtiger_List_Js('ITS4YouEmails_List_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerListColumnsEvents();
    },
    registerListColumnsEvents: function() {
        const self = this;

        self.updateEmailsRecords();

        app.event.on('post.listViewFilter.click', function (event, searchRow) {
            self.updateEmailsRecords();
        });
    },
    updateEmailsRecords: function () {
        if ('Emails' === $('[name="targetModule"]').val()) {
            $('.listViewEntries').each(function() {
                let row = $(this),
                    id = row.data('id'),
                    url = 'index.php?module=Emails&view=Detail&record=' + id;

                row.attr('data-recordurl', url);
                row.find('a').attr('href', url);
            });
        }
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