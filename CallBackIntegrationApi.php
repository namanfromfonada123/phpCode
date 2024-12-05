<?php
// Set the content type to JSON
header("Content-Type: application/json");

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get query parameters
    

    if (!isset($_GET['controlLocation'])){
        echo "Kindly provide  controlLocation"; die;
    }

    if (!isset($_GET['Mobile1'])){
        echo "Kindly provide  Mobile1"; die;
    }

    if (!isset($_GET['CampaignName'])){
        echo "Kindly provide  CampaignName"; die;
    }


    $controlLocation = $_GET['controlLocation'];
    $Mobile1 = $_GET['Mobile1'];
    $CampaignName = $_GET['CampaignName'];


 try {
        // 1. Call the first API
         $firstApiData = [
            "ClientID" => "N0UxRUJEMkMwRjI3",
            "ClientKey" => "JDJhJDA4JC5XY3A3QUtOOHQxSVUvdkdKSkZXZnVPMHNjZS50T2htWmpCOGYuYzY4VFhOTzRDbVdGSWhp"
        ];


            // echo $controlLocation." ".$Mobile1;die;


        $firstApiResponse = callPostApi("https://mhril-apis-uat.clubmahindra.com/api/v1/getToken", $firstApiData);


                $responseData =  is_string($firstApiResponse) ? json_decode($firstApiResponse, true) : $firstApiResponse;



            if (!$responseData['response'] || !$responseData) {

                        throw new Exception("Failed to get a successful response from API 1 ". json_encode($responseData,true));
                }


// Decode the inner JSON from the 'response' key
 			   $innerResponse = json_decode($responseData['response'], true);

            if (isset($innerResponse['data'])) {

                $accessToken = $innerResponse['data']['access_token'];

//	echo $accessToken;die;

                // 2. Encryption api call 
                    $secondApiResponseApiData = [
                        "AgeGroup" => "55-65",
                        "ProspectCity" =>"LeadCity",
                        "email1" => "smscampaignlead@gmail.com",
                        "FirstName" => "First Test",
                        "LastName" => "Last Test",
                        "controlLocation" => $controlLocation,
                        "CampaignName" => $CampaignName ,
                        "Mobile1" => $Mobile1,
                        "ReferSourceId" => "Shivtel" ,

                    ];
                    
// echo json_encode($secondApiResponseApiData);die;

                  $secondApiResponse = callPostApi( "http://192.168.12.5/phpcode/ShivtelEncryption.php/encrypt",$secondApiResponseApiData,["Authorization: Bearer $accessToken"]);
//			echo json_encode($secondApiResponse);die;

                    if (!$secondApiResponse) {
                        throw new Exception("Failed to get a response from API 2");
                    }

                    $responseDataSecond = is_string($secondApiResponse) ? json_decode($secondApiResponse, true) : $secondApiResponse;



                  if (isset($responseDataSecond['response'])) {

                        $decodedResponse = json_decode($responseDataSecond['response'], true);

                        if (isset($decodedResponse['str'])) {
                            $str = $decodedResponse['str'];


                        // request for 3 api call
                        $thirdApiData = [
                            "str"=> $str

                        ];


		//	echo json_encode($thirdApiData); die;
                           $thirdApiResponse = callPostApi("https://mhril-apis-uat.clubmahindra.com/api/v2/whatsApp/LeadPost", $thirdApiData,["Authorization: Bearer $accessToken"]);

                            if (!$thirdApiResponse) {
                                throw new Exception("Failed to get a response from API 3");
                            }


                            $responseDatathird = is_string($thirdApiResponse) ? json_decode($thirdApiResponse, true) : $thirdApiResponse;
					
 			//	echo json_encode($responseDatathird); die;
				
				$finalResponse = json_decode($responseDatathird['response'], true);
					
                             header('Content-Type: application/json');
                            echo json_encode($finalResponse);

                        }








                    }


}
             else {
                echo "Access token not found in the response.". json_encode($responseData);


            }

                } catch (Exception $e) {
                    // Handle errors
                    http_response_code(500);
                    echo json_encode(["error" => $e->getMessage()]);
                }





    // Send the response as JSON
                // echo json_encode($response);
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
                    return [
                    'response' => $response,
                    'httpStatusCode' => $httpStatusCode
                ];
                }


                return [
                    'response' => $response,
                    'httpStatusCode'=> $httpStatusCode,
		    'header' => $headers,
		    'Requested url' => $url		
                ];
            }
?>
