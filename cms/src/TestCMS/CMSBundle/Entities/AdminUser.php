<?php

namespace TestCMS\CMSBundle\Entities;

require_once "User.php";


class AdminUser extends User {

  
  public function __construct($db_conn) {
    parent::__construct($db_conn);
  }
    
  
  protected function decrypt_password() {
  
  }
  
  
  public function activate() {
  
  }
  
  
  public function create_user() {
    
  }


  public function edit_user() {
  
  }
  
  
  
  

}