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
    
    public function group_json($group_name = false){
        /*
        Loads the specified group (or all of them if no name passed) and formats it into JSON.
        
        @param $group_name: Name of the group to load.
        
        Returns:
            A JSON string representing the specified group(s).
        */
        
        // Load the specified groups
        $groups = NULL;
        if ($group_name){
            // Load the specified group
            $group_temp = $this->find('first', array(
                'conditions' => array('Group.name' => urldecode($group_name))
            ));
            $groups = array($group_temp);
        } else {
            // Load all groups
            $groups = $this->find('all');
        }
        
        // Package the groups into an array
        $group_array = array();
        foreach ($groups as $group){
            $temp_group = array(
                'id' => $group['Group']['id'],
                'name' => $group['Group']['name'],
                'description' => $group['Group']['description']
            );
            
            array_push($group_array, $temp_group);
        }
        
        // Return the JSON representation
        return json_encode($group_array);
    }
}
?>
