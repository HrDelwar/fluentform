import './helpers';
import Vue from 'vue';
import Vddl from 'vddl';

import store from './store';
import './css/element-variables.scss';
import {
    Button,
    Checkbox,
    CheckboxGroup,
    Col,
    ColorPicker,
    Dialog,
    Dropdown,
    DropdownItem,
    DropdownMenu,
    Form,
    FormItem,
    Input,
    Slider,
    Loading,
    Message,
    Notification,
    Option,
    Popover,
    Radio,
    RadioButton,
    RadioGroup,
    Rate,
    Row,
    Select,
    TabPane,
    Tabs,
    Tooltip,
    Upload,
    Switch,
    InputNumber,
    Card
} from 'element-ui';

import lang from 'element-ui/lib/locale/lang/en';
import locale from 'element-ui/lib/locale';
import mixins from './editor_mixins';
// Global error handling...
import Errors from '../common/Errors';
import FormEditor from './views/FormEditor.vue';
import {mapActions} from 'vuex';

Vue.use(Vddl);

Vue.use(Rate);
Vue.use(Tabs);
Vue.use(TabPane);
Vue.use(ColorPicker);
Vue.use(Dropdown);
Vue.use(DropdownMenu);
Vue.use(DropdownItem);
Vue.use(Select);
Vue.use(Slider);
Vue.use(Option);
Vue.use(Popover);
Vue.use(Checkbox);
Vue.use(CheckboxGroup);
Vue.use(Row);
Vue.use(Col);
Vue.use(RadioButton);
Vue.use(RadioGroup);
Vue.use(Radio);
Vue.use(Input);
Vue.use(Dialog);
Vue.use(Button);
Vue.use(Form);
Vue.use(FormItem);
Vue.use(Tooltip);
Vue.use(Upload);
Vue.use(Switch);
Vue.use(InputNumber);

Vue.use(Loading.directive);
Vue.prototype.$loading = Loading.service;
Vue.prototype.$notify = Notification;
Vue.prototype.$message = Message;

// configure language
locale.use(lang);

window.FluentFormEditorEvents = new Vue();

Vue.mixin(mixins);

global.Errors = Errors;

