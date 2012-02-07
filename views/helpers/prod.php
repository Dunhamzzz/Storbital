<?php
class ProdHelper extends AppHelper
{
	public $helpers = array('Html','Js', 'Storbital');
	
	//These are for temporarily storing field values that need to be altered with other field values.
	private $_product;
	private $_shortlist;
	private $_localRegion;

	/* Specification Stuff */
	public function setProduct($product) {
		$this->_product = $product;
	}
	
	public function setLocalRegion($region) {
		$this->_localRegion = $region;
	}
	
	public function setShortlist($shortlist) {
		$this->_shortlist = $shortlist;
	}
	
	/* returns a link to a product */
	public function link($product, $url = false, $htmlAttrs = array()) {
		if(!array_key_exists('Product', $product)) {
			$product['Product'] = $product;
		}
		
		$text = $product['Product']['name'];
		
		if(array_key_exists('Category',$product['Product'])) { //might be contained
			$type = $product['Product']['Category']['url_slug'];
		} else {
			$type = $product['Category']['url_slug'];
		}
		$slug = $product['Product']['url_slug'];

		//Return just a link
		if($url) {
			return $this->Html->url(array(
				'admin' => false,
				'controller' => 'products',
				'action' => 'view',
				'type' => $type,
				'slug' => $slug
			));
		}
		
		$defaultAttrs = array('title' => $text. ' specifications and where to buy');
		$htmlAttrs = array_merge($defaultAttrs, $htmlAttrs);
		
		return $this->Html->link($text, array(
			'admin' => false,
			'controller' => 'products',
			'action' => 'view',
			'type' => $type,
			'slug' => $slug
		), $htmlAttrs
		);
	}
	
	public function shortlist($productId) {
		if(isset($this->_shortlist) && array_key_exists($productId, $this->_shortlist)) {
			$action = 'delete';
			$class = 'shortlist-delete shortlist-button';
			$title = 'Click to remove this product from your shortlist.';
			$ed = 'ed';
		} else {
			$action = 'add';
			$class = 'shortlist-add shortlist-button';
			$title = 'Click to add this product to your Storbital shortlist.';
			$ed = '';
		}
		
		return $this->Html->link('Shortlist'.$ed,
			'/shortlist/'.$action.'/'.$productId,
			array(
				'class' => $class.' go-button',
				'title' => $title,
				'id' => 'product-'.$productId
			)
		);
	}
	
	/* Returns a widget with product */
	public function widget($product, $element = 'default') {
		$content = array( //this only works for stuff in the Product model
			'id' => $product['Product']['id'],
			'name' => $product['Product']['name'],
			'url' => $this->link($product, true),
			'link' => $this->link($product)
		);
		
		if(array_key_exists('Category', $product['Product'])) {
			$content['category'] = $product['Product']['Category'];
		} else {
			$content['category'] = $product['Category'];
		}
		
		if(array_key_exists('Cpu', $product['Product'])) {
			$content['cpu'] = $product['Product']['Cpu'];
		} else {
			$content['cpu'] = $product['Cpu'];
		}
		
		if(array_key_exists('Gpu', $product['Product'])) {
			$content['gpu'] = $product['Product']['Gpu'];
		} else {
			$content['gpu'] = $product['Gpu'];
		}
		
		if(!isset($options['imageSize'])) {
			$options['imageSize'] = 'normal';
		}
		//$content['image'] = $this->Storbital->imageURL($product['Product']['image_dir'],$product['Product']['image'], $options['imageSize']);
		$content['image'] = array($product['Product']['image_dir'],$product['Product']['image']);
		if(array_key_exists($this->_localRegion['lowest_key'], $product['Product']) && isset($product['Product'][$this->_localRegion['lowest_key']])) {
			if($product['Product'][$this->_localRegion['lowest_key']] == '0.00') {
				$content['lowest'] = 'Free';
			} else {
				$content['lowest'] =  $this->_localRegion['currency_symbol_html'].number_format($product['Product'][$this->_localRegion['lowest_key']],0);
			}
		}
		if(isset($product['Widget']['element'])) {
			$element = $product['Widget']['element'];
		}
		$view =& ClassRegistry::getObject('view');
		return $view->element('widgets/'.$element, compact('content'));
	}

