<?php

include 'config.php';
$query = new Database();

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

// Merchant information
$merchant_id = 'YOUR_MERCHANT_ID';
$service_id = 'YOUR_SERVICE_ID';
$merchant_user_id = 'YOUR_MERCHANT_USER_ID';
$secret_key = 'YOUR_SECRET_KEY';

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
    // Error in the request from Click
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

// Check the action field to ensure it is correct (action = 0 means prepare)
if ((int) $request['action'] != 0) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

// merchant_trans_id - This is the merchant_trans_id ID that they entered in the app
// Check if the merchant_trans_id exists (in your case, there's no users table)

$merchant_trans_id = $request['merchant_trans_id'];
if (!$merchant_trans_id) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
} else {
    // Since there is no 'users' table, we can skip this part and proceed to payment insertion.
    // Insert payment preparation data into the payments table
    $payment_data = [
        'amount' => $request['amount'],
        'time' => date('Y-m-d H:i:s', time()),
        'click_trans_id' => $request['click_trans_id'],
        'status' => 'unpay'  // Initial status is 'unpay'
    ];

    // Insert into the payments table and get the log_id
    $log_id = $query->insert('payments', $payment_data);

    if (!$log_id) {
        echo json_encode(array(
            'error' => -9,
            'error_note' => 'Failed to insert payment into the payments table'
        ));
        exit;
    }
}

// If all checks pass successfully, we save the successful preparation for payment in the database
echo json_encode(array(
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $request['click_trans_id'],
    'merchant_trans_id' => $request['merchant_trans_id'],
    'merchant_prepare_id' => $log_id,
));

exit;
?>