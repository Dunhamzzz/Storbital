<?php
class ProductsController extends AppController {
	public $name = 'Products';
	public $paginate = array(
		'limit' => 10,
		'order' => array(
			'Product.created' => 'desc'
		)
	);
	
	public $permissions = array(
		'admin_dashboard' => '*',
		'admin_index' => '*',
		'admin_edit' => '*',
		'admin_home' => 1
	);
	
	public $components = array('Filter.Filter');
	public $helpers = array('Prod', 'Paginator');

	public function beforeFilter() {
		$this->Auth->allow(array('home', 'index', 'view', 'autocomplete','compare', 'search', 'type', 'shortlistDelete', 'shortlistAdd'));
		parent::beforeFilter();
	}
	
	public function index() { //homepage
		//Temporary widget fetching until more time to implement a widget-per-page system.
		$this->set('dotw', $this->Product->Widget->find('first', array(
			'conditions' => array(
				'element' => 'dotw'
			),
			'contain' => array (
				'Product' => array('Category', 'Cpu', 'Gpu')
			)
		)));
		
		$this->set('best', $this->Product->Widget->find('first', array(
			'conditions' => array(
				'element' => 'best'
			),
			'contain' => array (
				'Product' => array('Category', 'Cpu', 'Gpu')
			)
		)));
		
		$this->set('netbooks', $this->Product->find('all',
			array(
				'conditions' => array('Product.popular' => 1, 'Product.category_id' => 1),
				'limit' => 3
			)
		));
		
		$this->set('tablets', $this->Product->find('all',
			array(
				'conditions' => array('Product.popular' => 1, 'Product.category_id' => 2),
				'limit' => 3
			)
		));
		
		$this->set('nettops', $this->Product->find('all',
			array(
				'conditions' => array('Product.popular' => 1, 'Product.category_id' => 4),
				'limit' => 3
			)
		));
		$this->set('title_for_layout', 'Compare Netbook Prices and Specifications with Storbital');
	}
	
	public function all($categoryUrlSlug) {
		$category = $this->Product->Category->findByUrlSlug($categoryUrlSlug);
		
		$this->paginate = array(
			'conditions' => array(
				'Product.category_id' => $category['Category']['id']
			),
			'limit' => 20
		);
		
		$this->set(compact('category'));
		$this->set('products', $this->paginate('Product'));
		$this->set('title_for_layout', 'View all '.$category['Category']['name_plural']);
	}
	
	public function type($categoryUrlSlug) {
		$category = $this->Product->Category->findByUrlSlug($categoryUrlSlug);
		$products = $this->Product->find('all', array(
			'conditions' => array(
				'Product.popular' => 1,
				'Category.id' => $category['Category']['id']
			),
			'contain' => array('Category','Cpu','Gpu')
		));
		
		$manufacturers = $this->Product->Manufacturer->find('all', array(
			'conditions' => array('Manufacturer.popular' => 1),
			'limit' => 4
		));
		
		$this->breadcrumb($category['Category']['name_plural'], $categoryUrlSlug);
		$this->set(compact('products'));
		$this->set(compact('manufacturers'));
		$this->set('type',$category['Category']['name_plural']);
		$this->set('title_for_layout', $category['Category']['name_plural'].' at Storbital');
	}
	
	public function search() {
		App::import('Sanitize');
		//$this->paginate['conditions'] = array('Product.publish' => 1);
		if(isset($this->params['url']['q'])) {
			$terms = urldecode($this->params['url']['q']);
			
			$this->set(compact('terms'));
			$results = $this->Product->find('all', array(
				'conditions' => array(
					'Product.name LIKE' => '%'.$terms.'%',
					'Product.publish' => 1
				),
				'limit' => 5,
				'order' => 'Product.name',
				'contain' => array('Category', 'Cpu', 'Gpu')
			));
			$total = $this->Product->find('count', array(
				'conditions' => array(
					'Product.name LIKE' => '%'.$terms.'%',
					'Product.publish' => 1
				)
			));
			$this->set(compact('terms'));
			$this->set(compact('products'));
			$this->set(compact('total'));
			/*$rawKeywords = explode(',', $this->params['url']['q']);
			$keywords = $tagIds = array();
			foreach($rawKeywords as $rawKeyword) {
				$keywords[] = '\''.trim(Sanitize::paranoid($rawKeyword, array(' '))).'\'';
			}
			
			$tagQuery = "SELECT `id` FROM `tags` WHERE `tags`.`name` IN(:keywords)";
			$query = String::insert($tagQuery, array('keywords' => implode(',',$keywords)));
			$tags = $this->Product->query($query);
			
			foreach($tags as $tag) {
				$tagIds[] = '\''.$tag['tags']['id'].'\'';
			}

			$searchQuery = "SELECT COUNT(*) as `weight`, `product_id` FROM `products_tags`
				WHERE `tag_id` IN(:tagids)
				GROUP BY `product_id`
				ORDER BY `weight`
			";
			
			$query = String::insert($searchQuery, array('tagids' => implode(',',$tagIds)));
			
			$results = $this->Product->query($query);*/
	/*	/*	$this->bindModel(array('hasOne' => array(
				'ProductsTag',
				'TagMatch' => array(
					'className' => 'Tag',
					'foreignKey' => false,
					'type' => 'INNER',
					'conditions' => array(
						
					)
				)
			)));
			
			$results = $this->Product->find('all', array(
				//'contain' =>
				'conditions' => array('Product.publish' => 1),
				'group' => array('Product.id', 'Product.name HAVING COUNT(*) >= 1')
			)); */
		}
		$this->breadcrumb('Search', 'search');
		$this->set('title_for_layout', 'Search and Compare Netbooks, Tablets and Nettops with Storbital');
	}

