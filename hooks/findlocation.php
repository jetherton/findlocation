<?php defined('SYSPATH') or die('No direct script access.');
/**
 * File Upload - sets up the hooks
 *
 * @author	   John Etherton
 * @package	   File Upload
 */

class findlocation {
	
	/**
	 * Registers the main event add method
	 */
	 
	 
	public function __construct()
	{
		// Hook into routing
		Event::add('system.pre_controller', array($this, 'add'));
		$this->post_data = null; //initialize this for later use
		
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		//check to see when the user is requesting reports/submit or admin/reports/edit
		if (Router::$controller == 'reports')
		{
			switch (Router::$method)
	        {
	        	case 'edit':
	        		Event::add('ushahidi_action.report_form_admin', array($this, '_add_urls'));
	        		plugin::add_javascript("findlocation/js/redo_find_location");
	        	case 'submit':
	        		Event::add('ushahidi_action.report_form', array($this, '_add_urls'));	        			        		
	        		plugin::add_javascript("findlocation/js/redo_find_location");
	        	break;    	
	        }
		}
	}
	
	public function _add_urls()
	{
		echo '<span id="base_url" style="display:none;">'.url::base().'</span>';
		echo '<span id="default_zoom" style="display:none;">'.Kohana::config('settings.default_zoom').'</span>';
	}
	
}

new findlocation;
