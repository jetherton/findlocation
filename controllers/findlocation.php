<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   John Etherton
 * @package	   SMS Automate
 */

class Findlocation_Controller extends Controller
{
	
	var $status_message = ''; // HT: variable for status of request
	
	public function index()
	{
	

	}// end of index
	
	
	//does the geocoding
	public function geocode()
	{
		$this->status_message = ""; // HT: Reset status message on each request
		$this->header = '<br/><h4>'.Kohana::lang("ui_main.search_results").':</h4>';
		$this->template = "";
		$this->auto_render = FALSE;
		
		$settings = ORM::factory('findlocation_settings')->where('id', 1)->find();

		if (isset($_GET['address']) AND ! empty($_GET['address']))
		{

			$address = strtoupper($_GET['address']);
			
			//check if this is already in the cache
			$geocode = $this->check_cache($address);
			if(count($geocode) > 0)
			{
				if($geocode[0]['name'] == null)
				{
					echo $this->header."<strong>Sorry No Results for $address</strong>";
					return;
				}
				
				//check if null
				$view = View::factory('findlocation/location_results');
				$view->places = $geocode;
				$view->render(TRUE);
				return;
			}
			
			//it wasn't in the cache so go find it
			$google_geocode = $this->google_geocode($address, $settings);
			$geonames_geocode = $this->geonames_geocode($address, $settings);
			
			$geocode = $this->merge_results($google_geocode, $geonames_geocode);
			
			$geocode = $this->check_bounding_box($geocode, $settings);
			
			//put the findings in the cache
			$this->put_in_cache($address, $geocode);
			
			if (count($geocode) > 0)
			{
				
				
				$view = View::factory('findlocation/location_results');
				$view->places = $geocode;
				$view->render(TRUE);
			}
			else
			{
				// HT: Updated the message when no result or error
				// echo $this->header."<strong>Sorry No Results for $address</strong>";
				echo $this->header;
				if($this->status_message != "")
				{
					echo "<strong>".$this->status_message."</strong><br/><br/>";
				}
				echo "<strong>Sorry No Results for $address</strong>";
			}
		}
		else
		{
			echo $this->header."<strong>Please specify a location</strong>";
		}
	}//end of geocode
	
	
	/****************************
	* Puts the findings of the geocode
	* into the cache
	*****************************/
	private function put_in_cache($address, $geocode)
	{
		if( count($geocode) == 0)
		{
			$cache = ORM::factory('findlocation_cache');
			$cache->search_term = $address;
			$cache->result_name = null;
			$cache->lat = null;
			$cache->lon = null;
			$cache->save();
		}
		
		foreach($geocode as $item)
		{
			$cache = ORM::factory('findlocation_cache');
			$cache->search_term = $address;
			$cache->result_name = $item['name'];
			$cache->lat = $item['lat'];
			$cache->lon = $item['lon'];
			$cache->save();
		}
	}
	
	
	/*****************************
	* Checks to see if the given address
	* has already been geolocated
	* if so returns the locations, otherwise returns an empty array
	*******************************/
	private function check_cache($address)
	{
		$retval = array();
		
		$cache = ORM::factory('findlocation_cache')
			->where('search_term', $address)
			->find_all();
		foreach($cache as $item)
		{
			$retval[] = array("name"=>$item->result_name, "lat"=>$item->lat, "lon"=>$item->lon);
		}
		
		return $retval;
			
	}
	
