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
    
    // Setup Caching
    /*public $helpers = array('Cache');
    public $cacheAction = array(
        'embed_script' => 1800 // Cache the embed script for 30 minutes
    );*/
    
    public function index() {
        /*
        Displays the primary tracking map containing all satellites & groups.
        */
        
        // Set the title
        $this->set('title_for_layout', 'Viewing All Satellites');
        
        // Load the list of satellites
        $this->set('satellite_json', str_replace("'", "\'", $this->Satellite->satellite_json()));
        
        // Load the list of groups
        $this->set('group_json', str_replace("'", "\'", $this->Group->group_json()));
        
        // Load the TLEs
        $this->set('tle_json', str_replace("'", "\'", $this->Tle->tle_json()));
        
        // Load the ground stations
        $this->set('station_json', str_replace("'", "\'", $this->Station->station_json()));
        
        // Load the configuration
        $this->set('configuration_json', str_replace("'", "\'", $this->Configuration->configuration_json()));
        
        // Load the active satellites
        $this->set('default_elements', str_replace("'", "\'", $this->Satellite->default_element_json()));
        
        // Render the main display view
        $this->render('display');
        $this->set('page_title', '');
    }
    
    public function satellite_display($satellite_name){
        /*
        Displays the tracker for the specified satellite.
        
        @param $satellite_name: Name of the satellite to display from route handler.
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
                $this->set('satellite_json', str_replace("'", "\'", $this->Satellite->satellite_json($satellite_name)));
                
                // Load the list of groups
                $group_names = array();
                foreach ($satellite['Group'] as $temp_group){
                    array_push($group_names, $temp_group['name']);
                }
                $this->set('group_json', str_replace("'", "\'", $this->Group->group_json($group_names)));
                
                // Load the TLEs
                $this->set('tle_json', str_replace("'", "\'", $this->Tle->tle_json()));
        
                // Load the ground stations
                $this->set('station_json', str_replace("'", "\'", $this->Station->station_json()));
                
                // Load the configuration
                $this->set('configuration_json', str_replace("'", "\'", $this->Configuration->configuration_json()));
                
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
        
        @param $group_name: Name of the group to display from route handler. 
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
                $this->set('satellite_json', str_replace("'", "\'", $this->Satellite->satellite_json(false, $group_name)));
                
                // Load the list of groups
                $this->set('group_json', str_replace("'", "\'", $this->Group->group_json($group_name)));
                
                // Load the TLEs
                $this->set('tle_json', str_replace("'", "\'", $this->Tle->tle_json()));
        
                // Load the ground stations
                $this->set('station_json', str_replace("'", "\'", $this->Station->station_json()));
                
                // Load the configuration
                $this->set('configuration_json', str_replace("'", "\'", $this->Configuration->configuration_json()));
                
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
    
    public function embed_script(){
      /*
      Generates the javascript required to generate and run a retroTrack instance.
      
      @note
      Takes 'satellites' and 'groups' as underscore (_) deliminted $_GET parameters. Both are optional.
      If both are set, every satellite in each of the groups and every individual satellite specified will be included.
      */
      
      // Parse the parameters
      $group_list = (isset($_GET['groups']))?explode("_", $_GET['groups']):false;
      $satellite_list = (isset($_GET['satellites']))?explode("_", $_GET['satellites']):false;
      
      // Load the required json
      $this->set('satellite_json', str_replace("'", "\'", $this->Satellite->satellite_json($satellite_list, $group_list)));
      $this->set('group_json', str_replace("'", "\'", $this->Group->group_json($group_list)));
      $this->set('tle_json', str_replace("'", "\'", $this->Tle->tle_json()));
      $this->set('station_json', str_replace("'", "\'", $this->Station->station_json()));
      $this->set('configuration_json', str_replace("'", "\'", $this->Configuration->configuration_json()));
      
      // Render the javascript template view
      $this->layout = 'ajax';
    }
}
?>
