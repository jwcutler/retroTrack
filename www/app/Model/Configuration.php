<?php
/*
Configuration Model

Manages site-wide configuration options.
*/

class Configuration extends AppModel {
    var $name = 'Configuration';
    
    public function configuration_json(){
        /*
        Generates a JSON representation of the tracker configuration options.
        
        Returns:
            A JSON string of configuration options.
        */
        
        // Load the configuration settings
        $configurations = $this->find('all');
        
        // Assemble the configuration settings into an array
        $configuration_settings = array();
        foreach($configurations as $configuration){
            $temp_configuration = array(
                'name' => $configuration['Configuration']['name'],
                'value' => $configuration['Configuration']['value']
            );
            
            $configuration_settings[$configuration['Configuration']['name']] = $temp_configuration;
        }
        
        // Output the JSON
        return json_encode($configuration_settings);
    }
    
    public function convertTimestamp($option_name){
        /*
        Loads the specified configuration option and converts the value from UNIX time.
        
        @params $option_name: Key to convert to UNIX time.
        
        Returns:
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
