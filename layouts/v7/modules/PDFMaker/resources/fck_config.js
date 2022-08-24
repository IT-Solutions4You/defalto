/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

CKEDITOR.editorConfig = function(config)
{
    config.skin = 'moonocolor';
    
    config.toolbar = 'BodyToolbar';
/*
    config.toolbar_BodyToolbar =
            [
                ['Source', '-', 'NewPage', 'Preview', '-', 'Templates'],
                ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Print', 'SpellChecker', 'Scayt'],
                ['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
                ['BidiLtr', 'BidiRtl'],
                '/',
                ['Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
                ['Image', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak'],
                '/',
                ['Styles', 'Format', 'Font', 'FontSize'],
                ['TextColor', 'BGColor'],
                ['Maximize', 'ShowBlocks', '-', 'About']
            ];*/
    config.height = '1000';
    config.width = '1100';
    config.font_names =
            'Arial/Arial, Helvetica, sans-serif;' +
            'Comic Sans MS;' +
            //'Comic Sans MS/Comic Sans MS, cursive;' +
            'Courier New/Courier New, Courier, monospace;' +
            'DejaVu Sans;' +
            'DejaVu Sans Condensed;' +
            'DejaVu Sans Mono;' +
            'DejaVu Serif;' +
            'DejaVu Serif Condensed;' +
            'Georgia;' +
            'Lucida Sans Unicode;' +
            //'Lucida Sans Unicode/Lucida Sans Unicode, Lucida Grande, sans-serif;' +
            'Tahoma;' +
            //'Tahoma/Tahoma, Geneva, sans-serif;' +
            'Times New Roman/Times New Roman, Times, serif;' +
            'Trebuchet MS;' +
            // 'Trebuchet MS/Trebuchet MS, Helvetica, sans-serif;' +
            'Verdana';
    //'Verdana/Verdana, Geneva, sans-serif';
};
