/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	config.language = 'es';
	//config.uiColor = '#F4F4F4';
	//config.skin = 'kama';
	
	config.toolbar = 'MyToolbar';
	config.toolbar_MyToolbar = [
		{ name: 'document', items : [ 'Source' ] },
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		'/',
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','-','RemoveFormat' ] },
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','SpecialChar','PageBreak' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] }
	];
	
	config.filebrowserBrowseUrl = './ckeditor/kcfinder-2.51/browse.php?type=files';
	config.filebrowserImageBrowseUrl = './ckeditor/kcfinder-2.51/browse.php?type=images';
	config.filebrowserFlashBrowseUrl = './ckeditor/kcfinder-2.51/browse.php?type=flash';
	config.filebrowserUploadUrl = './ckeditor/kcfinder-2.51/upload.php?type=files';
	config.filebrowserImageUploadUrl = './ckeditor/kcfinder-2.51/upload.php?type=images';
	config.filebrowserFlashUploadUrl = './ckeditor/kcfinder-2.51/upload.php?type=flash';
};

// Patch para imágenes
// Agregado para incluir width y height de la imagen SIN style
// Quitando este código, el tamaño de las imágenes solo se define por style (no sirve para newsletter)
CKEDITOR.on('instanceReady', function (ev) {
// Ends self closing tags the HTML4 way, like <br>.
ev.editor.dataProcessor.htmlFilter.addRules(
    {
        elements:
        {
            $: function (element) {
                // Output dimensions of images as width and height
                if (element.name == 'img') {
                    var style = element.attributes.style;

                    if (style) {
                        // Get the width from the style.
                        var match = /(?:^|\s)width\s*:\s*(\d+)px/i.exec(style),
                            width = match && match[1];

                        // Get the height from the style.
                        match = /(?:^|\s)height\s*:\s*(\d+)px/i.exec(style);
                        var height = match && match[1];

                        if (width) {
                            //element.attributes.style = element.attributes.style.replace(/(?:^|\s)width\s*:\s*(\d+)px;?/i, '');
                            element.attributes.width = width;
                        }

                        if (height) {
                            //element.attributes.style = element.attributes.style.replace(/(?:^|\s)height\s*:\s*(\d+)px;?/i, '');
                            element.attributes.height = height;
                        }
                    }
                }


                if (!element.attributes.style)
                    delete element.attributes.style;

                return element;
            }
        }
    });
});
// fin del patch para tamaño de imágenes