	public function view($type, $slug) {
    	$product = $this->Product->find('first',array(
			'conditions' => array(
				'Product.publish' => 1,
				'Product.url_slug' => $slug,
				'Category.url_slug' => $type,
			),
			'contain' => array(
				'Cpu','Gpu','Category','Manufacturer','Tag'
			)
		));
		if(empty($product)) {
			$this->cakeError('error404');
		}
		
		//View History - We need to hijack it to not include current product
		if(!isset($this->productHistory) || empty($this->productHistory)) {
			$this->productHistory = array($product['Product']['id']);
		} else {
			array_unshift($this->productHistory, $product['Product']['id']);
			if(count($this->productHistory) == 6) { //only keep 5 items
				array_shift($this->productHistory);
			}
			$this->productHistory = array_unique($this->productHistory, SORT_NUMERIC);
		}
		$this->Session->write('productHistory', $this->productHistory);
		
		$currentProductKey = array_search($product['Product']['id'], $this->productHistory);
		if($currentProductKey !== false) {
			unset($this->productHistory[$currentProductKey]);
		}
		
		$productHistory = $this->Product->find('all', array(
			'conditions' => array('Product.id' => $this->productHistory))
		);
		$this->set(compact('productHistory'));
		
		// Assign Breadcrumbs
		$this->breadcrumb($product['Category']['name_plural'],$product['Category']['url_slug']);
		$this->breadcrumb($product['Product']['name'],$product['Product']['url_slug']);
    	
    	//Get link info - need to query to get foreign values
    	$links = $this->Product->Link->find('all', array(
			'conditions' => array(
				'Link.publish' => 1,
				'Link.region_id' => $this->localRegion['id'],
    			'Link.product_id' =>  $product['Product']['id']
    		),
    		'contain' => array('Colour', 'Region', 'Retailer')
    	));
    	$this->set(compact('links'));
    	
    	App::import('Model','TagGroup');
		$tagGroupModel = new TagGroup;
		$tagGroups = $tagGroupModel->find('threaded', array('order' => 'order'));
		
    	//Set Vars
    	$this->set('title_for_layout',$product['Product']['name']. ' Tech Specs and Where to Buy');
    	$this->set(compact('tagGroups'));
    	$this->set(compact('product'));
	}
	
	public function compare() {
		$products = $this->Product->find('all' , array(
			'conditions' => array('Product.id' => $this->shortlist),
			'contain' => array('Manufacturer', 'Link', 'Cpu', 'Gpu', 'Category', 'Tag' => array('TagGroup'))
		));
		
		$this->sidebar = false;
		$this->breadcrumb('Compare', 'compare');
		$this->set(compact('products'));
		$this->set('title_for_layout', 'Compare your shortlisted products with Storbital');
	}
	
	/* AJAX Actions */
	public function autocomplete() {
		if ($this->RequestHandler->isAjax()) {
			$terms = urldecode($this->params['url']['q']);
			
			$this->set(compact('terms'));
			$products = $this->Product->find('all', array(
				'conditions' => array(
					'Product.name LIKE' => '%'.$terms.'%',
					'Product.publish' => 1
				),
				'limit' => 5,
				'order' => 'Product.name',
				'contain' => array('Category', 'Cpu', 'Gpu')
			));
			$total = $this->Product->find('count', array(
				'conditions' => array(
					'Product.name LIKE' => '%'.$terms.'%',
					'Product.publish' => 1
				)
			));
			$this->set(compact('products'));
			$this->set(compact('total'));
		} else {
			$this->cakeError('error404');
		}
	}
	
