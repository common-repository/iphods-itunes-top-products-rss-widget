jQuery(document).ready(function($) {
	// source: http://wordpress.stackexchange.com/questions/72394/how-to-add-a-shortcode-button-to-the-tinymce-editor

    tinymce.create('tinymce.plugins.iphods_plugin', {
        init : function(ed, url) {
        		// Register command for when button is clicked
                ed.addCommand('iphods_insert_shortcode', function() {
                    selected = tinyMCE.activeEditor.selection.getContent();

                    if( selected ){
                        //If text is selected when button is clicked
                        //Wrap shortcode around it.
                        content =  '[iphods]'+selected+'[/iphods]';
                    }else{
                        content =  '[iphods feedtype="toppaidapplications" limit="16" display="grid"]';
                    }

                    tinymce.execCommand('mceInsertContent', false, content);
                });

            // Register buttons - trigger above command when clicked
            ed.addButton('iphods_button', {title : 'Insert iPhods Shortcode', cmd : 'iphods_insert_shortcode', image: url + '/../images/button.png' });
        },   
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('iphods_button', tinymce.plugins.iphods_plugin);
});