<?php
/*
This controller is responsible for retroTrack administrator functionality.
*/

class PanelController extends AppController {
    var $uses = array('Admin','Tle','Configuration', 'Station'); 
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
        
        // Load in the ground stations
        $this->set('stations', $this->Station->find('all'));
        
        // Fetch the configuration options
        $this->set('clock_period', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'clock_period'))));
        $this->set('map_update_period', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'map_update_period'))));
        $this->set('default_ground_station', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'default_ground_station'))));
        $this->set('show_grid', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'show_grid'))));
        $this->set('show_sun', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'show_sun'))));
        $this->set('satellite_color', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'satellite_color'))));
        $this->set('ground_station_color', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'ground_station_color'))));
        $this->set('eclipse_color', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'eclipse_color'))));
        $this->set('satellite_size', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'satellite_size'))));
    }
    
    public function admin_update_configuration(){
        /*
        Processes configuration form submissions.
        
        Method: POST
        */
        
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
