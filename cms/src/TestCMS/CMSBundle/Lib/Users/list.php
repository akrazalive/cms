<?php

/**
 * This file contains functions for dealing with users. 
 *
 * @author Scott Davies
 * @version 1.0
 * @package encrypting
 */

use TestCMS\CMSBundle\Entities\DBConnection;

require_once __DIR__ . "/../../Entities/DBConnection.php";
require_once __DIR__ . "/../../Lib/common.php";


/**
 * Retrieves users from the database.
 * 
 * @param number $active
 */
function get_users($db_conn, $active=1) {
  $results = array("error"=> "", "users"=> array());
  
  $sql = "select id, first_name, last_name, email, group_name, DATE_FORMAT(
created_date,'%d/%m/%Y') as created_date, DATE_FORMAT(updated_date, '%d/%m/%Y') 
as updated_date, active from user where active = 1 order by last_name, first_name;";
  $res = $db_conn->query($sql);
  if (strlen($db_conn->get_error()) > 0) {
    $results["error"] .= $db_conn->get_error();
  }
  else {
    foreach ($res as $rec) {
      if ($rec["active"]) {
        $active = "Y";
      }
      else {
        $active = "N";
      }
      $user_arr = array("id"=> $rec["id"],
          "first_name"=> $rec["first_name"],
          "last_name"=> $rec["last_name"], 
          "email"=> $rec["email"], 
          "group_name"=> $rec["group_name"],
          "created_date"=> $rec["created_date"],
          "updated_date"=> $rec["updated_date"],
          "active"=> $active);
      $results["users"][] = $user_arr;
    }
  }
  return $results;
}


/* Test function
function test_get_users() {
  $db_params = get_db_params_from_config();
  $db_conn = new DBConnection($db_params);
  $db_conn->connect();
  
  $results = get_users($db_conn);
  foreach ($results["users"] as $user) {
    var_dump($user);
  }
}
*/