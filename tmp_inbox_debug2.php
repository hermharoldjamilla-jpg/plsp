<?php
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
function runMongoRequestsScript($action, $payload = []) {
    $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . 'mongo_requests.js';
    $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $cmd = 'node ' . escapeshellarg($scriptPath) . ' ' . escapeshellarg($action) . ' ' . escapeshellarg($jsonPayload);
    $output = shell_exec($cmd);
    var_dump($cmd);
    var_dump($output === null);
    var_dump($output);
    $clean = extractJson(trim($output));
    var_dump($clean);
    if ($clean === null) {
        return ['success' => false, 'error' => 'Invalid MongoDB helper response: ' . $output];
    }
    $result = json_decode($clean, true);
    var_dump(json_last_error_msg());
    if (!is_array($result)) {
        return ['success' => false, 'error' => 'Invalid JSON from MongoDB helper: ' . $clean];
    }
    return $result;
}
function getInboxRequests() {
    $result = runMongoRequestsScript('fetch');
    var_dump($result);
    if (is_array($result) && array_key_exists('success', $result)) {
        return ['items' => [], 'error' => $result['error'] ?? 'Unable to fetch requests.'];
    }
    if (!is_array($result)) {
        return ['items' => [], 'error' => 'Unexpected response from MongoDB request helper.'];
    }
    return ['items' => $result, 'error' => null];
}
$inboxData = getInboxRequests();
var_dump($inboxData);
