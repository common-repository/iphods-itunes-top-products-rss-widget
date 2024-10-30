<?php
/**
 * @package iphods
 * @version 8.17
 */
/*
Plugin Name: iPhods iTunes Top Products Widget
Plugin URI: http://wordpress.org/plugins/iphods-itunes-top-products-rss-widget/
Description: This plugin is a simple plugin to generate widgets highlighting top mac apps, music, books, ios apps, audiobooks and university courses available on Apple iTunes Store. This is in development, but provides a starting point for experienced developers who want to customize the widget to their needs. At this time, the plugin does not support affiliate links but will in the near future. 
Author: Bradford Knowton
Version: 8.17
Author URI: http://bradknowlton.com/
License: GPLv2 or later

*/

define('APPLE_COMMON_JSON_URL','https://rss.itunes.apple.com/data/lang/en-US/common.json');
define('APPLE_MEDIA_TYPES_JSON_URL','https://rss.itunes.apple.com/data/media-types.json');
define('APPLE_LANG_MEDIA_TYPES_JSON_URL','https://rss.itunes.apple.com/data/lang/en-US/media-types.json');
define('APPLE_COUNTRIES_JSON_URL','https://rss.itunes.apple.com/data/countries.json');
define('APPLE_ITUNES_URL','https://itunes.apple.com/');
define('ACTIVATION_URL','http://iphods.com/activation.php');
define('IPHODS_TOP_PRODUCTS_WIDGET_VERSION','8.17');

require(dirname(__FILE__)."/iphods_top_widget.php");
require(dirname(__FILE__)."/iphods_shortcode.php");
require(dirname(__FILE__)."/iphods_settings.php");

class iPhods {
    // property declaration
    protected $url = APPLE_ITUNES_URL; 
    
    // method declaration
    public function __construct()  
    {  
    	register_activation_hook( __FILE__, array( $this, 'register_iphods_plugin' ) );
    	register_deactivation_hook( __FILE__, array( $this, 'deregister_iphods_plugin' ) );
    	
    	add_action( 'widgets_init', array($this,'register_iphods_widget') );
    	
    	// only initate css on frontend
    	if(!is_admin()){
	    	add_action( 'wp_enqueue_scripts', array($this,'register_iphods_css') );	
    	}
    	
    	
    }  
    
    public function register_iphods_plugin(){
		// Check option to see if activation notification 
		if(get_option( 'iphods_activation_permissions' ) == 1){
			// if given permission, use actual url
			wp_remote_post( ACTIVATION_URL, array( 'body' => array( 'op'=> 'activate', 'bu' => get_bloginfo('url') ) ) );		}
			// removed to confirm to site WP repository guidelines
			/*
		else{
			// otherwise use md5 hash of url
			wp_remote_post( ACTIVATION_URL, array( 'body' => array( 'op'=> 'activate', 'bu' => md5(get_bloginfo('url')) ) ) );	
			}
*/
	} 
    
    public function deregister_iphods_plugin(){
		// Check option to see if deactivation notification 
		if(get_option( 'iphods_activation_permissions' ) == 1){
			// if given permission, use actual url
			wp_remote_post( ACTIVATION_URL, array( 'body' => array( 'op'=> 'deactivate', 'bu' => get_bloginfo('url') ) ) );
		}
		// removed to confirm to site WP repository guidelines
		/*
		else{
			// otherwise use md5 hash of url
			wp_remote_post( ACTIVATION_URL, array( 'body' => array( 'op'=> 'deactivate', 'bu' => md5(get_bloginfo('url')) ) ) );
		}
*/
  } 
    
   
    
    // register iPhods_Widget widget
	public function register_iphods_widget() {
	    register_widget( 'iPhods_Top_Widget' );
	}
    
    public function register_iphods_css(){
	     wp_register_style( 'iphods-style', plugins_url('css/iphods.css', __FILE__) );
         wp_enqueue_style( 'iphods-style' );
         
         wp_register_script( 'iphods-script', plugins_url( 'js/iphods.js', __FILE__ ) );
         wp_enqueue_script( 'iphods-script' );
    }
    
    public function render_shortcode( $atts ){
		
		$url = $this->generate_url($atts);
		
		$json = $this->load_json($url);
		
		$output = $this->render_output($json, $atts);
		
		return $output;
	}
	
	public function render_widget($atts = array() ){
		
		$url = $this->generate_url($atts);
		
		$json = $this->load_json($url);
		
		$output = $this->render_output($json, $atts);
		
		return $output;
	}
	
	public function generate_url($atts){
		
		// https://itunes.apple.com/us/rss/toppaidapplications/limit=10/json
		
		extract( wp_parse_args( $atts, array(
			'limit' => '10',
			'country' => 'us',
			'feedtype' => 'toppaidapplications',
			'genre' => '',
			'explicit' => '',
			'order_by' => 'rating'

		) ) );
		
		$url = $this->url."{$country}/rss/"."{$feedtype}/";
		
		if($order_by == 'random'){
			$url .=  "limit=100";
		}else{
			$url .=  "limit={$limit}";	
		}
		
		
		if($genre != ''){
			$url .= '/genre='.$genre;
		}
		
		if($explicit != ''){
			$url .= '/explicit='.$explicit;
		}
		
		$url .= "/json";
		
		return $url;
	}
	
