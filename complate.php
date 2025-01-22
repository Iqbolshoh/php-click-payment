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

$request_data = $_POST;

$transaction_id = $request_data['click_trans_id'] ?? null;
$service_id_received = $request_data['service_id'] ?? null;
$merchant_transaction_id = $request_data['merchant_trans_id'] ?? null;
$payment_amount = $request_data['amount'] ?? null;
$transaction_action = $request_data['action'] ?? null;
$error_code = $request_data['error'] ?? null;
$error_message = $request_data['error_note'] ?? null;
$timestamp = $request_data['sign_time'] ?? null;
$received_sign_string = $request_data['sign_string'] ?? null;
$merchant_prepare_id = $request_data['merchant_prepare_id'] ?? null;

log_message(1, "Received request: " . json_encode($request_data));

if (
    !isset(
    $transaction_id,
    $service_id_received,
    $merchant_transaction_id,
    $payment_amount,
    $transaction_action,
    $error_code,
    $error_message,
    $timestamp,
    $received_sign_string,
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

$generated_sign_string = md5(
    $transaction_id . $service_id_received . SECRET_KEY . $merchant_transaction_id .
    $merchant_prepare_id . $payment_amount . $transaction_action . $timestamp
);

if ($generated_sign_string !== $received_sign_string) {
    log_message(3, "SIGN CHECK FAILED! Expected: $generated_sign_string, Received: $received_sign_string");
    echo json_encode([
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ]);
    exit;
}

log_message(4, "Signature validation passed.");

if ((int) $transaction_action !== 1) {
    log_message(5, "Invalid action. Action received: $transaction_action");
    echo json_encode([
        'error' => -3,
        'error_note' => 'Invalid action'
    ]);
    exit;
}

if (empty($merchant_transaction_id)) {
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

$existing_payment = $query->select('payments', '*', 'click_trans_id = ?', [$transaction_id], 's');

if (empty($existing_payment)) {
    log_message(8, "Transaction does not exist in the database for click_trans_id: $transaction_id");
    echo json_encode([
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ]);
    exit;
}

$payment_log_id = $existing_payment[0]['id'];

if ($existing_payment[0]['status'] === 'paid') {
    log_message(9, "Payment already completed for click_trans_id: $transaction_id");
    echo json_encode([
        'error' => -7,
        'error_note' => 'Payment already completed'
    ]);
    exit;
}

$payment_update_data = [
    'status' => 'paid',
    'time' => date('Y-m-d H:i:s')
];

$update_result = $query->update('payments', $payment_update_data, 'click_trans_id = ?', [$transaction_id], 's');

if (!$update_result) {
    log_message(10, "Failed to update payment status for click_trans_id: $transaction_id");
    echo json_encode([
        'error' => -7,
        'error_note' => 'Failed to update payment status'
    ]);
    exit;
}

log_message(11, "Payment status updated successfully for click_trans_id: $transaction_id");

$response_data = [
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $transaction_id,
    'merchant_trans_id' => $merchant_transaction_id,
    'merchant_confirm_id' => $payment_log_id,
];

log_message(12, "Response sent: " . json_encode($response_data));
echo json_encode($response_data);
exit;
?>