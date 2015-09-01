<?php

class MVVideoField {

	const FIELD_KEY = 'mv_category_video';

	public static function render_field( $term_id ) {

		$value = self::get_value( $term_id );
		$html  = '<tr class="form-field"><th><label for="mv_category_video">Category Video</label></th><td>';
		if ( $value ) {
			$video_data = MVVideoIntegrations::get_video_data( $value );
			if ( is_array( $video_data ) ) {
				$html .= '
						<div class="form-field">
							<img src="' . $video_data['img'] . '"/>
							<p>Type: ' . $video_data['type'] . '</p>
							<p>ID: ' . $video_data['id'] . '</p>
						    <input type="url" id="mv-category-video" name="mv_category_video" value="' . $value . '"/>
						</div>';
			} else {
				$html .= '<div class="form-field"><p class="error">' . $video_data . '</p>
							<input type="url" id="mv-category-video" name="mv_category_video" value="' . $value . '"/>
						</div>';
			}
		} else {
			$html .= '<div class="form-field">
    					<input type="url" id="mv-category-video" name="mv_category_video"/>
					</div>';
		}
		$html .= '</td></tr>';

		return $html;
	}

	public static function save( $term_id ) {
		if ( isset( $_POST[ self::FIELD_KEY ] ) ) {
			$video_url   = $_POST[ self::FIELD_KEY ];
			$video_url   = esc_url( $video_url );
			$check_video = MVVideoIntegrations::get_video_data( $video_url );
			if ( is_array( $check_video ) ) {
				MVCategoryFieldsDB::save_value( $term_id, self::FIELD_KEY, $video_url );
			} else {
				MVCategoryFieldsDB::save_value( $term_id, self::FIELD_KEY, '' );
			}
		}

	}

	public static function get_value( $term_id ) {

		$video_url = MVCategoryFieldsDB::get_value( $term_id, static::FIELD_KEY );
		if ( $video_url && ! is_object( $video_url ) ) {
			return $video_url;
		} else {
			return null;
		}
	}

}