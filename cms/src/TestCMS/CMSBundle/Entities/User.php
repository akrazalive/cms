<?php

namespace TestCMS\CMSBundle\Entities;

require_once __DIR__ . "/../Lib/encrypting.php";
require_once "DBConnection.php";


abstract class User {
  private $attribs;
  private $db_conn;


  public function __construct($db_conn) {
    $this->attribs = array(
        "id"=> -1,
        "first_name"=> "",
        "last_name"=> "",
        "email"=> "",
        "password_enc"=> "",
        "group_name"=> "",
        "active"=> 0,
        "created"=> "00-00-0000",
        "updated"=> "00-00-0000",
        "facebook_id"=> "",
        "twitter_id"=> "");
    $this->db_conn = $db_conn;
  }



  public function get_attribs() {
  
  }
  
  
  public function set_attribs() {
  
  }
  
  
  public function update_attribs() {
  
  }
  
  
  protected function decrypt_password() {
  
  }
  
  
  public function activate() {
  
  }
  
  
  private function initial_user_info_check($json_arr) {
    $errors = "";
    $compulsory_keys = array("first_name", "last_name", "email", 
        "group_name", "active");
    $numeric_keys = array("id", "active");
    $date_keys = array("created", "updated");
    $date_patn = "/^\d{4}-\d{2}-\d{2}$/";
    
    // Check that all compulsory keys & values exist & are set. 
    foreach ($compulsory_keys as $compulsory) {
      if (array_key_exists($compulsory, $json_arr)) {
        if (! isset($json_arr[$compulsory])) {
          $errors .= "The user value for " . $compulsory . " is missing. ";
        }      
      }
      else {
        $errors .= "The user key " . $compulsory . " is missing. ";
      }      
    }
    // Check that all numeric fields are of the right type. 
    foreach ($numeric_keys as $numeric) {
      if ( (array_key_exists($numeric, $json_arr)) && 
      (isset($json_arr[$numeric])) ) {
        if (! is_numeric($json_arr[$numeric])) {
          $errors .= "The user value for " . $numeric . "must be a number. ";
        }
      }
    }
    // Check that all date fields are of the right format.
    foreach ($date_keys as $date) {
      if ( (array_key_exists($date, $json_arr)) &&
      (isset($json_arr[$date])) ) {
        if (! preg_match($date_patn, $json_arr[$date])) {
          $errors .= "The user value for " . $date . 
"must be in the format yyyy-mm-dd. ";
        }
      }
    }
    // Check that either a password_dec or facebook_id key exists & is set.
    if (! ((array_key_exists("password_dec", $json_arr)) ||  
      (array_key_exists("facebook_id", $json_arr))) ) {
      $errors .= "The user must have a key for either password_dec or
facebook_id. ";
    } 
        
    if (! ( (isset($json_arr["password_dec"]))  || 
      isset($json_arr["facebook_id"])) ) {
      $errors .= "The user must have a value for either password_dec or 
facebook_id. ";
    } 
    return $errors;
  }
  
  
  private function set_initial_missing_values($user) {
    $blank_strgs = array("password_enc", "facebook_id", "twitter_id");
    if ( (! array_key_exists("created_date", $user)) ||
          (! isset($user["created_date"])) ||
          ($user["created_date"] = "0000-00-00") ) { 
      $user["created_date"] = date("Y-m-d");
    } 
    if ( (! array_key_exists("updated_date", $user)) ||
    (! isset($user["updated_date"])) ) {
      $user["updated_date"] = "0000-00-00";
    }
    foreach ($blank_strgs as $blank_strg) {
      if ( (! array_key_exists($blank_strg, $user)) ||
      (! isset($user[$blank_strg])) ) {
        $user[$blank_strg] = "";
      }
    }
    return $user;
  }
    
  
  public function register_new($json_strg) {
    $results = array("error"=> "", "result"=> "");
    $json_arr = json_decode($json_strg, true);
    //var_dump($json_arr);
    
    $user = $json_arr["user"];
    
    $errors = $this->initial_user_info_check($user);
    if (strlen($errors) > 0) {
      $results["error"] .= $errors;
    }
    
    if (strlen($results["error"]) == 0) {
      $user = $this->set_initial_missing_values($user);
      //var_dump($user);
      
      $enc_vals = get_enc_vals();
      $enc_pw = encrypt($enc_vals["salt"], $user["password_dec"], $enc_vals["iv"]);
    
      $sql = sprintf("insert into user(
      first_name, last_name, email, password_enc, group_name, active, created_date,
      updated_date, facebook_id, twitter_id
      )
      values ('%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', '%s', '%s');",
      $user["first_name"],
      $user["last_name"],
      $user["email"],
      $enc_pw,
      $user["group_name"],
      $user["active"],
      $user["created_date"],
      $user["updated_date"],
      $user["facebook_id"],
      $user["twitter_id"]
      );
    
      $res = $this->db_conn->query($sql);
      if (strlen($this->db_conn->get_error()) > 0) {
        $results["error"] .= $this->db_conn->get_error();
      }
      else {
        $results["result"] .= "User added successfully into database. ";
      }
    }
    return $results;
  }
  
  
  public function find_by_email() {
    
  
  }


}