	public function specifications($product, $tagGroups) {
		$specs = array(
			'Details' => array(
				'Type' => 'Type: '.$product['Category']['name'],
				'Manufacturer' => 'Manufacturer: '.$product['Manufacturer']['name']
			)
		);
			
		if(!empty($product['Cpu']['full_name'])) {
			$specs['Processor'] = $this->cpuSpeed($product['Cpu']['speed_mhz']).' '.$product['Cpu']['full_name'];
		}
		
		if(!empty($product['Gpu']['full_name'])) {
			$specs['Graphics'] = $product['Gpu']['full_name'];
		}
		
		foreach($tagGroups as $tagGroup) {
			if($tagGroup['TagGroup']['order'] == '0') {
				continue; //Ignore invisibles.
			}
			
			if(!empty($tagGroup['children'])) {
				foreach($tagGroup['children'] as $tagGroupChild) {
					foreach($product['Tag'] as $key => $tag) {
						if(!empty($tag['label'])) {
							$tag['name'] = $tag['label'];
						}
						if($tag['tag_group_id'] == $tagGroupChild['TagGroup']['id']) {
							if(!array_key_exists($tagGroup['TagGroup']['name'], $specs) || !array_key_exists($tagGroupChild['TagGroup']['name'], $specs[$tagGroup['TagGroup']['name']])) {
								$specs[$tagGroup['TagGroup']['name']][$tagGroupChild['TagGroup']['name']] = $tagGroupChild['TagGroup']['prefix'].$tag['name'];
							} elseif(is_array($specs[$tagGroup['TagGroup']['name']][$tagGroupChild['TagGroup']['name']])) {
								$specs[$tagGroup['TagGroup']['name']][$tagGroupChild['TagGroup']['name']][] = $tag['name']; //dont add prefix again.
							} else {
								$newSpecs = array(
									$specs[$tagGroup['TagGroup']['name']][$tagGroupChild['TagGroup']['name']],
									$tag['name'] //dont add prefix again
								);
								$specs[$tagGroup['TagGroup']['name']][$tagGroupChild['TagGroup']['name']] = $newSpecs;
							}
							unset($product['Tag'][$key]);
						}
					}
				}
			}
			
			foreach($product['Tag'] as $key => $tag) {
				if($tag['tag_group_id'] == $tagGroup['TagGroup']['id']) {
					if(!empty($tag['label'])) {
							$tag['name'] = $tag['label'];
						}
					if(!array_key_exists($tagGroup['TagGroup']['name'], $specs)) {
						$specs[$tagGroup['TagGroup']['name']] = $tagGroup['TagGroup']['prefix'].$tag['name'];
					} elseif(is_array($specs[$tagGroup['TagGroup']['name']])) {
						$specs[$tagGroup['TagGroup']['name']][] = $tag['name'];
					} else {
						$newSpecs = array(
							$specs[$tagGroup['TagGroup']['name']],
							$tag['name']
						);
						$specs[$tagGroup['TagGroup']['name']] = $newSpecs;
					}
					unset($tag[$key]);
				}
			}
		}
		
		return $specs;
	}
	
	
	/* Generic Methods used by this helper */
	public function cpuSpeed($speed) {
		if($speed >= 1000) {
			return round($speed / 1000,2).'Ghz';
		} else {
			return $speed.'Mhz';
		}
	}
	
	public function _getDimensions() {
		$dimensions = explode('x', $this->_product['Product']['dimensions_mm']);
		
		return 'Dimensions: '.$dimensions[0].'mm x '.$dimensions[1].'mm x '.$dimensions[2].'mm';
	}
	

	public function _mb2gb($mb) {
		if($mb < 1024) {
			return $mb.'MB';
		} else {
			return ($mb / 1024).'GB';
		}
	}

}