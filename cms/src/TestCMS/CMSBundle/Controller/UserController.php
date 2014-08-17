<?php

namespace TestCMS\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TestCMS\CMSBundle\Entities\DBConnection;
use TestCMS\CMSBundle\Entities\RegularUser;


require_once __DIR__ . "/../Entities/RegularUser.php";
require_once __DIR__ . "/../Entities/DBConnection.php";
require_once __DIR__ . "/../Lib/common.php";
require_once __DIR__ . "/../Lib/User/register.php";

require_once( __DIR__ . "/../Lib/Facebook/FacebookRedirectLoginHelper.php" );
require_once( __DIR__ . "/../Lib/Facebook/FacebookRequest.php" );
require_once( __DIR__ . "/../Lib/Facebook/FacebookResponse.php" );
require_once( __DIR__ . "/../Lib/Facebook/FacebookSDKException.php" );
require_once( __DIR__ . "/../Lib/Facebook/FacebookRequestException.php" );
require_once( __DIR__ . "/../Lib/Facebook/FacebookAuthorizationException.php" );
require_once( __DIR__ . "/../Lib/Facebook/GraphObject.php" );

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRedirectLoginHelper;


class UserController extends Controller
{
  
  // Method: GET
  public function registerAction() {
    $result = array("result"=> "", "error"=> "");
    return $this->render("TestCMSCMSBundle:User:register.html.twig",
      array("result"=> $result));
  }
  
  
  /** Attempts to register a new user and send an email
   * Method: POST 
  */
  public function register_processAction(Request $req) {
    
    $result = array("result"=> "", "error"=> "");
    
    $user_register_details = array("user"=> array(
"first_name"=> filter_var($req->request->get("uFirstName"), 
    FILTER_SANITIZE_SPECIAL_CHARS),
"last_name"=> filter_var($req->request->get("uLastName"),
    FILTER_SANITIZE_SPECIAL_CHARS),
"email"=> filter_var($req->request->get("uEmail"), 
    FILTER_SANITIZE_EMAIL),
"password_dec"=> filter_var($req->request->get("uPassword"),
    FILTER_SANITIZE_SPECIAL_CHARS),
"group_name"=> "regular",
"active"=> 1,
"created_date"=> date("Y-m-d"),
"updated_date"=> "",
"facebook_id"=> "",
"twitter_id"=> ""
  ) );
    
    $false_check_arr = array("first_name", "last_name", "email", "password_dec");
    $user_check = $user_register_details["user"];
    foreach ($false_check_arr as $false_check) {
      if (! $user_check[$false_check]) {
        $result["error"] .= "Please enter a valid value for " . $false_check . ". ";
      }
    }
    
    if (strlen($result["error"]) == 0) {
      $db_params = get_db_params_from_config();
      $db_conn = new DBConnection($db_params);
      $db_conn->connect();
      
      $r_user = new RegularUser($db_conn);
      $reg_results = $r_user->register_new(json_encode($user_register_details));
      $result["result"] .= $reg_results["result"];
      $result["error"] .= $reg_results["error"];
    }
    
    if (strlen($result["error"]) == 0) {
      // Send confirmation email
      $em_result = send_email("cms@scottdnz.net", "cms_replies@scottdnz.net", 
      $user_register_details["email"]);
      // Deal with email errors ...
      
      return $this->render("TestCMSCMSBundle:Default:create_content.html.twig",
          array("result"=> $result)
      );
      
    }
    
    return $this->render("TestCMSCMSBundle:User:register.html.twig",
      array("result"=> $result)
    );
  }
  
  
  // This will basically be the REST API
  public function authenticateAction(Request $req) {
    $results = array("token"=> "", "success"=> "false", "message"=> "", 
        "error"=> "");
    
    if (0 === strpos($this->getRequest()->headers->get("Content-Type"), 
        "application/json")) {
      $data = json_decode($this->getRequest()->getContent(), true);
    }
    else {
      $results["error"] .= "Wrong format received. ";
    }
    
    $email = filter_var($data["email"], FILTER_SANITIZE_EMAIL);
    $password_dec = filter_var($data["password"],
        FILTER_SANITIZE_SPECIAL_CHARS);
    
    $db_params = get_db_params_from_config();
    $db_conn = new DBConnection($db_params);
    $db_conn->connect();
    
    $res_arr = retrieve_password_fields($conn, $user_name);
    $enc_vals = get_enc_vals();
    $enc_pw = encrypt($_SESSION["salt"], $password_dec, $_SESSION["iv"]);
    
    // Search database for user with matching encrypted password.
    // ...
    
    $response = new Response(json_encode($results));
    $response->headers->set('Content-Type', 'application/json');
    return $response;
  }
  
  
  // Attempt to log in via Facebook.
  public function loginfbAction() {
    $fb_params = get_facebook_params_from_config();
    $result = array("success"=> "", "error"=> "");
    
    session_start();
    
    //App Id, Secret
    FacebookSession::setDefaultApplication($fb_params["app_id"],
      $fb_params["app_secret"]);
    
    $helper = new FacebookRedirectLoginHelper("http://newcms.scottdnz.net");
    try {
      $session = $helper->getSessionFromRedirect();
    }
    catch(FacebookRequestException $ex) {
      // When Facebook returns an error
       $result["error"] .= "Exception: " . $ex . ". ";
    }
    catch(Exception $ex) {
      // When validation fails or other local issues
      $result["error"] .= "Exception: " . $ex . ". ";
    }
    if ($session) {
      // Logged in
      $result["success"] .= "Logged in. ";
      // Take Facebook ID and store it for user in database.
      // ...
      return $this->render("TestCMSCMSBundle:Default:create_content.html.twig",
        $result);
    }
    else {
      return $this->render("TestCMSCMSBundle:Default:index.html.twig", $result);
    }
  }
  

  // A Create User page
  public function createAction() {
    // Call check_auth() for an admin user
    
    return $this->render("TestCMSCMSBundle:User:create.html.twig");
  }
  
  
  // An Edit User page
  public function editAction() {
    // Call check_auth() for an admin user
    
    return $this->render("TestCMSCMSBundle:User:edit.html.twig");
  }
   
}
