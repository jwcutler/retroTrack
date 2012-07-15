<?php
/*
This controller is responsible for retroTrack administrator ground station management functionality.
*/

class StationController extends AppController {
    var $uses = array('Station'); 
    
    public function admin_index(){
        /*
        Displays a list of all of the currently configured ground stations.
        */
        
        $this->set("title_for_layout","Manage Ground Stations");
        
        // Load all configured ground stations
        $this->set('stations', $this->Station->find('all'));
    }
    
    public function admin_add(){
        /*
        Displays the form to create a new ground station.
        */
        
        $this->set("title_for_layout", "Create a Ground Station");
    }
    
    public function admin_create(){
        /*
        Processes form submissions from add().
        
        Method: POST
        */
        
        if ($this->request->is('post')) {
            // Make sure a ground station with that name doesn't exist
            $name_check = $this->Station->find('first', array(
                'conditions' => array('Station.name' => $_POST['station_name'])
            ));
            
            if ($name_check){
                // Station exists all ready
                $this->Session->setFlash('A ground station with that name all ready exists. Station names must be unique.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'station', 'action' => 'add'));
            } else {
                // Station doesn't exist, try to create it
                $new_station['Station']['name'] = $_POST['station_name'];
                $new_station['Station']['description'] = $_POST['station_description'];
                $new_station['Station']['longitude'] = $_POST['station_longitude'];
                $new_station['Station']['latitude'] = $_POST['station_latitude'];
                $new_station['Station']['created_on'] = date('Y-m-d H:i:s', time());
                $new_station['Station']['updated_on'] = date('Y-m-d H:i:s', time());
                
                $save_station = $this->Station->save($new_station);
                
                if ($save_station){
                    $this->Session->setFlash('The station has been added successfully.', 'default', array('class' => 'alert alert-success'));
                    CakeLog::write('admin', '[success] New station \''.$_POST['station_name'].'\' added.');
                } else {
                    $this->Session->setFlash('An error occured while adding that station. Please try again.', 'default', array('class' => 'alert alert-error'));
                    CakeLog::write('admin', '[error] Error adding station \''.$_POST['station_name'].'\' added.');
                }
                
                $this->redirect(array('controller' => 'station', 'action' => 'index'));
            }
        } else {
            // Redirect them to the add form
            $this->redirect(array('controller' => 'station', 'action' => 'add'));
        }
    }
    
    public function admin_remove(){
        /*
        Displays the ground station delete confirmation page.
        */
        
        $this->set("title_for_layout","Remove a Ground Station");
        
        // Load the ground station in question
        $station = $this->Station->find('first', array('conditions' => array('Station.id' => $this->params->id)));
        if($station){
            $this->set('station', $station);
        } else {
            $this->Session->setFlash('That station could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_delete(){
        /*
        Actually delete the specified ground station.
        
        Method: POST
        */
        
        // Load the station in question
        $station = $this->Station->find('first', array('conditions' => array('Station.id' => $this->params->id)));
        if($station){
            // Delete the station
            $delete_attempt = $this->Station->delete($this->params->id);
            if ($delete_attempt){
                $this->Session->setFlash('Ground station \''.$station['Station']['name'].'\' successfully deleted.', 'default', array('class' => 'alert alert-success'));
                CakeLog::write('admin', '[success] Ground station \''.$station['Station']['name'].'\' deleted.');
                $this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
            } else {
                $this->Session->setFlash('There was an error deleting the \''.$station['Station']['name'].'\' station.', 'default', array('class' => 'alert alert-error'));
                CakeLog::write('admin', '[error] Ground station \''.$station['Station']['name'].'\' could not be deleted.');
                $this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
            }
        } else {
            $this->Session->setFlash('That ground station could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_edit(){
        /*
        Displays the ground station edit page.
        */
        
        $this->set("title_for_layout","Edit a Ground Station");
        
        // Load the station in question
        $station = $this->Station->find('first', array('conditions' => array('Station.id' => $this->params->id)));
        if($station){
            $this->set('station', $station);
        } else {
            $this->Session->setFlash('That ground station could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_change(){
        /*
        Processes the form submission from admin_edit();
        
        Method: POST
        */
        
        // Load the station in question
        $station = $this->Station->find('first', array('conditions' => array('Station.id' => $this->params->id)));
        if($station){
			// Edit the station
			$station_changes['Station']['name'] = $_POST['station_name'];
			$station_changes['Station']['longitude'] = $_POST['station_longitude'];
			$station_changes['Station']['latitude'] = $_POST['station_latitude'];
			$station_changes['Station']['description'] = $_POST['station_description'];
			$station_changes['Station']['updated_on'] = date('Y-m-d H:i:s', time());
			$station_changes['Station']['id'] = $this->params->id;
			
			$edit_attempt = $this->Station->save($station_changes);
			if ($edit_attempt){
				$this->Session->setFlash('Ground station \''.$station['Station']['name'].'\' successfully edited', 'default', array('class' => 'alert alert-success'));
				CakeLog::write('admin', '[success] Ground station \''.$station['Station']['name'].'\' edited.');
				$this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
			} else {
				$this->Session->setFlash('There was an error editing the \''.$station['Station']['name'].'\' ground station.', 'default', array('class' => 'alert alert-error'));
				CakeLog::write('admin', '[error] Ground station \''.$station['Station']['name'].'\' could not be edited.');
				$this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
			}
        } else {
            $this->Session->setFlash('That ground station could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'station', 'action' => 'index', 'admin' => true));
        }
    }
}
?>
