import Vue from 'vue';
import Vuex from 'vuex';
import createLogger from 'vuex/dist/logger';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

import mutations from './mutations'
import actions from './actions'

export default new Vuex.Store({
    state: {
        fieldMode: 'add',
        sidebarLoading: true,
        editorShortcodes: {},
        showSubmitButton: true
    },
    getters: {
        fieldMode: state => state.fieldMode,
        sidebarLoading: state => state.sidebarLoading,
        showSubmitButton: state => state.showSubmitButton,
        editorShortcodes: state => state.editorShortcodes,
    },
    actions,
    mutations,
    // strict: debug,
    // plugins: debug ? [createLogger()] : []
});
