<?php

namespace TestCMS\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use TestCMS\CMSBundle\Entities\DBConnection;
use TestCMS\CMSBundle\Entities\RegularUser;

require_once __DIR__ . "/../Entities/DBConnection.php";
require_once __DIR__ . "/../Lib/common.php";
require_once __DIR__ . "/../Lib/Users/list.php";


class UsersController extends Controller
{
  
  public function listAction() {
    $db_params = get_db_params_from_config();
    $db_conn = new DBConnection($db_params);
    $db_conn->connect();
    
    $results = get_users($db_conn);
    
    return $this->render('TestCMSCMSBundle:Users:list.html.twig',
    array("results"=> $results));
  }
  
  
}