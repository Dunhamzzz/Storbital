<?php
class User extends AppModel {
    var $name = 'User';
    var $belongsTo = array('Group');
    var $hasMany = array('Product', 'Link');
    var $validate = array(
    	'id' => array('rule' => 'blank', 'on' => 'create'),
    	  'username' => array(
            'empty' => array(
             	'rule' => 'notEmpty',
	            'required' => true,
	            'allowEmpty' => false,
    			'last' => true,
                'message' => 'Please enter a username.'
            ),
            'length' => array(
                'rule' => array('between', 4, 20),
          	  	'last' => true,
                'message' => 'Usernames must be between 4 and 20 chracters long.'
            ),
            'validateFormat' => array(
                'rule' => 'alphaNumericDashUnderscore',
           		'last' => true,
                'message' => 'Only letters and numbers are allowed in usernames.'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'That username is already in use, please choose another.',
            )
        ),
        
        'password' => array(
			'rule' => array('confirmPassword', 'password'),
			'message' => 'Passwords do not match',
			'required' => 'true'
		),
        
        'name' => array(),
        
        'email' => array(
            'empty' => array(
                'rule' => 'notEmpty',
                'required' => true,
                'allowEmpty' => false,
        		'last' => true,
                'message' => 'Please enter an email address'
            ),
            'email' => array(
                'rule' => 'email',
                'required' => true,
                'allowEmpty' => true,
            	'last' => true,
                'message' => 'Please enter a valid email address'
            ),
            'unique' => array(
                'rule' => 'isUnique',
                'required' => true,
                'allowEmpty' => true,
                'message' => 'That email address is already in use by another user'
            )
        ),
        
        'group_it' => arraY('numeric')
    );
    
	function alphaNumericDashUnderscore($check) {
      // $data array is passed using the form field name as the key
      // have to extract the value to make the function generic
      $value = array_values($check);
      
      return preg_match('|^[0-9a-zA-Z_-]*$|', $value[0]);
    }
    

    function emptyUpdate() {
        if (!empty($this->data['User']['clear_password']) && empty($this->data['User']['confirm_password'])) {
            return false;
        } else {
            return true;
        }
    }

	function confirmPassword($data) {
		// We must manually hash the second piece in the same way the AuthComponent would
		if ($data['password'] == Security::hash(Configure::read('Security.salt') . $this->data['User']['confirm_password'])) {
			return true;
		}

		return false;
	}
}