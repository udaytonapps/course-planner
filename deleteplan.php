<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if ( $USER->instructor ) {
    if (isset($_GET["course"])) {
        $course = $_GET["course"];

        $planqry = $PDOX->prepare("SELECT * FROM {$p}course_planner_main WHERE course_id = :course");
        $planqry->execute(array(":course" => $course));
        $plan = $planqry->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            $_SESSION["error"] = "Unable to locate course plan.";
            header("Location: " . addSession("index.php"));
            return;
        } else {
            // Found plan so check that it's the current user's and delete
            if ((strcmp($USER->id, $plan["user_id"]) == 0)) {
                $deleteQry = $PDOX->prepare("DELETE FROM {$p}course_planner_main WHERE course_id = :course_id");
                $deleteQry->execute(array(":course_id" => $course));

                // Success
                $_SESSION["success"] = "Course plan successfully deleted.";
                header("Location: " . addSession("index.php"));
                return;

            } else {
                $_SESSION["error"] = "You can only delete course plans that you've created.";
                header("Location: " . addSession("index.php"));
                return;
            }
        }
    } else {
        $_SESSION["error"] = "Unable to delete course plan. Invalid id.";
        header("Location: " . addSession("index.php"));
        return;
    }
} else {
    die("This tool is for instructors only.");
}

