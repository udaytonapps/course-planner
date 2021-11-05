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
        }
    }
    $_SESSION["success"] = "Course content saved successfully.";
    header('Location: ' . addSession('edit.php?course='.$courseId));
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
    $_SESSION["error"] = "Unable to edit course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
    return;
}

$sharestmt = $PDOX->prepare("SELECT count(*) as total FROM {$p}course_planner_share WHERE course_id = :course_id");
$sharestmt->execute(array(":course_id" => $course));
$sharecount = $sharestmt->fetch(PDO::FETCH_ASSOC);

$accessstmt = $PDOX->prepare("SELECT * FROM {$p}course_planner_share WHERE course_id = :course_id AND user_email = :user_email");
$accessstmt->execute(array(":course_id" => $course, ":user_email" => $USER->email));
$access_level = $accessstmt->fetch(PDO::FETCH_ASSOC);

$can_edit = ((!$access_level && $courseData["user_id"] == $USER->id) || $access_level["can_edit"]) ? true : false;

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('Course Planner', 'index.php');
$menu->addRight('Exit Plan Editor <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'index.php');

$OUTPUT->header();
?>
<link rel="stylesheet" href="css/planner.css" type="text/css">
<style>
    .alert.alert-warning.h4 {
        position:absolute;
        left: 0;
        right:0;
        text-align:center;
        display:inline-block;
        font-weight: 400;
        margin:0 auto;
        padding: 0 15px;
    }
    <?php
    if (!$can_edit) {
        ?>
        tbody th:hover, tbody td:hover {
            cursor: default !important;
        }
        <?php
    }
    ?>
</style>
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

if (!$can_edit) {
    echo '<div style="position:relative;width:100%;"><div class="alert alert-warning h4"><span class="fas fa-lock" aria-hidden="true"></span> You have read-only access to this course plan.</div></div>';
}
switch ($courseTerm) {
    case 202110:
        $termTitle = "Spring 2021";
        break;
    case 2021531:
        $termTitle = "Summer 2021 - First Session";
        break;
    case 2021532:
        $termTitle = "Summer 2021 - Second Session";
        break;
    case 2021533:
        $termTitle = "Summer 2021 - Full Third Term";
        break;
    case 202180:
        $termTitle = "Fall 2021";
        break;
    case 202210:
        $termTitle = "Spring 2022";
        break;
    default:
        $termTitle = "Fall 2020";
        break;
}
echo '<h3 class="term-title">'.$termTitle.'</h3>';
?>
    <div id="toolTitle" class="h1">
        <div class="h3 inline pull-right">
            <?php
            if ($courseData["user_id"] == $USER->id) {
                // My course plan so show full menu
                ?>
                <a href="preview.php?course=<?=$course?>&back=edit" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                <a href="share.php?course=<?=$course?>&back=edit" class="plan-link" title="Share"><span class="fas fa-user-plus" aria-hidden="true"></span><span class="sr-only">Share</span></a>
                <a href="copyplan.php?course=<?=$course?>" class="plan-link" title="Copy"><span class="far fa-clone" aria-hidden="true"></span><span class="sr-only">Copy</span></a>
                <a href="#renameModal" data-toggle="modal" class="plan-link" title="Rename"><span class="fas fa-pencil-alt" aria-hidden="true"></span><span class="sr-only">Rename</span></a>
                <a href="deleteplan.php?course=<?=$course?>" onclick="return confirm('Are you sure you want to delete this course plan? Deleting a course plan also deletes it for everyone it was shared with. This can not be undone.');" class="plan-link" title="Delete"><span class="far fa-trash-alt" aria-hidden="true"></span><span class="sr-only">Delete</span></a>
                <?php
            } else {
                // Shared with me so show smaller menu
                ?>
                <a href="preview.php?course=<?=$course?>&back=edit" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                <a href="copyplan.php?course=<?=$course?>" class="plan-link" title="Copy"><span class="far fa-clone" aria-hidden="true"></span><span class="sr-only">Copy</span></a>
                <a href="unshare.php?course=<?=$course?>&email=<?=urlencode($USER->email)?>" onclick="return confirm('Are you sure you want to remove your access to this plan. The creator of the plan will need to grant you access to undo this action.');" class="plan-link" title="Remove from my list"><span class="fas fa-user-slash" aria-hidden="true"></span><span class="sr-only">Remove from my list</span></a>
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
    <p>Double-click on a table cell to edit the contents or double-click on a week header to edit all of the content for that week. Single click any table cell to see the full contents.</p>
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
        </tr>
        </thead>
        <tbody>
        <?php
        $cell_override = '';
        // Set the week count. Summer is special otherwise 16
        // Summer: 202153 (1 - S1, 2 - S2, 3 - FT)
        switch (intval($courseTerm)) {
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
        for ($weekNum = 1; $weekNum <= $weekCount; $weekNum++) {
            $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE course_id = :course AND weeknumber = :weekNumber");
            $weekStmt->execute(array(":course" => $course, ":weekNumber" => $weekNum));
            $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
            if ($weekNum > 8) {
                $cell_override = 'bottomhalf';
            }
            echo '<tr>';
            echo'<th data-week="'.$weekNum.'">'.getWeekInfo($courseTerm, $weekNum).'</th>';
            ?>
            <td data-week="<?=$weekNum?>" data-contenttype="Topic(s)" class="<?=$planWeek && !empty($planWeek["topics"]) ? 'hasContent' : 'empty'?>">
                <span><?=$planWeek ? strip_tags($planWeek["topics"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["topics"] : ""?></textarea>
                <div class="cell-details <?=$cell_override?>"><?=$planWeek ? $planWeek["topics"] : ""?></div>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Readings" class="<?=$planWeek && !empty($planWeek["readings"]) ? 'hasContent' : 'empty'?>">
                <span><?=$planWeek ? strip_tags($planWeek["readings"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["readings"] : ""?></textarea>
                <div class="cell-details <?=$cell_override?>"><?=$planWeek ? $planWeek["readings"] : ""?></div>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Videos" class="<?=$planWeek && !empty($planWeek["videos"]) ? 'hasContent' : 'empty'?>">
                <span><?=$planWeek ? strip_tags($planWeek["videos"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["videos"] : ""?></textarea>
                <div class="cell-details <?=$cell_override?>"><?=$planWeek ? $planWeek["videos"] : ""?></div>
            </td>
            <?php
            $cell_override = $cell_override . ' righthalf';
            ?>
            <td data-week="<?=$weekNum?>" data-contenttype="Activities" class="<?=$planWeek && !empty($planWeek["activities"]) ? 'hasContent' : 'empty'?>">
                <span><?=$planWeek ? strip_tags($planWeek["activities"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["activities"] : ""?></textarea>
                <div class="cell-details <?=$cell_override?>"><?=$planWeek ? $planWeek["activities"] : ""?></div>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Assignments" class="<?=$planWeek && !empty($planWeek["assignments"]) ? 'hasContent' : 'empty'?>">
                <span><?=$planWeek ? strip_tags($planWeek["assignments"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["assignments"] : ""?></textarea>
                <div class="cell-details <?=$cell_override?>"><?=$planWeek ? $planWeek["assignments"] : ""?></div>
            </td>
            <td data-week="<?=$weekNum?>" data-contenttype="Tests/Exams" class="<?=$planWeek && !empty($planWeek["exams"]) ? 'hasContent' : 'empty'?>">
                <span><?=$planWeek ? strip_tags($planWeek["exams"]) : ""?></span>
                <textarea class="content"><?=$planWeek ? $planWeek["exams"] : ""?></textarea>
                <div class="cell-details <?=$cell_override?>"><?=$planWeek ? $planWeek["exams"] : ""?></div>
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
                    <h4 class="modal-title">Update <?=$courseTitle?></h4>
                </div>
                <div class="modal-body">
                    <form class="form" method="post" action="renameplan.php">
                        <input type="hidden" name="course" value="<?=$course?>">
                        <input type="hidden" name="back" value="edit">
                        <div class="form-group">
                            <label for="planTitle" id="planTitleLabel">Course Plan Title</label>
                            <input type="text" class="form-control" name="title" id="planTitle" value="<?=$courseTitle?>" placeholder="e.g. TST 100 (Fall 2020)" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="renameTerm">Term Schedule</label>
                            <select id="renameTerm" name="term" class="form-control">
                                <option value="202210" <?= $courseTerm == 202210 ? "selected" : ""?>>Spring 2022</option>
                                <option value="202180" <?= $courseTerm == 202180 ? "selected" : ""?>>Fall 2021</option>
                                <option value="2021533" <?= $courseTerm == 2021533 ? "selected" : ""?>>Summer 2021 - Full Third Term</option>
                                <option value="2021531" <?= $courseTerm == 2021531 ? "selected" : ""?>>Summer 2021 - First Session</option>
                                <option value="2021532" <?= $courseTerm == 2021532 ? "selected" : ""?>>Summer 2021 - Second Session</option>
                                <option value="202110" <?= $courseTerm == 202010 ? "selected" : ""?>>Spring 2021</option>
                                <option value="202080" <?= $courseTerm == 202080 ? "selected" : ""?>>Fall 2020</option>
                            </select>
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
    <script src="ckeditor5/build/ckeditor.js"></script>
    <script>
        $(document).ready(function(){
            let theEditor;
            ClassicEditor
                .create( document.querySelector( '#editContent' ), {
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
                .then (editor => { theEditor = editor; })
                .catch( error => {
                    console.error( error );
                } );
            $('[data-toggle="tooltip"]').tooltip();
            $("td.hasContent").off("click").on("click", function() {
                $("div.cell-details").hide();
                $(this).find("div.cell-details").fadeToggle();
            });
            $("td.empty").off("click").on("click", function() {
                $("div.cell-details").hide();
            });
            <?php
            if ($can_edit) {
                ?>
            $("td").off("dblclick").on("dblclick", function() {


                let week = $(this).data("week");
                let contenttype = $(this).data("contenttype");
                let content = $(this).find("textarea.content").val();
                let contentlabel = "Week " + week + " - " + contenttype;

                $("#editWeek").val(week);
                $("#editContentType").val(contenttype);
                theEditor.setData(content);

                $("#editHeader").text(contentlabel);
                $("#editContentLabel").text(contentlabel);

                $("#editModal").modal("show", {
                    focus: false
                });
            });
            $("th").off("dblclick").on("dblclick", function() {
                // navigate to edit week
                let week = $(this).data("week");
                window.location.href = 'edit-week.php?course=<?=$course?>&week='+week+'&PHPSESSID=<?=$_GET["PHPSESSID"]?>'
            });
                <?php
            }
            ?>
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
                    $weekInfo = 'Week 1<br /><span style="font-weight: normal;">(8/24-8/30)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 1 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="MLK Day OFF, Mon, 1/18 / Classes start on Tues, 1/19"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(1/19-1/24)</span>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = 'Week 1<br /><span style="font-weight: normal;">(5/17-5/23)</span>';
                    break;
                case 2021532:
                    $weekInfo = 'Week 1<br /><span style="font-weight: normal;">(6/28-7/4)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 1<br /><span style="font-weight: normal;">(8/23-8/29)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 1<br /><span style="font-weight: normal;">(1/10-1/16)</span>';
                    break;
            }
            break;
        case 2:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 2<br /><span style="font-weight: normal;">(8/31-9/6)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 2<br /><span style="font-weight: normal;">(1/25-1/31)</span>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = 'Week 2<br /><span style="font-weight: normal;">(5/24-5/30)</span>';
                    break;
                case 2021532:
                    $weekInfo = 'Week 2 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No 7/5 in observance of Independence Day"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(7/5-7/11)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 2<br /><span style="font-weight: normal;">(8/30-9/5)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 2 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="MLK Day, 1/17"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(1/17-1/23)</span>';
                    break;
            }
            break;
        case 3:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 3<br /><span style="font-weight: normal;">(9/7-9/13)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 3<br /><span style="font-weight: normal;">(2/1-2/7)</span>';
                    break;
                case 2021531:
                case 2021533:
                $weekInfo = 'Week 3 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Memorial Day Off, 5/31"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(5/31-6/6)</span>';
                    break;
                case 2021532:
                    $weekInfo = 'Week 3<br /><span style="font-weight: normal;">(7/12-7/18)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 3 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Labor Day, 9/6"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(9/6-9/12)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 3<br /><span style="font-weight: normal;">(1/24-1/30)</span>';
                    break;
            }
            break;
        case 4:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 4<br /><span style="font-weight: normal;">(9/14-9/20)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 4<br /><span style="font-weight: normal;">(2/8-2/14)</span>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = 'Week 4<br /><span style="font-weight: normal;">(6/7-6/13)</span>';
                    break;
                case 2021532:
                    $weekInfo = 'Week 4<br /><span style="font-weight: normal;">(7/19-7/25)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 4<br /><span style="font-weight: normal;">(9/13-9/19)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 4<br /><span style="font-weight: normal;">(1/31-2/6)</span>';
                    break;
            }
            break;
        case 5:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 5 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 9/23"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(9/21-9/27)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 5<br /><span style="font-weight: normal;">(2/15-2/21)</span>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = 'Week 5<br /><span style="font-weight: normal;">(6/14-6/20)</span>';
                    break;
                case 2021532:
                    $weekInfo = 'Week 5<br /><span style="font-weight: normal;">(7/26-8/1)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 5<br /><span style="font-weight: normal;">(9/20-9/26)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 5<br /><span style="font-weight: normal;">(2/7-2/13)</span>';
                    break;
            }
            break;
        case 6:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 6<br /><span style="font-weight: normal;">(9/28-10/4)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 6 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Mini Break #1 - Tues, 2/23"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(2/22-2/28)</span>';
                    break;
                case 2021531:
                case 2021533:
                    $weekInfo = 'Week 6<br /><span style="font-weight: normal;">(6/21-6/26)</span>';
                    break;
                case 2021532:
                    $weekInfo = 'Week 6<br /><span style="font-weight: normal;">(8/2-8/7)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 6<br /><span style="font-weight: normal;">(9/27-10/3)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 6<br /><span style="font-weight: normal;">(2/14-2/20)</span>';
                    break;
            }
            break;
        case 7:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 7<br /><span style="font-weight: normal;">(10/5-10/11)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 7<br /><span style="font-weight: normal;">(3/1-3/7)</span>';
                    break;
                case 2021533:
                    $weekInfo = 'Week 7<br /><span style="font-weight: normal;">(6/28-7/4)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 7 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Mid-Term Break, 10/7-10/8"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(10/4-10/10)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 7<br /><span style="font-weight: normal;">(2/21-2/27)</span>';
                    break;
            }
            break;
        case 8:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 8<br /><span style="font-weight: normal;">(10/12-10/18)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 8<br /><span style="font-weight: normal;">(3/8-3/14)</span>';
                    break;
                case 2021533:
                    $weekInfo = 'Week 8 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No 7/5 in observance of Independence Day"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(7/5-7/11)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 8<br /><span style="font-weight: normal;">(10/11-10/17)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 8<br /><span style="font-weight: normal;">(2/28-3/6)</span>';
                    break;
            }
            break;
        case 9:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 9 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 10/20"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(10/19-10/25)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 9<br /><span style="font-weight: normal;">(3/15-3/21)</span>';
                    break;
                case 2021533:
                    $weekInfo = 'Week 9<br /><span style="font-weight: normal;">(7/12-7/18)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 9<br /><span style="font-weight: normal;">(10/18-10/24)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 9<br /><span style="font-weight: normal;">(3/7-3/13)</span>';
                    break;
            }
            break;
        case 10:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 10<br /><span style="font-weight: normal;">(10/26-11/1)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 10<br /><span style="font-weight: normal;">(3/22-3/28)</span>';
                    break;
                case 2021533:
                    $weekInfo = 'Week 10<br /><span style="font-weight: normal;">(7/19-7/25)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 10<br /><span style="font-weight: normal;">(10/25-10/31)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 10 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Spring Break, 3/12-3/20"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(3/14-3/20)</span>';
                    break;
            }
            break;
        case 11:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 11<br /><span style="font-weight: normal;">(11/2-11/8)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 11 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Good Friday Off, 4/2"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(3/29-4/4)</span>';
                    break;
                case 2021533:
                    $weekInfo = 'Week 11<br /><span style="font-weight: normal;">(7/26-8/1)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 11<br /><span style="font-weight: normal;">(11/1-11/7)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 11<br /><span style="font-weight: normal;">(3/21-3/27)</span>';
                    break;
            }
            break;
        case 12:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 12<br /><span style="font-weight: normal;">(11/9-11/15)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 12<br /><span style="font-weight: normal;">(4/5-4/11)</span>';
                    break;
                case 2021533:
                    $weekInfo = 'Week 12<br /><span style="font-weight: normal;">(8/2-8/7)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 12<br /><span style="font-weight: normal;">(11/8-11/14)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 12<br /><span style="font-weight: normal;">(3/28-4/3)</span>';
                    break;
            }
            break;
        case 13:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 13<br /><span style="font-weight: normal;">(11/16-11/22)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 13 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Mini Break #2 - Wed, 4/14"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(4/12-4/18)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 13<br /><span style="font-weight: normal;">(11/15-11/21)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 13<br /><span style="font-weight: normal;">(4/4-4/10)</span>';
                    break;
            }
            break;
        case 14:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 14 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 11/25-11/27"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(11/23-11/29)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 14 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Stander Symposium, Thurs, 4/22"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(4/19-4/25)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 14 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Thanksgiving Break, 11/24-11/26"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(11/22-11/28)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 14 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Easter, 4/14-4/18"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(4/11-4/17)</span>';
                    break;
            }
            break;
        case 15:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 15<br /><span style="font-weight: normal;">(11/30-12/6)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 15 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Last Day of Classes - Fri, 4/30"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(4/26-5/2)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 15<br /><span style="font-weight: normal;">(11/29-12/5)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 15 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Stander Symposium, 4/20"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(4/18-4/24)</span>';
                    break;
            }
            break;
        case 16:
            switch($term) {
                case 202080:
                    $weekInfo = 'Week 16 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="No classes 12/8"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(12/7-12/13)</span>';
                    break;
                case 202110:
                    $weekInfo = 'Week 16 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Finals Week (M-F)"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(5/3-5/7)</span>';
                    break;
                case 202180:
                    $weekInfo = 'Week 16 <a href="#" class="pull-right" data-toggle="tooltip" data-placement="top" title="Feast of Immaculate Conception, 12/8"><span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only">Information</span></a>
                        <br /><span style="font-weight: normal;">(12/6-12/12)</span>';
                    break;
                case 202210:
                    $weekInfo = 'Week 16<br /><span style="font-weight: normal;">(4/25-5/1)</span>';
                    break;
            }
            break;
    }
    return $weekInfo;
}