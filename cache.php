<?php
/**
 * Custom cache class for Wordpress theme
 * https://github.com/FusedInteractive/Wordpress-Cache
 * Created by Fused Interactive
 * http://fusedinteractive.com
 *
 * View the read me file before using.
 *
 * NOTICE: You will need to change the database functions to use this beyond Wordpress.
 */
 
class Simple_WP_Cache {
	private $dir_name = 'files';
	private $storage_dir;
	private $date_format = 'D M j G:i:s Y';
	
	public function __construct() {
		$this->storage_dir = dirname(__FILE__).'/'.$this->dir_name;
	}
	
	public function exists($name) {
		if(file_exists($this->storage_dir.'/'.$name) == false) {
			return false;
		} else {
			return true;
		}
	}

	public function load($name) {
		if(isset($name)) {
			// Do we need to update the data? If so, say false
			if($this->check($name)) {
				return false;
			}
			
			if($this->exists($name)) {
				$contents = file_get_contents($this->storage_dir.'/'.$name);
				return unserialize($contents);
			}
		}
		
		return false;
	}

	public function build($name, $data) {
		global $wpdb;
		
		if(!empty($name) && !empty($data)) {
			if(file_exists($this->storage_dir.'/'.$name)) {
				unlink($this->storage_dir.'/'.$name);
			}
			
			if($file = fopen($this->storage_dir.'/'.$name, 'w')) {
				$output_data = serialize($data);
				fwrite($file, $output_data);
				
				fclose($file);
				
				$wpdb->update( 
					'cache', 
					array( 
						'lastrun' => date($this->date_format),
					), 
					array(
						'name' => $name
					)
				);
				
				return $data;
			}
		}
		
		return false;
	}
	
	public function check($name) {
		global $wpdb;
		
		if(!$record = $wpdb->get_row("SELECT * FROM cache WHERE name='{$name}'")) {
			$wpdb->insert( 
				'cache', 
				array( 
					'name' => $name
				)
			);
			
			$record = $wpdb->get_row("SELECT * FROM cache WHERE name='{$name}'");
		}
		
		$start = $record->lastrun;
		$max_min = $record->runevery_min;
		$max_hour = $record->runevery_hour;
		$max_day = $record->runevery_day;
		
		if(empty($start)) {
			// Hasn't ever ran
			return true;
		}
		$end = date($this->date_format);
		
		$uts['start'] = strtotime( $start );
        $uts['end'] = strtotime( $end );
        if($uts['start'] !== -1 && $uts['end'] !== -1) {
            if($uts['end'] >= $uts['start']) {
                $diff = $uts['end'] - $uts['start'];
                if($days=intval((floor($diff/86400)))) {
					$diff = $diff % 86400;
				}
                if($hours=intval((floor($diff/3600)))) {
					$diff = $diff % 3600;
				}
                if($minutes=intval((floor($diff/60)))) {
					$diff = $diff % 60;
				}
                    
                $diff = intval( $diff );
                $time_diff = array(
					'days'		=> $days,
					'hours'		=> $hours,
					'minutes'	=> $minutes,
					'seconds'	=> $diff
				);
            } else {
                trigger_error("Ending date/time is earlier than the start date/time", E_USER_WARNING);
            }
        } else {
            trigger_error("Invalid date/time data detected", E_USER_WARNING);
        }
		
		if(isset($max_day)) {
			if($max_day != 0) {
				if($max_day < $time_diff['days']) {
					return true;
				}
			}
		}
		if(isset($max_hour)) {
			if($max_hour != 0) {
				if($max_hour < $time_diff['hours']) {
					return true;
				}
			}
		}
		if(isset($max_min)) {
			if($max_min != 0) {
				if($max_min < $time_diff['minutes']) {
					return true;
				}
			}
		}

		return false;
    }
}