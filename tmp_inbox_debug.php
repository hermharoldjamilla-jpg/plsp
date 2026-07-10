<?php
$scriptPath = __DIR__ . '/inbox.php';
$output = shell_exec('php ' . escapeshellarg($scriptPath));
file_put_contents(__DIR__ . '/tmp_inbox_render.html', $output);
echo "DONE\n";
