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

// Check if all parameters are sent in the request
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

// Check hash string
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

// Check the action field to ensure it is correct
if ((int) $request['action'] != 0) {
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
} else {
    // Retrieve temporary user information from the database using the select() method
    $user_data = $query->select('users', '*', 'username = ?', [$user], 's');
    if (empty($user_data)) {
        echo json_encode(array(
            'error' => -5,
            'error_note' => 'User does not exist in temporary users'
        ));
        exit;
    }

    $name = $user_data[0]['ism'];
    $login = $user_data[0]['login'];
    $parol = $user_data[0]['parol'];

    // Insert user information into the 'users' table using the insert() method
    $user_insert_data = [
        'full_name' => $name,
        'username' => $login,
        'password' => $parol
    ];
    $log_id = $query->insert('users', $user_insert_data);

    if (!$log_id) {
        echo json_encode(array(
            'error' => -9,
            'error_note' => 'Failed to insert user into the users table'
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