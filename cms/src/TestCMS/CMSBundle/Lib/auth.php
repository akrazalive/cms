<?php 


/**
* This file contains functions for authorization.
*
* @author Scott Davies
* @version 1.0
* @package
*/

require_once __DIR__ . "/../Entities/DBConnection.php";
require_once __DIR__ . "/encrypting.php";


/**
 * Checks that the two authorisation cookies and session variables have been
 * set. Also check that they match. The check_val will be 1 or 0.
 *
 * @return array
 */
function check_auth() {
  $check_val = 0;
  $test_strg = "";
  while (1) {
    // Check all client-side cookies exist.
    if ( (! isset($_COOKIE["userName"])) || (! isset($_COOKIE["password"])) ||
    (! isset ($_COOKIE["PHPSESSID"])) )
    {
      $test_strg .= "Auth Cookie missing";
      break;
    }
    if (session_id() == "") {
      session_start(addslashes($_COOKIE["PHPSESSID"]));
      $test_strg .= "Session started. ";
    }

    // Check all server-side session variables exit.
    if ( (! isset($_SESSION["salt"])) || (! isset($_SESSION["enc_pw"])) ||
    (! isset($_SESSION["iv"])) ) {
      $test_strg . "Session val missing.";
      break;
      //Get auth values from client cookies
      $cookie_user_name = filter_var(trim($_COOKIE["userName"]),
          FILTER_SANITIZE_STRING);
      $cookie_enc_password = filter_var(trim($_COOKIE["password"]),
          FILTER_SANITIZE_STRING);

      $dec_cookie_pw = decrypt($_SESSION["salt"], $cookie_enc_password,
          $_SESSION["iv"]);
      $dec_session_pw = decrypt($_SESSION["salt"], $_SESSION["enc_pw"],
          $_SESSION["iv"]);

      // Compare the user auth cookies with those stored in the SESSION.
      if ( ($cookie_user_name != $_SESSION["userName"]) ||
      ($dec_cookie_pw != $dec_session_pw) ) {
        $test_strg .= "Cookie & session vals don't match.";
        break;
      }
      $check_val = 1;
      break;
    }
    return array($test_strg, $check_val);
  }
}

  
/**
 * Attempts to retrieve password field from the user table in the DB.
 * @param "database connection object" $conn
 * @param string user_name
 * @return array
 */
function retrieve_password_field($db_conn, $user_name) {
  $results_arr = array("error"=> "", "results"=> array());
  $sql = "select password_enc from user where email = '";
  $sql .= $user_name . " and active = 1';";
  
  $res = $db_conn->query($sql);
  if (strlen($db_conn->get_error()) > 0) {
     $results_arr["error"] = $db_conn->get_error();
  }

  return $results_arr;
}