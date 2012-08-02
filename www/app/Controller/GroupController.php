<?php
/*
This controller is responsible for retroTrack administrator group management functionality.
*/

class GroupController extends AppController {
    var $uses = array('Satellite', 'Configuration', 'Group'); 
    
    public function admin_index(){
        /*
        Displays a list of all of the currently configured groups.
        */
        
        $this->set("title_for_layout","Manage Satellite Groups");
        
        // Load all configured groups
        $this->set('groups', $this->Group->find('all'));
    }
    
    public function admin_add(){
        /*
        Displays the form to create a new group.
        */
        
        $this->set("title_for_layout","Create a Satellite Group");
        
        // Load all existing satellites
        $this->set('satellites', $this->Satellite->find('all'));
    }
    
    public function admin_create(){
        /*
        Process the form submission from 'admin_add' and create the group.
        
        Method: POST
        */
        
        if ($this->request->is('post')) {
            // Make sure a group with this name doesn't exist
            $name_check = $this->Group->find('first', array(
                'conditions' => array('Group.name' => $_POST['group_name'])
            ));
            
            if ($name_check){
                // Group all ready exists
                $this->Session->setFlash('A group with that name all ready exists. Group names must be unique.', 'default', array('class' => 'alert alert-error'));
                $this->redirect(array('controller' => 'group', 'action' => 'add'));
            } else {
                // Group doesn't exist, try to create it
                $satellite_ids = array();
                
                // Loop through and add all of the satellite IDs
                foreach($_POST['satellites'] as $satellite_id){
                    array_push($satellite_ids, $satellite_id);
                }
                
                // Assemble query
                $show_on_home = (isset($_POST['show_on_home']))?'1':'0';
                $new_group['Group'] = array(
                    'name' => $_POST['group_name'],
                    'description' => $_POST['group_description'],
                    'show_on_home' => $show_on_home,
                    'created_on' => date('Y-m-d H:i:s', time()),
                    'updated_on' =>date ('Y-m-d H:i:s', time())
                );
                $new_group['Satellite'] = array('Satellite' => $satellite_ids);
                
                $save_group = $this->Group->save($new_group);
                
                if ($save_group){
                    $this->Session->setFlash('The group has been added successfully.', 'default', array('class' => 'alert alert-success'));
                    CakeLog::write('admin', '[success] New group \''.$_POST['group_name'].'\' added.');
                } else {
                    $this->Session->setFlash('An error occured while adding that group. Please try again.', 'default', array('class' => 'alert alert-error'));
                    CakeLog::write('admin', '[error] Error adding group \''.$_POST['group_name'].'\' added.');
                }
                
                $this->redirect(array('controller' => 'group', 'action' => 'index'));
            }
        } else {
            // Redirect them to the add form
            $this->redirect(array('controller' => 'group', 'action' => 'add'));
        }
    }
    
    public function admin_remove(){
        /*
        Displays the group delete confirmation page.
        */
        
        $this->set("title_for_layout","Remove a Group");
        
        // Load the group in question
        $group = $this->Group->find('first', array('conditions' => array('Group.id' => $this->params->id)));
        if($group){
            $this->set('group', $group);
        } else {
            $this->Session->setFlash('That group could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'group', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_delete(){
        /*
        Actually delete the specified group.
        
        Method: POST
        */
        
        // Load the group in question
        $group = $this->Group->find('first', array('conditions' => array('Group.id' => $this->params->id)));
        if($group){
            // Delete the group
            $delete_attempt = $this->Group->delete($this->params->id);
            if ($delete_attempt){
                $this->Session->setFlash('Group \''.$group['Group']['name'].'\' successfully deleted.', 'default', array('class' => 'alert alert-success'));
                CakeLog::write('admin', '[success] Group \''.$group['Group']['name'].'\' deleted.');
                $this->redirect(array('controller' => 'group', 'action' => 'index', 'admin' => true));
            } else {
                $this->Session->setFlash('There was an error deleting the \''.$group['Group']['name'].'\' group.', 'default', array('class' => 'alert alert-error'));
                CakeLog::write('admin', '[error] Group \''.$group['Group']['name'].'\' could not be deleted.');
                $this->redirect(array('controller' => 'group', 'action' => 'index', 'admin' => true));
            }
        } else {
            $this->Session->setFlash('That group could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'group', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_edit(){
        /*
        Displays the group edit page.
        */
        
        $this->set("title_for_layout","Edit a Group");
        
        // Load the group in question
        $group = $this->Group->find('first', array('conditions' => array('Group.id' => $this->params->id)));
        if($group){
            // Load all of the satellites
            $satellites = $this->Satellite->find('all');
            $group_satellites = array();
            
            // Assemble an array of the ID's of the satellites in the group
            foreach($group['Satellite'] as $group_satellite){
                array_push($group_satellites, $group_satellite['id']);
            }
            
            $this->set('group', $group);
            $this->set('satellites', $satellites);
            $this->set('group_satellites', $group_satellites);
        } else {
            $this->Session->setFlash('That group could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'group', 'action' => 'index', 'admin' => true));
        }
    }
    
    public function admin_change(){
        /*
        Process the form submission from 'admin_edit' and edit the group.
        
        Method: POST
        */
        
        // Load the group in question
        $group = $this->Group->find('first', array('conditions' => array('Group.id' => $this->params->id)));
        if($group){
			// Edit the group
			$satellite_ids = array();
			
			// Loop through and add all of the satellite IDs
			foreach($_POST['satellites'] as $satellite_id){
				array_push($satellite_ids, $satellite_id);
			}
			
			// Assemble query
            $show_on_home = (isset($_POST['show_on_home']))?'1':'0';
			$group_changes['Group'] = array(
                'id' => $group['Group']['id'],
                'name' => $_POST['group_name'],
                'description' => $_POST['group_description'],
                'show_on_home' => $show_on_home,
                'updated_on' =>date ('Y-m-d H:i:s', time())
            );
			$group_changes['Satellite'] = array('Satellite' => $satellite_ids);
			
			$edit_group = $this->Group->save($group_changes);
			
			if ($edit_group){
				$this->Session->setFlash('The group has been edited successfully.', 'default', array('class' => 'alert alert-success'));
				CakeLog::write('admin', '[success] The group \''.$_POST['group_name'].'\' has been edited.');
			} else {
				$this->Session->setFlash('An error occured while editing that group. Please try again.', 'default', array('class' => 'alert alert-error'));
				CakeLog::write('admin', '[error] Error editing the group \''.$_POST['group_name'].'\'.');
			}
			
			$this->redirect(array('controller' => 'group', 'action' => 'index'));
        } else {
            $this->Session->setFlash('That group could not be found.', 'default', array('class' => 'alert alert-error'));
            $this->redirect(array('controller' => 'group', 'action' => 'index', 'admin' => true));
        }
    }
}
?>
