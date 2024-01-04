/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
Settings_Workflows_Edit_Js.prototype.preSaveVTEMAILMakerMailTask = function (tasktype) {

}

Settings_Workflows_Edit_Js.prototype.registerVTEMAILMakerMailTaskEvents = function () {
    let textAreaElement = jQuery('#content');

    this.registerFillTaskFromEmailFieldEvent();
    this.registerCcAndBccEvents();
};

Settings_Workflows_Edit_Js.prototype.VTEMAILMakerMailTaskCustomValidation = function () {
    let result = true,
        selectElement1 = jQuery('input[name="recepient"]'),
        control1 = selectElement1.val();

    if (!control1) {
        jQuery('#detailViewLayoutBtn').trigger('click');
        result = app.vtranslate('JS_REQUIRED_FIELD');
    }

    return result;
};