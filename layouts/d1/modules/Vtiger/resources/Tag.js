/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_Tag_Js */
Vtiger.Class("Vtiger_Tag_Js", {}, {

    editTagTemplate: '<div class="popover" role="tooltip"><div class="arrow"></div>\n\
                                <form onsubmit="return false;">\n\
                                    <div class="popover-content"></div>\n\
                                </form>\n\
                                </div>',
    editTagContainerCached: false,

    init: function () {
        this.editTagContainerCached = jQuery('.editTagContainer');
    },

    saveTag: function (callerParams) {
        let aDeferred = jQuery.Deferred(),
            params = {
                'module': app.getModuleName(),
                'action': 'TagCloud',
                'mode': 'saveTags'

            };
        params = jQuery.extend(params, callerParams);

        app.helper.showProgress();
        app.request.post({'data': params}).then(function (error, data) {
            app.helper.hideProgress();

            if (!error) {
                aDeferred.resolve(data);
            } else {
                aDeferred.reject(error);
            }
        });

        return aDeferred.promise();
    },

    updateTag: function (callerParams) {
        var aDeferred = jQuery.Deferred();
        var params = {
            'module': app.getModuleName(),
            'action': 'TagCloud',
            'mode': 'update'
        }
        params = jQuery.extend(params, callerParams);
        app.request.post({'data': params}).then(function (error, data) {
            if (error == null) {
                aDeferred.resolve(data);
            } else {
                aDeferred.reject(error);
            }
        });
        return aDeferred.promise();
    },

    constructTagElement: function (params) {
        let tagElement = jQuery(jQuery('#dummyTagElement').html()).clone(true);
        tagElement.attr('data-id', params.id).attr('data-type', params.type);
        tagElement.find('.tagLabel').html(params.name);
        return tagElement
    },

    addTagsToShowAllTagContainer: function (tagsList) {
        let showAllTagContainer = jQuery('.showAllTagContainer'),
            viewAllTagContainer = jQuery('.viewAllTagsContainer'),
            currentTagHolder = showAllTagContainer.find('.currentTag'),
            viewAllCurrentTagHolder = viewAllTagContainer.find('.currentTag'),
            currentTagMenu = showAllTagContainer.find('.currentTagMenu');

        for (let index in tagsList) {
            let tagInfo = tagsList[index],
                tagId = tagInfo.id;

            if (currentTagHolder.find('[data-id="' + tagId + '"]').length > 0) {
                continue;
            }

            let newTagEle = this.constructTagElement(tagInfo);
            currentTagHolder.append(newTagEle);
            viewAllCurrentTagHolder.append(newTagEle.clone());
            currentTagMenu.find('[data-id="' + tagId + '"]').closest('li.tag-item').remove();
        }
    },

    removeTagsFromShowTagContainer: function (tagsList, container) {
        var showAllTagContainer = (typeof container === 'undefined') ? jQuery('.showAllTagContainer') : container;
        var currentTagHolder = showAllTagContainer.find('.currentTag');
        var currentTagMenu = showAllTagContainer.find('.currentTagMenu');

        for (var index in tagsList) {
            var tagInfo = tagsList[index];
            var tagId = tagInfo.id;

            var tagEle = currentTagHolder.find('[data-id="' + tagId + '"]');
            if (tagEle.length <= 0) {
                continue;
            }
            tagEle.find('.editTag,.deleteTag').remove();
            var newTagLiEle = jQuery('<li class="tag-item list-group-item"> <a style="margin-left:0px"></a> </li>').find('a').html(tagEle).closest('li');
            currentTagMenu.find('ul').append(newTagLiEle);
        }
    },
    viewAllTags: function (container) {
        let self = this,
            recordId = app.getRecordId(),
            params = {
                module: app.getModuleName(),
                record: recordId ? recordId : null,
                view: 'Detail',
                mode: 'showAllTagsModalWindow',
            };

        app.request.post({data: params}).then(function (error, data) {
            self.viewAllTagsModal($(data));
        });
    },
    viewAllTagsModal: function (container) {
        // There is no delete option from view All Tags
        container.find(".deleteTag").remove();

        app.helper.showModal(container.find('.modal-dialog'), {
            'cb': function (modalContainer) {
                let registerViewAllTagEvents = function (modalContainer) {
                    let currentTagHolder = modalContainer.find('.currentTag');
                    app.helper.showScroll(currentTagHolder);
                }
                registerViewAllTagEvents(modalContainer);
            }
        });
    },
    showAllTags: function (container, callerParams) {
        let self = this,
            recordId = app.getRecordId(),
            params = {
                module: app.getModuleName(),
                record: recordId ? recordId : null,
                view: 'Detail',
                mode: 'showTagsModalWindow',
                deleteOldTags: 'Detail' === app.getViewName() ? '1' : '0',
            };

        app.request.post({data: params}).then(function (error, data) {
            self.showAllTagsModal(container, callerParams, $(data));
        });
    },
    showAllTagsModal: function (container, callerParams, showTagModal) {
        let self = this;

        app.helper.showModal(showTagModal.find('.modal-dialog'), {
            'cb': function (modalContainer) {
                let registerShowAllTagEvents = function (modalContainer) {
                    let currentTagsSelected = [],
                        currentTagHolder = modalContainer.find('.currentTag'),
                        deletedTags = [];

                    modalContainer.find('.dropdown-menu').on('click', function (e) {
                        e.stopPropagation();
                    });

                    modalContainer.find('.currentTagMenu').off('click', 'li > a').on('click', 'li > a', function (e) {
                        let element = jQuery(e.currentTarget),
                            selectedTag = jQuery(element.html());

                        selectedTag.append('<i class="editTag fa fa-pencil"></i><i class="deleteTag fa fa-times"></i>');
                        currentTagsSelected.push(selectedTag.data('id'));
                        element.remove();
                        currentTagHolder.append(selectedTag);
                    });

                    let form = modalContainer.find('form'),
                        tagList = modalContainer.find('.tagListSelect'),
                        newTagList = modalContainer.find('input[name="createNewTag"]');

                    vtUtils.showSelect2ElementView(tagList);

                    form.off('submit').on('submit', function (e) {
                        e.preventDefault();
                        let formData = form.serializeFormData(),
                            modalContainerClone = modalContainer.clone(true),
                            saveParams = {};

                        app.helper.hideModal();

                        if (typeof callerParams !== 'undefined') {
                            saveParams = callerParams;
                        }

                        saveParams['tagsList'] = tagList.val();
                        saveParams['newTagList'] = newTagList.val().split(',');
                        saveParams['newTagType'] = formData['visibility'];
                        saveParams['deleteOldTags'] = formData['deleteOldTags'];

                        self.saveTag(saveParams).then(function (data) {
                            app.event.trigger('post.MassTag.save', modalContainerClone, data);
                        });

                        return false;
                    });

                    modalContainer.off('click', '.deleteTag').on('click', '.deleteTag', function (e) {
                        let currenttarget = jQuery(e.currentTarget),
                            currentTagHolder = currenttarget.closest(".currentTag"),
                            tag = currenttarget.closest('.tag'),
                            deletedTagId = tag.data('id'),
                            index = currentTagsSelected.indexOf(deletedTagId);

                        //if the tag is currently selected then remove it from currently selected list
                        if (index >= 0) {
                            currentTagsSelected.splice(index, 1);
                        } else {
                            deletedTags.push(deletedTagId);
                        }

                        let tagInfo = {
                            'id': deletedTagId
                        };

                        self.removeTagsFromShowTagContainer(new Array(tagInfo), modalContainer);
                    });
                }

                registerShowAllTagEvents(modalContainer);
            }
        });
    },

    registerShowMassTagListener: function () {
        let self = this;

        app.event.on('Request.MassTag.show', function (e, container, saveParams) {
            if (typeof container == 'undefined') {
                container = jQuery('body');
            }

            self.showAllTags(container, saveParams);
        });
    },

    registerEditTagEvents: function () {
        const self = this;

        jQuery(document).on('click', '.editTag', function (e) {
            let element = jQuery(e.currentTarget),
                tag = element.closest('.tag'),
                editTagContainer = self.editTagContainerCached.clone();

            editTagContainer.find('[name="id"]').val(tag.data('id'));
            editTagContainer.find('[name="tagName"]').val(tag.find('.tagLabel').text());

            if (tag.attr('data-type') === 'public') {
                editTagContainer.find('[type="checkbox"]').prop('checked', true);
            } else {
                editTagContainer.find('[type="checkbox"]').prop('checked', false);
            }

            editTagContainer.removeClass('hide');

            let container = element.closest('.tagContainer'),
                placement = 'bottom',
                config = {
                    'content': editTagContainer,
                    'html': true,
                    'placement': placement,
                    'animation': true,
                    'trigger': 'manual',
                    'container': container
                };

            element.popover(config);
            element.popover('show');
        });

        jQuery(document).on('click', '.editTagContainer .saveTag', function (e) {
            let element = jQuery(e.currentTarget),
                editTagContainer = element.closest('.editTagContainer'),
                tagName = editTagContainer.find('[name="tagName"]').val();

            if (tagName.trim() === '') {
                let message = app.vtranslate('JS_PLEASE_ENTER_VALID_TAG_NAME');
                app.helper.showErrorNotification({'message': message});
                return;
            }

            let valueParams = {};
            valueParams['name'] = editTagContainer.find('[name="tagName"]').val();
            let visibility = 'private';

            if (editTagContainer.find('[name="visibility"][type="checkbox"]').is(':checked')) {
                visibility = editTagContainer.find('[name="visibility"][type="checkbox"]').val();
            }

            valueParams.visibility = visibility;
            let tagId = editTagContainer.find('[name="id"]').val();
            valueParams.id = tagId;
            self.updateTag(valueParams).then(function (data) {
                let tagElement = jQuery('[data-id="' + tagId + '"]');
                tagElement.find('.tagLabel').text(data.name);
                tagElement.attr('data-type', data.type);

                let popOverId = element.closest('.popover').attr('id');
                jQuery('[aria-describedby="' + popOverId + '"]').popover('hide');
            }, function (error) {
                app.helper.showAlertBox({'message': error.message});
            });
        });

        jQuery(document).on('click', '.editTagContainer .cancelSaveTag', function (e) {
            let element = jQuery(e.currentTarget),
                popOverId = element.closest('.popover').attr('id');

            jQuery('[aria-describedby="' + popOverId + '"]').popover('hide');
        });

        jQuery(document).on('keyup', '.editTagContainer [name="tagName"]', function (e) {
            (e.keyCode || e.which) === 13 && jQuery(e.target).closest('.editTagContainer').find('.saveTag').trigger('click');
        });
    },

    registerViewAllTagsListener: function () {
        var self = this;
        app.event.on('Request.AllTag.show', function (e, container) {
            if (typeof container == 'undefined') {
                container = jQuery('body');
            }
            self.viewAllTags(container);
        });
    },

    registerEvents: function () {
        this.registerShowMassTagListener();
        this.registerEditTagEvents();
        this.registerViewAllTagsListener();
    }
});

