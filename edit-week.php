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
    $post_discussions = isset($_POST["discussions"]) ? $_POST["discussions"] : "";

    // Check for existing week row
    $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
    $weekStmt->execute(array(":course" => $courseId, ":weekNumber" => $weekNumber));
    $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);

    if (!$planWeek) {
        $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, topics, readings, videos, activities, assignments, exams, discussions) 
                            VALUES (:courseId, :weekNum, :topics, :readings, :videos, :activities, :assignments, :exams, :discussions)");
        $newStmt->execute(array(
            ":courseId" => $courseId,
            ":weekNum" => $weekNumber,
            ":topics" => $post_topics,
            ":readings" => $post_readings,
            ":videos" => $post_videos,
            ":activities" => $post_activities,
            ":assignments" => $post_assignments,
            ":exams" => $post_exams,
            ":discussions" => $post_discussions
        ));
    } else {
        // Existing plan week record so run an update
        $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set 
                                        topics = :topics, readings = :readings, videos = :videos, activities = :activities, assignments = :assignments, exams = :exams, discussions = :discussions
                                        WHERE course_id = :courseId AND weeknumber = :weekNum");
        $updateStmt->execute(array(
            ":courseId" => $courseId,
            ":weekNum" => $weekNumber,
            ":topics" => $post_topics,
            ":readings" => $post_readings,
            ":videos" => $post_videos,
            ":activities" => $post_activities,
            ":assignments" => $post_assignments,
            ":exams" => $post_exams,
            ":discussions" => $post_discussions
        ));
    }
    $_SESSION["success"] = "Course content saved successfully.";
    header('Location: ' . addSession('edit.php?course='.$courseId));
}

if (isset($_GET["course"])) {
    $course = $_GET["course"];
    // Get the title for the course
    $query = "SELECT title FROM {$p}course_planner_main WHERE course_id = :courseId;";
    $arr = array(':courseId' => $course);
    $courseData = $PDOX->rowDie($query, $arr);
    $courseTitle = $courseData ? $courseData["title"] : "";
} else {
    $_SESSION["error"] = "Unable to edit course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
}

if (isset($_GET["week"]) && is_numeric($_GET["week"])) {
    $weekNum = $_GET["week"];
} else {
    $_SESSION["error"] = "Unable to edit week or undefined week number.";
    header('Location: ' . addSession('edit.php?course='.$course));
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
$weekInfo = getWeekInfo($weekNum);
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
            <div class="form-group">
                <label for="editDiscussions">Discussions</label>
                <textarea id="editDiscussions" name="discussions"><?=$planWeek ? $planWeek["discussions"] : ""?></textarea>
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
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editReadings' ), {
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editVideos' ), {
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editActivities' ), {
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editAssignments' ), {
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editExams' ), {
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
            ClassicEditor
                .create( document.querySelector( '#editDiscussions' ), {
                    removePlugins: ['Link'],
                    toolbar: [ 'heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote' ],
                    heading: {
                        options: [
                            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                            { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                        ]
                    }
                } )
                .catch( error => {
                    console.error( error );
                } );
        });
    </script>
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
            $weekInfo = '(9/21-9/27) <a href="#" data-toggle="tooltip" data-placement="top" title="No classes 9/23"><span class="fas fa-info-circle h5" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
            break;
        case 6:
            $weekInfo = '(9/28-10/4)';
            break;
        case 7:
            $weekInfo = '(10/5-10/11)';
            break;
        case 8:
            $weekInfo = '(10/12-10/18) <a href="#"  data-toggle="tooltip" data-placement="top" title="No classes 10/20"><span class="fas fa-info-circle h5" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
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
            $weekInfo = '(11/23-11/29) <a href="#"  data-toggle="tooltip" data-placement="top" title="No classes 11/25-11/27"><span class="fas fa-info-circle h5" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
            break;
        case 15:
            $weekInfo = '(11/30-12/6)';
            break;
        case 16:
            $weekInfo = '(12/7-12/13) <a href="#"  data-toggle="tooltip" data-placement="top" title="No classes 12/8"><span class="fas fa-info-circle h5" aria-hidden="true"></span><span class="sr-only">Information</span></a>';
            break;
    }
    return $weekInfo;
}