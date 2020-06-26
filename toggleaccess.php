<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if (!$USER->instructor) {
    echo '<p>This tool is for instructors only.</p>';
    return;
}

if (isset($_GET["course"]) && isset($_GET["email"])) {
    $course = $_GET["course"];
    $email = $_GET["email"];
    $canEdit = isset($_GET["access"]) && (strcmp($_GET["access"], "edit") == 0) ? 1 : 0;

    $updateQry = $PDOX->prepare("UPDATE {$p}course_planner_share SET can_edit = :can_edit WHERE course_id = :course_id AND user_email = :user_email");
    $updateQry->execute(array(":can_edit" => $canEdit, ":course_id" => $course, ":user_email" => $email));

    if ($canEdit == 1) {
        $_SESSION["success"] = $email . " can now edit this course plan.";
    } else {
        $_SESSION["success"] = $email . "'s access has been set to read-only for this course plan.";
    }

    $back = isset($_GET["back"]) && $_GET["back"] == 'edit' ? "edit" : "index";

    header("Location: " . addSession("share.php?course=".$course."&back=".$back));

} else {
    $_SESSION["error"] = "Unable to remove sharing for course plan. Invalid id or email.";
    header("Location: " . addSession("index.php"));
}

