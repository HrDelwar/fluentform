import Vue from 'vue';
import locale from 'element-ui/lib/locale';
import lang from 'element-ui/lib/locale/lang/en';

import Settings from './Settings.vue';
import reCaptcha from './reCaptcha.vue';
import hCaptcha from './hCaptcha.vue';
import pdf_settings from './Pdf.vue';
import GeneralIntegrationSettings from './GeneralIntegrationSettings';
import DoubleOptinSettings from './DoubleOptinSettings';

import {
    Button,
    Radio,
    RadioGroup,
    Form,
    FormItem,
    Input,
    Tooltip,
    Row,
    Col,
    Select,
    Option,
    OptionGroup,
    Switch,
    Dialog,
    Loading,
    Notification,
    Checkbox,
    CheckboxGroup,
    ColorPicker,
    InputNumber
} from 'element-ui';

locale.use(lang);
Vue.use(Button);
Vue.use(Form);
Vue.use(Row);
Vue.use(Col);
Vue.use(Input);
Vue.use(Checkbox);
Vue.use(CheckboxGroup);
Vue.use(Select);
Vue.use(Option);
Vue.use(OptionGroup);
Vue.use(Switch);
Vue.use(Tooltip);
Vue.use(FormItem);
Vue.use(Loading.directive);
Vue.use(InputNumber);
Vue.use(ColorPicker);
Vue.use(RadioGroup);
Vue.use(Radio);
Vue.use(Dialog);
Vue.prototype.$notify = Notification;
Vue.prototype.$loading = Loading.service;

new Vue({
    el: '#ff_global_settings_option_app',
    components: {
        settings: Settings,
        re_captcha: reCaptcha,
        h_captcha: hCaptcha,
        pdf_settings: pdf_settings,
        'general-integration-settings': GeneralIntegrationSettings,
        'double_optin_settings': DoubleOptinSettings
    },
    data: {
        component: 'settings',
        App: window.FluentFormApp,
        settings_key: ''
    },
    methods: {
        setRoute(hash) {
            // get component by hash
            let $el = jQuery('.ff_settings_list li').find('a[data-hash=' + hash + ']');
            let component = hash;
            if ($el.data('component')) {
                component = $el.data('component');
            } else {
                $el = jQuery('.ff_settings_list li').find('a[data-component=' + hash + ']');
            }
            if (this.$options.components[component]) {
                jQuery('.ff_settings_list li').removeClass('active');
                $el.parent().addClass('active');
                this.settings_key = jQuery($el).attr('data-settings_key');
                this.component = component;
            }
        }
    },
    created() {
        let hash = location.hash.substr(1);
        if (hash) {
            this.setRoute(hash);
        }
        const that = this;
        jQuery('.ff_settings_list li a').on('click', function () {
            let hash = jQuery(this).attr('data-hash');
            if (hash) {
                that.setRoute(hash);
            }
            jQuery(this).parent().addClass('active');
        });
    }
});
