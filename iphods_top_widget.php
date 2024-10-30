<?php

/**
 * Adds iPhods_Widget widget.
 */
class iPhods_Top_Widget extends WP_Widget {

	var $countries;
	var $lang;
	var $media;

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		global $iPhods;
		parent::__construct(
			'iphods_widget', // Base ID
			'iPhods Top Widget', // Name
			array( 'description' => __( 'An iPhods Widget to show Top Products in Apple iTunes Store', 'iphods' ), ) // Args
		);
		
		// load up apple country list
		$apple_common = $iPhods->load_json(APPLE_COMMON_JSON_URL);
		$this->countries = (array) $apple_common->feed_country;
		
		// load up apple language list
		$apple_lang = $iPhods->load_json(APPLE_LANG_MEDIA_TYPES_JSON_URL);
		$this->lang = (array) $apple_lang;
		
		// patching missing phrases:
		$this->lang['toptvepisodes'] = "Top TV Episodes";
		$this->lang['toptvseasons'] = "Top TV Seasons";
		
		// load up apple media list
		$apple_media = $iPhods->load_json(APPLE_MEDIA_TYPES_JSON_URL);
		$this->media = (array) $apple_media;
		
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		global $iPhods;
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		$feedtype = "";
		if(isset($instance['feedtype'])){
			$feedtype = $instance['feedtype'];
		}
		
		$current_country = "";
		if(isset($instance['current_country'])){
			$current_country = $instance['current_country'];	
		}
		
		echo $args['before_widget'];
		if ( ! empty( $title ) ){
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		if ( empty( $feedtype ) ){
			$feedtype = "toppaidapplications";
		}
		if ( empty( $current_country ) ){
			$current_country = "us";
		}
		
		$atts = array(	'country' => $current_country,
						'feedtype' => $feedtype,
						'limit' => '12',
						'image_size' => '0',
						'columns' => '4' );
			
		echo $iPhods->render_widget($atts);
		
		if(get_option( 'iphods_backlink_permissions' ) == 1){
			echo '<div class="backlink">powered by <a href="http://iphods.com/?utm_source='.urlencode($_SERVER['HTTP_HOST']).'&utm_medium=plugin&utm_campaign=iphods+plugin" target="_blank">iPhods iTunes RSS Widget</a></div>';
		}
				
		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		global $iPhods;
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'iphods' );
		}
		if ( isset( $instance[ 'feedtype' ] ) ) {
			$feedtype = $instance[ 'feedtype' ];
		}
		else {
			$feedtype = __( 'toppaidapplications', 'iphods' );
		}
		if ( isset( $instance[ 'current_country' ] ) ) {
			$current_country = $instance[ 'current_country' ];
		}
		else {
			$current_country = __( 'us', 'iphods' );
		}
		
		?>
		<p>
		<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:', 'iphods' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_name( 'feedtype' ); ?>"><?php _e( 'Feed Type:', 'iphods' ); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'feedtype' ); ?>" name="<?php echo $this->get_field_name( 'feedtype' ); ?>" >
			<?php
				// var_dump($apple_common);
				foreach($this->media as $feed){
					echo "<optgroup label='".$this->lang[$feed->translation_key]."'>";
					foreach($feed->feed_types as $type){
						echo "<option ";
						if(esc_attr( $feedtype ) == $type->translation_key){
							echo " selected='selected' ";
						}
						echo 'value="'.$type->translation_key.'">'.$this->lang[$type->translation_key].'</option>'."\n";
					}
					echo "</optgroup>";
				}
			?>
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_name( 'current_country' ); ?>"><?php _e( 'Country:', 'iphods' ); ?></label> 
		<select class="widefat" id="<?php echo $this->get_field_id( 'current_country' ); ?>" name="<?php echo $this->get_field_name( 'current_country' ); ?>">
			<?php
				// var_dump($apple_common);
				foreach($this->countries as $key => $country){
					echo "<option ";
					if(esc_attr( $current_country ) == $key){
						echo " selected='selected' ";
					}
					echo 'value="'.$key.'">'.$country.'</option>'."\n";
				}
			?>
		</select>
		
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['feedtype'] = ( ! empty( $new_instance['feedtype'] ) ) ? strip_tags( $new_instance['feedtype'] ) : '';
		$instance['current_country'] = ( ! empty( $new_instance['current_country'] ) ) ? strip_tags( $new_instance['current_country'] ) : '';
		
		return $instance;
	}

} // class iPhods_Top_Widget