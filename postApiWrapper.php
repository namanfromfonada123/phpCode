<?php
// Set the content type to JSON
header("Content-Type: application/json");


// Define the log file path (relative directory)
$logFile = 'logs/postApiWrapper_log_' . date('Y-m') . '.log'; // Log file with date
date_default_timezone_set('Asia/Kolkata'); // Replace with your time zone

// Function to log messages
function logMessage($message) {
    global $logFile;
    
    // Ensure the log directory exists; create if it doesn't
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }

    // Prepare log entry
    $timestamp = date("Y-m-d H:i:s");
    $logEntry = "[$timestamp] $message" . PHP_EOL;

    // Write log entry to the log file
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}


// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get query parameters
    

    if (!isset($_GET['key'])){
        echo "Kindly provide  key"; die;
    }

    if (!isset($_GET['name'])){
        echo "Kindly provide  name"; die;
    }

    if (!isset($_GET['mobile'])){
        echo "Kindly provide  mobile"; die;
    }
    if (!isset($_GET['lender_name'])){
        echo "Kindly provide  lender_name"; die;
    }

    if (!isset($_GET['campaign_name'])){
        echo "Kindly provide  campaign_name"; die;
    }

    if (!isset($_GET['digitsPressed'])){
        echo "Kindly provide  digitsPressed"; die;
    }
    if (!isset($_GET['disposition'])){
        echo "Kindly provide  disposition"; die;
    }

    if (!isset($_GET['amount'])){
        echo "Kindly provide  amount"; die;
    }

    if (!isset($_GET['emi'])){
        echo "Kindly provide  emi"; die;
    }
      if (!isset($_GET['tenure'])){
        echo "Kindly provide  tenure"; die;
    }

    if (!isset($_GET['leadType'])){
        echo "Kindly provide  leadType"; die;
    }

    if (!isset($_GET['aff_id'])){
        echo "Kindly provide  aff_id"; die;
    }

     if (!isset($_GET['uuid'])){
        echo "Kindly provide  uuid"; die;
    }


    $key = $_GET['key'];
    $name = $_GET['name'];
    $mobile = $_GET['mobile'];
    $lender_name = $_GET['lender_name'];
    $campaign_name = $_GET['campaign_name'];
    $digitsPressed = $_GET['digitsPressed'];
    $disposition = $_GET['disposition'];
    $amount = $_GET['amount'];
    $emi = $_GET['emi'];
    $tenure = $_GET['tenure'];
    $leadType = $_GET['leadType'];
    $aff_id = $_GET['aff_id'];
    $uuid = $_GET['uuid'];



 // Log request data
    logMessage("Received request with parameters: key=$key, name=$name, mobile=$mobile, lender_name=$lender_name, campaign_name=$campaign_name, digitsPressed=$digitsPressed, disposition=$disposition, amount=$amount, emi=$emi, tenure=$tenure, leadType=$leadType, aff_id=$aff_id, uuid=$uuid");



                // 2. Encryption api call 
                    $RequestData = [
                        "key" => $key,
                        "name" =>$name,
                        "mobile" => $mobile,
                        "lender_name" => $lender_name,
                        "campaign_name" =>$campaign_name,
                        "digitsPressed" => $digitsPressed,
                        "disposition" => $disposition,
                        "amount" => $amount,
                        "emi" =>  $emi ,
                        "tenure" => $tenure,
                        "leadType" =>$leadType,
                        "aff_id" => $aff_id,
                        "uuid" => $uuid
                    ];


                     
                try {
                      $BackendApiResponse = callPostApi( "https://msg-api.digitmoney.in/api/v1/ivr_data/fdData-onlyPressOne",$RequestData);

                   if (!$BackendApiResponse)
                    {
                           logMessage("Failed to get a response from the API. for Request Data : ".$RequestData);
                            throw new Exception("Failed to get a response from API 2");
                    }


                    $responseDataSecond = is_string($BackendApiResponse) ? json_decode($BackendApiResponse, true) : $BackendApiResponse;


                         // Log API response
                            logMessage("Received API response: " . json_encode($responseDataSecond));
                            logMessage("",true);

                         echo json_encode($responseDataSecond);

                } catch (Exception $e) {
                    // Log error
                        logMessage("Error: " . $e->getMessage());
                        logMessage("",true);
                        echo json_encode(["error" => $e->getMessage()]);

                }

                






}


        // Function to call a POST API
            function callPostApi($url, $data, $headers = [])
            {
                $jsonData = json_encode($data);
                $defaultHeaders = [
                    "Content-Type: application/json",
                    "Content-Length: " . strlen($jsonData),
                   // "User-Agent: PostmanRuntime/7.42.0"
                ];

                    // echo "headers : ". json_encode($headers);die;


                $headers = array_merge($defaultHeaders, $headers);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                $response = curl_exec($ch);

                if (curl_errno($ch)) {
                  
                    return false;
                }

                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpStatusCode==200) {
                    return $response;
                }


                return [
                    'response' => $response,
                    'httpStatusCode'=> $httpStatusCode,
		            'header' => $headers,
		            'Requested url' => $url		
                ];


            }
?>
