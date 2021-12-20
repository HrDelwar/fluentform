<template>
  <div>
    <el-row :gutter="30">
      <el-col :md="24 /columns">
        <label v-if="item.settings.label" class="label-block el-form-item__label" :class="item.settings.required ? 'is-required' : ''">{{ item.settings.label }}</label>
      </el-col>
      <el-col :md="24 /columns">
        <el-checkbox
            v-model="item.settings.auto_detect"
            :label="item.settings.auto_detect_label"
            size="medium"
        ></el-checkbox>
      </el-col>
    </el-row>


    <el-row :gutter="30">
      <el-col v-for="field, key in item.fields" :key="key" :md="24 / columns" v-if="field.settings.visible" :class="'ff-el-form-'+item.settings.label_placement" class="address-field-wrapper">
        <component :is="guessElTemplate(field)" :item="field"></component>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import inputText from "./inputText";

export default {
  name: "locationFields",
  props: ['item'],
  data() {
    return {
      autodetect : true
    }
  },
  components: {
    'ff_inputText': inputText
  },
  computed: {
    columns() {
      let count = 0;
      _ff.each(this.item.fields, (element) => {
        if (element.settings.visible) {
          count++;
        }
      });
      return count;
    }
  }
}
</script>

<style scoped>

</style>