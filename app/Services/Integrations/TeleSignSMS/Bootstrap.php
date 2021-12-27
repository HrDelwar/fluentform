<?php

namespace FluentForm\App\Services\Integrations\TeleSignSMS;

use FluentForm\Framework\Foundation\Application;
use FluentForm\App\Services\Integrations\IntegrationManager;

class Bootstrap extends IntegrationManager
{

    public function __construct(Application $app)
    {
        parent::__construct(
            $app,
            'TeleSign SMS',
            'telesign_sms',
            '_fluenform_telesign_sms_settings',
            'telesign_sms_feed',
            25
        );


        $this->logo = $this->app->url('public/img/integrations/telesign.png');
        $this->description = 'Send SMS in real time when a form is submitted with TeleSign.';
        $this->registerAdminHooks();
        add_filter('fluentform_notifying_async_telesign_sms','__return_false');
    }

    public function getGlobalFields($fields)
    {
        return [
            'logo' => $this->logo,
            'menu_title' => __('SMS Provider Settings (TeleSign)','fluentform'),
            'menu_description' => __('Please Provide your TeleSign Settings here', 'fluentform'),
            'valid_message' =>__('Your TeleSign information is valid', 'fluentform'),
            'invalid_message'  => __('Your TeleSign information  is not valid', 'fluentform'),
            'save_button_text' => __('Save Settings', 'fluentform'),
            'fields'           => [
                'senderNumber' => [
                    'type'        => 'text',
                    'placeholder' => 'TeleSign Number',
                    'label_tips'  => __("Enter your TeleSign sender number", 'fluentform'),
                    'label'       => __('Telesign Number', 'fluentform'),
                    'optional'    => true
                ],
                'accountSID'   => [
                    'type'        => 'text',
                    'placeholder' => 'Account SID',
                    'label_tips'  => __("Enter TeleSign Account SID. This can be found from TeleSign", 'fluentform'),
                    'label'       => __('Account SID', 'fluentform'),
                ],
                'authToken'    => [
                    'type'        => 'password',
                    'placeholder' => 'Auth Token',
                    'label_tips'  => __("Enter TeleSign API Auth Token. This can be found from TeleSign", 'fluentform'),
                    'label'       => __('Auth Token', 'fluentform'),
                ]
            ],
            'hide_on_valid'    => true,
            'discard_settings' => [
                'section_description' => 'Your TeleSign API integration is up and running',
                'button_text'         => 'Disconnect TeleSign',
                'data'                => [
                    'authToken' => ''
                ],
                'show_verify'         => true
            ]
        ];
    }

    public function getGlobalSettings($settings)
    {
       $globalSettings = get_option($this->optionKey);
       if(!$globalSettings) {
           $globalSettings = [];
       }
       $defaults = [
           'senderNumber' => '',
           'accountSID'   => '',
           'authToken'    => '',
           'provider'     => 'telesign'
       ];
       return wp_parse_args($globalSettings,$defaults);
    }

