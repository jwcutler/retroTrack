<?php
/*
This controller is responsible for retroTrack administrator functionality.
*/

class PanelController extends AppController {
    var $uses = array('Admin','Tle','Configuration', 'Station'); 
    var $components = array('RequestHandler');
    
    function beforeFilter(){
        parent::beforeFilter();
        
        // Setup authentication
        $this->Auth->authenticate = array(
            'Form' => array('userModel' => 'Admin')
        );
        
        // Let the user access the login page
        $this->Auth->allow('admin_login', 'admin_add', 'admin_tleupdate', 'admin_makehash', 'admin_generatehash'); 
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
        
        // Load the configuration options
        $configurations = $this->Configuration->find('all');
        foreach($configurations as $configuration){
            $this->set($configuration['Configuration']['name'], $configuration);
        }
    }
    
    public function admin_generatehash(){
        /*
        Generates a password hash for the posted password.
        */
        
        $password = (isset($_POST['password']))?$_POST['password']:'';
        $this->layout = 'ajax';
        
        if (isset($password)&&!empty($password)){
            // Generate the password hash
            $password_hash = $this->Auth->password($password);
            $this->set('hash_response', $password_hash);
        } else {
            // No password specified
            $this->set('hash_response', 'No password was specifed. Please try again.');
        }
    }
    
    public function admin_makehash(){
        /*
        Displays the form to generate a password hash.
        */
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
        
        // Load the source URL
        $tle_source = $this->Configuration->find('first', array(
            'conditions' => array('Configuration.name' => 'tle_source')
        ));
        $tle_source = $tle_source['Configuration']['value'];
        
        // Attempt to update the TLE's
        $update_status = $this->Tle->updateTles($tle_source);
        
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
			if ($this->request->is('post')){
				if ($this->Auth->login()) {
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
