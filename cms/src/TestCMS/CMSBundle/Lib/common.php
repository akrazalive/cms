<?php

/**
 * This file contains common functions. Requires the libyaml-dev package in 
 * Ubuntu, and the yaml package from PECL.
 *
 * @author Scott Davies
 * @version 1.0
 * @package encrypting
 */


/**
 * Gets Database connection values from the Symfony config.
 * 
 * @return array $db_params
 */
function get_db_params_from_config() {
  //
  $config_vals = yaml_parse_file(__DIR__ . "/../../../../app/config/parameters.yml");
  $params = $config_vals["parameters"];
  $db_params = array("hostname"=> $params["database_host"],
      "username"=> $params["database_user"],
      "password"=> $params["database_password"],
      "database"=> $params["database_name"],
      "options"=> array("port"=> "")
  );
  return $db_params;
}


/**
 * Gets Facebook app values from the Symfony app config.
 * 
 * Returns Facebook app parameters in the format:
 * array("app_id"=> "xxx", "app_secret"=> "xxx")
 * 
 * @return array
 */
function get_facebook_params_from_config() {
  $config_vals = yaml_parse_file("../Resources/config/central_config.yml");
  return $config_vals["facebook"];
}


/**
 * Gets Gmail SMTP values from the Symfony app config.
 *
 * @return array
 */
function get_gmail_params_from_config() {
  $config_vals = yaml_parse_file("../Resources/config/central_config.yml");
  return $config_vals["gmail"];
}


date_default_timezone_set("UTC");