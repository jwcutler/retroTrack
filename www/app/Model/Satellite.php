<?php
/*
Satellite Model
*/

// Load other required models
App::import('model','Group');

class Satellite extends AppModel {
    var $name = 'Satellite';
    
    // Define associations
    public $hasAndBelongsToMany = array(
        'Group' =>
            array(
                'className'              => 'Group',
                'joinTable'              => 'groups_satellites',
                'foreignKey'             => 'satellite_id',
                'associationForeignKey'  => 'group_id',
                'unique'                 => true
            )
    );
    
    public function satellite_json($satellite_name = false, $group_name = false){
        /*
        Loads the specified satellite (or all of them if no argument passed) and formats it into JSON.
        
        @param $satellite_name: Name of the satellite to load.
        @param $group_name: Name of the group to load satellites for.
        
        Returns:
            JSON string representing the satellite(s).
        */
        
        // Load the specified satellites
        $satellites = NULL;
        if ($satellite_name){
            // Satellite specified, load it
            $satellite_temp = $this->find('first', array(
                'conditions' => array('Satellite.name' => urldecode($satellite_name))
            ));
            $satellites = array($satellite_temp);
        } else if ($group_name){
            // Group specified, load its satellites
            $satellites = Classregistry::init('Group')->Satellite->find('all');
        } else {
            // No satellite specified, load all of them
            $satellites = $this->find('all');
        }
        
        // Create a JSON object for the satellites
        $satellite_array = array();
        foreach ($satellites as $satellite){
            $temp_satellite = array(
                'id' => $satellite['Satellite']['id'],
                'name' => $satellite['Satellite']['name'],
                'description' => $satellite['Satellite']['description'],
                'groups' => array()
            );
            
            // Add all of the satellite's groups
            foreach($satellite['Group'] as $group){
                $temp_group = array(
                    'id' => $group['id'],
                    'name' => $group['name']
                );
                
                array_push($temp_satellite['groups'], $temp_group);
            }
            
            $satellite_array[$satellite['Satellite']['id']] = $temp_satellite;
        }
        
        // Return the JSON representation
        return json_encode($satellite_array);
    }
}
?>
