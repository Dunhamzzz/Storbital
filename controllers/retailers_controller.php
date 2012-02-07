<?php
class RetailersController extends AppController {
	var $paginate = array(
		'limit' => 20,
		'order' => array(
			'Retailer.name' => 'asc'
		)
	);
	var $permissions = array('admin_index' => '*');
	
	function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow(array('index', 'view'));
	}
	
	function index() {
		
	}
	
	function view($urlSlug) {
		$retailer = $this->Retailer->findByUrlSlug($urlSlug);
		$this->set(compact('retailer'));
	}
	
	function admin_index($id = null) {
		$this->Retailer->id = $id;
		if ($id && empty($this->data)) {
			$this->data = $this->Retailer->read();
			if(empty($this->data)) {
				$this->Session->setFlash('Invalid retailer ID.');
				$this->redirect(array('action' => 'index'));
			}
			$pageAction = 'edit';
		} else {
			if (!empty($this->data) && $this->Retailer->save($this->data)) {
				$this->Session->setFlash('Retailer has been saved.');
				$this->redirect(array('action' => 'index'));
			} else {
				$pageAction = 'add';
			}
		}
		
		$retailers = $this->paginate();
		$this->set(compact('retailers'));
		$this->set(compact('pageAction'));
		$this->set('title_for_layout','Manage Retailers');
	}
}