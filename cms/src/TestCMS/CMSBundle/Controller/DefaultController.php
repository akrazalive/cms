<?php


namespace TestCMS\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


require_once __DIR__ . "/../Lib/common.php";
require_once __DIR__ . "/../Lib/encrypting.php";


class DefaultController extends Controller
{
    // The homepage, i.e. a Log In page
    public function indexAction() {
      // Call check_auth()  
         
      return $this->render('TestCMSCMSBundle:Default:index.html.twig');
    }
    
    
    /**
     * Method POST only
     * Processes the log in data.
     */
    public function loginAction(Request $req) {
      
      // Call check_auth()
      $user_name = filter_var($req->request->get("uEmail"),
          FILTER_SANITIZE_EMAIL);
      $password_dec = filter_var($req->request->get("uPassword"),
              FILTER_SANITIZE_SPECIAL_CHARS);
      
      if ( (isset($user_name)) && (isset($password)) ) {
        //Check db match for user details
        $res_arr = retrieve_password_fields($conn, $user_name);
      
        //There is a match, so start a session
        session_start();
        $enc_vals = get_enc_vals();
        // Set 3 variables in the server SESSION.
        $_SESSION["salt"] = $enc_vals["salt"];
        $_SESSION["iv"] = $enc_vals["iv"];
        $_SESSION["userName"] = $user_name;
        $enc_pw = encrypt($_SESSION["salt"], $password_dec, $_SESSION["iv"]);
        $_SESSION["enc_pw"] = $enc_pw;
      
        // Set two cookies on the client machine.
        setcookie("userName", $user_name, time() + 28800, "/", "", 0);
        setcookie("password", $enc_pw, time() + 28800, "/", "", 0);
        
        return $this->render('TestCMSCMSBundle:Default:create_content.html.twig');
      }
    }
    
    
    // A Create Content page, the default after someone has logged in.
    public function createcontentAction() {
      // Call check_auth()
    
      return $this->render('TestCMSCMSBundle:Default:create_content.html.twig');
    }
    
   
}
