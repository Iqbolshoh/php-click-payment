<?php

include 'config.php';
$query = new Database();

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

// Merchant information
$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// Check if all required parameters are present
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

// Check hash for data authenticity
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

// Validate the sign string to check its authenticity
if ($sign_string != $request['sign_string']) {
    echo json_encode(array(
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ));
    exit;
}

// Check if action is valid (action = 1 means successful payment)
if ((int) $request['action'] != 1) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

// Check if user ID (merchant_trans_id) is provided
$user_id = $request['merchant_trans_id'];
if (!$user_id) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
}

// Check if merchant_prepare_id exists (if the transaction exists)
$prepared = $request['merchant_prepare_id'];
if (!$prepared) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    // Get the payment details from the request
    $amount = $request['amount'];
    $time = time();
    $trans_id = $request['click_trans_id'];

    $payment_data = [
        'amount' => $amount,
        'time' => date('Y-m-d H:i:s', $time),
        'click_trans_id' => $trans_id,
        'status' => 'unpay'
    ];

    // Insert the payment data into the payments table
    $log_id = $query->insert('payments', $payment_data);

    // Check if the payment was successfully inserted
    if (!$log_id) {
        echo json_encode(array(
            'error' => -7,
            'error_note' => 'Failed to record payment'
        ));
        exit;
    }
}

// Check if there was an error (payment was not deducted from the user's card)
if ($request['error'] < 0) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    // If everything is successful and the payment was deducted from the user's card
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