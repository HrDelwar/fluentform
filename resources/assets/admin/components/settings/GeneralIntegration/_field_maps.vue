<template>
    <div class="ff_merge_fields">
        <table v-if="appReady" class="ff_inner_table" width="100%">
            <thead>
            <tr>
                <th class="text-left" width="50%">{{field.field_label_remote}}</th>
                <th class="text-left" width="50%">{{field.field_label_local}}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="primary_field in field.primary_fileds">
                <td :class="(primary_field.required) ? 'is-required' : ''" class="el-form-item">
                    <label class="el-form-item__label">{{primary_field.label}}</label>
                </td>
                <td>
                    <div style="position: relative; margin-bottom: 15px;">
                        <el-select
                            v-if="primary_field.input_options == 'emails'"
                            v-model="settings[primary_field.key]"
                            placeholder="Select a Field"
                            style="width:100%"
                            clearable
                        >
                            <el-option
                                v-for="(option, index) in inputs"
                                v-if="option.attributes.type === 'email'"
                                :key="index" :value="option.attributes.name"
                                :label="option.admin_label"
                            ></el-option>
                        </el-select>

                        <el-select
                            v-else-if="primary_field.input_options == 'all'"
                            v-model="settings[primary_field.key]"
                            placeholder="Select a Field"
                            style="width:100%"
                            clearable
                        >
                            <el-option
                                v-for="(option, index) in inputs"
                                :key="index" :value="option.attributes.name"
                                :label="option.admin_label"
                            ></el-option>
                        </el-select>


                        <template v-else>
                            <field-general
                                :editorShortcodes="editorShortcodes"
                                v-model="settings[primary_field.key]"
                            ></field-general>
                        </template>

                        <div
                            style="color: #999;font-size: 12px;line-height: 15px;font-style: italic;"
                            class="primary_field_help_text"
                            v-if="primary_field.help_text"
                        >{{ primary_field.help_text }}</div>

                        <error-view field="fieldEmailAddress" :errors="errors"></error-view>
                    </div>
                </td>
            </tr>
            <tr v-if="field.default_fields" v-for="default_field in field.default_fields" :key="default_field.name">
                <td :class="(default_field.required) ? 'is-required' : ''" class="el-form-item">
                    <label class="el-form-item__label">{{default_field.label}}</label>
                </td>
                <td>
                    <div style="position: relative; margin-bottom: 15px;">
                        <field-general
                            :editorShortcodes="editorShortcodes"
                            v-model="settings.default_fields[default_field.name]"
                        ></field-general>
                        <error-view field="default_fields" :errors="errors"></error-view>
                    </div>
                </td>
            </tr>
            <tr v-for="(field, field_index) in merge_fields" :key="field.key || field">
                <td class="el-form-item">

                    <label class="el-form-item__label">
                      <template >
                        {{field.label || field}}
                        <el-tooltip
                            v-if="field.tips"
                            class="item"
                            effect="light"
                            placement="bottom-start"
                        >
                          <div slot="content">
                            <p v-html="field.tips"></p>
                          </div>
                          <i class="el-icon-info el-text-info"></i>
                        </el-tooltip>
                      </template>
                    </label>
                </td>
                <td>
                  <template v-if="field.component == 'select'">
                    <el-select
                        filterable
                        clearable
                        :multiple="field.is_multiple"
                        v-model="settings[field.key]"
                        :placeholder="field.placeholder">
                      <el-option
                          v-for="(list_name, list_key) in field.options"
                          :key="list_key"
                          :value="list_key"
                          :label="list_name"
                      ></el-option>
                    </el-select>
                    <error-view :field="field.key" :errors="errors"></error-view>
                  </template>


                  <template v-else-if="field.component == 'value_text'">
                    <field-general
                        :editorShortcodes="editorShortcodes"
                        v-model="settings[field.key]"
                    ></field-general>
                    <p v-if="field.inline_tip" v-html="field.inline_tip"></p>
                  </template>

                  <template v-else-if="field.component == 'value_textarea'">
                    <field-general
                        field_type="textarea"
                        :editorShortcodes="editorShortcodes"
                        v-model="settings[field.key]"
                    ></field-general>
                    <p v-if="field.inline_tip" v-html="field.inline_tip"></p>
                  </template>


                  <template v-else-if="field.component == 'datetime'">
                    <el-date-picker
                        v-model="settings[field.key]"
                        type="datetime"
                        format="yyyy/MM/dd HH:mm:ss"
                        v-on:change="handleChange($event, field.key)"
                        :placeholder="field.placeholder"
                    >
                    </el-date-picker>
                    <p v-if="field.inline_tip" v-html="field.inline_tip"></p>
                  </template>

                  <template v-else-if="field.component == 'dropdown_many_fields'">
                    <drop-down-many-fields
                        :errors="errors"
                        :inputs="inputs"
                        :field="field"
                        :settings="settings"
                        :editorShortcodes="editorShortcodes"
                    />
                  </template>

                  <template v-else-if="field.component == 'chained_select'">
                    <chained-selects
                        v-if="has_pro"
                        :settings="settings"
                        v-model="settings[field.key]"
                        :field="field"
                    ></chained-selects>
                    <p style="color: red;" v-else>
                      This field only available on pro version.
                      Please install Fluent Forms Pro.
                    </p>
                  </template>

                  <template v-else>
                    <field-general
                        :editorShortcodes="editorShortcodes"
                        v-model="merge_model[field_index]"
                    ></field-general>
                  </template>

                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script type="text/babel">
    import ErrorView from '../../../../common/errorView';
    import FieldGeneral from './_FieldGeneral'
    import ChainedSelects from "./_ChainedSelects";
    import DropDownManyFields from "./_DropdownManyFields";


    export default {
        name: 'field_maps',
        components: {
          DropDownManyFields,
            ErrorView,
            FieldGeneral,
            ChainedSelects,
        },
        props: ['settings', 'merge_fields', 'field', 'inputs', 'errors', 'merge_model', 'editorShortcodes','has_pro'],
        data() {
            return {
                appReady: false
            }
        },
        methods:{
          handleChange : function (value,key){
            let date = value.toString().replace(value.toString().match(/\(([A-Za-z\s].*)\)/)[0],'').trim();
            this.settings[key] = date;
          }
        },

        mounted() {
            if (Array.isArray(this.merge_model) || !this.merge_model) {
                this.merge_model = {};
            }
            this.appReady = true;
        }

    };
</script>
