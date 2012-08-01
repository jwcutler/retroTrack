<?php
/*
TLE Model

Allows administrators to manage TLE entries.
*/

// Load other required models
App::import('model','Configuration');

class Tle extends AppModel {
    var $name = 'Tle';
    
    public function tle_json(){
        /*
        Generates a JSON representation of all TLE's.
        
        Returns:
            A JSON string representing the TLE's.
        */
        
        // Load the tle's
        $tles = $this->find('all');
        
        // Assemble an array of TLE information
        $tles_array = array();
        foreach ($tles as $tle){
            $temp_tle = array(
                'id' => $tle['Tle']['id'],
                'name' => $tle['Tle']['name'],
                'satellite_number' => $tle['Tle']['satellite_number'],
                'classification' => $tle['Tle']['classification'],
                'launch_year' => $tle['Tle']['launch_year'],
                'launch_number' => $tle['Tle']['launch_number'],
                'launch_piece' => $tle['Tle']['launch_piece'],
                'epoch_year' => $tle['Tle']['epoch_year'],
                'epoch' => $tle['Tle']['epoch'],
                'ftd_mm_d2' => $tle['Tle']['ftd_mm_d2'],
                'std_mm_d6' => $tle['Tle']['std_mm_d6'],
                'bstar_drag' => $tle['Tle']['bstar_drag'],
                'element_number' => $tle['Tle']['element_number'],
                'checksum_l1' => $tle['Tle']['checksum_l1'],
                'inclination' => $tle['Tle']['inclination'],
                'right_ascension' => $tle['Tle']['right_ascension'],
                'eccentricity' => $tle['Tle']['eccentricity'],
                'perigee' => $tle['Tle']['perigee'],
                'mean_anomaly' => $tle['Tle']['mean_anomaly'],
                'mean_motion' => $tle['Tle']['mean_motion'],
                'revs' => $tle['Tle']['revs'],
                'checksum_l2' => $tle['Tle']['checksum_l2'],
                'raw_l1' => $tle['Tle']['raw_l1'],
                'raw_l2' => $tle['Tle']['raw_l2']
            );
            
            $tles_array[$tle['Tle']['name']] = $temp_tle;
        }
        
        //unset($tles_array['DTUSAT']);
        
        // Return the JSON
        return json_encode($tles_array);
    }
    
    public function updateTles($tle_source){
        /*
        Loads & parses the specified TLE source and saves the entries into the database.
        
        @param $tle_source: TLE source file.
        
        Returns:
            TRUE on success.
            FALSE on failure.
        */
        
        // Load the file
        $tle_file = file($tle_source);
        
        if ($tle_file){
            $tle_line_counter = 0;
            $tle_entry_counter = 0;
            $new_tle_entries = array();
            $valid_tle = true;
            foreach ($tle_file as $tle_line){
                // Check if the entry needs to be saved
                if ($tle_line_counter==3){
                    $tle_line_counter = 0;
                    $tle_entry_counter++;
                }
                
                // Add a new TLE entry array if needed
                if ($tle_line_counter == 0){
                    array_push($new_tle_entries, array());
                }
                
                // Parse the first line
                if ($tle_line_counter == 0){
                    $new_tle_entries[$tle_entry_counter]['name'] = trim($tle_line);
                }
                
                // Parse the second line
                if ($tle_line_counter == 1){
                    // Make sure the TLE line is the right length
                    if (strlen(trim($tle_line))!=69){
                        $valid_tle = false;
                        break;
                    }
                    
                    $new_tle_entries[$tle_entry_counter]['satellite_number'] = trim(substr($tle_line, 2, 5));
                    $new_tle_entries[$tle_entry_counter]['classification'] = trim(substr($tle_line, 7, 1));
                    $new_tle_entries[$tle_entry_counter]['launch_year'] = trim(substr($tle_line, 9, 2));
                    $new_tle_entries[$tle_entry_counter]['launch_number'] = trim(substr($tle_line, 11, 3));
                    $new_tle_entries[$tle_entry_counter]['launch_piece'] = trim(substr($tle_line, 14, 1));
                    $new_tle_entries[$tle_entry_counter]['epoch_year'] = trim(substr($tle_line, 18, 2));
                    $new_tle_entries[$tle_entry_counter]['epoch'] = trim(substr($tle_line, 20, 12));
                    $new_tle_entries[$tle_entry_counter]['ftd_mm_d2'] = trim(substr($tle_line, 33, 12));
                    $new_tle_entries[$tle_entry_counter]['std_mm_d6'] = trim(substr($tle_line, 45, 7));
                    $new_tle_entries[$tle_entry_counter]['bstar_drag'] = trim(substr($tle_line, 53, 8));
                    $new_tle_entries[$tle_entry_counter]['element_number'] = trim(substr($tle_line, 65, 3));
                    $new_tle_entries[$tle_entry_counter]['checksum_l1'] = trim(substr($tle_line, 68, 1));
                    $new_tle_entries[$tle_entry_counter]['raw_l1'] = trim($tle_line);
                }
                
                // Parse the third line
                if ($tle_line_counter == 2){
                    // Make sure the TLE line is the right length
                    if (strlen(trim($tle_line))!=69){
                        $valid_tle = false;
                        break;
                    }
                    
                    $new_tle_entries[$tle_entry_counter]['inclination'] = trim(substr($tle_line, 8, 8));
                    $new_tle_entries[$tle_entry_counter]['right_ascension'] = trim(substr($tle_line, 17, 8));
                    $new_tle_entries[$tle_entry_counter]['eccentricity'] = trim(substr($tle_line, 26, 7));
                    $new_tle_entries[$tle_entry_counter]['perigee'] = trim(substr($tle_line, 34, 8));
                    $new_tle_entries[$tle_entry_counter]['mean_anomaly'] = trim(substr($tle_line, 43, 8));
                    $new_tle_entries[$tle_entry_counter]['mean_motion'] = trim(substr($tle_line, 52, 11));
                    $new_tle_entries[$tle_entry_counter]['revs'] = trim(substr($tle_line, 63, 5));
                    $new_tle_entries[$tle_entry_counter]['checksum_l2'] = trim(substr($tle_line, 68, 1));
                    $new_tle_entries[$tle_entry_counter]['raw_l2'] = trim($tle_line);
                    $new_tle_entries[$tle_entry_counter]['created_on'] = date('Y-m-d H:i:s', time());
                }
                
                $tle_line_counter++;
            }
            
            if ($valid_tle){
                // Clear the table
                $this->query('TRUNCATE tles;');
                
                // Save the new TLE information
                $save_result = $this->saveMany($new_tle_entries, array('deep' => true));
                
                if ($save_result){
                    // Update the tle_last_updateconfiguration option
                    $option = Classregistry::init('Configuration')->find('first', array(
                        'conditions' => array('Configuration.name' => 'tle_last_update')
                    ));
                    Classregistry::init('Configuration')->id = $option['Configuration']['id'];
                    Classregistry::init('Configuration')->saveField('value', time());
                
                    return true;
                } else {
                    // Error saving TLE entries
                    return false;
                }
            } else {
                // Invalid TLE line
                return false;
            }
        } else {
            // Error loading file
            return false;
        }
    }
}
?>
