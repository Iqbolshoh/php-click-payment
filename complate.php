<?php
error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');

include 'config.php';
$query = new Database();

function log_message($step, $message)
{
    $log_file = 'complete_log.txt';
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
$merchant_prepare_id = $request['merchant_prepare_id'] ?? null;

log_message(1, "Received request: " . json_encode($request));

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
    $merchant_prepare_id
)
) {
    log_message(2, "Missing required parameters in the request.");
    echo json_encode([
        'error' => -8,
        'error_note' => 'Error in request from Click'
    ]);
    exit;
}

$sign_string = md5(
    $click_trans_id . $service_id_request . SECRET_KEY . $merchant_trans_id .
    $merchant_prepare_id . $amount . $action . $sign_time
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

if ((int) $action !== 1) {
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

if (empty($merchant_prepare_id)) {
    log_message(7, "Merchant prepare ID is missing.");
    echo json_encode([
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ]);
    exit;
}

$existing_payment = $query->select('payments', '*', 'click_trans_id = ?', [$click_trans_id], 's');

if (empty($existing_payment)) {
    log_message(8, "Transaction does not exist in the database for click_trans_id: $click_trans_id");
    echo json_encode([
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ]);
    exit;
}

$log_id = $existing_payment[0]['id'];

if ($existing_payment[0]['status'] === 'paid') {
    log_message(9, "Payment already completed for click_trans_id: $click_trans_id");
    echo json_encode([
        'error' => -7,
        'error_note' => 'Payment already completed'
    ]);
    exit;
}

$payment_update = [
    'status' => 'paid',
    'time' => date('Y-m-d H:i:s')
];

$update_result = $query->update('payments', $payment_update, 'click_trans_id = ?', [$click_trans_id]);

if (!$update_result) {
    log_message(10, "Failed to update payment status for click_trans_id: $click_trans_id");
    echo json_encode([
        'error' => -7,
        'error_note' => 'Failed to update payment status'
    ]);
    exit;
}

log_message(11, "Payment status updated successfully for click_trans_id: $click_trans_id");

$response = [
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $click_trans_id,
    'merchant_trans_id' => $merchant_trans_id,
    'merchant_confirm_id' => $log_id,
];

log_message(12, "Response sent: " . json_encode($response));
echo json_encode($response);
exit;
?>