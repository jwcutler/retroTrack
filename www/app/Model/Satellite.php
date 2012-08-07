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
    
    public function default_element_json(){
        /*
        Loads all default groups and satellites into JSON for use on the homepage.
        
        Returns:
            JSON string representing default satellites and groups.
        */
        
        // Setup
        $default_array['groups'] = Array();
        $default_array['satellites'] = Array();
        
        // Load all default satellites and groups
        $default_groups = $this->Group->find('all', array(
            'conditions' => array(
                'Group.default_on_home' => 1
            )
        ));
        foreach($default_groups as $default_group){
            // Add the group ID to the default array
            array_push($default_array['groups'], $default_group['Group']['id']);
            
            // Add each of the group's satellites to the default array
            foreach($default_group['Satellite'] as $default_group_satellite){
                array_push($default_array['satellites'], array(
                    'name' => $default_group_satellite['name'],
                    'id' => $default_group_satellite['id']
                ));
            }
        }
        
        // Load all default satellites
        $default_satellites = $this->find('all', array(
            'conditions' => array(
                'Satellite.default_on_home' => 1
            )
        ));
        foreach($default_satellites as $default_satellite){
            // Add the satellite to the default array
            array_push($default_array['satellites'], $default_satellite['Satellite']['name']);
        }
        
        return json_encode($default_array);
    }
    
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
                
                array_push($satellites, $satellite_temp);
            }
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
            // No satellite specified, find all satellites that are visible on the homepage
            $satellites = $this->find('all');
            
            // Loop through satellites and remove elements that aren't homepage visible
            foreach ($satellites as $satellite_key => $temp_satellite){
                $show_on_home = false;
                if ($temp_satellite['Satellite']['show_on_home']=='1'){
                    $show_on_home = true;
                } else {
                    // Check the groups
                    foreach ($temp_satellite['Group'] as $temp_group){
                        if ($temp_group['show_on_home']=='1'){
                            $show_on_home = true;
                        } else {
                            $show_on_home = false;
                        }
                    }
                    
                    // Remove the satellite if needed
                    if (!$show_on_home){
                        unset($satellites[$satellite_key]);
                    }
                }
            }
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
