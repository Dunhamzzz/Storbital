<?php
class GpusController extends AppController {
	public $name = 'Gpu';
	
	public $paginate = array(
		'limit' => 25,
		'order' => array(
		'Gpu.name' => 'asc'
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
			if($this->Gpu->save($this->data)) {
				$this->Session->setFlash($this->data['Gpu']['name'].' Saved');
			}
		}
		
		$gpus = $this->paginate('Gpu');
		$this->set(compact('gpus'));
		$this->set('title_for_layout','Manage GPUs');
	}
	
	
	
	public function admin_delete($id = null) {
		if($this->RequestHandler->isAjax()) {
			$this->autoRender = $this->layout = false;
			if($id != null && is_numeric($id)) {
				// get the Item
				$Gpu = $this->Gpu->read(null,$id);
				// check Item is valid
				if(!empty($Gpu) && $Gpu['Gpu']['product_count'] == 0) {
					// try deleting the item
					if($this->Gpu->delete($id)) {
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