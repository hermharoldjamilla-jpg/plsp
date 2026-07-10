<?php
$mysqli = @new mysqli('127.0.0.1', 'root', '', 'plsp', 3306);
if (! $mysqli || $mysqli->connect_error) {
    echo json_encode(['ok' => false, 'error' => $mysqli ? $mysqli->connect_error : 'no mysqli']);
} else {
    echo json_encode(['ok' => true, 'ver' => $mysqli->server_info]);
    $mysqli->close();
}
