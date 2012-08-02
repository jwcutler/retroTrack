<?php
/*
Group Model
*/

class Group extends AppModel {
    var $name = 'Group';
    
    // Define associations
    public $hasAndBelongsToMany = array(
        'Satellite' =>
            array(
                'className'              => 'Satellite',
                'joinTable'              => 'groups_satellites',
                'foreignKey'             => 'group_id',
                'associationForeignKey'  => 'satellite_id',
                'unique'                 => true
            )
    );
    
    // Validations
    public $validate = array(
        'name' => array(
            'name_unique' => array(
                'rule' => 'isUnique',
                'required' => true
            )
        )
    );
    
    public function group_json($group_names = false){
        /*
        Loads the specified group (or all of them if no name passed) and formats it into JSON.
        
        @param $group_names: Name(s) of the group to load.
        
        Returns:
            A JSON string representing the specified group(s).
        */
        
        // Load the specified groups
        $groups = NULL;
        if (is_array($group_names)){
            // Load each of the specified groups
            $groups = array();
            foreach ($group_names as $group_name){
                $group_temp = $this->find('first', array(
                    'conditions' => array('Group.name' => urldecode($group_name))
                ));
                
                array_push($groups, $group_temp);
            }
        } else if ($group_names){
            // Load the specified group
            $group_temp = $this->find('first', array(
                'conditions' => array('Group.name' => urldecode($group_names))
            ));
            $groups = array($group_temp);
        } else {
            // Load all groups (that are homepage visible)
            $groups = $this->find('all', array(
                'conditions' => array('Group.show_on_home' => '1')
            ));
        }
        
        // Package the groups into an array
        $group_array = array();
        foreach ($groups as $group){
            $temp_group = array(
                'id' => $group['Group']['id'],
                'name' => $group['Group']['name'],
                'description' => $group['Group']['description'],
                'satellites' => array()
            );
            
            // Add all of the group's satellites
            foreach($group['Satellite'] as $satellite){
                $temp_satellite = array(
                    'id' => $satellite['id'],
                    'name' => $satellite['name']
                );
                
                array_push($temp_group['satellites'], $temp_satellite);
            }
            
            $group_array[$group['Group']['id']] = $temp_group;
        }
        
        // Return the JSON representation
        return json_encode($group_array);
    }
}
?>
