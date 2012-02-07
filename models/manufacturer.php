<?php
class Manufacturer extends AppModel {
	var $name = 'Manufacturer';
	var $hasMany = 'Product';
	var $actsAs = array(
		/*'MeioUpload.MeioUpload' => array(
			'logo' => array(
				'create_directory' => true,
				'thumbsizes' => array(
					'normal' => array('width' => 200, 'height'=> 200)
				),
				'uploadName' => 'name',
	            'allowedMime' => array('image/jpeg', 'image/pjpeg', 'image/png'),
				'allowedExt' => array('.jpg', '.jpeg', '.png'),
				'fields' => array(
						'dir' => 'logo_dir',
						'filesize' => 'logo_filesize',
						'mimetype' => 'logo_mimetype'
					),
			)
		)*/
    );
}