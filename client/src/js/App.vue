<template>
  <div>
    <div>
      <input
        type="checkbox"
        v-model="rgbValuesAvailable">
      <label>{{ i18n('ADD_RGB_VALUES') }}</label>
    </div>

    <verte
      v-if="rgbValuesAvailable && visible"
      class="level51-verte level51-verte-horizontal"
      picker="square"
      model="rgb"
      v-model="rgb"
      :show-history="false"
      :draggable="false"
      :enable-alpha="false"
      :rgb-sliders="true"
      display="widget" />

    <input
      type="hidden"
      :name="`${payload.name}[R]`"
      min="0"
      max="255"
      :value="colorObject.r">
    <input
      type="hidden"
      :name="`${payload.name}[G]`"
      min="0"
      max="255"
      :value="colorObject.g">
    <input
      type="hidden"
      :name="`${payload.name}[B]`"
      min="0"
      max="255"
      :value="colorObject.b">
  </div>
</template>

<script>
import Verte from 'verte';

export default {
  props: {
    payload: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      visible: false,
      rgbValuesAvailable: false,
      rgb: null
    };
  },
  components: { Verte },
  created() {
    if (this.payload.value) {
      const values = JSON.parse(this.payload.value);

      if (Object.values(values).filter(val => val >= 0 && val <= 255).length === 3) {
        this.rgb = `rgb(${values.R}, ${values.G}, ${values.B})`;
        this.rgbValuesAvailable = true;
      }
    }
  },
  mounted() {
    // Set visibility after a short delay to prevent slider styling bugs
    setTimeout(() => {
      this.visible = true;
    }, 500);
  },
  computed: {
    colorObject() {
      // Special case if no value should be set, use -1,-1,-1 for this
      if (!this.rgbValuesAvailable) {
        return {
          r: -1,
          g: -1,
          b: -1
        };
      }

      // No value selected yet, default to 0,0,0
      if (!this.rgb || typeof this.rgb !== 'string') {
        return {
          r: 0,
          g: 0,
          b: 0
        };
      }

      // Color set, prepare object from the given rgb(r,g,b) string
      const color = this.rgb.replace('rgb(', '').replace(')', '').split(',');

      return {
        r: color[0],
        g: color[1],
        b: color[2]
      };
    }
  },
  methods: {
    i18n(label) {
      const { i18n } = this.payload;
      return i18n.hasOwnProperty(label) ? i18n[label] : label;
    }
  }
};
</script>

<style lang="less">
@import "~verte/dist/verte.css";

.verte {
  &.level51-verte {
    &, input, button {
      font-size: 12px;
    }

    .verte__submit {
      display: none;
    }
  }

  &.level51-verte-horizontal {
    justify-content: flex-start;

    .verte__controller {
      padding: 20px 20px 20px 0;

      .slider.slider--editable {
        width: 180px;
      }
    }

    .verte__menu-origin {
      > div {
        display: flex;
        flex-direction: row-reverse;
        width: auto;
        background: none;
        box-shadow: none;
      }
    }
  }
}
</style>
