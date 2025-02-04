<?php
$conn = mysqli_connect("localhost", "root", "", "goalsphere");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?> 