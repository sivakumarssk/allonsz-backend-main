<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use \Auth;

use App\Models\User;
use DB;
use \Session;
use Mail;
use \Str;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

use \Hash;

class TruthScreenController extends Controller
{
    const API_URL = 'https://www.truthscreen.com/v1/apicall/nid/aadhar_get_otp';
    const CIPHER_METHOD = 'aes-128-cbc';
    const CIPHER_KEY_LEN = 16;

    /**
     * Generate the encryption key from the token using SHA-512 hashing.
     */
    private function generateEncryptionKey($token)
    {
        $hashedKey = hash('sha512', $token, false);
        return substr($hashedKey, 0, self::CIPHER_KEY_LEN);
    }

    /**
     * Encrypt the input data using AES-128 encryption.
     */
    private function encryptData($key, $data)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
        $encryptedData = openssl_encrypt(
            $data,
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($encryptedData) . ':' . base64_encode($iv);
    }

    /**
     * Decrypt the response data using AES-128 decryption.
     */
    private function decryptData($key, $encryptedPayload)
    {
        [$encryptedData, $iv] = explode(':', $encryptedPayload);

        return openssl_decrypt(
            base64_decode($encryptedData),
            self::CIPHER_METHOD,
            $key,
            OPENSSL_RAW_DATA,
            base64_decode($iv)
        );
    }

    /**
     * Handle the TruthScreen Search API request.
     */
    public function search(Request $request)
    {
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        // $token = env('AUTHBRIDGE_TOKEN', 'India@2608');
        $token = env('AUTHBRIDGE_TOKEN', 'Hello@123!');
        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'transID' => $request->transID, // Default or provided
            'docType' => $request->docType,         // Mandatory field
            'aadharNo' => $request->docNumber,        // Mandatory field
        ];

        if (!$input['aadharNo']) {
            return response()->json([
                'status' => 'error',
                'message' => 'docNumber is required.',
            ], 400);
        }

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->post(self::API_URL, $payload);

