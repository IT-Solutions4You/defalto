/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Knowledge files (Pro/partials/KnowledgeFiles.tpl): the company ones in the
 * admin AI settings, the personal ones in My Settings. Works on every
 * .its4you-knowledge block of the page, also one loaded later (the My
 * Settings panel is rebuilt on each opening and calls init() again).
 */
/** @var MailManager_ProKnowledge_Js */
Vtiger.Class('MailManager_ProKnowledge_Js', {

    EXTENSIONS: ['md', 'markdown', 'txt', 'html', 'htm'],

    registered: false,

    /**
     * Draws the blocks present now and listens for the clicks of any block
     * (a static "init" would run at class creation in jQuery.Class)
     */
    initialize: function () {
        const self = this;

        jQuery('.its4you-knowledge').each(function () {
            self.render(jQuery(this), jQuery(this).data('files') || []);
        });

        if (self.registered) {
            return;
        }

        self.registered = true;
        self.registerUpload();
        self.registerDelete();
    },

    /**
     * Uploads the picked files of a block
     */
    registerUpload: function () {
        const self = this;

        jQuery(document).on('change', '.its4you-knowledge-input', function () {
            const input = jQuery(this),
                block = input.closest('.its4you-knowledge'),
                files = Array.prototype.slice.call(this.files || []);

            input.val('');

            if (!files.length) {
                return;
            }

            const wrong = files.filter(function (file) {
                return self.EXTENSIONS.indexOf(String(file.name).split('.').pop().toLowerCase()) === -1;
            });

            if (wrong.length) {
                app.helper.showErrorNotification({message: block.data('badType')});
                return;
            }

            const formData = new FormData();
            formData.append('module', 'MailManager');
            formData.append('action', 'ProKnowledge');
            formData.append('mode', 'upload');
            formData.append('scope', block.data('scope'));
            jQuery.each(files, function (index, file) {
                formData.append('file[]', file, file.name);
            });
            self.send(block, {url: 'index.php', type: 'POST', data: formData, processData: false, contentType: false});
        });
    },

    /**
     * Deletes a knowledge file after a confirmation
     */
    registerDelete: function () {
        const self = this;

        jQuery(document).on('click', '.its4you-knowledge-delete', function () {
            const link = jQuery(this),
                block = link.closest('.its4you-knowledge');

            app.helper.showConfirmationBox({message: app.vtranslate('JS_KNOWLEDGE_DELETE_CONFIRM')}).then(function () {
                self.send(block, {
                    data: {
                        module: 'MailManager',
                        action: 'ProKnowledge',
                        mode: 'delete',
                        scope: block.data('scope'),
                        document: link.data('document')
                    }
                });
            });
        });
    },

    /**
     * @param {jQuery} block
     * @param {object} request app.request.post parameters
     */
    send: function (block, request) {
        const self = this;

        app.helper.showProgress();
        app.request.post(request).then(function (err, data) {
            app.helper.hideProgress();

            if (err || !data) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                return;
            }

            if (!data.success) {
                app.helper.showErrorNotification({message: app.htmlEncode(data.message) || app.vtranslate('JS_SAVE_FAILED')});
            }

            self.render(block, data.files || []);
        });
    },

    /**
     * @param {jQuery} block
     * @param {object[]} files [{id, title, filename, size, chunks, waiting, url}]
     */
    render: function (block, files) {
        const body = block.find('.its4you-knowledge-table tbody').empty();

        jQuery.each(files, function (index, file) {
            const ready = file.chunks > 0 && file.waiting === 0;

            jQuery('<tr></tr>')
                .append(jQuery('<td class="its4you-knowledge-actions"></td>').append(
                    jQuery('<a href="javascript:void(0);" class="its4you-knowledge-delete"><i class="fa-solid fa-trash"></i></a>')
                        .attr('data-document', file.id)
                        .attr('title', block.data('delete'))
                ))
                .append(jQuery('<td></td>')
                    .append(jQuery('<a target="_blank" rel="noopener"></a>').attr('href', file.url).text(file.title))
                    .append(jQuery('<span class="text-secondary"></span>').text(' — ' + file.filename + ', ' + file.size)))
                .append(jQuery('<td class="its4you-knowledge-state"></td>')
                    .addClass(ready ? 'text-success' : 'text-secondary')
                    .text(ready ? String(block.data('ready')).replace('%s', file.chunks) : block.data('waiting')))
                .appendTo(body);
        });
        block.find('.its4you-knowledge-table').toggleClass('d-none', !files.length);
        block.find('.its4you-knowledge-empty').toggleClass('d-none', files.length > 0);
    }
}, {});

// the My Settings panel (another script) calls window.MailManager_ProKnowledge.init()
window.MailManager_ProKnowledge = {
    init: function () {
        MailManager_ProKnowledge_Js.initialize();
    }
};

jQuery(function () {
    MailManager_ProKnowledge_Js.initialize();
});
