<?php
class ReviewsController extends AppController {
	public $name = 'Reviews';
	public $permissions = array(
		'admin_index' => 1
	);
	
	public function beforeFilter() {
		$this->Auth->allow('add');
		parent::beforeFilter();
	}
	
	public function add($productId = null) {
		if(!empty($this->data['Review'])) {
			$this->data['Review']['user_id'] = $this->Auth->user('id');
			$this->data['Review']['publish'] = 0;
			
			if($this->Review->save($this->data)) {
				$this->Session->setFlash('Your review has been submitted, it will appear on the site once it has been approved.');
				$this->redirect(array('controller' => 'products', 'action' => 'view'));
			}
		}
		
		if(!isset($productId)) {
			$productId = $this->data['Review']['product_id'];
		}
		
		$product = $this->Review->Product->findById($productId);
		$products = $this->Review->Product->find('list', array('order' => 'name asc'));
		
		$this->set(compact('product'));
		$this->set(compact('products'));
		$this->set('title_for_layout', 'Add a Review');
	}
	
	public function admin_index() {
		$reviews = $this->Review->find('all');
		
		$this->set(compact('reviews'));
		$this->set('title_for_layout', 'Manage Reviews');
	}
}