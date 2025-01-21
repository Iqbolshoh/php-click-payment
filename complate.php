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

if (
    !(isset($request['click_trans_id']) &&
        isset($request['service_id']) &&
        isset($request['merchant_trans_id']) &&
        isset($request['amount']) &&
        isset($request['action']) &&
        isset($request['error']) &&
        isset($request['error_note']) &&
        isset($request['sign_time']) &&
        isset($request['sign_string']) &&
        isset($request['click_paydoc_id']))
) {
    echo json_encode(array(
        'error' => -8,
        'error_note' => 'Error in request from Click'
    ));
    exit;
}

$sign_string = md5(
    $request['click_trans_id'] .
    $request['service_id'] .
    $secret_key .
    $request['merchant_trans_id'] .
    $request['merchant_prepare_id'] .
    $request['amount'] .
    $request['action'] .
    $request['sign_time']
);

if ($sign_string != $request['sign_string']) {
    echo json_encode(array(
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ));
    exit;
}

if ((int) $request['action'] != 1) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

$user_id = $request['merchant_trans_id'];
if (!$user_id) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
}

$merchant_prepare_id = $request['merchant_prepare_id'];
if (!$merchant_prepare_id) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    $amount = $request['amount'];
    $time = time();
    $trans_id = $request['click_trans_id'];

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

    } else {
        $payment_data = [
            'amount' => $amount,
            'time' => date('Y-m-d H:i:s', $time),
            'click_trans_id' => $trans_id,
            'status' => 'unpay'
        ];

        $log_id = $query->insert('payments', $payment_data);

        if (!$log_id) {
            echo json_encode(array(
                'error' => -7,
                'error_note' => 'Failed to record payment'
            ));
            exit;
        }
    }
}

if ($request['error'] < 0) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    echo json_encode(array(
        'error' => 0,
        'error_note' => 'Success',
        'click_trans_id' => $request['click_trans_id'],
        'merchant_trans_id' => $request['merchant_trans_id'],
        'merchant_confirm_id' => $log_id,
    ));
    exit;
}

?>