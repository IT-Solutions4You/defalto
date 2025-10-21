/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger_Detail_Js("EMAILMaker_Detail_Js", {

    myCodeMirror: false,

    changeActiveOrDefault: function (templateid, type) {
        if (templateid != "") {
            var url = 'index.php?module=EMAILMaker&action=IndexAjax&mode=ChangeActiveOrDefault&templateid=' + templateid + '&subjectChanged=' + type;
            AppConnector.request(url).then(function () {
                location.reload(true);
            });
        }
    },
    setPreviewContent: function (type) {
        var previewcontent = jQuery('#previewcontent_' + type).html();
        var previewFrame = document.getElementById('preview_' + type);
        var preview = previewFrame.contentDocument || previewFrame.contentWindow.document;
        preview.open();
        preview.write(previewcontent);
        preview.close();
        jQuery('#previewcontent_' + type).html('');
    },
}, {
    registerEditConditionsClickEvent: function () {
        jQuery('.editDisplayConditions').on('click', function (e) {
            var element = jQuery(e.currentTarget);
            window.location.href = element.data('url');
        });
    },
    deleteDocumentRelation: function (relatedId) {

        var aDeferred = jQuery.Deferred();
        var recordId = app.getRecordId();

        var params = {};
        params['mode'] = "deleteRelation";
        params['module'] = "EMAILMaker";
        params['action'] = 'RelationAjax';
        params['related_module'] = "Documents";
        params['relationId'] = recordId;
        params['src_record'] = recordId;
        params['related_record_list'] = JSON.stringify([relatedId]);

        app.request.post({"data": params}).then(
            function (err, responseData) {
                aDeferred.resolve(responseData);
            },
            function (textStatus, errorThrown) {
                aDeferred.reject(textStatus, errorThrown);
            }
        );
        return aDeferred.promise();
    },
    registerDocumentsWidget() {
        let self = this, container = this.getContainer();

        container.on('click', 'a.relationDelete', function (e) {
            e.stopImmediatePropagation();
            let element = jQuery(e.currentTarget),
                key = self.getDeleteMessageKey(),
                message = app.vtranslate(key),
                row = element.closest('tr'),
                relatedId = row.data('id');

            app.helper.showConfirmationBox({'message': message}).then(function () {
                self.deleteDocumentRelation(relatedId).then(function () {
                    location.reload(true);
                });
            });
        });

        container.on('click', 'button.selectTemplateRelation', function (e) {
            let moduleName = jQuery(e.currentTarget).attr('data-modulename'),
                relatedController = self.getRelatedController(moduleName);

            if (relatedController) {
                let popupParams = relatedController.getPopupParams(), popup = new Vtiger_Popup_Js();

                popup.showPopup(popupParams, 'post.Documents.List.click');
            }
        });

        app.event.on('post.Documents.List.click', function (event, data) {
            app.helper.hideModal();

            let responseData = JSON.parse(data), idList = [];

            for (let id in responseData) {
                idList.push(id);
            }

            let relatedController = self.getRelatedController('Documents');

            if (relatedController) {
                relatedController.addRelations(idList).then(function () {
                    location.reload();
                });
            }
        });
    },
    registerEvents: function () {
        var thisInstance = this;
        thisInstance.registerEditConditionsClickEvent();
        thisInstance.registerDocumentsWidget();
    }
});  