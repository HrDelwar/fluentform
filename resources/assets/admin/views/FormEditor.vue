<template>
    <div class="form-editor" id="form-editor">
        <div id="js-form-editor--body" class="form-editor--body"
             :style="{width: editorConfig.bodyWidth ? editorConfig.bodyWidth + 'px' : ''}">
            <div class="form-editor__body-content">
                <div class="">
                    <!-- =========================
                         PAGING START
                    ============================== -->
                    <div v-if="haveFormSteps" class="form-step__wrapper form-step__start panel__body--item">
                        <div @click="editSelected(stepStart)" class="item-actions-wrapper hover-action-middle">
                            <div class="item-actions">
                                <i @click="editSelected(stepStart)" class="icon icon-pencil"></i>
                            </div>
                        </div>
                        <div class="step-start text-center">
                            <div class="step-start__indicator">
                                <strong>PAGING START</strong>
                                <hr>
                            </div>
                            <div class="start-of-page">
                                Click to configure your step settings
                            </div>
                        </div>
                    </div>

                    <!-- =========================
                         FORM DROPZONE
                    ============================== -->
                    <el-form :class="'label_position_org_'+original_label_placement" class="form-editor-elements"
                             :label-position="labelPlacement" label-width="120px">
                        <vddl-list class="panel__body--list"
                                   :class="{'empty-dropzone': !form.dropzone.length}"
                                   :list="form.dropzone"
                                   :drop="handleDrop"
                                   :horizontal="false">

                            <!-- Placeholder shown when element dragged -->
                            <vddl-placeholder :style="{ minHeight: dragSourceHeight }">
                            </vddl-placeholder>

                            <!-- Empty dropzone placeholder -->
                            <div v-if="!form.dropzone.length" class="empty-dropzone-placeholder">
                                <i @click.stop="editorInserterPopup(0, form.dropzone)"
                                   class="popup-search-element">+</i>
                            </div>

                            <template v-if="is_conversion_form">
                                <list-conversion v-for="(item, index) in form.dropzone"
                                :handleDragstart="handleDragstart"
                                :handleDragend="handleDragend"
                                :allElements="form.dropzone"
                                :handleEdit="editSelected"
                                :handleDrop="handleDrop"
                                :wrapper="form.dropzone"
                                :key="item.uniqElKey"
                                :editItem="editItem"
                                :index="index"
                                :item="item">
                                </list-conversion>
                            </template>
                            <template v-else>
                                <list v-for="(item, index) in form.dropzone"
                                      :handleDragstart="handleDragstart"
                                      :handleDragend="handleDragend"
                                      :allElements="form.dropzone"
                                      :handleEdit="editSelected"
                                      :handleDrop="handleDrop"
                                      :wrapper="form.dropzone"
                                      :key="item.uniqElKey"
                                      :editItem="editItem"
                                      :index="index"
                                      :item="item">
                                </list>
                            </template>

                        </vddl-list>
                    </el-form>

                    <!-- =========================
                        SUBMIT BUTTON
                    ============================== -->
                  <el-row>

                    <el-col :span="showResetButton ? 12 : 24">
                      <div v-if="showSubmitButton" class="ff_default_submit_button_wrapper">
                        <submitButton
                            v-if="Object.keys(submitButton).length"
                            :editItem="editItem"
                            :submitButton="submitButton"
                            :editSelected="editSelected"/>
                      </div>
                    </el-col>

                    <el-col :span="showSubmitButton ? 12 : 24" v-if="showResetButton">
                      <reset-button
                          :editItem="editItem"
                          :resetButton="resetButton"
                          :editSelected="editSelected"/>
                    </el-col>

                  </el-row>


                    <div v-if="!form.dropzone.length" class="ff-user-guide">
                        <div @click="introVisible = true" class="editor_play_video"><i class="el-icon-video-play"></i> Video Instruction</div>
                        <img :src="instructionImage" alt="">
                        <div class="text-align-center">
                            <el-button type="danger" @click="introVisible = true"><i class="el-icon-video-play"></i> Video Instruction</el-button>
                        </div>
                        <el-dialog
                            title="How to create a form"
                            :visible.sync="introVisible"
                            :append-to-body="true"
                            width="60%">
                            <div v-if="introVisible" class="videoWrapper">
                                <iframe width="1237" height="696" src="https://www.youtube.com/embed/ebZUein_foM?autoplay=1"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                            </div>
                            <span slot="footer" class="dialog-footer">
                            <el-button @click="introVisible = false">Close</el-button>
                          </span>
                        </el-dialog>
                    </div>

                    <!-- =========================
                         PAGING END
                    ============================== -->
                    <div v-if="haveFormSteps" class="form-step__wrapper form-step__end panel__body--item">
                        <div @click="editSelected(stepEnd)" class="item-actions-wrapper hover-action-middle">
                            <div class="item-actions">
                                <i @click="editSelected(stepEnd)" class="icon icon-pencil"></i>
                            </div>
                        </div>
                        <div class="step-start text-center">
                            <div class="start-of-page">
                                End of last page
                            </div>
                            <div class="step-start__indicator">
                                <strong>PAGING END</strong>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =========================
             SIDEBAR
        ============================== -->
        <div class="form-editor--sidebar"
             :style="{width: editorConfig.sidebarWidth ? editorConfig.sidebarWidth + 'px' : ''}">

            <div class="form-editor--sidebar-content nav-tabs new-elements">
                <ul class="nav-tab-list toggle-fields-options">
                    <li :class="fieldMode == 'add' ? 'active' : ''">
                        <a href="#" @click.prevent="changeFieldMode('add')">Input Fields</a>
                    </li>
                    <li :class="fieldMode == 'edit' ? 'active' : ''">
                        <a href="#" @click.prevent="changeSidebarMode('edit')">Input Customization</a>
                    </li>
                </ul>

                <div v-loading="!isMockLoaded"
                     element-loading-text="Loading Awesomeness..."
                     style="min-height: 150px;"
                     class="panel-full-height nav-tab-items">
                    <template v-if="isMockLoaded">
                        <!-- =========================
                             ADD FIELDS
                        ============================== -->
                        <template v-if="fieldMode == 'add'">
                            <searchElement
                                placeholder="Search (name, address)..."
                                :isSidebarSearch.sync="isSidebarSearch"
                                :moved="moved"
                                :isDisabled="isDisabled"
                                :insertItemOnClick="insertItemOnClick"
                                :list="[
                                    ...postMockList,
                                    ...taxonomyMockList,
                                    ...generalMockList,
                                    ...advancedMockList,
                                    ...containerMockList,
                                    ...paymentsMockList
                                ]"/>

                            <div class="sidebar_elements_wrapper" v-show="!isSidebarSearch">
                                <!-- Post Fields -->
                                <div
                                    v-if="isPostForm"
                                    class="option-fields-section"
                                    :class="(optionFieldsSection == 'post') ? 'option-fields-section_active' : ''"
                                >
                                    <h5 @click="toggleFieldsSection('post')"
                                        :class="optionFieldsSection == 'post' ? 'active' : ''"
                                        class="option-fields-section--title">
                                        Post Fields
                                    </h5>

                                    <transition name="slide-fade">
                                        <div v-show="optionFieldsSection == 'post'"
                                             class="option-fields-section--content">
                                            <div v-for="itemMockList, i in postMockListChunked" :key="i"
                                                 class="v-row mb15">
                                                <div class="v-col--33" v-for="itemMock, i in itemMockList" :key="i">
                                                    <vddl-draggable
                                                        class="btn-element"
                                                        :class="{ 'disabled': isDisabled(itemMock) }"
                                                        :draggable="itemMock"
                                                        :selected="insertItemOnClick"
                                                        :index="i"
                                                        :wrapper="itemMockList"
                                                        :disable-if="isDisabled(itemMock)"
                                                        :moved="moved"
                                                        effectAllowed="copy">
                                                        <i :class="itemMock.editor_options.icon_class"></i>
                                                        {{ itemMock.editor_options.title }}
                                                    </vddl-draggable>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- Taxonomy Fields -->
                                <div
                                    v-if="isPostForm"
                                    class="option-fields-section"
                                    :class="(optionFieldsSection == 'taxonomy') ? 'option-fields-section_active' : ''"
                                >
                                    <h5 @click="toggleFieldsSection('taxonomy')"
                                        :class="optionFieldsSection == 'taxonomy' ? 'active' : ''"
                                        class="option-fields-section--title">
                                        Taxonomy Fields
                                    </h5>

                                    <transition name="slide-fade">
                                        <div
                                            class="option-fields-section--content"
                                            v-show="optionFieldsSection == 'taxonomy'"
                                        >
                                            <div
                                                :key="i"
                                                class="v-row mb15"
                                                v-for="itemMockList, i in taxonomyMockListChunked"
                                            >
                                                <div
                                                    :key="i"
                                                    class="v-col--33"
                                                    v-for="itemMock, i in itemMockList"
                                                >
                                                    <vddl-draggable
                                                        class="btn-element"
                                                        :class="{ 'disabled': isDisabled(itemMock) }"
                                                        :draggable="itemMock"
                                                        :selected="insertItemOnClick"
                                                        :index="i"
                                                        :wrapper="itemMockList"
                                                        :disable-if="isDisabled(itemMock)"
                                                        :moved="moved"
                                                        effectAllowed="copy">
                                                        <i :class="itemMock.editor_options.icon_class"></i>
                                                        {{ itemMock.editor_options.title }}
                                                    </vddl-draggable>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <!-- General Fields -->
                                <div
                                    class="option-fields-section"
                                    :class="(optionFieldsSection == 'general') ? 'option-fields-section_active' : ''"
                                >
                                    <h5 @click="toggleFieldsSection('general')"
                                        :class="optionFieldsSection == 'general' ? 'active' : ''"
                                        class="option-fields-section--title">
                                        <span v-if="is_conversion_form">Available Fields</span>
                                        <span v-else>General Fields</span>
                                    </h5>

                                    <transition name="slide-fade">
                                        <div v-show="optionFieldsSection == 'general'"
                                             class="option-fields-section--content">
                                            <div v-for="(itemMockList, i) in itemMockListChunked" :key="i"
                                                 class="v-row mb15" :class="'ff_items_'+itemMockList.length">
                                                <div class="v-col--33" v-for="(itemMock, i) in itemMockList" :key="i">
                                                    <vddl-draggable
                                                        class="btn-element"
                                                        :class="{ 'disabled': isDisabled(itemMock) }"
                                                        :draggable="itemMock"
                                                        :selected="insertItemOnClick"
                                                        :index="i"
                                                        :wrapper="itemMockList"
                                                        :disable-if="isDisabled(itemMock)"
                                                        :moved="moved"
                                                        effectAllowed="copy">
                                                        <i :class="itemMock.editor_options.icon_class"></i>
                                                        {{ itemMock.editor_options.title }}
                                                    </vddl-draggable>
                                                </div>
                                            </div>
                                        </div>
                                    </transition>
                                </div>

                                <template v-if="!is_conversion_form">
                                    <!-- Advanced Fields -->
                                    <div
                                        class="option-fields-section"
                                        :class="(optionFieldsSection == 'others') ? 'option-fields-section_active' : ''"
                                    >
                                        <h5 @click="toggleFieldsSection('others')"
                                            :class="optionFieldsSection == 'others' ? 'active' : ''"
                                            class="option-fields-section--title">
                                            Advanced Fields
                                        </h5>
                                        <transition name="slide-fade">
                                            <div v-show="optionFieldsSection == 'others'"
                                                 class="option-fields-section--content">
                                                <div v-for="itemMockList, i in otherItemsMockListChunked" :key="i"
                                                     class="v-row mb15">
                                                    <div class="v-col--33" v-for="itemMock, i in itemMockList" :key="i">
                                                        <vddl-draggable
                                                            class="btn-element"
                                                            :draggable="itemMock"
                                                            :index="i"
                                                            :wrapper="itemMockList"
                                                            :selected="insertItemOnClick"
                                                            :disable-if="isDisabled(itemMock)"
                                                            :moved="moved"
                                                            effect-allowed="copy"
                                                        ><i :class="itemMock.editor_options.icon_class"></i> {{
                                                                itemMock.editor_options.title
                                                            }}
                                                        </vddl-draggable>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>

                                    <!-- Container Fields -->
                                    <div
                                        class="option-fields-section"
                                        :class="(optionFieldsSection == 'container') ? 'option-fields-section_active' : ''"
                                    >
                                        <h5 @click="toggleFieldsSection('container')"
                                            :class="optionFieldsSection == 'container' ? 'active' : ''"
                                            class="option-fields-section--title">
                                            Container
                                        </h5>
                                        <transition name="slide-fade">
                                            <div v-show="optionFieldsSection == 'container'"
                                                 class="option-fields-section--content">
                                                <div class="v-row mb15">
                                                    <div class="v-col--50" v-for="mockItem, i in containerMockList">
                                                        <vddl-draggable
                                                            class="btn-element mb15"
                                                            :draggable="mockItem"
                                                            :wrapper="containerMockList"
                                                            :index="i"
                                                            :selected="insertItemOnClick"
                                                            :moved="moved"
                                                            effect-allowed="copy"
                                                        ><i :class="mockItem.editor_options.icon_class"></i> {{
                                                                mockItem.editor_options.title
                                                            }}
                                                        </vddl-draggable>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>

                                    <!-- Payment Fields -->
                                    <div
                                        v-if="has_payment_features"
                                        class="option-fields-section"
                                        :class="(optionFieldsSection == 'payment') ? 'option-fields-section_active' : ''"
                                    >
                                        <h5 @click="toggleFieldsSection('payment')"
                                            :class="optionFieldsSection == 'payment' ? 'active' : ''"
                                            class="option-fields-section--title">
                                            Payment Fields
                                        </h5>
                                        <transition name="slide-fade">
                                            <div v-show="optionFieldsSection == 'payment'"
                                                 class="option-fields-section--content">
                                                <div v-for="(itemMockList, i) in paymentsMockListChunked" :key="i"
                                                     class="v-row mb15">
                                                    <div class="v-col--33" v-for="(itemMock, i) in itemMockList" :key="i">
                                                        <vddl-draggable
                                                            class="btn-element"
                                                            :class="{ 'disabled': isDisabled(itemMock) }"
                                                            :draggable="itemMock"
                                                            :selected="insertItemOnClick"
                                                            :index="i"
                                                            :wrapper="itemMockList"
                                                            :disable-if="isDisabled(itemMock)"
                                                            :moved="moved"
                                                            effectAllowed="copy">
                                                            <i :class="itemMock.editor_options.icon_class"></i>
                                                            {{ itemMock.editor_options.title }}
                                                        </vddl-draggable>
                                                    </div>
                                                </div>
                                            </div>
                                        </transition>
                                    </div>
                                </template>

                            </div>
                        </template>

                        <!-- =========================
                             EDIT FIELDS
                        ============================== -->
                        <template
                            v-if="fieldMode == 'edit' && Object.keys(editItem).length">
                            <div v-loading="sidebarLoading" style="min-height: 100px;">
                                <EditorSidebar
                                    v-if="!sidebarLoading"
                                    :editItem="editItem"
                                    :form_items="form.dropzone"
                                />
                            </div>
                        </template>
                    </template>
                </div>
            </div>
        </div>

        <!-- OTHER MODAL/POPUP COMPONENTS -->
        <ItemDisabled
            :visibility.sync="whyDisabledModal"
            :modal="itemDisableConditions[whyDisabledModal]">
        </ItemDisabled>

        <editorInserter
            :dropzone="dropzone"
            :postMockList="postMockList"
            :taxonomyMockList="taxonomyMockList"
            :generalMockList="generalMockList"
            :paymentsMockList="paymentsMockList"
            :advancedMockList="advancedMockList"
            :visible.sync="editorInserterVisible"
            :containerMockList="containerMockList"
            :insertItemOnClick="insertItemOnClick"/>

        <RenameForm
            v-if="form.title"
            :formTitle="form.title"
            @rename-success="formRenameSuccess"
            :visible.sync="renameFormVisibility"/>
    </div>
