<?php 
/**
 * This file creates tables with their fields in the database. Run it once on 
 * the CLI.
 * 
 * It requires these packages in Ubuntu: php5-dev, libyaml-dev. It also 
 * requires the yaml PHP extension installed through PECL, then enabled in 
 * php.ini. 
 * 
 * @author Scott Davies
 * @version 1.0
 * @package
 */


require_once(__DIR__ . "/../../Entities/DBConnection.php");
require_once(__DIR__ . "/../common.php");
use TestCMS\CMSBundle\Entities\DBConnection;



/* Creates a table. */
function create_tbl_access_group($db_conn) {
  $sql = "create table access_group(
name varchar(30),
access_add_user tinyint,
access_edit_user tinyint,
access_list_users tinyint,
primary key (name)
);";
  $db_conn->query($sql);
  return $db_conn->get_error();
}


/* Creates a table. */
function create_tbl_user($db_conn) {
  $sql = "create table user(
id int not null auto_increment,
first_name varchar(50),
last_name varchar(50),
email varchar(75),
password_enc varchar(100),
group_name varchar(30),
active tinyint,
created_date date,
updated_date date,
facebook_id varchar(25),
twitter_id varchar(25),
primary key (id)
);";
  $db_conn->query($sql);
  return $db_conn->get_error();    
}


/**
 * Drops all tables in the database, if required.
 * 
 * @param "database connection object" $conn
 */
function drop_all_tables($db_conn) {
	$sql = "show tables;";
	$res = $db_conn->query($sql);
	foreach($res as $row) {
		$tbl = $row["Tables_in_cms"];
		$sql = sprintf("drop table if exists %s;", $tbl);
		$res = $db_conn->query($sql);
		if (strlen($db_conn->get_error()) < 1) {
			echo "Table '" . $tbl . "' dropped.\n";
		}
		else {
			echo sprintf("Problem, table '%s' NOT dropped.\nError: %s", $tbl, 
			            $db_conn->get_error());
		}
	}
	return;
}


/**
 * Creates all the tables via functions.
 * 
 * @param "database connection object" $conn
 * @return string $error_msg
 */
 
function create_all_tables($db_conn) {	
	$error_msg = "";
  $res = create_tbl_access_group($db_conn);
  $error_msg .= $db_conn->get_error();
  $res = create_tbl_user($db_conn);
  $error_msg .= $db_conn->get_error();
	return $error_msg;
}


/**
 * Reads a JSON data file into a string, and returns an assoc. array.
 * 
 * @param unknown $web_data_dir
 * @param unknown $fname
 * @return array
 */
function fetch_json_data($web_data_dir, $fname) {
  $path_to_f = sprintf("%s%s%s", $web_data_dir, DIRECTORY_SEPARATOR, $fname); 
  $json_strg = file_get_contents($path_to_f);
  return json_decode($json_strg);
}


/**
 * Inserts access groups into the DB.
 * 
 * @param unknown $db_conn
 * @param unknown $json_obj
 */
function insert_default_groups($db_conn, $json_obj) {
  $sql = "insert into access_group(
name, access_add_user, access_edit_user, access_list_users)
values %s;";
  $group_strgs = array();
  foreach ($json_obj->access_groups as $group) {
    $group_strgs[] = sprintf("('%s', %d, %d, %d)", 
    $group->name,
    $group->access_add_user,
    $group->access_edit_user,
    $group->access_list_users
    ); 
  }
  $sql = sprintf($sql, join($group_strgs, ","));
  $res = $db_conn->query($sql);
  return;
}
  

/**
 * Inserts users into the DB.
 *
 * @param unknown $db_conn
 * @param unknown $json_obj
 */
function insert_default_users($db_conn, $json_obj) {
  $sql = "insert into user(
first_name, last_name, email, password_enc, group_name, active, created_date, 
updated_date, facebook_id, twitter_id
)
values %s;";
  $user_strgs = array();
  foreach ($json_obj->users as $user) {
    $user_strgs[] = sprintf("('%s', '%s', '%s', '%s', '%s', %d, '%s', '%s', 
'%s', '%s')",
      $user->first_name,
      $user->last_name,
      $user->email,
      $user->password_enc,
      $user->group_name,
      $user->active,
      $user->created_date,
      $user->updated_date,
      $user->facebook_id,
      $user->twitter_id
    );
  }
  $sql = sprintf($sql, join($user_strgs, ","));
  $res = $db_conn->query($sql);
}


/**
 * Main flow of program.
 */
$web_data_dir = __DIR__ . "/../../../../../web/bundles/testcmscms/data";
$groups_data_fname = "groups_init.json";
$users_data_fname = "users_init.json";

//Try connecting to the database.
$db_params = get_db_params_from_config();
$db_conn = new DBConnection($db_params);
$db_conn->connect();
if (strlen($db_conn->get_error()) > 0) {
  echo "Error: " . $db_conn->get_error();
  exit();
}
//Clear out any existing data.
drop_all_tables($db_conn);
//Create the tables in the database.
$error_msg = create_all_tables($db_conn);
if (strlen($error_msg) > 0) {
  echo $error_msg . "\n";
}
else {
  echo "All tables created.\n";
}
//Insert default access_groups into db.
$groups_json_obj = fetch_json_data($web_data_dir, $groups_data_fname);
insert_default_groups($db_conn, $groups_json_obj);
if (strlen($db_conn->get_error()) > 0) {
  echo "Error: " . $db_conn->get_error();
  exit(0);
}
else {
  echo "Records inserted into access_group. \n";
}
//Insert default users into db.
$users_json_obj = fetch_json_data($web_data_dir, $users_data_fname);
insert_default_users($db_conn, $users_json_obj);
if (strlen($db_conn->get_error()) > 0) {
  echo "Error: " . $db_conn->get_error();
  exit(0);
}
else {
  echo "Records inserted into user. \n";
}


$res = $db_conn->close();