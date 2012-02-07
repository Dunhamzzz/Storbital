<?php
class TagsController extends AppController {
	public $name = 'Tags';
	
	public function admin_add() {
		
		$this->set('tagGroups', $this->Tag->TagGroup->find('list'));
		$this->set('title_for_layout', 'Add Tags');
	}
	
	public function admin_optionlist($tagGroup) {
		
	}
}