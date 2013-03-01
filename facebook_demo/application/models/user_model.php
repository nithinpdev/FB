<?php
	/**
	 * CodeIgniter Facebook Connect Graph API User Model 
	 * 
	 * Author: Graham McCarthy (graham@hitsend.ca) HitSend inc. (http://hitsend.ca)
	 * 
	 * VERSION: 1.0 (2010-09-30)
	 * LICENSE: GNU GENERAL PUBLIC LICENSE - Version 2, June 1991
	 * 
	 **/
 class User_model extends Model {
   var $user_id = "";
   var $full_name = "";
   var $pwd = "";
   var $fb_uid = "";
   
   function User_model() {
      //Call the Model constructor
      parent::Model();

   }

   function validate_user_facebook($uid = 0) {
		//confirm that facebook session data is still valid and matches
		$this->load->library('fb_connect');
		
   		//see if the facebook session is valid and the user id in the sesison is equal to the user_id you want to validate
		$session_uid = 'fb:' .  $this->fb_connect->fbSession['uid'];
		if(!$this->fb_connect->fbSession || $session_uid != $uid ) {
   	  		return false;
		}
        
   	  	//Receive Data
      	$this->user_id    = $uid;

      //See if User exists
      $this->db->where('user_id', $this->user_id);
      $q = $this->db->get('users');

      if($q->num_rows == 1) {
         //yes, a user exists,
		 return true;
      }

      //no user exists
      return false;
   }
     
   function create_user($db_values = '') {
      $this->user_id       = $db_values["user_id"];
      $this->full_name  = $db_values["full_name"];
      $this->pwd           = md5($db_values["pwd"]);
      if(strlen($db_values['fb_uid']) > 0) {
      	$this->fb_uid 	   = $db_values['fb_uid'];
      } else {
      	 $this->fb_uid = "";
      }
      
      $new_user_data = array(
          'user_id'  => $this->user_id,
          'full_name'  => $this->full_name,
          'pwd'      => $this->pwd,
          'fb_uid' => $this->fb_uid,
      );

      $insert = $this->db->insert('users', $new_user_data);

      return $insert;
   }
   
   function get_user_by_fb_uid($fb_uid = 0) {
	   	//returns the facebook user as an array.
	   	$sql = " SELECT * FROM users WHERE 0 = 0 AND fb_uid = ?";
	   	$usr_qry = $this->db->query($sql, array('fb:'.$fb_uid));
	   	
	   	if($usr_qry->num_rows == 1) {
	   		//yes, a user exists
	   		return $usr_qry->result();
	   	} else {
	   		// no user exists
	   		return false;
	   	}
   }	
 }