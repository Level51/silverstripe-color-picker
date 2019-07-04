import Vue from 'vue';
import ColorPicker from 'src/App.vue';
import watchElement from './util';

const render = (el) => {
  new Vue({
    render(h) {
      return h(ColorPicker, {
        props: {
          payload: JSON.parse(el.dataset.payload)
        }
      });
    }
  }).$mount(`#${el.id}`);
};

watchElement('.level51-color-picker-field', (el) => {
  setTimeout(() => {
    render(el);
  }, 1);
});
