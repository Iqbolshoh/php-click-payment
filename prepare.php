<?php

include 'config.php';
$query = new Database();

error_reporting(0);
header('Content-Type: application/json; charset=UTF-8');

$request = $_POST;

$merchant_id = 'YOUR_MERCHANT_ID';
$service_id = 'YOUR_SERVICE_ID';
$merchant_user_id = 'YOUR_MERCHANT_USER_ID';
$secret_key = 'YOUR_SECRET_KEY';

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

if (
    empty($click_trans_id) || empty($service_id_request) || empty($merchant_trans_id) ||
    empty($amount) || empty($action) || empty($error) || empty($error_note) ||
    empty($sign_time) || empty($sign_string_request) || empty($click_paydoc_id)
) {
    echo json_encode([
        'error' => -8,
        'error_note' => 'Error in request from Click'
    ]);
    exit;
}

$sign_string = md5(
    $click_trans_id . $service_id_request . $secret_key . $merchant_trans_id .
    $amount . $action . $sign_time
);

if ($sign_string !== $sign_string_request) {
    echo json_encode([
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ]);
    exit;
}

if ((int) $action !== 0) {
    echo json_encode([
        'error' => -3,
        'error_note' => 'Invalid action'
    ]);
    exit;
}

if (empty($merchant_trans_id)) {
    echo json_encode([
        'error' => -5,
        'error_note' => 'User does not exist'
    ]);
    exit;
}

$payment_data = [
    'amount' => $amount,
    'time' => date('Y-m-d H:i:s'),
    'click_trans_id' => $click_trans_id,
    'merchant_trans_id' => $merchant_trans_id,
    'status' => 'unpay'
];

$log_id = $query->insert('payments', $payment_data);

if (!$log_id) {
    echo json_encode([
        'error' => -9,
        'error_note' => 'Failed to insert payment into the payments table'
    ]);
    exit;
}

echo json_encode([
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $click_trans_id,
    'merchant_trans_id' => $merchant_trans_id,
    'merchant_prepare_id' => $log_id,
]);

exit;

?>