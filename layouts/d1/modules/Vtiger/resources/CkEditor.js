/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

/** @var Vtiger_CkEditor_Js */
jQuery.Class("Vtiger_CkEditor_Js",{},{
	
	/*
	 *Function to set the textArea element 
	 */
	setElement : function(element){
		this.element = element;
		return this;
	},
	
	/*
	 *Function to get the textArea element
	 */
	getElement : function(){
		return this.element;
	},
	
	/*
	 * Function to return Element's id atrribute value
	 */
	getElementId :function(){
		let element = this.getElement();
		return element.attr('id');
	},
	/*
	 * Function to get the instance of ckeditor
	 */
	
	getCkEditorInstanceFromName :function(){
		let elementName = this.getElementId();
		return CKEDITOR.instances[elementName];
	},
    
    /***
     * Function to get the plain text
     */
    getPlainText : function() {
        let ckEditorInstance = this.getCkEditorInstanceFromName();
        return ckEditorInstance.document.getBody().getText();
    },

    getConfig: function() {
        const config = {};

        config.removePlugins = 'save,maximize,magicline,wsc,scayt';
        config.fullPage = true;
        config.allowedContent = true;
        config.disableNativeSpellChecker = false;
        config.enterMode = CKEDITOR.ENTER_BR;
        config.shiftEnterMode = CKEDITOR.ENTER_P;
        config.autoParagraph = false;
        config.fillEmptyBlocks = false;
        config.filebrowserBrowseUrl = 'vendor/kcfinder/kcfinder/browse.php?type=images';
        config.filebrowserUploadUrl = 'vendor/kcfinder/kcfinder/upload.php?type=images';
        config.filebrowserUploadMethod = 'form';
        config.plugins = 'dialogui,dialog,docprops,about,a11yhelp,dialogadvtab,basicstyles,bidi,blockquote,clipboard,button,panelbutton,panel,floatpanel,colorbutton,colordialog,menu,contextmenu,div,resize,toolbar,elementspath,enterkey,entities,popup,filebrowser,find,fakeobjects,floatingspace,listblock,richcombo,font,format,horizontalrule,htmlwriter,wysiwygarea,image,indent,indentblock,indentlist,justify,link,list,liststyle,magicline,pagebreak,preview,removeformat,selectall,showborders,sourcearea,specialchar,menubutton,stylescombo,tab,table,tabletools,undo,wsc';
        config.toolbarGroups = [
            { name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
            { name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
            { name: 'insert' ,groups:['blocks']},
            { name: 'links' },
            { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
            '/',
            { name: 'styles' },
            { name: 'colors' },
            { name: 'tools' },
            { name: 'others' },
            { name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },{name: 'align'},
            { name: 'paragraph', groups: [ 'list', 'indent' ] },
        ];

        config.styles = true;
        config.classes = true;
        config.fullPage = true;

        return config;
    },

	/*
	 * Function to load CkEditor
	 * @params : element: element on which CkEditor has to be loaded, config: custom configurations for ckeditor
	 */
	loadCkEditor : function(element,customConfig){
		
		this.setElement(element);
		let instance = this.getCkEditorInstanceFromName(),
            elementName = this.getElementId(),
            config = this.getConfig();
        
		if(typeof customConfig != 'undefined'){
			config = jQuery.extend(config,customConfig);
		}

		if(instance)
		{
			CKEDITOR.remove(instance);
		}
    
		CKEDITOR.replace( elementName,config);
	},
	
	/*
	 * Function to load contents in ckeditor textarea
	 * @params : textArea Element,contents ;
	 */
	loadContentsInCkeditor : function(contents){
		let CkEditor = this.getCkEditorInstanceFromName();
		CkEditor.setData(contents);
	},

    /**
     * Function to remove ckeditor instance
     */
    removeCkEditor : function() {
        if(this.getElement()) {
            let instance = this.getCkEditorInstanceFromName();
            //first check if textarea element already exists in CKEditor, then destroy it
            if(instance) {
                instance.updateElement();
                instance.destroy();
            }
        }
    }
});
    