<?php
class UsersController extends AppController {
	var $name = 'Users';
	var $permissions = array(
		'admin_index' => 1,
		'admin_edit' => 1,
   		'admin_delete' => 1,
	);

	function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->autoRedirect = false;
		$this->Auth->allow('register', 'login', 'logout');
	}
	
	function register() {
		if($this->Auth->user()) {
			$this->redirect('/');
			return;
		}
		if (isset($this->data)) {
			$this->data['User']['group_id'] = 3;
			$this->User->create();
			if ($this->User->save($this->data)) {
				$this->Session->setFlash('Thank you for registering, please login below using the details you provided.');
				$this->redirect(array('controller' => 'user', 'action'=>'login'));
			} else {
				print_r($this->User->invalidFields());
				// Make the password fields blank
				unset($this->data['User']['password']);
				unset($this->data['User']['confirm_password']);
				$this->Session->setFlash('An error occurred, try again!');
			}
		}
			
		$this->set('title_for_layout', 'Storbital Registration');
	}
	
	function login() {
		$this->set('title_for_layout', 'Login to Storbital');
		$this->breadcrumb('Login', 'users/login');
		if($this->Auth->user()) {
			if(!empty($this->data)) {
				if(empty($this->data['User']['remember_me']) || $this->data['User']['remember_me'] == 0) {
					$this->RememberMe->delete();
				} else {
					$this->RememberMe->remember($this->data['User']['username'], $this->data['User']['password'] );
				}
				$this->User->id = $this->Auth->user('id');
				$this->User->saveField('last_login', date(DATE_ATOM));
				$this->redirect($this->Auth->redirect());
			} else {
				$this->redirect($this->referer());
			}
		}
	}

	function logout() {
		$this->Session->setFlash('Your are now logged out');
		$this->RememberMe->delete();
		$this->redirect($this->Auth->logout());
	}

	function admin_index() {
		$users = $this->User->find('all');
		$this->set(compact('users'));
		$this->set('title_for_layout', 'Manage Users');
	}

	function admin_edit($id = null) {
		$this->User->id = $id;
		if ($id && empty($this->data)) { //Load record for edit
			$this->data = $this->User->read();
			if(empty($this->data)) {
				$this->Session->setFlash('Invalid link ID.');
				$this->redirect(array('action' => 'index'));
			}
			$pageAction = 'edit';
		} else {
			if(!empty($this->data)) {
				$this->User->set($this->data);
				if($this->User->validates()) {
					if ($this->User->save($this->data)) {
						$this->Session->setFlash('User has been saved.');
						$this->redirect(array('action' => 'index'));
					}
				}
			}
			$pageAction = 'add';
		}

		$this->set(compact('pageAction'));
		$this->set('groups', $this->User->Group->find('list'));
	}

	function admin_delete($id) {
		$user = $this->User->findById($id);
		if (empty($user)) {
			$this->Session->setFlash('Invalid User ID', 'default', array('class'=>'bad'));
		} else {
			if ($user['User']['id'] == $this->Session->read('Auth.User.id')) {
				$this->Session->setFlash('Sorry, you can not delete yourself', 'default', array('class'=>'bad'));
			} else {
				if ($this->User->del($id)) {
					$this->Session->setFlash($user['User']['name'].' Deleted', 'default', array('class'=>'good'));
				} else {
					$this->Session->setFlash('Failed to delete '.$user['User']['name'], 'default', array('class'=>'bad'));
				}
			}
		}
		$this->redirect('admin_index');
	}
}