<template>
    <withLabel :item="item">
        <template v-if="inputType == 'single'">
            <span>{{item.settings.price_label}} <span v-html="currency"></span>{{item.attributes.value}}</span>
        </template>
        <template v-else-if="inputType == 'select'">
            <select class="select el-input__inner">
                <option v-for="(option, index) in item.settings.pricing_options" :key="index">{{option.label}}</option>
            </select>
        </template>
        <template v-else-if="!item.settings.enable_image_input">
            <el-radio-group v-if="inputType == 'radio'" class="el-radio-horizontal">
                <el-radio v-for="option in item.settings.pricing_options" :label="option.value" :key="option.value">
                    {{ option.label }}
                </el-radio>
            </el-radio-group>
            <template v-else>
                <div v-for="(option,index) in item.settings.pricing_options" :key="index" style="line-height: 25px;">
                    <input type="checkbox" :value="option.value"> {{ option.label }}
                </div>
            </template>
        </template>
        <div class="ff_checkable_images" v-else>
            <div v-for="option in item.settings.pricing_options" class="ff_check_photo_item">
                <div v-if="option.image" class="ff_photo_holder" :style="{ backgroundImage: 'url('+option.image+')' }"></div>
                <label><input :name="item.attributes.name" :value="option.value" :type="inputType"></input> <span v-html="option.label"></span></label>
            </div>
        </div>
    </withLabel>
</template>

<script type="text/babel">
import withLabel from './withLabel.vue';

export default {
    name: 'inputMultiPayment',
    props: ['item'],
    components: {
        withLabel
    },
    data() {
      return {
          currency: window.FluentFormApp.payment_settings.currency_sign
      }
    },
    computed: {
        inputType() {
            return this.item.attributes.type;
        }
    }
}
</script>