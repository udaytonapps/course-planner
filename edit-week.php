<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$LAUNCH = LTIX::requireData();

$p = $CFG->dbprefix;

if (!$USER->instructor) {
    echo '<p>This tool is for instructors only.</p>';
    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Saving or updating a cell in the table
    $courseId = isset($_POST["course"]) ? $_POST["course"] : false;
    if (!$courseId) {
        $_SESSION["error"] = "Error saving content, course not set properly.";
        header('Location: ' . addSession('index.php'));
        return;
    }
    $weekNumber = isset($_POST["week"]) ? $_POST["week"] : false;
    if (!$weekNumber) {
        $_SESSION["error"] = "Error saving content, week not set properly.";
        header('Location: ' . addSession('edit.php?course='.$courseId));
        return;
    }

    $post_topics = isset($_POST["topics"]) ? $_POST["topics"] : "";
    $post_readings = isset($_POST["readings"]) ? $_POST["readings"] : "";
    $post_videos = isset($_POST["videos"]) ? $_POST["videos"] : "";
    $post_activities = isset($_POST["activities"]) ? $_POST["activities"] : "";
    $post_assignments = isset($_POST["assignments"]) ? $_POST["assignments"] : "";
    $post_exams = isset($_POST["exams"]) ? $_POST["exams"] : "";

    // Check for existing week row
    $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
    $weekStmt->execute(array(":course" => $courseId, ":weekNumber" => $weekNumber));
    $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);

    if (!$planWeek) {
        $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, topics, readings, videos, activities, assignments, exams) 
                            VALUES (:courseId, :weekNum, :topics, :readings, :videos, :activities, :assignments, :exams)");
        $newStmt->execute(array(
            ":courseId" => $courseId,
            ":weekNum" => $weekNumber,
            ":topics" => $post_topics,
            ":readings" => $post_readings,
            ":videos" => $post_videos,
            ":activities" => $post_activities,
            ":assignments" => $post_assignments,
            ":exams" => $post_exams
        ));
    } else {
        // Existing plan week record so run an update
        $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set 
                                        topics = :topics, readings = :readings, videos = :videos, activities = :activities, assignments = :assignments, exams = :exams
                                        WHERE course_id = :courseId AND weeknumber = :weekNum");
        $updateStmt->execute(array(
            ":courseId" => $courseId,
            ":weekNum" => $weekNumber,
            ":topics" => $post_topics,
            ":readings" => $post_readings,
            ":videos" => $post_videos,
            ":activities" => $post_activities,
            ":assignments" => $post_assignments,
            ":exams" => $post_exams
        ));
    }
    $_SESSION["success"] = "Course content saved successfully.";
    header('Location: ' . addSession('edit.php?course='.$courseId));
    return;
}

if (isset($_GET["course"])) {
    $course = $_GET["course"];
    // Get the title for the course
    $query = "SELECT title, term FROM {$p}course_planner_main WHERE course_id = :courseId;";
    $arr = array(':courseId' => $course);
    $courseData = $PDOX->rowDie($query, $arr);
    $courseTitle = $courseData ? $courseData["title"] : "";
    $courseTerm = $courseData ? (int) $courseData["term"] : 202080;
} else {
    $_SESSION["error"] = "Unable to edit course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
    return;
}

if (isset($_GET["week"]) && is_numeric($_GET["week"])) {
    $weekNum = $_GET["week"];
} else {
    $_SESSION["error"] = "Unable to edit week or undefined week number.";
    header('Location: ' . addSession('edit.php?course='.$course));
    return;
}

$weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
$weekStmt->execute(array(':course' => $course, ':weekNumber' => $weekNum));
$planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('Course Planner', 'index.php');
$menu->addRight('Exit Week Editor <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'edit.php?course='.$course);

$OUTPUT->header();
?>
    <link rel="stylesheet" href="css/planner.css" type="text/css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($courseTitle, false, false);
?>
<h3>Editing Week <?=$weekNum?>
<?php
$weekInfo = getWeekInfo($courseTerm, $weekNum);
if ($weekInfo) {
    echo ' <span class="text-muted" style="font-weight:500;">'.$weekInfo.'</span>';
}
?>
</h3>
<div class="row">
    <div class="col-sm-7">
        <form class="form edit-week" method="post">
            <input type="hidden" name="week" value="<?=$weekNum?>">
            <input type="hidden" name="course" value="<?=$course?>">
            <div class="form-group">
                <label for="editTopics">Topic(s)</label>
                <textarea id="editTopics" name="topics"><?=$planWeek ? $planWeek["topics"] : ""?></textarea>
            </div>
            <div class="form-group">
                <label for="editReadings">Readings</label>
                <textarea id="editReadings" name="readings"><?=$planWeek ? $planWeek["readings"] : ""?></textarea>
            </div>
            <div class="form-group">
                <label for="editVideos">Videos</label>
                <textarea id="editVideos" name="videos"><?=$planWeek ? $planWeek["videos"] : ""?></textarea>
            </div>
            <div class="form-group">
                <label for="editActivities">Activities</label>
                <textarea id="editActivities" name="activities"><?=$planWeek ? $planWeek["activities"] : ""?></textarea>
            </div>
            <div class="form-group">
                <label for="editAssignments">Assignments</label>
                <textarea id="editAssignments" name="assignments"><?=$planWeek ? $planWeek["assignments"] : ""?></textarea>
            </div>
            <div class="form-group">
                <label for="editExams">Test/Exams</label>
                <textarea id="editExams" name="exams"><?=$planWeek ? $planWeek["exams"] : ""?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Save</button> <a href="edit.php?course=<?=$course?>">Cancel</a>
        </form>
    </div>
</div>
<?php
echo '</div>';// end container

$OUTPUT->footerStart();
?>
    <script src="https://cdn.ckeditor.com/ckeditor5/16.0.0/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
            ClassicEditor
                .create( document.querySelector( '#editTopics' ), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'bold',
                            'italic',
                            'bulletedList',
                            'numberedList',
                            'link',
                            'blockQuote',
                            '|',
                            'indent',
                            'outdent',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en'
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editReadings' ), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'bold',
                            'italic',
                            'bulletedList',
                            'numberedList',
                            'link',
                            'blockQuote',
                            '|',
                            'indent',
                            'outdent',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en'
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editVideos' ), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'bold',
                            'italic',
                            'bulletedList',
                            'numberedList',
                            'link',
                            'blockQuote',
                            '|',
                            'indent',
                            'outdent',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en'
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editActivities' ), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'bold',
                            'italic',
                            'bulletedList',
                            'numberedList',
                            'link',
                            'blockQuote',
                            '|',
                            'indent',
                            'outdent',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en'
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editAssignments' ), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'bold',
                            'italic',
                            'bulletedList',
                            'numberedList',
                            'link',
                            'blockQuote',
                            '|',
                            'indent',
                            'outdent',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en'
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editExams' ), {
                    toolbar: {
                        items: [
                            'heading',
                            '|',
                            'fontSize',
                            'fontColor',
                            'fontBackgroundColor',
                            '|',
                            'bold',
                            'italic',
                            'bulletedList',
                            'numberedList',
                            'link',
                            'blockQuote',
                            '|',
                            'indent',
                            'outdent',
                            '|',
                            'undo',
                            'redo'
                        ]
                    },
                    language: 'en'
                } )
                .catch( error => {
                    console.error( error );
                } );
        });
    </script>
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
                    $weekInfo = '(1/19-1/24) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="MLK Day OFF, Mon, 1/18 / Classes start on Tues, 1/19"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
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
            }
            break;
        case 5:
            switch($term) {
                case 202080:
                    $weekInfo = '(9/21-9/27) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 9/23"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
                case 202110:
                    $weekInfo = '(2/15-2/21)';
                    break;
            }
            break;
        case 6:
            switch($term) {
                case 202080:
                    $weekInfo = '(9/28-10/4)';
                    break;
                case 202110:
                    $weekInfo = '(2/22-2/28) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Mini Break #1 - Tues, 2/23"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
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
            }
            break;
        case 9:
            switch($term) {
                case 202080:
                    $weekInfo = '(10/19-10/25) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 10/20"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
                case 202110:
                    $weekInfo = '(3/15-3/21)';
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
            }
            break;
        case 11:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/2-11/8)';
                    break;
                case 202110:
                    $weekInfo = '(3/29-4/4) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Good Friday Off, 4/2"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
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
            }
            break;
        case 13:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/16-11/22)';
                    break;
                case 202110:
                    $weekInfo = '(4/12-4/18) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Mini Break #2 - Wed, 4/14"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
            }
            break;
        case 14:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/23-11/29) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 11/25-11/27"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
                case 202110:
                    $weekInfo = '(4/19-4/25) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Stander Symposium, Thurs, 4/22"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
            }
            break;
        case 15:
            switch($term) {
                case 202080:
                    $weekInfo = '(11/30-12/6)';
                    break;
                case 202110:
                    $weekInfo = '(4/26-5/2) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Last Day of Classes - Fri, 4/30"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
            }
            break;
        case 16:
            switch($term) {
                case 202080:
                    $weekInfo = '(12/7-12/13) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 12/8"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
                case 202110:
                    $weekInfo = '(5/3-5/7) <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Finals Week (M-F)"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
                    break;
            }
            break;
    }
    return $weekInfo;
}