<?php
class AppController extends Controller {
	public $ext = '.phtml';

	public $uses = array('Product','User','Region');
	public $helpers = array(
		'Html',
		'Text',
		'Form',
		'Time',
		'Storbital',
		'Session',
		'Js' => array('Jquery'),
		'OpenGraph'
	);
	public $components = array(
		'Cookie',
		'Auth' => array(
			'authorize' => 'controller',
			'autoRedirect' => false,
			//'loginRedirect' => array('controller' => 'products'),
			//'logoutRedirect' => array('controller' => 'products'),
			'loginAction' => array('controller' => 'users', 'action' => 'login', 'admin' => false),
		),
		'RememberMe',
		'RequestHandler',
		'Session'
	);
	
	public $permissions = array();
	public $breadcrumbs = array();
	public $localRegion, $productHistory, $shortlist, $sidebar;

	public function beforeFilter() {
	
		$this->RememberMe->check();
		if($this->Auth->user()) {
        	$this->User->id = $this->Auth->user('id');
        	$this->User->saveField('last_url', $this->params['url']['url']);
        	$this->set('user', $this->Auth->user());
        }
        
    	$this->localRegion = $this->Cookie->read('Region');
		if(empty($this->localRegion) && !is_array($this->localRegion)) {
			$this->localRegion = $this->Region->setRegion();
			$this->Cookie->write('Region', $this->localRegion);
		}
		
		
		$reg = $this->Cookie->read('Region');
		
		
    	//Shortlist
    	$this->shortlist = $this->Session->read('shortlist');
    	if(!empty($this->shortlist)) {
    		//extract shortlsit ids
	    	$shortlistArray = $this->Product->find('all', array(
	    		'conditions' => array('Product.id' => $this->shortlist),
				'fields' => array('Product.id', 'Product.name', 'Category.url_slug', 'Product.url_slug'),
				'contain' => array(false)
	    	));
	    	
	    	usort($shortlistArray, array('AppController', 'cmpShortlist'));
	    	
	    	
	    	$shortlist = array(); //Putting it into a id => product array.
	    	foreach($shortlistArray as $shortlistItem) {
	    		$shortlist[$shortlistItem['Product']['id']] = $shortlistItem;
	    	}
	    	$this->set(compact('shortlist'));
    	}
    	
		//Recent Products
		$this->productHistory = $this->Session->read('productHistory');
		if(!empty($this->productHistory)) {
			$productHistory = $this->Product->find('all', array(
				'conditions' => array('Product.id' => $this->productHistory),
				'contain' => array('Category', 'Gpu', 'Cpu')
			));
			$this->set(compact('productHistory'));
		}
		
	}
	
    public function beforeRender () {
		$this->Cookie->write('storbital.region', $this->localRegion);
		$this->set('localRegion', $this->localRegion);
		
        //Set layout to admin if accessing an admin action, but not an ajax request
        if (isset($this->params['admin']) && $this->params['admin'] && !$this->RequestHandler->isAjax()) {
            $this->layout = 'admin';
        }
        
        $this->set('sidebar', $this->sidebar);
        $this->set('breadcrumbs', $this->breadcrumbs);
    }
    
	public function isAuthorized() { //this only gets called when they try to access a restricted page
        if($this->Auth->user('group_id') == 1) return true; //Admins get it all
        if(!empty($this->permissions[$this->action])){
            if($this->permissions[$this->action] == '*') return true;
            if(in_array($this->Auth->user('group_id'), $this->permissions[$this->action])) return true;
        }
        $this->Session->setFlash('You do not have access to this area.','default', array('class' => 'bad'));
        return false;
    }
    
    public function breadcrumb($text, $slug, $title = false) {
    	if(!is_array($this->breadcrumbs)) { //might have been set to false.
    		$this->breadcrumbs = array();
    		$slug = '/'.$slug;
    	} else {
    		$path = end($this->breadcrumbs);
    		$slug = $path['url'].'/'.$slug;
    	}
    	
    	if(!$title) {
    		$title = $text;
    	}
    	
    	$this->breadcrumbs[] = array(
    		'text' => $text,
    		'url' => $slug,
    		'title' => $title
    	);
    }

	public function displayCaptcha() {
		require_once('/sites/lib/recaptchalib.php');
		return recaptcha_get_html(Configure::read('Recaptcha.publicKey'));
	}

	public function checkCaptcha() {
		require_once('/sites/lib/recaptchalib.php');
		$privatekey = Configure::read('Recaptcha.privateKey');
		$resp = recaptcha_check_answer ($privatekey,
			$_SERVER["REMOTE_ADDR"],
			$_POST["recaptcha_challenge_field"],
			$_POST["recaptcha_response_field"]
		);

		if (!$resp->is_valid) {
			return false;
		}
		return true;
	}
	
	static function cmpShortlist($a, $b) {
		return strcmp($a['Product']['name'], $b['Product']['name']);
	}
}