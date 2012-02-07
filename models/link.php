<?php
class Link extends AppModel {
	public $name = 'Link';
	public $belongsTo = array(
		'User' => array('counterCache' => true),
		'Product',
		'Retailer',
		'Region',
	);
	
	public $hasAndBelongsToMany = array('Colour');
	
	public $validate = array(
		'product_id' => array(
			'rule' => 'numeric',
			'required' => true,
			'message' => 'Please select which product this link is for.'
		),
		'retailer_id' => array(
			'rule' => 'numeric',
			'required' => true,
			'message' => 'Please select which retailer this link is for.'
		),
		'url' => array(
			'rule' => array('url', true),
			'message' => 'Please enter a valid link to the retailers product page.',
			'required' => true
		),
		'price' => array(
			'rule' => array('decimal',2),
			'message' => 'Please enter a valid price in decimal format.',
			'required' => false,
			'allowEmpty' => true
		),
		
		'price_monthly' => array(
			'rule' => array('decimal',2),
			'message' => 'Please enter a valid price in decimal format.',
			'required' => false
		),
//		'region_id' => array(
//			'required' => true,
//			'message' => 'Please enter the currency.'
//		),
		'publish' => array('boolean'),
		'expire' => array(
            'rule' => 'date',
            'message' => 'Enter a valid date, or leave this field blank.',
            'allowEmpty' => true
        ),
		'sponsored' => array('boolean'),
		'special_offer' => array('boolean'),
	);
	
	public $actAs = array('Containable');
	
	public function afterSave() {
		$this->_setLowestPrice(
			$this->data['Link']['product_id'],
			$this->data['Link']['region_id'],
			$this->data['Link']['price']
		);
	}
	
	public function afterDelete() {
		$field = 'lowest_'.strtolower($this->data['Link']['region_id']);
		if($this->data['Product'][$field] == $this->data['Link']['price']) {
			$this->_setLowestPrice(
				$this->data['Link']['product_id'],
				$this->data['Link']['region_id']
			);
		}
	}
	
	private function _setLowestPrice($productId, $regionId, $price = false) {
		$this->recursive = -1;
		$links = $this->find('all', array('conditions' => array(
			'Link.product_id' => $productId,
			'Link.region_id' => $regionId
		)));
		
		if(!empty($links)) {
			if(!$price) {
				$lowestPrice = $price;
			}
			
			foreach($links as $link) {
				if(!isset($lowestPrice) || $link['Link']['price'] < $lowestPrice) {
					$lowestPrice = $link['Link']['price'];
				}
			}
		} else {
			$lowestPrice = $price;
		}
		
		$field = 'lowest_'.strtolower($regionId);
		App::import('Model', 'Product');
		$this->Product->id = $productId;
		$this->Product->set($field, $lowestPrice);
		$this->Product->save(null, array('validate' => false));
	}
}