    public function saveGlobalSettings($settings)
    {
        if(!$settings['authToken']){
            $integrationSettings = [
                'senderNumber' => '',
                'accountSID'   => '',
                'authToken'    => '',
                'provider'     => 'telesign',
                'status'       => false
            ];

            update_option($this->optionKey, $integrationSettings, 'no');
            wp_send_json_success([
                'message' => __('Your settings has been updated', 'fluentform'),
                'status' => false
            ],200);
        }

        try {
            $integrationSettings = [
                'senderNumber' => sanitize_textarea_field($settings['senderNumber'])??'',
                'accountSID'   => sanitize_text_field($settings['accountSID']),
                'authToken'    => sanitize_text_field($settings['authToken']),
                'provider'     => 'telesign',
                'status'       => false
            ];
            update_option($this->optionKey, $integrationSettings, 'no');
            $api = new TeleSign($settings['authToken'], $settings['accountSID'], $settings['senderNumber']);
            $result = $api->auth_test();
            if($result['response']['response']['code'] == '401'){
                throw new \Exception($result['message']);
            }
        }catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ],400);
        }


        // Integration information is verified now, Proceed now

        $integrationSettings = [
            'senderNumber' => sanitize_textarea_field($settings['senderNumber']),
            'accountSID'   => sanitize_text_field($settings['accountSID']),
            'authToken'    => sanitize_text_field($settings['authToken']),
            'provider'     => 'telesign',
            'status'       => true
        ];

        // Update the reCaptcha details with siteKey & secretKey.
        update_option($this->optionKey, $integrationSettings, 'no');

        wp_send_json_success([
            'message' => __('Your Telesign information has been verified and successfully set', 'fluentform'),
            'status'  => true
        ], 200);
    }

    public function getIntegrationDefaults($settings, $formId)
    {
        return [
            'name'            => '',
            'receiver_number' => '',
            'message'         => '',
            'conditionals'    => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all'
            ],
            'enabled'         => true
        ];
    }

    public function pushIntegration($integrations, $formId)
    {
        $integrations[$this->integrationKey] = [
            'title'                 => 'SMS Notification by TeleSign',
            'logo'                  => $this->logo,
            'is_active'             => $this->isConfigured(),
            'configure_title'       => 'Configuration required!',
            'global_configure_url'  => admin_url('admin.php?page=fluent_forms_settings#general-sms_notification-settings'),
            'configure_message'     => 'SMS Notification is not configured yet! Please configure your SMS api first',
            'configure_button_text' => 'Set SMS Notification API'
        ];
        return $integrations;
    }

    public function getSettingsFields($settings, $formId)
    {
        return [
            'fields'              => [
                [
                    'key'         => 'name',
                    'label'       => 'Name',
                    'required'    => true,
                    'placeholder' => 'Your Feed Name',
                    'component'   => 'text'
                ],
                [
                    'key'         => 'receiver_number',
                    'label'       => 'To',
                    'required'    => true,
                    'placeholder' => 'Type the receiver number',
                    'component'   => 'value_text'
                ],
                [
                    'key'         => 'message',
                    'label'       => 'SMS text',
                    'required'    => true,
                    'placeholder' => 'SMS Text',
                    'component'   => 'value_textarea'
                ],
                [
                    'require_list' => false,
                    'key'          => 'conditionals',
                    'label'        => 'Conditional Logics',
                    'tips'         => 'Send SMS Notification conditionally based on your submission values',
                    'component'    => 'conditional_block'
                ],
                [
                    'require_list'    => false,
                    'key'             => 'enabled',
                    'label'           => 'Status',
                    'component'       => 'checkbox-single',
                    'checkbox_label' => 'Enable This feed'
                ]
            ],
            'button_require_list' => false,
            'integration_title'   => $this->title
        ];
    }

    public function getMergeFields($list, $listId, $formId)
    {
        return [];
    }

    public function notify($feed, $formData, $entry, $form)
    {

        $feedData = $feed['processedValues'];

        if (empty($feedData['receiver_number']) || empty($feedData['message'])) {
            do_action('ff_integration_action_result', $feed, 'failed',  'no valid receiver_number found');
            return;
        }

        $apiSettings = $this->getGlobalSettings([]);

        $smsData = [
            "phone_number" => $feedData['receiver_number'],
            "message" => $feedData['message'],
            "message_type" => "ARN"
        ];

        $smsData = apply_filters('fluentform_integration_data_'.$this->integrationKey, $smsData, $feed, $entry);

        $api = $this->getRemoteClient();
        $response = $api->sendSMS($smsData);

        if (is_wp_error($response)) {
            do_action('ff_integration_action_result', $feed, 'failed',  $response->get_error_message());
        } else {
            do_action('ff_integration_action_result', $feed, 'success', 'Telesign SMS feed has been successfully initialed and pushed data');
        }
    }

    public function getRemoteClient()
    {
        $settings = $this->getGlobalSettings([]);
        return new TeleSign($settings['authToken'], $settings['accountSID'], $settings['senderNumber']);
    }
}