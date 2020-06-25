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
    if (!isset($_POST["action"])) {
        $_SESSION["error"] = "Unable to process that action. Please try again.";
        header('Location: ' . addSession('index.php'));
    }
    if ($_POST["action"] == "add") {
        // Add new course plan and go to planner
        $newStmt = "INSERT INTO {$p}course_planner_main (user_id, title) VALUES (:userId, :title);";
        $arr = array(':userId' => $USER->id, ':title' => $_POST["title"]);
        $PDOX->queryDie($newStmt, $arr);
        $course_id = $PDOX->lastInsertId();
        $_SESSION["success"] = "Course plan saved successfully.";
        header('Location: ' . addSession('edit.php?course='.$course_id));
    }
}

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('My Course Planner', 'index.php');

$OUTPUT->header();
?>
    <link rel="stylesheet" href="css/planner.css" type="text/css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

?>
<div class="row" style="margin-top: 3.23rem;">
    <div class="col-sm-5">
        <h2 style="margin-top:0;">What is this tool?</h2>
        <p>A, alias cumque deserunt ducimus et expedita inventore ipsa modi, nisi porro soluta. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusantium alias architecto consectetur consequatur, et excepturi illo iure labore laborum laudantium, magnam officiis optio provident sit tempore. Autem dolor eveniet qui!</p>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. A, alias cumque deserunt ducimus et expedita inventore ipsa modi, nisi porro soluta.</p>
        <p class="h4 inline"><a href="">Watch a video about it <span class="fas fa-chevron-right" aria-hidden="true"></span></a></p>
    </div>
    <div class="col-sm-7">
        <div class="planbox">
            <a href="#addPlanModal" data-toggle="modal" class="btn btn-link pull-right"><span class="fas fa-plus" aria-hidden="true"></span> Add Course Plan</a>
            <h3 style="margin:0;">My Course Plans</h3>
            <p>Click on the title of a plan below to edit.</p>
            <?php
            $plansqry = $PDOX->prepare("SELECT * FROM {$p}course_planner_main WHERE user_id = :user_id ORDER BY title");
            $plansqry->execute(array(":user_id" => $USER->id));
            $plans = $plansqry->fetchAll(PDO::FETCH_ASSOC);
            if (!$plans) {
                echo '<p><em>No course plans created yet. Click on the "+ Add Course Plan" link in the top right to begin.</em></p>';
            } else {
                echo '<div class="list-group">';
                foreach ($plans as $plan) {
                    $sharestmt = $PDOX->prepare("SELECT count(*) as total FROM {$p}course_planner_share WHERE course_id = :course_id");
                    $sharestmt->execute(array(":course_id" => $plan["course_id"]));
                    $sharecount = $sharestmt->fetch(PDO::FETCH_ASSOC);
                    echo '<div class="list-group-item h4">';
                    echo '<a href="edit.php?course='.$plan["course_id"].'"><span class="fas fa-cube" style="padding-right:8px;" aria-hidden="true"></span> '.$plan["title"].'</a> ';
                    if ($sharecount["total"] > 1) {
                        echo '<span class="text-muted" data-toggle="tooltip" title="Shared with multiple people"><span class="fas fa-users fa-fw" aria-hidden="true"></span></span>';
                    } else if ($sharecount["total"] > 0) {
                        echo '<span class="text-muted" data-toggle="tooltip" title="Shared with one other person"><span class="fas fa-user-friends fa-fw" aria-hidden="true"></span></span>';
                    }
                    echo '<div class="pull-right">
                            <a href="#" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                            <a href="share.php?course='.$plan["course_id"].'" class="plan-link" title="Share"><span class="fas fa-user-plus" aria-hidden="true"></span><span class="sr-only">Share</span></a>
                            <a href="#" class="plan-link" title="Rename"><span class="fas fa-pencil-alt" aria-hidden="true"></span><span class="sr-only">Rename</span></a>
                            <a href="#" class="plan-link" title="Delete"><span class="far fa-trash-alt" aria-hidden="true"></span><span class="sr-only">Delete</span></a>
                          </div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            $sharedplansqry = $PDOX->prepare("SELECT m.course_id as course_id, m.title as title, m.user_id as creator_id, s.can_edit as can_edit FROM
                                                        {$p}course_planner_share s join {$p}course_planner_main m on s.course_id = m.course_id
                                                        WHERE s.user_email = :email ORDER BY m.title");
            $sharedplansqry->execute(array(":email" => $USER->email));
            $shared_plans = $sharedplansqry->fetchAll(PDO::FETCH_ASSOC);
            ?>
            <hr />
            <h4>Shared with me</h4>
            <?php
            if (!$shared_plans) {
                echo '<p><em>No shared course plans yet.</em></p>';
            } else {
                echo '<div class="list-group">';
                foreach ($shared_plans as $shared_plan) {
                    echo '<div class="list-group-item h4">';
                    if ($shared_plan["can_edit"]) {
                        echo '<a href="edit.php?course='.$shared_plan["course_id"].'"><span class="fas fa-cube" style="padding-right:8px;" aria-hidden="true"></span> '.$shared_plan["title"].'</a> ';
                    } else {
                        echo '<span style="color: #4a5568;"><span class="fas fa-lock" style="padding-right:8px;" aria-hidden="true"></span> '.$shared_plan["title"].'</span>';
                    }
                    echo '<div class="pull-right">
                            <a href="#" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                            <a href="unshare.php?course='.$shared_plan["course_id"].'&email='.$USER->email.'" onclick="return confirm(\'Are you sure you want to remove your access to this plan. The creator of the plan will need to grant you access to undo this action.\');" class="plan-link" title="Remove from my list"><span class="fas fa-user-slash" aria-hidden="true"></span><span class="sr-only">Remove from my list</span></a>
                          </div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>
    <div id="addPlanModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add New Course Plan</h4>
                </div>
                <div class="modal-body">
                    <form class="form" method="post">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label for="planTitle" id="planTitleLabel">Course Plan Title</label>
                            <input type="text" class="form-control" name="title" id="planTitle" value="" placeholder="e.g. TST 100 (Fall 2020)" required autofocus>
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
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
<?php
$OUTPUT->footerEnd();