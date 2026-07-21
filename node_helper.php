<?php
// Shared Node helper utilities for invoking node helper scripts reliably
function locate_node_executable(): string {
    $nodePath = getenv('NODE_PATH') ?: '';
    if ($nodePath) return $nodePath;

    if (stripos(PHP_OS_FAMILY, 'Windows') === 0) {
        $where = @shell_exec('where node 2>&1');
        if ($where) {
            $paths = preg_split('/[\r\n]+/', trim($where));
            return $paths[0] ?: 'node';
        }
    } else {
        $which = @shell_exec('command -v node 2>&1');
        if ($which) return trim($which);
    }

    return 'node';
}

function run_node_helper_script(string $scriptPath, array $args = []): array {
    $node = locate_node_executable();
    $parts = array_merge([$node, $scriptPath], $args);
    $command = implode(' ', array_map('escapeshellarg', $parts));
    $cwd = is_dir(dirname($scriptPath)) ? dirname($scriptPath) : __DIR__;

    $descriptors = [
        ["pipe", "r"],
        ["pipe", "w"],
        ["pipe", "w"],
    ];

    $env = $_ENV;
    if (!isset($env['PATH']) || $env['PATH'] === '') {
        $env['PATH'] = getenv('PATH') ?: '';
    }
    if (!isset($env['NODE_PATH']) || $env['NODE_PATH'] === '') {
        $nodePath = getenv('NODE_PATH');
        if ($nodePath) {
            $env['NODE_PATH'] = $nodePath;
        }
    }

    $process = @proc_open($command, $descriptors, $pipes, $cwd, $env);
    if (!is_resource($process)) {
        return ['success' => false, 'stdout' => '', 'stderr' => 'Unable to start helper process', 'exitCode' => -1];
    }

    if (is_resource($pipes[0])) { fclose($pipes[0]); }
    $stdout = is_resource($pipes[1]) ? stream_get_contents($pipes[1]) : '';
    $stderr = is_resource($pipes[2]) ? stream_get_contents($pipes[2]) : '';
    if (is_resource($pipes[1])) { fclose($pipes[1]); }
    if (is_resource($pipes[2])) { fclose($pipes[2]); }

    $exitCode = proc_close($process);
    return ['success' => $exitCode === 0, 'stdout' => $stdout, 'stderr' => $stderr, 'exitCode' => $exitCode];
}

function parse_helper_json(string $stdout) {
    $text = trim($stdout);
    if ($text === '') {
        return null;
    }

    $decoded = json_decode($text, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $decoded;
    }

    $length = strlen($text);
    $startPositions = [];
    for ($i = 0; $i < $length; $i++) {
        if ($text[$i] === '{' || $text[$i] === '[') {
            $startPositions[] = $i;
        }
    }

    foreach ($startPositions as $start) {
        $stack = [];
        $inString = false;
        $escape = false;
        $openingChar = $text[$start];
        $closingChar = $openingChar === '{' ? '}' : ']';

        for ($j = $start; $j < $length; $j++) {
            $char = $text[$j];
            if ($escape) {
                $escape = false;
                continue;
            }
            if ($char === '\\') {
                $escape = true;
                continue;
            }
            if ($char === '"') {
                $inString = !$inString;
                continue;
            }
            if ($inString) {
                continue;
            }
            if ($char === $openingChar) {
                $stack[] = $char;
                continue;
            }
            if ($char === $closingChar) {
                array_pop($stack);
                if (empty($stack)) {
                    $candidate = substr($text, $start, $j - $start + 1);
                    $decoded = json_decode($candidate, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }
                    break;
                }
            }
        }
    }

    return null;
}

function run_mongo_helper(string $scriptName, array $args = [], ?string $jsonPayload = null): array {
    $scriptPath = __DIR__ . DIRECTORY_SEPARATOR . $scriptName;
    if ($jsonPayload !== null) {
        $args[] = $jsonPayload;
    }

    $result = run_node_helper_script($scriptPath, $args);
    $data = parse_helper_json($result['stdout'] ?? '');
    if ($data !== null) {
        return [
            'success' => true,
            'data' => $data,
            'stdout' => $result['stdout'],
            'stderr' => $result['stderr'],
            'exitCode' => $result['exitCode'],
        ];
    }

    return [
        'success' => false,
        'error' => trim($result['stderr'] ?: 'Invalid JSON from helper script.'),
        'stdout' => $result['stdout'],
        'stderr' => $result['stderr'],
        'exitCode' => $result['exitCode'],
    ];
}

?>