new Vue({
    el: "#ff_form_editor_app",
    store,
    components: {
        ff_form_editor: FormEditor
    },
    data: {
        form_id: window.FluentFormApp.form_id,
        form: {
            title: "",
            dropzone: [],
            submitButton: {},
            resetButton:{},
            stepsWrapper: {},
            stepStart: {
                element: "step_start",
                attributes: {
                    id: "",
                    class: ""
                },
                settings: {
                    progress_indicator: "progress-bar",
                    step_titles: [],
                    disable_auto_focus: 'no',
                    enable_auto_slider: 'no',
                    enable_step_data_persistency: 'no',
                    enable_step_page_resume: 'no'
                },
                editor_options: {
                    title: "Start Paging"
                }
            },
            stepEnd: {
                element: "step_end",
                attributes: {
                    id: "",
                    class: ""
                },
                settings: {
                    prev_btn: {
                        type: "default",
                        text: "Previous",
                        img_url: ""
                    }
                },
                editor_options: {
                    title: "End Paging"
                }
            }
        },
        submitButtonMock: {
            uniqElKey: "el_" + Date.now(),
            element: "button",
            attributes: {
                type: "submit",
                class: ""
            },
            settings: {
                align: "left",
                button_style: "default",
                container_class: "",
                help_message: "",
                button_size: "md",
                button_ui: {
                    type: "default",
                    text: "Submit Form",
                    img_url: ""
                },
                conditional_logics: [],
                normal_styles: {
                    'backgroundColor' : '#409EFF',
                    'borderColor'     : '#409EFF',
                    'color'           : '#ffffff',
                    'borderRadius'    : '',
                    'minWidth'        : ''
                },
                hover_styles: {
                    'backgroundColor' : '#ffffff',
                    'borderColor'     : '#409EFF',
                    'color'           : '#409EFF',
                    'borderRadius'    : '',
                    'minWidth'        : ''
                }
            },
            editor_options: {
                title: "Submit Button"
            }
        },
        resetButtonMock: {
            uniqElKey: "el_" + Date.now(),
            element: "button",
            attributes: {
                type: "reset",
                class: ""
            },
            settings: {
                align: "right",
                background_color: "#F56C6C",
                color:"#ffffff",
                button_style: "red",
                container_class: "",
                help_message: "",
                button_size: "md",
                button_ui: {
                    type: "default",
                    text: "Reset Form",
                    img_url: ""
                },
                conditional_logics: [],
                normal_styles: {
                    'backgroundColor' : '#F56C6C',
                    'borderColor'     : '#F56C6C',
                    'color'           : '#ffffff',
                    'borderRadius'    : '',
                    'minWidth'        : ''
                },
                hover_styles: {
                    'backgroundColor' : '#F56C88',
                    'borderColor'     : '#F56C88',
                    'color'           : '#ffffff',
                    'borderRadius'    : '',
                    'minWidth'        : ''
                }
            },
            editor_options: {
                title: "Reset Button"
            }
        },
        loading: false,
        form_saving: false
    },
    methods: {
        ...mapActions(["loadEditorShortcodes"]),

        /**
         * Prepare the form for the dropzone
         * after fetching from the server
         */
        prepareForm() {
            var form = window.FluentFormApp.form;
            let formData = form.form_fields ? JSON.parse(form.form_fields) : {};

            this.form.id = form.id;
            this.form.title = form.title;
            this.form.dropzone = formData.fields || [];
            let button = formData.submitButton || JSON.parse(JSON.stringify(this.submitButtonMock));
            let resetButton = formData.resetButton || JSON.parse(JSON.stringify(this.resetButtonMock));

            if (!button.settings.conditional_logics) {
                // we will implement later
                //  button.settings.conditional_logics = [];
            }

            this.form.submitButton = button;
            this.form.resetButton = resetButton;

            this.form.stepsWrapper = formData.stepsWrapper || this.form.stepsWrapper;

            if (formData.stepsWrapper && formData.stepsWrapper.stepStart) {
                if(!formData.stepsWrapper.stepStart.settings.disable_auto_focus) {
                    formData.stepsWrapper.stepStart.settings.disable_auto_focus = 'no';
                }
                
                if(!formData.stepsWrapper.stepStart.settings.enable_auto_slider) {
                    formData.stepsWrapper.stepStart.settings.enable_auto_slider = 'no';
                }

                if(!formData.stepsWrapper.stepStart.settings.enable_step_data_persistency) {
                    formData.stepsWrapper.stepStart.settings.enable_step_data_persistency = 'no';
                }

                if(!formData.stepsWrapper.stepStart.settings.enable_step_page_resume) {
                    formData.stepsWrapper.stepStart.settings.enable_step_page_resume = 'no';
                }

                this.form.stepStart = formData.stepsWrapper.stepStart;
            }

            if (formData.stepsWrapper && formData.stepsWrapper.stepEnd) {
                this.form.stepEnd = formData.stepsWrapper.stepEnd;
            }

            this.initNotifyUnsaved();
        },

        /**
         * The the form data into the server
         */
        saveForm() {
            this.form_saving = true;
            let formFields = {
                fields: this.form.dropzone,
                submitButton: this.form.submitButton,
                resetButton: this.form.resetButton
            };

            if (!_ff.isEmpty(this.form.stepsWrapper)) {
                formFields.stepsWrapper = this.form.stepsWrapper;
            }

            let data = {
                action: 'fluentform-form-update',
                formId: this.form_id,
                title: this.form.title,
                formFields: JSON.stringify(formFields)
            };

            FluentFormsGlobal.$post(data)
                .done(response => {
                    this.$notify({
                        title: "Success",
                        message: response.message,
                        type: "success",
                        position: "bottom-right"
                    });
                    this.form_saving = false;
                    FluentFormApp.isDirty = false;

                    const saveFormBtn = jQuery("#saveFormData");
                    saveFormBtn.text(saveFormBtn.data("text"));

                    // Update the hash now.
                    this.saveHash();
                })
                .error(error => {
                    this.$notify.error({
                        title: "Oops",
                        message: error.responseJSON.title,
                        offset: 30
                    });
                    this.form_saving = false;
                });
        },

        initNotifyUnsaved() {
            // Save the initial save.
            this.saveHash();

            jQuery(window).on('beforeunload', () => {
                if (this.dropzoneHash !== JSON.stringify(this.form.dropzone)) return true;
            });
        },

        saveHash() {
            this.dropzoneHash = JSON.stringify(this.form.dropzone);
        }
    },
    mounted() {
        this.prepareForm();
        this.loadEditorShortcodes(this.form_id);
        if(this.is_conversion_form) {
            jQuery('#wpcontent').addClass('ff_conversion_editor');
        }
    },
    beforeCreate() {
        // Event listener for page title updater
        this.$on("change-title", module => {
            jQuery("title").text(`${module} - Fluentform`);
        });
        this.$emit("change-title", "Editor");
    }
});
