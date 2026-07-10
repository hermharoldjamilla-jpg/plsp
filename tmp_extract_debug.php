<?php
$output = shell_exec('node ' . escapeshellarg(__DIR__ . '/mongo_requests.js') . ' fetch ' . escapeshellarg('{}'));
function extractJson($text) {
    $text = trim($text);
    $startPos = false;
    foreach ([strpos($text, '{'), strpos($text, '[')] as $pos) {
        if ($pos !== false && ($startPos === false || $pos < $startPos)) {
            $startPos = $pos;
        }
    }
    if ($startPos === false) return null;
    $stack = [];
    $inString = false;
    $escape = false;
    $len = strlen($text);
    for ($i = $startPos; $i < $len; $i++) {
        $ch = $text[$i];
        if ($escape) { $escape = false; continue; }
        if ($ch === '\\') { $escape = true; continue; }
        if ($ch === '"') { $inString = !$inString; continue; }
        if ($inString) continue;
        if ($ch === '{' || $ch === '[') { $stack[] = $ch; continue; }
        if ($ch === '}' || $ch === ']') {
            $last = array_pop($stack);
            if (($ch === '}' && $last !== '{') || ($ch === ']' && $last !== '[')) return null;
            if (empty($stack)) return substr($text, $startPos, $i - $startPos + 1);
        }
    }
    return null;
}
$clean = extractJson($output);
var_dump($output);
echo "----\n";
var_dump($clean);
if ($clean !== null) {
    $decoded = json_decode($clean, true);
    var_dump(json_last_error(), $decoded === null ? null : count($decoded));
}
