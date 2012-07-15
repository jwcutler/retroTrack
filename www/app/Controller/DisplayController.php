<?php
/*
This controller is reponsible for displaying all retro track maps.

Various actions are called depending on what satellites and groups need to be displayed.
*/

class DisplayController extends AppController {
    var $uses = array('Station', 'Satellite', 'Group', 'Tle', 'Configuration'); 
    
    public function beforeFilter(){
        parent::beforeFilter();
        
        // Allow unauthenticated access to any methods of this controller
        $this->Auth->allow('*');
    }
    
    public function index() {
        /*
        Displays the primary tracking map containing all satellites & groups.
        */
        
        // Set the title
        $this->set('title_for_layout', 'Viewing All Satellites');
        
        // Load the list of satellites
        $this->set('satellite_json', $this->Satellite->satellite_json());
        
        // Load the list of groups
        $this->set('group_json', $this->Group->group_json());
        
        // Load the TLEs
        $this->set('tle_json', $this->Tle->tle_json());
        
        // Load the ground stations
        $this->set('station_json', $this->Station->station_json());
        
        // Load the configuration
        $this->set('configuration_json', $this->Configuration->configuration_json());
        
        $this->set('page_title', '');
        
        // Render the main display view
        $this->render('display');
    }
    
    public function satellite_display($satellite_name){
        /*
        Displays the tracker for the specified satellite.
        
        @param $satellite_name: Name of the satellite to display
        */
        
        if (!empty($satellite_name)){
            // Make sure the satellite exists
            $satellite = $this->Satellite->find('first', array(
                'conditions' => array('Satellite.name' => urldecode($satellite_name))
            ));
            
            if ($satellite){
                // Set the title
                $this->set('title_for_layout', 'Viewing Satellite \''.$satellite['Satellite']['name'].'\'');
        
                // Load the list of satellites
                $this->set('satellite_json', $this->Satellite->satellite_json($satellite_name));
                
                // Load the list of groups
                $this->set('group_json', $this->Group->group_json());
                
                // Load the TLEs
                $this->set('tle_json', $this->Tle->tle_json());
        
                // Load the ground stations
                $this->set('station_json', $this->Station->station_json());
                
                // Load the configuration
                $this->set('configuration_json', $this->Configuration->configuration_json());
                
                $this->set('page_title', 'Now viewing the \''.$satellite['Satellite']['name'].'\' satellite');
            } else {
                // Invalid satellite
                $this->Session->setFlash('Error: That satellite does not exist.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'display', 'action' => 'index'));
            }
        } else {
            // Missing satellite
            $this->Session->setFlash('Error: No satellite specified.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'display', 'action' => 'index'));
        }
        
        // Render the main display view
        $this->render('display');
    }
    
    public function group_display($group_name){
        /*
        Displays the tracker for the specified group
        
        @param $group_name: Name of the group to display
        */
        
        if (!empty($group_name)){
            // Make sure the satellite exists
            $group = $this->Group->find('first', array(
                'conditions' => array('Group.name' => urldecode($group_name))
            ));
            
            if ($group){
                // Set the title
                $this->set('title_for_layout', 'Viewing Group \''.$group['Group']['name'].'\'');
        
                // Load the list of satellites
                $this->set('satellite_json', $this->Satellite->satellite_json(false, $group_name));
                
                // Load the list of groups
                $this->set('group_json', $this->Group->group_json($group_name));
                
                // Load the TLEs
                $this->set('tle_json', $this->Tle->tle_json());
        
                // Load the ground stations
                $this->set('station_json', $this->Station->station_json());
                
                // Load the configuration
                $this->set('configuration_json', $this->Configuration->configuration_json());
                
                $this->set('page_title', 'Now viewing the \''.$group['Group']['name'].'\' satellite group');
            } else {
                // Invalid satellite
                $this->Session->setFlash('Error: That group does not exist.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'display', 'action' => 'index'));
            }
        } else {
            // Missing satellite
            $this->Session->setFlash('Error: No group specified.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'display', 'action' => 'index'));
        }
        
        // Render the main display view
        $this->render('display');
    }
}
?>
