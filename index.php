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
        $term = (is_numeric($_POST["term"]) ? (int) $_POST["term"] : 202080);
        $newStmt = "INSERT INTO {$p}course_planner_main (user_id, title, term) VALUES (:userId, :title, :term);";
        $arr = array(':userId' => $USER->id, ':title' => $_POST["title"], ":term" => $term);
        $PDOX->queryDie($newStmt, $arr);
        $course_id = $PDOX->lastInsertId();
        $_SESSION["success"] = "Course plan saved successfully.";
        header('Location: ' . addSession('edit.php?course='.$course_id));
    }
}

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('Course Planner', 'index.php');

$OUTPUT->header();
?>
    <link rel="stylesheet" href="css/planner.css" type="text/css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();

?>
<div class="row" style="margin-top: 3.23rem;margin-bottom:2rem;">
    <div class="col-sm-5">
        <h2 style="margin-top:0;">Course Planner</h2>
        <p>
            Planning is an essential first step in constructing and delivering a quality course. There are many key decisions that must be made up front that inform how a course will ultimately be constructed.
        </p>
        <ul>
            <li>What topics will I cover and what order should they be delivered in?</li>
            <li>How many assignments should I have?</li>
            <li>What readings or lecture materials will I need to provide if I'm going to ask them to participate in a discussion?</li>
            <li>When will I deliver my first test?</li>
            <li>How will the holiday schedule affect my course?</li>
        </ul>
        <p>
            Course Planner is a simple utility tool designed to help faculty start laying out the key elements of a course. It also provides a great way to visualize how all of the elements will fit together. Faculty can create as many individual course plans as they want and each course plan can be shared with colleagues for easy collaboration.
        </p>
    </div>
    <div class="col-sm-7">
        <div class="planbox">
            <a href="#addPlanModal" data-toggle="modal" class="btn btn-link pull-right"><span class="fas fa-plus" aria-hidden="true"></span> Add Course Plan</a>
            <h3 style="margin:0;">My Course Plans</h3>
            <?php
            $plansqry = $PDOX->prepare("SELECT * FROM {$p}course_planner_main WHERE user_id = :user_id ORDER BY term desc, title");
            $plansqry->execute(array(":user_id" => $USER->id));
            $plans = $plansqry->fetchAll(PDO::FETCH_ASSOC);
            if (!$plans) {
                echo '<p style="clear:right;"><em>No course plans created yet.</em></p>';
            } else {
                echo '<p>Click on the title of a plan below to edit.</p>';
                $current_term = -1;
                foreach ($plans as $plan) {
                    $sharestmt = $PDOX->prepare("SELECT count(*) as total FROM {$p}course_planner_share WHERE course_id = :course_id");
                    $sharestmt->execute(array(":course_id" => $plan["course_id"]));
                    $sharecount = $sharestmt->fetch(PDO::FETCH_ASSOC);
                    if ($current_term != $plan["term"]) {
                        if ($current_term != -1) {
                            echo '</div>'; // End list group if not first iteration
                        }
                        $current_term = $plan["term"];
                        // New list group
                        switch ($current_term) {
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
                            default:
                                $termTitle = "Fall 2020";
                                break;
                        }
                        echo '<h5>'.$termTitle.'</h5>';
                        echo '<div class="list-group">';
                    }
                    echo '<div class="list-group-item h4">';
                    echo '<a href="edit.php?course='.$plan["course_id"].'"><span class="fas fa-cube" style="padding-right:8px;" aria-hidden="true"></span> '.$plan["title"].'</a> ';
                    if ($sharecount["total"] > 1) {
                        echo '<span class="text-muted" data-toggle="tooltip" title="Shared with multiple people"><span class="fas fa-users fa-fw" aria-hidden="true"></span></span>';
                    } else if ($sharecount["total"] > 0) {
                        echo '<span class="text-muted" data-toggle="tooltip" title="Shared with one other person"><span class="fas fa-user-friends fa-fw" aria-hidden="true"></span></span>';
                    }
                    echo '<div class="pull-right">
                            <a href="preview.php?course='.$plan["course_id"].'" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                            <a href="share.php?course='.$plan["course_id"].'" class="plan-link" title="Share"><span class="fas fa-user-plus" aria-hidden="true"></span><span class="sr-only">Share</span></a>
                            <a href="copyplan.php?course='.$plan["course_id"].'" class="plan-link" title="Copy"><span class="far fa-clone" aria-hidden="true"></span><span class="sr-only">Copy</span></a>
                            <a href="javascript:void(0);" class="plan-link rename-link" title="Rename" data-course="'.$plan["course_id"].'" data-plantitle="'.$plan["title"].'" data-term="'.$plan["term"].'">
                                <span class="fas fa-pencil-alt" aria-hidden="true"></span><span class="sr-only">Rename</span>
                            </a>
                            <a href="deleteplan.php?course='.$plan["course_id"].'" onclick="return confirm(\'Are you sure you want to delete this course plan? Deleting a course plan also deletes it for everyone it was shared with. This can not be undone.\');" class="plan-link" title="Delete"><span class="far fa-trash-alt" aria-hidden="true"></span><span class="sr-only">Delete</span></a>
                          </div>';
                    echo '</div>';
                }
                echo '</div>';
            }
            $sharedplansqry = $PDOX->prepare("SELECT m.course_id as course_id, m.title as title, m.user_id as creator_id, s.can_edit as can_edit, m.term as term FROM
                                                        {$p}course_planner_share s join {$p}course_planner_main m on s.course_id = m.course_id
                                                        WHERE s.user_email = :email ORDER BY m.term desc, m.title");
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
                $current_term = -1;
                foreach ($shared_plans as $shared_plan) {
                    // Get owner's name
                    $displayname_qry = "SELECT displayname FROM {$p}lti_user WHERE user_id = :user_id;";
                    $displayname_arr = array(':user_id' => $shared_plan["creator_id"]);
                    $lti_user = $PDOX->rowDie($displayname_qry, $displayname_arr);
                    $displayname = $lti_user ? $lti_user["displayname"] : "";
                    if ($current_term != $shared_plan["term"]) {
                        if ($current_term != -1) {
                            echo '</div>'; // End list group if not first iteration
                        }
                        $current_term = $shared_plan["term"];
                        // New list group
                        switch ($current_term) {
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
                            default:
                                $termTitle = "Fall 2020";
                                break;
                        }
                        echo '<h5>'.$termTitle.'</h5>';
                        echo '<div class="list-group">';
                    }
                    echo '<div class="list-group-item h4">';
                    echo '<a href="edit.php?course='.$shared_plan["course_id"].'"><span class="fas fa-cube" style="padding-right:8px;" aria-hidden="true"></span> '.$shared_plan["title"].'</a> ';
                    echo '<span data-toggle="tooltip" title="Owner: '.$displayname.'" class="text-muted"><span class="fas fa-user-shield" aria-hidden="true"></span><span class="sr-only">Owner</span></span>';
                    echo '<div class="pull-right">';
                    if ($shared_plan["can_edit"] == 0) {
                        echo '<span data-toggle="tooltip" title="Read-only access" class="fas fa-lock text-muted" style="padding-left:8px;" aria-hidden="true"></span><span class="sr-only">Read-only</span>';
                    }
                    echo '  <a href="preview.php?course='.$shared_plan["course_id"].'" class="plan-link" title="Preview"><span class="far fa-eye" aria-hidden="true"></span><span class="sr-only">Preview</span></a>
                            <a href="copyplan.php?course='.$shared_plan["course_id"].'" class="plan-link" title="Copy"><span class="far fa-clone" aria-hidden="true"></span><span class="sr-only">Copy</span></a>
                            <a href="unshare.php?course='.$shared_plan["course_id"].'&email='.urlencode($USER->email).'" onclick="return confirm(\'Are you sure you want to remove your access to this plan. The creator of the plan will need to grant you access to undo this action.\');" class="plan-link" title="Remove from my list"><span class="fas fa-user-slash" aria-hidden="true"></span><span class="sr-only">Remove from my list</span></a>
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
                        <div class="form-group">
                            <label for="term">Term Schedule</label>
                            <select id="term" name="term" class="form-control">
                                <option value="2021533">Summer 2021 - Full Third Term</option>
                                <option value="2021531">Summer 2021 - First Session</option>
                                <option value="2021532">Summer 2021 - Second Session</option>
                                <option value="202110">Spring 2021</option>
                                <option value="202080">Fall 2020</option>
                            </select>
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
                    <h4 class="modal-title">Update <span id="renameHeader"></span></h4>
                </div>
                <div class="modal-body">
                    <form class="form" method="post" action="renameplan.php">
                        <input type="hidden" id="renameCourse" name="course" value="">
                        <input type="hidden" name="back" value="index">
                        <div class="form-group">
                            <label for="planTitle" id="planTitleLabel">Course Plan Title</label>
                            <input type="text" class="form-control" name="title" id="renameTitle" value="" placeholder="e.g. TST 100 (Fall 2020)" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="renameTerm">Term Schedule</label>
                            <select id="renameTerm" name="term" class="form-control">
                                <option value="2021533">Summer 2021 - Full Third Term</option>
                                <option value="2021531">Summer 2021 - First Session</option>
                                <option value="2021532">Summer 2021 - Second Session</option>
                                <option value="202110">Spring 2021</option>
                                <option value="202080">Fall 2020</option>
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
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
            $("a.rename-link").off("click").on("click", function() {
                let course = $(this).data("course");
                let plantitle = $(this).data("plantitle");
                let term = $(this).data("term");

                $("#renameCourse").val(course);
                $("#renameTitle").val(plantitle);
                $('#renameTerm option[value="'+term+'"]').prop("selected", true);
                $("#renameHeader").text(plantitle);

                $("#renameModal").modal("show");
            });
        });
    </script>
<?php
$OUTPUT->footerEnd();