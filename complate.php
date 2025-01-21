<?php

include 'config.php';
$query = new Database();

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// Check if all parameters are sent
if (
    !(
        isset($request['click_trans_id']) &&
        isset($request['service_id']) &&
        isset($request['merchant_trans_id']) &&
        isset($request['amount']) &&
        isset($request['action']) &&
        isset($request['error']) &&
        isset($request['error_note']) &&
        isset($request['sign_time']) &&
        isset($request['sign_string']) &&
        isset($request['click_paydoc_id'])
    )
) {
    echo json_encode(array(
        'error' => -8,
        'error_note' => 'Error in request from click'
    ));
    exit;
}

// Check hash
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

// Check if action is valid
if ((int) $request['action'] != 1) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

// merchant_trans_id - This is the user ID that they entered in the app
// Here, we need to check if we have a user with this ID in our database
$user = $request['merchant_trans_id'];
if (!$user) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
}

// Check if the merchant_prepare_id exists
$prepared = $request['merchant_prepare_id'];
if (!$prepared) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    // Get the amount and transaction details
    $amount = $request['amount'];
    $time = time();
    $trans_id = $request['click_trans_id'];

    // Insert payment record into the database using the Database class
    $payment_data = [
        'amount' => $amount,
        'time' => date('Y-m-d H:i:s', $time),
        'click_trans_id' => $trans_id,
        'status' => 'unpay' // Set status to 'unpay' by default
    ];

    // Insert the payment data into the payments table and get the inserted log_id
    $log_id = $query->insert('payments', $payment_data);

    if (!$log_id) {
        // Failed to insert payment
        echo json_encode(array(
            'error' => -7,
            'error_note' => 'Failed to record payment'
        ));
        exit;
    }
}

// Error: money was not deducted from the user's card
if ($request['error'] < 0) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction does not exist'
    ));
    exit;
} else {
    // If everything is successful and money was deducted from the user's card
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