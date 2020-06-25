<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array();

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(
    array("{$CFG->dbprefix}course_planner_main",
        "create table {$CFG->dbprefix}course_planner_main (
    course_id      INTEGER NOT NULL AUTO_INCREMENT,
    user_id        INTEGER NOT NULL,
	title          VARCHAR(255) NULL,
    
    PRIMARY KEY(course_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array("{$CFG->dbprefix}course_planner",
        "create table {$CFG->dbprefix}course_planner (
    planweek_id     INTEGER NOT NULL AUTO_INCREMENT,
    course_id       INTEGER NOT NULL,
    weeknumber      INTEGER NOT NULL,
    topics          TEXT NULL,
    readings        TEXT NULL,
    videos          TEXT NULL,
    activities      TEXT NULL,
    assignments     TEXT NULL,
    exams           TEXT NULL,
    discussions     TEXT NULL,

    CONSTRAINT `{$CFG->dbprefix}cp_ibfk_1`
        FOREIGN KEY (`course_id`)
        REFERENCES `{$CFG->dbprefix}course_planner_main` (`course_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(planweek_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8"),
    array("{$CFG->dbprefix}course_planner_share",
        "create table {$CFG->dbprefix}course_planner_share (
    share_id        INTEGER NOT NULL AUTO_INCREMENT,
    course_id       INTEGER NOT NULL,
    user_email       VARCHAR(255) NOT NULL,
    can_edit        BOOL NOT NULL DEFAULT 0,

    CONSTRAINT `{$CFG->dbprefix}cp_ibfk_2`
        FOREIGN KEY (`course_id`)
        REFERENCES `{$CFG->dbprefix}course_planner_main` (`course_id`)
        ON DELETE CASCADE,

    PRIMARY KEY(share_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);
