<?php

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
    $host = "HOST";
    $user_d = "USER";
    $password = "PAROL";
    $db = "DATA_BASE_NAME";
    $link = mysqli_connect($host, $user_d, $password, $db);

    // Check if the database connection was successful
    if (!$link) {
        exit();
    } else {
        // Retrieve temporary user information from the database
        $sql = mysqli_query($link, "SELECT * FROM user_temp WHERE telefon='$user' ORDER BY id DESC");
        $row = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $name = $row['ism'];
        $telefon = $user;
        $login = $row['login'];
        $parol = $row['parol'];
        $faoliyat = $row['faoliyat'];
        $rol = "user";

        // Insert user information into the 'users' table
        $sql = mysqli_query($link, "INSERT INTO users (full_name, username, password) 
            VALUES ('$name', '$login', '$parol')");

        // Retrieve the inserted user data to get the user ID (log_id)
        $sql = mysqli_query($link, "SELECT * FROM users WHERE username='$login' ORDER BY id DESC");
        $data = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $log_id = $data['id'];
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