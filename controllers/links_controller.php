<?php
class LinksController extends AppController {
	var $name = 'Links';
	var $paginate = array(
		'limit' => 25,
		'order' => array(
			'Link.publish' => 'asc', 'Product.url_slug'
		)
	);
	var $helpers = array('Paginator');
	var $components = array('Filter.Filter');
	var $permissions = array(
		'admin_index' => '*',
		'admin_edit' => '*',
		'admin_ajaxTogglePublish' => '*',
		'admin_ajaxUpdateUrl' => '*'
	);
    
    function beforeFilter() {
        parent::beforeFilter();
    	$this->Auth->allow('add');
    }

	function add($productId = null) {
		if(!is_numeric($productId)) {
			$productId = null;
		}
		$this->set(compact('productId'));
		$this->set('captcha', $this->displayCaptcha());
		$this->set('products', $this->Link->Product->find('list', array('order' => 'name')));
		$this->set('retailers', $this->Link->Retailer->find('list', array('order' => 'name')));
		$this->set('regions', $this->Link->Region->find('list'));
		if (!empty($this->data)) {
			$this->Link->set($this->data);
			if($this->Link->validates() && $this->checkCaptcha()) {
				$this->data['Link']['publish'] = 0;
				$this->data['Link']['user_id'] = 0;
				if ($this->Link->save($this->data)) {
					$this->Session->setFlash('Your link has been submitted and will be published once it has been approved.');
					$this->redirect(array('action' => 'add'));
				}
			}
			if(!$this->checkCaptcha()){
				$this->set('captchaError',true);
			}
		}
		
		$this->set('title_for_layout', 'Add a Link to Storbital');
	}
	
	function admin_index() {
		$this->Filter->initialize($this, array('actions' => array('admin_index')));
		$this->paginate = array_merge_recursive($this->paginate, $this->Filter->paginate);
		$this->set('urlOptions', $this->Filter->url);
		$links = $this->paginate();
		$this->set(compact('links'));
		$this->set('products', $this->Link->Product->find('list', array('order' => 'name')));
		$this->set('retailers', $this->Link->Retailer->find('list', array('order' => 'name')));
		$this->set('title_for_layout','Manage Links');
	}
	
	function admin_edit( $id = null ) {
		$this->Link->id = $id;
		if ($id && empty($this->data)) { //Load record for edit
			$this->data = $this->Link->read();
			if(empty($this->data)) {
				$this->Session->setFlash('Invalid link ID.');
				$this->redirect(array('action' => 'index'));
			}
			$pageAction = 'edit';
		} else { //Save
			if(!empty($this->data)) {
				$this->data['Product']['id'] = $id;
				$this->data['Link']['user_id'] = $this->Auth->user('id');
				if ($this->Link->save($this->data)) {
					$this->Session->setFlash('Link has been saved.');
					$this->redirect(array('action' => 'index'));
				}
			}
			if(isset($this->params['named']['productId']) && is_numeric($this->params['named']['productId'])) {
				$productId = $this->params['named']['productId'];
			} else {
				$productId = null;
			}
			$pageAction = 'add';
		}
		
		//Get Retailer/Product Info
		$this->set(compact('productId'));
		$this->set('products', $this->Link->Product->find('list', array('order' => 'name')));
		$this->set('retailers', $this->Link->Retailer->find('list', array('order' => 'name')));
		$this->set('colours', $this->Link->Colour->find('list', array('order' => 'name')));
		$this->set('regions', $this->Link->Region->find('list'));
		$this->set(compact('pageAction'));
		
		if($pageAction == 'edit') {
			$title_for_layout = 'Edit Link';
		} else {
			$title_for_layout = 'Add Link';
		}
		$this->set(compact('title_for_layout'));
	}
	
	function admin_delete($id = null) {
		if(is_numeric($id)) {
			$link = $this->Link->read(null, $id);
			if(!empty($link)) {
				// try deleting the item
				if($this->Link->delete($id)) {
					$success = '1';
				} else {
					$success = '0';
				}
			} else {
				$success = '0';
			}
		} else {
			$success = '0';
		}
		
		if($this->RequestHandler->isAjax()) {
			exit($success);
		} else {
			if($success == '1') {
				$message = 'Link deleted successfully.';
			} else {
				$message = 'Unable to delete link.';
			}
			$this->Session->setFlash($message);
			$this->redirect(array('action' => 'index'));
		}
	}
	
	function admin_ajax_toggle_publish() {
		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash('This is an ajax function only!');
			$this->redirect(array('action' => 'index'));
		}
		$this->data['id'] = preg_replace("/[^0-9]/", '', $this->data['id']);
		$link = $this->Link->read(null, $this->data['id']);
		if (!empty($link) && $this->Link->save($this->data, true, array('publish'))) {
			$this->set('return', 1);
			$this->render('/elements/ajaxreturn');
			return;
		} else {
			$this->set('return', 0);
			$this->render('/elements/ajaxreturn');
			return;
		}
		$this->set('return', 1);
		$this->render('/elements/ajaxreturn');
		return;
	}
	
	function admin_ajaxUpdateUrl () {
		if ($this->RequestHandler->isAjax()) {
			$this->autoRender = $this->layout = false;
			$this->data['id'] = preg_replace("/[^0-9]/", '', $this->data['id']);
			$this->Link->read(null, $this->data['id']);
			$oldUrl = $this->Link->data['Link']['url'];
			$return = array('id' => $this->data['id']);
			if ($this->Link->save($this->data, true, array('url'))) {
				$return['success'] = 1;
				$return['value'] = $this->data['url'];
			} else {
				$return['success'] = 0;
				$return['value'] = $oldUrl;
			}
			echo json_encode($return);
			exit;
		} else {
			$this->cakeError('error404');
		}
	}
}