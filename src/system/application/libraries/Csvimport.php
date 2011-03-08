<?php
/* 
 * Import CSV values for events and talks
 */

class Csvimport {

    private $_ci	    = null;

    public function __construct(){
		$this->CI=&get_instance();
		$this->CI->load->database();
    }

    public function import($file,$event_id){
		$this->CI->load->library('timezone');
	    $this->_importEvent($file, $event_id);
    }
    //---------------
    private function _importEvent($file, $event_id){
		$fp = fopen($file, 'r');
		$this->_event_id = $event_id;

		// check event exists, get event data
		$events_where = array('ID' => $event_id);
		$events_query = $this->CI->db->get_where('events', $events_where);
		if(is_array($events_query->result())) {
			$this->_event = array_shift($events_query->result());
		} else {
			throw new Exception("Invalid event ID " . $event_id);
			return;
		}

		// check required fields and work out which columns are where
		$title_row = fgetcsv($fp);
		foreach($title_row as $index => $column) {
			switch(strtolower($column)) {
				case 'title':
					$this->_title_index = $index;
					break;
				case 'description':
					$this->_description_index = $index;
					break;
				case 'date':
					$this->_date_index = $index;
					break;
				case 'time':
					$this->_time_index = $index;
					break;
				case 'language':
					$this->_language_index = $index;
					break;
				case 'speaker':
					$this->_speaker_index = $index;
					break;
				case 'track':
					$this->_track_index = $index;
					break;
				case 'type':
					$this->_type_index = $index;
					break;
				default:
					throw new Exception("<p>field " . $column . " ignored</p>\n");
					break;
			}
		}

		if(!isset($this->_title_index)) {
			throw new Exception("Title is a required field");
		}
		if(!isset($this->_description_index)) {
			throw new Exception("Description is a required field");
		}
		if(!isset($this->_date_index)) {
			throw new Exception("Date is a required field");
		}
		if(!isset($this->_time_index)) {
			throw new Exception("Time is a required field");
		}
		if(!isset($this->_speaker_index)) {
			throw new Exception("Speaker is a required field");
		}
		
		// get the talk categories
		$categories_query = $this->CI->db->get('categories');
		$categories_result = $categories_query->result();
		if(is_array($categories_result)) {
			foreach($categories_result as $cat) {
				$this->_categories[$cat->cat_title] = $cat;
			}
		}

		// pull a list of languages
		$languages_query = $this->CI->db->get('lang');
		$languages_result = $languages_query->result();
		if(is_array($languages_result)) {
			foreach($languages_result as $lang) {
				$this->_languages[$lang->lang_abbr] = $lang;
			}
		}

		// get the talk tracks
		$tracks_where = array('event_id' => $event_id);
		$tracks_query = $this->CI->db->get_where('event_track', $tracks_where);
		$tracks_result = $tracks_query->result();
		if(is_array($tracks_result)) {
			foreach($tracks_result as $track) {
				$this->_tracks[$track->track_name] = $track;
			}
		} else {
			$this->_tracks = array();
		}

		// FINALLY, actually import each row
        $talks = array();
		while($row = fgetcsv($fp)) {
			$talks[] = $this->_importSession($row);
		}

        // Write the data to a temporary file
        $tmpfile = sys_get_temp_dir().'/ji_import_'.$this->CI->session->userdata('session_id');
        file_put_contents($tmpfile, serialize($talks));

		return true;
    }

    
    private function _importSession($row){
		$talk_data=array(
		    'talk_title'  => $row[$this->_title_index],
		    'event_id'	  => $this->_event_id,
		    'talk_desc'	  => trim($row[$this->_description_index]),
		    'active'	  => 1,
			'lang'		  => 'uk',       // Default language
            'category'    => 'talk,',    // Default category
            'tracks'      => array()
		);

		// is there a language in this import, how about in this row?
		if(isset($this->_language_index) && isset($row[$this->_language_index])) {
			// if there's a language, is it valid?
			if(isset($this->_languages[$row[$this->_language_index]])) {
                $talk_data['lang'] = $row[$this->_language_index];
            } else {
                throw new Exception("Language " . $row[$this->_language_index] . " not supported");
            }
		}
        $talk_data['lang_name'] = $this->_languages[$talk_data['lang']]->lang_name;
        $talk_data['lang_id'] = $this->_languages[$talk_data['lang']]->ID;

		// handle date and time, this requires event tz to be set correctly
		
		$second = 0;
		$time = explode(':',$row[$this->_time_index]);
		$hour = $time[0];
		$minute = $time[1];

		// Date required in ISO EN18601 (YYYY-MM-DD)
		$date = explode('-',$row[$this->_date_index]);
		$day = $date[2];
		$month = $date[1];
		$year = $date[0];

		$tz = $this->_event->event_tz_cont . '/' . $this->_event->event_tz_place;

		$talk_data['date_given'] = $this->CI->timezone->UnixtimeForTimeInTimezone($tz, $year, $month, $day, $hour, $minute, $second);

		// handle the category - figure out which it is, then save it
		$talk_data['cat_id'] = $this->_categories['Talk']->ID;
        $talk_data['cat_title'] = $this->_categories['Talk']->cat_title;
		if(isset($this->_type_index)) {
			if(isset($this->_categories[$row[$this->_type_index]])) {
                $talk_data['cat_id'] = $this->_categories[$row[$this->_type_index]]->ID;
                $talk_data['cat_title'] = $this->_categories[$row[$this->_type_index]]->cat_title;
			} else {
			 	throw new Exception("Cannot create session of type " . $row[$this->_type_index]);
			}
		}

		// Import the speakers
		if(empty($row[$this->_speaker_index])) {
			throw new Exception("Speaker is a required field (Talk: " . $row[$this->_title_index] . ')');
		}
		$talk_data['speakers'] = explode(',', $row[$this->_speaker_index]);

		// handle the track - figure out which it is, then save it
		if(isset($this->_track_index) && !empty($row[$this->_track_index])) {
			$tracks = explode(',', $row[$this->_track_index]);
			foreach($tracks as $track) {
				if(isset($this->_tracks[$track])) {
                    $trackinfo = array();
                    $trackinfo['track_id'] = $this->_tracks[$track]->ID;
                    $trackinfo['track_name'] = $this->_tracks[$track]->track_name;
					$talk_data['tracks'][] = $trackinfo;
				} else {
					throw new Exception("Track " . $track . " is not recognized");
				}
			}
		}

        return $talk_data;
    }