	public function shortlistAdd($id) {
		$this->autoRender = $this->layout = false;
		if(!isset($this->shortlist) || !in_array($id, $this->shortlist)) {
			$product = $this->Product->find('first', array(
				'conditions' => array('Product.id' => $id),
				'fields' => array('Product.id', 'Product.name', 'Category.url_slug','Product.url_slug'),
				'contain' => array(false)
			));
		} else {
			if ($this->RequestHandler->isAjax()) {
				exit('1'); //already there
			} else {
				$this->Session->setFlash('This product is already in your shortlist.', 'default', array('class' => 'bad'));
				$this->redirect($this->referer());
			}
		}
		
		if(isset($product) && !empty($product)) {
			if(!isset($this->shortlist)) {
				$shortlist = array($id);
			} else {
				$shortlist = $this->shortlist;
				$shortlist[] = $id;
			}
			$shortlist = array_unique($shortlist, SORT_NUMERIC);
			$this->Session->write('shortlist', $shortlist);
			if ($this->RequestHandler->isAjax()) {
				$this->set(compact('product'));
				$this->render('shortlist_link');
			} else {
				$this->Session->setFlash($product['Product']['name'].' has been added to your shortlist.');
				$this->redirect($this->referer());
			}
		} else { //Bad id, no product found.
			if ($this->RequestHandler->isAjax()) {
				exit('0'); //already there
			} else {
				$this->Session->setFlash('An error occured, could not add product to shortlist.', 'default', array('class' => 'bad'));
				$this->redirect($this->referer());
			}
		}
	}
	
	public function shortlistDelete($id) {
		if($id == 'all') {
			$this->Session->delete('shortlist');
		} else {
			$idKey = array_search($id, $this->shortlist);
			if($idKey !== false) {
				unset($this->shortlist[$idKey]);
				$this->Session->write('shortlist', $this->shortlist);
			}
		}
	
		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash('Product(s) have been removed from your shortlist.');
			$this->redirect($this->referer());
		} else {
			exit('1');
		}
	}
	
	/* Admin Actions */
	public function admin_index() {
		$this->paginate['limit'] = 15;
		$this->paginate['conditions'] = array(); //remove publish = 1
		$this->Filter->initialize($this, array('actions' => array('admin_index')));
		$this->paginate = array_merge_recursive($this->paginate, $this->Filter->paginate);
		$this->set('urlOptions', $this->Filter->url);
		$products = $this->paginate();
		$this->set(compact('products'));
		$this->set('users', $this->Product->User->find('list', array('order' => 'name')));
		$this->set('manufacturers', $this->Product->Manufacturer->find('list', array('order' => 'name')));
		$this->set('title_for_layout','Manage Products');
	}
	
	public function admin_dashboard() {
		$products = $this->Product->find('all', array('order' => 'Product.created DESC'));
		$latestProducts = array_slice($products, 0, 10);
		
		
		$users = $this->Product->User->find('all', array('order' => 'User.product_count DESC'));
		
		$this->set('numProducts', count($products));
		$this->set(compact('latestProducts'));
		$this->set(compact('users'));
		$this->set('title_for_layout','Storbital Dashboard');
	}
	
	public function admin_edit($id = null) {
		$this->Product->id = $id;
		if($id && empty($this->data)) {
			$this->data = $this->Product->read();
			$this->set('title_for_layout','Edit '.$this->data['Product']['name']);
			$this->set('pageAction', 'edit');
			if(empty($this->data)) {
				$this->Session->setFlash('Invalid product ID.');
				$this->redirect(array('action' => 'index'));
			}
		} else {
			if(!empty($this->data)) {
				$userId = $this->data['Product']['user_id'];//just incase we need it later
				unset($this->data['Product']['user_id']);
				if($this->data['Product']['publish'] == 0) { // do not validate if it's not being published
					$this->Product->validate = array();
				}
				$this->data['Product']['last_modified_user_id'] = $this->Auth->user('id');
				if($this->Product->save($this->data)) {
					$this->Session->setFlash('Changes made to '.$this->data['Product']['name'].' have been saved.');
					$this->data = $this->Product->read();
				}
				
				$this->set('title_for_layout','Edit '.$this->data['Product']['name']);
				$this->set('pageAction', 'edit');
			} else {
				$this->set('title_for_layout', 'Add Product');
				$this->set('pageAction', 'add');
			}
		}
		
		App::import('Model','TagGroup');
		$tagGroup = new TagGroup;
		$tagGroupsThreaded = $tagGroup->find('threaded', array('order' => 'order'));
		$this->set(compact('tagGroupsThreaded'));
		$this->set('tagGroups', $tagGroup->find('list', array('order' => 'order')));
		$this->set('categories', $this->Product->Category->find('list', array('order' => 'name')));
		$this->set('cpus', $this->Product->Cpu->find('list', array('order' => 'name')));
		$this->set('gpus', $this->Product->Gpu->find('list', array('order' => 'name')));
		$this->set('colours', $this->Product->Colour->find('list', array('order' => 'name')));
		$this->set('manufacturers', $this->Product->Manufacturer->find('list', array('order' => 'name')));
	}
	
	
	function admin_ajax_toggle_popular() {
		if (!$this->RequestHandler->isAjax()) {
			$this->Session->setFlash('This is an ajax function only!');
			$this->redirect(array('action' => 'index'));
		}
		
		$this->Product->id = preg_replace("/[^0-9]/", '', $this->data['id']);
		$this->Product->saveField('popular', $this->data['popular']);
		$this->set('return', 1);
		$this->render('/elements/ajaxreturn');
		return;
	}
}