<?php
/**
* This file contains encrypting & decrypting functions.
*
* @author Scott Davies
* @version 1.0
* @package encrypting
*/


/**
 * Returns a randomly generated string set to the length value passed in. The
 * string contains only alphanumeric characters.
 * 
 * @param int $len_val
 * @return string $rand_strg
 */
function get_random_strg($len_val=16) {
  $rand_strg = "";
  for ($i = 0; $i < $len_val; $i++) {
    $rand_num = rand(1,62);
    if ($rand_num < 11) {
      // Digits are in ASCII range 48-57.
      $add_val = 47;
    }
    else if ($rand_num < 37) {
      // Capital alphabetic characters are in range 65-90.
      $add_val = 54;
    }
    else {
      // Lower case alphabetic characters are in range 97-122.
      $add_val = 60;
    }
    $rand_num += $add_val;
    $rand_strg .= chr($rand_num);
  }
  return $rand_strg;
}


/**
 * Returns a random salt key and initialization vector for encryption.
 * 
 * @return array
 */
function get_enc_vals() {
  $salt = get_random_strg();
  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  return array("salt"=> $salt, "iv"=> $iv);
}


/**
 * Encrypts a string with a salt and an initialization vector. Returns the 
 * new string.
 * 
 * @param string $salt
 * @param string $text
 * @param string $iv 
 * @return string
 */
function encrypt($salt, $text, $iv) {
  /* Syntax: mcrypt_encrypt ($cipher, $key, $data, $mode, $iv) */
  $enc_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text,
      MCRYPT_MODE_ECB, $iv);
  return trim(base64_encode($enc_text));
}


/**
 * Decrypts a string with a salt and an initialization vector. Returns the
 * new string.
 * 
 * @param string $salt
 * @param string $text
 * @param string $iv 
 * @return string
 */
function decrypt($salt, $text, $iv) {
  $text = base64_decode($text);
  /* Syntax: mcrypt_decrypt ($cipher, $key, $data, $mode, $iv) */
  $dec_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, $text,
      MCRYPT_MODE_ECB, $iv);
  return trim($dec_text);
}
