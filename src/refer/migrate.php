<?php
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.
include "plugin_info.php";
if (in_array($user->data()->id, $master_account) && pluginActive($plugin_name,true)){
//all actions should be performed here.

//check which updates have been installed
$count = 0;
$db = DB::getInstance();

//Make sure the plugin is installed and get the existing updates
$checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
$checkC = $checkQ->count();
if($checkC < 1){
  $fields = array(
    'plugin'=>$plugin_name,
    'status'=>'installed',
  );
  $db->insert('us_plugins',$fields);
  $checkQ = $db->query("SELECT id,updates FROM us_plugins WHERE plugin = ?",array($plugin_name));
  $checkC = $checkQ->count();
}
if($checkC > 0){
  $check = $checkQ->first();
  if($check->updates == ''){
  $existing = []; //deal with not finding any updates
  }else{
  $existing = json_decode($check->updates);
  }

  //list your updates here from oldest at the top to newest at the bottom.
  //Give your update a unique update number/code.

  //here is an example
  $update = '00001';
  if(!in_array($update,$existing)){
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");

  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00002';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE settings ADD COLUMN site_url varchar(255)");
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00003';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE plg_refer_settings ADD COLUMN ref_string varchar(255)");
  $db->query("ALTER TABLE plg_refer_settings ADD COLUMN ref_notice varchar(255)");
  $db->update("plg_refer_settings",1,["ref_string"=>"Please enter your referral code"]);
  $db->update("plg_refer_settings",1,["ref_notice"=>"You must have a valid referral code to register"]);
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }

  $update = '00004';
  if(!in_array($update,$existing)){
  $db->query("ALTER TABLE plg_refer_settings CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
  logger($user->data()->id,"Migrations","$update migration triggered for $plugin_name");
  $existing[] = $update; //add the update you just did to the existing update array
  $count++;
  }






  //after all updates are done. Keep this at the bottom.
  $new = json_encode($existing);
  $db->update('us_plugins',$check->id,['updates'=>$new,'last_check'=>date("Y-m-d H:i:s")]);
  if(!$db->error()) {
    logger($user->data()->id,"Migrations","$count migration(s) susccessfully triggered for $plugin_name");
  } else {
   	logger($user->data()->id,"USPlugins","Failed to save updates, Error: ".$db->errorString());
  }
}//do not perform actions outside of this statement
}
