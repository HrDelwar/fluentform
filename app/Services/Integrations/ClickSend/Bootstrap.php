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
        // Verify API key now
        try {
            if (empty($settings['authToken'])) {
                //prevent saving integration without the sender number
                throw new \Exception('authToken is required');

            }

            if (empty($settings['senderNumber'])) {
                //prevent saving integration without the sender number
                throw new \Exception('Sender number is required');

            }
            if (empty($settings['username'])) {
                //prevent saving integration without the sender number
                throw new \Exception('Username number is required');
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
            $integrationSettings = [
                'senderNumber' => '',
                'username'   => '',
                'authToken'    => '',
                'provider'     => 'Clicksend',
                'status'       => false
            ];
            update_option($this->optionKey, $integrationSettings, 'no');
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
            'name'             => '',
            'receiver_number'  => '',
            'list_id'          => 'single-sms',
            'message_body'     => 'message-input',
            'message'          => '',
            'phone_number'     => '',
            'fields'           => (object)[],
            'other_add_contact_fields' => [
                [
                    'item_value' => '',
                    'label' => ''
                ]
            ],
            'contact_list_name'=> '',
            'campaign_name'    => '',
            'email_campaign_subject'    => '',
            'campaign_list_id' => '',
            'schedule'         => '',
            'template_id'      => '',
            'email_template_id'      => '',
            'email_address_id'      => '',
            'email_form_name'      => '',
            'enabled'          => true
        ];
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
                    'key'         => 'list_id',
                    'label'       => 'Services',
                    'placeholder' => 'Select Service',
                    'required'    => true,
                    'component'   => 'list_ajax_options',
                    'options' => [
                        'single-sms'    => 'Single SMS',
                        'sms-campaign'  => 'SMS Campaign',
                        'create-new-contact'   => 'Create New Contact',
                        'add-contact-list'     => 'Add Contact List',
                        'email-campaign'     => 'Add Email Campaign',
                    ]
                ],
                [
                    'key' => 'fields',
                    'require_list' => true,
                    'label' => 'Service Fields',
                    'tips' => 'The service fields is an important input data field. Those field data will pass clicksend for fulfil service operation.',
                    'component' => 'map_fields',
                    'field_label_remote' => 'Field Label',
                    'field_label_local' => 'Field Value',
                ],
            ],
            'button_require_list' => false,
            'integration_title'   => $this->title
        ];
    }


    public function getMergeFields($list, $listId, $formId)
    {

        $api = $this->getRemoteClient();

        if($listId == 'single-sms'){

            $template_options = array();
            $templates = $api->getTemplates('sms/templates');
            if (!is_wp_error($templates)) {
                foreach ($templates['data']['data'] as $template){
                    $template_options[$template['template_id']] = $template['template_name'];
                }
            }

            $fields = [
                [
                    'key'         => 'message_body',
                    'label'       => 'Message Body',
                    'tips'        => 'Select which type message body you want to send. If choose template message manually message is not send or if choose input message template is not working. Default Input message.',
                    'placeholder' => 'Chose Message Body',
                    'required'    => true,
                    'component'   => 'select',
                    'options' => [
                        'message-input'       => 'Input Message',
                        'template-message'  => 'Template Message',
                    ]
                ],
                [
                    'key'         => 'receiver_number',
                    'label'       => 'To',
                    'required'    => true,
                    'tips'        => 'Enter a receiver number of select input field shortcode.',
                    'placeholder' => 'Type the receiver number',
                    'component'   => 'value_text'
                ],
                [
                    'key'         => 'message',
                    'label'       => 'Message',
                    'tips'        => 'Enter manual message of select input field shortcode for sms body.',
                    'required'    => false,
                    'placeholder' => 'Message Body',
                    'component'   => 'value_textarea'
                ],
                [
                    'key'         => 'template_id',
                    'label'       => 'SMS Template',
                    'placeholder' => 'Choose Template',
                    'tips'        => 'Choose a template for sms body.',
                    'required'    => false,
                    'component'   => 'select',
                    'options'     => $template_options
                ],
                [
                    'key'         => 'schedule',
                    'label'       => 'SMS Schedule',
                    'placeholder' => 'SMS schedule date and time ',
                    'tips'        => 'Choose a datetime for send sms.',
                    'required'    => false,
                    'component'   => 'datetime',
                ]
            ];
        }
        elseif ($listId == 'sms-campaign'){

            $template_options = array();
            $templates = $api->getTemplates('sms/templates');
            if (!is_wp_error($templates)) {
                foreach ($templates['data']['data'] as $template){
                    $template_options[$template['template_id']] = $template['template_name'];
                }
            }

            $list_options = array();
            $lists = $api->getLists();
            if (!is_wp_error($lists)) {
                foreach ($lists['data']['data'] as $list){
                    $list_options[$list['list_id']] = $list['list_name'];
                }
            }

            $fields = [
                [
                    'key'         => 'message_body',
                    'label'       => 'Message Body*',
                    'tips'        => 'Select which type message body you want to send. If choose template message manually message is not send or if choose input message template is not working. Default Input message.',
                    'placeholder' => 'Chose Message Body',
                    'required'    => true,
                    'component'   => 'select',
                    'options' => [
                        'message-input'       => 'Input Message',
                        'template-message'  => 'Template Message',
                    ]
                ],
                [
                    'key'         => 'campaign_name',
                    'label'       => 'Campaign Name*',
                    'tips'        => 'Enter your campaign name or select input shortcode field for campaign name.',
                    'required'    => true,
                    'placeholder' => 'Campaign Name',
                    'component'   => 'value_text'
                ],
                [
                    'key'         => 'campaign_list_id',
                    'label'       => 'Campaign List*',
                    'placeholder' => 'Choose list',
                    'tips'        => 'Choose a list for sending sms all of list contact.',
                    'required'    => true,
                    'component'   => 'select',
                    'options'     => $list_options
                ],
                [
                    'key'         => 'message',
                    'label'       => 'Message',
                    'tips'        => 'Enter manual message of select input field shortcode for sms body.',
                    'required'    => false,
                    'placeholder' => 'Message Body',
                    'component'   => 'value_textarea'
                ],
                [
                    'key'         => 'template_id',
                    'label'       => 'SMS Template',
                    'placeholder' => 'Choose Template',
                    'tips'        => 'Choose a template for sms body.',
                    'required'    => false,
                    'component'   => 'select',
                    'options'     => $template_options
                ],
                [
                    'key'         => 'schedule',
                    'label'       => 'Campaign Schedule',
                    'placeholder' => 'Campaign schedule date and time ',
                    'tips'        => 'Choose a datetime for your sms campaign.',
                    'required'    => false,
                    'component'   => 'datetime',
                ]
            ];
        }
        elseif ($listId == 'create-new-contact'){
            $list_options = array();
            $lists = $api->getLists();
            if (!is_wp_error($lists)) {
                foreach ($lists['data']['data'] as $list){
                    $list_options[$list['list_id']] = $list['list_name'];
                }
            }
            $fields = [
                [
                    'key'           => 'campaign_list_id',
                    'label'         => 'Campaign List',
                    'placeholder'   => 'Choose list',
                    'tips'          => 'Choose the list which list you want to add contact.',
                    'required'      => true,
                    'component'     => 'select',
                    'options'       => $list_options
                ],
                [
                    'key'           => 'phone_number',
                    'label'         => 'Phone Number*',
                    'placeholder'   => 'Subscriber Number',
                    'tips'          => 'Enter subscriber or select input shortcode field for add contact in list.',
                    'required'      => true,
                    'component'     => 'value_text',
                ],
                [
                    'key' => 'other_add_contact_fields',
                    'require_list' => false,
                    'label' => 'Other Fields',
                    'tips' => 'Other contact field for more details about contact. Those fields is optional.',
                    'component' => 'dropdown_many_fields',
                    'field_label_remote' => 'Others Add Contact Field',
                    'field_label_local' => 'Others Add Contact Field',
                    'options' => [
                        'first_name'         => 'First Name',
                        'last_name'          => 'Last Name',
                        'email'              => 'Email',
                        'organization_name'  => 'Company',
                        'fax_number'         => 'Fax Number',
                        'address_line_1'     => 'Address Line 1',
                        'address_line_2'     => 'Address Line 2',
                        'address_city'       => 'City',
                        'address_state'      => 'State',
                        'address_postal_code'=> 'Postal Code',
                        'address_country'    => 'Country',
                        'custom_1'           => 'Custom Field 1',
                        'custom_2'           => 'Custom Field 2',
                        'custom_3'           => 'Custom Field 3',
                        'custom_4'           => 'Custom Field 4',
                    ]
                ],
            ];
        }
        elseif ($listId == 'add-contact-list'){
            $fields = [
                [
                    'key'           => 'contact_list_name',
                    'label'         => 'List Name*',
                    'placeholder'   => 'Contact List Name',
                    'tips'          => 'Enter name or select input shortcode field for contact list.',
                    'required'      => true,
                    'component'     => 'value_text',
                ],
            ];
        }
        elseif ($listId == 'email-campaign'){

            $list_options = array();
            $lists = $api->getLists();
            if (!is_wp_error($lists)) {
                foreach ($lists['data']['data'] as $list){
                    $list_options[$list['list_id']] = $list['list_name'];
                }
            }

            $template_options = array();
            $templates = $api->getTemplates('email/templates');
            if (!is_wp_error($templates)) {
                foreach ($templates['data']['data'] as $template){
                    $template_options[$template['template_id']] = $template['template_name'];
                }
            }

            $email_address_options = array();
            $email_address = $api->getEmailAddress('email/addresses');
            if (!is_wp_error($email_address)) {
                foreach ($email_address['data']['data'] as $email_info){
                    $email_address_options[$email_info['email_address_id']] = $email_info['email_address'];
                }
            }

            $from_name_options = array();
            $account_info = $api->get('account');
            if (!is_wp_error($account_info)) {
                $first_name = $account_info['data']['user_first_name'];
                $last_name = $account_info['data']['user_last_name'];
                $from_name_options[$first_name . ' ' .$last_name] = $first_name . ' ' .$last_name ;
                $from_name_options[$first_name] = $first_name;
                $from_name_options[$last_name] = $last_name;
                $from_name_options[$account_info['data']['username']] = $account_info['data']['username'];
                $from_name_options[$account_info['data']['account_name']] = $account_info['data']['account_name'];
            }

            $fields = [
                [
                    'key'         => 'campaign_name',
                    'label'       => 'Campaign Name*',
                    'tips'        => 'Enter your campaign name or select input shortcode field for campaign name.',
                    'required'    => true,
                    'placeholder' => 'Campaign Name',
                    'component'   => 'value_text'
                ],
                [
                    'key'         => 'email_campaign_subject',
                    'label'       => 'Campaign Subject*',
                    'tips'        => 'Enter your campaign subject or select input shortcode field for campaign subject.',
                    'required'    => true,
                    'placeholder' => 'Campaign Subject',
                    'component'   => 'value_text'
                ],
                [
                    'key'         => 'campaign_list_id',
                    'label'       => 'Campaign List*',
                    'placeholder' => 'Choose list',
                    'tips'        => 'Choose a list for sending email all of list contact.',
                    'required'    => true,
                    'component'   => 'select',
                    'options'     => $list_options
                ],

                [
                    'key'         => 'message',
                    'label'       => 'Message*',
                    'tips'        => 'Enter manual message of select input field shortcode for email body.',
                    'required'    => false,
                    'placeholder' => 'Message Body',
                    'component'   => 'value_textarea'
                ],

                [
                    'key'         => 'email_template_id',
                    'label'       => 'Email Template',
                    'placeholder' => 'Choose Template',
                    'tips'        => 'Choose a template for sms body.',
                    'required'    => false,
                    'component'   => 'select',
                    'options'     => $template_options
                ],
                [
                    'key'         => 'email_address_id',
                    'label'       => 'From Email Address*',
                    'placeholder' => 'Choose From Email',
                    'tips'        => 'Choose a email for form email.',
                    'required'    => true,
                    'component'   => 'select',
                    'options'     => $email_address_options
                ],
                [
                    'key'         => 'email_form_name',
                    'label'       => 'Email Form Name*',
                    'placeholder' => 'Choose Form Name',
                    'tips'        => 'Choose a name for sending email form name.',
                    'required'    => true,
                    'component'   => 'select',
                    'options'     => $from_name_options
                ],
                [
                    'key'         => 'schedule',
                    'label'       => 'Campaign Schedule',
                    'placeholder' => 'Campaign schedule date and time ',
                    'tips'        => 'Choose a datetime for your sms campaign.',
                    'required'    => false,
                    'component'   => 'datetime',
                ]
            ];
        }
        else{
            return false;
        }
        return $fields;
    }


    /*
     * Form Submission Hooks Here
     */
    public function notify($feed, $formData, $entry, $form)
    {
        $feedData = $feed['processedValues'];

        if (empty($feedData['list_id'])) {
            do_action('ff_integration_action_result', $feed, 'failed',  'no valid service found');
            return;
        }

        $apiSettings = $this->getGlobalSettings([]);
        $api = $this->getRemoteClient();

//        sms body
        $smsBody = '';
        if(!empty($feedData['message_body'])){
            if($feedData['message_body'] == 'template-message' && !empty($feedData['template_id'])){
                $templates = $api->getTemplates('sms/templates');
                if (!is_wp_error($templates)) {
                    foreach ($templates['data']['data'] as $template){

                        if($template['template_id'] == $feedData['template_id']){
                            $smsBody = $template['body'];
                        }
                    }
                }
            }
            if($feedData['message_body'] == 'message-input' && !empty($feedData['message'])){
                $smsBody = $feedData['message'];
            }
        }

//        sms time schedule
        $schedule = 0;
        if(!empty($feedData['schedule'])){
            $schedule = strtotime($feedData['schedule']);
        }

//         service- single sms send
        if($feedData['list_id'] == 'single-sms'){
            if(
                empty($feedData['message_body'])    ||
                empty($feedData['receiver_number']) ||
                ($feedData['message_body'] == 'template-message' && empty($feedData['template_id'])) ||
                ($feedData['message_body'] == 'message-input' && empty($feedData['message']))
            ){
                do_action('ff_integration_action_result', $feed, 'failed',  'no fulfill required field');
                return;
            }

            $action = 'single-sms';
            $smsData = [
                "messages" => array([
                    'body' => $smsBody,
                    'schedule' => $schedule,
                    'from' => $apiSettings['senderNumber'],
                    'to'   => $feedData['receiver_number'],
                ])
            ];
            $this->handleSMSResponse($action, $smsData, $feed, $entry);
        }

//        service- sms campaign
        if ($feedData['list_id'] == 'sms-campaign'){
            if(empty($feedData['message_body'])      ||
                empty($feedData['campaign_list_id']) ||
                ($feedData['message_body'] == 'template-message' && empty($feedData['template_id'])) ||
                ($feedData['message_body'] == 'message-input' && empty($feedData['message']))
            ){
                do_action('ff_integration_action_result', $feed, 'failed',  'no fulfill required field');
                return;
            }
            $action = 'sms-campaign';
            $smsData = [
                'body' => $smsBody,
                'schedule' => $schedule,
                'from' => $apiSettings['senderNumber'],
                'name' => $feedData['campaign_name'],
                'list_id'   => $feedData['campaign_list_id']
            ];
            $this->handleSMSResponse($action, $smsData, $feed, $entry);

        }

//        service- add subscriber contact in list
        if($feedData['list_id'] == 'create-new-contact'){

            if(empty($feedData['phone_number']) || empty($feedData['campaign_list_id'])){
                do_action('ff_integration_action_result', $feed, 'failed',  'no fulfill required field');
                return;
            }

            $data = array();
            $data['phone_number'] = $feedData['phone_number'];

            if($feedData['other_add_contact_fields']){
                foreach ($feedData['other_add_contact_fields'] as  $field){
                    $data[$field['label']] = $field['item_value'];
                }
            }

            $response = $api->addSubscriberContact($feedData['campaign_list_id'],$data);

            if (is_wp_error($response)) {
                do_action('ff_integration_action_result', $feed, 'failed',  $response->get_error_message());
            } else {
                do_action('ff_integration_action_result', $feed, 'success', 'Clicksend SMS feed has been successfully initialed and pushed data');
            }

        }

//        service- add contact list
        if($feedData['list_id'] == 'add-contact-list'){
            if(empty($feedData['contact_list_name'])){
                do_action('ff_integration_action_result', $feed, 'failed',  'no fulfill required field');
                return;
            }

            $data = array();
            $data['list_name'] = $feedData['contact_list_name'];

            $response = $api->addContactList($data);

            if (is_wp_error($response)) {
                do_action('ff_integration_action_result', $feed, 'failed',  $response->get_error_message());
            } else {
                do_action('ff_integration_action_result', $feed, 'success', 'Clicksend SMS feed has been successfully initialed and pushed data');
            }
        }

//        service- email-campaign
        if($feedData['list_id'] == 'email-campaign'){
            if(
                empty($feedData['email_address_id']) ||
                empty($feedData['campaign_list_id']) ||
                empty($feedData['email_campaign_subject']) ||
                empty($feedData['campaign_name']) ||
                empty($feedData['email_form_name']) ||
                empty($feedData['message'])
            ){
                do_action('ff_integration_action_result', $feed, 'failed',  'no fulfill required field');
                return;
            }
            $data = array();

            $data['list_id'] = $feedData['campaign_list_id'];
            $data['subject'] = $feedData['email_campaign_subject'];
            $data['name'] = $feedData['campaign_name'];
            $data['body'] = $feedData['message'];
            $data['from_name'] = $feedData['email_form_name'];
            $data['schedule'] = $schedule;
            $data['from_email_address_id'] = $feedData['email_address_id'];
            if($feedData['email_template_id']){
                $data['template_id'] = $feedData['email_template_id'];
            }
            $response = $api->addEmailCampaign($data);

            if (is_wp_error($response)) {
                do_action('ff_integration_action_result', $feed, 'failed',  $response->get_error_message());
            } else {
                do_action('ff_integration_action_result', $feed, 'success', 'Clicksend SMS feed has been successfully initialed and pushed data');
            }
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
