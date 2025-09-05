<?php

// echo "debug on connect";
$data = array(
	'status' => false,
	'message' => "",
	'user'=>"",
);
if (isset($_POST['password']) and isset($_POST['third_party_id']) ) {
	
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/date_machine.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/notifier.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/coins.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/add_to_mailer.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/user_info.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/get_users.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/follow_a_person_by_sign_up.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";
	
	$username = mysqli_real_escape_string($connect, clean_inputs($_POST['username']));
	$password = clean_inputs($_POST["password"]);
	$md5pass = md5($password);
	$sha1pass = sha1($md5pass);
	$lastcrypt =  "Allin1".$sha1pass."7y";
	$lastcrypt = password_hash($lastcrypt, PASSWORD_BCRYPT);
	$email = (isset($_POST['email'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST["email"])) : null;
	// $email = filter_var($email, FILTER_VALIDATE_EMAIL);
	$verify1 = rand (10, 99);
	$verify2 = rand (10, 99);
	$verify3 = rand (10, 99);
	$unique_input = $verify1 . $verify2 . $verify3;
	$verified_input = 0;
	$third_party_id = mysqli_real_escape_string($connect, clean_inputs($_POST["third_party_id"]) );
	$ip = get_ip();
	if($ip = NULL){
		$ip = 0;
	}
	//increasing the wallet of the director....
	if(isset($_POST['referrer_id'])){
		$referrer_id = (isset($_POST['referrer_id'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST["referrer_id"])) : null;
		$user_info = user_info_third_party_id($connect, $referrer_id);
		$directed_by = (empty($user_info)) ? null : $user_info["id"];
	}else{
		$directed_by = (isset($_POST['directed_by'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST["directed_by"])) : null;
	}
	
	
	
	//getting time stuffs
	$user_id = 0;
	$gotten_time = (isset($_POST['signup_time'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['signup_time'])) : null;
	$gotten_date = (isset($_POST['signup_date'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['signup_date'])) : null;
	$locals = local_time_getter(null, $gotten_time, $gotten_date);
	$gotten_time = $locals['local_time'];
	$gotten_date = $locals['local_date'];
	$hash = generateHash();

	$data["message"] = "another test of process";
	//echo $gotten_date;
	if(filter_var($email, FILTER_VALIDATE_EMAIL) && num_count_email($connect, $email) == 0 || $email == null) {
        
        
        $inserter = mysqli_query($connect, "INSERT INTO `users` (`id`, `username`, `password`, `email`, `verified`, 
        `unique_code`, `country`, `language`, `celebrity`, `time_zone`, 
        `firstname`, `surname`, `growse_coin`, `growse_ability`, `city`, `town`,
        `schools`, `friends`, `followers`, `following`, `ip`, `profile_pix`, 
        `time`, `date`, `subscribe`, `category`, `fcm_token`, `third_party_id`, `addon`, `thirdparty_referral_code`) VALUES (NULL, '$username', '$lastcrypt', '$email', '0', 
        '$unique_input', '', '', '0', '', '', '', '0', '0', '', '', '', '0', '0', '0', '0', '../storage/profile/default_user.png', '$gotten_time', '$gotten_date', 'TRUE', '', '', '$third_party_id', '$hash', '$directed_by' );");
        
        
        if (!$inserter){
            $data["message"] = "Could not register users";
        }
        else if($inserter){
            $data["message"] = " registration complete ";
            $data["status"] = true;
			$data["hash"] = $hash;

            if ($directed_by > 0){
                $message_dir = 'You have been given fifty coins in Growse Ability for using your share-link. 
                    This coins can make you play any game and post anything and enchance your posting power. reuse link for more coins';
                notifier_inserter($directed_by, 0, 'profile', $message_dir, '', 0, $gotten_time, $gotten_date);
                coins_increase($directed_by, 200, $gotten_time, $gotten_date);
            }
            $this_user_row = user_info_register($unique_input, $lastcrypt);
			$data["user"] = $this_user_row;
            $newbie_id = $this_user_row['id'];
            if($newbie_id > 0){
                mailer_add($newbie_id, 1, 1, 1, 1, 1, 1, 1, 1);
                if($directed_by > 0 ) {follow_directed($newbie_id, $directed_by, 1, $gotten_time, $gotten_date);}
                $message_new = 'Welcome to Roynek. As an act of good will we have given you 150 coins to make your first post and play our games. 
                You can Invite friends and enrich your profile. It is nice to have you - Welcome';
                #notifier_inserter($newbie_id, 0, 'profile', $message, '', 0, $gotten_time, $gotten_date);
                #notifier_inserter($newbie_id, 0, 'profile', $message_new, '', 0, $gotten_time, $gotten_date);
                coins_increase($newbie_id, 150, $gotten_time, $gotten_date);
            }
            
            
            //echo $_SERVER['SERVER_NAME']."/backend/user_account_verification.php?user=".$unique_input."&key=gjhtiidsimi09403jfjkdknf";
        }else{$data["message"] = 'server delay please retry..';}
	}
	else {
        $data["message"] = " Invalid email, Example of a valid email looks like this-somebody@somewhere.com or an account has been registered with this email address";
    }
}

echo json_encode($data);

function clean_inputs($data){
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	$data = strip_tags($data);
	return $data;
}

function generateHash() {
    return bin2hex(random_bytes(16)); // Generate a 32 character hash
}

/* function isEmail($email) {
     if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
          return true;
     } else {
          return false;
     }
} */

function get_ip() {
 		if (isset($_SERVER['HTTP_CLIENT_IP'])){
 				return $_SERVER['HTTP_CLIENT_IP'];
 		}
 		elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
 			return $_SERVER['HTTP_X_FORWARDED_FOR'];
 		}
 		else {
 			return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');		
 		}
 	}
?>
