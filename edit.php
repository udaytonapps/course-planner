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
        header('Location: ' . addSession('edit.php'));
        return;
    }
    $weekNumber = isset($_POST["week"]) ? $_POST["week"] : false;
    if (!$weekNumber) {
        $_SESSION["error"] = "Error saving content, week not set properly.";
        header('Location: ' . addSession('edit.php?course='.$courseId));
        return;
    }
    $contentType = isset($_POST["contenttype"]) ? $_POST["contenttype"] : false;
    if (!$contentType) {
        $_SESSION["error"] = "Error saving content, content type not set properly.";
        header('Location: ' . addSession('edit.php?course='.$courseId));
        return;
    }
    $content = isset($_POST["content"]) ? $_POST["content"] : "";
    // Check for existing week row
    $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
    $weekStmt->execute(array(":course" => $courseId, ":weekNumber" => $weekNumber));
    $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
    if (!$planWeek) {
        // No existing row so insert instead of update
        switch ($contentType) {
            case "Topic(s)":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, topics) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Readings":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, readings) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Videos":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, videos) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Activities":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, activities) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Assignments":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, assignments) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Tests/Exams":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, exams) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Discussions":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (course_id, weeknumber, discussions) 
                            VALUES (:courseId, :weekNum, :content)");
                $newStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
        }
    } else {
        // Existing plan week record so run an update
        switch ($contentType) {
            case "Topic(s)":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set topics = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Readings":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set readings = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Videos":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set videos = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Activities":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set activities = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Assignments":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set assignments = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Tests/Exams":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set exams = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Discussions":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set discussions = :content WHERE course_id = :courseId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":courseId" => $courseId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
        }
    }
    $_SESSION["success"] = "Course content saved successfully.";
    header('Location: ' . addSession('edit.php?course='.$courseId));
}

if (isset($_GET["course"])) {
    $course = $_GET["course"];
    // Get the title for the course
    $query = "SELECT * FROM {$p}course_planner_main WHERE course_id = :courseId;";
    $arr = array(':courseId' => $course);
    $courseData = $PDOX->rowDie($query, $arr);
    $courseTitle = $courseData ? $courseData["title"] : "";
} else {
    $_SESSION["error"] = "Unable to edit course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
}

