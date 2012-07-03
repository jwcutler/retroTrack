<?php
/*
This controller is responsible for retroTrack administrator functionality.
*/

class AdminController extends AppController {
    function beforeFilter(){
        parent::beforeFilter();
        
        // Let the user access the login page
        $this->Auth->allow('login', 'add'); 
    }
    
    public function index() {
        /*
        Displays the main administrator menu.
        */
        
        
    }
    
    public function add() {
        if ($this->request->is('post')) {
            $this->Admin->create();
            if ($this->Admin->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        }
    }
    
    public function login(){
        /*
        Process login requests.
        */
        
        if ($this->request->is('post')) {
            //var_dump($_POST);
            
            if ($this->Auth->login()) {
                $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash('Invalid username or password. Please try again.', 'default', array('class' => 'alert alert-error'));
            }
        }
    }

    public function logout(){
        /*
        Process logout requests.
        */
        
        $this->redirect($this->Auth->logout());
    }
}
?>
