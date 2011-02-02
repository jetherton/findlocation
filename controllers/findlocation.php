<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   John Etherton
 * @package	   SMS Automate
 */

class Findlocation_Controller extends Controller
{
	
	
	public function index()
	{
	

	}// end of index
	
	
	//does the geocoding
	public function geocode()
	{
		$this->template = "";
		$this->auto_render = FALSE;

		if (isset($_GET['address']) AND ! empty($_GET['address']))
		{
			$geocode = map::geocode($_GET['address']);
			$address = $_GET['address'];
			
			$google_geocode = $this->google_geocode($address);
			$geonames_geocode = $this->geonames_geocode($address);
			
			$geocode = $this->merge_results($google_geocode, $geonames_geocode);
			
			if (count($geocode) > 0)
			{
				$view = View::factory('findlocation/location_results');
				$view->places = $geocode;
				$view->render(TRUE);
			}
			else
			{
				echo "<strong>Sorry No Results for $address</strong>";
			}
		}
		else
		{
			echo "<strong>Sorry No Results for $address</strong>";
		}
	}//end of geocode
	
	
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
	private function google_geocode($address) 
	{
		//get the region stuff from the database
		$region_str = "";
		$append_str = "";
		$settings = ORM::factory('findlocation_settings')->where('id', 1)->find();
		if($settings->loaded)
		{
			if($settings->region_code != "")
			{
				$region_str = "&region=".$settings->region_code;
			}
			$append_str = rawurlencode($settings->append_to_google);
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
	private function geonames_geocode($address) 
	{
		//get the region stuff from the database
		$region_str = "";
		$append_str = "";
		$username = "";
		$settings = ORM::factory('findlocation_settings')->where('id', 1)->find();
		if($settings->loaded)
		{
			if($settings->region_code != "")
			{
				$region_str = "&country=".$settings->region_code;
			}
			$append_str = rawurlencode($settings->append_to_google);
			$username = $settings->geonames_username;
		}
		else
		{
			return array();
		}
		
		
		$address = rawurlencode($address);
		$_url = "http://api.geonames.org/searchJSON?q=".$address.$append_str."&username=".$username.$region_str;
        
		$ret_val = array();
		
		$_result = false;
		if($_result = $this->fetchURL($_url)) 
		{
			$_result_parts = json_decode($_result);
			
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