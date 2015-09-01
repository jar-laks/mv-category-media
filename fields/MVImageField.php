<?php

class MVImageField {

	const FIELD_KEY = 'mv_category_thumbnail';

	public static function render_field( $term_id ) {
		self::_register_scripts();
		$value = self::get_value( $term_id );
		$html  = '<tr class="form-field"><th><label for="mv_category_thumbnail">Category Image</label></th><td>';
		if ( $value ) {
			$html .= '<div id="mv-image-wrap" class="form-field">
	    				' . wp_get_attachment_image( $value, 'thumbnail', true ) . '
			            <a title="Set featured image" style="display: none" href="#" id="set-mv-category-thumbnail"
			            class="thickbox">Set featured image</a>
			            <a title="Remove Image"  href="#" id="remove-mv-category-thumbnail"
			            class="thickbox">Remove Image</a>
			            <input type="hidden" id="mv-category-thumbnail" name="mv_category_thumbnail" value="' . $value . '"/>
					</div>';
		} else {

			$html .= '<div id="mv-image-wrap" class="form-field">
				    	<img id="mv-featured-image" src="" style="display: none"/>
				    	<a title="Set featured image" href="#" id="set-mv-category-thumbnail"
				       	class="thickbox">Set featured image</a>
				    	<a title="Remove Image" style="display: none" href="#" id="remove-mv-category-thumbnail"
				       	class="thickbox">Remove Image</a>
				    	<input type="hidden" id="mv-category-thumbnail" name="mv_category_thumbnail"/>
					</div>';
		}
		$html .= self::_render_scripts();
		$html .= '</td></tr>';
		return $html;
	}

	public static function save( $term_id ) {
		if ( isset( $_POST[ self::FIELD_KEY ] ) ) {
			$img_id = $_POST[ self::FIELD_KEY ];
			MVCategoryFieldsDB::save_value( $term_id, self::FIELD_KEY, $img_id );
		}
	}

	public static function get_value( $term_id ) {

		$img_id = MVCategoryFieldsDB::get_value( $term_id, static::FIELD_KEY );
		if ( $img_id && ! is_object( $img_id ) ) {
			if ( get_post_status( $img_id ) == 'publish' ) {
				return $img_id;
			} else {
				return null;
			}
		} else {
			return null;
		}
	}

	protected static function _register_scripts() {
		wp_enqueue_media();
	}

	protected static function _render_scripts() {

		ob_start();
		?>
		<script>
			jQuery(function () {
				var wp_media_frame = null;
				var $img = jQuery('#mv-image-wrap').find('img');
				var $field = jQuery('#mv-category-thumbnail');
				var $add_btn = jQuery('#set-mv-category-thumbnail');
				var $rm_btn = jQuery('#remove-mv-category-thumbnail');


				$add_btn.click(function (event) {
					event.preventDefault();


					if (wp_media_frame != null) {
						wp_media_frame.close();
					}

					wp_media_frame = wp.media.frames.customHeader = wp.media({
						title: 'Select Category Image',
						button: {
							text: 'Add Image'
						},
						library: {
							type: 'image'
						},
						multiple: false
					});

					wp_media_frame.on('select', function () {
						var _attachment = wp_media_frame.state().get('selection').first().toJSON(),
							_url = _attachment.icon;

						if (_attachment.mime.indexOf('image') != -1) {

							if (typeof _attachment.sizes.thumbnail !== 'undefined' && _attachment.sizes.thumbnail !== null) {
								_url = _attachment.sizes.thumbnail.url;
							}
							else {
								_url = _attachment.sizes.full.url;
							}
						}

						$field.val(_attachment.id);
						$rm_btn.show();
						$add_btn.hide();
						$img.show().attr('src', _url);
					});

					wp_media_frame.on('open', function () {
						if ($field.val() != '' && wp_media_frame.state().get('selection').length == 0) {
							wp_media_frame.state().get('selection').push(wp.media.attachment($field.val()));
						}
					});

					wp_media_frame.open();
				});
				$rm_btn.click(function (event) {
					event.preventDefault();

					$field.val('');
					$img.hide().attr('src', '');
					$add_btn.show();
					$rm_btn.hide();
				});

			});
		</script>

		<?php
		return ob_get_clean();
	}


}