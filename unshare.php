<?php
require_once "../../config.php";

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if ( $USER->instructor ) {
    if (isset($_GET["course"]) && isset($_GET["email"])) {
        $course = $_GET["course"];

        $sharedplanqry = $PDOX->prepare("SELECT m.course_id as course_id, m.user_id as creator_id, s.user_email as shared_email FROM
                                                        {$p}course_planner_share s join {$p}course_planner_main m on s.course_id = m.course_id
                                                        WHERE s.user_email = :email AND m.course_id = :course");
        $sharedplanqry->execute(array(":email" => $_GET["email"], ":course" => $course));
        $shared_plan = $sharedplanqry->fetch(PDO::FETCH_ASSOC);

        if (!$shared_plan) {
            $_SESSION["error"] = "Unable to locate share record for plan.";
            header("Location: " . addSession("index.php"));
        } else {
            // Found share record so remove if is current user is creator or current user matches email
            if ((strcmp($USER->email, $shared_plan["shared_email"]) == 0) || (strcmp($shared_plan["creator_id"], $USER->id) == 0)) {
                $deleteQry = $PDOX->prepare("DELETE FROM {$p}course_planner_share WHERE course_id = :course_id AND user_email = :user_email");
                $deleteQry->execute(array(":user_email" => $_GET["email"], ":course_id" => $course));

                // Success
                $_SESSION["success"] = "Access removed for ".$_GET["email"]." successfully.";
                if (isset($_GET["back2"]) && $_GET["back2"] == "share") {

                    $back = isset($_GET["back"]) && $_GET["back"] == 'edit' ? "edit" : "index";

                    header("Location: " . addSession("share.php?course=".$course."&back=".$back));
                } else {
                    header("Location: " . addSession("index.php"));
                }
            } else {
                $_SESSION["error"] = "You can only remove your own access or access to plans you've created.";
                header("Location: " . addSession("index.php"));
            }
        }
    } else {
        $_SESSION["error"] = "Unable to remove sharing for course plan. Invalid id or email.";
        header("Location: " . addSession("index.php"));
    }
} else {
    die("This tool is for instructors only.");
}