</template>

<script type="text/babel">
import {mapActions, mapGetters, mapMutations} from 'vuex';
import Clipboard from 'clipboard';
import List from '../components/nested-list.vue';
import ListConversion from '../components/nested-list-conversion.vue';
import recaptcha from '../components/modals/Recaptcha.vue';
import searchElement from '../components/searchElement.vue';
import EditorSidebar from '../components/EditorSidebar.vue';
import RenameForm from '../components/modals/RenameForm.vue';
import ItemDisabled from '../components/modals/ItemDisabled.vue';
import submitButton from '../components/templates/submitButton.vue';
import resetButton from "../components/templates/resetButton";
import editorInserter from '../components/includes/editor-inserter.vue';

export default {
    name: 'FormEditor',
    props: [
        'form',
        'save_form',
        'form_saving'
    ],
    components: {
        List,
        ListConversion,
        recaptcha,
        RenameForm,
        ItemDisabled,
        submitButton,
        EditorSidebar,
        searchElement,
        editorInserter,
        resetButton
    },
    data() {
        return {
            form_id: window.FluentFormApp.form_id,
            labelPlacement: 'top',
            original_label_placement: 'top',
            isMockLoaded: false,
            editorConfig: {
                bodyWidth: 0,
                sidebarWidth: 0
            },
            editItem: {},
            disable: false,
            dragSourceHeight: null,
            whyDisabledModal: '',
            optionFieldsSection: window.FluentFormApp.form.type === 'post' ? 'post' : 'general',
            nameEditable: false,
            postMockList: [],
            taxonomyMockList: [],
            generalMockList: [],
            advancedMockList: [],
            paymentsMockList: [],
            containerMockList: [],
            itemDisableConditions: {},
            searchElementStr: '',
            searchResult: [],
            isSidebarSearch: false,
            editorInserterVisible: false,
            insertNext: {
                index: null,
                wrapper: null
            },
            renameFormVisibility: false,
            editorInserterInContainer: false,
            instructionImage: FluentFormApp.plugin_public_url + 'img/help.png',
            has_payment_features: FluentFormApp.has_payment_features,
            introVisible: false,
            showResetButton: false,
        }
    },
    computed: {
        ...mapGetters({
            fieldMode: 'fieldMode',
            sidebarLoading: 'sidebarLoading',
            showSubmitButton : 'showSubmitButton'
        }),

        /**
         * Make chunks of item draggable
         * in post section
         * @return {Array}
         */
        postMockListChunked() {
            return _ff.chunk(this.postMockList, 3);
        },

        /**
         * Make chunks of item draggable
         * in post section
         * @return {Array}
         */
        paymentsMockListChunked() {
            return _ff.chunk(this.paymentsMockList, 3);
        },

        /**
         * Make chunks of item draggable
         * in taxonomy section
         * @return {Array}
         */
        taxonomyMockListChunked() {
            return _ff.chunk(this.taxonomyMockList, 3);
        },

        /**
         * Make chunks of item draggable
         * in general section
         * @return {Array}
         */
        itemMockListChunked() {
            return _ff.chunk(this.generalMockList, 3);
        },

        /**
         * Make chunks of item draggable
         * in advanced section
         * @return {Array}
         */
        otherItemsMockListChunked() {
            return _ff.chunk(this.advancedMockList, 3);
        },

        /**
         * Default form elements
         * @return {Array}
         */
        dropzone() {
            return this.form.dropzone;
        },

        /**
         * Submit button object
         * @return {Object}
         */
        submitButton() {
            return this.form.submitButton;
        },

        resetButton() {
          return this.form.resetButton;
        },

        /**
         * Checks if the form have steps in it
         * @return {boolean}
         */
        haveFormSteps() {
            let result = false;
            _ff.map(this.form.dropzone, (field) => {
                if (field && field.editor_options && field.editor_options.template == "formStep") {
                    return result = true;
                }
            });
            return result;
        },

        /**
         * Checks how many form steps have in it
         * @return {number}
         */
        formStepsCount() {
            let count = 1;
            _ff.map(this.form.dropzone, (field) => {
                if (field && field.editor_options && field.editor_options.template == "formStep") {
                    count++;
                }
            });
            return count;
        },

        /**
         * Form step beginning options
         * @return {Object}
         */
        stepStart() {
            return this.form.stepsWrapper.stepStart;
        },

        /**
         * Form step ending options
         * @return {Object}
         */
        stepEnd() {
            return this.form.stepsWrapper.stepEnd;
        },
        isPostForm() {
            return window.FluentFormApp.form.type === 'post';
        }
    },
    watch: {
        form_saving() {
            const saveBtn = jQuery('#saveFormData');
            if (this.form_saving) {
                this.clearEditableObject(); // Empty {editItem} after form saved
                saveBtn.text('Saving Form');
            } else {
                saveBtn.text('Save Form');
            }
        },

        formStepsCount() {
            if (this.stepStart && this.stepStart.settings.step_titles.length > this.formStepsCount) {
                this.stepStart.settings.step_titles.splice(this.formStepsCount);
            }
        },

        /**
         * Remove step staring & ending options if no steps found
         * Adds step staring & ending options if any steps found
         */
        haveFormSteps() {
            if (this.haveFormSteps) {
                this.form.stepsWrapper = {
                    stepStart: this.form.stepStart,
                    stepEnd: this.form.stepEnd
                }
            } else {
                this.$delete(this.form.stepsWrapper, 'stepStart');
                this.$delete(this.form.stepsWrapper, 'stepEnd');
            }
        }
    },
    methods: {
        ...mapMutations({
            changeFieldMode: 'changeFieldMode',
            updateSidebar: 'updateSidebar',
        }),

        ...mapActions(['loadEditorShortcodes']),

        moved(o) {
            // vddl has issue with this method.
            // we can remove this method once fixed.
        },

        // probably not in use (need to re-check)
        enableNameEditable() {
            this.nameEditable = true;
            jQuery('.nameEditableInput').find('input').focus();
        },

        /**
         * Empty {editItem} and restore sidebar to add item mode
         */
        clearEditableObject() {
            this.changeFieldMode('add');
            this.editItem = {};
        },

        /**
         * Toggle between 'Add Fields' & 'Field Options'
         * from the Editor sidebar
         * @param mode
         */
        changeSidebarMode(mode) {
            this.isSidebarSearch = false;
            this.updateSidebar();
            this.changeFieldMode(mode);
            if (_ff.isEmpty(this.editItem)) {
                // if (this.form.dropzone[0].element != 'container') {
                this.editItem = this.form.dropzone[0];
                // }
            }
        },

        /**
         * Actions after Drag & Drop an element
         * @param {Object|vddl prams}
         * @return {void}
         */
        handleDrop({index, list, item, type}) {
            if (type != 'existingElement') {
                this.makeUniqueNameAttr(this.form.dropzone, item);
            }
            if (item.element == 'container' && this.form.dropzone != list) {
                this.$message({
                    message: 'You can not insert a container into another.',
                    type: 'warning',
                });
                return false;
            }

            item.uniqElKey = 'el_' + new Date().getTime();

            list.splice(index, 0, item);
        },

        /**
         * Insert element into form on click sidebar buttons
         * @param item
         * @return {void}
         */
        insertItemOnClick(item, target) {
            if (this.itemDisableConditions.hasOwnProperty(item.element) && this.itemDisableConditions[item.element].disabled) {
                this.whyDisabledModal = item.element;
                return;
            }
            let freshCopy = _ff.cloneDeep(item);
            const $target = jQuery(target);

            if (target && !target.draggable
                && $target.hasClass('disabled')
                || $target.parents().hasClass('disabled')) {
                return this.showWhyDisabled(item);
            }

            if (this.editorInserterInContainer && freshCopy.element == 'container') {
                this.$message({
                    message: 'You can not insert a container into another.',
                    type: 'warning',
                });

                return;
            }

            this.makeUniqueNameAttr(this.form.dropzone, freshCopy);

            if (this.insertNext.index != null) {
                this.insertNext.wrapper.splice(this.insertNext.index + 1, 0, freshCopy);
                this.editorInserterDismiss();
                this.editItem = freshCopy;
                this.changeSidebarMode('edit');
            } else {
                this.form.dropzone.push(freshCopy);
            }
        },

        /**
         * Show a modal for disabled components
         */
        showWhyDisabled(item) {
            this.whyDisabledModal = item.editor_options.why_disabled_modal;
        },

        /**
         * Action for clicking on edit icon
         * from an element
         * @param item
         * @param mode
         */
        editSelected(item, mode = 'edit') {
            this.updateSidebar();
            this.changeFieldMode(mode);

            this.editItem = item;

            jQuery('html, body').animate({
                scrollTop: 0
            }, 300);
        },

        /**
         * Checks if an item is disabled
         * @param item
         * @return {boolean}
         */
        isDisabled(item) {
            if (this.itemDisableConditions.hasOwnProperty(item.element)) {
                return this.itemDisableConditions[item.element].disabled;
            }
            return false;
        },

        /**
         * Fetch default elements from the server
         * prepare those for editor render.
         */
        initiateMockLists() {
            FluentFormsGlobal.$get({
                action: 'fluentform-load-editor-components',
                formId: window.FluentFormApp.form.id
            })
                .done(response => {
                    _ff.each(response.data.components, (components, key) => {
                        this[`${key}MockList`] = components;
                    });

                    this.itemDisableConditions = response.data.disabled_components;

                    this.isMockLoaded = true;
                })
                .fail(res => console.log(res));
        },


        /**
         * Rearrange elements in the editor
         * Action when drag starts
         * @param el
         */
        handleDragstart(el) {
            var height = jQuery(el).height() + 20;
            this.dragSourceHeight = height + 'px';
        },

        /**
         * Rearrange elements in the editor
         * Action when drag ends
         * @param el
         */
        handleDragend(el) {
            this.dragSourceHeight = null;
        },

        /**
         * Rename the form in the editor
         */
        renameForm() {
            this.save_form();
            this.nameEditable = false;
        },

        /**
         * Fetch form settings from the server.
         * And do necessary adjustments to the editor
         */
        fetchSettings() {
            FluentFormsGlobal.$get({
                action: 'fluentform-settings-formSettings',
                form_id: this.form_id,
                meta_key: 'formSettings'
            })
                .done(response => {
                    let result = response.data.result[0];
                    if (result && result.value) {
                        let settings = result.value;
                        this.showResetButton = settings.resetButton;
                        this.original_label_placement = settings.layout.labelPlacement;
                        if (settings.layout.labelPlacement == 'hide_label') {
                            settings.layout.labelPlacement = 'top';
                        } else {
                            this.labelPlacement = settings.layout.labelPlacement;
                        }
                    }
                })
                .fail(res => {
                    // ...
                });
        },

        /**
         * Clean any data binding associating with it
         * once the element is removed
         */
        garbageCleaner() {
            FluentFormEditorEvents.$on('onElRemoveSuccess', field => {
                // Reset Editable Object
                if (!_ff.isEmpty(this.editItem) && this.editItem.attributes.name == field)
                    this.clearEditableObject();

                // remove garbage conditional logics
                // recursively from nested elements
                this.mapElements(this.dropzone, (el) => {
                    const conditions = el.settings.conditional_logics && el.settings.conditional_logics.conditions;
                    _ff.map(conditions, (condition, index) => {
                        if (condition) {
                            if (condition.field == field || condition.field.startsWith(field)) {
                                conditions.splice(index, 1);
                            }
                        }
                        if (!conditions.length) {
                            el.settings.conditional_logics.status = false;
                        }
                    });
                });
            });
        },

        /**
         * Inject save form button to navigation
         * Initiate click and save event
         **/
        initSaveBtn() {
            const self = this;
            var saveButton = jQuery('<button />', {
                id: 'saveFormData',
                class: 'el-button el-button--primary el-button--small',
                text: 'Save Form'
            });
            saveButton.on('click', function () {
                const $this = jQuery(this);
                if (!$this.data('text')) $this.data('text', $this.text());
                $this.text('Saving Form');
                self.save_form();
            });

            jQuery('.ff-navigation-right').append(saveButton);

            var screenButton = jQuery('<span />', {
                id: 'switchScreen',
                class: 'ff_icon_switch_full el-icon-full-screen',
                text: ''
            });

            screenButton.on('click', function () {
                const $body = jQuery('body');
                let wasFullScreen = $body.hasClass('ff_full_screen');
                if (window.localStorage) {
                    if (wasFullScreen) {
                        window.localStorage.setItem('ff_is_full_screen', 'no');
                    } else {
                        window.localStorage.setItem('ff_is_full_screen', 'yes');
                    }
                }
                $body.toggleClass('ff_full_screen');
            });

            if (window.localStorage) {
                if (window.localStorage.getItem('ff_is_full_screen') == 'yes') {
                    jQuery('body').addClass('ff_full_screen').addClass('folded');
                }
            } else {
                jQuery('body').addClass('ff_full_screen').addClass('folded');
            }

            jQuery('.ff-navigation-right').append(screenButton);
        },

        /**
         * Hide editor inserter popup
         * Clean associated data
         */
        editorInserterDismiss() {
            this.insertNext.index = null;
            this.insertNext.wrapper = null;
            this.editorInserterVisible = false;
            this.editorInserterInContainer = false;
            jQuery('.js-editor-item').removeClass('is-editor-inserter');
        },

        /**
         * Show editor inserter popup event
         */
        editorInserterPopup(index, wrapper) {
            FluentFormEditorEvents.$emit('editor-inserter-popup', index, wrapper, this.$el);
        },

        /**
         * Initiate form rename popup
         */
        initRenameForm() {
            jQuery('#js-ff-nav-title')
                .on('click', _ => this.renameFormVisibility = true)
                .prepend('<i class="el-icon-edit"></i> ')
                .css('cursor', 'pointer');
        },

        /**
         * Event for the form rename success
         * passed by the component
         */
        formRenameSuccess(title) {
            this.form.title = title;
            jQuery('#js-ff-nav-title').find('span').text(title);
        }
    },
    created() {
        /**
         * Event: To clear editor inserter dependency
         * and hide from view
         * @augments {event}
         */
        FluentFormEditorEvents.$on('editor-inserter-dismiss', this.editorInserterDismiss);

        /**
         * Event: On editor inserter popup visible
         * @augments {index, wrapper, elDOM}
         */
        FluentFormEditorEvents.$on('editor-inserter-popup', (index, wrapper, elDOM) => {
            this.editorInserterDismiss();
            jQuery(elDOM).find('.js-editor-item').first().addClass('is-editor-inserter');

            this.insertNext = {index, wrapper};
            this.$nextTick(_ => this.editorInserterVisible = true);
        });

        /**
         * Event listener
         * If the editor inserter popup is triggered from a container element
         */
        FluentFormEditorEvents.$on('editor-inserter-in-container', _ => {
            this.editorInserterInContainer = true;
        });
    },
    mounted() {
        this.fetchSettings();
        this.initiateMockLists();
        this.garbageCleaner();
        this.initSaveBtn();
        this.initRenameForm();

        /**
         * Dismiss editor inserter popup when clicked outside
         */
        jQuery(document).on('click', this.editorInserterDismiss);

        /**
         * Copy to clip board
         * @type {Clipboard}
         */
        (new Clipboard('.copy')).on('success', (e) => {
            this.$message({
                message: 'Copied to Clipboard!',
                type: 'success'
            });
        });
    }
};
</script>

