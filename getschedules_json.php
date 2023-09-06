<?php
require('vendor/autoload.php');

// Create a new NFL client
$client = new \NFL\Client();

// Get the schedules
$schedules = $client->getSchedules();

// Save the schedules to a file
file_put_contents('schedules.json', json_encode($schedules));