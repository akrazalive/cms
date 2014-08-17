<?php

//namespace TestCMS\CMSBundle\Entities;

//Run on the CLI with: phpunit test_DBConnection.php
$base_dir = "../cms/src/TestCMS/CMSBundle/";
require_once $base_dir . "Entities/RegularUser.php";
require_once $base_dir . "Entities/DBConnection.php";
require_once $base_dir . "Lib/common.php";





class UserTest extends PHPUnit_Framework_TestCase {

  protected static $conn;
  
  

  public static function setUpBeforeClass() {
    // Comment the next lines out to test the connect/close methods
    
    $db_params = get_db_params_from_config(); 
	  self::$conn = new DBConnection($db_params);
    self::$conn->connect();
  }
  
  
  public static function tearDownAfterClass() {
    // Comment this out if needed
    $ignored = self::$conn->close();
    //remove_test_data();  
  }
  
  
  public function testRegisterNew() {
    $users_f = __DIR__ . "/../cms/web/bundles/testcmscms/data/users_init.json";
    $json_strg = file_get_contents($users_f);
    $json_obj = json_decode($json_strg);
    
    $reg_user_json_obj = $json_obj->users[0];
    $json_strg = json_encode(array("user"=> $reg_user_json_obj));
    
    $r_user = new RegularUser(self::$conn);
    $results = $r_user->register_new($json_strg);
    
    echo "Errors: " . $results["error"] . ", result: " . $results["result"] . "\n";
    
    
    //$this->assertNotEquals(false, $res);

  }
  
  
}