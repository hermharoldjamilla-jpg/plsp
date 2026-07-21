<?php
$script = __DIR__ . '/mongo_auth.js';
$cmd = sprintf('node %s %s %s %s', escapeshellarg($script), escapeshellarg('student'), escapeshellarg('test@example.com'), escapeshellarg('password'));
$output = shell_exec($cmd);
var_dump($cmd);
echo "\nOUTPUT_START\n";
var_dump($output);
echo "\nOUTPUT_END\n";
