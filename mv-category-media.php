<?php
/*
Plugin Name: MV Category Media
Plugin URI: https://github.com/jar-laks/mv-category-media
Description: Creates additional media fields for default category
Version: 0.1
Author: Maks Viter
Author URI: https://github.com/jar-laks
*/

define( 'MV_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
require_once( MV_PLUGIN_PATH . '/db/MVCategoryFieldsDB.php' );
require_once( MV_PLUGIN_PATH . '/fields/MVCategoryFields.php' );
require_once( MV_PLUGIN_PATH . '/fields/MVImageField.php' );
require_once( MV_PLUGIN_PATH . '/fields/MVVideoField.php' );
require_once( MV_PLUGIN_PATH . '/fields/integration/MVVideoIntegrations.php' );


register_activation_hook( __FILE__, array( 'MVCategoryFieldsDB', 'create_db_table' ) );
register_uninstall_hook( __FILE__, array( 'MVCategoryFieldsDB', 'drop_table' ) );

class MVCategoryMedia {

	protected static $instance = null;


	protected function __construct() {
		add_action( 'admin_menu', array( $this, 'create_menu' ) );


	}


	public function create_menu() {

		add_menu_page( 'MV Plugin Settings', 'MV Settings', 'administrator', __FILE__, array(
			$this,
			'settings_page'
		), 'dashicons-playlist-video' );

		add_action( 'admin_init', array( $this, 'register_settings' ) );

	}


	public static function get_instance() {
		if ( static::$instance == null ) {
			static::$instance = new static;
		}

		return static::$instance;
	}

	function register_settings() {
		register_setting( 'mv-settings-group', 'mv_youtube_api_key' );
	}

	function settings_page() {
		?>
		<div class="wrap">
			<h2>MV Category Media</h2>

			<form method="post" action="options.php">
				<?php settings_fields( 'mv-settings-group' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?= __( 'Youtube API key' ) ?></th>
						<td><p id="status_key">Key status</p>
							<input type="text" name="mv_youtube_api_key"
							       value="<?php echo get_option( 'mv_youtube_api_key' ); ?>"/></td>
						<a class="button-primary" id="check-api-key">Check Api Key</a>
						<script>
							jQuery(function () {
								var $check_btn = jQuery('#check-api-key');

								var check_status = function () {
									var $key = jQuery('[name="mv_youtube_api_key"]').val();
									var status_key = jQuery('#status_key');
									jQuery.ajax({
										method: "GET",
										url: "https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=1&q=video&key=" + $key,
										error: function (responce) {
											status_key.css('color', 'red').text('Incorrect Key');
										},
										success: function (response) {
											status_key.css('color', 'green').text('Key OK');
										}
									})
								};
								check_status();


								$check_btn.on('click', function(e){
									e.preventDefault();
									check_status();
								});
							});
						</script>
					</tr>

				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>"/>
				</p>

			</form>
		</div>
	<?php }


}

MVCategoryMedia::get_instance();
MVCategoryFields::get_instance();


add_filter( 'mv_additional_cat_meta', function ( $cat_id ) {
	$category_img   = MVImageField::get_value( $cat_id );
	$category_video = MVVideoField::get_value( $cat_id );
	$video_html     = '';
	if ( $category_video ) {
		$video_html = MVVideoIntegrations::get_video_embed_url_not_auto( $category_video );
	}
	$meta_html = apply_filters( 'mv_additional_cat_video_html', $video_html );
	$img_html  = wp_get_attachment_image( $category_img, 'large' );
	$meta_html .= apply_filters( 'mv_additional_cat_img_html', $img_html );

	return $meta_html;
}, 1 );


add_filter( 'category_description', function ( $content, $cat_id ) {
	$meta_content = '';
	if ( ! is_admin() ) {
		$meta_content = apply_filters( 'mv_additional_cat_meta', $cat_id );
	}

	return $meta_content . $content;
}, 1, 2 );



