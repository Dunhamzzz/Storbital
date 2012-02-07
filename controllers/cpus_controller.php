<?php
class CpusController extends AppController {
	public $name = 'Cpu';
	
	public $paginate = array(
		'limit' => 25,
		'order' => array(
		'Cpu.name' => 'asc'
		)
	);
	
	public $permissions = array(
		'admin_index' => '*',
		'admin_delete' => '*'
		
	);
	
	public function beforeFilter() {
		parent::beforeFilter();
	}
	
	public function admin_index() {
		if(!empty($this->data)) {
			if($this->Cpu->save($this->data)) {
				$this->Session->setFlash($this->data['Cpu']['name'].' Saved');
			}
		}
		
		$cpus = $this->paginate('Cpu');
		$this->set(compact('cpus'));
		$this->set('title_for_layout','Manage Processors');
	}
	
	
	
	public function admin_delete($id = null) {
		if($this->RequestHandler->isAjax()) {
			$this->autoRender = $this->layout = false;
			if($id != null && is_numeric($id)) {
				// get the Item
				$cpu = $this->Cpu->read(null,$id);
				// check Item is valid
				if(!empty($cpu) && $cpu['Cpu']['product_count'] == 0) {
					// try deleting the item
					if($this->Cpu->delete($id)) {
						exit('1');
					} else {
						exit('0');
					}
				}
				exit('0');
			}
		} else {
			$this->cakeError('error404');
		}
	}
}