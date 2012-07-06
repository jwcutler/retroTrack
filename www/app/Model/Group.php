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
    
    public $validate = array(
        'name' => array(
            'name_unique' => array(
                'rule' => 'isUnique',
                'required' => true
            )
        )
    );
}
?>
