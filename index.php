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
    $contextId = isset($_POST["context"]) ? $_POST["context"] : false;
    if (!$contextId) {
        $_SESSION["error"] = "Error saving content, context not set properly.";
        header('Location: ' . addSession('index.php'));
        return;
    }
    $weekNumber = isset($_POST["week"]) ? $_POST["week"] : false;
    if (!$weekNumber) {
        $_SESSION["error"] = "Error saving content, week not set properly.";
        header('Location: ' . addSession('index.php?context='.$contextId));
        return;
    }
    $contentType = isset($_POST["contenttype"]) ? $_POST["contenttype"] : false;
    if (!$contentType) {
        $_SESSION["error"] = "Error saving content, content type not set properly.";
        header('Location: ' . addSession('index.php?context='.$contextId));
        return;
    }
    $content = isset($_POST["content"]) ? $_POST["content"] : "";
    // Check for existing week row
    $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE context_id = :context AND weeknumber = :weekNumber");
    $weekStmt->execute(array(":context" => $contextId, ":weekNumber" => $weekNumber));
    $planWeek = $weekStmt->fetch(PDO::FETCH_ASSOC);
    if (!$planWeek) {
        // No existing row so insert instead of update
        switch ($contentType) {
            case "Topic(s)":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, topics) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Readings":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, readings) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Videos":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, videos) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Activities":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, activities) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Assignments":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, assignments) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Tests/Exams":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, exams) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Discussions":
                $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner (user_id, context_id, weeknumber, discussions) 
                            VALUES (:userId, :contextId, :weekNum, :content)");
                $newStmt->execute(array(":userId" => $USER->id, ":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
        }
    } else {
        // Existing plan week record so run an update
        switch ($contentType) {
            case "Topic(s)":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set topics = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Readings":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set readings = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Videos":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set videos = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Activities":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set activities = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Assignments":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set assignments = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Tests/Exams":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set exams = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
            case "Discussions":
                $updateStmt = $PDOX->prepare("UPDATE {$p}course_planner set discussions = :content WHERE context_id = :contextId AND weeknumber = :weekNum");
                $updateStmt->execute(array(":contextId" => $contextId, ":weekNum" => $weekNumber, ":content" => $content));
                break;
        }
    }
    $_SESSION["success"] = "Course content saved successfully.";
    header('Location: ' . addSession('index.php?context='.$contextId));
}

if (isset($_GET["context"])) {
    $context = $_GET["context"];
    // Get the title for the context
    $query = "SELECT title FROM {$p}lti_context WHERE context_id = :contextId;";
    $arr = array(':contextId' => $context);
    $contextData = $PDOX->rowDie($query, $arr);
    $contextTitle = $contextData ? $contextData["title"] : "";
} else {
    $context = $CONTEXT->id;
    $contextTitle = $CONTEXT->title;
}

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('Course Planner', 'index.php?context='.$context);
$menu->addRight('<span class="fas fa-print" aria-hidden="true"></span> Print', "");
$menu->addRight('<span class="fas fa-share-square" aria-hidden="true"></span> Share (via Email)', "");
$menu->addRight('<span class="fas fa-download" aria-hidden="true"></span> Download', "");


$OUTPUT->header();
?>
<link rel="stylesheet" href="css/planner.css" type="text/css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

$OUTPUT->pageTitle($contextTitle, false, false);
?>
<p class="lead">Click on a cell in the table to add content or click on the row title to edit all items in the row.</p>
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
            $weekStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner WHERE context_id = :context AND weeknumber = :weekNumber");
            $weekStmt->execute(array(":context" => $context, ":weekNumber" => $weekNum));
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
                        <input type="hidden" name="context" value="<?=$context?>">
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
                window.location.href = 'edit-week.php?context=<?=$context?>&week='+week+'&PHPSESSID=<?=$_GET["PHPSESSID"]?>'
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