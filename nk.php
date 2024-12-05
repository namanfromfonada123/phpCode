<?php
// Set the content type to JSON
header("Content-Type: application/json");

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get query parameters
    if (!isset($_GET['controlLocation'])) {
        echo json_encode(["error" => "Kindly provide controlLocation"]);
        die;
    }

    if (!isset($_GET['Mobile1'])) {
        echo json_encode(["error" => "Kindly provide Mobile1"]);
        die;
    }

    $controlLocation = $_GET['controlLocation'];
    $Mobile1 = $_GET['Mobile1'];

    try {
        // 1. Call the first API
        $firstApiData = [
            "ClientID" => "N0UxRUJEMkMwRjI3",
            "ClientKey" => "JDJhJDA4JC5XY3A3QUtOOHQxSVUvdkdKSkZXZnVPMHNjZS50T2htWmpCOGYuYzY4VFhOTzRDbVdGSWhp"
        ];

        $firstApiResponse = callPostApi("https://mhril-apis-uat.clubmahindra.com/api/v1/getToken", $firstApiData);
        logApiResponse("First API Response", $firstApiResponse);

        $responseData = json_decode($firstApiResponse['response'], true);

        if (!isset($responseData['response']) || !$responseData) {
            throw new Exception("Failed to get a successful response from API 1: " . json_encode($responseData));
        }

        if (isset($responseData['response']['data'])) {
            $accessToken = $responseData['response']['data']['access_token'];

            // 2. Call the second API
            $secondApiResponseApiData = [
                "AgeGroup" => "55-65",
                "ProspectCity" => "LeadCity",
                "email1" => "smscampaignlead@gmail.com",
                "FirstName" => "First Test",
                "LastName" => "Last Test",
                "controlLocation" => $controlLocation,
                "CampaignName" => "Last Test",
                "Mobile1" => $Mobile1,
                "ReferSourceId" => "Shivtel",
            ];

            $secondApiResponse = callPostApi(
                "https://b86.ivrobd.com/phpcode/ShivtelEncryption.php/encrypt",
                $secondApiResponseApiData,
                ["Authorization: Bearer $accessToken"]
            );
            logApiResponse("Second API Response", $secondApiResponse);

            $responseDataSecond = json_decode($secondApiResponse['response'], true);

            if (isset($responseDataSecond['response'])) {
                $decodedResponse = json_decode($responseDataSecond['response'], true);

                if (isset($decodedResponse['str'])) {
                    $str = $decodedResponse['str'];

                    // 3. Call the third API
                    $thirdApiData = ["str" => $str];

                    $thirdApiResponse = callPostApi(
                        "https://mhril-apis-uat.clubmahindra.com/api/v2/whatsApp/LeadPost",
                        $thirdApiData,
                        ["Authorization: Bearer $accessToken"]
                    );
                    logApiResponse("Third API Response", $thirdApiResponse);

                    $responseDatathird = json_decode($thirdApiResponse['response'], true);

                    header('Content-Type: application/json');
                    echo json_encode($responseDatathird['response']);
                }
            }
        } else {
            echo json_encode(["error" => "Access token not found in the response: " . json_encode($responseData)]);
        }
    } catch (Exception $e) {
        // Handle errors
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    // Handle non-GET requests
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'status' => 'error',
        'message' => 'Only GET requests are allowed.',
    ]);
}

// Function to call a POST API
function callPostApi($url, $data, $headers = [])
{
    $jsonData = json_encode($data);
    $defaultHeaders = [
        "Content-Type: application/json",
        "Content-Length: " . strlen($jsonData),
        "User-Agent: PostmanRuntime/7.42.0"
    ];
    $headers = array_merge($defaultHeaders, $headers);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disable SSL verification (Not recommended for production)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return ["error" => curl_error($ch)];
    }

    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'response' => $response,
        'httpStatusCode' => $httpStatusCode
    ];
}

// Function to log API responses
function logApiResponse($apiName, $response)
{
    file_put_contents("api_logs.txt", "$apiName: " . print_r($response, true) . PHP_EOL, FILE_APPEND);
}


