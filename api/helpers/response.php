<?php

function jsonResponse($status, $message, $data = null) {
    http_response_code($status);
    return json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
}
