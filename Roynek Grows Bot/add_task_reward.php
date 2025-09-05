<?php
session_start();

require $_SERVER['DOCUMENT_ROOT']."/alltrenders/env_variables/accessor/accessor_main.php";
require $_SERVER['DOCUMENT_ROOT']."/alltrenders/codes/backend/coins.php";

$gotten_time = (isset($_POST['signup_time'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['signup_time'])) : null;
$gotten_date = (isset($_POST['signup_date'])) ? mysqli_real_escape_string($connect, clean_inputs($_POST['signup_date'])) : null;
$locals = local_time_getter(null, $gotten_time, $gotten_date);
$gotten_time = $locals['local_time'];
$gotten_date = $locals['local_date'];


header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['userid'];
    $task_hash = $_POST['task_hash'];

    if ( $user_id == 451 ) {
        $data["message"] = "Anonymous User.";
        exit;
    }

    // Predefined JSON of valid task hashes
    $valid_tasks = json_decode('["X_task", "You_task", "T_task", "W_task", "T_m_task", "Tik__task", "FB_task" "Other_task"]', true);
    $score = array("X_task"=>300, "You_task"=>250, "T_task"=>200, "W_task"=>200, "T_m_task"=>200, "Tik__task"=>150, "FB_task"=>200, "Other_task"=>'');

    // Check if task_hash is valid
    if (in_array($task_hash, $valid_tasks)) {
        $query = $connect->prepare("SELECT * FROM task WHERE user_id = ? AND task_unique = ?");
        $query->bind_param("is", $user_id, $task_hash);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => false, "message" => "You have claimed this task before."]);
        } else {
            // Insert the new task entry
            $insert_query = $connect->prepare("INSERT INTO task (user_id, task_unique, addon, timestamp) VALUES (?, ?, '', NOW())");
            $insert_query->bind_param("is", $user_id, $task_hash);

            if ($insert_query->execute()) {
                echo json_encode(["status" => true, "message" => "Task has been successfully recorded."]);
                coins_increase($user_id, $score[$task_hash], $gotten_time, $gotten_date);
            } else {
                echo json_encode(["status" => false, "message" => "Error recording the task."]);
            }
        }
    } else {
        echo json_encode(["status" => false, "message" => "Invalid task hash."]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Invalid request method."]);
}
?>
