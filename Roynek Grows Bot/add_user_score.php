<?php
session_start();

header('Content-Type: application/json');

require $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/notifier.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/coins.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/date_machine.php";


$userId = $_SESSION['userid'];
$gameId = $_POST['game_id'];
$proposed_score = $_POST['score'];
$userTaps = json_decode($_POST['user_taps'], true);

$gotten_time = (isset($_POST['signup_time'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['signup_time'])) : null;
$gotten_date = (isset($_POST['signup_date'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['signup_date'])) : null;
$locals = local_time_getter(null, $gotten_time, $gotten_date);
$gotten_time = $locals['local_time'];
$gotten_date = $locals['local_date'];


$highest_score = 100;
$data = array(
	'status' => false,
	'message' => "Nothing processed...",
	'user'=>"",
);

if ( $userId == 451 ) {
    $data["message"] = "Anonymous User.";
    $data["status"] = true;
    echo json_encode($data);

    exit;
}


// Retrieve game data from database
$sql = "SELECT * FROM games WHERE id = ? AND game_status = 'active'";
$stmt = $connect->prepare($sql);
$stmt->bind_param('i', $gameId);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();

if (!$game) {
    return false;
    $data["message"] = "Could not connect to gaming db";
}else{
    $gameData = json_decode($game['game_data'], true);
    $gameTime = strtotime($game['timestamp']);
    $current_timestamp = date('Y-m-d H:i:s');

    $correct = validateScore($gameData, $userTaps) ;
    // echo json_encode($gameData);
    // echo json_encode($userTaps);
    // echo $proposed_score . '<br/>';
    // echo $correct;

    $sql = "UPDATE games SET game_status = 'inactive' WHERE id = ? AND game_status = 'active'";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param('i', $gameId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $data["message"] = "Game status updated successfully.";
        $current_timestamp = time(); // Current timestamp in seconds since the Unix Epoch


        // we would check to see how long the game was played...
        if (($current_timestamp - $gameTime) <= 5) {
            $data["message"] = "The difference is less than or equal to 10 seconds.";
            echo json_encode($data);

            exit;
        } else {
            $data["message"] .= "The difference is more than 10 seconds.";
        }

        if ($correct > $highest_score){
            $data["messagee"] = "score just too high";
            exit;
        }

        if ($correct) {
            $data["message"] .= "all data gotten";
            $data["status"] = true;
            // notifier_inserter($userId, 0, 'profile', $message_dir, '', 0, $gotten_time, $gotten_date);
            coins_increase($userId, $correct, $gotten_time, $gotten_date);
        }else{
            $data["message"] .= "Could not validate score";
        }


    } else {
        $data["message"] = "No active game found with the given ID.";
    }
    
}

// function validateScore($gameData, $userTaps) {
//     foreach ($userTaps as $index => $tapData) {
//         if ($tapData['category'] === "whot" || $tapData['category'] === "joker") {
//             continue;
//         }
//         if ($tapData['taps'] > $gameData[$index]['count']) {
//             $correct = false;
//             break;
//         }
//     }
    
//     return false;
// }

function validateScore($gameData, $userTaps) {
    $score = 0;

    foreach ($userTaps as $index => $tapData) {
        $category = $tapData['category'];
        $taps = $tapData['taps'];
        $gameCount = $gameData[$index]['count'];

        if ($category === "whot" && $taps <= 20) {
            $score += $taps;
        } elseif ($category === "joker" && $taps <= 25) {
            $score += $taps;
        } elseif ($category === $gameData[$index]['category']) {
            if ($taps <= $gameCount) {
                $score += $taps;
            } else {
                $score -= 1; // Penalize for exceeding the count
            }
        }
    }

    return $score;
}

echo json_encode($data);

function generateHash() {
    return bin2hex(random_bytes(16)); // Generate a 32 character hash
}

?>
