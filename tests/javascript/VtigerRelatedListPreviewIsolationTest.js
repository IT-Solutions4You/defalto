const assert = require('assert'),
    path = require('path');

global.window = global;
global.document = {};

let relatedListPrototype;

function jQuery(selector) {
    if (selector === global.document) {
        return {ready: function () {}};
    }

    throw new Error('Unexpected global jQuery lookup: ' + selector);
}

jQuery.Class = function (name, staticMethods, instanceMethods) {
    function RelatedList() {}

    Object.assign(RelatedList, staticMethods);
    RelatedList.prototype = instanceMethods;
    global[name] = RelatedList;
    relatedListPrototype = instanceMethods;
};
jQuery.Deferred = function () {
    return {
        resolve: function () {},
        reject: function () {},
        promise: function () {
            return {};
        }
    };
};
jQuery.extend = function (target, source) {
    return Object.assign(target, source);
};

global.jQuery = global.$ = jQuery;

require(path.resolve(__dirname, '../../layouts/d1/modules/Vtiger/resources/RelatedList.js'));

let renderedResponse,
    postLoadContainer;
const relatedContentContainer = {
        html: function (response) {
            renderedResponse = response;
        }
    },
    tabs = {
        find: function (selector) {
            assert.strictEqual(selector, 'li');

            return {removeClass: function () {}};
        }
    },
    selectedTab = {
        addClass: function () {}
    },
    relatedList = Object.create(relatedListPrototype);

relatedList.relatedModulename = 'ModComments';
relatedList.relatedTabsContainer = tabs;
relatedList.selectedRelatedTabElement = selectedTab;
relatedList.relatedContentContainer = relatedContentContainer;
relatedList.getCompleteParams = function () {
    return {record: 'preview-record'};
};
relatedList.initializePaginationEvents = function () {};
relatedList.triggerRelationAdditionalActions = function () {};

global.app = {
    helper: {
        showProgress: function () {},
        hideProgress: function () {}
    },
    event: {
        trigger: function (eventName, container) {
            if (eventName === 'post.relatedListLoad.click') {
                postLoadContainer = container;
            }
        }
    },
    request: {
        post: function (params) {
            assert.strictEqual(params.data.record, 'preview-record');

            return {
                then: function (success) {
                    success(null, '<div class="preview-comments"></div>');
                }
            };
        }
    }
};

relatedList.loadRelatedList({page: 2});

assert.strictEqual(renderedResponse, '<div class="preview-comments"></div>');
assert.strictEqual(postLoadContainer, relatedContentContainer);

console.log('Vtiger related-list preview isolation test passed');
