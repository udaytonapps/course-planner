<?php
require_once "../config.php";
require_once "./terms.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if (!$USER->instructor) {
    ?>
        <link rel="stylesheet" href="css/learner.css" type="text/css">
    <?php
    echo '<p class="learner-text">This tool is for instructors only.</p>';
    return;
}

if (isset($_GET["course"])) {
    $course = $_GET["course"];
    // Get the title for the course
    $query = "SELECT * FROM {$p}course_planner_main WHERE course_id = :courseId;";
    $arr = array(':courseId' => $course);
    $courseData = $PDOX->rowDie($query, $arr);
    $courseTitle = $courseData ? $courseData["title"] : "";
    $courseTerm = $courseData ? (int) $courseData["term"] : 202080;
} else {
    $_SESSION["error"] = "Unable to preview course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
    return;
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
    #theplan a[href] {
        text-decoration: underline;
    }
</style>
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();
$termTitle = getTerm($courseTerm);
?>
<h2>
    <a href="javascript:window.print();" class="print-icon pull-right btn btn-link">
        <span class="fas fa-print" aria-hidden="true"></span> Print
    </a>
    <small>Course Plan - <?= $termTitle ?></small><br /><?= $courseTitle ?>
</h2>
    <article id="theplan">
<?php
// Set the week count. Summer is special otherwise 16
// Summer: 202153 (1 - S1, 2 - S2, 3 - FT)
switch (intval($courseTerm)) {
    case 2021531:
    case 2021532:
    case 2022531:
    case 2022532:
        $weekCount = 6;
        break;
    case 2021533:
    case 2022533:
        $weekCount = 12;
        break;
    default:
        $weekCount = 16;
        break;
}
for ($weekNum = 1; $weekNum <= $weekCount; $weekNum++) {
    $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
    $weekStmt->execute(array(":course" => $course, ":weekNumber" => $weekNum));
    $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <hr />
    <h3>Week <?=$weekNum?> <?=getWeekInfo($courseTerm, $weekNum)?></h3>
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
    </article>
<?php
echo '</div>';// end container

$OUTPUT->footerStart();
?>

<?php
$OUTPUT->footerEnd();

function getWeekInfo($term, $weekNum) {
    $weekInfo = "";
    switch ($weekNum) {
        case 1:
            switch($term) {
                case 202080:
                    $weekInfo = '(8/24-8/30)';
                    break;
                case 202110:
                    $weekInfo = '(1/19-1/24) <small>MLK Day OFF, Mon, 1/18 / Classes start on Tues, 1/19</small>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = '(5/17 - 5/23)';
                    break;
                case 2021532:
                    $weekInfo = '(6/28 - 7/4)';
                    break;
                case 202180:
                    $weekInfo = '(8/23-8/29)';
                    break;
                case 202210:
                    $weekInfo = '(1/10-1/16)';
                    break;
                case 2022531:
                case 2022533:
                    $weekInfo = '(5/16 - 5/22)';
                    break;
                case 2022532:
                    $weekInfo = '(6/27 - 7/3)';
                    break;
                case 202280:
                    $weekInfo = '(8/22-8/28)';
                    break;
                case 202310:
                    $weekInfo = '(1/9-1/15)';
                    break;
            }
            break;
        case 2:
            switch($term) {
                case 202080:
                    $weekInfo = '(8/31-9/6)';
                    break;
                case 202110:
                    $weekInfo = '(1/25-1/31)';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = '(5/24 - 5/30)';
                    break;
                case 2021532:
                    $weekInfo = '(7/5-7/11) <small>No 7/5 in observance of Independence Day</small>';
                    break;
                case 202180:
                    $weekInfo = '(8/30-9/5)';
                    break;
                case 202210:
                    $weekInfo = '(1/17-1/23) <small>MLK Day, 1/17</small>';
                    break;
                case 2022531:
                case 2022533:
                    $weekInfo = '(5/23 - 5/29)';
                    break;
                case 2022532:
                    $weekInfo = '(7/4-7/10) <small>No class 7/5 in observance of Independence Day</small>';
                    break;
                case 202280:
                    $weekInfo = '(8/29-9/4)';
                    break;
                case 202310:
                    $weekInfo = '(1/16-1/22) <small>MLK Day, 1/16</small>';
                    break;
            }
            break;
        case 3:
            switch($term) {
                case 202080:
                    $weekInfo = '(9/7-9/13)';
                    break;
                case 202110:
                    $weekInfo = '(2/1-2/7)';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = '(5/31-6/6) <small>Memorial Day Off, 5/31</small>';
                    break;
                case 2021532:
                    $weekInfo = '(7/12 - 7/18)';
                    break;
                case 202180:
                    $weekInfo = '(9/6-9/12) <small>Labor Day, 9/6</small>';
                    break;
                case 202210:
                    $weekInfo = '(1/24-1/30)';
                    break;
                case 2022531:
                case 2022533:
                    $weekInfo = '(5/30-6/5) <small>Memorial Day Off, 5/30</small>';
                    break;
                case 2022532:
                    $weekInfo = '(7/11 - 7/17)';
                    break;
                case 202280:
                    $weekInfo = '(9/5-9/11) <small>Labor Day, 9/5</small>';
                    break;
                case 202310:
                    $weekInfo = '(1/23-1/29)';
                    break;
            }
            break;
        case 4:
            switch($term) {
                case 202080:
                    $weekInfo = '(9/14-9/20)';
                    break;
                case 202110:
                    $weekInfo = '(2/8-2/14)';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = '(6/7 - 6/13)';
                    break;
                case 2021532:
                    $weekInfo = '(7/19 - 7/25)';
                    break;
                case 202180:
                    $weekInfo = '(9/13-9/19)';
                    break;
                case 202210:
                    $weekInfo = '(1/31-2/6)';
                    break;
                case 2022531:
                case 2022533:
                    $weekInfo = '(6/6 - 6/12)';
                    break;
                case 2022532:
                    $weekInfo = '(7/18 - 7/24)';
                    break;
                case 202280:
                    $weekInfo = '(9/12-9/20)';
                    break;
                case 202310:
                    $weekInfo = '(1/30-2/5)';
                    break;
            }
            break;
        case 5:
            switch($term) {
                case 202080:
                    $weekInfo = '(9/21-9/27) <small>No classes 9/23</small>';
                    break;
                case 202110:
                    $weekInfo = '(2/15-2/21)';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = '(6/14 - 6/20)';
                    break;
                case 2021532:
                    $weekInfo = '(7/26 - 8/1)';
                    break;
                case 202180:
                    $weekInfo = '(9/20-9/26)';
                    break;
                case 202210:
                    $weekInfo = '(2/7-2/27)';
                    break;
                case 2022531:
                case 2022533:
                    $weekInfo = '(6/13 - 6/19)';
                    break;
                case 2022532:
                    $weekInfo = '(7/25 - 7/31)';
                    break;
                case 202280:
                    $weekInfo = '(9/19-9/25)';
                    break;
                case 202310:
                    $weekInfo = '(2/6-2/12)';
                    break;
            }
            break;
        case 6:
            switch($term) {
                case 202080:
                    $weekInfo = '(9/28-10/4)';
                    break;
                case 202110:
                    $weekInfo = '(2/22-2/28) <small>Mini Break #1 - Tues, 2/23</small>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = '(6/21 - 6/26)';
                    break;
                case 2021532:
                    $weekInfo = '(8/2 - 8/7)';
                    break;
                case 202180:
                    $weekInfo = '(9/27-10/3)';
                    break;
                case 202210:
                    $weekInfo = '(2/14-2/20)';
                    break;
                case 2022531:
                case 2022533:
                    $weekInfo = '(6/20-6/25) <small>No class 6/20 in observance of Juneteenth</small>';
                    break;
                case 2022532:
                    $weekInfo = '(8/1 - 8/6)';
                    break;
                case 202280:
                    $weekInfo = '(9/26-10/2)';
                    break;
                case 202310:
                    $weekInfo = '(2/13-2/19)';
                    break;
            }
            break;
        case 7:
            switch($term) {
                case 202080:
                    $weekInfo = '(10/5-10/11)';
                    break;
                case 202110:
                    $weekInfo = '(3/1-3/7)';
                    break;
                case 2021533:
                    $weekInfo = '(6/28 - 7/4)';
                    break;
                case 202180:
                    $weekInfo = '(10/4-10/10) <small>Mid-Term Break, 10/7-10/8</small>';
                    break;
                case 202210:
                    $weekInfo = '(2/21-2/27)';
                    break;
                case 2022533:
                    $weekInfo = '(6/27 - 7/3)';
                    break;
                case 202280:
                    $weekInfo = '(10/3-10/09)';
                    break;
                case 202310:
                    $weekInfo = '(2/20-2/26)';
                    break;
            }
            break;
        case 8:
            switch($term) {
                case 202080:
                    $weekInfo = '(10/12-10/18)';
                    break;
                case 202110:
                    $weekInfo = '(3/8-3/14)';
                    break;
                case 2021533:
                    $weekInfo = '(7/5-7/11) <small>No 7/5 in observance of Independence Day</small>';
                    break;
                case 202180:
                    $weekInfo = '(10/11-10/17)';
                    break;
                case 202210:
                    $weekInfo = '(2/28-3/6)';
                    break;
                case 2022533:
                    $weekInfo = '(7/4-7/10 <small>No class 7/4 in observance of Independence Day</small>';
                    break;
                case 202280:
                    $weekInfo = '(10/10-10/16)';
                    break;
                case 202310:
                    $weekInfo = '(2/27-3/5)';
                    break;
            }
            break;
        case 9:
            switch($term) {
                case 202080:
                    $weekInfo = '(10/19-10/25) <small>No classes 10/20</small>';
                    break;
                case 202110:
                    $weekInfo = '(3/15-3/21)';
                    break;
                case 2021533:
                    $weekInfo = '(7/12 - 7/18)';
                    break;
                case 202180:
                    $weekInfo = '(10/18-10/24)';
                    break;
                case 202210:
                    $weekInfo = '(3/7-3/13)';
                    break;
                case 2022533:
                    $weekInfo = '(7/11 - 7/17)';
                    break;
                case 202280:
                    $weekInfo = '(10/17-10/23) <small>Mid-Term Break, 10/17-10/18</small>';
                    break;
                case 202310:
                    $weekInfo = '(3/6-3/12) <small>Spring Break begins 3/11</small>';
                    break;
            }
            break;
        case 10:
            switch($term) {
                case 202080:
                    $weekInfo = '(10/26-11/1)';
                    break;
                case 202110:
                    $weekInfo = '(3/22-3/28)';
                    break;
                case 2021533:
                    $weekInfo = '(7/19 - 7/25)';
                    break;
                case 202180:
                    $weekInfo = '(10/25-10/31)';
                    break;
                case 202210:
                    $weekInfo = '(3/14-3/20) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Spring Break, 3/12-3/20"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
                case 2022533:
                    $weekInfo = '(7/18 - 7/24)';
                    break;
                case 202280:
                    $weekInfo = '(10/24-10/30)';
                    break;
                case 202310:
                    $weekInfo = '(3/13-3/12) <small>Spring Break ends 3/19</small>';
                    break;
            }
            break;
        case 11:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/2-11/8)';
                    break;
                case 202110:
                    $weekInfo = '(3/29-4/4) <small>Good Friday Off, 4/2</small>';
                    break;
                case 2021533:
                    $weekInfo = '(7/26 - 8/1)';
                    break;
                case 202180:
                    $weekInfo = '(11/1-11/7)';
                    break;
                case 202210:
                    $weekInfo = '(3/21-3/27)';
                    break;
                case 2022533:
                    $weekInfo = '(7/25 - 7/31)';
                    break;
                case 202280:
                    $weekInfo = '(10/31-11/6)';
                    break;
                case 202310:
                    $weekInfo = '(3/20-3/26)';
                    break;
            }
            break;
        case 12:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/9-11/15)';
                    break;
                case 202110:
                    $weekInfo = '(4/5-4/11)';
                    break;
                case 2021533:
                    $weekInfo = '(8/2 - 8/7)';
                    break;
                case 202180:
                    $weekInfo = '(11/8-11/14)';
                    break;
                case 202210:
                    $weekInfo = '(3/28-4/3)';
                    break;
                case 2022533:
                    $weekInfo = '(8/1 - 8/6)';
                    break;
                case 202280:
                    $weekInfo = '(11/7-11/13)';
                    break;
                case 202310:
                    $weekInfo = '(3/27-4/2)';
                    break;
            }
            break;
        case 13:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/16-11/22)';
                    break;
                case 202110:
                    $weekInfo = '(4/12-4/18) <small>Mini Break #2 - Wed, 4/14</small>';
                    break;
                case 202180:
                    $weekInfo = '(11/15-11/21)';
                    break;
                case 202210:
                    $weekInfo = '(4/4-4/10)';
                    break;
                case 202280:
                    $weekInfo = '(11/14-11/20)';
                    break;
                case 202310:
                    $weekInfo = '(4/3-4/9) <small>Easter, 4/6-4/10</small>';
                    break;
            }
            break;
        case 14:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/23-11/29) <small>No classes 11/25-11/27</small>';
                    break;
                case 202110:
                    $weekInfo = '(4/19-4/25) <small>Stander Symposium, Thurs, 4/22</small>';
                    break;
                case 202180:
                    $weekInfo = '(11/22-11/28) <small>Thanksgiving Break, 11/24-11/26</small>';
                    break;
                case 202210:
                    $weekInfo = '(4/11-4/17) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Easter, 4/14-4/18"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
                case 202280:
                    $weekInfo = '(11/21-11/27) <small>Thanksgiving Break, 11/23-11/25</small>';
                    break;
                case 202310:
                    $weekInfo = '(4/10-4/16)';
                    break;
            }
            break;
        case 15:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/30-12/6)';
                    break;
                case 202110:
                    $weekInfo = '(4/26-5/2) <small>Last Day of Classes - Fri, 4/30</small>';
                    break;
                case 202180:
                    $weekInfo = '(11/29-12/5)';
                    break;
                case 202210:
                    $weekInfo = '(4/18-4/24) <small>Stander Symposium, 4/20</small>';
                    break;
                case 202280:
                    $weekInfo = '(11/28-12/4)';
                    break;
                case 202310:
                    $weekInfo = '(4/17-4/23) <small>Stander Symposium, 4/19</small>';
                    break;
            }
            break;
        case 16:
            switch($term) {
                case 202080:
                    $weekInfo = '(12/7-12/13) <small>No classes 12/8</small>';
                    break;
                case 202110:
                    $weekInfo = '(5/3-5/7) <small>Finals Week (M-F)</small>';
                    break;
                case 202180:
                    $weekInfo = '(12/6-12/12) <small>Feast of Immaculate Conception, 12/8</small>';
                    break;
                case 202210:
                    $weekInfo = '(4/25-5/1)';
                    break;
                case 202280:
                    $weekInfo = '(12/5-12/11) <small>Feast of Immaculate Conception, 12/8</small>';
                    break;
                case 202310:
                    $weekInfo = '(4/24-4/28)';
                    break;
            }
            break;
    }
    return $weekInfo;
}