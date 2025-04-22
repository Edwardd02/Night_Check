<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker
*/

// Basic variables - FIXED NAMING
$name        = 'Night Check';  // Must match folder name exactly (no underscores)
$description = 'Night check system for UWC CSC';
$entryURL    = 'night_check_attendance.php';
$type        = 'Additional';
$category    = 'People';
$version     = '0.0.2';       // Use proper version format
$author      = 'Renxuan Yao';
$url         = 'https://renxuanyao.github.io/';

// Module tables - EMPTY SINCE WE'RE NOT USING DB
$moduleTables = [];

// Gibbon settings - MINIMAL EXAMPLE
$gibbonSetting = [];
$gibbonSetting[] = "INSERT INTO gibbonSetting 
    (scope, name, nameDisplay, description, value) 
    VALUES 
    ('Night Check', 'active', 'Active', 'Enable night check system', 'Y')";

// Single action configuration - SIMPLIFIED
$actionRows = [];
$actionRows[0] = [
    'name'                      => 'Night Check Attendance',
    'precedence'                => 0,
    'category'                  => 'Night Check', // Must match $name
    'description'               => 'Perform nightly student check-ins',
    'URLList'                   => 'night_check_attendance.php',
    'entryURL'                  => 'night_check_attendance.php',
    'entrySidebar'              => 'Y', // REQUIRED FOR SIDEBAR
    'menuShow'                  => 'Y',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'Y',
    'defaultPermissionStudent'  => 'N', // Students shouldn't see People menu
    'defaultPermissionParent'   => 'Y',
    'defaultPermissionSupport'  => 'Y',
    'categoryPermissionStaff'   => 'Y',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'N',
    'categoryPermissionOther'   => 'N'
];

$actionRows[1] = [
    'name'                      => 'Submit a leave request',
    'precedence'                => 0,
    'category'                  => 'Night Check', // Must match $name
    'description'               => 'Submit your leave request here',
    'URLList'                   => 'night_check_leave_request.php',
    'entryURL'                  => 'night_check_leave_request.php',
    'entrySidebar'              => 'Y', // REQUIRED FOR SIDEBAR
    'menuShow'                  => 'Y',
    'defaultPermissionAdmin'    => 'Y',
    'defaultPermissionTeacher'  => 'N',
    'defaultPermissionStudent'  => 'N', // Students shouldn't see People menu
    'defaultPermissionParent'   => 'Y',
    'defaultPermissionSupport'  => 'N',
    'categoryPermissionStaff'   => 'N',
    'categoryPermissionStudent' => 'N',
    'categoryPermissionParent'  => 'Y',
    'categoryPermissionOther'   => 'N'
];

// No hooks needed for basic functionality
$hooks = [];