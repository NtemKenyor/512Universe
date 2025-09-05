<?php
session_start();

// Check if data is received through POST
$data = array(
	'status' => false,
	'message' => "",
	'user'=>"",
);

if (isset($_POST['tele_id']) and isset($_SESSION['userid'])) {
	include $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/get_users.php";
	require $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";

    $user_id = $_SESSION['userid'];
	$tele_id = mysqli_real_escape_string($connect, clean_inputs($_POST['tele_id']));
    // $unique_code = (isset($_POST['tele_id'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['code'])) : null;
	#$password = clean_inputs($_POST["password"]);
	// $user_info = user_info_third_party_id($connect, $tele_id);
    // $user_info = user_info_third_pid_unique($connect, $tele_id, $unique_code);

    $user_info = user_info_third_pid_userid($connect, $tele_id, $user_id);
    
    if ($user_info != array() ){
        $data["status"] = true;
        $data["user"] = $user_info;
        
    }else{
        $data["message"] = "could not find this user";
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
    
?>