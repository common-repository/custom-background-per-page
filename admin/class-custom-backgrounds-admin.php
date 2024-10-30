<?php
final class ITS_Custom_Background_Admin {

	private static $instance;

	public $theme_has_callback = false;


	public function __construct() {

		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		if ( !current_user_can( 'itcb_edit_background' ) && !current_user_can( 'edit_theme_options' ) )
			return;

		add_action( 'load-post.php',     array( $this, 'load_post' ) );
		add_action( 'load-post-new.php', array( $this, 'load_post' ) );
	}

	public function load_post() {
		$screen = get_current_screen();

		if ( !current_theme_supports( 'custom-background' ) || !post_type_supports( $screen->post_type, 'custom-background' ) )
			return;

		$wp_head_callback = get_theme_support( 'custom-background', 'wp-head-callback' );

		$this->theme_has_callback = empty( $wp_head_callback ) || '_custom_background_cb' === $wp_head_callback ? false : true;
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}


	public function enqueue_scripts( $hook_suffix ) {

		if ( !in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) ) )
			return;

		wp_localize_script(
			'custom-background-per-page',
			'itcb_custom_backgrounds',
			array(
				'title'  => __( 'Set Background Image', 'custom-background-per-page' ),
				'button' => __( 'Set background image', 'custom-background-per-page' )
			)
		);

		wp_enqueue_script( 'custom-background-per-page' );
		wp_enqueue_style(  'wp-color-picker'            );
	}

	function add_meta_boxes( $post_type ) {

		add_meta_box(
			'itcb-custom-background-per-page',
			__( 'Custom Background', 'custom-background-per-page' ),
			array( $this, 'do_meta_box' ),
			$post_type,
			'side',
			'core'
		);
	}


	function do_meta_box( $post ) {
		$color = trim( get_post_meta( $post->ID, '_custom_background_color', true ), '#' );
		$attachment_id = get_post_meta( $post->ID, '_custom_background_image_id', true );
		if ( !empty( $attachment_id ) )
			$image = wp_get_attachment_image_src( absint( $attachment_id ), 'post-thumbnail' );
		$url = !empty( $image ) && isset( $image[0] ) ? $image[0] : '';
		$repeat     = get_post_meta( $post->ID, '_custom_background_repeat',     true );
		$position_x = get_post_meta( $post->ID, '_custom_background_position_x', true );
		$position_y = get_post_meta( $post->ID, '_custom_background_position_y', true );
		$attachment = get_post_meta( $post->ID, '_custom_background_attachment', true );
		$size 						= get_post_meta( $post->ID, '_custom_background_size',						 true );
		$mod_repeat     = get_theme_mod( 'background_repeat',     'repeat' );
		$mod_position_x = get_theme_mod( 'background_position_x', 'left'   );
		$mod_position_y = get_theme_mod( 'background_position_y', 'top'    );
		$mod_attachment = get_theme_mod( 'background_attachment', 'scroll' );
		$mod_size 						= get_theme_mod( 'background_size', 'auto' );

		$repeat     = !empty( $repeat )     ? $repeat     : $mod_repeat;
		$position_x = !empty( $position_x ) ? $position_x : $mod_position_x;
		$position_y = !empty( $position_y ) ? $position_y : $mod_position_y;
		$attachment = !empty( $attachment ) ? $attachment : $mod_attachment;
		$size = !empty( $size ) ? $size : $mod_size;		

		$repeat_options = array( 
			'no-repeat' => __( 'No Repeat',           'custom-background-per-page' ), 
			'repeat'    => __( 'Repeat',              'custom-background-per-page' ),
			'repeat-x'  => __( 'Repeat Horizontally', 'custom-background-per-page' ),
			'repeat-y'  => __( 'Repeat Vertically',   'custom-background-per-page' ),
		);

		$position_x_options = array( 
			'left'   => __( 'Left',   'custom-background-per-page' ), 
			'right'  => __( 'Right',  'custom-background-per-page' ),
			'center' => __( 'Center', 'custom-background-per-page' ),
		);

		$position_y_options = array( 
			'top'    => __( 'Top',    'custom-background-per-page' ), 
			'bottom' => __( 'Bottom', 'custom-background-per-page' ),
			'center' => __( 'Center', 'custom-background-per-page' ),
		);

		$attachment_options = array( 
			'scroll' => __( 'Scroll', 'custom-background-per-page' ), 
			'fixed'  => __( 'Fixed',  'custom-background-per-page' ),
		); 
			/* Set up an array of allowed values for the attachment option. */
		$size_options = array( 
			'auto' => __( 'Auto', 'custom-background-per-page' ),
			'contain' => __( 'Contain', 'custom-background-per-page' ), 
			'cover'  => __( 'Cover',  'custom-background-per-page' ),
		); ?>

		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'itcb_meta_nonce' ); ?>
		<input type="hidden" name="itcb-background-image" id="itcb-background-image" value="<?php echo esc_attr( $attachment_id ); ?>" />

		<p>
			<label for="itcb-background-color"><?php _e( 'Color', 'custom-background-per-page' ); ?></label>
			<input type="text" name="itcb-background-color" id="itcb-backround-color" class="itcb-wp-color-picker" value="#<?php echo esc_attr( $color ); ?>" />
		</p>

		<p>
			<a href="#" class="itcb-add-media itcb-add-media-img"><img class="itcb-background-image-url" src="<?php echo esc_url( $url ); ?>" style="max-width: 100%; max-height: 200px; display: block;" /></a>
			<a href="#" class="itcb-add-media itcb-add-media-text"><?php _e( 'Set background image', 'custom-background-per-page' ); ?></a> 
			<a href="#" class="itcb-remove-media"><?php _e( 'Remove background image', 'custom-background-per-page' ); ?></a>
		</p>
		<div class="itcb-background-image-options">

			<p>
				<label for="itcb-background-repeat"><?php _e( 'Repeat', 'custom-background-per-page' ); ?></label>
				<select class="widefat" name="itcb-background-repeat" id="itcb-background-repeat">
				<?php foreach( $repeat_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $repeat, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<p>
				<label for="itcb-background-position-x"><?php _e( 'Horizontal Position', 'custom-background-per-page' ); ?></label>
				<select class="widefat" name="itcb-background-position-x" id="itcb-background-position-x">
				<?php foreach( $position_x_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_x, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

			<?php if ( !$this->theme_has_callback ) { ?>
			<p>
				<label for="itcb-background-position-y"><?php _e( 'Vertical Position', 'custom-background-per-page' ); ?></label>
				<select class="widefat" name="itcb-background-position-y" id="itcb-background-position-y">
				<?php foreach( $position_y_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $position_y, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>
			<?php } ?>

			<p>
				<label for="itcb-background-attachment"><?php _e( 'Attachment', 'custom-background-per-page' ); ?></label>
				<select class="widefat" name="itcb-background-attachment" id="itcb-background-attachment">
				<?php foreach( $attachment_options as $option => $label ) { ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $attachment, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>
   			<p>
				<label for="itcb-background-size"><?php _e( 'Size', 'custom-background-per-page' ); ?></label>
				<select class="widefat" name="itcb-background-size" id="itcb-background-size">
				<?php foreach( $size_options as $option => $label ) { ?>
<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $size, $option ); ?> /><?php echo esc_html( $label ); ?></option>
				<?php } ?>
				</select>
			</p>

		</div>

	<?php }

	function save_post( $post_id, $post ) {

		if ( !isset( $_POST['itcb_meta_nonce'] ) || !wp_verify_nonce( $_POST['itcb_meta_nonce'], plugin_basename( __FILE__ ) ) )
			return;

		$post_type = get_post_type_object( $post->post_type );

		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
			return $post_id;

		if ( 'revision' == $post->post_type )
			return;

		$color = preg_replace( '/[^0-9a-fA-F]/', '', $_POST['itcb-background-color'] );

		$image_id = absint( $_POST['itcb-background-image'] );

		if ( 0 >= $image_id ) {

			$repeat = $position_x = $position_y = $attachment = $size = '';

		} else {

			if ( !empty( $image_id ) ) {

				$is_custom_header = get_post_meta( $image_id, '_wp_attachment_is_custom_background', true );

				if ( $is_custom_header !== get_stylesheet() )
					update_post_meta( $image_id, '_wp_attachment_is_custom_background', get_stylesheet() );
			}


			$allowed_repeat     = array( 'no-repeat', 'repeat', 'repeat-x', 'repeat-y' );
			$allowed_position_x = array( 'left', 'right', 'center' );
			$allowed_position_y = array( 'top', 'bottom', 'center' );
			$allowed_attachment = array( 'scroll', 'fixed' );
			$allowed_size 						= array( 'auto', 'contain' , 'cover' );

$repeat     = in_array( $_POST['itcb-background-repeat'],     $allowed_repeat )     ? $_POST['itcb-background-repeat']     : '';
$position_x = in_array( $_POST['itcb-background-position-x'], $allowed_position_x ) ? $_POST['itcb-background-position-x'] : '';
$position_y = in_array( $_POST['itcb-background-position-y'], $allowed_position_y ) ? $_POST['itcb-background-position-y'] : '';
$attachment = in_array( $_POST['itcb-background-attachment'], $allowed_attachment ) ? $_POST['itcb-background-attachment'] : '';	$size = in_array( $_POST['itcb-background-size'], $allowed_size ) ? $_POST['itcb-background-size'] : '';
		}

		$meta = array(
			'_custom_background_color'      => $color,
			'_custom_background_image_id'   => $image_id,
			'_custom_background_repeat'     => $repeat,
			'_custom_background_position_x' => $position_x,
			'_custom_background_position_y' => $position_y,
			'_custom_background_attachment' => $attachment,
			'_custom_background_size' 						=> $size,
		);

		foreach ( $meta as $meta_key => $new_meta_value ) {

			$meta_value = get_post_meta( $post_id, $meta_key, true );

			if ( $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );

			elseif ( $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, $meta_key, $new_meta_value );

			elseif ( '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

	public function plugin_row_meta( $meta, $file ) {

		if ( preg_match( '/custom-background-per-page\.php/i', $file ) ) {
			$meta[] = '<a href="http://itspiders.net/support">' . __( 'Plugin support', 'custom-background-per-page' ) . '</a>';
			$meta[] = '<a href="http://wordpress.org/plugins/custom-background-per-page">' . __( 'Rate plugin', 'custom-background-per-page' ) . '</a>';
			$meta[] = '<a href="http://itspiders.net/donate">' . __( 'Donate', 'custom-background-per-page' ) . '</a>';
		}

		return $meta;
	}

	public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

ITS_Custom_Background_Admin::get_instance();

?>