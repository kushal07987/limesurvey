// $Id: labels.js 8649 2010-04-28 21:38:53Z c_schmitz $
var loadCKEditorFields = function(){

    if (sHTMLEditorMode=='inline') {
        $('textarea.fulledit').ckeditor(function() { /* callback code */ }, {	toolbar : sHTMLEditorMode,
                                                                                language : sEditorLanguage,
                                                                                width: 660,
                                                                                customConfig : '/scripts/admin/ckeditor-config.js' });
    }

}
$(document).ready(loadCKEditorFields);
$(document).on('pjax:end', loadCKEditorFields);
