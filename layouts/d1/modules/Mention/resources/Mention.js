/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

jQuery.Class('Mention_Js', {

    initialize: function () {
        if (this.initialized) {
            return;
        }

        if ('List' !== app.getViewName() && 'Detail' !== app.getViewName()) {
            return;
        }

        this.initialized = true;
        this.registerEvents();
        this.replaceCommentNames();
    },
    registerReply: function (element) {
        const comment = element.parents('.singleComment'),
            creator = comment.find('.creatorName').text().trim(),
            editReason = comment.find('[name="reasonToEdit"]');

        if (creator && !editReason.length && !element.is('.replyUpdated')) {
            jQuery.each(this.getMentionUsers(), function (index, value) {
                if (value['name'].trim() === creator) {
                    element.addClass('replyUpdated');

                    return element.val('@' + value['username']);
                }
            });
        }
    },
    replaceCommentNames: function () {
        const self = this,
            comments = jQuery('.detailViewContainer .commentInfoContent:visible').not('.mentionReplace');

        if (!comments.length) {
            return;
        }

        this.retrieveMentionUsers().done(function () {
            comments.not('.mentionReplace').each(function () {
                const comment = jQuery(this);

                jQuery.each(self.getMentionUsers(), function (index, value) {
                    comment.html(self.replaceUser(value, comment.html()));
                    comment.data('fullcomment', self.replaceUser(value, comment.data('fullcomment')));
                    comment.data('shortcomment', self.replaceUser(value, comment.data('shortcomment')));
                });

                comment.addClass('mentionReplace');
            });
        });
    },
    replaceUser(user, comment) {
        if ('undefined' !== typeof comment) {
            let commentParts = comment.split(' ');

            $.each(commentParts, function (index, word) {
                let name = jQuery('<div>').text('@' + user['name']).html(),
                    username = '@' + user['username'];

                if (word === username) {
                    commentParts[index] = name;
                }
            });

            return commentParts.join(' ');
        }

        return comment;
    },
    registerEvents: function () {
        const self = this,
            isDetail = app.getViewName() === 'Detail',
            selector = isDetail ? '.commentcontent' : '[name="commentcontent"]';

        jQuery(document).on('input.mention keyup.mention', selector, function (event) {
            const editor = this,
                element = jQuery(editor),
                isReply = isDetail && event.type === 'keyup' && element.closest('.singleComment').length
                    && !element.is('.replyUpdated') && !element.closest('.singleComment').find('[name="reasonToEdit"]').length;

            if (!isReply && (element.data('typeahead') || !/@[^\s]*$/.test(editor.value.substring(0, editor.selectionStart)))) {
                return;
            }

            self.retrieveMentionUsers().done(function () {
                if (isReply && document.activeElement === editor) {
                    self.registerReply(element);
                }

                if (!element.data('typeahead') && self.getMentionUsers().length) {
                    self.includeMentionJs().done(function () {
                        if (document.activeElement === editor && !element.data('typeahead')) {
                            self.registerMention(element);

                            if (/@[^\s]*$/.test(editor.value.substring(0, editor.selectionStart))) {
                                element.data('typeahead').lookup();
                            }
                        }
                    });
                }
            });
        });

        if (isDetail) {
            jQuery(document).on('ajaxComplete.mention shown.bs.tab.mention', function () {
                self.replaceCommentNames();
            });
            app.event.on('post.summarywidget.load', function () {
                self.replaceCommentNames();
            });
            app.event.on('post.relatedListLoad.click', function () {
                self.replaceCommentNames();
            });
        }
    },
    registerMention: function (element) {
        const self = this,
            originalRenderer = jQuery.fn.typeahead.Constructor.prototype.render;

        element.mention({
            delimiter: '@',
            sensitive: false,
            users: this.getMentionUsers(),
            typeaheadOpts: {
                menu: '<ul class="mention-menu dropdown-menu shadow p-1"></ul>',
                item: '<li><a class="dropdown-item d-flex align-items-center gap-2 rounded py-2" href="#"></a></li>',
                matcher: function (user) {
                    return self.isMentionMatch(this, user);
                }
            }
        });
        // Upstream changes the shared renderer; keep the Bootstrap adapter local to this editor.
        jQuery.fn.typeahead.Constructor.prototype.render = originalRenderer;
        element.data('typeahead').render = function (users) {
            return self.renderMentionSuggestions(this, users);
        };
        element.data('typeahead').focus();
    },
    isMentionMatch: function (typeahead, user) {
        const beforeCaret = typeahead.query.substring(0, typeahead.$element[0].selectionStart),
            match = beforeCaret.match(/@([^\s]*)$/);

        if (!match) {
            return false;
        }

        const query = match[1].toLowerCase();

        return [user.name, user.username].some(function (value) {
            return String(value || '').toLowerCase().includes(query);
        });
    },
    renderMentionSuggestions: function (typeahead, users) {
        const items = jQuery(users).map(function (index, user) {
            const row = jQuery(typeahead.options.item).attr('data-value', user.username),
                content = jQuery('<span>', {class: 'mention-details d-flex flex-column overflow-hidden'}),
                link = row.find('a');

            if (user.image) {
                link.append(jQuery('<img>', {class: 'mention-avatar rounded-circle flex-shrink-0', src: user.image, alt: ''}));
            } else {
                link.append(jQuery('<span>', {class: 'mention-avatar d-inline-flex align-items-center justify-content-center rounded-circle bg-body-secondary text-body-secondary flex-shrink-0', 'aria-hidden': 'true'})
                    .append(jQuery('<i>', {class: user.type === 'group' ? 'bi bi-people-fill' : 'bi bi-person-fill'})));
            }

            content.append(jQuery('<span>', {class: 'fw-semibold text-truncate'}).text(user.name || user.username));

            if (user.username) {
                content.append(jQuery('<small>', {class: 'mention-username text-body-secondary text-truncate'}).text('@' + user.username));
            }

            link.append(content);

            return row[0];
        });

        items.first().addClass('active');
        typeahead.$menu.html(items);

        return typeahead;
    },
    mentionUsers: [],
    mentionUsersRequest: null,
    mentionScriptsRequest: null,
    getMentionUsers: function () {
        return this.mentionUsers;
    },
    retrieveMentionUsers: function() {
        if (this.mentionUsersRequest) {
            return this.mentionUsersRequest;
        }

        const self = this,
            aDeferred = jQuery.Deferred(),
            params = {
                module: 'Mention',
                action: 'Users',
                sourceModule: app.getModuleName(),
                sourceRecord: app.getRecordId(),
            };

        this.mentionUsersRequest = aDeferred.promise();
        app.request.post({data: params}).then(function (error, data) {
            if (!error && data && Array.isArray(data['users'])) {
                self.mentionUsers = data['users'];

                aDeferred.resolve();
            } else {
                aDeferred.reject();
            }
        }, function () {
            aDeferred.reject();
        });

        return this.mentionUsersRequest;
    },
    includeMentionJs: function () {
        if (!this.mentionScriptsRequest) {
            const self = this;

            jQuery('<link>', {rel: 'stylesheet', href: this.getResourceUrl('Mention.css')}).appendTo('head');
            this.mentionScriptsRequest = jQuery.ajax({url: this.getResourceUrl('bootstrap-typeahead.js', true), dataType: 'script', cache: true, global: false})
                .then(function () {
                    return jQuery.ajax({url: self.getResourceUrl('mention.js', true), dataType: 'script', cache: true, global: false});
                });
        }

        return this.mentionScriptsRequest;
    },
    getResourceUrl: function (filename, library = false) {
        const script = jQuery('script[src*="/Mention/resources/Mention.js"]').first().attr('src') || '',
            query = script.includes('?') ? script.substring(script.indexOf('?')) : '',
            path = library ? 'vendor/jakiestfu/mention/' : 'layouts/d1/modules/Mention/resources/';

        return path + filename + query;
    }
}, {});

jQuery(function() {
    Mention_Js.initialize();
})
