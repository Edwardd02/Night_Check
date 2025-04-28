<?php
// USE ;end TO SEPARATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = [];
$count = 0;

// v0.0.00
$sql[$count][0] = "0.0.1";
$sql[$count][1] = "";


$count++;
$sql[$count][0] = "0.0.2";
$sql[$count][1] = "";

$count++;
$sql[$count][0] = "0.0.3";
$sql[$count][1] = "";

$count++;
$sql[$count][0] = "0.0.31";
$sql[$count][1] = "";

$count++;
$sql[$count][0] = "0.0.4";
$sql[$count][1] = "CREATE TABLE gibbonNightCheck (
    gibbonPersonID INT unsigned NOT NULL,
    attendance ENUM('Present', 'Absent', 'Late') NOT NULL DEFAULT 'Present',
    out_of_school ENUM('Yes', 'No') NOT NULL DEFAULT 'No',
    date DATE NOT NULL,
    PRIMARY KEY (gibbonPersonID, date),
    FOREIGN KEY (gibbonPersonID) REFERENCES gibbonPerson(gibbonPersonID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";