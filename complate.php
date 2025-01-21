<?php

include 'config.php';
$query = new Database();

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'YOUR_MERCHANT_ID';
$service_id = 'YOUR_SERVICE_ID';
$merchant_user_id = 'YOUR_MERCHANT_USER_ID';
$secret_key = 'YOUR_SECRET_KEY';

$click_trans_id = isset($request['click_trans_id']) ? $request['click_trans_id'] : null;
$service_id_request = isset($request['service_id']) ? $request['service_id'] : null;
$merchant_trans_id = isset($request['merchant_trans_id']) ? $request['merchant_trans_id'] : null;
$amount = isset($request['amount']) ? $request['amount'] : null;
$action = isset($request['action']) ? $request['action'] : null;
$error = isset($request['error']) ? $request['error'] : null;
$error_note = isset($request['error_note']) ? $request['error_note'] : null;
$sign_time = isset($request['sign_time']) ? $request['sign_time'] : null;
$sign_string_request = isset($request['sign_string']) ? $request['sign_string'] : null;
$merchant_prepare_id = isset($request['merchant_prepare_id']) ? $request['merchant_prepare_id'] : null;

if (
    !(isset($click_trans_id) &&
        isset($service_id_request) &&
        isset($merchant_trans_id) &&
        isset($amount) &&
        isset($action) &&
        isset($error) &&
        isset($error_note) &&
        isset($sign_time) &&
        isset($sign_string_request) &&
        isset($merchant_prepare_id))
) {
    echo json_encode(array(
        'error' => -8,
        'error_note' => 'Error in request from Click'
    ));
    exit;
}

$sign_string = md5(
    $click_trans_id .
    $service_id_request .
    $secret_key .
    $merchant_trans_id .
    $merchant_prepare_id .
    $amount .
    $action .
    $sign_time
);

if ($sign_string != $sign_string_request) {
    echo json_encode(array(
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ));
    exit;
}

if ((int) $action != 1) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

if (!$merchant_trans_id) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
}

if (!$merchant_prepare_id) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    $time = time();
    $trans_id = $click_trans_id;

    $existing_payment = $query->select('payments', '*', 'click_trans_id = ?', [$trans_id], 's');

    if (!empty($existing_payment)) {
        $payment_update = [
            'status' => 'paid',
            'time' => date('Y-m-d H:i:s', $time)
        ];

        $update_result = $query->update('payments', $payment_update, 'click_trans_id = ?', [$trans_id]);

        if (!$update_result) {
            echo json_encode(array(
                'error' => -7,
                'error_note' => 'Failed to update payment status'
            ));
            exit;
        }
    }
}

if ($error < 0) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    echo json_encode(array(
        'error' => 0,
        'error_note' => 'Success',
        'click_trans_id' => $click_trans_id,
        'merchant_trans_id' => $merchant_trans_id,
        'merchant_confirm_id' => $log_id,
    ));
    exit;
}

?>