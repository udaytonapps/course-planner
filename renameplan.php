<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $_SESSION["error"] = "Unable to update course plan. Invalid request method.";
    header("Location: " . addSession("index.php"));
}

if ( $USER->instructor ) {
    if (isset($_POST["course"])) {
        $course = $_POST["course"];

        $planqry = $PDOX->prepare("SELECT * FROM {$p}course_planner_main WHERE course_id = :course");
        $planqry->execute(array(":course" => $course));
        $plan = $planqry->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            $_SESSION["error"] = "Unable to locate course plan.";
            header("Location: " . addSession("index.php"));
        } else {
            // Found plan so check that it's the current user's and delete
            if ((strcmp($USER->id, $plan["user_id"]) == 0)) {
                $termInt = is_numeric($_POST["term"]) ? (int) $_POST["term"] : 202080;
                $editQry = $PDOX->prepare("UPDATE {$p}course_planner_main SET title = :title, term = :term WHERE course_id = :course_id");
                $editQry->execute(array(":course_id" => $course, ":title" => $_POST["title"], ":term" => $termInt));

                // Success
                $_SESSION["success"] = "Course plan updated successfully.";

                if (isset($_POST["back"]) && $_POST["back"] == 'edit') {
                    header("Location: " . addSession("edit.php?course=".$course));
                } else {
                    header("Location: " . addSession("index.php"));
                }
            } else {
                $_SESSION["error"] = "You can only update course plans that you've created.";
                header("Location: " . addSession("index.php"));
            }
        }
    } else {
        $_SESSION["error"] = "Unable to update course plan. Invalid id.";
        header("Location: " . addSession("index.php"));
    }
} else {
    die("This tool is for instructors only.");
}