	public function load_json($url = ""){
		
		if(!$url){
			return;
		}
		
		// setup key to index cache
		$key = md5($url);
		
		// check cache, if it exists otherwise reload url
		$result = wp_cache_get( 'iphods_'.$key );
		if ( false === $result ) {
			$result = wp_remote_get($url);
			
			if ( is_wp_error( $result ) ) {
			   return "";
			} 
			
			wp_cache_set( 'iphods_'.$key, $result['body'] );
		} 
		// Do something with $result;
	
		// decode the response body
		$json = json_decode($result['body']);
	
		return $json;
		
	}
	
	private function render_output($json = "", $atts = array()){
	
		if(!$json){
			return;
		}
		
		extract( wp_parse_args( $atts, array(
			'limit' => '10',
			'image_size' => '2',
			'show_title' => true,
			'show_summary' => false,
			'summary_length' => 225,
			'columns' => '4',
			'tracking' => true,
			'target' => true,
			'display' => 'grid',
			'show_price' => 'false',
			'order_by' => 'rating'
			
			
		) ) );
		
		$count = 1;
		
		$output = "";
	
		$output .= '<ul class="iphods-item-list columns-'.$columns.' '.$display.'">';
	
		$lines = array();
	
		foreach($json->feed->entry as $entry){
			$lines[] = $entry;
		}
		
		// order by
		if($order_by == 'random'){
			shuffle($lines);
			$lines = array_slice($lines, 0, $limit);
			
		}
		
		foreach($lines as $entry){
			// var_dump($entry->id->label);
			
			$class ="";
			if($count%$columns==0){
				$class= "last";
			}elseif($count%$columns==1){
				$class= "first";
			}else{
				$class = "";
			}
			
			
			if($tracking == 'true'){
				$tracking_click = 'onClick="trackOutboundLink(this, \'iPhods Widget\', \''.$entry->{'im:name'}->label.'\' ); return false;"';
			}else{
				$tracking_click = '';	
			}
			
			if($target == 'true'){
				$target_attr = 'target="_blank"';
			}else{
				$target_attr = '';	
			}
			
			if($display == "list"){
			
				$summary = "";
				if(isset($entry->summary->label)){
					$summary = $entry->summary->label;
					$summary = $this->trim_text($summary, $summary_length);
					$summary = str_replace("\n\n", "\n",$summary);
					$summary = nl2br($summary);
				}
				
				$category = "";
				if(isset($entry->category->attributes->label)){
					$category = $entry->category->attributes->label;
				}
				
				$artist = ""; // im:artist
				if(isset($entry->{'im:artist'}->label)){
					$artist = $entry->{'im:artist'}->label;
				}
			
				$price = ""; // im:price
				if(isset($entry->{'im:price'}->label) && $show_price == "true"){
					$price = "Price: ".$entry->{'im:price'}->label;
				}
				
			
				$block = sprintf('<li class="%4$s"><a href="%2$s" %5$s %6$s title="Download %1$s Now" ><img src="%3$s" alt="Icon for %1$s" /></a>
				<div class="iphods-details">
					<a href="%2$s" %5$s %6$s title="Download %1$s Now" >%1$s</a>
					<div>%7$s</div>
					<div>Category: %8$s</div>
					<div>Studio: %9$s</div>
					<div>%10$s</div>
					</div>
				</li>',
							$entry->{'im:name'}->label, 
							$entry->id->label,
							$entry->{'im:image'}[$image_size]->label,
							$class,
							$tracking_click,
							$target_attr,
							$price,
							$category, 
							$artist,
							$summary
							);
				
			}else{
				$block = sprintf('<li class="%4$s"><a href="%2$s" %5$s %6$s title="Download %1$s Now" ><img src="%3$s" alt="Icon for %1$s" /><br/>%1$s</a></li>',
							$entry->{'im:name'}->label, 
							$entry->id->label,
							$entry->{'im:image'}[$image_size]->label,
							$class,
							$tracking_click,
							$target_attr
							);
			
			}
			
			$output .= $block;
			$count++;
			
		}
		
		
		$output .= '</ul>';
	
		return $output;
	}
	
	private function trim_text($input, $length) {

	    // If the text is already shorter than the max length, then just return unedited text.
	    if (strlen($input) <= $length) {
	        return $input;
	    }
	
	    // Find the last space (between words we're assuming) after the max length.
	    $last_space = strrpos(substr($input, 0, $length), ' ');
	    // Trim
	    $trimmed_text = substr($input, 0, $last_space);
	    // Add ellipsis.
	    $trimmed_text .= '...';
	
	    return $trimmed_text;
	}
	
}

$iPhods = new iPhods();