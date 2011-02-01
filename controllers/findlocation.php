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
			
			$geocode = $this->geoGetCoords($address);
			
			if ($geocode)
			{
				//echo json_encode(array("status"=>"success", "message"=>array($geocode['lat'], $geocode['lon'])));
				//echo $geocode;
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
	
	//does all the geocoding work
	private function geoGetCoords($address) 
	{
		$address = rawurlencode($address);
		$_url = "http://maps.googleapis.com/maps/api/geocode/json?address=".$address.",%20Liberia&sensor=false&region=lr";
        
		$_result = false;
		if($_result = $this->fetchURL($_url)) 
		{
			$_result_parts = json_decode($_result);
			if($_result_parts->status!="OK")
			{
				return false;
			}
			echo "<ul>";
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
				$output = "<li><a href=\"#\" onclick=\"placeLocation(";
				$output .= $results->geometry->location->lat. ", ";
				$output .= $results->geometry->location->lng.", '";
				$output .= $place_name."'); return false;\"> ";
				$output .= $place_name. "</a></li>";
				echo $output;
				
			
			}
			echo "</ul>";
			
			$_coords['lat'] = $_result_parts->results[0]->geometry->location->lat;
			$_coords['lon'] = $_result_parts->results[0]->geometry->location->lng;
		}
			 
		return $_result;      
	}
	
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