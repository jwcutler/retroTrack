<?php
/*
Station Model
*/

class Station extends AppModel {
    var $name = 'Station';
    
    // Validation rules
    public $validate = array(
        'name' => array(
            'name_unique' => array(
                'rule' => 'isUnique',
                'required' => true
            )
        )
    );
    
    public function station_json($station_names = false){
        /*
        Generates a JSON representation of all of the ground stations.
        
        @param $station_names: An array of stations to load the JSON for.
        
        Returns:
            A JSON string representing each ground station.
        */
        
        // Setup
        $stations = null;
        
        if (is_array($station_names)){
            // Load all of the specified stations
            $stations = array();
            foreach ($station_names as $station_name){
                $station_temp = $this->find('first', array(
                    'conditions' => array('Station.name' => urldecode($station_name))
                ));
                
                array_push($stations, $station_temp);
            }
        } else {
            // Load all ground stations
            $stations = $this->find('all');
        }
        
        // Assemble an array containing the ground stations
        $station_array = array();
        foreach ($stations as $station){
            $temp_station = array(
                'id' => $station['Station']['id'],
                'longitude' => $station['Station']['longitude'],
                'latitude' => $station['Station']['latitude'],
                'name' => $station['Station']['name'],
                'description' => $station['Station']['description']
            );
            
            $station_array[$station['Station']['name']] = $temp_station;
        }
        
        // Return the JSON string
        return json_encode($station_array);
    }
}
?>
