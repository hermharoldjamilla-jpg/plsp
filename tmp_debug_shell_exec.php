<?php
$scriptPath = __DIR__ . '/mongo_requests.js';
$cmd = 'node ' . escapeshellarg($scriptPath) . ' fetch ' . escapeshellarg('{}');
echo "CMD: $cmd\n";
$output = shell_exec($cmd);
var_dump($output);
if ($output !== null) {
    if (preg_match_all('/(\{[\s\S]*?\})/', $output, $matches)) {
        echo "OBJECTS: " . count($matches[1]) . "\n";
        var_dump($matches[1]);
    }
    if (preg_match_all('/(\[[\s\S]*?\])/', $output, $matches2)) {
        echo "ARRAYS: " . count($matches2[1]) . "\n";
        var_dump($matches2[1]);
    }
}
