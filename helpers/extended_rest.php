<?php

class extended_rest_Core {

	static function get($request) {	
		return array(
			"version" => module::get_version("extended_rest"),
		);
	}

	
	/**
	 * Injects the latitude and longitude informations in the item representing array. 
	 * But only if the exif_gps module is up and runnnig.
	 *
     * @param item  $item which is represented in the result
     * @param Array $result we inject the covers inside the array representation
     */	
	static function injectGps($item, &$result) {
	
		if (!module::is_active("exif_gps")) {
			return false;
		}
	
		$exif = ORM::factory('exif_coordinate')->where('item_id', '=', $item->id)->find();
	
		if ($exif->id) {
			$result['entity']['coordinate_latitude'] = $exif->latitude;
			$result['entity']['coordinate_longitude'] = $exif->longitude;
		}
	}
	
	
	/**
	 * Injects the transcoded videos in the item representing array. But only if 
	 * the transcode module is up and runnnig.
	 *
     * @param item  $item which is represented in the result
     * @param Array $result we inject the covers inside the array representation
     */	
  	static function injectTranscodedVideos($item, &$result) {
	
		if (!module::is_active("transcode")) {
			return false;
		}
	
		$transcodes = ORM::factory('transcode_resolution')->where('item_id', '=', $item->id)->find_all();

		$result['entity']['transcoded_videos'] = array();
		
		foreach($transcodes as $key => $transcode) {
			
			$entry = array();
			$entry['description'] = $transcode->resolution;
			$entry['url'] = url::abs_file("var/modules/transcode/flv/" . $item->id) .'/'. $transcode->resolution .'.'. module::get_var("transcode", "format");
			$result['entity']['transcoded_videos'][] = $entry;
			
			if($key == 0) {
				$result['entity']['file_url_public'] = $entry['url'];
				$result['entity']['file_url'] = $entry['url'];
			}
			
		}
	}
	
	
	/**
	 * Injects the multiple album cover (needed by the iGallery3 albumview) in the
	 * item representing array.
	 *
     * @param item  $item which is represented in the result
     * @param Array $result we inject the covers inside the array representation
     */
	static function injectMultipleAlbumCovers($item, &$result) {
	
		if (!$item->is_album()) {
			return false;
		}
		
		$cover_limit = 5;
    	$current_cover = 2;
    
    	$coverItem = ORM::factory('item', $item->album_cover_item_id);

    	if ($coverItem->is_photo()) {
    	    $result['entity']['extended_album_cover_1'] = rest::url("data", $coverItem, "resize"); 
    	}else{
    	    $current_cover = 1;
    	}
	
    	foreach($item->children() as $child) {
  
    		if ($child->id == $item->album_cover_item_id) {
    			continue;
    		}
    		
    		if (!$child->is_photo()) {
    			continue;
    		}
    	 
    		$result['entity']['extended_album_cover_' . $current_cover] = rest::url("data", $child, "resize");
    	
    		if ($current_cover >= $cover_limit) {
    			break;
    		}
    		
    		$current_cover++;
    	}	
	}
}