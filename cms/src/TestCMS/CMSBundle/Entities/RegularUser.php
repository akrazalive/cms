<?php

namespace TestCMS\CMSBundle\Entities;

require_once "User.php";


class RegularUser extends User {

  
  public function __construct($db_conn) {
    parent::__construct($db_conn);
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



}