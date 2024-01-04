/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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

    registerCreateStyleEvent: function (container) {
        var self = this;
        jQuery('#js-create-style', container).on('click', function () {
            var form = container.find('form');
            if (form.valid()) {
                self._createStyle(form);
            }
        });
    },

    _createStyle: function (form) {
        var formData = form.serializeFormData();
        app.helper.showProgress();

        formData["stylecontent"] = this.myCodeMirror.getValue();

        app.request.post({'data': formData}).then(function () {
            location.reload(true);
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

    deleteStyleRelation: function (relatedStyleId) {

        var aDeferred = jQuery.Deferred();
        var recordId = app.getRecordId();

        var params = {};
        params['mode'] = "deleteRelation";
        params['module'] = "ITS4YouStyles";
        params['action'] = 'RelationAjax';
        params['related_module'] = "EMAILMaker";
        params['relationId'] = recordId;
        params['src_record'] = recordId;
        params['related_record_list'] = JSON.stringify([relatedStyleId]);

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

    registerStyleRecord: function (container) {
        var self = this;

        container.on('click', 'a[name="styleEdit"]', function (e) {
            var element = jQuery(e.currentTarget);
            window.location.href = element.data('url');
        });

        container.on('click', 'a.relationDelete', function (e) {
            e.stopImmediatePropagation();
            var element = jQuery(e.currentTarget);
            var key = self.getDeleteMessageKey();
            var message = app.vtranslate(key);
            var row = element.closest('tr');
            var relatedId = row.data('id');
            var relatedModuleName = row.data('module');

            app.helper.showConfirmationBox({'message': message}).then(
                function () {
                    if (relatedModuleName == "Documents") {
                        self.deleteDocumentRelation(relatedId).then(function () {
                            location.reload(true);
                        });
                    } else {
                        self.deleteStyleRelation(relatedId).then(function () {
                            location.reload(true);
                        });
                    }
                },
                function (error, err) {
                }
            );
        });
    },

    registerCodeMirorEvent: function () {
        var TextArea = document.getElementById("ITS4YouStyles_editView_fieldName_stylecontent");
        this.myCodeMirror = CodeMirror.fromTextArea(TextArea, {
            mode: 'shell',
            lineNumbers: true,
            styleActiveLine: true,
            matchBrackets: true,
            height: 'dynamic'
        });
    },

    registerAddStyleClickEvent: function () {

        var self = this;

        jQuery('.addStyleContentBtn').on('click', function () {

            var recordId = app.getRecordId();
            var params = {
                module: 'ITS4YouStyles',
                view: 'AddStyleAjax',
                source_module: 'EMAILMaker',
                source_id: recordId
            };

            app.helper.showProgress();
            app.request.get({data: params}).then(function (err, response) {
                var callback = function (container) {
                    self.registerCreateStyleEvent(container);
                    self.registerCodeMirorEvent(container);
                };
                var data = {};
                data['cb'] = callback;
                app.helper.hideProgress();
                app.helper.showModal(response, data);
            });
        });
    },

    registerEventForSelectingRelatedStyle: function () {
        var thisInstance = this;
        var detailViewContainer = thisInstance.getDetailViewContainer();
        detailViewContainer.on('click', 'button.selectTemplateRelation', function (e) {
            var modulename = jQuery(e.currentTarget).attr('data-modulename');
            var relatedController = thisInstance.getRelatedController(modulename);
            if (relatedController) {
                var popupParams = relatedController.getPopupParams();
                var popupjs = new Vtiger_Popup_Js();
                popupjs.showPopup(popupParams, "post." + modulename + ".List.click");
            }
        });
    },

    registerEvents: function () {
        var thisInstance = this;
        var detailViewContainer = this.getContentHolder();
        thisInstance.registerEditConditionsClickEvent();
        thisInstance.registerAddStyleClickEvent();
        thisInstance.registerStyleRecord(detailViewContainer);
        thisInstance.registerEventForSelectingRelatedStyle();

        //thisInstance.setPreviewContent('body');
        app.event.on("post.Documents.List.click", function (event, data) {
            var responseData = JSON.parse(data);
            var idList = [];
            for (var id in responseData) {
                idList.push(id);
            }
            app.helper.hideModal();
            var relatedController = thisInstance.getRelatedController('Documents');
            if (relatedController) {
                relatedController.addRelations(idList).then(function () {
                    location.reload();
                });
            }
        });

        app.event.on("post.ITS4YouStyles.List.click", function (event, data) {
            var responseData = JSON.parse(data);
            var idList = [];
            for (var id in responseData) {
                idList.push(id);
            }
            app.helper.hideModal();
            var relatedController = thisInstance.getRelatedController('ITS4YouStyles');
            if (relatedController) {
                relatedController.addRelations(idList).then(function () {
                    location.reload();
                });
            }
        });
    }
});  