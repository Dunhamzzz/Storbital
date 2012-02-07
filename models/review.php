<?php
class Review extends AppModel {
	var $name = 'Review';
	var $belongsTo = array(
		'User',
		'Product'
	);
	
	var $validate = array(
		'id' => array('rule' => 'blank', 'on' => 'create'),
		'product_id' => array('rule' => 'numeric','required' => 'true'),
		'user_id' => array('rule' => 'numeric','required' => 'true'),
		'title' => array(
			'rule' => 'notEmpty',
			'required' => 'true'
		),
		'body' => array(
			'rule' => 'notEmpty',
			'required' => 'true'
		),
		'rating' => array(
			'rule' => array('between', 1,10),
			'required' => 'true'
		)
	);
}