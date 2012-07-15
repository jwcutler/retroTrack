<?php
/*
This controller is responsible for exporting the static version of retroTrack.
*/

class ExportController extends AppController {
    var $uses = array('Admin','Tle','Configuration', 'Station', 'Satellite', 'Group'); 
    
    public function admin_index() {
        /*
        Displays the main export page. Lists the satellites, groups, and ground stations for inclusion into the static version.
        */
        
        // Load all satellites
        $this->set('satellites', $this->Satellite->find('all'));
        
        // Load all groups
        $this->set('groups', $this->Group->find('all'));
        
        // Load all ground stations
        $this->set('stations', $this->Station->find('all'));
    }
    
    public function admin_generate() {
        /*
        Assembles the static version of retroTracker with the specified configurations.
        */
        
        
    }
}
?>
