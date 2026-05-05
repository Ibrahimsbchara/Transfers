<?php
// Diagnostic page — visit this URL to see what's broken, then delete it
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h2>System Check</h2><pre>';

// PHP version
echo 'PHP version : ' . phpversion() . "\n";

// PDO SQLite
echo 'PDO SQLite  : ' . (extension_loaded('pdo_sqlite') ? 'YES' : 'NO - THIS IS THE PROBLEM') . "\n";

// ZipArchive (for Excel)
echo 'ZipArchive  : ' . (class_exists('ZipArchive') ? 'YES' : 'NO - EXCEL WONT WORK') . "\n";

// Sessions
session_start();
$_SESSION['test'] = 1;
echo 'Sessions    : ' . (isset($_SESSION['test']) ? 'YES' : 'NO - THIS IS THE PROBLEM') . "\n";

// Directory writable?
$dir = __DIR__;
echo 'Dir writable: ' . (is_writable($dir) ? 'YES' : 'NO - CANNOT CREATE DATABASE') . "\n";
echo 'Dir path    : ' . $dir . "\n";

// Try creating SQLite DB
echo "\nTrying to create SQLite DB...\n";
try {
    $db = new PDO('sqlite:' . __DIR__ . '/transfers.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('CREATE TABLE IF NOT EXISTS _test (id INTEGER PRIMARY KEY)');
    $db->exec('DROP TABLE _test');
    echo "SQLite DB   : OK\n";
} catch (Exception $e) {
    echo "SQLite DB   : FAILED — " . $e->getMessage() . "\n";
}

echo '</pre>';
echo '<p>Once you see the results above, <strong>delete check.php from your server</strong>.</p>';
