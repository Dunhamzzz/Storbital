<?php
class WidgetsController extends AppController {
	public $name = 'Widgets';
	
	public function admin_index() {
		pr($this->data);
		/*
		if(!empty($this->data)) {
			if($this->ProductWidget->save($this->data)) {
				$this->Session->setFlash('Homepage updated');
			}
		}*/
		
		$widgets = $this->Widget->find('all',
			array('contain' => array(
				'Product','Product.Category','Product.Cpu','Product.Gpu'
			)
		));
		
		
		/*//Organize widgets by key.
		$productBoxes = array();
		foreach($productWidgetsArray as $productWidget) {
			$productWidgetTypes[$productWidget['ProductWidget']['key']][] = $productWidget;
		}*/
		
		$this->set('products', $this->Product->find('list', array('order' => 'name asc')));
		$this->set(compact('widgets'));
		$this->set('title_for_layout', 'Edit Widgets');
	}
	
	public function admin_edit($id = null) {
		$this->Widget->id = $id;
		if($id && empty($this->data)) {
			$this->data = $this->Widget->read();
			$this->set('title_for_layout','Edit '.$this->data['Widget']['name']);
			$this->set('pageAction', 'edit');
			if(empty($this->data)) {
				$this->Session->setFlash('Invalid product ID.');
				$this->redirect(array('action' => 'index'));
			}
		} else {
			if(!empty($this->data)) {
				if($this->Widget->save($this->data)) {
					$this->Session->setFlash('Changes made to '.$this->data['Widget']['name'].' have been saved.');
					$this->data = $this->Widget->read();
				}
				
				$this->set('title_for_layout','Edit '.$this->data['Widget']['name']);
				$this->set('pageAction', 'edit');
			} else {
				$this->set('title_for_layout', 'Add Product');
				$this->set('pageAction', 'add');
			}
		}
		
		$this->set('products', $this->Product->find('list', array('order' => 'name')));
	}

	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(sprintf(__('Invalid id for %s', true), 'widget'));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Widget->delete($id)) {
			$this->Session->setFlash(sprintf(__('%s deleted', true), 'Widget'));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(sprintf(__('%s was not deleted', true), 'Widget'));
		$this->redirect(array('action' => 'index'));
	}
}
?>