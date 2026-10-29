const assert = require('assert'),
    path = require('path');

global.window = global;

let detailPrototype;

global.Vtiger = {
    Class: function (name, staticMethods, instanceMethods) {
        function Detail() {}

        Object.assign(Detail, staticMethods);
        Detail.prototype = instanceMethods;
        global[name] = Detail;
        detailPrototype = instanceMethods;
    }
};
global.jQuery = function (selector) {
    throw new Error('Unexpected global jQuery lookup: ' + selector);
};

require(path.resolve(__dirname, '../../layouts/d1/modules/Vtiger/resources/Detail.js'));

const rollUpComments = {},
    currentSearchParams = {
        val: function () {
            return '';
        }
    },
    emptySearchRows = {
        find: function (selector) {
            assert.strictEqual(selector, '.listSearchContributor');

            return {
                each: function () {}
            };
        }
    },
    contentHolder = {
        find: function (selector) {
            assert.strictEqual(selector, '#rollupcomments');

            return rollUpComments;
        }
    },
    detailContainer = {
        is: function (container) {
            return container === detailContainer;
        },
        find: function (selector) {
            if (selector === '.searchRow') {
                return emptySearchRows;
            }

            assert.strictEqual(selector, '#currentSearchParams');

            return currentSearchParams;
        }
    },
    detail = Object.create(detailPrototype);

detail.getContentHolder = function () {
    return contentHolder;
};
detail.getContainer = function () {
    return detailContainer;
};

assert.strictEqual(detail.getRollUpComments(), rollUpComments);
assert.deepStrictEqual(detail.getRelatedListSearchParams(), [[]]);
assert.strictEqual(detail.isRelatedListEventForCurrentDetail(detailContainer), true);
assert.strictEqual(detail.isRelatedListEventForCurrentDetail({}), false);

console.log('Vtiger detail preview isolation test passed');
