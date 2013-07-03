<?php
/**
 * File Upload - Install
 *
 * @author	   John Etherton
 * @package	   File Upload
 */

class Findlocation_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the actionable plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name
		// HT: Added `fuzzy` boolean NOT NULL default \'0\' for fuzzy search
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'findlocation_settings` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `region_code` varchar(10) DEFAULT NULL,
				  `append_to_google` varchar(255) DEFAULT NULL,
				  `geonames_username` varchar(255) DEFAULT NULL,
				  `n_w_lat` double NOT NULL default \'0\',
				  `n_w_lon` double NOT NULL default \'0\',
				  `s_e_lat` double NOT NULL default \'0\',
				  `s_e_lon` double NOT NULL default \'0\',
				  `fuzzy` boolean NOT NULL default \'0\',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'findlocation_cache` (
				  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				  `search_term` varchar(255) DEFAULT NULL,
				  `result_name` varchar(255) DEFAULT NULL,
				  `lat` double NOT NULL default \'0\',
				  `lon` double NOT NULL default \'0\',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
				
	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'findlocation_settings`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'findlocation_cache`');
	}
}