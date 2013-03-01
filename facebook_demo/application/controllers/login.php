<?php
	/**
	 * CodeIgniter Facebook Connect Graph API Login Controller 
	 * 
	 * Author: Graham McCarthy (graham@hitsend.ca) HitSend inc. (http://hitsend.ca)
	 * 
	 * VERSION: 1.0 (2010-09-30)
	 * LICENSE: GNU GENERAL PUBLIC LICENSE - Version 2, June 1991
	 * 
	 **/
class Login extends Controller {
	function Login() {
    	parent::Controller();
    	$this->load->model('user_model');
	}

	function index() {
		//create blank data array to return
		$data = array();
	   
	    $this->load->library('fb_connect');
   		$data = array(
						'facebook'		=> $this->fb_connect->fb,
						'fbSession'		=> $this->fb_connect->fbSession,
						'user'			=> $this->fb_connect->user,
						'uid'			=> $this->fb_connect->user_id,
						'fbLogoutURL'	=> $this->fb_connect->fbLogoutURL,
						'fbLoginURL'	=> $this->fb_connect->fbLoginURL,	
						'base_url'		=> site_url('login/facebook'),
						'appkey'		=> $this->fb_connect->appkey,
					);
   				
	  	$this->load->view('login_view', $data);
   }

   //This won't destroy your facebook session
   function logout() {
   		$this->session->sess_destroy();
      	$data['logged_out'] = TRUE;
      	redirect('/login/index');
   } // function logout()

   
   function _facebook_validate($uid = 0) {
   		//this query basically sees if the users facebook user id is associated with a user.
   		$bQry = $this->user_model->validate_user_facebook($uid);
      	
		if($bQry) { // if the user's credentials validated...
        	$data = array(
            				'user_id' => $uid,
							'is_logged_in' => true, 
							'list_type' => 'hot'
			);
         	
			$this->session->set_userdata($data);
         
         	$uri_var = $this->uri->segment(3);
         	
         	if (strlen($uri_var) > 0 ){
				$url_location = $uri_var;
	      		$url_location = str_replace('-', '/', $url_location);
				redirect($url_location);
			} else{
				redirect('/message/index');
			}
			 
      	} else {
        	// incorrect username or password
        	$data = array();
         	$data["login_failed"] = TRUE;
         	$this->index($data);
      	}
   }
   
   
   function facebook() {
   		//1. Check to see if the facebook session has been declared
   		$this->load->library('fb_connect');
   		
   		if(!$this->fb_connect->fbSession) {
   			//2. If No, bounce back to login
   			$this->index();
   		} else {
   			
   			$fb_uid = $this->fb_connect->user_id;
   			$fb_usr = $this->fb_connect->user;
   			
   			if($fb_uid != false) {
	   			//3. If yes, see if the facebook id is associated with any existing account
	   			$usr = $this->user_model->get_user_by_fb_uid($fb_uid);
	   			
	   			if( is_array($usr) && count($usr) == 1) {
	   				$usr = $usr[0]; //the model returns an object array, so get the first elemet of it which contains all of the data we need.
	   				//3.a. if yes, log the person in
	   				//echo "Logging in via facebook...";
	   				$this->_facebook_validate($usr->user_id);
	   			} else {
	   				//3.b. if no, register the new user.
	   				//echo "Creating a new account...";
	   				$fname = $fb_usr["first_name"]; 
	   				$lname = $fb_usr["last_name"];
	   				$fullname = $fb_usr["name"];
	   				$pwd = ''; //left blank so user can modify this later
	   				$email = $fb_usr["email"];
	   				
	   				$db_values = array (
                     'user_id' => "fb:".$fb_uid,
	   				 'fb_uid' => "fb:".$fb_uid,
                     'full_name' => $fullname,
                     'pwd' => "",
           			 );
           			 
           			//data ready, try to create the new user 
	   				if($query = $this->user_model->create_user($db_values) ) {
              			$data['account_created'] = true;
               			//log user in
               			$this->_facebook_validate($db_values["user_id"]);
            		} else {
            			//Did not work, go back to login page
              			$this->index();
            		}
	   			}
	   		} 
   		}
   }   
}