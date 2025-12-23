<?php
session_start(); // For CSRF and future sessions

// Security: Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// MongoDB Connection (use env var for security)
$mongoUri = getenv('MONGODB_URI') ?: 'mongodb+srv://<your_atlas_user>:<your_password>@yourcluster.mongodb.net/school_fees?retryWrites=true&w=majority'; 
// CHANGE FOR LIVE: Set MONGODB_URI in hosting environment variables
$client = new MongoDB\Client($mongoUri);
$collection = $client->school_fees->transactions;

// Remita Demo Credentials (official test values)
$merchantId = "2547916";     // DEMO - CHANGE TO YOUR LIVE MERCHANT ID
$apiKey = "1946";            // DEMO - CHANGE TO YOUR LIVE API KEY
$serviceTypeId = "4430731";  // DEMO - CHANGE TO YOUR LIVE SERVICE TYPE ID

// URLs (demo working as per latest examples)
$demoBaseUrl = "https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc";
$liveBaseUrl = "https://login.remita.net/remita/exapp/api/v1/send/api/echannelsvc"; // CHANGE: Use for live
$baseUrl = $demoBaseUrl; // Switch to $liveBaseUrl for production

// Correct RRR Generation Endpoint
$rrrEndpoint = "/merchant/api/paymentinit";

// Generate Hash (SHA-512)
function generateHash($merchantId, $serviceTypeId, $orderId, $amount, $apiKey) {
    return hash('sha512', $merchantId . $serviceTypeId . $orderId . $amount . $apiKey);
}

// Generate RRR
function generateRRR($payerName, $payerEmail, $payerPhone, $description, $amount, $orderId) {
    global $merchantId, $serviceTypeId, $apiKey, $baseUrl, $rrrEndpoint;

    $hash = generateHash($merchantId, $serviceTypeId, $orderId, $amount, $apiKey);

    $url = $baseUrl . $rrrEndpoint;

    $data = [
        "serviceTypeId" => $serviceTypeId,
        "amount" => number_format($amount, 2, '.', ''), // Ensure format
        "orderId" => $orderId,
        "payerName" => $payerName,
        "payerEmail" => $payerEmail,
        "payerPhone" => $payerPhone,
        "description" => $description
    ];

    $jsonData = json_encode($data);

    $headers = [
        "Content-Type: application/json",
        "Authorization: remitaConsumerKey=$merchantId,remitaConsumerToken=$hash"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Security
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);
    // Add httpCode for debugging
    $result['httpCode'] = $httpCode;
    return $result;
}

// Check Status (updated path)
function checkRRRStatus($rrr, $orderId) {
    global $merchantId, $apiKey, $baseUrl;

    $hash = hash('sha512', $rrr . $apiKey . $merchantId);

    $url = $baseUrl . "/$merchantId/$rrr/$orderId/$hash/status.reg";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['statuscode']) && in_array($result['statuscode'], ["00", "01"])) {
        return 'successful';
    } elseif (isset($result['statuscode'])) {
        return 'failed';
    }
    return 'pending';
}

// CSRF Token Function (security)
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}
?>