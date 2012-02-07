<?php
class RegionsController extends AppController {
	var $name = 'Regions';

    function beforeFilter() {
    	$this->Auth->allow('change');
    	parent::beforeFilter();
    }
    
	function change($id = false) {
    	$this->Cookie->write('storbital.region', $this->Region->setRegion($id));
    	$this->redirect($this->referer());
    }
}