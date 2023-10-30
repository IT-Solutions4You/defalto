/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Index_Js("Settings_Vtiger_Index_Js",{

	showMessage : function(customParams){
		var params = {};
		params.animation = "show";
		params.type = 'info';
		params.title = app.vtranslate('JS_MESSAGE');

		if(typeof customParams != 'undefined') {
			var params = jQuery.extend(params,customParams);
		}
		Vtiger_Helper_Js.showPnotify(params);
	}


},{
	registerDeleteShortCutEvent : function(shortCutBlock) {
		let thisInstance = this;

		if(typeof shortCutBlock == 'undefined') {
			shortCutBlock = jQuery('#settingsShortCutsContainer');
		}

		shortCutBlock.find('.unpin').on('click', function(e) {
			e.stopPropagation();

			let actionEle = jQuery(e.currentTarget),
				closestBlock = actionEle.closest('.moduleBlock'),
				fieldId = actionEle.data('id'),
				shortcutBlockActionUrl = closestBlock.data('actionurl'),
				actionUrl = shortcutBlockActionUrl+'&pin=false';

			app.request.post({'url':actionUrl}).then(function(err, data) {
				if(err === null) {
					closestBlock.remove();
					thisInstance.registerSettingShortCutAlignmentEvent();

					let menuItemId = '#'+fieldId+'_menuItem',
						shortCutActionEle = jQuery(menuItemId);

					shortCutActionEle.data('action','pin');
					shortCutActionEle.html('<i class="fa-solid fa-link"></i>')
					app.helper.showSuccessNotification({'message':app.vtranslate('JS_SUCCESSFULLY_UNPINNED')});
				}
			});
		});
	},

	registerPinUnpinShortCutEvent: function () {
		let thisInstance = this,
			widget = jQuery('#accordion');

		widget.on('click', '.pinUnpinShortCut', function (e) {
			let shortCutActionEle = jQuery(e.currentTarget),
				url = shortCutActionEle.data('actionurl'),
				shortCutElementActionStatus = shortCutActionEle.data('action'),
				actionUrl;

			if ('pin' === shortCutElementActionStatus) {
				actionUrl = url + '&pin=true';
			} else {
				actionUrl = url + '&pin=false';
			}

			app.request.post({'url': actionUrl}).then(function (error, data) {
				if (data['SUCCESS'] === 'OK') {
					if (shortCutElementActionStatus === 'pin') {
						let unpinTitle = shortCutActionEle.data('unpintitle');

						shortCutActionEle.data('action', 'unpin').attr('title', unpinTitle);
						shortCutActionEle.html('<i class="fa-solid fa-link-slash"></i>');

						let shortCutsMainContainer = jQuery('#settingsShortCutsContainer > .row');

						if (shortCutsMainContainer.length > 0) {
							let url = 'module=Vtiger&parent=Settings&view=IndexAjax&mode=getSettingsShortCutBlock&fieldid=' + shortCutActionEle.data('id');

							app.request.post({url: url}).then(function (err, data) {
								let newBlock = jQuery(data).appendTo(shortCutsMainContainer);
								thisInstance.registerSettingShortCutAlignmentEvent();
								thisInstance.registerDeleteShortCutEvent(newBlock);
							});
						}

						app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESSFULLY_PINNED')});
					} else {
						let pinTitle = shortCutActionEle.data('pintitle');

						shortCutActionEle.data('action', 'pin').attr('title', pinTitle);
						shortCutActionEle.html('<i class="fa-solid fa-link"></i>');

						jQuery('#shortcut_' + shortCutActionEle.data('id')).remove();

						thisInstance.registerSettingShortCutAlignmentEvent();

						app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESSFULLY_UNPINNED')});
					}
				}
			});

			e.preventDefault();
		});
	},

	registerSettingsShortcutClickEvent : function() {
		jQuery('#settingsShortCutsContainer').on('click','.moduleBlock',function(e){
			var url = jQuery(e.currentTarget).data('url');
			window.location.href = url;
		});
	},

	registerSettingShortCutAlignmentEvent : function() {
		jQuery('#settingsShortCutsContainer').find('.moduleBlock').removeClass('marginLeftZero');
		jQuery('#settingsShortCutsContainer').find('.moduleBlock:nth-child(4n+1)').addClass('marginLeftZero');
	},

	registerWidgetsEvents : function() {
		var widgets = jQuery('div.widgetContainer');
		widgets.on({
			shown: function(e) {
				var widgetContainer = jQuery(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement')
				var imagePath = imageEle.data('downimage');
				imageEle.attr('src',imagePath);
			},
			hidden: function(e) {
				var widgetContainer = jQuery(e.currentTarget);
				var quickWidgetHeader = widgetContainer.closest('.quickWidget').find('.quickWidgetHeader');
				var imageEle = quickWidgetHeader.find('.imageElement');
				var imagePath = imageEle.data('rightimage');
				imageEle.attr('src',imagePath);
			}
		});
	},

	registerAddShortcutDragDropEvent : function() {
		var thisInstance = this;

		jQuery( ".menuItemLabel" ).draggable({
			appendTo: "body",
			helper: "clone"
		});
		jQuery( "#settingsShortCutsContainer" ).droppable({
			activeClass: "ui-state-default",
			hoverClass: "ui-state-hover",
			accept: ".menuItemLabel",
			drop: function( event, ui ) {
				var actionElement = ui.draggable.find('.pinUnpinShortCut');
				var pinStatus = actionElement.data('action');
				if(pinStatus === 'unpin') {
					app.helper.showSuccessNotification({'message':app.vtranslate('JS_SHORTCUT_ALREADY_ADDED')});
				} else {
					actionElement.trigger('click');
				}
			}
		});
	},

	registerEventForShowOrHideSettingsLinks: function () {
		jQuery('.slidingDiv').hide();
		jQuery('.show_hide').click(function (e) {
			jQuery(this).next(".slidingDiv").slideToggle('fast');
		});
	},

	registerAccordionClickEvent : function() {
		function toggleChevron(e) {
			$(e.target)
				.prev('.app-nav')
				.find("i.indicator")
				.toggleClass('fa-chevron-down fa-chevron-right');
		}
		$('#accordion').on('hidden.bs.collapse', toggleChevron);
		$('#accordion').on('shown.bs.collapse', toggleChevron);
	},

	registerBasicSettingsEvents : function() {
			this.registerAccordionClickEvent();
			this.registerFilterSearch();
			if(window.hasOwnProperty('Vtiger_List_Js')) {
				var listInstance = new Vtiger_List_Js();
				setTimeout(function(){
					listInstance.registerFloatingThead();
				}, 10);

				app.event.on('Vtiger.Post.MenuToggle', function() {
					listInstance.reflowList();
				});
				listInstance.registerDynamicDropdownPosition();
			}
	},

	registerFilterSearch: function () {
		let searchContainer = jQuery('.settingsSearch'),
			searchInput = jQuery('.settingsSearchInput');

		searchInput.instaFilta({
			targets: '.settingsSearchLabel',
			sections: '.settingsSearch',
			markMatches: true,
			onFilterComplete: function (matchedItems) {
				$('.settingsSearchTab', searchContainer).removeClass('show');
				$('.settingsSearchButton', searchContainer).addClass('collapsed');
				$('.settingsSearchLabel', searchContainer).parents('.settingsSearchTabItem').removeClass('hide');

				if (!searchInput.val().length) {
					let activeLabel = $('.settingsSearchActiveLabel').parents('.settingsSearch');

					$('.settingsSearchTab', activeLabel).removeClass('collapse');
					$('.settingsSearchButton', activeLabel).removeClass('collapsed');
					return;
				}

				$('.settingsSearchTab', searchContainer).addClass('show');
				$('.settingsSearchButton', searchContainer).removeClass('collapsed');
				$('.settingsSearchLabel', searchContainer).not(':visible').parents('.settingsSearchTabItem').addClass('hide');
			}
		});
	},

	registerEvents: function() {
		this._super();
		this.registerSettingsShortcutClickEvent();
		this.registerDeleteShortCutEvent();
		this.registerWidgetsEvents();
		this.registerPinUnpinShortCutEvent();
		this.registerAddShortcutDragDropEvent();
		this.registerSettingShortCutAlignmentEvent();
		this.registerBasicSettingsEvents();
	}

});
