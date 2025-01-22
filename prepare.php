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
$payment_doc_id = $request_data['click_paydoc_id'] ?? null;

log_message(1, "Received request with parameters: " . json_encode($request_data));

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
    $payment_doc_id
)
) {
    log_message(2, "Missing required parameters in the request.");
    echo json_encode([
        'error' => -8,
        'error_note' => 'Missing required parameters in the request'
    ]);
    exit;
}

$generated_sign_string = md5(
    $transaction_id . $service_id_received . SECRET_KEY . $merchant_transaction_id .
    $payment_amount . $transaction_action . $timestamp
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

if ((int) $transaction_action !== 0) {
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

log_message(7, "Merchant transaction ID validation passed.");

$payment_details = [
    'amount' => $payment_amount,
    'time' => date('Y-m-d H:i:s'),
    'click_trans_id' => $transaction_id,
    'merchant_trans_id' => $merchant_transaction_id,
    'status' => 'unpay'
];

$payment_log_id = $query->insert('payments', $payment_details);

if (!$payment_log_id) {
    log_message(8, "Failed to insert payment into the payments table.");
    echo json_encode([
        'error' => -9,
        'error_note' => 'Failed to insert payment into the payments table'
    ]);
    exit;
}

log_message(9, "Payment inserted successfully with log ID: $payment_log_id.");

echo json_encode([
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $transaction_id,
    'merchant_trans_id' => $merchant_transaction_id,
    'merchant_prepare_id' => $payment_log_id,
]);

log_message(10, "Response sent successfully.");
exit;
?>