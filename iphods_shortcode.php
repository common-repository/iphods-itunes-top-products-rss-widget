<?php
    	
class iPhods_Shortcode {
    
    // method declaration
    public function __construct()  
    {  
    	add_shortcode( 'iphods', array($this,'render_shortcode') );
    	
    	
      //Abort early if the user will never see TinyMCE
      // if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') && get_user_option('rich_editing') == 'true')
      //    return;

      //Add a callback to regiser our tinymce plugin   
      add_filter("mce_external_plugins", array($this,'iphods_register_tinymce_plugin')); 

      // Add a callback to add our button to the TinyMCE toolbar
      add_filter('mce_buttons', array($this,'iphods_add_tinymce_button'));
      
      
    }  
    
    public function render_shortcode($atts){
    	global $iPhods;
		
	    return $iPhods->render_shortcode($atts);
    }
    
    public function iphods_register_tinymce_plugin($plugin_array){
    	$plugin_array['iphods_button'] = plugins_url( 'js/shortcode.js' , __FILE__ );
    	return $plugin_array;
	}
    
    public function iphods_add_tinymce_button($buttons){
	    $buttons[] = "iphods_button";
	    return $buttons;
    }
    
    
} // end iPhods_Shortcode


$iPhods_Shortcode = new iPhods_Shortcode();