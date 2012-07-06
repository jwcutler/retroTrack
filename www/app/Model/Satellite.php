<?php
/*
Satellite Model
*/

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
}
?>
