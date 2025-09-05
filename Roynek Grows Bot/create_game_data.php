<?php
session_start();

require $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";

function get_the_time($userid){
    global $connect;
    // Retrieve game data from database
    $sql = "SELECT * FROM games WHERE creators_id = ? ORDER BY id DESC";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param('i', $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    $game = $result->fetch_assoc();
    $ret = true;

    if ($stmt->affected_rows > 0) {
        $gameData = json_decode($game['game_data'], true);
        $gameTime = strtotime($game['timestamp']);
        // $current_timestamp = date('Y-m-d H:i:s');
        $current_timestamp = time(); // Current timestamp in seconds since the Unix Epoch
        // echo $gameTime;
        // print(($current_timestamp - $gameTime));
        //
        if (($current_timestamp - $gameTime) <= 60) {
            $data["message"] = "The difference is less than or equal to 1 minute.";
            // echo "The difference is less than or equal to 1 minute.";
            $ret = false;
        } else {
            $data["message"] = "The difference is more than 1 minute.";
            // echo "The difference is more than 1 minute.";
            $ret = true;
        }
    }else{
        $ret = true;
        $data["message"] = "Could not connect to gaming db";
        // echo "Could not connect to gaming db";
    }

    return $ret;
}
// Function to generate random game data
function generateGameData() {
    $categories = ["star", "circle", "cross", "triangle", "whot"];
    $cards = [];
    $jokerIncluded = false;

    foreach ($categories as $category) {
        $count = rand(1, 10); // Random count between 1 and 10
        $cards[] = ["category" => $category, "count" => $count];
    }

    // Always include a joker
    $cards[] = ["category" => "joker", "count" => "any"];

    return $cards;
}

// Create new game data and store in database
function createGameData() {
    global $connect;
    $gameData = generateGameData();
    $gameData_stringed = json_encode($gameData);
    $randomcards = "CARDS RANDOM";
    $gameHash = md5(uniqid(rand(), true));
    $gameUnique = uniqid("game_", true);
    $creatorsId = $_SESSION['userid'];
    $coplayers_id = 0;
    $moderator_id = (isset($_POST['dev_id'])) ? $_POST['dev_id'] : 0;
    $addon = "";
    $gameStatus = 'active';
    $timestamp = date('Y-m-d H:i:s');

    //making use of the time...
    if (!get_the_time($creatorsId)){return ["status"=> false, "message"=>"Wait for a minute to start a new game." ];}

    //so the standard is that we now use 'Y-m-d H:i:s' for timestamp and h:i:sa for time
    // INSERT INTO `games` (`id`, `game_hash`, `game_unique`, `creators_id`, `coplayers_id`, `game_status`, `timestamp`) VALUES (NULL, 'REVERERV', 'RVEVRTRV', '0', '0', 'active', CURRENT_TIMESTAMP);
    // INSERT INTO `games` (`id`, `game_type`, `game_hash`, `game_unique`, `game_data`, `creators_id`, `coplayers_id`, `game_status`, `timestamp`) VALUES (NULL, 're', 'ree', 'trer', 'trre', '0', '0', 'trtrer', CURRENT_TIMESTAMP);
    $sql = "INSERT INTO games (`id`, `game_type`, `game_hash`, `game_unique`, `game_data`, `creators_id`, `coplayers_id`, `moderator`, `addon`, `game_status`, `timestamp`) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param('ssssdddsss', $randomcards, $gameHash, $gameUnique, $gameData_stringed, $creatorsId, $coplayers_id, $moderator_id, $addon ,$gameStatus, $timestamp);
    // $stmt->execute();
    
    if ($stmt->execute()) {
        return [
            "status" => true,
            "gameId" => $connect->insert_id,
            "gameData" => $gameData,
            "gameHash" => $gameHash,
            "gameUnique" => $gameUnique,
            "msg" => "We are good"
        ];
    }else{
        return [
            "status"=>false,
            "msg"=>"some issues with creating the game data"
        ];
    }
    
}

header('Content-Type: application/json');

// $data = array(
// 	'status' => false,
// 	'message' => "",
// 	'user'=>"",
// );

$d_ = (isset($_SESSION["userid"])) ? createGameData() : ["status"=>false, "msg"=>"No active session."] ;
echo json_encode($d_);

?>
