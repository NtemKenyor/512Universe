<?php
session_start();
// require require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/date_machine.php";

require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/date_machine.php";

require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/notifier.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/coins.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/add_to_mailer.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/get_users.php";

$data = array(
	'status' => false,
	'message' => "",
	'user'=>"",
);

if (isset($_SESSION['tele_id']) and isset($_SESSION['uname']) and isset($_POST['email'])){
	// $tele_id = clean_inputs($_SESSION['tele_id']);
    // The truth is I sent some POST stuff as a scam because all we need is just the active session.
    $third_party_id = (isset($_SESSION['tele_id'])) ? clean_inputs($_SESSION["tele_id"]) : '';
    $userid = (isset($_SESSION['userid'])) ? clean_inputs($_SESSION["userid"]) : '';
    $username = (isset($_SESSION['uname'])) ? clean_inputs($_SESSION["uname"]) : '';
    $firstname = (isset($_POST['firstname'])) ? clean_inputs($_POST["firstname"]) : '';
    $lastname = (isset($_POST['lastname'])) ? clean_inputs($_POST["lastname"]) : '';
    $email = clean_inputs($_POST['email']);

    // echo $third_party_id . ' // '. $userid . ' // '.$username . ' // '.$firstname . ' // ' .$email . ' // '; 
    // $email = $_SESSION['email'];

    //increasing the wallet of the director....
	$directed_by = (isset($_POST['directed_by'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST["directed_by"])) : null;
	

	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";
	// require "update_token_non_users_functions.php";
	
	// $email = mysqli_real_escape_string($connect, clean_inputs($_POST['email']));
    $verify1 = rand (10, 99);
	$verify2 = rand (10, 99);
	$verify3 = rand (10, 99);
	$unique_input = $verify1 . $verify2 . $verify3;
	// $key = $_SESSION['key'];
    if(filter_var($email, FILTER_VALIDATE_EMAIL) && num_count_email($connect, $email) == 0){
        $updater_d_coins = $connect->prepare("UPDATE `users` SET `email` = ?, `unique_code` = ? WHERE `third_party_id` = ? AND `username` = ?;");
        // $updater_d_coins = $connect->prepare(" UPDATE `users` SET `email` = ? WHERE `users`.`third_party_id` = ?;");
       
        if( $updater_d_coins &&
            $updater_d_coins->bind_param("siss", $email, $unique_input, $third_party_id, $username) &&
            $updater_d_coins->execute()
            ){
            if($updater_d_coins){
                //echo "done and dusted";
                $data["status"] = true;
                $data["message"] = "update entered successfully";

                $mail_director =  "https://roynek.com/alltrenders/codes/backend/user_account_verification.php?user=".$unique_input."&key=gjhtiidsimi09403jfjkdknf";
                $to = $email;
                $subject = "The Roynek Grows Coin";
                
                $message = '
                <html>
                <head>
                <title> Verify Your Roynek Account </title>
                <link rel="stylesheet" href="roynek.com/stylesheets/mailer.css" />
                </head>
                <body style="font-family: "Lato", sans-serif;background-color:#00058A; color: #ffffff; font-size: 18px; text-align: center;">

                <h3><center> A1in1/Alltrenders </center></h3>
                <p> Welcome. Well done Roynekian for coming this far. We can see that you trust us and we have awarded you some Roynek Grows Coin. To verify your account please copy the text below into your browser and load it, if clicking the button does not work as is the case with some mail servers.
                <br/>
                
                <code><b> '.$mail_director.'</b></code>
                <br/>
                <br/>
                    
                    <a href="'.$mail_director.'" style="width: 80%; padding:11px; font-weight: bold; border-radius: 12px;text-align: center; background-color: #45de32;color: #ffffff;"> Verify </a>
                
                </p>
                
                <p> If this action was not initiated by you, please do ignore this mail. Best Regards</p>
                </body>
                </html>
                ';
                
                // Always set content-type when sending HTML email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                
                // More headers: verifier
                $headers .= 'From: <verifier@roynek.com>' . "\r\n";
                //$headers .= 'Cc: customerinfo_area@moneyminer.org' . "\r\n";
                
                mail($to,$subject,$message,$headers);
                // notifier_sign_up($to,$subject,$message,$headers);
                // notifier_sign_up($to,$subject,$content,$headers);
        
                // echo " \n Successful";
                $message_dir = 'You have been given fifty coins in Growse Ability for adding your email address. Check your inbox or spam to verify this email address.';
                notifier_inserter($userid, 0, 'profile', $message_dir, '', 0, $gotten_time, $gotten_date);
                coins_increase($userid, 150, $gotten_time, $gotten_date);
            
            }
        }else{
            $data["message"] = "issues with connecting to db.";
        }
    }else{
        $data["message"] = "Email already exist or invalid email.";
    }
	// if(filter_var($email, FILTER_VALIDATE_EMAIL) && num_count_email($connect, $email) == 0){
    //     $updater_d_coins = $connect->prepare("UPDATE `users` SET `email` = ?, `unique_code` = ? WHERE `third_party_id` = ?, `username` = ?, `firstname` = ?, `surname` = ?;");
    //     if( $updater_d_coins &&
    //         $updater_d_coins->bind_param("siisss", $email, $unique_input, $third_party_id, $username, $firstname, $lastname) &&
    //         $updater_d_coins->execute()
    //         ){
    //         if($updater_d_coins){
    //             //echo "done and dusted";
    //             $data["status"] = true;

    //             $mail_director =  "https://roynek.com/alltrenders/codes/backend/user_account_verification.php?user=".$unique_input."&key=gjhtiidsimi09403jfjkdknf";
    //             $to = $email;
    //             $subject = "Verification Process";
                
    //             $message = '
    //             <html>
    //             <head>
    //             <title> Verify Your Roynek Account </title>
    //             <link rel="stylesheet" href="roynek.com/stylesheets/mailer.css" />
    //             </head>
    //             <body style="font-family: "Lato", sans-serif;background-color:#00058A; color: #ffffff; font-size: 18px; text-align: center;">

    //             <h3><center> A1in1/Alltrenders </center></h3>
    //             <p> Welcome to Roynek.com. To verify your account please copy the text below into your browser-url and load it, if clicking the button does not work as is the case with some mail servers.
    //             <br/>
                
    //             <code><b> '.$mail_director.'</b></code>
    //             <br/>
    //             <br/>
                    
    //                 <a href="'.$mail_director.'" style="width: 80%; padding:11px; font-weight: bold; border-radius: 12px;text-align: center; background-color: #45de32;color: #ffffff;"> Verify </a>
                
    //             </p>
                
    //             <p> If this action was not initiated by you, please do ignore this mail</p>
    //             </body>
    //             </html>
    //             ';
                
    //             // Always set content-type when sending HTML email
    //             $headers = "MIME-Version: 1.0" . "\r\n";
    //             $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                
    //             // More headers: verifier
    //             $headers .= 'From: <verifier@roynek.com>' . "\r\n";
    //             //$headers .= 'Cc: customerinfo_area@moneyminer.org' . "\r\n";
                
    //             mail($to,$subject,$message,$headers);
    //             // notifier_sign_up($to,$subject,$message,$headers);
    //             // notifier_sign_up($to,$subject,$content,$headers);
        
    //             // echo " \n Successful";
    //             $message_dir = 'You have been given fifty coins in Growse Ability for adding your email address. Check your inbox or spam to verify this email address.';
    //             notifier_inserter($directed_by, 0, 'profile', $message_dir, '', 0, $gotten_time, $gotten_date);
    //             coins_increase($directed_by, 150, $gotten_time, $gotten_date);
            
    //         }
    //     }else{
    //         $data["message"] = "issues with connecting to db";
    //     }
    // }
}else{
    $data["message"] = "proper parameters not set or used...";
}

echo json_encode($data);

function clean_inputs($data){
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	$data = strip_tags($data);
	return $data;
}
?>
