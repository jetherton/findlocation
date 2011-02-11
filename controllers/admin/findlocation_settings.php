<?php defined('SYSPATH') or die('No direct script access.');
/**
 * SMS Automate Administrative Controller
 *
 * @author	   John Etherton
 * @package	   SMS Automate
 */

class Findlocation_settings_Controller extends Admin_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'settings';

		// If this is not a super-user account, redirect to dashboard
		if(!$this->auth->logged_in('admin') && !$this->auth->logged_in('superadmin'))
		{
			url::redirect('admin/dashboard');
		}
	}
	
	public function index()
	{
		
		$this->template->content = new View('findlocation/findlocation_admin');
		$this->template->content->cache_cleared = false;
		
		//create the form array
		$form = array
		(
		        'region_code' => "",
			'append_to_google' => "",
			'geonames_username' => "",
			'n_w_lat' => "",
			'n_w_lon' => "",
			's_e_lat' => "",
			's_e_lon' => ""
		);
		
		$errors = $form;
		$form_error = FALSE;
		$form_saved = FALSE;
				
		// check, has the form been submitted if so check the input values and save them
		if ($_POST)
		{
			// Instantiate Validation, use $post, so we don't overwrite $_POST
			// fields with our own things
			$post = new Validation($_POST);
			
			// Add some filters
			$post->pre_filter('trim', TRUE);
			//no real reason to require any of this stuff
			$post->add_rules('region_code', 'length[0,3]');
			$post->add_rules('append_to_google', 'length[0,200]');
			$post->add_rules('geonames_username', 'length[0,200]');
			$post->add_rules('n_w_lat', 'between[-90,90]');
			$post->add_rules('n_w_lon', 'between[-180,180]');
			$post->add_rules('s_e_lat', 'between[-90,90]');
			$post->add_rules('s_e_lon', 'between[-180,180]');
			
			 if ($post->validate())
			{
				
				$settings = ORM::factory('findlocation_settings')
					->where('id', 1)
					->find();
				if(!$settings->loaded)
				{
					$settings = ORM::factory('findlocation_settings');
				}
				$settings->region_code = $post->region_code;
				$settings->append_to_google = $post->append_to_google;
				$settings->geonames_username = $post->geonames_username;
				$settings->n_w_lat = $post->n_w_lat;
				$settings->n_w_lon = $post->n_w_lon;
				$settings->s_e_lat = $post->s_e_lat;
				$settings->s_e_lon = $post->s_e_lon;
				$settings->save();
				$form_saved = TRUE;
				$form = arr::overwrite($form, $post->as_array());
				
				
				//clear cache
				
				if(isset($_POST['clearcache']))
				{
					$cache_items = ORM::factory('findlocation_cache')->find_all();
					foreach($cache_items as $cache_item)
					{
						$cache_item->delete();
					}
					$this->template->content->cache_cleared = true;
				}
				
				
			}//end of if passed validation
			
			// No! We have validation errors, we need to show the form again,
			// with the errors
			else
			{
				// repopulate the form fields
				$form = arr::overwrite($form, $post->as_array());

				// populate the error fields, if any
				$errors = arr::overwrite($errors, $post->errors('settings'));
				$form_error = TRUE;
			}
		}
		else
		{
			//get settings from the database
			$settings = ORM::factory('findlocation_settings')
				->where('id', 1)
				->find();
			if($settings->loaded)
			{
				$form['region_code'] = $settings->region_code;
				$form['append_to_google'] = $settings->append_to_google;
				$form['geonames_username'] = $settings->geonames_username;
				$form['n_w_lat'] = $settings->n_w_lat;
				$form['n_w_lon'] = $settings->n_w_lon;
				$form['s_e_lat'] = $settings->s_e_lat;
				$form['s_e_lon'] = $settings->s_e_lon;
			}
		}//end of not being posted
		
		
		
		$this->template->content->form_saved = $form_saved;
		$this->template->content->form = $form;
		$this->template->content->form_error = $form_error;
		$this->template->content->errors = $errors;
		
	}//end index method
	
	

	
}