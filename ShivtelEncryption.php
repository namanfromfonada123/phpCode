<?php
// index.php

header('Content-Type: application/json');

// Getting the request method and path
$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handling POST requests
if ($requestMethod == 'POST') {
    // Getting the raw POST data
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // Checking for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }

    // Removing leading slash from the path
    $path = ltrim($path, '/');
    // echo $path;die;

    // Routing the request based on the path
    if (strpos($path, '/encrypt') !== false) {
 

   // echo $path;die;


        // The entire input is the data to be encrypted
        $data = $input;

        // Optionally extracting 'encryption_key' if provided
        $key = false;
        if (isset($data['encryption_key'])) {
            $key = $data['encryption_key'];
            unset($data['encryption_key']);
        }

        // Serializing the data to JSON string
        $clearTextData = json_encode($data);

        // Encrypting the data
        $encryptedData = encryptionData($clearTextData, $key);

            // echo json_encode($data);die;


        if ($encryptedData === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Encryption failed']);
            exit;
        }

        // Returning the encrypted data
        echo json_encode(['str' => $encryptedData]);

    } elseif ($path == 'decrypt') {
        // Checking if 'str' is provided
        if (!isset($input['str'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No data provided']);
            exit;
        }

        // Getting the encrypted data and key
        $cipherData = $input['str'];
        $key = isset($input['encryption_key']) ? $input['encryption_key'] : false;

        // Decrypting the data
        $decryptedData = decryptionData($cipherData, $key);

        if ($decryptedData === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Decryption failed']);
            exit;
        }

        // Deserializing the JSON string back to an array
        $originalData = json_decode($decryptedData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(500);
            echo json_encode(['error' => 'Decryption succeeded but failed to parse JSON']);
            exit;
        }

        // Returning the original data
        echo json_encode($originalData);

    } else {
        // If the path is not recognized
        http_response_code(404);
        echo json_encode(['error' => 'Not Found!!!!!']);
    }

} else {
    // If the request method is not POST
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}

// Existing Functions

function encryptionData($clearTextData, $encryptionKey = false)
{
    try {

        $encryptionAlgorithm = 'AES-256-CBC'; 
        $defaultKey = 'nYSe3niV7OA5aBn1RgooyShivtel';     

        $encryptionKey = $encryptionKey == false ? $defaultKey : $encryptionKey;
        $encryptionKey = hash('sha256', $encryptionKey, true);
        $encryptionKey = substr($encryptionKey, 0, 32);
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $encryptedData = base64_encode(openssl_encrypt($clearTextData, $encryptionAlgorithm, $encryptionKey, OPENSSL_RAW_DATA, $iv));
        return $encryptedData;
    } catch (Exception $ex) {
        return false;
    }
}

function decryptionData($cipherData, $decryptionKey = false)
{
    try {

        $decryptionAlgorithm = 'AES-256-CBC'; 
        $defaultKey = 'nYSe3niV7OA5aBn1RgooyShivtel';     

        $decryptionKey = $decryptionKey == false ? $defaultKey : $decryptionKey;
        $decryptionKey = hash('sha256', $decryptionKey, true);
        $decryptionKey = substr($decryptionKey, 0, 32);
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
        $decryptedData = openssl_decrypt(base64_decode($cipherData), $decryptionAlgorithm, $decryptionKey, OPENSSL_RAW_DATA, $iv);
        return $decryptedData;
    } catch (Exception $ex) {
        return false;
    }
}
