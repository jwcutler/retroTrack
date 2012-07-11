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
        $this->set('clock_update_period', $this->Configuration->find('first', array('conditions' => array('Configuration.name' => 'clock_update_period'))));
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
        
        if ($this->request->is('post')){
            // Loop through the configuration fields
            $new_configuration = array();
            foreach($_POST['config'] as $field_id => $field_value){
                $temp_field = array(
                    'id' => $field_id,
                    'value' => $field_value
                );
                
                array_push($new_configuration, $temp_field);
            }
            
            // Loop through the boolean configuration options
            $new_configuration_bool = array();
            foreach($_POST['config_bool_list'] as $field_id => $field_value){
                if (isset($_POST['config_bool'][$field_id])){
                    // Enable field
                    $temp_field = array(
                        'id' => $field_id,
                        'value' => '1'
                    );
                    
                    array_push($new_configuration_bool, $temp_field);
                } else {
                    // Disable field
                    $temp_field = array(
                        'id' => $field_id,
                        'value' => '0'
                    );
                    
                    array_push($new_configuration_bool, $temp_field);
                }
            }
            
            
            // Save the configuration
            $new_configuration = array_merge($new_configuration, $new_configuration_bool);
            $save_attempt = $this->Configuration->saveMany($new_configuration);
            
            if ($save_attempt){
                $this->Session->setFlash('The general configuration has been updated successfully.', 'default', array('class' => 'alert alert-success'));
                CakeLog::write('admin', '[success] Configuration successfully saved.');
                $this->redirect(array('controller' => 'panel', 'action' => 'index'));
            } else {
                $this->Session->setFlash('There was an error saving the general configuration. Please try again.', 'default', array('class' => 'alert alert-error'));
                CakeLog::write('admin', '[error] Error saving the configuration, please try again.');
                $this->redirect(array('controller' => 'panel', 'action' => 'index'));
            }
        } else {
            // Redirect them to the add form
            $this->redirect(array('controller' => 'panel', 'action' => 'index'));
        }
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
        
		if ($this->Auth->user()){
			$this->redirect(array('controller' => 'panel', 'action' => 'index', 'admin' => true));
		} else {
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
    }

    public function admin_logout(){
        /*
        Process logout requests.
        */
        
        $this->redirect($this->Auth->logout());
    }
}
?>
