<?php
/*
This controller is reponsible for displaying all retro track maps.

Various actions are called depending on what satellites and groups need to be displayed.
*/

class DisplayController extends AppController {
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
        
        // Render the main display view
        $this->render('display');
    }
}
?>
