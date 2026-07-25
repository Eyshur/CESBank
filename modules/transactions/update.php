<?php http_response_code(405); header('Content-Type: application/json'); echo json_encode(['success' => false, 'message' => 'Transaction records are immutable and cannot be edited.']); ?>
