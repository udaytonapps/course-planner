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
    $courseId = isset($_POST["course_id"]) ? $_POST["course_id"] : false;
    if (!$courseId) {
        $_SESSION["error"] = "Error sharing course plan, plan not set properly.";
        header('Location: ' . addSession('index.php'));
        return;
    }
    $email_array = array_unique(explode(",", $_POST["emails"]));
    $edit_access = isset($_POST["access"]) && $_POST["access"] == "edit" ? 1 : 0;
    $notify = isset($_POST["notify"]) && $_POST["notify"] == "1" ? true : false;

    $errors = false;
    foreach ($email_array as $email) {
        // Check for existing share row
        $email = trim($email); // Remove any whitespace
        $checkStmt = $PDOX->prepare("SELECT * FROM {$p}course_planner_share WHERE course_id = :course AND user_email = :email");
        $checkStmt->execute(array(":course" => $courseId, ":email" => $email));
        $check = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$check) {
            // Not currently shared so insert record
            $newStmt = $PDOX->prepare("INSERT INTO {$p}course_planner_share (course_id, user_email, can_edit) VALUES (:courseId, :email, :canEdit)");
            $newStmt->execute(array(
                ":courseId" => $courseId,
                ":email" => $email,
                ":canEdit" => $edit_access
            ));
            // Notify if set
            if ($notify) {
                // Notify elearning that there is a new answer
                // the message
                $access_string = $edit_access == 1 ? "edit" : "read-only";
                $msg = "Hello!\n\nYou have been granted ".$access_string." access to a new course plan created by ".$USER->displayname." via the \"Course Planner\" tool in Isidore. You will find the course plan in the \"Shared with me\" section on the main page of the tool.\n\nYou can access the course planning tool by logging into Isidore and clicking on \"Course Planner\" in the left-hand menu from the Home page.\n\nHappy Planning!";

                // use wordwrap() if lines are longer than 70 characters
                $msg = wordwrap($msg,70);

                $headers  = "From: Course Planner < no-reply@learningapps.udayton.edu >\n";

                // send email
                mail($email, "You have been granted ".$access_string." access to a new course plan!", $msg, $headers);
            }
        } else {
            $errors = true;
        }
    }

    $success = "Access granted successfully.";
    if ($notify) {
        $success = $success." Email notifications sent successfully.";
    }

    if ($errors) {
        $success = $success. " Some users were previously granted access and were skipped.";
    }

    if (isset($_POST["back"]) && $_POST["back"] == "edit") {
        $back = "edit";
    } else {
        $back = "index";
    }

    $_SESSION["success"] = $success;
    header('Location: ' . addSession('share.php?course='.$courseId.'&back='.$back));
    return;
}

if (isset($_GET["course"])) {
    $course = $_GET["course"];
    // Get the title for the course
    $query = "SELECT * FROM {$p}course_planner_main WHERE course_id = :courseId;";
    $arr = array(':courseId' => $course);
    $courseData = $PDOX->rowDie($query, $arr);
    $courseTitle = $courseData ? $courseData["title"] : "";
    $courseTerm = $courseData ? $courseData["term"] : 202080;
} else {
    $_SESSION["error"] = "Unable to share course plan. Invalid id.";
    header("Location: " . addSession("index.php"));
    return;
}

$sharesqry = $PDOX->prepare("SELECT * FROM {$p}course_planner_share WHERE course_id = :course_id ORDER BY user_email");
$sharesqry->execute(array(":course_id" => $course));
$shares = $sharesqry->fetchAll(PDO::FETCH_ASSOC);

$back = isset($_GET["back"]) && $_GET["back"] == 'edit' ? "edit" : "index";

$menu = new \Tsugi\UI\MenuSet();
$menu->setHome('Course Planner', 'index.php');
if ($back == "index") {
    $menu->addRight('Exit Sharing <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'index.php');
} else {
    $menu->addRight('Exit Sharing <span class="fas fa-sign-out-alt" aria-hidden="true"></span>', 'edit.php?course='.$course);
}

$OUTPUT->header();
?>
    <link rel="stylesheet" href="css/planner.css" type="text/css">
<?php
$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

echo '<div class="container-fluid">';

$OUTPUT->flashMessages();
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
    default:
        $termTitle = "Fall 2020";
        break;
}
echo '<h3 class="term-title">'.$termTitle.'</h3>';
$OUTPUT->pageTitle($courseTitle, false, false);
?>
<div class="row">
    <div class="col-sm-6">
        <div class="planbox">
            <h3 style="margin:0;">Share with people </h3>
            <p>Enter an email (or emails comma-separated) below to share this plan.</p>
            <form class="form" method="post">
                <input type="hidden" name="course_id" value="<?=$course?>">
                <input type="hidden" name="back" value="<?=$back?>">
                <div class="form-group">
                    <label for="addpeople" id="addpeoplelabel">Add people by email address</label>
                    <input type="text" class="form-control" name="emails" value="" placeholder="e.g. email@udayton.edu, email2@udayton.edu" required autofocus>
                </div>
                <div class="radio">
                    <label><input type="radio" name="access" value="edit" checked> User(s) can edit</label>
                </div>
                <div class="radio">
                    <label><input type="radio" name="access" value="locked"> Read-only access</label>
                </div>
                <div class="form-group">
                    <div class="checkbox">
                        <label><input type="checkbox" name="notify" value="1"> Notify user(s) via email</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Grant Access</button>
            </form>
        </div>
    </div>
    <div class="col-sm-6">
        <h3>Currently shared with</h3>
        <?php
        if (!$shares) {
            echo '<p><em>This course plan is not currently shared with anyone else.</em></p>';
        } else {
            echo '<div class="list-group">';
            foreach ($shares as $share) {
                echo '<div class="list-group-item h4"><span class="email" style="color: #4a5568;">'.$share["user_email"].'</span>';
                echo '<div class="pull-right">';
                if ($share["can_edit"]) {
                    echo '<a href="toggleaccess.php?course='.$course.'&email='.urlencode($share["user_email"]).'&access=readonly&back='.$back.'" class="plan-link" title="User can edit this plan"><span class="fas fa-lock-open" aria-hidden="true"></span><span class="sr-only">Make read-only</span></a>';
                } else {
                    echo '<a href="toggleaccess.php?course='.$course.'&email='.urlencode($share["user_email"]).'&access=edit&back='.$back.'" onclick="return confirm(\'Are you sure you want to grant this user the ability to edit this course plan?\');" class="plan-link" title="User has read-only access to this plan"><span class="fas fa-lock" aria-hidden="true"></span><span class="sr-only">Allow editing</span></a>';
                }
                echo '<a href="unshare.php?course='.$course.'&email='.urlencode($share["user_email"]).'&back2=share&back='.$back.'" class="plan-link" title="Unshare"><span class="fas fa-user-slash" aria-hidden="true"></span><span class="sr-only">Unshare</span></a>';
                echo '</div></div>';
            }
            echo '</div>';
        }
        ?>
    </div>
</div>
<?php

echo '</div>';// end container

$OUTPUT->footerStart();

$OUTPUT->footerEnd();