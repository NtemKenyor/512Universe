<?php
session_start();
header('Content-Type: application/json');

include $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";

function generateHash() {
    return bin2hex(random_bytes(16)); // Generate a 32 character hash
}

$hash = isset($_POST['hash']) ? $_POST['hash'] : null;
$telegramId = isset($_POST['tele_id']) ? $_POST['tele_id'] : null;
$username = isset($_POST['username']) ? $_POST['username'] : null;

if (!$hash || !$telegramId || !$username) {
    echo json_encode(['status' => false, 'message' => 'Missing parameters.']);
    exit;
}

// Verify the hash and login the user
$query = "SELECT * FROM `users` WHERE `third_party_id` = ? AND `addon` = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("ss", $telegramId, $hash);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userid = $row['id'];
    $cryptpass = $row['unique_code'];
    
    // User verified, create session and update hash
    $_SESSION['userid'] = $userid;
    $_SESSION['uname'] = $username;
    $_SESSION['tele_id'] = $telegramId;
    
    ob_start();
    setcookie('cryptpass', $cryptpass, time()+500*24*60*60*1000, '/');
    // time() + 30 * 24 * 60 * 60, // for 1 month...
    setcookie('userid', $userid, time()+500*24*60*60*1000, '/'); 	
    setcookie('uname', $username, time()+500*24*60*60*1000, '/'); 	
    
    $newHash = generateHash();
    $updateQuery = "UPDATE `users` SET `addon` = ? WHERE `third_party_id` = ?";
    $updateStmt = $connect->prepare($updateQuery);
    $updateStmt->bind_param("ss", $newHash, $telegramId);
    if ($updateStmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Login successful.', 'user' => $row , 'session_id' => session_id()]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to update user hash.']);
    }
    $updateStmt->close();
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid hash or Telegram ID.']);
}

$stmt->close();
$connect->close();
?>

