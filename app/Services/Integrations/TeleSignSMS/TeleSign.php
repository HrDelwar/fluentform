<?php
namespace FluentForm\App\Services\Integrations\TeleSignSMS;

use GuzzleHttp\Client;
class TeleSign
{
    protected $apiUrl = 'https://rest-api.telesign.com/v1/messaging';
    protected $customer_id;
    protected $api_key;
    public function __construct($authToken = null, $accountSID = null, $senderNumber = "")
    {
        $this->api_key = $authToken;
        $this->customer_id = $accountSID;
        if(!empty($senderNumber)){
            $this->apiUrl = "https://rest-api.telesign.com/v1/phoneid/" . $senderNumber;
        }
    }

    public function auth_test(){
        return $this->make_request('', [
            "phone_number" => "",
            "message" => "",
            "message_type" => "ARN"
        ], "POST");
    }

    public function make_request($action, $options = array(), $method = 'POST'){

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $this->customer_id . ':' . $this->api_key),
                'Content-Type' => 'application/x-www-form-urlencoded',
            )
        );
        $request_url = $this->apiUrl . $action;

        switch ( $method ) {
            case 'POST':
                //$request_url .= '?'.http_build_query($options);
                $args['body'] = $options;
                $response = wp_remote_post( $request_url, $args );
                break;
            case 'GET':
                $response = wp_remote_get( $request_url, $args );
                break;
        }

        if ( is_wp_error( $response ) ) {
            return [
                'error' => 'API_Error',
                'message' => $response->get_error_message(),
                'response' => $response
            ];
        } else if($response['response']['code'] >= 300) {
            return [
                'error'    => 'API_Error',
                'message'  => $response['response']['message'],
                'response' => $response
            ];
        } else {
            return json_decode( $response['body'], true );
        }

    }

    public function sendSMS($data)
    {
        $response = $this->make_request('', $data, 'POST');

        if(!empty($response['error'])) {
            return new \WP_Error('api_error', $response['message']);
        }

        return $response;
    }
}