<?php

// Import the necessary libraries
require 'vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;

// Get the URL
$url = 'https://www.pro-football-reference.com/years/2023/games.htm#games';

// Create a Crawler object
$crawler = new Crawler();

// Crawl the URL
$crawler->request('GET', $url);

// Find the table
$table = $crawler->filter('table.data');

// Extract the information from the table
$rows = $table->filter('tr');

foreach ($rows as $row) {
    $game_info = [];

    // Extract the information from each cell
    foreach ($row->filter('td') as $cell) {
        $game_info[] = $cell->text();
    }

    // Print the information
    print_r($game_info);
}

