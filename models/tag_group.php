<?php
class TagGroup extends AppModel {
	public $name = 'TagGroup';
	public $hasMany = 'Tag';
}