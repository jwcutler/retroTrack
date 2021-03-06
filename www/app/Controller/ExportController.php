<?php
/*
This controller is responsible for exporting the static version of retroTrack.
*/

class ExportController extends AppController {
  var $uses = array('Admin','Tle','Configuration', 'Station', 'Satellite', 'Group'); 
  
  public function admin_index() {
    /*
    Displays the main export page. Lists the satellites, groups, and ground stations for inclusion into the static version.
    */
    
    // Load all satellites
    $this->set('satellites', $this->Satellite->find('all'));
    
    // Load all groups
    $this->set('groups', $this->Group->find('all'));
    $this->set('groups_json', $this->Group->group_json('all'));
    
    // Load all ground stations
    $this->set('stations', $this->Station->find('all'));
  }
  
  public function admin_generate() {
    /*
    Assembles the static version of retroTracker with the specified configurations.
    */
    
    // Create the required group, satellite, and configuration JSON
    $group_names = array();
    foreach ($_POST['groups'] as $temp_group){
      array_push($group_names, $temp_group);
    }
    $group_json = str_replace("'", "\'", $this->Group->group_json($group_names));
    
    $satellite_names = array();
    if (isset($_POST['satellites'])){
      foreach ($_POST['satellites'] as $temp_satellite){
        array_push($satellite_names, $temp_satellite);
      }
    $satellite_json = str_replace("'", "\'", $this->Satellite->satellite_json($satellite_names, false, false));
    } else {
      $this->Session->setFlash('Please select at least one satellite.', 'default', array('class' => 'alert alert-error'));
      $this->redirect(array('controller' => 'export', 'action' => 'index'));
    }
    
    $default_elements = str_replace("'", "\'", $this->Satellite->default_element_json());

    $station_names = array();
    foreach ($_POST['stations'] as $temp_station){
      array_push($station_names, $temp_station);
    }
    $station_json = str_replace("'", "\'", $this->Station->station_json($station_names));
    
    $configuration_json = str_replace("'", "\'", $this->Configuration->configuration_json());
    
    // URL encode the satellite names for use
    $encoded_satellites = Array();
    foreach ($satellite_names as $satellite_name){
      array_push($encoded_satellites, rawurlencode($satellite_name));
    }
    $encoded_satellites = join("_", $encoded_satellites);
    
    // Load the configuration into the template
    $static_template_contents = file_get_contents(APP."Vendor/static_template/index.html");
    $static_template_contents = str_replace('{satellite_json}', $satellite_json, $static_template_contents);
    $static_template_contents = str_replace('{group_json}', $group_json, $static_template_contents);
    $static_template_contents = str_replace('{station_json}', $station_json, $static_template_contents);
    $static_template_contents = str_replace('{default_elements_json}', $default_elements, $static_template_contents);
    $static_template_contents = str_replace('{encoded_satellites}', $encoded_satellites, $static_template_contents);
    $static_template_contents = str_replace('{configuration_json}', $configuration_json, $static_template_contents);
    $static_template_contents = str_replace('{tle_base_path}', $_POST['tle_base_path'], $static_template_contents);
    $static_template_contents = str_replace('{base_url}', Router::url('/', true), $static_template_contents);
    $temp_file_name = "tmp/static_zips/temp_index_".time().".html";
    file_put_contents(APP.$temp_file_name, $static_template_contents);
    
    // Package the static version into a zip
    $zip_file = new ZipArchive();
    $zip_file_name = "static_retroTrack_".time().".zip";
    $zip_file_path = "webroot/img/static_versions/".$zip_file_name;
    if ($zip_file->open(APP.$zip_file_path, ZipArchive::CREATE)){
      // Add the required files & directories to the zip
      $zip_file->addEmptyDir("css");
      $zip_file->addEmptyDir("img");
      $zip_file->addEmptyDir("js");
      $zip_file->addFile(APP."webroot/css/jquery-ui-1.9.1.custom.min.css", "css/jquery-ui-1.9.1.custom.min.css");
      $zip_file->addFile(APP."webroot/css/chosen.css", "css/chosen.css");
      $zip_file->addFile(APP."webroot/css/retrotrack_display.css", "css/retrotrack_display.css");
      $zip_file->addFile(APP."webroot/img/browser_chrome.gif", "img/browser_chrome.gif");
      $zip_file->addFile(APP."webroot/img/browser_firefox.gif", "img/browser_firefox.gif");
      $zip_file->addFile(APP."webroot/img/map_bg.jpg", "img/map_bg.jpg");
      $zip_file->addFile(APP."webroot/img/map_bg_simple.png", "img/map_bg_simple.png");
      $zip_file->addFile(APP."webroot/img/mxl_logo.png", "img/mxl_logo.png");
      $zip_file->addFile(APP."webroot/js/jquery-1.7.2.min.js", "js/jquery-1.7.2.min.js");
      $zip_file->addFile(APP."webroot/js/jquery-ui-1.9.1.custom.min.js", "js/jquery-ui-1.9.1.custom.min.js");
      $zip_file->addFile(APP."webroot/js/chosen.jquery.min.js", "js/chosen.jquery.min.js");
      $zip_file->addFile(APP."webroot/js/modernizr.custom.js", "js/modernizr.custom.js");
      $zip_file->addFile(APP."View/Elements/retrotrack_javascript.ctp", "js/retrotrack_javascript.js");
      $zip_file->addFile(APP.$temp_file_name, "index.html");
      
      // Close the file
      $zip_file->close();
    } else {
      $this->Session->setFlash('There was an error creating the static version. Please try again.', 'default', array('class' => 'alert alert-error'));
      CakeLog::write('admin', '[error] An error occured while trying to create a static version zip file.');
      $this->redirect(array('controller' => 'panel', 'action' => 'index'));
    }
    
    // Delete the edited template
    unlink(APP.$temp_file_name);
    
    // Redirect the user to the download page
    $this->Session->setFlash('Your static version of retroTrack was created. Download it <a target="_blank" href="'.$this->webroot.'img/static_versions/'.$zip_file_name.'" class="link">here</a>.', 'default', array('class' => 'alert alert-success'));
    CakeLog::write('admin', '[success] A static version of the site was successfully created.');
    $this->redirect(array('controller' => 'panel', 'action' => 'index'));
  }
}
?>
