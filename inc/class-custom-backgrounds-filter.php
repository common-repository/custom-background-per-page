<?php
final class ITS_Custom_Background_Filter {
	private static $instance;
	public $color = '';
	public $image = '';
	public $repeat = 'repeat';
	public $position_y = 'top';
	public $position_x = 'left';
	public $attachment = 'scroll';
	public $size = 'auto';
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ), 95 );
	}
	public function add_theme_support() {
		if ( !current_theme_supports( 'custom-background' ) )
			return;
		add_action( 'template_redirect', array( $this, 'setup_background' ) );

		$wp_head_callback = get_theme_support( 'custom-background', 'wp-head-callback' );

		if ( empty( $wp_head_callback ) || '_custom_background_cb' === $wp_head_callback )
			add_theme_support( 'custom-background', array( 'wp-head-callback' => array( $this, 'custom_background_callback' ) ) );
	}

	public function setup_background() {

		if ( !is_singular() )
			return;

		$post    = get_queried_object();
		$post_id = get_queried_object_id();

		if ( !post_type_supports( $post->post_type, 'custom-background' ) )
			return;

		$this->color = get_post_meta( $post_id, '_custom_background_color', true );

		$attachment_id = get_post_meta( $post_id, '_custom_background_image_id', true );

		if ( !empty( $attachment_id ) ) {

			$image = wp_get_attachment_image_src( $attachment_id, 'full' );

			$this->image = !empty( $image ) && isset( $image[0] ) ? esc_url( $image[0] ) : '';
		}
		add_filter( 'theme_mod_background_color', array( $this, 'background_color' ), 25 );
		add_filter( 'theme_mod_background_image', array( $this, 'background_image' ), 25 );

		if ( !empty( $this->image ) ) {

			$this->repeat     = get_post_meta( $post_id, '_custom_background_repeat',     true );
			$this->position_x = get_post_meta( $post_id, '_custom_background_position_x', true );
			$this->position_y = get_post_meta( $post_id, '_custom_background_position_y', true );
			$this->attachment = get_post_meta( $post_id, '_custom_background_attachment', true );
			$this->size = get_post_meta( $post_id, '_custom_background_size', true );
			add_filter( 'theme_mod_background_repeat',     array( $this, 'background_repeat'     ), 25 );
			add_filter( 'theme_mod_background_position_x', array( $this, 'background_position_x' ), 25 );
			add_filter( 'theme_mod_background_position_y', array( $this, 'background_position_y' ), 25 );
			add_filter( 'theme_mod_background_attachment', array( $this, 'background_attachment' ), 25 );
			add_filter( 'theme_mod_background_size', array( $this, 'background_size' ), 25 );
		}
	}

	public function background_color( $color ) {
		return !empty( $this->color ) ? preg_replace( '/[^0-9a-fA-F]/', '', $this->color ) : $color;
	}

	public function background_image( $image ) {

		if ( !empty( $this->image ) )
			$image = $this->image;

		elseif ( !empty( $this->color ) )
			$image = '';

		return $image;
	}

	public function background_repeat( $repeat ) {
		return !empty( $this->repeat ) ? $this->repeat : $repeat;
	}

	public function background_position_x( $position_x ) {
		return !empty( $this->position_x ) ? $this->position_x : $position_x;
	}

	public function background_position_y( $position_y ) {
		return !empty( $this->position_y ) ? $this->position_y : $position_y;
	}

	public function background_attachment( $attachment ) {
		return !empty( $this->attachment ) ? $this->attachment : $attachment;
	}
public function background_size( $size ) {
		return !empty( $this->size ) ? $this->size : $size;
	}
	public function custom_background_callback() {
		$image = set_url_scheme( get_background_image() );
		$color = get_background_color();
		if ( empty( $image ) && empty( $color ) )
			return;

		$style = $color ? "background-color: #{$color};" : '';

		if ( $image ) {

			$style .= " background-image: url('{$image}');";

			$repeat = get_theme_mod( 'background_repeat', 'repeat' );
			$repeat = in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) ? $repeat : 'repeat';
			$style .= " background-repeat: {$repeat};";

			$position_y = get_theme_mod( 'background_position_y', 'top' );
			$position_y = in_array( $position_y, array( 'top', 'center', 'bottom' ) ) ? $position_y : 'top';

			$position_x = get_theme_mod( 'background_position_x', 'left' );
			$position_x = in_array( $position_x, array( 'center', 'right', 'left' ) ) ? $position_x : 'left';
			$style .= " background-position: {$position_y} {$position_x};";

			$attachment = get_theme_mod( 'background_attachment', 'scroll' );
			$attachment = in_array( $attachment, array( 'fixed', 'scroll' ) ) ? $attachment : 'scroll';
			$style .= " background-attachment: {$attachment};";
			
			$size = get_theme_mod( 'background_size', 'auto' );
			$size = in_array( $size, array( 'auto', 'contain', 'cover' ) ) ? $size : 'auto' ;
			$style .= " background-size: {$size};";
		}
		echo "\n" . '<style type="text/css" id="custom-background-css">body.custom-background{ ' . trim( $style ) . ' }</style>' . "\n";
	}
public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

ITS_Custom_Background_Filter::get_instance();

?>