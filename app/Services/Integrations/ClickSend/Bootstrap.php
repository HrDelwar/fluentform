<?php

namespace FluentForm\App\Services\Integrations\ClickSend;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;
use \FluentForm\App\Services\Integrations\ClickSend\ClickSend;

class Bootstrap extends IntegrationManager
{
    public function __construct(Application $app)
    {
        parent::__construct(
            $app,
            'ClickSend SMS Notification',
            'clicksend_sms_notification',
            '_fluentform_clicksend_sms_notification_settings',
            'clicksend_sms_notification_feed',
            25
        );

        $this->logo = $this->app->url('public/img/integrations/clicksend.png');

        $this->description = 'Send SMS in real time when a form is submitted with Clicksend.';


        $this->registerAdminHooks();
        add_action('wp_ajax_fluentform_clicksend_sms_config', array($this, 'getClickSendConfigOptions'));

        add_filter('fluentform_notifying_async_clicksend_sms_notification', '__return_false');
    }

    public function getClickSendConfigOptions()
    {

    }

    public function getGlobalFields($fields)
    {
        return [
            'logo'             => $this->logo,
            'menu_title'       => __('SMS Provider Settings (Clicksend)', 'fluentform'),
            'menu_description' => __('Please Provide your Clicksend Settings here', 'fluentform'),
            'valid_message'    => __('Your Clicksend API Key is valid', 'fluentform'),
            'invalid_message'  => __('Your Clicksend API Key is not valid', 'fluentform'),
            'save_button_text' => __('Save Settings', 'fluentform'),
            'fields'           => [
                'senderNumber' => [
                    'type'        => 'text',
                    'placeholder' => 'Clicksend Number',
                    'label_tips'  => __("Enter your clicksend sender number", 'fluentform'),
                    'label'       => __('Number From', 'fluentform'),
                ],
                'username'   => [
                    'type'        => 'text',
                    'placeholder' => 'Clicksend username',
                    'label_tips'  => __("Enter Clicksend username. This can be found from Clicksend", 'fluentform'),
                    'label'       => __('Username', 'fluentform'),
                ],
                'authToken'    => [
                    'type'        => 'password',
                    'placeholder' => 'Auth Token',
                    'label_tips'  => __("Enter Clicksend API Auth Token. This can be found from Clicksend", 'fluentform'),
                    'label'       => __('Auth Token', 'fluentform'),
                ]
            ],
            'hide_on_valid'    => true,
            'discard_settings' => [
                'section_description' => 'Your Clicksend API integration is up and running',
                'button_text'         => 'Disconnect Clicksend',
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
        if (!$globalSettings) {
            $globalSettings = [];
        }
        $defaults = [
            'senderNumber' => '',
            'username'   => '',
            'authToken'    => '',
            'provider'     => 'Clicksend'
        ];

        return wp_parse_args($globalSettings, $defaults);
    }

    public function saveGlobalSettings($settings)
    {
        if (!$settings['authToken']) {
            $integrationSettings = [
                'senderNumber' => '',
                'username'   => '',
                'authToken'    => '',
                'provider'     => 'Clicksend',
                'status'       => false
            ];
            // Update the reCaptcha details with siteKey & secretKey.
            update_option($this->optionKey, $integrationSettings, 'no');
            wp_send_json_success([
                'message' => __('Your settings has been updated', 'fluentform'),
                'status'  => false
            ], 200);
        }

        // Verify API key now
        try {
            
             if (empty($settings['senderNumber'])) {
                 //prevent saving integration without the sender number
                 throw new \Exception('Sender number is required');
                 
             }
            $integrationSettings = [
                'senderNumber' => sanitize_textarea_field($settings['senderNumber']),
                'username'   => sanitize_text_field($settings['username']),
                'authToken'    => sanitize_text_field($settings['authToken']),
                'provider'     => 'Clicksend',
                'status'       => false
            ];
            update_option($this->optionKey, $integrationSettings, 'no');

            $api = new ClickSend($settings['authToken'], $settings['username']);
            $result = $api->auth_test();

            if (!empty($result['error'])) {
                throw new \Exception($result['message']);
            }
        } catch (\Exception $exception) {
            wp_send_json_error([
                'message' => $exception->getMessage()
            ], 400);
        }

        // Integration key is verified now, Proceed now

        $integrationSettings = [
            'senderNumber' => sanitize_textarea_field($settings['senderNumber']),
            'username'   => sanitize_text_field($settings['username']),
            'authToken'    => sanitize_text_field($settings['authToken']),
            'provider'     => 'Clicksend',
            'status'       => true
        ];

        // Update the reCaptcha details with siteKey & secretKey.
        update_option($this->optionKey, $integrationSettings, 'no');

        wp_send_json_success([
            'message' => __('Your Clicksend api key has been verified and successfully set', 'fluentform'),
            'status'  => true
        ], 200);
    }

    public function pushIntegration($integrations, $formId)
    {
        $integrations[$this->integrationKey] = [
            'title'                 => 'SMS Notification by Clicksend',
            'logo'                  => $this->logo,
            'is_active'             => $this->isConfigured(),
            'configure_title'       => 'Configuration required!',
            'global_configure_url'  => admin_url('admin.php?page=fluent_forms_settings#general-sms_notification-settings'),
            'configure_message'     => 'SMS Notification is not configured yet! Please configure your SMS api first',
            'configure_button_text' => 'Set SMS Notification API'
        ];
        return $integrations;
    }

    public function getIntegrationDefaults($settings, $formId)
    {
        return [
            'name'            => '',
            'receiver_number' => '',
            'message'         => '',
            'campaign_name' => '',
            'clicksend_config' => [
                'list_id'       => '',
                'template_id'  => ''
            ],
            'conditionals'    => [
                'conditions' => [],
                'status'     => false,
                'type'       => 'all'
            ],
            'enabled'         => true
        ];
    }

    public function getSettingsFields($settings, $formId)
    {
        $api = $this->getRemoteClient();

        $list_options = array();
        $lists = $api->getLists();
        if (!is_wp_error($lists)) {
            foreach ($lists['data']['data'] as $list){
                $list_options[$list['list_id']] = $list['list_name'];
            }
        }

        $template_options = array();
        $templates = $api->getTemplates();
        if (!is_wp_error($templates)) {
            foreach ($templates['data']['data'] as $template){
                $template_options[$template['template_id']] = $template['template_name'];
            }
        }

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
                    'required'    => false,
                    'placeholder' => 'Type the receiver number',
                    'component'   => 'value_text'
                ],
                [
                    'key'         => 'message',
                    'label'       => 'SMS text',
                    'required'    => false,
                    'placeholder' => 'SMS Text',
                    'component'   => 'value_textarea'
                ],
                [
                    'key'         => 'campaign_name',
                    'label'       => 'Campaign Name',
                    'required'    => false,
                    'placeholder' => 'Campaign Name',
                    'component'   => 'value_text'
                ],
                [
                    'key'            => 'clicksend_config',
                    'label'          => 'Clicksend SMS Campaign',
                    'required'       => false,
                    'component'      => 'chained_select',
                    'primary_key'    => 'list_id',
                    'fields_options' => [
                        'list_id'       => $list_options,
                        'template_id'  => $template_options,
                    ],
                    'options_labels' => [
                        'list_id'       => [
                            'label'       => 'Campaign List',
                            'type'        => 'select',
                            'placeholder' => 'Select List'
                        ],
                        'template_id'  => [
                            'label'       => 'Select Template',
                            'type'        => 'select',
                            'placeholder' => 'Choose SMS Template'
                        ]
                    ],
                    'remote_url'     => admin_url('admin-ajax.php?action=fluentform_clicksend_sms_config')
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
        return $list;
    }


    /*
     * Form Submission Hooks Here
     */
    public function notify($feed, $formData, $entry, $form)
    {
        $feedData = $feed['processedValues'];


        if (empty($feedData['receiver_number']) && empty($feedData['clicksend_config']['list_id'])) {
            do_action('ff_integration_action_result', $feed, 'failed',  'no valid receiver_number found');
            return;
        }

        if(empty($feedData['message']) && empty($feedData['clicksend_config']['template_id'])){
            do_action('ff_integration_action_result', $feed, 'failed',  'no valid receiver_number found');
            return;
        }


        $apiSettings = $this->getGlobalSettings([]);
        $api = $this->getRemoteClient();

        if(!empty($feedData['clicksend_config']['template_id'])){
            $templates = $api->getTemplates();
            if (!is_wp_error($templates)) {
                foreach ($templates['data']['data'] as $template){

                    if($template['template_id'] == $feedData['clicksend_config']['template_id']){
                        $smsBody = $template['body'];
                    }
                }
            }
        }

        if(!empty($feedData['message'])){
            $smsBody = $feedData['message'];
        }


        if($feedData['receiver_number'] && $feedData['clicksend_config']['list_id']){
            $action = 'single-sms';
            $smsData = [
                "messages" => array([
                    'body' => $smsBody,
                    'from' => $apiSettings['senderNumber'],
                    'to'   => $feedData['receiver_number']
                ])
            ];
            $this->handleSMSResponse($action, $smsData, $feed, $entry);

            $action = 'sms-campaign';
            $smsData = [
                'body' => $smsBody,
                'schedule' => 0,
                'from' => $apiSettings['senderNumber'],
                'name' => $feedData['campaign_name'],
                'list_id'   => $feedData['clicksend_config']['list_id']
            ];
            $this->handleSMSResponse($action, $smsData, $feed, $entry);
        }else{
            if ($feedData['receiver_number']){
                $action = 'single-sms';
                $smsData = [
                    "messages" => array([
                        'body' => $smsBody,
                        'from' => $apiSettings['senderNumber'],
                        'to'   => $feedData['receiver_number']
                    ])
                ];
            }else{
                $action = 'sms-campaign';
                $smsData = [
                    'body' => $smsBody,
                    'schedule' => 0,
                    'name' => $feedData['campaign_name'],
                    'from' => $apiSettings['senderNumber'],
                    'list_id'   => $feedData['clicksend_config']['list_id']
                ];
            }
            $this->handleSMSResponse($action, $smsData, $feed, $entry);
        }

    }

    public function handleSMSResponse($action , $smsData, $feed, $entry){

        $smsData = apply_filters('fluentform_integration_data_'.$this->integrationKey, $smsData, $feed, $entry);
        $api = $this->getRemoteClient();
        $response = $api->sendSMS($action, $smsData);

        if (is_wp_error($response)) {
            do_action('ff_integration_action_result', $feed, 'failed',  $response->get_error_message());
        } else {
            do_action('ff_integration_action_result', $feed, 'success', 'Clicksend SMS feed has been successfully initialed and pushed data');
        }
    }

    public function getRemoteClient()
    {
        $settings = $this->getGlobalSettings([]);
        return new ClickSend($settings['authToken'], $settings['username']);
    }

}