$sharestmt = $PDOX->prepare("SELECT count(*) as total FROM {$p}course_planner_share WHERE course_id = :course_id");
$sharestmt->execute(array(":course_id" => $course));
$sharecount = $sharestmt->fetch(PDO::FETCH_ASSOC);

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('My Course Planner', 'index.php');
$menu->addRight('Exit Plan Editor <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'index.php');

$OUTPUT->header();
?>
<link rel="stylesheet" href="css/planner.css" type="text/css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();
?>
    <div id="toolTitle" class="h1">
        <div class="h3 inline pull-right">
            <?php
            if ($courseData["user_id"] == $USER->id) {
                // My course plan so show full menu
                ?>
                <a href="#" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                <a href="share.php?course=<?=$course?>&back=edit" class="plan-link" title="Share"><span class="fas fa-user-plus" aria-hidden="true"></span><span class="sr-only">Share</span></a>
                <a href="#renameModal" data-toggle="modal" class="plan-link" title="Rename"><span class="fas fa-pencil-alt" aria-hidden="true"></span><span class="sr-only">Rename</span></a>
                <a href="deleteplan.php?course=<?=$course?>" onclick="return confirm('Are you sure you want to delete this course plan? Deleting a course plan also deletes it for everyone it was shared with. This can not be undone.');" class="plan-link" title="Delete"><span class="far fa-trash-alt" aria-hidden="true"></span><span class="sr-only">Delete</span></a>
                <?php
            } else {
                // Shared with me so show smaller menu
                ?>
                <a href="#" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                <a href="unshare.php?course=<?=$course?>&email=<?=$USER->email?>" onclick="return confirm('Are you sure you want to remove your access to this plan. The creator of the plan will need to grant you access to undo this action.');" class="plan-link" title="Remove from my list"><span class="fas fa-user-slash" aria-hidden="true"></span><span class="sr-only">Remove from my list</span></a>
                <?php
            }
            ?>
        </div>
        <span class="title-text-span"><?= $courseTitle ?></span>
        <?php
        if ($sharecount["total"] > 1) {
            echo '<span class="text-muted" data-toggle="tooltip" title="Shared with multiple people"><span class="fas fa-users fa-fw" aria-hidden="true"></span></span>';
        } else if ($sharecount["total"] > 0) {
            echo '<span class="text-muted" data-toggle="tooltip" title="Shared with one other person"><span class="fas fa-user-friends fa-fw" aria-hidden="true"></span></span>';
        }
        ?>
    </div>
    <p>Click on a table cell to edit the content or click on a week header to edit all of the content for that week.</p>
    <div class="table-responsive">
    <table class="table table-bordered table-condensed">
        <thead>
        <tr>
            <th>Weeks</th>
            <th>Topic(s)</th>
            <th>Readings</th>
            <th>Videos</th>
            <th>Activities</th>
            <th>Assignments</th>
            <th>Tests/Exams</th>
            <th>Discussions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($weekNum = 1; $weekNum <= 16; $weekNum++) {
            $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
            $weekStmt->execute(array(":course" => $course, ":weekNumber" => $weekNum));
            $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
            echo '<tr>';
            echo'<th data-week="'.$weekNum.'">'.getWeekInfo($weekNum).'</th>';
            ?>
            <td data-week="<?=$weekNum?>" data-contenttype="Topic(s)" <?=$planWeek && !empty($planWeek["topics"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["topics"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["topics"] : ""?></textarea>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Readings" <?=$planWeek && !empty($planWeek["readings"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["readings"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["readings"] : ""?></textarea>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Videos" <?=$planWeek && !empty($planWeek["videos"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["videos"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["videos"] : ""?></textarea>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Activities" <?=$planWeek && !empty($planWeek["activities"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["activities"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["activities"] : ""?></textarea>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Assignments" <?=$planWeek && !empty($planWeek["assignments"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["assignments"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["assignments"] : ""?></textarea>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Tests/Exams" <?=$planWeek && !empty($planWeek["exams"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["exams"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["exams"] : ""?></textarea>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Discussions" <?=$planWeek && !empty($planWeek["discussions"]) ? 'class="hasContent"' : ''?>>
                <span><?=$planWeek ? strip_tags($planWeek["discussions"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["discussions"] : ""?></textarea>
            </td>
            <?php
            echo '</tr>';
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th>Weeks</th>
            <th>Topic(s)</th>
            <th>Readings</th>
            <th>Videos</th>
            <th>Activities</th>
            <th>Assignments</th>
            <th>Tests/Exams</th>
            <th>Discussions</th>
        </tr>
        </tfoot>
    </table>
</div>
    <div id="editModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Editing <span id="editHeader"></span></h4>
                </div>
                <div class="modal-body">
                    <p>Add quick details to this box about each item in this category for this week. Keep it brief and do not add full details or instructions for this content (e.g. Group Assignment 1, Dog Lecture Video, Quiz 2 on Pizza, etc.).</p>
                    <form class="form" method="post">
                        <input type="hidden" name="course" value="<?=$course?>">
                        <input type="hidden" id="editWeek" name="week" value="">
                        <input type="hidden" id="editContentType" name="contenttype" value="">
                        <div class="form-group">
                            <label for="editContent" id="editContentLabel"></label>
                            <textarea class="form-control" rows="5" id="editContent" name="content"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button> <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <div id="renameModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Rename <?=$courseTitle?></h4>
                </div>
                <div class="modal-body">
                    <form class="form" method="post" action="renameplan.php">
                        <input type="hidden" name="course" value="<?=$course?>">
                        <input type="hidden" name="back" value="edit">
                        <div class="form-group">
                            <label for="planTitle" id="planTitleLabel">Course Plan Title</label>
                            <input type="text" class="form-control" name="title" id="planTitle" value="<?=$courseTitle?>" placeholder="e.g. TST 100 (Fall 2020)" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button> <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
echo '</div>';// end container

$OUTPUT->footerStart();
?>
    <script src="https://cdn.ckeditor.com/ckeditor5/16.0.0/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function(){
            let theEditor;
            ClassicEditor
                .create( document.querySelector( '#editContent' ), {
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
                .then (editor => { theEditor = editor; })
                .catch( error => {
                    console.error( error );
                } );
            $('[data-toggle="tooltip"]').tooltip();
            $("td").off("click").on("click", function() {


                let week = $(this).data("week");
                let contenttype = $(this).data("contenttype");
                let content = $(this).find("textarea.content").val();
                let contentlabel = "Week " + week + " - " + contenttype;

                $("#editWeek").val(week);
                $("#editContentType").val(contenttype);
                theEditor.setData(content);

                $("#editHeader").text(contentlabel);
                $("#editContentLabel").text(contentlabel);

                $("#editModal").modal("show");
            });
            $("th").off("click").on("click", function() {
                // navigate to edit week
                let week = $(this).data("week");
                window.location.href = 'edit-week.php?course=<?=$course?>&week='+week+'&PHPSESSID=<?=$_GET["PHPSESSID"]?>'
            });
        });
    </script>
<?php
$OUTPUT->footerEnd();

function getWeekInfo($weekNum) {
    $weekInfo = "";
    switch ($weekNum) {
        case 1:
            $weekInfo = 'Week 1<br />(8/24-8/30)';
            break;
        case 2:
            $weekInfo = 'Week 2<br />(8/31-9/6)';
            break;
        case 3:
            $weekInfo = 'Week 3<br />(9/7-9/13)';
            break;
        case 4:
            $weekInfo = 'Week 4<br />(9/14-9/20)';
            break;
        case 5:
            $weekInfo = 'Week 5 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 9/23"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                <br />(9/21-9/27)';
            break;
        case 6:
            $weekInfo = 'Week 6<br />(9/28-10/4)';
            break;
        case 7:
            $weekInfo = 'Week 7<br />(10/5-10/11)';
            break;
        case 8:
            $weekInfo = 'Week 8 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 10/20"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                <br />(10/12-10/18)';
            break;
        case 9:
            $weekInfo = 'Week 9<br />(10/19-10/25)';
            break;
        case 10:
            $weekInfo = 'Week 10<br />(10/26-11/1)';
            break;
        case 11:
            $weekInfo = 'Week 11<br />(11/2-11/8)';
            break;
        case 12:
            $weekInfo = 'Week 12<br />(11/9-11/15)';
            break;
        case 13:
            $weekInfo = 'Week 13<br />(11/16-11/22)';
            break;
        case 14:
            $weekInfo = 'Week 14 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 11/25-11/27"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                <br />(11/23-11/29)';
            break;
        case 15:
            $weekInfo = 'Week 15<br />(11/30-12/6)';
            break;
        case 16:
            $weekInfo = 'Week 16 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 12/8"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                <br />(12/7-12/13)';
            break;
    }
    return $weekInfo;
}