    public function commitTalk($row){
        $talk_data = array();
        $talk_data['talk_title'] = $row['talk_title'];
        $talk_data['slides_link'] = '';
        $talk_data['event_id'] = $row['event_id'];
        $talk_data['talk_desc'] = trim($row['talk_desc']);
        $talk_data['active'] = 1;
        $talk_data['lang'] = $row['lang_id'];
        $talk_data['date_given'] = $row['date_given'];

        // save talk detail
        $result = $this->CI->db->insert('talks', $talk_data);
        if ($result === false) {
            // Initial db query failed. No need to rollback anything.
            return false;
        }

        // Save the current talk id
        $talk_id = $this->CI->db->insert_id();

        // Insert category
        $result = $this->CI->db->insert('talk_cat',array("talk_id" => $talk_id, "cat_id" => $row['cat_id']));
        if ($result === false) {
            $this->_rollbackTalk("error_category", $talk_id);
            return false;
        }

        // Insert all speakers
        foreach($row['speakers'] as $speaker) {
            $result = $this->CI->db->insert('talk_speaker', array("talk_id" => $talk_id, "speaker_name" => $speaker));
            if ($result === false) {
                $this->_rollbackTalk("error_speaker", $talk_id);
                return false;
            }
        }

        // Insert track join table
        foreach ($row['tracks'] as $track) {
            $result = $this->CI->db->insert('talk_track',array("talk_id" => $talk_id, "track_id" => $track['track_id']));
            if ($result === false) {
                $this->_rollbackTalk("error_track", $talk_id);
                return false;
            }
        }

        return true;
    }

    /**
     *
     * $state holds where where the insert currently resides. When something fails, we know
     * from which point on we should do a cleanup. Granted, that is what transactions are
     * for, but we are running on MyISAM.
     */
    protected function _rollbackTalk($state, $talk_id) {
        switch ($state) {
            case "error_track" :
                $this->CI->db->delete('talk_track', array('talk_id' => $talk_id));
                // fall through
            case "error_speaker" :
                $this->CI->db->delete('talk_speaker', array('talk_id' => $talk_id));
                // fall through
            case "error_category" :
                $this->CI->db->delete('talk_cat', array('talk_id' => $talk_id));
                // fall through
            case "error_talk" :
                $this->CI->db->delete('talks', array('ID' => $talk_id));
        }
    }
}
