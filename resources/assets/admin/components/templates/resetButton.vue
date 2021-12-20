<template>
  <div class="panel__body--item"
       :class="['text-' + resetButton.settings.align, {'selected': !!editItem.uniqElKey && editItem.uniqElKey == resetButton.uniqElKey}]">
    <div @click="editSelected(resetButton)" class="item-actions-wrapper hover-action-middle">
      <div class="item-actions">
        <i @click="editSelected(resetButton)" class="icon icon-pencil"></i>
      </div>
    </div>
    <!-- ADDED IN v1.2.6 -->
    <template v-if="resetButton.settings.button_ui">
      <button
          class="ff-btn"
          :class="[btnSize, btnStyleClass]"
          v-if="resetButton.settings.button_ui.type == 'default'"
          v-html="resetButton.settings.button_ui.text"
          :style="btnStyles">
      </button>
      <img v-else :src="resetButton.settings.button_ui.img_url" alt="Submit Button" style="max-width: 200px;">
    </template>

    <!-- Button before 1.2.6 -->
    <button
        class="ff-btn"
        :class="btnSize"
        v-if="resetButton.settings.btn_text"
        :style="btnStyles">
      {{ resetButton.settings.btn_text }}
    </button>
  </div>
</template>

<script>
export default {
  name: 'resetButton',
  props: ['resetButton', 'editSelected', 'editItem'],
  computed: {
    btnStyles() {
      if(this.resetButton.settings.button_style != '') {
        return {
          backgroundColor: this.resetButton.settings.background_color,
          color: this.resetButton.settings.color,
        }
      }

      let defaultStyles = this.resetButton.settings.normal_styles;

      let currentState = 'normal_styles';
      if(this.resetButton.settings.current_state == 'hover_styles' && this.editItem.element == 'button') {
        currentState = 'hover_styles';
      }

      if(!this.resetButton.settings[currentState]) {
        return defaultStyles;
      }

      let styles = JSON.parse(JSON.stringify(this.resetButton.settings[currentState]));

      if(styles.borderRadius) {
        styles.borderRadius = styles.borderRadius+'px';
      } else {
        delete(styles.borderRadius);
      }

      if(!styles.minWidth) {
        delete(styles.minWidth);
      }

      return { ...defaultStyles, ...styles};
    },
    btnStyleClass() {
      return this.resetButton.settings.button_style;
    },
    btnSize() {
      return 'ff-btn-' + this.resetButton.settings.button_size
    }
  }
}
</script>

