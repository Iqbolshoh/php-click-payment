<?php

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// So'rovda barcha parametrlar borligini tekshirish
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
        'error_note' => 'Clickdan so\'rovda xato'
    ));

    exit;
}

// Xeshni tekshirish
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

// Agar xesh noto'g'ri bo'lsa
if ($sign_string != $request['sign_string']) {

    echo json_encode(array(
        'error' => -1,
        'error_note' => 'XESH TEKSHIRUVIDA XATO!'
    ));

    exit;
}

// Agar action parametrining qiymati 1 bo'lmasa
if ((int) $request['action'] != 1) {

    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action topilmadi'
    ));

    exit;
}

// merchant_trans_id foydalanuvchi tomonidan kiritilgan ID
$user = $request['merchant_trans_id'];
if (!$user) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'Foydalanuvchi mavjud emas'
    ));

    exit;
}

// merchant_prepare_id - transactionning IDsi
$prepared = $request['merchant_prepare_id'];

if (!$prepared) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction mavjud emas'
    ));

    exit;
} else {
    // So'rovda yuborilgan summa va boshqa ma'lumotlar
    $summa = $request['amount'];
    $vaqt = time();
    $trans_id = $request['click_trans_id'];

    // MySQL ma'lumotlar bazasi bilan aloqani o'rnatish
    $url = "MANZIL";
    $host = "HOST";
    $user_d = "USER";
    $password = "PAROL";
    $db = "DATA_BASE_NAME";
    $link = mysqli_connect($host, $user_d, $password, $db);
    if (!$link) {
        exit(); // Agar MySQL bilan aloqa o'rnatilmasa, chiqish
    } else {
        // Tulovlarni ma'lumotlar bazasiga kiritish
        $sql = mysqli_query($link, "INSERT INTO tulovlar (user,summa, vaqt, trans_id) VALUES ('$user','$summa', '$vaqt', '$trans_id')");
        if ($sql == true) {
            // Agar SQL so'rovi muvaffaqiyatli bo'lsa
        } else {
            // Agar SQL so'rovi muvaffaqiyatsiz bo'lsa
        }
        $sql = mysqli_query($link, "SELECT * from tulovlar WHERE telefon='$user' order by id desc");
        $data = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $log_id = $data['id'];
    }
}

// Agar so'rovda xatolik bo'lsa
if ($request['error'] < 0) {
    echo json_encode(array(
        'error' => -6,
        'error_note' => 'Transaction mavjud emas'
    ));

    exit;
} else {
    // Agar barcha tekshiruvlar muvaffaqiyatli bo'lsa, natijani qaytarish
    echo json_encode(array(
        'error' => 0,
        'error_note' => 'Muvaffaqiyat',
        'click_trans_id' => $request['click_trans_id'],
        'merchant_trans_id' => $request['merchant_trans_id'],
        'merchant_confirm_id' => $log_id,
    ));

    exit;
}
?>