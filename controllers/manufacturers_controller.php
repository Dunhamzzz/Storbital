<?php
class ManufacturersController extends AppController {
	var $paginate = array(
		'limit' => 10,
		'order' => array(
			'Manufacturer.name' => 'asc'
		)
	);
	
	var $permissions = array('admin_index' => '*');
	
    function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('index', 'view');
    }
    
	function index() {
		$manufacturers = $this->Manufacturer->find('all', array('order' => array('name' => 'ASC')));
		$this->set(compact('manufacturers'));
	}
	
	function view($slug) {
		$manufacturer = $this->Manufacturer->findByUrlSlug($slug);
		if(empty($manufacturer)) {
			$this->cakeError('error404');
		}
		
		$this->set(compact('manufacturer'));
	}
	
	function admin_index( $id = null ) {
		$this->Manufacturer->id = $id;
		if ($id && empty($this->data)) {
			$this->data = $this->Manufacturer->read();
			if(empty($this->data)) {
				$this->Session->setFlash('Invalid manu ID.');
				$this->redirect(array('action' => 'index'));
			}
			$pageAction = 'edit';
		} else {
			if (!empty($this->data) && $this->Manufacturer->save($this->data)) {
				$this->Session->setFlash('Manufacturer has been saved.');
				$this->redirect(array('action' => 'index'));
			} else {
				$pageAction = 'add';
			}
		}
		
		$manus = $this->paginate();
		$this->set(compact('manus'));
		$this->set(compact('pageAction'));
		$this->set('title_for_layout', 'Manage Manufacturers');
	}
}