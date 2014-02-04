<?

/*
The purpose of this class is to manage scheduled events.


Irfan Rasool
*/

class scheduled_sharing{

	var $query; #A query object to be used for all MySQL queries within class.
	var $rpp=10; #Records per page. Used to paginate output. Set to 0 to disable pagination.
	var $is_more; #After retrieveing a page, indicates if there's more records to load.
	var $get_count=false; #If set to true, will get an item count for every request and save it into $count var.
	var $count=0; #If get_count is set to true, this variable will hold a number of total items in a previous query.
	var $failed_time=600; #Time, aftter which a scheduled task is considered to be failed if it wasn't shared.
	
	/*
		Construct function sets the query object to use it for all MySQL queries
	*/
	
	public function __construct($query){
		$this->query=$query;
	}

	/*
		Get a list of scheduled tasks for a single user
	*/
	
	public function get_schedule_for_irfan($user_id,$page=0){
		$params=Array('user_id'=>$user_id);
		if($page!=0) $params['page']=$page;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of scheduled tasks for a single api account
		It's important to set subgroup_id=false if you want to select schedule for account and all pages across this account.
		If it's set to subgroup_id='' - only schedule for base account will be selected. 
		And if it's set to group ID - only schedule for this group will be selected.
	*/
	
	public function get_schedule_for_account($user_api_id,$subgroup_id='',$page=0){
		$params=Array('user_api_id'=>$user_api_id);
		if($subgroup_id!==false) $params['user_api_page']=$subgroup_id;
		if($page!=0) $params['page']=$page;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of scheduled tasks for a single item (bm/element/etc.)
	*/
	
	public function get_schedule_for_item($item_type,$item_id){
		$params=Array('type'=>$item_type,'item_id'=>$item_id);
		if($page!=0) $params['page']=$page;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of scheduled tasks for a specific site id
	*/
	
	public function get_schedule_for_site($site_id,$user_id=0,$page=0){
		$params=Array('site_id'=>$site_id);
		if($user_id!=0) $params['user_id']=$user_id;
		if($page!=0) $params['page']=$page;
		return $this->get_schedule($params);
	}
	
	
	
	/*
		Get a list of drafts for a single api account
	*/
	
	public function get_schedule_for_irfan($user_api_id,$subgroup_id='',$page=0){
		$params=Array('user_api_id'=>$user_api_id);
		if($subgroup_id!==false) $params['user_api_page']=$subgroup_id;
		if($page!=0) $params['page']=$page;
		$params['status_id']=2;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of drafts for a single item (bm/element/etc.)
	*/
	
	public function get_drafts_for_item($item_type,$item_id){
		$params=Array('type'=>$item_type,'item_id'=>$item_id);
		if($page!=0) $params['page']=$page;
		$params['status_id']=2;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of drafts for a specific site id
	*/
	
	public function get_drafts_for_site($site_id,$user_id=0,$page=0){
		$params=Array('site_id'=>$site_id);
		if($user_id!=0) $params['user_id']=$user_id;
		if($page!=0) $params['page']=$page;
		$params['status_id']=2;
		return $this->get_schedule($params);
	}
	
	
	
	/*
		Get a list of under approval drafts for a single api account
	*/
	
	public function get_schedule_for_irfan($user_api_id,$subgroup_id='',$page=0){
		$params=Array('user_api_id'=>$user_api_id);
		if($subgroup_id!==false) $params['user_api_page']=$subgroup_id;
		if($page!=0) $params['page']=$page;
		$params['status_id']=3;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of under approval drafts for a single item (bm/element/etc.)
	*/
	
	public function get_drafts_under_approval_for_item($item_type,$item_id){
		$params=Array('type'=>$item_type,'item_id'=>$item_id);
		if($page!=0) $params['page']=$page;
		$params['status_id']=3;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of under approval drafts for a specific site id
	*/
	
	public function get_schedule_for_irfan($site_id,$user_id=0,$page=0){
		$params=Array('site_id'=>$site_id);
		if($user_id!=0) $params['user_id']=$user_id;
		if($page!=0) $params['page']=$page;
		$params['status_id']=3;
		return $this->get_schedule($params);
	}
	
	
	
	/*
		Get a list of scheduled tasks that was not shared on schedule
		$fail_notification_sent tells if we should get all failed tasks (false), 
		those, for which we sent notifictions (1) or those, for which we haven't sent notifictions yet (0)
	*/
	
	public function get_failed_tasks($user_id=0,$fail_notification_sent=false,$page=0){
		$params=Array(
			'min_dos'=>time()-(60*60*24), #Pick up failed tasks not older then one day. Just a failsafe for a case if we have some months old failed scheduled shares.
			'max_dos'=>time()-($this->failed_time)-1 #Max time of failed scheduled task is 10 minutes before now. If task was scheduled to share 10 minutes ago and wasn't shared, we consider it failed and do not try to share again.
		);
		if($user_id!=0) $params['user_id']=$user_id;
		if($fail_notification_sent!==false) $params['fail_notification_sent']=$fail_notification_sent;
		if($page!=0) $params['page']=$page;
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of scheduled tasks for current time
	*/
	
	public function get_schedule_for_now(){
		$params=Array(
			'max_dos'=>time(),
			'min_dos'=>time()-($this->failed_time)
		);
		return $this->get_schedule($params);
	}
	
	/*
		Get a list of scheduled tasks for current time
	*/
	
	public function get_single_task_by_id($id){
		$params=Array('id'=>$id,'status_id'=>'all');
		$tasks=$this->get_schedule($params);
		return $tasks[0];
	}
	
	/*
		Execute scheduled tasks that are below current time
		This function is (and must be) only used by cron job
	*/
	
	public function execute_current_tasks(){
	
		$tasks=$this->get_schedule_for_now();

		if(count($tasks)){
			$executed=$failed=0;
			foreach($tasks as &$task){
				$json_reply=json_decode($this->execute_task($task));
				#If no errors, we check if that account has more tasks
				#If it is so, we send email to user, who scheduled for it last
				if(!$json_reply->str_error){
					$tasks_for_account=$this->get_schedule_for_account($task->user_api_id,$task->user_api_page?$task->user_api_page:"");
					if(!count($tasks_for_account)){
						#Check if user has this notification turned on
						include_once(DOC_ROOT."includes/class.preferences.php");
						$pref=new Preferences();
						$notifications=$pref->fnNotifications($task->task_owner_id);
						#User has this option turned on - sending a notification
						if($notifications['email_schedule_empty']=='y'){
							#Getting user info. Need email and username
							$task_owner_info=fnGetUserInfo($task->task_owner_id);
							#Getting project
							$site=false;
							if($task->type=='element'){
								$this->query->query("SELECT sites.* 
									FROM bookmarks_elements 
									JOIN sites ON sites.id=bookmarks_elements.site_id AND bookmarks_elements.site_id!=0
									WHERE bookmarks_elements.id=".$task->item_id,SQL_FIRST,SQL_OBJECT);
								if(count($this->query->record)) $site=$this->query->record;
							}else{
								$this->query->query("SELECT sites.* 
									FROM bookmarks 
									JOIN sites ON sites.id=bookmarks.site_id AND bookmarks.site_id!=0
									WHERE bookmarks.id=".$task->item_id,SQL_FIRST,SQL_OBJECT);
								if(count($this->query->record)) $site=$this->query->record;
							}
							#Get a link to right inbox
							if($site) $inbox_link="<a href='http://".DOMAIN."/project/".($site->id)."/content/inbox/'>".($site->site_name)." project inbox</a>";
							else $inbox_link="<a href='http://".DOMAIN."/content/inbox/'>personal project inbox</a>";
							#Name of account/page that we just shared to and found that share queue is empty
							$account_name=$task->subgroup_info?$task->subgroup_info->name:$task->api_username;
							#Defining subject of email
							$subject="Shareist: ".$account_name." has no more items scheduled for sharing";
							#Defining html of email
							$message_body="We have noticed that you have no more items scheduled for sharing to \"".$account_name."\"  ".($task->api_display_name?$task->api_display_name:ucfirst($task->api_name))." account. You can schedule more shares from your ".$inbox_link." or any other <a href='http://".DOMAIN."/projects/'>project</a> you work on.";
							#Putting html into template
							$message_html=$pref->fnEmailtplHtml($task_owner_info->username,$task_owner_info->id,'','',$message_body);
							#Sending an email
							fnSendEmail($task_owner_info->email,$task_owner_info->username,$subject,$message_html);
						}
					}
					#Increment executed task counter
					$executed++;
				}else{
					#Increment failed tasks counter
					$failed++;
				}	
			}
			return $executed." task".($executed>1?"s":"")." executed, ".$failed." task".($failed>1?"s":"")." failed.";
		}else return "There are no tasks scheduled.";
	}
	
	/*
		Check for any tasks that were failed to be shared and notify their owners about the failure.
		Also update these tasks and save the time when emails were sent in fail_notification_sent field.
	*/
	
	function send_failure_notifications(){
		#Getting all failed tasks for which we haven't sent notifications
		$failed_tasks=$this->get_failed_tasks(0,0);
		#Sending notifications to author of each scheduled task
		if(count($failed_tasks)){
			$n=0;
			foreach($failed_tasks as $task){
				#Check if user has this notification turned on
				include_once(DOC_ROOT."includes/class.preferences.php");
				$pref=new Preferences();
				/////$notifications=$pref->fnNotifications($task->task_owner_id);
				#Getting user info. Need email and username
				$task_owner_info=fnGetUserInfo($task->task_owner_id);
				#Getting project
				$site=false;
				if($task->type=='element'){
					$this->query->query("SELECT sites.* 
						FROM bookmarks_elements 
						JOIN sites ON sites.id=bookmarks_elements.site_id AND bookmarks_elements.site_id!=0
						WHERE bookmarks_elements.id=".$task->item_id,SQL_FIRST,SQL_OBJECT);
					if(count($this->query->record)) $site=$this->query->record;
				}else{
					$this->query->query("SELECT sites.* 
						FROM bookmarks 
						JOIN sites ON sites.id=bookmarks.site_id AND bookmarks.site_id!=0
						WHERE bookmarks.id=".$task->item_id,SQL_FIRST,SQL_OBJECT);
					if(count($this->query->record)) $site=$this->query->record;
				}
				#Get a link to right inbox
				if($site) $scheduled_link="<a href='http://".DOMAIN."/project/".($site->id)."/content/scheduled/'>".($site->site_name)." schedule page</a>";
				else $scheduled_link="<a href='http://".DOMAIN."/content/scheduled/'>personal project schedule page</a>";
				#Name of account/page that we just shared to and found that share queue is empty
				$account_name=$task->subgroup_info?$task->subgroup_info->name:$task->api_username;
				#Defining subject of email
				$subject="Shareist: ".$account_name." share failed";
				#Defining html of email
				$message_body="The item you have scheduled for sharing to \"".$account_name."\" ".($task->api_display_name?$task->api_display_name:ucfirst($task->api_name))." was not shared due to an error which we are looking into right now. You can re-schedule your share from your ".$scheduled_link.".";
				#Putting html into template
				$message_html=$pref->fnEmailtplHtml($task_owner_info->username,$task_owner_info->id,'','',$message_body);
				#Sending an email
				fnSendEmail($task_owner_info->email,$task_owner_info->username,$subject,$message_html);
				
				#Update the task
				$this->query->query("UPDATE user_share_schedule SET fail_notification_sent=NOW() WHERE id=".$task->id.";");
				
				/*
					Log failed task
				*/
				
				$log_data=Array(
					'display_error_text'=>$message_body, #What user saw
					'user_data'=>$task_owner_info, #Complete user data
					'task'=>$task, #Scheduled share task
					'project_id'=>$site->id #Site id
				);
				require_once(DOC_ROOT."includes/functions.log.php");
				log_sharing_error("share_scheduled_failed",$log_data);
				unset($log_data);
				
				$n++;
			}
			return $n." fail notification".($n>1?"s":"")." sent.";
		}else return "No fail notifications sent.";
	}
	
	/*
		Take the data and immediately share it without creating scheduled task.
		This is used when sharing "Now".
	*/
	
	public function share_immediately($network_user_data,$api_acc,$post,$element,$page){
	
		#Prepare JSON response
		$response=new stdClass();
	
		#A dirty hack for class.sharing. It depends on many global variables so we bring them inside of this function's scope.
		#To avoid this in the future, classes should not rely on "global" vars and only pass data in arguments.
		global $req,$arrElement,$intSiteId,$int_Global_Site_Id,$user_data,$network_user_data,$query;
		
		require_once(DOC_ROOT."includes/class.sharing.php");
		require_once(DOC_ROOT."includes/functions.social.php");
		
		/*
		
			Prepare the input for $obj_social_share object.
			
		*/
		
		#Create an instance of class.sharing
		$obj_social_share=new Socialshare();
		
		$obj_social_share->str_fb_api_in_use=($fb_api_in_use)?$fb_api_in_use:"new";	#?
		
		$obj_social_share->api_acc=$api_acc;
		
		#Create a query object
		$query=new DB1_SQL("globaldb");
		
		#Set data for element
		if(is_numeric($post['element_id'])){
			$obj_social_share->int_element_id=$post['element_id'];
			$arrElement=get_element_data($post['element_id'],$network_user_data->id,"l");
			$obj_social_share->fn_Set_element_Info();
			$req=Array("","share","element",$post['element_id']);
		#Set data for page
		}else if(is_numeric($post['page_id'])){
			$obj_social_share->int_bm_id=$post['page_id'];			
			$obj_social_share->fn_Set_Bm_Info();			
			$req=Array("","share","page",$post['page_id']);
		}
		
		#Setting site id
		$intSiteId=($element['site_id']?$element['site_id']:($page['site_id']?$page['site_id']:0));
		$int_Global_Site_Id=$intSiteId;
		
		#Setting user data
		$user_data=get_user_social_data($api_acc['user_id']);
		$user_data->id=$network_user_data->id; #Hack. Use current user's ID with account owner's social data.
		$user_data->pre_id=$api_acc->id;
		$actual_network_user_data=$network_user_data;
		$network_user_data=$user_data;
		
		#Allow setting URL, other then original
		if($post['override_url']) $stripped_url=stripslashes($post['override_url']);
		else $stripped_url=stripslashes($post['url']);
		
		#Allow setting image, other then original
		if($post['override_image']) $post['image']=$post['override_image'];

		#Set share message
		$obj_social_share->str_bm_orig_url=$stripped_url;
		$obj_social_share->str_bm_full_url=$stripped_url;
		$obj_social_share->str_social_post_url=$stripped_url;
		$obj_social_share->str_share_msg=stripslashes($post['message']);
		if($post['no_image']!='on' and $post['image']) $obj_social_share->set_image_to_post=stripslashes($post['image']);
		if($post['title']) $obj_social_share->set_title_to_post=stripslashes($post['title']);
		if($post['description']) $obj_social_share->set_description_to_post=stripslashes($post['description']);
		
		$obj_social_share->share_post_link_to="original_source";
		
		#Set element type
		$obj_social_share->element_type=$element['element_type'];
		
		#Share link as image
		if($post['post_as_image']=='1' && $api_acc['api_name']=="facebook" && ($element['element_type']=='link' or $element['element_type']=='product' or is_numeric($post['page_id']))){
			$obj_social_share->element_type='image';
			$obj_social_share->set_title_to_post="";
			$obj_social_share->set_description_to_post="";
		}
		
		#Other properties
		$obj_social_share->str_share_log_type=$api_acc['api_full_name']."_".($post['element_id']?"element":"bm")."_share";
		$obj_social_share->arr_multiple_ac=Array($api_acc['subgroup_info']?$api_acc['subgroup_info']['id']:$api_acc['id']);

		#For fb we can set *_user_wall or *_page_wall
		if($api_acc['subgroup_info']){
			$obj_social_share->post_to_fb_page_wall=true;
			$obj_social_share->str_share_msg_fbpage=$obj_social_share->str_share_msg;
		}else{
			$obj_social_share->post_to_fb_user_wall=true;
			$obj_social_share->str_share_msg_fbpage="";
		}
		
		#Additional info for logging
		if($api_acc['subgroup_info']){
			$obj_social_share->last_updated_info['int_acc_page_id']=stripslashes($api_acc['subgroup_info']['id']);
			$obj_social_share->last_updated_info['str_acc_page_name']=stripslashes($api_acc['subgroup_info']['name']);
		}
		$obj_social_share->last_updated_info['element_type']=$element['element_type'];
		$obj_social_share->last_updated_info['shared_url']=$stripped_url;
		
		#For buffer
		if($api_acc['api_name']=="bufferapp") $obj_social_share->buffer_profile_id=$api_acc['subgroup_info']['id'];
		
		/*
		
			Execute sharing
			
		*/
		
		#Call the sharing function
		if($api_acc['api_name']=="facebook") $obj_social_share->fn_Share_On_Facebook();
		else if($api_acc['api_name']=="twitter") $obj_social_share->fn_Share_On_Twitter();
		else if($api_acc['api_full_name']=="linkedin") $obj_social_share->fn_Share_On_Linkedin();
		else if($api_acc['api_full_name']=="linkedin_group") $obj_social_share->fn_Share_On_Linkedin_Group();
		else if($api_acc['api_name']=="bufferapp") $obj_social_share->fn_Share_On_Bufferapp();
		else if($api_acc['api_name']=="tumblr") $obj_social_share->fn_Share_On_Tumblr();
		
		/*
			
			Reacting to error
		
		*/
		
		if($obj_social_share->is_error){
			#Get user friendly error massage
			$arr_error_info=Class_api_errors::fn_log_api_error($api_acc['api_name'],$obj_social_share->response_from_api);
			#Pass user friendly error to be shown to user
			$response->str_error=$arr_error_info['display_error_text'];
			#Log error data
			$log_data=Array(
				'display_error_text'=>$arr_error_info['display_error_text'], #What user saw
				'response_from_api'=>$obj_social_share->response_from_api, #What API responded to last request
				'user_data'=>fnSetNetworkUserData($actual_network_user_data->id), #Complete user data
				'project_id'=>$int_Global_Site_Id, #Site id
				'api_account'=>$api_acc, #Complete information about this social account
				'obj_social_share'=>(array)$obj_social_share #State of share object
			);
			require_once("includes/functions.log.php");
			log_sharing_error("share_now",$log_data);
		}

		/*
		
			Response
			
		*/
			
		#Set this hack back
		$network_user_data=$actual_network_user_data;
			
		#If it was successfuly shared
		if($obj_social_share->lastuserLogId){
			$response->is_shared=1;
			$response->log_id=$obj_social_share->lastuserLogId;
			$response->share_info=Array(
				'account_display_name'=>$api_acc['display_name']?$api_acc['display_name']:ucfirst($api_acc['api_name']),
				'account_icon_type'=>$api_acc['api_icon_type'],
				'account_avatar'=>$api_acc['subgroup_info']?$api_acc['subgroup_info']['logo']:$api_acc['avatar'],
				'account_name'=>$api_acc['subgroup_info']?$api_acc['subgroup_info']['name']:$api_acc['username'],
				'account_url'=>$api_acc['profile_url'],
				'post_url'=>$obj_social_share->str_social_post_url
			);
		}

		return $response;
	
	}
	
	/*
		Execute a single task
	*/
	
	public function execute_task(&$task){
		global $fb_api_in_use; #Do we need this?

		#Prepare JSON response
		$response=new stdClass();
		
		require_once(DOC_ROOT."includes/class.sharing.php");
		require_once(DOC_ROOT.'includes/class.social_accounts.php');
		require_once(DOC_ROOT."includes/functions.social.php");
		
		/*
		
			Preparing data and environment for $obj_social_share object
		
		*/

		#Setting site id
		global $intSiteId,$int_Global_Site_Id;
		$intSiteId=$task->site_id;
		$int_Global_Site_Id=$task->site_id;
		
		#Setting user data
		global $user_data,$network_user_data;
		$user_data=get_user_social_data($task->account_owner_id);
		
		#set task owner id as a user_id - BY AMOLM 05-07-13
		if($task->task_owner_id) $user_data->id_no=$task->task_owner_id; //Hack. Use current user's ID with account owner's social data.
		$user_data->id=$user_data->id_no;
		#$network_user_data=$user_data;
		$network_user_data=fnSetNetworkUserData($user_data->id);

		#Getting account info
		$sa=new social_accounts($this->query);
		$api_acc=$sa->get_user_api_account($task->user_id,$task->user_api_id,$task->user_api_page,false); #last argument sets if we want to use the cache
		
		if(!$api_acc){
			#Prepare JSON response
			$response->str_error="Problem getting account information.";
			/*
			if($sa->last_mysql_error) $json->error_message.=" Last MySQL error is: ".$sa->last_mysql_error;
			if($sa->last_api_error) $json->error_message.=" Last API error is: ".$sa->last_api_error;
			*/
			return json_encode($response);
		}
	
		#Create an instance of Socialshare
		$obj_social_share=new Socialshare();
		
		$obj_social_share->str_fb_api_in_use=($fb_api_in_use)?$fb_api_in_use:"new";	#?
		
		$obj_social_share->api_acc=$api_acc;

		#Set data for  page
		if($task->type=='page'){
			$obj_social_share->int_bm_id=$task->item_id;			
			$obj_social_share->fn_Set_Bm_Info();			
			global $req;
			$req=Array("","share","page",$task->item_id);
		#Set data for element
		}else if($task->type=='element'){
			$obj_social_share->int_element_id=$task->item_id;
			global $arrElement;
			$arrElement=get_element_data($task->item_id,$task->user_id,"l");
			$obj_social_share->fn_Set_element_Info();
			global $req;
			$req=Array("","share","element",$task->item_id);
		}
		
		#Create a query object
		global $query;
		$query=new DB1_SQL("globaldb");
		
		#Set share message
		$obj_social_share->str_bm_orig_url=$task->share_url;
		$obj_social_share->str_bm_full_url=$task->share_url;
		$obj_social_share->str_social_post_url=$task->share_url;
		$obj_social_share->str_share_msg=$task->share_message;
		if($task->share_image) $obj_social_share->set_image_to_post=$task->share_image;
		if($task->share_title) $obj_social_share->set_title_to_post=$task->share_title;
		if($task->share_description) $obj_social_share->set_description_to_post=$task->share_description;
		
		$obj_social_share->share_post_link_to="original_source";
		
		#Set notebook id
		$obj_social_share->notebook_id = (int)$task->site_id;
		
		#Set element type
		$obj_social_share->element_type=$task->additional_data->element_type;

		#Share link as image
		if($task->additional_data->post_as_image=='1' && $task->api_name=="facebook" && ($task->additional_data->element_type=='link' or $element['element_type']=='product' or $task->type=='page')){
			$obj_social_share->element_type='image';
			$obj_social_share->set_title_to_post="";
			$obj_social_share->set_description_to_post="";
		}
		
		#Other properties
		$obj_social_share->str_share_log_type=$task->api_full_name."_".($task->type=='page'?'bm':$task->type)."_share";
		$obj_social_share->arr_multiple_ac=Array($task->user_api_page?$task->user_api_page:$task->user_api_id);

		#For fb we can set *_user_wall or *_page_wall
		if($task->user_api_page) $obj_social_share->post_to_fb_page_wall=true;
		else $obj_social_share->post_to_fb_user_wall=true;
		
		#Additional info for logging
		if($api_acc['subgroup_info']){
			$obj_social_share->last_updated_info['int_acc_page_id']=$api_acc['subgroup_info']['id'];
			$obj_social_share->last_updated_info['str_acc_page_name']=$api_acc['subgroup_info']['name'];
			#$obj_social_share->last_updated_info['api_page_url']=$api_acc['profile_url'];
		}
		#$obj_social_share->last_updated_info['element_type']=$element['element_type'];
		$obj_social_share->last_updated_info['shared_url']=$task->share_url;
		
		#Mark that this share is done from scheduling class. This is so we can send user an email on sharing error.
		$obj_social_share->called_from_scheduling=1;
		
		#Pass user_schedule_share id
		$obj_social_share->schedule_share_id=$task->id;
		
		/*
		
			Execute sharing
		
		*/

		#Call the sharing function
		if($task->api_name=="facebook") $obj_social_share->fn_Share_On_Facebook();
		else if($task->api_name=="twitter") $obj_social_share->fn_Share_On_Twitter();
		else if($task->api_full_name=="linkedin") $obj_social_share->fn_Share_On_Linkedin();
		else if($task->api_full_name=="linkedin_group") $obj_social_share->fn_Share_On_Linkedin_Group();
		else if($task->api_full_name=="tumblr") $obj_social_share->fn_Share_On_Tumblr();
		
		/*
			
			Reacting to error
		
		*/
		
		if($obj_social_share->is_error){
			#Get user friendly error massage
			$arr_error_info=Class_api_errors::fn_log_api_error($api_acc['api_name'],$obj_social_share->response_from_api);
			#Pass user friendly error to be shown to user
			$response->str_error=$arr_error_info['display_error_text'];
			#Log error data
			$log_data=Array(
				'display_error_text'=>$arr_error_info['display_error_text'], #What user saw
				'response_from_api'=>$obj_social_share->response_from_api, #What API responded to last request
				'user_data'=>fnSetNetworkUserData($network_user_data->id), #Complete user data
				'project_id'=>$int_Global_Site_Id, #Site id
				'api_account'=>$api_acc, #Complete information about this social account
				'obj_social_share'=>(array)$obj_social_share #State of share object
			);
			require_once("includes/functions.log.php");
			log_sharing_error("share_now",$log_data);
		}
		
		#Update the error message for this scheduled share
		$this->query->query("SELECT additional_data FROM user_share_schedule WHERE id=".$task->id.";",SQL_FIRST,SQL_OBJECT);
		if($this->query->record){
			$additional_data=json_decode($this->query->record->additional_data);
			$additional_data->str_error=$response->str_error;
			$this->query->query("UPDATE user_share_schedule SET additional_data='".$this->query->db->escape(json_encode($additional_data))."' WHERE id=".$task->id.";",SQL_FIRST,SQL_OBJECT);
		}
		
		/*
		
			Response
		
		*/

		unset($user_data,$network_user_data,$req,$intSiteId);

		#Set task's status_id=10 (shared) if no API errors returned
		if(!$response->str_error) $this->query->query("UPDATE user_share_schedule SET status_id=10 WHERE id=".$task->id,SQL_ALL,SQL_OBJECT);
		
		#Return JSON response
		return json_encode($response);
	
	}
	
	/*
		Take all parameters an create a scheduled share task.
		This function takes user data, social account, post data, element, page and custom share date.
		Creation of a share task is also being logged.
		$force_date overrides the date, set by $_POST data. Used for crating multiple tasks at once.
	*/
	
	public function create_task_from_data($network_user_data,$api_acc,$post,$element,$page,$force_date=false){
	
		#This array is used to create a new scheduled task
		$task_params=Array();
		#Set user's id
		$task_params['user_id']=$network_user_data->id;
		#Set user-specific api id
		$task_params['user_api_id']=intval($api_acc['id']);
		#Set external page/group id if present
		if($api_acc['subgroup_info']) $task_params['user_api_page']=$api_acc['subgroup_info']['id'];
		#Set element/page info
		if(is_numeric($post['element_id'])){
			$task_params['type']="element";
			$task_params['item_id']=$post['element_id'];
		}else if(is_numeric($post['page_id'])){
			$task_params['type']="page";
			$task_params['item_id']=$post['page_id'];
		}
		
		#Set date of sharing
		if(is_null($force_date)){
			$task_params['dos']=NULL; #Set to NULL if this is a draft with no date set
		}else if($force_date!==false){
			$task_params['dos']=$force_date; #Use force_date
		}else{
			$task_params['dos']=$post['schedule_datetime']; #Use _POST date if date in all other cases
		}
		
		#Allow secting another URL
		if($post['override_url']){
			$post['url']=$post['override_url'];
			require_once(DOC_ROOT."includes/class.internal_shortener.php");
			$longurl=internal_shortener::expand($post['url']);
			if($longurl and $post['url']!=$longurl){
				$longurl=internal_shortener::remove_unique_param($longurl);
				$task_params['additional_data']['original_url']=$longurl;
			}
		}

		#Allow setting another image
		if($post['override_image']) $post['image']=$post['override_image'];

		#Set share message properties
		$task_params['share_url']=$post['url'];
		$task_params['share_message']=$post['message'];
		$task_params['share_image']=$post['image'];
		$task_params['share_title']=$post['title'];
		$task_params['share_description']=$post['description'];
		#Saving element type
		if($element) $task_params['additional_data']['element_type']=$element['element_type'];
		#Add page/group data if available

		if($task_params['user_api_page'] and $api_acc['subgroup_info']) $task_params['additional_data']['api_page_name']=stripslashes($api_acc['subgroup_info']['name']);
		if($task_params['user_api_page'] and $api_acc['profile_url']) $task_params['additional_data']['api_page_url']=$api_acc['profile_url'];

		#Share link as image
		if($post['post_as_image']=='1' && $api_acc['api_name']=="facebook" && ($element['element_type']=='link' or is_numeric($post['page_id']))) $task_params['additional_data']['post_as_image']=1;

		#Save as draft
		if(isset($post['save_unapproved'])) $task_params['status_id']=3; #3=unapproved
		else if(isset($post['save_draft'])) $task_params['status_id']=2; #2=draft

		#Create scheduled task
		$task_created_id=$this->add_record($task_params);

		#Log that share task was created
		if($task_created_id){
			
			$log_event_type=$api_acc['api_full_name']."_scheduled_share_".($task_params['type']=='page'?'bm':$task_params['type'])."_add";
			
			#Getting updated task
			$task=$this->get_single_task_by_id($task_created_id);
			
			#Log that the scheduled message was created
			fnGlobalLogUserAction(
				$task->type=="page"?$task->item_id:0,
				$task->item_id,
				$log_event_type,
				0, #What's this?
				$task->share_message,
				$task->user_api_id,
				date("Y-m-d H:i:s"),
				"",
				$task->user_id,
				$task->site_id,
				0,
				Array("int_acc_page_id"=>$task->user_api_page),
				0,
				date("Y-m-d H:i:s",strtotime($task->dos)),
				"",
				Array(
					"schedule_id"=>$task->id,
					"override_status"=>1,
					"override_type"=>$log_event_type,
					"set_title"=>$task->share_title,
					"set_description"=>$task->share_description,
					"set_image"=>$task->share_image
				)
			);
			
			#Return the task we created
			return $task;
			
		}else return false;

	}

	/*
		Function to create a new record using parameters
	*/
	
	public function add_record($params){
	
		$this->query->query("INSERT INTO user_share_schedule SET
			user_id=".$params['user_id'].",
			user_api_id=".$params['user_api_id'].",
			".($params['user_api_page']?"user_api_page='".$this->query->db->escape($params['user_api_page'])."',":"")."
			type='".$params['type']."',
			item_id=".$params['item_id'].",
			doe=NOW(),
			dlu=NOW(),
			".(!is_null($params['dos'])?"dos=FROM_UNIXTIME(".strtotime($params['dos'])."),":"")."
			status_id='".(is_numeric($params['status_id'])?$params['status_id']:1)."',
			share_url='".$this->smartslashes($params['share_url'])."',
			share_message='".$this->smartslashes($params['share_message'])."',
			share_image='".$this->smartslashes($params['share_image'])."',
			share_title='".$this->smartslashes($params['share_title'])."',
			share_description='".$this->smartslashes($params['share_description'])."'
			".(is_array($params['additional_data'])?",additional_data='".$this->query->db->escape(json_encode($params['additional_data']))."'":"")."
		",SQL_INSERT_ID);
		
		if(!$this->query->error){
			return $this->query->record;
		}else return false;
	
	}
	
	/*
		Function to update a record
	*/
	
	public function update_record($id,$params){
		$this->query->query("UPDATE user_share_schedule SET
			dlu=NOW()
			".(isset($params['dos'])?",dos=".(!is_null($params['dos'])?"FROM_UNIXTIME(".strtotime($params['dos']).")":"'0000-00-00 00:00:00'"):"")."
			".(is_numeric($params['status_id'])?",status_id='".$params['status_id']."'":"")."
			".(isset($params['share_message'])?",share_message='".$this->smartslashes($params['share_message'])."'":"")."
			".(isset($params['share_image'])?",share_image='".$this->smartslashes($params['share_image'])."'":"")."
			".(isset($params['share_title'])?",share_title='".$this->smartslashes($params['share_title'])."'":"")."
			".(isset($params['share_description'])?",share_description='".$this->smartslashes($params['share_description'])."'":"")."
			".(strtotime($params['dos'])>time()?",fail_notification_sent=NULL":"")."
			WHERE id=".intval($id)."
		");
		
		if(!$this->query->error) return true;
		else return false;
	}
	
	/*
		Function to delete record
	*/
	
	public function delete_record($id){
		$this->query->query("UPDATE user_share_schedule SET status_id=0 WHERE id=".intval($id));
		if(!$this->query->error) return true;
		else return false;
	}
	
	/*
		General function to retrieve and prepare all data related to schedule records.
		It's recommended to not use this function directly but use one of get_schedule_* functions instead.
	*/
	
	public function get_schedule($params){
		#If limit is set in params, use it
		if($params['limit']){
			$limit=$params['limit'];
		#Otherwise use internal limit for current page
		}else{
			#If pagination is on, set the LIMIT for MySQL query.
			if($this->rpp){
				$params['page']=(is_numeric($params['page']) and $params['page'])?$params['page']:1;
				#Instead of getting number of rows we're just checking if there's one more record to load.
				#This is quicker then doing a separate MySQL query to COUNT() all matching rows.
				$limit="LIMIT ".($params['page']-1)*$this->rpp.",".($this->rpp+1);
			}else $limit="";
		}
		
		#Build status_id IN string
		if(trim($params['status_id'])!="all"){
			$temp_status_id=explode(",",$params['status_id']);
			$status_id=Array();
			foreach($temp_status_id as $id) if(is_numeric(trim($id))) $status_id[]=trim($id);
			if(count($status_id)) $status_id=implode(",",$status_id);
			else $status_id=1; #Select only 'ready'
		}else unset($status_id); #Select all statuses

		$query_parts=Array(
			"SELECT",
			"
				uss.*,
				uss.user_id as task_owner_id,
				users.username,
				user_api_info.username AS api_username,
				user_api_info.avatar,
				user_api_info.user_id AS account_owner_id,
				user_api_info.profile_url AS profile_url,
				user_api_info.subgroups_json,
				api_service_info.api_name,
				api_service_info.display_name AS api_display_name,
				COALESCE(bookmarks.site_id,bookmarks_elements.site_id) AS site_id
			",
			"FROM",
			"
				user_share_schedule AS uss
				JOIN users ON users.id=uss.user_id
				JOIN user_api_info ON user_api_info.id=uss.user_api_id
				JOIN api_service_info ON api_service_info.id=user_api_info.api_id
				LEFT JOIN bookmarks ON (uss.type='bm' OR uss.type='page') AND bookmarks.id=uss.item_id
				LEFT JOIN bookmarks_elements ON uss.type='element' AND bookmarks_elements.id=uss.item_id
			",
			"WHERE",
			"
				1
				".(isset($status_id)?"AND uss.status_id IN (".$status_id.")":"")."
				".($params['id']?"AND uss.id='".$params['id']."'":"")."
				".($params['user_id']?"AND uss.user_id='".$params['user_id']."'":"")."
				".($params['user_api_id']?"AND uss.user_api_id='".$params['user_api_id']."'":"")."
				".(isset($params['user_api_page'])?"AND uss.user_api_page='".$params['user_api_page']."'":"")."
				".($params['type']?"AND uss.type='".$params['type']."'":"")."
				".($params['item_id']?"AND uss.item_id='".$params['item_id']."'":"")."
				".($params['max_dos']?"AND dos<=FROM_UNIXTIME(".$params['max_dos'].")":"")."
				".($params['min_dos']?"AND dos>=FROM_UNIXTIME(".$params['min_dos'].")":"")."
				".((isset($params['fail_notification_sent']) and $params['fail_notification_sent']!==false)?"AND fail_notification_sent IS ".($params['fail_notification_sent']===1?"NOT":"")." NULL":"")."
				".((isset($params['site_id']) and is_numeric($params['site_id']))?"AND (((uss.type='bm' OR uss.type='page') AND bookmarks.site_id=".intval($params['site_id']).") OR (uss.type='element' AND bookmarks_elements.site_id=".intval($params['site_id'])."))":"")."
				".((isset($params['site_id']) and !is_numeric($params['site_id']))?"AND (((uss.type='bm' OR uss.type='page') AND bookmarks.site_id IN (".$params['site_id'].")) OR (uss.type='element' AND bookmarks_elements.site_id IN (".$params['site_id'].")))":"")."
				".(isset($params['custom_sql'])?$params['custom_sql']:"")." 
			",
			"ORDER BY dos ASC",
			$limit
		);

		#Count query
		if($this->get_count){
			$query_parts_count=$query_parts;
			$query_parts_count[1]="COUNT(*) AS count";
			$query_parts_count[6]="";
			$query_parts_count[7]="";
			$this->query->query(implode(" ",$query_parts_count),SQL_FIRST,SQL_OBJECT);
			$this->count=$this->query->record->count;
		}

		#Normal query
		$this->query->query(implode(" ",$query_parts),SQL_ALL,SQL_OBJECT);

		#Is there more records to load
		$this->is_more=false;
		if($this->rpp and count($this->query->record)>$this->rpp){
			$this->is_more=true;
			array_pop($this->query->record);
		}
		
		#Prepare the data for being displayed.
		$scheduled_tasks=Array();
		if(count($this->query->record)){
			foreach($this->query->record as $r){
				#Decode additional data
				if($r->additional_data) $r->additional_data=json_decode($r->additional_data);
				#Define full api name to use for displaying icons
				if(isset($r->user_api_page) and $r->api_name=='facebook') $r->api_full_name="facebook_page";
				else if(isset($r->user_api_page) and $r->api_name=='linkedin') $r->api_full_name="linkedin_group";
				else $r->api_full_name=$r->api_name;
				#Define a link to user's profile
				if(!$r->profile_url and $r->api_name=='twitter') $r->profile_url="https://twitter.com/".$r->api_username."/";
				else if(!$r->profile_url and $r->api_name=='facebook') $r->profile_url="http://www.facebook.com/".$r->api_username."/";
				#Reshare URL
				$r->reshare_url="/share/".($r->type=='element'?"element":"page")."/".$r->item_id;
				#Time
				if($r->dos=="0000-00-00 00:00:00" or !$r->dos){
					$r->dos=false;
					$r->dos_unix=false;
					$r->dos_display="No time set";
					$r->dos_display_tooltip="No time set";
				}else{
					$r->dos_unix=strtotime($r->dos);
					$r->dos_display=date('m/d/y \a\t h:i A',$r->dos_unix);
					$r->dos_display_tooltip=date('j F, Y H:i',$r->dos_unix);
				}
				#Get information about subgroup
				if(isset($r->user_api_page) and isset($r->subgroups_json)){
					$subgroups=json_decode($r->subgroups_json);
					foreach($subgroups->subgroups as $subgroup){
						if($subgroup->id==$r->user_api_page){
							$r->subgroup_info=$subgroup;
							break;
						}
					}
				}
				#Get full element data for this record
				if($r->type=='element'){
					if(!isset($global_elements)){
						include_once(DOC_ROOT."includes/class.global_elements.php");
						$global_elements=new Global_Elements();
					}
					$r->element=$global_elements->fnFetchElement($r->item_id);
				}
				#Get full page data for this record
				if($r->type=='page'){
					if(!isset($global_bookmarks)){
						require_once(DOC_ROOT."includes/class.bookmarkGlobal.php");
						$global_bookmarks=new bookmarkGlobal();
					}
					$r->page=$global_bookmarks->getBookmark($r->item_id,false);
				}
				
				#Add row to an aoutput array
				$scheduled_tasks[]=$r;
			}
		}
		
		return $scheduled_tasks;
	
	}

	/*
		A utility function to properly escape MySQL strings
	*/
	
	private function smartslashes($string){
		if(!is_object($this->query)) $this->query=new GLOBAL_SQL();
		if(!$this->query->db->connection) $this->query->db->connect();
		#Return escaped data if it's no
		$string=$this->query->escape(get_magic_quotes_gpc()?stripslashes($string):$string);
		#Return ascaped data
		return $string;
	}

}

?>