<template>
  <div>
    <div
      v-if="showCheckbox">
      <input
        type="checkbox"
        v-model="rgbValuesAvailable">
      <label>{{ i18n('ADD_RGB_VALUES') }}</label>
    </div>

    <verte
      v-if="rgbValuesAvailable && visible"
      class="level51-verte level51-verte-horizontal"
      picker="square"
      :model="payload.mode"
      v-model="inRgbMode ? rgb : simpleValue"
      :show-history="false"
      :draggable="false"
      :enable-alpha="false"
      :rgb-sliders="true"
      display="widget" />

    <template v-if="inRgbMode">
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
    </template>

    <template v-if="inHexMode">
      <input
        type="hidden"
        :name="payload.name"
        :value="rgbValuesAvailable ? simpleValue : null">
    </template>
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
      /**
       * Handle the visibility of the color picker itself.
       *
       * Used to control the time of rendering to prevent issues
       * when rendered while in background.
       */
      visible: false,

      /**
       * Whether RGB values should be added or not.
       *
       * The RGB values will be -1,-1,-1 if set to false.
       */
      rgbValuesAvailable: false,

      /**
       * The color selected by the picker, in the format "rgb(r,g,b)".
       * Only used if in rgb mode.
       */
      rgb: null,

      /** Simple value used e.g. in the hex mode */
      simpleValue: null
    };
  },
  components: { Verte },
  created() {
    this.rgbValuesAvailable = !this.showCheckbox;

    // Check for an existing value.
    if (this.payload.value && this.payload.value !== 'null') {
      if (this.inRgbMode) {
        const values = JSON.parse(this.payload.value);

        // Check for valid RGB values, set the picker value and rgbValuesAvailable flag
        if (Object.values(values).filter(val => val >= 0 && val <= 255).length === 3) {
          this.rgb = `rgb(${values.R}, ${values.G}, ${values.B})`;
          this.rgbValuesAvailable = true;
        }
      }

      if (this.inHexMode) {
        this.rgbValuesAvailable = true;
        this.simpleValue = this.payload.value;
      }
    }
  },
  mounted() {
    // Wait for the element to become visible (get a "real width", render afterwards)
    const interval = setInterval(() => {
      if (this.$el.getBoundingClientRect().width > 0) {
        this.visible = true;
        clearInterval(interval);
      }
    }, 100);
  },
  computed: {
    inRgbMode() {
      return this.payload.mode === 'rgb';
    },
    inHexMode() {
      return this.payload.mode === 'hex';
    },
    showCheckbox() {
      if (typeof this.payload.showCheckbox === 'boolean') return this.payload.showCheckbox;

      return true;
    },
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
        width: 450px;
        background: none;
        box-shadow: none;
      }
    }

    canvas {
      width: 250px;
      height: 200px;
    }
  }
}
</style>
