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
            // Found plan so first copy main
            $copyMainQry = $PDOX->prepare("INSERT INTO {$p}course_planner_main (user_id, title, term) VALUES (:user_id, :title, :term)");
            $copyMainQry->execute(array(":user_id" => $USER->id, ":title" => 'COPY - '.$plan["title"], ":term" => $plan["term"]));
            $newPlanId = $PDOX->lastInsertId();

            // Set the week count. Summer is special otherwise 16
            // Summer: 202153 (1 - S1, 2 - S2, 3 - FT)
            switch (intval($plan["term"])) {
                case 2021531:
                case 2021532:
                    $weekCount = 6;
                    break;
                case 2021533:
                    $weekCount = 12;
                    break;
                default:
                    $weekCount = 16;
                    break;
            }

            // Now copy all of the weeks
            for ($weekNum = 1; $weekNum <= $weekCount; $weekNum++) {
                $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
                $weekStmt->execute(array(":course" => $plan["course_id"], ":weekNumber" => $weekNum));
                $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
                if ($planWeek){
                    $copyWeekQry = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, topics, readings, videos, activities, assignments, exams, last_modified) 
                        VALUES (:course_id, :weeknumber, :topics, :readings, :videos, :activities, :assignments, :exams, :last_modified)");
                    $copyWeekQry->execute(array(
                        ":course_id" => $newPlanId,
                        ":weeknumber" => $planWeek["weeknumber"],
                        ":topics" => $planWeek["topics"],
                        ":readings" => $planWeek["readings"],
                        ":videos" => $planWeek["videos"],
                        ":activities" => $planWeek["activities"],
                        ":assignments" => $planWeek["assignments"],
                        ":exams" => $planWeek["exams"],
                        ":last_modified" => $planWeek["last_modified"]
                    ));
                }
            }

            // Success
            $_SESSION["success"] = "Course plan successfully copied.";
            header("Location: " . addSession("index.php"));
            return;
        }
    } else {
        $_SESSION["error"] = "Unable to copy course plan. Invalid id.";
        header("Location: " . addSession("index.php"));
        return;
    }
} else {
    die("This tool is for instructors only.");
}

