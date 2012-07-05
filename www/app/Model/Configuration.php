<?php
/*
Configuration Model

Manages site-wide configuration options.
*/

class Configuration extends AppModel {
    var $name = 'Configuration';
    
    public function convertTimestamp($option_name){
        /*
        Loads the specified configuration option and converts the value from UNIX time.
        
        @params $option_name: Key to convert to UNIX time.
        @returns:
            String representation of the UNIX timestamp on success.
            'Invalid Timestamp' on failure.
        */
        
        // Attempt to load the option
        $option = $this->find('first', array(
            'conditions' => array('Configuration.name' => $option_name)
        ));
        
        if ($option){
            // Attempt to convert the timestamp
            return date("m/d/Y H:i:s", $option['Configuration']['value']);
        } else {
            return "Invalid Timestamp";
        }
    }
}
?>
