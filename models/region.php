<?php
class Region extends AppModel {
	var $name = 'Region';
	
 	function setRegion($id = false) {
    	if(!$id) {
    		$id = 'GB'; //geoip_country_code_by_name($_SERVER["REMOTE_ADDR"]);
    	}
    	
    	$region = $this->findById($id);
		//If none of that worked
		if(!$region) {
			$region =  $this->findById('US');
		}

    	return $region['Region'];
    }
}