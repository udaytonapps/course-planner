<?php

// The SQL to uninstall this tool
$DATABASE_UNINSTALL = array(
);

// The SQL to create the tables if they don't exist
$DATABASE_INSTALL = array(array( "{$CFG->dbprefix}course_planner",
    "create table {$CFG->dbprefix}course_planner (
    planweek_id     INTEGER NOT NULL AUTO_INCREMENT,
    user_id         INTEGER NOT NULL,
    context_id      INTEGER NOT NULL,
    weeknumber      INTEGER NOT NULL,
    topics          TEXT NULL,
    readings        TEXT NULL,
    videos          TEXT NULL,
    activities      TEXT NULL,
    assignments     TEXT NULL,
    exams           TEXT NULL,
    discussions     TEXT NULL,

    PRIMARY KEY(planweek_id)
	
) ENGINE = InnoDB DEFAULT CHARSET=utf8")
);
