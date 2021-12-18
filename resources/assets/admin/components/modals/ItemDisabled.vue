<template>
    <div :class="{'ff_backdrop': visibility}">
        <el-dialog
            title="Field disabled"
            :visible.sync="isVisible"
            :before-close="close"
            width="50%">
            <template v-if="contentComponent">
                <component :is="contentComponent"></component>
            </template>

            <template v-else>
                <div style="text-align: center;">
                    <div v-if="modal && modal.is_payment">
                        <h2>Fluent Forms Payment Module</h2>
                        <p>Accept Payment online as part of the Forms submission process. With Fluent Forms Powerful payment integration, you can easily accept and process payments in your Fluent Forms via Stripe / PayPal. Payment Module is available on Pro Version.</p>
                        <a  target="_blank"
                            class="el-button el-button--danger"
                            :href="pay_campaignUrl" >
                            Upgrade to Pro Now
                        </a>
                    </div>
                    <div v-else-if="modal && modal.disable_html">
                        <div v-html="modal.disable_html"></div>
                    </div>
                    <div v-else>
                        <p style="margin-bottom: 30px; font-size: 18px;">This field is only available on pro add-on</p>
                        <a  target="_blank"
                            class="el-button el-button--danger"
                            :href="campaignUrl" >
                            Upgrade to Pro Now
                        </a>
                    </div>

                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script>
    import recaptcha from './Recaptcha.vue';
    import hcaptcha from './Hcaptcha.vue';

    export default {
        name: 'ItemDisabled',
        props: ['visibility', 'modal', 'value'],
        components: { hcaptcha, recaptcha },
        data() {
            return {
                contentComponent: '',
                campaignUrl: window.FluentFormApp.upgrade_url || 'https://wpmanageninja.com/downloads/fluentform-pro-add-on/?utm_source=fluentform&utm_medium=wp&utm_campaign=wp_plugin&utm_term=upgrade&utm_content=pop',
                pay_campaignUrl: window.FluentFormApp.upgrade_url || 'https://wpmanageninja.com/downloads/fluentform-pro-add-on/?utm_source=fluentform&utm_medium=wp_payment&utm_campaign=wp_plugin&utm_term=upgrade&utm_content=pop'
            }
        },
        watch: {
            modal() {
                if (this.modal && this.modal.contentComponent) {
                    this.contentComponent = this.modal.contentComponent
                }
            }
        },
        computed: {
            isVisible() {
                return !!this.visibility || !!this.value;
            }
        },
        methods: {
            close() {
                this.$emit('update:visibility', false);
                this.$emit('input', false);
                setTimeout(() => {
                    this.contentComponent = '';
                }, 350);
            }
        }
    }
</script>