<?php defined("SYSPATH") or die("No direct script access.");
/**
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2012 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	 See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */

class item_rest extends item_rest_Core {

	static function get($request) {
	
		$result = parent::get($request);

		$item_id = $result['entity']['id'];
		
		$exif = ORM::factory('exif_coordinate')->where('item_id', '=', $item_id)->find();
	
		if ($exif->id) {
			$result['entity']['coordinate_latitude'] = $exif->latitude;
			$result['entity']['coordinate_longitude'] = $exif->longitude;
		}		
		
		$item = ORM::factory('item', $item_id);
		
		if ($item->is_album()) {
		
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

		return $result;
	}
}