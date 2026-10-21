/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Must extend Settings_Vtiger_Index_Js - app.controller() instantiates this class
   instead of the core one */
/** @var Settings_MailManager_AISettings_Js */
Settings_Vtiger_Index_Js('Settings_MailManager_AISettings_Js', {}, {

    /**
     * @return {jQuery}
     */
    getContainer: function () {
        return jQuery('.its4you-ai-settings');
    },

    /**
     * @param {string} value
     * @return {string}
     */
    escapeHtml: function (value) {
        // quotes too — the value goes into attributes as well
        return jQuery('<div></div>').text(value === null || typeof value === 'undefined' ? '' : String(value)).html()
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    },

    /**
     * The agent data of the page: providers (keys, models, catalog), agents, efforts, tiers
     *
     * @param {jQuery} container
     * @return {Object}
     */
    getAgentData: function (container) {
        return container.data('agents') || {providers: {}, agents: [], efforts: [], tiers: []};
    },

    /**
     * Posts one agent settings action; on success the page reloads on the Agents tab
     *
     * @param {Object} params
     * @param {function} [done] instead of the reload
     */
    postAgentAction: function (params, done) {
        app.helper.showProgress();
        app.request.post({data: jQuery.extend({module: app.getModuleName(), parent: app.getParentModuleName()}, params)}).then(function (err, data) {
            app.helper.hideProgress();
            if (err || !data || !data.success) {
                app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_ERROR')});
                return;
            }
            if (done) {
                done(data);
                return;
            }
            window.location.hash = 'mmAiTabAgents';
            window.location.reload();
        });
    },

    /**
     * Keys per provider: verify and save (the key is never shown back), "Use recommended"
     *
     * @param {jQuery} container
     */
    registerKeys: function (container) {
        const self = this;

        container.on('click', '.its4you-ai-key-verify', function () {
            const row = jQuery(this).closest('.its4you-ai-key-row');

            self.postAgentAction({action: 'VerifyAiKey', provider: row.data('provider'), api_key: row.find('.its4you-ai-key').val()}, function (data) {
                row.find('.its4you-ai-key').val('');
                app.helper.showSuccessNotification({
                    message: app.vtranslate('JS_KEY_VERIFIED') + (data.prefilled ? ' ' + app.vtranslate('JS_AGENTS_PREFILLED').replace('%s', data.prefilled) : '')
                });
                window.location.hash = 'mmAiTabAgents';
                window.location.reload();
            });
        });
        container.on('click', '.its4you-ai-apply-recommended', function () {
            const provider = jQuery(this).closest('.its4you-ai-key-row').data('provider');

            app.helper.showConfirmationBox({message: app.vtranslate('JS_APPLY_RECOMMENDED_CONFIRM')}).then(function () {
                self.postAgentAction({action: 'ApplyAiRecommended', provider: provider});
            });
        });
    },

    /**
     * The model select of the agent form: the recommended models of the agent's type on top
     * (with description and price), every current model only under "Show all models"
     *
     * @param {jQuery} form
     * @param {Object} data getAgentData()
     * @param {string} selected
     */
    fillAgentModels: function (form, data, selected) {
        const self = this,
            provider = form.find('[name="provider"]').val(),
            tier = form.find('[name="tier"]').val(),
            info = data.providers[provider] || {models: [], catalog: {}},
            showAll = form.find('.its4you-ai-all-models').is(':checked'),
            select = form.find('[name="model"]'),
            recommended = (info.catalog[tier] || []),
            recommendedIds = recommended.map(function (entry) {
                return entry.id;
            }),
            group = jQuery('<optgroup></optgroup>').attr('label', app.vtranslate('JS_MODELS_RECOMMENDED'));
        let found = false;

        select.empty();
        jQuery.each(recommended, function (index, entry) {
            found = found || entry.id === selected;
            group.append(jQuery('<option></option>').val(entry.id)
                .text(entry.id + ' — ' + entry.description + ' · ' + '€'.repeat(entry.price || 1)));
        });
        if (recommended.length) {
            select.append(group);
        }
        if (showAll) {
            const all = jQuery('<optgroup></optgroup>').attr('label', app.vtranslate('JS_MODELS_ALL'));

            jQuery.each(info.models || [], function (index, model) {
                if (recommendedIds.indexOf(model) === -1) {
                    found = found || model === selected;
                    all.append(jQuery('<option></option>').val(model).text(model));
                }
            });
            select.append(all);
        }
        if (selected && !found) {
            select.append(jQuery('<option></option>').val(selected).text(selected));
        }
        if (!select.find('option').length) {
            select.append(jQuery('<option value=""></option>').text(app.vtranslate('JS_MODELS_VERIFY_KEY')));
        }
        select.val(selected && select.find('option').filter(function () {
            return this.value === selected;
        }).length ? selected : select.find('option').first().val());
        self.showModelFit(form, data);
        form.find('.its4you-ai-effort-row').toggleClass('hide', !info.effort);
    },

    /**
     * The badge of a model outside the recommendations of the agent's type — the pick stays allowed
     *
     * @param {jQuery} form
     * @param {Object} data
     */
    showModelFit: function (form, data) {
        const provider = form.find('[name="provider"]').val(),
            tier = form.find('[name="tier"]').val(),
            model = form.find('[name="model"]').val(),
            catalog = (data.providers[provider] || {}).catalog || {},
            tiers = data.tiers || [];
        let fit = 'not_recommended';

        jQuery.each(tiers, function (index, catalogTier) {
            const ids = (catalog[catalogTier] || []).map(function (entry) {
                return entry.id;
            });

            if (ids.indexOf(model) !== -1) {
                fit = catalogTier === tier ? '' : (tiers.indexOf(catalogTier) > tiers.indexOf(tier) ? 'expensive' : 'not_recommended');
            }
        });
        form.find('.its4you-ai-model-fit').text(model && fit ? app.vtranslate('JS_MODEL_FIT_' + fit.toUpperCase()) : '');
    },

    /**
     * The backup model of an agent (I17): the catalog of the backup provider for the
     * agent's type (a model picked before stays); empty = the recommended one
     *
     * @param {jQuery} form
     * @param {object} data getAgentData()
     * @param {string} selected
     */
    fillBackupModels: function (form, data, selected) {
        const select = form.find('[name="backup_model"]').empty(),
            chosen = form.find('[name="backup_provider"]').val(),
            provider = data.providers[chosen === '' ? data.backup_provider : chosen],
            tier = form.find('[name="tier"]').val(),
            seen = {};

        select.append(jQuery('<option value=""></option>').text(app.vtranslate('JS_BACKUP_MODEL_AUTO')));
        select.toggleClass('hide', !provider || chosen === 'none');

        if (!provider) {
            return;
        }

        // the recommended models of the agent's type only — older ones (gpt-4o, o3…) are not offered
        jQuery.each((provider.catalog || {})[tier] || [], function (index, entry) {
            seen[entry.id] = true;
            select.append(jQuery('<option></option>').val(entry.id).text(entry.id + ' – ' + entry.description));
        });

        if (selected && !seen[selected]) {
            select.append(jQuery('<option></option>').val(selected).text(selected));
        }

        select.val(selected);
    },

    /**
     * Add / edit an agent in a window
     *
     * @param {jQuery} container
     */
    registerAgentForm: function (container) {
        const self = this;

        container.on('click', '.its4you-ai-agent-edit', function () {
            const data = self.getAgentData(container),
                agentId = parseInt(jQuery(this).data('agentid'), 10) || 0,
                agent = jQuery.grep(data.agents, function (row) {
                    return parseInt(row.agentid, 10) === agentId;
                })[0] || {name: '', tier: 'fast', active: '1', provider: Object.keys(data.providers)[0], model: '', effort: '', instructions: '', max_input_tokens: 100000, backup_provider: '', backup_model: ''},
                field = function (label, control) {
                    return jQuery('<div class="row py-2"></div>')
                        .append(jQuery('<div class="col-lg-4 fieldLabel"></div>').text(app.vtranslate(label)))
                        .append(jQuery('<div class="col-lg-8 fieldValue"></div>').append(control));
                },
                providerSelect = jQuery('<select class="form-select inputElement" name="provider"></select>'),
                tierSelect = jQuery('<select class="form-select inputElement" name="tier"></select>'),
                effortSelect = jQuery('<select class="form-select inputElement" name="effort"></select>')
                    .append(jQuery('<option value=""></option>').text(app.vtranslate('JS_EFFORT_DEFAULT'))),
                // the backup when the provider fails (I17): the global one, none, or one of its own
                backupSelect = jQuery('<select class="form-select inputElement" name="backup_provider"></select>')
                    .append(jQuery('<option value=""></option>').text(app.vtranslate('JS_BACKUP_GLOBAL')))
                    .append(jQuery('<option value="none"></option>').text(app.vtranslate('JS_BACKUP_NONE'))),
                form = jQuery('<div class="its4you-ai-agent-form"></div>');

            jQuery.each(data.providers, function (provider, info) {
                providerSelect.append(jQuery('<option></option>').val(provider).text(info.label + (info.has_key ? '' : ' (' + app.vtranslate('JS_KEY_MISSING') + ')')));
            });
            jQuery.each(data.providers, function (provider, info) {
                backupSelect.append(jQuery('<option></option>').val(provider).text(info.label + (info.has_key ? '' : ' (' + app.vtranslate('JS_KEY_MISSING') + ')')));
            });
            jQuery.each(data.tiers, function (index, tier) {
                tierSelect.append(jQuery('<option></option>').val(tier).text(app.vtranslate('JS_AGENT_TIER_' + tier.toUpperCase())));
            });
            jQuery.each(data.efforts, function (index, effort) {
                effortSelect.append(jQuery('<option></option>').val(effort).text(app.vtranslate('JS_EFFORT_' + effort.toUpperCase())));
            });

            form.append(field('JS_AGENT_NAME', jQuery('<input type="text" class="form-control inputElement" name="name" maxlength="100">').val(agent.name)))
                .append(field('JS_AGENT_TIER', tierSelect.val(agent.tier)))
                .append(field('JS_AGENT_PROVIDER', providerSelect.val(agent.provider)))
                .append(field('JS_AGENT_MODEL', jQuery('<div></div>')
                    .append('<select class="form-select inputElement" name="model"></select>')
                    .append(jQuery('<div class="form-check mt-1"></div>')
                        .append('<input type="checkbox" class="form-check-input its4you-ai-all-models" id="its4youAiAllModels">')
                        .append(jQuery('<label class="form-check-label small" for="its4youAiAllModels"></label>').text(app.vtranslate('JS_MODELS_SHOW_ALL'))))
                    .append('<div class="form-text text-warning its4you-ai-model-fit"></div>')))
                .append(field('JS_AGENT_EFFORT', effortSelect.val(agent.effort || '')).addClass('its4you-ai-effort-row'))
                .append(field('JS_AGENT_INSTRUCTIONS', jQuery('<div></div>')
                    .append(jQuery('<textarea class="form-control inputElement" name="instructions" rows="3" maxlength="4000"></textarea>').val(agent.instructions || ''))
                    .append(jQuery('<div class="form-text"></div>').text(app.vtranslate('JS_AGENT_INSTRUCTIONS_HINT')))))
                .append(field('JS_AGENT_MAX_INPUT', jQuery('<input type="number" class="form-control inputElement" name="max_input_tokens" min="1000" max="1000000" step="1000">').val(agent.max_input_tokens)))
                .append(field('JS_AGENT_BACKUP', jQuery('<div></div>')
                    .append(backupSelect.val(agent.backup_provider || ''))
                    .append(jQuery('<select class="form-select inputElement mt-1 its4you-ai-backup-model" name="backup_model"></select>'))
                    .append(jQuery('<div class="form-text"></div>').text(app.vtranslate('JS_AGENT_BACKUP_HINT')))))
                .append(field('JS_AGENT_ACTIVE', jQuery('<div class="form-check form-switch"></div>')
                    .append(jQuery('<input type="checkbox" class="form-check-input" name="active">').prop('checked', String(agent.active) === '1'))));

            const modal = jQuery('<div class="modal-dialog modal-lg"></div>').append(jQuery('<div class="modal-content"></div>')
                .append(jQuery('<div class="modal-header"></div>')
                    .append('<button type="button" class="btn-close order-last ms-auto" data-bs-dismiss="modal"></button>')
                    .append(jQuery('<h5 class="modal-title"></h5>').text(app.vtranslate(agentId ? 'JS_AGENT_EDIT' : 'JS_AGENT_ADD'))))
                .append(jQuery('<div class="modal-body"></div>').append(form))
                .append(jQuery('<div class="modal-footer"></div>').append(jQuery('<div class="container-fluid"><div class="row"></div></div>')
                    .find('.row')
                    .append(jQuery('<div class="col-6 text-end"></div>').append(jQuery('<button type="button" class="btn btn-primary cancelLink" data-bs-dismiss="modal"></button>').text(app.vtranslate('JS_CANCEL'))))
                    .append(jQuery('<div class="col-6"></div>').append(jQuery('<button type="button" class="btn btn-primary active its4you-ai-agent-save"></button>').text(app.vtranslate('JS_SAVE'))))
                    .end())));

            app.helper.showModal(modal, {cb: function (shown) {
                const refill = function () {
                    self.fillAgentModels(form, data, form.find('[name="model"]').val() || agent.model);
                };

                self.fillAgentModels(form, data, agent.model);
                self.fillBackupModels(form, data, agent.backup_model || '');
                shown.on('change', '[name="provider"], [name="tier"], .its4you-ai-all-models', refill);
                shown.on('change', '[name="backup_provider"], [name="tier"]', function () {
                    self.fillBackupModels(form, data, form.find('[name="backup_model"]').val() || '');
                });
                shown.on('change', '[name="model"]', function () {
                    self.showModelFit(form, data);
                });
                shown.on('click', '.its4you-ai-agent-save', function () {
                    self.postAgentAction({
                        action: 'SaveAiAgent',
                        agentid: agentId,
                        name: form.find('[name="name"]').val(),
                        tier: form.find('[name="tier"]').val(),
                        provider: form.find('[name="provider"]').val(),
                        model: form.find('[name="model"]').val() || '',
                        effort: form.find('.its4you-ai-effort-row').hasClass('hide') ? '' : form.find('[name="effort"]').val(),
                        instructions: form.find('[name="instructions"]').val(),
                        max_input_tokens: form.find('[name="max_input_tokens"]').val(),
                        backup_provider: form.find('[name="backup_provider"]').val(),
                        backup_model: form.find('[name="backup_model"]').val() || '',
                        active: form.find('[name="active"]').is(':checked') ? '1' : '0'
                    });
                });
            }});
        });
        container.on('click', '.its4you-ai-agent-delete', function () {
            const agentId = jQuery(this).data('agentid');

            app.helper.showConfirmationBox({message: app.vtranslate('JS_AGENT_DELETE_CONFIRM')}).then(function () {
                self.postAgentAction({action: 'DeleteAiAgent', agentid: agentId});
            });
        });
        container.on('click', '.its4you-ai-agent-test', function () {
            self.postAgentAction({action: 'TestAiAgent', agentid: jQuery(this).data('agentid')}, function (data) {
                app.helper.showAlertBox({message: jQuery('<div></div>')
                    .append(jQuery('<div class="mb-2 small text-secondary"></div>').text(app.vtranslate('JS_AGENT_TEST_RESULT')
                        .replace('%1$s', data.model || '').replace('%2$s', data.ms).replace('%3$s', data.input_tokens).replace('%4$s', data.output_tokens)))
                    .append(jQuery('<pre class="mb-0 small" style="white-space: pre-wrap"></pre>').text(data.text))
                    .html()});
            });
        });
    },

    /**
     * Which agent does each task, the triage cascade, and the prices of the usage overview
     *
     * @param {jQuery} container
     */
    registerTasksAndPrices: function (container) {
        const self = this;

        container.on('click', '.its4you-ai-tasks-save', function () {
            const tasks = {};

            container.find('.its4you-ai-task').each(function () {
                tasks[jQuery(this).data('task')] = jQuery(this).val();
            });
            self.postAgentAction({
                action: 'SaveAiTasks',
                tasks: JSON.stringify(tasks),
                cascade: container.find('input[name="ai_triage_cascade"]').is(':checked') ? '1' : '0',
                fallback: container.find('.its4you-ai-fallback').val() || ''
            }, function () {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_SETTINGS_SAVED')});
            });
        });
        container.on('click', '.its4you-ai-prices-save', function () {
            const prices = {};

            container.find('.its4you-ai-prices tr[data-model]').each(function () {
                const row = jQuery(this);

                prices[row.data('model')] = {in: row.find('.its4you-ai-price-in').val(), out: row.find('.its4you-ai-price-out').val()};
            });
            self.postAgentAction({action: 'SaveAiPrices', prices: JSON.stringify(prices)}, function () {
                window.location.hash = 'mmAiTabUsage';
                window.location.reload();
            });
        });
        // indicative prices from the public OpenRouter catalog — empty and earlier indicative
        // prices only, the admin's own ones with "overwrite"
        container.on('click', '.its4you-ai-prices-load', function () {
            self.postAgentAction({
                action: 'LoadAiPrices',
                overwrite: container.find('[name="ai_prices_overwrite"]').is(':checked') ? '1' : '0'
            }, function (data) {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_AI_PRICES_LOADED')
                    .replace('%1$s', (data.filled || []).length)
                    .replace('%2$s', (data.missing || []).length)});
                window.location.hash = 'mmAiTabUsage';
                window.location.reload();
            });
        });
    },

    /**
     * Save of the global AI settings
     *
     * @param {jQuery} container
     */
    registerSaveSettings: function (container) {
        container.on('click', '.its4you-save-ai', function () {
            const button = jQuery(this),
                params = {
                    module: app.getModuleName(),
                    parent: app.getParentModuleName(),
                    action: 'SaveAiSettings',
                    ai_enabled: container.find('input[name="ai_enabled"]').is(':checked') ? '1' : '0',
                    list_page_size: container.find('select[name="list_page_size"]').val(),
                    ai_priority_rules: container.find('textarea[name="ai_priority_rules"]').val(),
                    ai_triage_mode: container.find('select[name="ai_triage_mode"]').val(),
                    ai_autocreate_limit: container.find('input[name="ai_autocreate_limit"]').val(),
                    ai_feature_summary: container.find('input[name="ai_feature_summary"]').is(':checked') ? '1' : '0',
                    ai_feature_translate: container.find('input[name="ai_feature_translate"]').is(':checked') ? '1' : '0',
                    ai_feature_draft: container.find('input[name="ai_feature_draft"]').is(':checked') ? '1' : '0',
                    ai_feature_triage: container.find('input[name="ai_feature_triage"]').is(':checked') ? '1' : '0',
                    ai_feature_entity: container.find('input[name="ai_feature_entity"]').is(':checked') ? '1' : '0',
                    ai_feature_draftcheck: container.find('input[name="ai_feature_draftcheck"]').is(':checked') ? '1' : '0',
                    ai_feature_radar: container.find('input[name="ai_feature_radar"]').is(':checked') ? '1' : '0',
                    ai_anonymize: container.find('select[name="ai_anonymize"]').val(),
                    ai_feature_spamlearn: container.find('input[name="ai_feature_spamlearn"]').is(':checked') ? '1' : '0',
                    ai_feature_autocomment: container.find('input[name="ai_feature_autocomment"]').is(':checked') ? '1' : '0',
                    ai_feature_emailfiling: container.find('input[name="ai_feature_emailfiling"]').is(':checked') ? '1' : '0',
                    ai_feature_similar: container.find('input[name="ai_feature_similar"]').is(':checked') ? '1' : '0',
                    ai_feature_knowledge: container.find('input[name="ai_feature_knowledge"]').is(':checked') ? '1' : '0',
                    ai_knowledge_faq: container.find('input[name="ai_knowledge_faq"]').is(':checked') ? '1' : '0',
                    ai_knowledge_tickets: container.find('input[name="ai_knowledge_tickets"]').is(':checked') ? '1' : '0',
                    ai_feature_recordhistory: container.find('input[name="ai_feature_recordhistory"]').is(':checked') ? '1' : '0',
                    ai_recordhistory_tokens: container.find('input[name="ai_recordhistory_tokens"]').val()
                };

            // the assistant needs a key of at least one provider (the Agents tab)
            if (params.ai_enabled === '1' && !container.find('.its4you-ai-key-row .text-success').length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_KEY_MISSING_ANY')});
                return;
            }

            button.prop('disabled', true);
            app.request.post({data: params}).then(function (err, data) {
                if (err || !data || !data.success) {
                    button.prop('disabled', false);
                    app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_SAVE_ERROR')});
                    return;
                }
                // back to the mailbox list; the flash carrier there shows the toast
                window.location.href = 'index.php?module=' + app.getModuleName()
                    + '&parent=Settings&view=Index&success=3';
            });
        });
    },

    /**
     * The backup provider (I17): saved only with the confirmation that data goes to the
     * second provider during an outage; "Simulate an outage" per provider
     *
     * @param {jQuery} container
     */
    registerFailover: function (container) {
        const self = this,
            reload = function () {
                window.location.hash = 'mmAiTabStatus';
                window.location.reload();
            };

        // "Backup provider during an outage: … – set" on the Agents tab opens its tab
        container.on('click', '.its4you-ai-open-failover', function (event) {
            const link = container.find('.nav-tabs a[href="#mmAiTabStatus"]');

            event.preventDefault();

            if (link.length && window.bootstrap) {
                bootstrap.Tab.getOrCreateInstance(link[0]).show();
            }
        });
        container.on('click', '.its4you-ai-failover-save', function () {
            const status = container.find('.its4you-ai-status'),
                provider = status.find('[name="ai_failover_provider"]').val(),
                confirmed = status.find('[name="ai_failover_confirmed"]').is(':checked');

            if (provider && !confirmed) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FAILOVER_CONFIRM_REQUIRED')});

                return;
            }

            self.postAgentAction({
                action: 'SaveAiFailover',
                provider: provider,
                confirmed: confirmed ? '1' : '0',
                email: status.find('[name="ai_failover_email"]').val()
            }, reload);
        });
        container.on('click', '.its4you-ai-outage-simulate', function () {
            const button = jQuery(this),
                on = String(button.data('on')) === '1',
                post = function () {
                    self.postAgentAction({action: 'SimulateAiOutage', provider: button.data('provider'), on: on ? '1' : '0'}, reload);
                };

            if (on) {
                app.helper.showConfirmationBox({message: app.vtranslate('JS_OUTAGE_SIMULATE_CONFIRM')}).then(post);
            } else {
                post();
            }
        });
    },

    /**
     * Wires the page: provider switch, key verification, save and the global label editor
     */
    registerEvents: function () {
        this._super();

        const container = this.getContainer();
        if (!container.length) {
            return;
        }

        this.registerKeys(container);
        this.registerAgentForm(container);
        this.registerTasksAndPrices(container);
        this.registerSaveSettings(container);
        this.registerFailover(container);
        // a reload after an agent (or a backup) change comes back to its tab
        if (['#mmAiTabAgents', '#mmAiTabStatus', '#mmAiTabUsage'].indexOf(window.location.hash) !== -1 && window.bootstrap) {
            const link = container.find('.nav-tabs a[href="' + window.location.hash + '"]');

            if (link.length) {
                bootstrap.Tab.getOrCreateInstance(link[0]).show();
            }
        }
        // global labels — the same editor edits a user's own in the mailbox detail
        if ('function' !== typeof window.MailManager_ProLabelEditor_Js) {
            return;
        }

        new MailManager_ProLabelEditor_Js(
            container.find('.its4you-label-editor'),
            jQuery('.its4you-label-modal'),
            {module: app.getModuleName(), parent: app.getParentModuleName(), saveAction: 'SaveLabel', deleteAction: 'DeleteLabel'}
        );
    }
});
