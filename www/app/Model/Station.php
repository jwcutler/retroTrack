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
}
?>
