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
    
    public function station_json(){
        /*
        Generates a JSON representation of all of the ground stations.
        
        Returns:
            A JSON string representing each ground station.
        */
        
        // Load all ground stations
        $stations = $this->find('all');
        
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
