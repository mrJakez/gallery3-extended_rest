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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
 
class items_rest extends items_rest_Core {

	//TODO: why implement the get() function? Isn't it an extended class?
  static function get($request) {
    $items = array();
    $types = array();

    if (isset($request->params->urls)) {
      if (isset($request->params->type)) {
        $types = explode(",", $request->params->type);
      }

      foreach (json_decode($request->params->urls) as $url) {
        $item = rest::resolve($url);
        if (!access::can("view", $item)) {
          continue;
        }

        if (empty($types) || in_array($item->type, $types)) {
          $items[] = items_rest::_format_restful_item($item, $types);
        }
      }
    } else if (isset($request->params->ancestors_for)) {
      $item = rest::resolve($request->params->ancestors_for);
      if (!access::can("view", $item)) {
        throw new Kohana_404_Exception();
      }
      $items[] = items_rest::_format_restful_item($item, $types);
      while (($item = $item->parent()) != null) {
        array_unshift($items, items_rest::_format_restful_item($item, $types));
      };
    }

    return $items;
  }
  
  private static function _format_restful_item($item, $types) {
    $item_rest = array("url" => rest::url("item", $item),
                       "entity" => $item->as_restful_array(),
                       "relationships" => rest::relationships("item", $item));
            
            
                       
	$item_id = $item_rest['entity']['id'];
		
	$exif = ORM::factory('exif_coordinate')->where('item_id', '=', $item_id)->find();
    	
	if ($exif->id) {
		$item_rest['entity']['coordinate_latitude'] = $exif->latitude;
		$item_rest['entity']['coordinate_longitude'] = $exif->longitude;
	}
	    
	if ($item->is_album()) {
    
    	$cover_limit = 5;
    	$current_cover = 2;
    
    	$coverItem = ORM::factory('item', $item->album_cover_item_id);
    	$item_rest['entity']['extended_album_cover_1'] = rest::url("data", $coverItem, "resize"); 

    	foreach($item->children() as $child) {
  
    		if ($child->id == $item->album_cover_item_id) {
    			continue;
    		}
    		
    		if ($child->is_album()) {
    			continue;
    		}
    	 
    		$item_rest['entity']['extended_album_cover_' . $current_cover] = rest::url("data", $child, "resize");
    	
    		if ($current_cover >= $cover_limit) {
    			break;
    		}
    		
    		$current_cover++;
    	}	
    }

                       
                       
    if ($item->type == "album") {
      $members = array();
      foreach ($item->viewable()->children() as $child) {
        if (empty($types) || in_array($child->type, $types)) {
          $members[] = rest::url("item", $child);
        }
      }
      $item_rest["members"] = $members;
    }

    return $item_rest;
  }
}