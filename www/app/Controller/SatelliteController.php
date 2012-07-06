<?php
/*
This controller is responsible for retroTrack administrator satellite management functionality.
*/

class SatelliteController extends AppController {
    var $uses = array('Satellite', 'Tle', 'Configuration'); 
    
    public function admin_index(){
        /*
        Displays a list of all of the currently configured satellites.
        */
        
        $this->set("title_for_layout","Manage Satellites");
        
        // Load all configured satellites
        $this->set('satellites', $this->Satellite->find('all'));
    }
    
    public function admin_add(){
        /*
        Displays the form to create a new satellite.
        */
        
        $this->set("title_for_layout","Create a Satellite");
        
        // Load the list of configured TLE's that can be used as the satellite name.
        $this->set('tle_names', $this->Tle->find('all', array('fields' => array('Tle.name'))));
    }
    
    public function admin_create(){
        /*
        Processes form submissions from add().
        
        Method: POST
        */
        
        if ($this->request->is('post')) {
            // Make sure a satellite with this name doesn't exist
            $name_check = $this->Satellite->find('first', array(
                'conditions' => array('Satellite.name' => $_POST['satellite_name'])
            ));
            
            if ($name_check){
                // Satellite exists all ready
                $this->Session->setFlash('A satellite with that name all ready exists. TLE names must be unique.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'satellite', 'action' => 'add'));
            } else {
                // Satellite doesn't exist, try to create it
                $new_satellite['Satellite']['name'] = $_POST['satellite_name'];
                $new_satellite['Satellite']['description'] = $_POST['satellite_description'];
                $new_satellite['Satellite']['created_on'] = date('Y-m-d H:i:s', time());
                $new_satellite['Satellite']['updated_on'] = date('Y-m-d H:i:s', time());
                
                $save_satellite = $this->Satellite->save($new_satellite);
                
                if ($save_satellite){
                    $this->Session->setFlash('The satellite has been added successfully.', 'default', array('class' => 'alert alert-success'));
                    CakeLog::write('satellites', '[success] New satellite \''.$_POST['satellite_name'].'\' added.');
                } else {
                    $this->Session->setFlash('An error occured while adding that satellite. Please try again.', 'default', array('class' => 'alert alert-error'));
                    CakeLog::write('satellites', '[error] Error adding satellite \''.$_POST['satellite_name'].'\' added.');
                }
                
                $this->redirect(array('controller' => 'satellite', 'action' => 'index'));
            }
        } else {
            // Redirect them to the add form
            $this->redirect(array('controller' => 'satellite', 'action' => 'add'));
        }
    }
    
    public function admin_remove(){
        /*
        Displays the satellite delete confirmation page.
        */
        
        $this->set("title_for_layout","Remove a Satellite");
        
        // Load the satellite in question
        $satellite = $this->Satellite->find('first', array('conditions' => array('Satellite.id' => $this->params->id)));
        if($satellite){
            $this->set('satellite', $satellite);
        } else {
            $this->Session->setFlash('That satellite could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_delete(){
        /*
        Actually delete the specified satellite.
        
        Method: POST
        */
        
        // Load the satellite in question
        $satellite = $this->Satellite->find('first', array('conditions' => array('Satellite.id' => $this->params->id)));
        if($satellite){
            // Delete the satellite
            $delete_attempt = $this->Satellite->delete($this->params->id);
            if ($delete_attempt){
                $this->Session->setFlash('Satellite \''.$satellite['Satellite']['name'].'\' successfully deleted.', 'default', array('class' => 'alert alert-success'));
                $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
            } else {
                $this->Session->setFlash('There was an error deleting the \''.$satellite['Satellite']['name'].'\' satellite.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
            }
        } else {
            $this->Session->setFlash('That satellite could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_edit(){
        /*
        Displays the satellite edit page.
        */
        
        $this->set("title_for_layout","Edit a Satellite");
        
        // Load the satellite in question
        $satellite = $this->Satellite->find('first', array('conditions' => array('Satellite.id' => $this->params->id)));
        if($satellite){
            $this->set('satellite', $satellite);
        } else {
            $this->Session->setFlash('That satellite could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_change(){
        /*
        Processes the form submission from admin_edit().
        
        Method: POST
        */
        
        // Load the satellite in question
        $satellite = $this->Satellite->find('first', array('conditions' => array('Satellite.id' => $this->params->id)));
        if($satellite){
            // Edit the satellite
            $satellite_changes['Satellite']['description'] = $_POST['satellite_description'];
            $satellite_changes['Satellite']['updated_on'] = date('Y-m-d H:i:s', time());
            $satellite_changes['Satellite']['id'] = $this->params->id;
            
            $edit_attempt = $this->Satellite->save($satellite_changes);
            if ($edit_attempt){
                $this->Session->setFlash('Satellite \''.$satellite['Satellite']['name'].'\' successfully edited', 'default', array('class' => 'alert alert-success'));
                $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
            } else {
                $this->Session->setFlash('There was an error editing the \''.$satellite['Satellite']['name'].'\' satellite.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
            }
        } else {
            $this->Session->setFlash('That satellite could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'satellite', 'action' => 'index', 'admin' => true));
        }
    }
}
?>
