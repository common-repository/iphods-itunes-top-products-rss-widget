<?php

class iPhods_Settings {

	protected $affiliate_programs = array(
											'none' => array('name'=>'No Affiliate Program'),
											'linkshare' => array('name'=>'Linkshare', 'url'=>'http://www.linkshare.com/', 'description' => 'Rakuten LinkShare is the leader of Affiliate Marketing solutions - increase online sales.'),
											'tradedoubler' => array('name'=>'Tradedoubler', 'url'=>'http://www.tradedoubler.com/', 'description'=>'European network providing services for merchants to promote their products and services.'),
											'performancehorizon' => array('name'=>'Performance Horizon Group', 'url'=>'http://www.performancehorizon.com/', 'description'=>'PHG is an online marketing technology provider with offices in the UK and the US.')			);

    // method declaration
    public function __construct()  
    {  
    	if ( is_admin() ){
    		// add settings admin page
    		add_action('admin_menu', array($this,'iphods_admin_menu'));
    		add_action('admin_init', array( $this, 'iphods_page_init' ) );
    	
    		// add shortcut to settings from plugin page
    		add_filter('plugin_action_links', array($this,'iphods_plugin_action_links'), 10, 2);
    	}      
    }  

 public function iphods_admin_menu() {
	    $page_title = 'iPhods Top Products Settings';
	    $menu_title = 'iPhods Settings';
	    $capability = 'manage_options';
	    $menu_slug = 'iphods-settings';
	    $function = array($this,'iphods_settings');
	    add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);
	}
    
    public function iphods_settings() {
	    if (!current_user_can('manage_options')) {
	        wp_die('You do not have sufficient permissions to access this page.');
	    }
	
	 // Here is where you could start displaying the HTML needed for the settings
	 // page, or you could include a file that handles the HTML output for you.
	    
	    ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>iPhods Settings</h2>			
	    <form method="post" action="options.php">
	        <?php
            // This prints out all hidden setting fields
		    settings_fields( 'iphods_option_group' );
		    settings_fields( 'iphods_affiliate_group' );	
		    do_settings_sections( 'iphods-settings' );
		    
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
	<?php
	   
	}
	
		// initialize settings page
		
	    public function iphods_page_init() {
	    		
        register_setting( 'iphods_option_group', 'iphods_activation_permission', array( $this, 'check_activation_permissions' ) );
        register_setting( 'iphods_option_group', 'iphods_backlink_permission', array( $this, 'check_backlink_permissions' ) );
        
        register_setting( 'iphods_affiliate_group', 'iphods_affiliate_program', array( $this, 'check_affiliate_program' ) );
            
         add_settings_section(
            'iphods_section_id',
            'General Settings',
            array( $this, 'print_section_info' ),
            'iphods-settings'
        );	
            
        add_settings_field(
            'iphods_activation_permissions', 
            __('Permission to notify plugin author of site url during activation of plugin, default is turned OFF','iphods'), 
            array( $this, 'create_activation_permissions_field' ), 
            'iphods-settings',
            'iphods_section_id'			
        );	
        
        add_settings_field(
            'iphods_backlink_permissions', 
            __('Permission to display backlink below widget','iphods'), 
            array( $this, 'create_backlink_permissions_field' ), 
            'iphods-settings',
            'iphods_section_id'			
        );	
        
        add_settings_section(
            'iphods_section_affiliate',
            'Affiliate Settings',
            array( $this, 'print_affiliate_info' ),
            'iphods-settings'
        );	
        
        add_settings_field(
            'iphods_affiliate_program', 
            __('Choose current Affiliate program','iphods'), 
            array( $this, 'create_affiliate_program_field' ), 
            'iphods-settings',
            'iphods_section_affiliate'			
        );		
    }
    
    public function check_activation_permissions( $input ) {
        if ( is_numeric( $input['iphods_activation_permissions'] ) ) {
            $mid = $input['iphods_activation_permissions'];			
            if ( get_option( 'iphods_activation_permissions' ) === FALSE ) {
                add_option( 'iphods_activation_permissions', $mid );
            } else {
                update_option( 'iphods_activation_permissions', $mid );
            }
        } else {
            $mid = '';
        }
        return $mid;
    }

    public function check_backlink_permissions( $input ) {
        if ( is_numeric( $input['iphods_backlink_permissions'] ) ) {
            $mid = $input['iphods_backlink_permissions'];			
            if ( get_option( 'iphods_backlink_permissions' ) === FALSE ) {
                add_option( 'iphods_backlink_permissions', $mid );
            } else {
                update_option( 'iphods_backlink_permissions', $mid );
            }
        } else {
            $mid = '';
        }
        return $mid;
    }
    

    public function check_affiliate_program( $input ) {
        if ( array_key_exists( $input['iphods_affiliate_program'], $this->affiliate_programs ) ) {
            $mid = $input['iphods_affiliate_program'];			
            if ( get_option( 'iphods_affiliate_program' ) === FALSE ) {
                add_option( 'iphods_affiliate_program', $mid );
            } else {
                update_option( 'iphods_affiliate_program', $mid );
            }
        } else {
            $mid = '';
        }
        return $mid;
    }    

	
    public function print_section_info(){
        print 'Enter your setting below:';
    }
    
    public function print_affiliate_info(){
        echo 'Complete the following Affiliate section, to earn revenue for clicks on iPhods widgets and shortcode links.
        <blockquote><dl>';
        
    		foreach($this->affiliate_programs as $key => $affiliate_program){
    			if($key == 'none' ){continue;}
    			
    				printf('<dt><a href="%3$s" target="_BLANK">%1$s</a></dt><dd>%2$s<br/><a href="%3$s" target="_BLANK">%3$s</a></dd>', $affiliate_program['name'], $affiliate_program['description'], $affiliate_program['url']);
    				/*
echo '<dt>'.$affiliate_program['name'].'</dt>';
    				echo '<dd>'.$affiliate_program['description'];
    				echo $affiliate_program['url'].'</dd>'; 
    				
*/
    		}
    	
        
        echo '</dl></blockquote>';
    }    
	
    public function create_activation_permissions_field(){
        ?>
        <select id="input_iphods_activation_permissions" name="iphods_activation_permission[iphods_activation_permission]">
        	<option value="0" <?php echo (get_option( 'iphods_activation_permissions') != 1 )?'selected="selected"':''; ?>>Disabled</option>
        	<option value="1" <?php echo (get_option( 'iphods_activation_permissions') == 1 )?'selected="selected"':''; ?>>Enabled</option>
        </select><?php
        
    }
    
     public function create_backlink_permissions_field(){
        ?>
        <select type="text" id="input_iphods_backlink_permission" name="iphods_backlink_permission[iphods_backlink_permission]" >
        	<option value="0" <?php echo (get_option( 'iphods_backlink_permissions') != 1 )?'selected="selected"':''; ?>>Disabled</option>
        	<option value="1" <?php echo (get_option( 'iphods_backlink_permissions') == 1 )?'selected="selected"':''; ?>>Enabled</option>
        </select><?php
    }
    
    public function create_affiliate_program_field(){
        ?>
        <select type="text" id="input_iphods_affiliate_program" name="iphods_affiliate_program[iphods_affiliate_program]" >
        	<?php
        		foreach($this->affiliate_programs as $key => $affiliate_program){
        			?>
        				<option value="<?php echo $key; ?>" <?php echo (get_option( 'iphods_affiliate_program') == $key )?'selected="selected"':''; ?>><?php echo $affiliate_program['name']; ?></option>
        			<?php
        		}
        	?>
        </select><?php
    }

    // End of settings page code



    // add settings link to plugin page
	public function iphods_plugin_action_links($links, $file) {
	    static $this_plugin;
	
	    if (!$this_plugin) {
	        $this_plugin = plugin_basename(__FILE__);
	    }
	
	    if ($file == $this_plugin) {
	        // The "page" query string value must be equal to the slug
	        // of the Settings admin page we defined earlier, which in
	        // this case equals "myplugin-settings".
	        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=iphods-settings">Settings</a>';
	        array_unshift($links, $settings_link);
	    }
	
	    return $links;
	}

} // end iPhods_Settings


$iPhods_Settings = new iPhods_Settings();