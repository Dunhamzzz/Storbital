<?php
class Retailer extends AppModel {
	var $name = 'Retailer';
	var $hasMany = array(
		'Link',
		/*'Rating' => array(
			'className'   => 'Rating',
			'foreignKey'  => 'model_id',
			'conditions' => array('Rating.model' => 'Retailer'),
			'dependent'   => true,
			'exclusive'   => true
		)*/
	);
	
	var $actsAs = array(
		'MeioUpload.MeioUpload' => array(
			'logo' => array(
				'thumbsizes' => array('display' => array('width' => 100, 'height' => 50)),
				'uploadName' => 'url_slug',
				'create_directory' => true,
	            'allowed_mime' => array('image/jpeg', 'image/pjpeg', 'image/png'),
				'allowed_ext' => array('.jpg', '.jpeg', '.png'),
				'fields' => array(
						'dir' => 'logo_dir',
						'filesize' => 'logo_filesize',
						'mimetype' => 'logo_mimetype'
					),
			)
		)
    );
}