<?php

require_once 'config.php'; // Config.php faylini ulash

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// So'rovda barcha parametrlar borligini tekshirish
if (!(
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
)) {
    echo json_encode([
        'error' => -8,
        'error_note' => 'Clickdan sorovda xato'
    ]);
    exit;
}

// Xeshni tekshirish
$sign_string = md5(
    $request['click_trans_id'] .
    $request['service_id'] .
    $secret_key .
    $request['merchant_trans_id'] .
    ($request['merchant_prepare_id'] ?? '') .
    $request['amount'] .
    $request['action'] .
    $request['sign_time']
);

if ($sign_string != $request['sign_string']) {
    echo json_encode([
        'error' => -1,
        'error_note' => 'XESH TEKSHIRUVIDA XATO!'
    ]);
    exit;
}

// Action tekshirish
if ((int) $request['action'] != 1) {
    echo json_encode([
        'error' => -3,
        'error_note' => 'Action topilmadi'
    ]);
    exit;
}

// Foydalanuvchi ID va transactionni tekshirish
$user = $request['merchant_trans_id'];
if (!$user) {
    echo json_encode([
        'error' => -5,
        'error_note' => 'Foydalanuvchi mavjud emas'
    ]);
    exit;
}

$prepared = $request['merchant_prepare_id'];
if (!$prepared) {
    echo json_encode([
        'error' => -6,
        'error_note' => 'Transaction mavjud emas'
    ]);
    exit;
}

// Ma'lumotlarni saqlash
$summa = $request['amount'];
$vaqt = time();
$trans_id = $request['click_trans_id'];

try {
    $db = new Database(); // Config.php faylidagi Database sinfidan foydalanish

    // Tulovlarni ma'lumotlar bazasiga kiritish
    $insertData = [
        'user' => $user,
        'summa' => $summa,
        'vaqt' => $vaqt,
        'trans_id' => $trans_id
    ];
    $log_id = $db->insert('tulovlar', $insertData);

    // Xatolik bo'lsa
    if (!$log_id) {
        echo json_encode([
            'error' => -6,
            'error_note' => 'Malumotlar bazasiga kiritishda xato'
        ]);
        exit;
    }

    echo json_encode([
        'error' => 0,
        'error_note' => 'Muvaffaqiyat',
        'click_trans_id' => $request['click_trans_id'],
        'merchant_trans_id' => $request['merchant_trans_id'],
        'merchant_confirm_id' => $log_id,
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        'error' => -7,
        'error_note' => 'Server xatosi: ' . $e->getMessage()
    ]);
    exit;
}

?>
