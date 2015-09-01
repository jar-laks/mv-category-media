<?php

class MVVideoIntegrations {

	public static function get_video_data( $url ) {

		if ( esc_url( $url ) ) {
			$data_arr = self::_get_video_type_id( $url );
			if ( ! $data_arr ) {
				return 'Undefined video type.';
			}
			if ( $data_arr['type'] == 'youtube' ) {
				$youtube_data = self::_get_youtube_data( $data_arr['id'] );
				if ( !is_object($youtube_data) ) {
					return "Youtube video not found or Api key problems";
				} else {
					$img             = $youtube_data->thumbnails->default->url;
					$url             = "//www.youtube.com/embed/" . $data_arr['id'] . "?rel=0";
					$data_arr['img'] = $img;
					$data_arr['url'] = $url;
				}
			} else {
				$vimeo_data = self::_get_vimeo_data( $data_arr['id'] );
				if (!is_object($vimeo_data) ) {
					return "Vimeo video not found";
				} else {
					$img             = $vimeo_data->thumbnail_small;
					$url             = "//player.vimeo.com/video/" . $data_arr['id'] . "?byline=0&amp;portrait=0";
					$data_arr['img'] = $img;
					$data_arr['url'] = $url;
				}
			}

			return $data_arr;
		} else {
			return "Invalid input type";
		}
	}

	protected static function _get_video_type_id( $url ) {

		if ( strpos( $url, 'youtu' ) !== false ) {
			$pattern = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
			preg_match( $pattern, $url, $result );
			if ( isset( $result[7] ) ) {
				return array( 'type' => 'youtube', 'id' => $result[7] );
			}
		} elseif ( strpos( $url, 'vimeo' ) !== false ) {
			$thumb = explode( '/', $url );
			$thumb = array_filter( $thumb );

			return array( 'type' => 'vimeo', 'id' => end( $thumb ) );
		}

		return false;
	}

	protected static function _get_youtube_data( $id ) {

		$api_key = get_option( 'mv_youtube_api_key' );
		if ( ! $api_key ) {
			return false;
		}
		$request_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet&maxResults=1&id={$id}&key={$api_key}";
		$res         = wp_remote_get( $request_url );
		if ( isset( $res['response'] ) ) {
			if ( $res['response']['code'] == '200' ) {
				$data = json_decode( $res['body'] );
				if ( isset( $data->items ) && count( $data->items ) > 0 ) {
					return $data->items[0]->snippet;
				} else {
					return 'No results';
				}
			} else {
				return $res['response']['message'];
			}
		} else {
			return null;
		}
	}

	protected static function _get_vimeo_data( $id ) {
		$request_url = "http://vimeo.com/api/v2/video/{$id}.json";
		$res         = wp_remote_get( $request_url );
		if ( isset( $res['response'] ) ) {
			if ( $res['response']['code'] == '200' ) {
				$data = json_decode( $res['body'] );

				return $data[0];
			} else {
				return $res['response']['message'];
			}

		} else {
			return null;
		}


	}

	public static function get_video_embed_url_not_auto( $url ) {
		$_result = '';
		$data    = self::_get_video_type_id( $url );
		if ( is_array( $data ) ) {
			$protocol='http';
			if(is_ssl()){
				$protocol='https';
			}
			if ( $data['type'] == 'youtube' ) {
				$_result = "<iframe id=\"yplay\" src=\"{$protocol}://www.youtube.com/embed/" . $data['id'] . "?wmode=transparent&feature=oembed&amp;enablejsapi=1\" frameborder=\"0\" allowfullscreen=\"\"></iframe>";
			} else {
				$_result = "<iframe id=\"yplay\" src=\"{$protocol}://player.vimeo.com/video/" . $data['id'] . "?wmode=transparent&api=1\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>";
			}
		}

		return $_result;
	}
}