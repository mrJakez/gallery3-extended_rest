<?php

class extended_rest_Core {
	static function get($request) {
	
		return array(
			"version" => module::get_version("extended_rest"),
		);
	}
}
  
 ?>