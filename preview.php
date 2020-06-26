<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if (!$USER->instructor) {
    echo '<p>This tool is for instructors only.</p>';
    return;
}

if (isset($_GET["course"])) {
    $course = $_GET["course"];
    // Get the title for the course
    $query = "SELECT * FROM {$p}course_planner_main WHERE course_id = :courseId;";
    $arr = array(':courseId' => $course);
    $courseData = $PDOX->rowDie($query, $arr);
    $courseTitle = $courseData ? $courseData["title"] : "";
} else {
    $_SESSION["error"] = "Unable to preview course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
}

$back = isset($_GET["back"]) && $_GET["back"] == 'edit' ? "edit" : "index";

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('Course Planner', 'index.php');
if ($back == "index") {
    $menu->addRight('Exit Plan Preview <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'index.php');
} else {
    $menu->addRight('Exit Plan Preview <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'edit.php?course='.$course);
}

$OUTPUT->header();
?>
    <link rel="stylesheet" href="css/planner.css" type="text/css">
    <link rel="stylesheet" href="css/print.css" media="print" type="text/css">
<style>
    body {
        font-size: 11pt;
    }
    h1, h2 {
        line-height: 1.2em;
        font-weight: 400;
    }
    h3 {
        font-weight: 500;
    }
    h4 {
        font-weight: 600;
    }
    h1 > small, h2 > small, h3 > small, h4 > small {
        font-weight: normal;
    }
</style>
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();
?>
<h2>
    <a href="javascript:window.print();" class="print-icon pull-right btn btn-link">
        <span class="fas fa-print" aria-hidden="true"></span> Print
    </a>
    <small>Course Plan</small><br /><?= $courseTitle ?>
</h2>
<?php
for ($weekNum = 1; $weekNum <= 16; $weekNum++) {
    $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
    $weekStmt->execute(array(":course" => $course, ":weekNumber" => $weekNum));
    $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <hr />
    <h3>Week <?=$weekNum?> <?=getWeekInfo($weekNum)?></h3>
    <h4>Topic(s)</h4>
    <div><?=$planWeek ? $planWeek["topics"] : ""?></div>
    <h4>Readings</h4>
    <div><?=$planWeek ? $planWeek["readings"] : ""?></div>
    <h4>Videos</h4>
    <div><?=$planWeek ? $planWeek["videos"] : ""?></div>
    <h4>Activities</h4>
    <div><?=$planWeek ? $planWeek["activities"] : ""?></div>
    <h4>Assignments</h4>
    <div><?=$planWeek ? $planWeek["assignments"] : ""?></div>
    <h4>Tests/Exams</h4>
    <div><?=$planWeek ? $planWeek["exams"] : ""?></div>
    <?php
}
?>
<?php
echo '</div>';// end container

$OUTPUT->footerStart();
?>

<?php
$OUTPUT->footerEnd();

function getWeekInfo($weekNum) {
    $weekInfo = "";
    switch ($weekNum) {
        case 1:
            $weekInfo = '(8/24-8/30)';
            break;
        case 2:
            $weekInfo = '(8/31-9/6)';
            break;
        case 3:
            $weekInfo = '(9/7-9/13)';
            break;
        case 4:
            $weekInfo = '(9/14-9/20)';
            break;
        case 5:
            $weekInfo = '(9/21-9/27) <small>No classes 9/23</small>';
            break;
        case 6:
            $weekInfo = '(9/28-10/4)';
            break;
        case 7:
            $weekInfo = '(10/5-10/11)';
            break;
        case 8:
            $weekInfo = '(10/12-10/18) <small>No classes 10/20</small>';
            break;
        case 9:
            $weekInfo = '(10/19-10/25)';
            break;
        case 10:
            $weekInfo = '(10/26-11/1)';
            break;
        case 11:
            $weekInfo = '(11/2-11/8)';
            break;
        case 12:
            $weekInfo = '(11/9-11/15)';
            break;
        case 13:
            $weekInfo = '(11/16-11/22)';
            break;
        case 14:
            $weekInfo = '(11/23-11/29) <small>No classes 11/25-11/27</small>';
            break;
        case 15:
            $weekInfo = '(11/30-12/6)';
            break;
        case 16:
            $weekInfo = '(12/7-12/13) <small>No classes 12/8</small>';
            break;
    }
    return $weekInfo;
}