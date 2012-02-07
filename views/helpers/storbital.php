<?php
/* *
 * Global Helper for Storbital
 * */

class StorbitalHelper extends AppHelper {
	public $helpers = array('Html', 'Js', 'Form', 'Prod');
	
	/* Gets an image thats been uploaded */
	public function image($dir, $fileName, $size = null, $imgAttrs = array()) {
		if(!empty($size) && $size !=='normal') {
			$thumbnailPath = 'thumb/'.$size.'/';
		} else {
			$thumbnailPath = '';
		}
		$imgPath = '/'.$dir.'/'.$thumbnailPath.$fileName;
		return $this->Html->image($imgPath, $imgAttrs);
	}
	
	// Same as above, but returns the url.
	public function imageURL($dir, $fileName, $size = null) {
		if(!empty($size) && $size !=='normal') {
			$thumbnailPath = 'thumb/'.$size.'/';
		} else {
			$thumbnailPath = '';
		}
		return '/'.$dir.'/'.$thumbnailPath.$fileName;
	}
	
	public function login($before = '', $after = '') {
		$links = '<a href="/users/login/" class="overlayer" rel="#login-overlay">login or register</a>';
		$links = trim($before.' '.$links.' '.$after);
		return $links;
	}
	
	public function logout() {
		return $this ->Html->link('Logout', array(
			'controller' => 'users',
			'action' => 'logout',
			'admin' => false,
		));
	}
	
	/* Admin methods */
	
	public function tagSelect($tagGroup, $selectedIds) {
		$output = '<select id="tagroup'.$tagGroup['TagGroup']['id'].'" autocomplete="off" name="data[Tag][Tag][]"';
		if($tagGroup['TagGroup']['type'] == 'multi') {
			$output .=' multiple="multiple"';
		}
		$output.= '>'."\n";
		
		usort($tagGroup['Tag'], array($this, 'tagCmp'));
		$output.= $this->tagSelectOptions($tagGroup['Tag'], $selectedIds);
		$output.= '</select><a href="#tag-add-form" class="tag-add" rel="'.$tagGroup['TagGroup']['id'].'"><img src="/img/icons/add.png" alt="Add Tag" /></a>';
		return $output;
	}
	
	public function tagSelectOptions($tags, $selectedIds) {
		$output= '<option></option>';
		foreach($tags as $tag) {
			$label = !empty($tag['label']) ? $tag['label'] : $tag['name'];
			$output.= '<option value="'. $tag['id'].'"';
			if(in_array($tag['id'], $selectedIds)) {
				$output.= ' selected="selected"';
			}
			$output.= '>'.$label.'</option>'."\n";
		}
		return $output;
	}
	
	static function tagCmp($a, $b) {
		if($a['name'] == $b['name']) {
			return 0;
		}
		
		if(is_numeric($a['name']) && is_numeric($b['name'])) {
			return ($a['name'] > $b['name']) ? +1 : -1;
		} else {
			return strcmp($a['name'], $b['name']);
		}
	}
}