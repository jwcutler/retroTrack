<?php
/*
This controller is responsible for retroTrack administrator functionality.
*/

class PanelController extends AppController {
    var $uses = array('Admin','Tle','Configuration'); 
    var $components = array('RequestHandler');
    
    function beforeFilter(){
        parent::beforeFilter();
        
        // Let the user access the login page
        $this->Auth->allow('admin_login', 'admin_add', 'admin_tleupdate'); 
    }
    
    public function admin_index() {
        /*
        Displays the main administrator menu.
        */
        
        // Fetch all existing TLE's
        $this->set('tles', $this->Tle->find('all'));
        $this->set('tle_last_update', $this->Configuration->convertTimestamp('tle_last_update'));
    }
    
    public function admin_tleupdate(){
        /*
        Processes the TLE source file and stores each entry in the database.
        
        Accessed via Ajax. Outputs 'okay' on success and 'error' on error.
        */
        
        // Attempt to update the TLE's
        $update_status = $this->Tle->updateTles();
        
        // Log the update
        if ($update_status){
            CakeLog::write('tles', '[success] TLE\'s successfully updated.');
        } else {
            CakeLog::write('tles', '[error] There was an error updating the TLE\'s.');
        }
        
        $this->set('update_status', $update_status);
    }
    
    public function admin_login(){
        /*
        Process administrator login requests.
        */
        
        if ($this->request->is('post')) {
            // Store user credentials
            $this->data['Admin']['username'] = $_POST['username'];
            $this->data['Admin']['password'] = $_POST['password'];
            
            if ($this->Auth->login($this->data)) {
                $this->Session->setFlash('You have been logged in. Welcome back.', 'default', array('class' => 'alert alert-success'));
                $this->redirect($this->Auth->redirect());
            } else {
                $this->Session->setFlash('Invalid username or password. Please try again.', 'default', array('class' => 'alert alert-error'));
            }
        }
    }

    public function admin_logout(){
        /*
        Process logout requests.
        */
        
        $this->redirect($this->Auth->logout());
    }
}
?>
