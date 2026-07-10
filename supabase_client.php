<?php
function loadEnvFile(): void
{
    $envPath = __DIR__ . '/.env';
    if (!is_file($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $name = trim($name);
        $value = trim($value);
        if ($name === '') {
            continue;
        }

        $value = preg_replace('/^(["\'])(.*)\1$/', '$2', $value) ?? $value;
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }

        if (!isset($_ENV[$name])) {
            $_ENV[$name] = $value;
        }
        if (!isset($_SERVER[$name])) {
            $_SERVER[$name] = $value;
        }
    }
}

loadEnvFile();

function supabaseRequest(string $path, array $query = [], string $method = 'GET', ?array $body = null): array
{
    $baseUrl = trim(getenv('SUPABASE_URL') ?: '');
    $serviceKey = trim(getenv('SUPABASE_SERVICE_ROLE_KEY') ?: getenv('SUPABASE_ANON_KEY') ?: '');

    $placeholderUrl = strpos($baseUrl, 'your-project.supabase.co') !== false;
    $placeholderKey = strpos($serviceKey, 'your_service_role_key_here') !== false || strpos($serviceKey, 'your_anon_key_here') !== false;

    if ($baseUrl === '' || $serviceKey === '' || $placeholderUrl || $placeholderKey) {
        throw new RuntimeException('Supabase credentials are not configured. Set the real values in .env or your server environment.');
    }

    $url = rtrim($baseUrl, '/') . '/rest/v1' . $path;
    if (!empty($query)) {
        $url .= '?' . http_build_query($query);
    }

    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'apikey: ' . $serviceKey,
        'Authorization: Bearer ' . $serviceKey,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if ($method !== 'GET' && $body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError !== '') {
        throw new RuntimeException('Supabase request failed: ' . $curlError);
    }

    if ($statusCode >= 400) {
        throw new RuntimeException('Supabase request failed with status ' . $statusCode . ': ' . $response);
    }

    if ($response === '') {
        return [];
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}

function supabaseFindOne(string $table, string $field, string $value): ?array
{
    $query = [
        'select' => '*',
        'limit' => '1',
        $field => 'eq.' . $value,
    ];

    $rows = supabaseRequest('/' . ltrim($table, '/'), $query);
    return is_array($rows) && isset($rows[0]) ? $rows[0] : null;
}

function getSupabaseAdminTable(): string
{
    return getenv('SUPABASE_ADMIN_TABLE') ?: getenv('SUPABASE_ADMIN_COLLECTION') ?: 'admin';
}

function supabaseFindAdminByEmail(string $email): ?array
{
    $table = getSupabaseAdminTable();
    $candidateFields = ['email', 'admin_email', 'email_address', 'username'];

    foreach ($candidateFields as $field) {
        $admin = supabaseFindOne($table, $field, $email);
        if ($admin) {
            return $admin;
        }
    }

    return null;
}

function verifyStoredPassword(string $storedPassword, string $inputPassword): bool
{
    if ($storedPassword === '' || $inputPassword === '') {
        return false;
    }

    $info = password_get_info($storedPassword);
    if (is_array($info) && isset($info['algo']) && $info['algo'] !== 0) {
        return password_verify($inputPassword, $storedPassword);
    }

    if (strpos($storedPassword, '$2') === 0) {
        return password_verify($inputPassword, $storedPassword);
    }

    return hash_equals($storedPassword, $inputPassword);
}
