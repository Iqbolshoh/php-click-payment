<?php
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');

include 'config.php';
$query = new Database();

function log_message($step, $message)
{
    $log_file = 'prepare_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] Step $step: $message" . PHP_EOL;
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

$request = $_POST;

$click_trans_id = $request['click_trans_id'] ?? null;
$service_id_request = $request['service_id'] ?? null;
$merchant_trans_id = $request['merchant_trans_id'] ?? null;
$amount = $request['amount'] ?? null;
$action = $request['action'] ?? null;
$error = $request['error'] ?? null;
$error_note = $request['error_note'] ?? null;
$sign_time = $request['sign_time'] ?? null;
$sign_string_request = $request['sign_string'] ?? null;
$click_paydoc_id = $request['click_paydoc_id'] ?? null;

log_message(1, "Received request with parameters: " . json_encode($request));

if (
    !isset(
    $click_trans_id,
    $service_id_request,
    $merchant_trans_id,
    $amount,
    $action,
    $error,
    $error_note,
    $sign_time,
    $sign_string_request,
    $click_paydoc_id
)
) {
    log_message(2, "Missing required parameters in the request.");
    echo json_encode([
        'error' => -8,
        'error_note' => 'Missing required parameters in the request'
    ]);
    exit;
}

$sign_string = md5(
    $click_trans_id . $service_id_request . SECRET_KEY . $merchant_trans_id .
    $amount . $action . $sign_time
);

if ($sign_string !== $sign_string_request) {
    log_message(3, "SIGN CHECK FAILED! Expected: $sign_string, Received: $sign_string_request");
    echo json_encode([
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ]);
    exit;
}

log_message(4, "Signature validation passed.");

if ((int) $action !== 0) {
    log_message(5, "Invalid action. Action received: $action");
    echo json_encode([
        'error' => -3,
        'error_note' => 'Invalid action'
    ]);
    exit;
}

if (empty($merchant_trans_id)) {
    log_message(6, "Merchant transaction ID is missing.");
    echo json_encode([
        'error' => -5,
        'error_note' => 'User does not exist'
    ]);
    exit;
}

log_message(7, "Merchant transaction ID validation passed.");

$payment_data = [
    'amount' => $amount,
    'time' => date('Y-m-d H:i:s'),
    'click_trans_id' => $click_trans_id,
    'merchant_trans_id' => $merchant_trans_id,
    'status' => 'unpay'
];

$log_id = $query->insert('payments', $payment_data);

if (!$log_id) {
    log_message(8, "Failed to insert payment into the payments table.");
    echo json_encode([
        'error' => -9,
        'error_note' => 'Failed to insert payment into the payments table'
    ]);
    exit;
}

log_message(9, "Payment inserted successfully with log ID: $log_id.");

echo json_encode([
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $click_trans_id,
    'merchant_trans_id' => $merchant_trans_id,
    'merchant_prepare_id' => $log_id,
]);

log_message(10, "Response sent successfully.");
exit;
?>