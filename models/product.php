<?php
class Product extends AppModel {
	public $name = 'Product';
	public $belongsTo = array(
		'User' => array('counterCache' => true),
		'Manufacturer' => array('counterCache' => true, 'counterScope' => array('Product.publish' => 1)),
		'Category',
		'Cpu' => array('counterCache' => true),
		'Gpu' => array('counterCache' => true),
	);
	public $hasMany = array('Link','Widget');
	public $hasAndBelongsToMany = array('Colour', 'Tag');
	
	public $actsAs = array(
		'Containable',
		'MeioUpload.MeioUpload' => array(
			'image' => array(
				'create_directory' => true,
				'uploadName' => 'url_slug',
				'thumbsizes' => array(
					'large' => array('width' => 300, 'height' => 250),
					'normal' => array('width' => 200, 'height'=> 200),
					'small' => array('width' => 80, 'height' => 80)
				),
	            'allowedMime' => array('image/jpeg', 'image/pjpeg', 'image/png'),
				'allowedExt' => array('.jpg', '.jpeg', '.png'),
				'fields' => array(
						'dir' => 'image_dir'
				),
				'validations' => array(
					'Empty' => array(
						'check' => false,
					)
				)
			)
		)
    );
    
    public $validate = array(
    	'name' => array(
    		'rule' => 'isUnique',
    		'message' => 'It appears we already have this product'
    	),
    	'manufacturer_id' => array(
    		'rule' => 'numeric',
    		'allowEmpty' => false,
    		'message' => 'Please select a manufacturer.'
    	),
    	
    	'url_slug' =>  array(
    		'format' => array(
                'rule' => 'alphaNumericDashUnderscore',
           		'last' => true,
                'message' => 'Only letters, numbers, dashes and underscores are allowed in the URL Slug.'
            )
		)
    );
    
	public function alphaNumericDashUnderscore($check) {
      // $data array is passed using the form field name as the key
      // have to extract the value to make the function generic
      $value = array_values($check);
      
      return preg_match('|^[0-9a-zA-Z_-]*$|', $value[0]);
    }
}