        // Step 5: Handle the API response
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($decryptedData, true),
            ],200);
        }
        
        $responseData = $response->json('responseData');
        
        if($responseData){
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            return response()->json([
                'status' => 'error',
                'data' => json_decode($decryptedData, true),
            ],$response->status());
        }

        return response()->json([
            'status' => 'error',
            'message' => $response->body(),
        ], $response->status());
    }
    
    public function get_aadhar_validation_link(Request $request)
    {
        $rules = [
            'aadhar_no' => [
                'required',
                'numeric',
                'digits:12',
                'regex:/^[2-9]{1}[0-9]{11}$/', // Ensures the first digit is between 2-9 (valid Aadhar format)
            ],
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        
        $user = User::where('id','!=',Auth::User()->id)->where('aadhar_no',$request->aadhar_no)->where('aadhar_status','Verified')->first();
        
        if($user){
            return response()->json([
                'error' => 'This aadhar is already taken'
            ], 422);
        }
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        $token = env('AUTHBRIDGE_TOKEN', 'India@2608');

        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'trans_id' => $this->generateUniqueTransId(), // Default or provided
            'doc_type' => 472,         // Mandatory field
            'action'  => 'LINK'        // Mandatory field
        ];

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->withOptions([
    'verify' => false, // Disable SSL verification (DEVELOPMENT ONLY!)
])->post('https://www.truthscreen.com/api/v1.0/eaadhaardigilocker/', $payload);

        // Step 5: Handle the API response
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            
            $user = Auth::User();
            $user->aadhar_no = $request->aadhar_no;
            $user->aadhar_status = 'Pending';
            $user->save();
            
            return response()->json([
                'status' => 'success',
                'data' => json_decode($decryptedData, true),
            ],200);
        }

        return response()->json([
            'status' => 'error',
            'message' => json_decode($response->body(),true),
        ], 422);
    }
    
    public function validate_aadhar(Request $request)
    {
        
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        $token = env('AUTHBRIDGE_TOKEN', 'India@2608');

        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'ts_trans_id' => $request->ts_trans_id,
            'doc_type' => 472,
            'action' => 'STATUS' 
        ];
        
        if (!$input['ts_trans_id']) {
            return response()->json([
                'status' => 'error',
                'message' => 'tsTransId is required.',
            ], 400);
        }

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->post('https://www.truthscreen.com/api/v1.0/eaadhaardigilocker', $payload);
        
        // Step 5: Handle the API response
        
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            
            $data = json_decode($decryptedData, true);
            if($data['data'][$request->ts_trans_id]['final_status'] == 'Completed'){
                $user = Auth::User();
                $user->aadhar_details = $data['data'];
                $user->aadhar_status = 'Verified';
                $user->save();
    
                return response()->json([
                    'status' => 'success',
                    'data' => $data['data'],
                ],200);
            }
            return response()->json([
                    'status' => 'error',
                    'data' => $data['data'],
            ],422);
            
        }
        $responseData = $response->json('responseData');
        
        if($responseData){
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            return response()->json([
                'status' => 'error',
                'data' => json_decode($decryptedData, true),
            ],422);
        }

        return response()->json([
            'status' => 'error',
            'message' => json_decode($response->body(),true),
        ], 422);
    }
    
    public function get_aadhar_otp(Request $request)
    {
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        $token = env('AUTHBRIDGE_TOKEN', 'India@2608');

        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'transID' => $this->generateUniqueTransId(), // Default or provided
            'docType' => 211,         // Mandatory field
            'aadharNo' => $request->aadhar_no,        // Mandatory field
        ];

        if (!$input['aadharNo']) {
            return response()->json([
                'status' => 'error',
                'message' => 'aadharNo is required.',
            ], 400);
        }

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->post('https://www.truthscreen.com/v1/apicall/nid/aadhar_get_otp', $payload);

        // Step 5: Handle the API response
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            
            $user = Auth::User();
            $user->aadhar_no = $request->aadhar_no;
            $user->aadhar_status = 'Pending';
            $user->save();

            return response()->json([
                'status' => 'success',
                'data' => json_decode($decryptedData, true),
            ],200);
        }
        
        $responseData = $response->json('responseData');
        
        if($responseData){
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            return response()->json([
                'status' => 'error',
                'data' => json_decode($decryptedData, true),
            ],$response->status());
        }

        return response()->json([
            'status' => 'error',
            'message' => $response->body(),
        ], $response->status());
    }
    
    public function verify_aadhar_otp(Request $request)
    {
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        $token = env('AUTHBRIDGE_TOKEN', 'India@2608');

        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'transId' => $request->tsTransId,
            'otp' => (int)$request->otp,
        ];

        if (!$input['otp']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Otp is required.',
            ], 400);
        }
        
        if (!$input['transId']) {
            return response()->json([
                'status' => 'error',
                'message' => 'tsTransId is required.',
            ], 400);
        }

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->post('https://www.truthscreen.com/v1/apicall/nid/aadhar_submit_otp', $payload);

        // Step 5: Handle the API response
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            
            $user = Auth::User();
            $user->aadhar_details = json_decode($decryptedData, true);
            $user->aadhar_status = 'Verified';
            $user->save();

            return response()->json([
                'status' => 'success',
                'data' => json_decode($decryptedData, true),
            ],200);
        }
        
        $responseData = $response->json('responseData');
        
        if($responseData){
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            return response()->json([
                'status' => 'error',
                'data' => json_decode($decryptedData, true),
            ],$response->status());
        }

        return response()->json([
            'status' => 'errorss',
            'message' => $response->body(),
        ], $response->status());
    }
    
    public function verify_pan_number(Request $request)
    {
        $rules = [
            'pan_no' => 'required|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'name' => 'required|string|max:40',
            'dob' => [
                'required',
                'regex:/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d{2}$/', // Ensures DD/MM/YYYY format
                function ($attribute, $value, $fail) {
                    $date = \DateTime::createFromFormat('d/m/Y', $value);
                    if (!$date || $date->format('d/m/Y') !== $value) {
                        $fail('The date of birth format is invalid.');
                    }
                    if ($date > new \DateTime()) {
                        $fail('The date of birth must be before today.');
                    }
                },
            ],
        ];
        
        $messages = [
            'pan_no.required' => 'The PAN number is required.',
            'pan_no.string' => 'The PAN number must be a valid string.',
            'pan_no.size' => 'The PAN number must be exactly 10 characters long.',
            'pan_no.regex' => 'The PAN number format is invalid.',
            
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a valid string.',
            'name.max' => 'The name cannot exceed 40 characters.',
            
            'dob.required' => 'The date of birth is required.',
            'dob.regex' => 'The date of birth format must be DD/MM/YYYY.',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user = User::where('id','!=',Auth::User()->id)->where('pan_no',$request->pan_no)->where('pan_status','Verified')->first();
        if($user){
            return response()->json([
                'error' => 'This pan is already taken'
            ], 422);
        }
        $user = Auth::User();
        $aadharDetails = json_decode($user->aadhar_details, true);
        $firstKey = array_key_first($aadharDetails); // Get the first key dynamically
        if(!$firstKey){
            $user->aadhar_status = 'Pending';
            $user->save();
            abort(response()->json(
                [
                    'error' => 'aadhar is not verified',
                    'redirect' => 'aadhar_screen',
                ], 422));
        }
        
        $aadhar_name = trim($aadharDetails[$firstKey]['msg'][0]['data']['name'] ?? '');
        $aadhar_dob = trim($aadharDetails[$firstKey]['msg'][0]['data']['dob'] ?? '');
    
        $request_name = trim($request->name);
        $request_dob = trim($request->dob); // Expected format: DD/MM/YYYY
    
        // Convert request DOB (DD/MM/YYYY) to Aadhar format (YYYY-MM-DD)
        $request_dob_formatted = null;
        if (!empty($request_dob)) {
            $date_parts = explode('/', $request_dob);
            if (count($date_parts) === 3) {
                $request_dob_formatted = $date_parts[2] . '-' . str_pad($date_parts[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
            }
        }
    
        // Normalize name (Remove prefixes & sort words alphabetically)
        function normalizeName($name) {
            $name = trim(str_ireplace(['Mr.', 'Mr', 'Ms.', 'Ms', 'Mrs.', 'Mrs'], '', $name)); // Remove titles
            $name = strtolower($name); // Convert to lowercase for case-insensitive matching
            $name_parts = preg_split('/\s+/', $name); // Split by any space
            $name_parts = array_filter($name_parts); // Remove empty values
            sort($name_parts, SORT_STRING); // Sort words alphabetically
            return implode(' ', $name_parts);
        }
    
        // Check if one name is a subset of the other
        function isNameMatching($aadhar_name, $request_name) {
            $aadhar_parts = explode(' ', $aadhar_name);
            $request_parts = explode(' ', $request_name);
    
            // Check if all words of Aadhar name exist in the request name or vice versa
            return empty(array_diff($aadhar_parts, $request_parts)) || empty(array_diff($request_parts, $aadhar_parts));
        }
    
        $normalized_aadhar_name = normalizeName($aadhar_name);
        $normalized_request_name = normalizeName($request_name);
    
        // Debugging (Check the values before comparison)
        // return response()->json(['aadhar_name' => $normalized_aadhar_name, 'request_name' => $normalized_request_name, 'aadhar_dob' => $aadhar_dob, 'request_dob' => $request_dob_formatted]);
    
        // Check if either name or DOB matches
        // if (!isNameMatching($normalized_aadhar_name, $normalized_request_name) && $aadhar_dob !== $request_dob_formatted) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Please use your own PAN number, Name or DOB NOT matching with Aadhar'
        //     ], 422);
        // }
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        $token = env('AUTHBRIDGE_TOKEN', 'India@2608');

        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'transId' => $this->generateUniqueTransId(),
            'docType' => 549,         // Mandatory field
            'docNumber' => $request->pan_no,
            'name' => $request->name,
            'dob' => $request->dob,
        ];

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->post('https://www.truthscreen.com/v1/apicall/nid/pan_online_verification', $payload);

        // Step 5: Handle the API response
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            $data = json_decode($decryptedData, true);
            if($data['status'] != 1){
                return response()->json([
                    'status' => 'error',
                    'message' => $data['msg'],
                ],422);
            }
            if($data['msg']['dateOfBirth'] == 'NOT-MATCHING'){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid date of birth',
                ],422);
            }
            if($data['msg']['name'] == 'NOT-MATCHING'){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid name',
                ],422);
            }
            if($data['msg']['panStatus'] != 'EXISTING AND VALID'){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid pan no',
                ],422);
            }
            // if($data['msg']['dateOfBirth'] == 'MATCHING' && $data['msg']['name'] == 'MATCHING' && $data['msg']['panStatus'] == 'EXISTING AND VALID'){
                $data['msg']['dateOfBirth'] = $request->dob;
                $data['msg']['name'] = $request->name;
                $user->pan_no = $request->pan_no;
                $user->pan_details = $data;
                $user->pan_status = 'Verified';
                $user->save();
    
                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                ],200);
            // }
            
            return response()->json([
                'status' => 'error',
                'message' => 'Something is wrong, please try again',
            ],422);
            
        }
        
        $responseData = $response->json('responseData');
        
        if($responseData){
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            return response()->json([
                'status' => 'error',
                'data' => json_decode($decryptedData, true),
            ],$response->status());
        }

        return response()->json([
            'status' => 'errorss',
            'message' => json_decode($response->body(),true),
        ], $response->status());
    }
    
    public function update_bank_details(Request $request)
    {
        $rules = [
            'account_no' => 'required|numeric|digits_between:9,18',
            'ifsc_code' => [
                'required',
                'string',
                'size:11',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'
            ],
        ];
        
        $messages = [
            'account_no.required' => 'The account number is required.',
            'account_no.numeric' => 'The account number must be a valid number.',
            'account_no.digits_between' => 'The account number must be between 9 to 18 digits.',
            
            'ifsc_code.required' => 'The IFSC code is required.',
            'ifsc_code.string' => 'The IFSC code must be a valid string.',
            'ifsc_code.size' => 'The IFSC code must be exactly 11 characters long.',
            'ifsc_code.regex' => 'The IFSC code format is invalid.',
        ];

        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        
        $user = User::where('id','!=',Auth::User()->id)->where('account_no',$request->account_no)->where('bank_status','Verified')->first();
        if($user){
            return response()->json([
                'error' => 'This account no is already taken'
            ], 422);
        }
        
        $user = Auth::User();
        $aadharDetails = json_decode($user->aadhar_details, true);
        $firstKey = array_key_first($aadharDetails); // Get the first key dynamically
        if(!$firstKey){
            $user->aadhar_status = 'Pending';
            $user->save();
            abort(response()->json(
                [
                    'error' => 'aadhar is not verified',
                    'redirect' => 'aadhar_screen',
                ], 422));
        }
        
        // Replace these with your credentials or retrieve from the .env file
        $username = env('AUTHBRIDGE_USERNAME', 'production@analogueitsolutions.com');
        $token = env('AUTHBRIDGE_TOKEN', 'India@2608');

        // Step 1: Generate the encryption key
        $encryptionKey = $this->generateEncryptionKey($token);

        // Step 2: Prepare the input JSON structure
        $input = [
            'transId' => $this->generateUniqueTransId(),
            'docType' => 430,         // Mandatory field
            'accountNumber' => $request->account_no,
            'ifscCode' => $request->ifsc_code,
        ];

        $inputJson = json_encode($input);

        // Step 3: Encrypt the input JSON
        $encryptedData = $this->encryptData($encryptionKey, $inputJson);

        // Step 4: Prepare and send the HTTP request
        $payload = ['requestData' => $encryptedData];
        $response = Http::withHeaders([
            'username' => $username,
            'Content-Type' => 'application/json',
        ])->post('https://www.truthscreen.com/BankIfscVerification/idsearch', $payload);

        // Step 5: Handle the API response
        $data = '';
        if ($response->successful()) {
            $responseData = $response->json('responseData');
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            $data = json_decode($decryptedData,true);
            
            // if(isset($data['status']) && $data['status'] == 0){
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => $data['msg']['status'],
            //     ],422);
            // }
            
            // if(isset($data['result']) && $data['result']['status'] == 0){
            //     return response()->json([
            //         'status' => 'error',
            //         'message' => $data['result']['msg']['status'],
            //     ],422);
            // }
            
            if(isset($data['status']) && $data['status'] == 1){

                $aadhar_name = trim($aadharDetails[$firstKey]['msg'][0]['data']['name'] ?? '');
                $bank_name = trim($data['msg']['Account Holder Name'] ?? '');

                // Normalize name (Remove prefixes & sort words alphabetically)
                function normalizeName($name) {
                    $name = trim(str_ireplace(['Mr.', 'Mr', 'Ms.', 'Ms', 'Mrs.', 'Mrs'], '', $name)); // Remove titles
                    $name = strtolower($name); // Convert to lowercase for case-insensitive matching
                    $name_parts = explode(' ', $name);
                    $name_parts = array_filter($name_parts); // Remove empty values
                    sort($name_parts, SORT_STRING); // Sort words alphabetically
                    return implode(' ', $name_parts);
                }

                // Check if one name is a subset of the other
                function isNameMatching($aadhar_name, $bank_name) {
                    $aadhar_parts = explode(' ', $aadhar_name);
                    $bank_parts = explode(' ', $bank_name);

                    // Check if all words of Aadhar name exist in the request name or vice versa
                    return empty(array_diff($aadhar_parts, $bank_parts)) || empty(array_diff($bank_parts, $aadhar_parts));
                }

                $normalized_aadhar_name = normalizeName($aadhar_name);
                $normalized_bank_name = normalizeName($bank_name);
                // Check if either name or DOB matches
                // if (!isNameMatching($normalized_aadhar_name, $normalized_bank_name)) {
                //     return response()->json([
                //         'status' => 'error',
                //         'message' => 'Please use your own account number, Name or DOB NOT matching with Aadhar'
                //     ], 422);
                // }
                $user->bank_details = $data;
                $user->account_no = $request->account_no;
                $user->bank_status = 'Verified';
                
                $user->profile_status = 'Verified';
                $user->document_status = 'Verified';
                $user->save();
    
                return response()->json([
                    'status' => 'success',
                    'data' => $data,
                ],200);
            }
        }
        $user->bank_details = $data;
        $user->account_no = $request->account_no;
        $user->bank_status = 'Verified';
                
        $user->profile_status = 'Verified';
        $user->document_status = 'Verified';
        $user->save();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ],200);
        
        $responseData = $response->json('responseData');
        
        if($responseData){
            $decryptedData = $this->decryptData($encryptionKey, $responseData);
            return response()->json([
                'status' => 'error',
                'message' => json_decode($decryptedData, true),
            ],422);
        }

        return response()->json([
            'status' => 'errorss',
            'message' => json_decode($response->body(),true),
        ], 422);
    }
    
    private function generateUniqueTransId()
    {
        return uniqid('trans_', true); // Prefix with 'trans_' and include more entropy
    }
}
