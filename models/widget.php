<?php
class Widget extends AppModel {
	public $name = 'widget';
	public $belongsTo = array('Product');
	public $actsAs = array('Containable');
}