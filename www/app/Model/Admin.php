<?php
/*
Administrator Account Model

Manages retroTrack administrator accounts.
*/

App::uses('AuthComponent', 'Controller/Component');

class Admin extends AppModel {
    public $name = 'Admin';
    
    // Setup validation rules
    public $validate = array(
        'username' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please enter a username.'
            )
        ),
        'password' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Please enter your password.'
            )
        )
    );
    
    public function beforeSave() {
        // Hash the password before saving it
        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias]['password']);
        }
        
        return true;
    }
}

?>
