<?php
/*
TleUpdateShell

This shell updates the specified source(s) via console/cron access.
*/

class TleUpdateShell extends AppShell {
    // Setup models
    public $uses = array('Tle', 'Configuration');
    
    public function main() {
        /*
        Just display a quick readme. The main function can't receive parameters.
        */
        
$this->out('NAME:
    TleUpdate - retroTrack Console TLE update utility.

SYNOPSIS:
    app/Console/cake TleUpdate update

DESCRIPTION:
    TleUpdate is a console utility that allows users to update retroTracker\'s TLE source.

EXAMPLES:
    app/Console/cake TleUpdate update
        Updates the configured TLE source.
');
    }
    
    public function update(){
        /*
        Parses the provided arguments and connects to the Source model to update the specified TLE source(s).
        */
        
        // Setup
        $update_attempt = null;
        $this->stdout->styles('success', array('text' => 'green'));
        
        $this->out('Updating TLE source...');
        
        // Load the TLE source
        $tle_source = $this->Configuration->find('first', array(
            'conditions' => array('Configuration.name' => 'tle_source')
        ));
        $tle_source = $tle_source['Configuration']['value'];
        $update_attempt = $this->Tle->updateTles($tle_source);
        
        if ($update_attempt){
            $this->out('<success>All specified sources were successfully updated without error.</success>');
        } else {
            $this->out('<warning>At least one source failed to update. For more information, see the admin panel.</warning>');
        }
    }
}
?>
