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
    
    public function satellite_json($satellite_names = false, $group_name = false, $use_decode = true){
        /*
        Loads the specified satellite (or all of them if no argument passed) and formats it into JSON.
        
        @param $satellite_names: Name of the satellite to load.
        @param $group_name: Name of the group to load satellites for.
		@param $use_decode: Whether or not URL decode is needed.
        
        Returns:
            JSON string representing the satellite(s).
        */
        
        // Load the specified satellites
        $satellites = NULL;
        if (is_array($satellite_names)){
            // Load each of the specified satellites
            $satellites = array();
            foreach ($satellite_names as $satellite_name){
				//echo $satellite_name."-".urldecode($satellite_name)."-";
				$satellite_name = ($use_decode)?urldecode($satellite_name):$satellite_name;
                $satellite_temp = $this->find('first', array(
                    'conditions' => array('Satellite.name' => $satellite_name)
                ));
				//echo $satellite_temp."<br />";
                
                array_push($satellites, $satellite_temp);
            }
			//exit;
        } else if ($satellite_names){
            // Satellite specified, load it
			$satellite_name = ($use_decode)?urldecode($satellite_names):$satellite_names;
            $satellite_temp = $this->find('first', array(
                'conditions' => array('Satellite.name' => $satellite_name)
            ));
            $satellites = array($satellite_temp);
        } else if ($group_name){
            // Group specified, load its satellites
            $satellites = array();
            $group_temp = $this->Group->find('first', array(
                'conditions' => array('Group.name' => urldecode($group_name))
            ));
            foreach ($group_temp['Satellite'] as $temp_satellite){
                // Find the satellite
				$satellite_name = ($use_decode)?urldecode($temp_satellite['name']):$temp_satellite['name'];
                $satellite_temp = $this->find('first', array(
                    'conditions' => array('Satellite.name' => $satellite_name)
                ));
                array_push($satellites, $satellite_temp);
            }
        } else {
            // No satellite specified, load all of them
            $satellites = $this->find('all');
        }
        
        // Create a JSON object for the satellites
        $satellite_array = array();
        foreach ($satellites as $satellite){
            //var_dump($satellite);
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
