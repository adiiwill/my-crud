<?php
include_once("./constants.php");

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Could not connect to database.");
}