	private function check_bounding_box($results, $settings)
	{
		//makie sure the bounding box is specified
		if($settings->loaded)
		{
			if($settings->n_w_lat == 0 &&
				$settings->n_w_lon == 0 &&
				$settings->s_e_lat == 0 &&
				$settings->s_e_lon == 0)
			{
				return $results;
			}
		}
		else
		{
			return $results;
		}
	
		//first check and see if the bounding box is even specified
		
		$retval = array();
		foreach($results as $result)
		{
			
			//figure out if we're crossing the int date line
			$dateline = false;
			if($settings->n_w_lon > $settings->s_e_lon)
			{ //crossing the int date line
				$dateline = true;
			}
			
			//first test the lat
			if ($result['lat']  <= $settings->n_w_lat && $result['lat'] >= $settings->s_e_lat)
			{
			}
			else //it isn't inside the bounds
			{
				continue;
			}
			//next test the lon
			if ( ((!$dateline) && ($result['lon']  >= $settings->n_w_lon) && ($result['lon'] <= $settings->s_e_lon)) ||
				($dateline) && ((($result['lon'] >= $settings->n_w_lon) && ($result['lon'] <= 180)) ||  (($result['lon'] <= $settings->s_e_lon) && ($result['lon'] >= -180))) )
			{
				$retval[] = $result;
			}
			else //it isn't inside the bounds
			{
				continue;
			}
		}
		
		return $retval;
	}
	
	
	private function merge_results($google, $geonames)
	{
		$google_result = array();
		foreach($google as $g)
		{
			$include = true;
			
			foreach($geonames as $n)
			{
				$distance = $this->getDistance($n['lat'], $n['lon'], $g['lat'], $g['lon']);
				if ($distance < 1000) //if less than 2km
				{
					$include = false;
					break;
				}
			}
			
			if($include)
			{
				$google_result[] = $g;
			}
		}
		
		return array_merge($geonames, $google_result);
		
	}//end merge_results
	
	
	//$distance between two points in meters
	private function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
	{
		$earth_radius = 6371;

		$dLat = deg2rad($latitude2 - $latitude1);
		$dLon = deg2rad($longitude2 - $longitude1);

		$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
		$c = 2 * asin(sqrt($a));
		$d = $earth_radius * $c;

		return $d;
	}
	
	
	
	
	//does all the geocoding work
	private function google_geocode($address, $settings)
	{
		//get the region stuff from the database
		$region_str = "";
		$append_str = "";
		
		if($settings->loaded)
		{
			if($settings->region_code != "")
			{
				$region_str = "&region=".$settings->region_code;
			}
			$append_str = rawurlencode(strtoupper($settings->append_to_google));
		}
		
		
		$address = rawurlencode($address);
		$_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$address.$append_str."&sensor=false".$region_str;
        
		$_result = false;
		if($_result = $this->fetchURL($_url))
		{
			$_result_parts = json_decode($_result);
			if($_result_parts->status!="OK")
			{
				return array();
			}
			$ret_val = array();
			foreach($_result_parts->results as $results)
			{
				$place_name = "";
				$count = 0;
				foreach($results->address_components  as $component)
				{
					$count++;
					if($count>1)
					{
						$place_name .= ", ";
					}
					$place_name .= $component->long_name;
				}
				$lat = $results->geometry->location->lat;
				$lon = $results->geometry->location->lng;
				$ret_val[] = array("name"=>$place_name, "lat"=>$lat, "lon"=>$lon);
				
			}
		}
			 
		return $ret_val;
	}//end of get google coordinates
	
	
	
		//does all the geocoding work
	private function geonames_geocode($address, $settings)
	{
		//get the region stuff from the database
		$fuzzy_str = ""; // HT: Added for fuzzy search
		$region_str = "";
		$append_str = "";
		$username = "";
		if($settings->loaded)
		{
			if($settings->region_code != "")
			{
				$region_str = "&country=".$settings->region_code;
			}
			// HT: If condition added for fuzzy search
			if($settings->fuzzy)
			{
				$fuzzy_str = "&fuzzy=0.6";
			}
			// HT: End of code If condition added for fuzzy search
			$append_str = rawurlencode($settings->append_to_google);
			$username = $settings->geonames_username;
		}
		else
		{
			return array();
		}
		
		
		$address = rawurlencode($address);
		$_url = "http://api.geonames.org/searchJSON?q=".$address.$append_str."&username=".$username.$region_str;
		$_url .= $fuzzy_str; // HT: Fuzzy url string added for fuzzy search
		$ret_val = array();
		
		$_result = false;
		if($_result = $this->fetchURL($_url))
		{
			$_result_parts = json_decode($_result);
			
			if(isset($_result_parts->status))
			{
					if($_result_parts->status->message != '') // HT: Added to check the status messages
					//if($_result_parts->status->message == 'user does not exist.') // HT: removed as was checking only one status
					{
						$this->status_message = $_result_parts->status->message; // HT: Status Message set to be displayed
						return array();
					}
			}
			
			
			$results_count = intval($_result_parts->totalResultsCount);
			if( $results_count = 0)
			{
				return array();
			}
			foreach($_result_parts->geonames as $results)
			{
				$place_name = "";
				$count = 0;
				
				$place_name = $results->name. ", ". $results->adminName1. ", ". $results->countryName;
				
				$lat = $results->lat;
				$lon = $results->lng;
				$ret_val[] = array("name"=>$place_name, "lat"=>$lat, "lon"=>$lon);
				
			}
		}
			 
		return $ret_val;
	}//end of get google coordinates

	
	/**
	* fetch a URL. Override this method to change the way URLs are fetched.
	*
	* @param string $url
	*/
	private function fetchURL($url)
	{

		return file_get_contents($url);

	}




}