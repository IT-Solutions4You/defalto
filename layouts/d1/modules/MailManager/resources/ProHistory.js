/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Activity history of one mailbox (Pro/partials/History.tpl) — search, action
 * filter and "Load more"; My Settings and the admin's mailbox history
 */
/** @var MailManager_ProHistory_Js */
Vtiger.Class('MailManager_ProHistory_Js', {}, {

    /** Pause after the last key before the search runs (ms) */
    SEARCH_DELAY: 400,

    /**
     * @param {jQuery} container .its4you-history
     */
    init: function (container) {
        if (!container.length) {
            return;
        }
        this.container = container;
        this.searchTimer = null;
        this.loadRequest = 0;
        this.registerEvents();
    },

    /**
     * Loads a page of entries: a new filter replaces the rows, "Load more"
     * appends the next page
     *
     * @param {boolean} append
     */
    load: function (append) {
        let self = this,
            tbody = self.container.find('.its4you-history-table tbody'),
            moreButton = self.container.find('.its4you-history-more'),
            offset = append ? tbody.find('tr').not('.its4you-history-empty').length : 0,
            // a slower answer of an older filter must not overwrite the newer one
            requestId = ++self.loadRequest;

        moreButton.prop('disabled', true);
        app.request.post({data: {
            module: 'MailManager',
            action: 'ProHistory',
            mailbox: self.container.data('mailbox'),
            search: self.container.find('.its4you-history-search').val(),
            history_action: self.container.find('.its4you-history-action').val(),
            offset: offset
        }}).then(function (err, data) {
            if (requestId !== self.loadRequest) {
                return;
            }
            moreButton.prop('disabled', false);
            if (err || !data || !data.success) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_FAILED')});
                return;
            }
            if (append) {
                tbody.append(data.html);
            } else {
                tbody.html(data.html);
            }
            moreButton.toggleClass('hide', !data.has_more);
        });
    },

    /**
     * Search, action filter and "Load more"
     */
    registerEvents: function () {
        let self = this;

        self.container.on('input', '.its4you-history-search', function () {
            clearTimeout(self.searchTimer);
            self.searchTimer = setTimeout(function () {
                self.load(false);
            }, self.SEARCH_DELAY);
        });
        self.container.on('change', '.its4you-history-action', function () {
            self.load(false);
        });
        self.container.on('click', '.its4you-history-more', function () {
            self.load(true);
        });
    }
});
