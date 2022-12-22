(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('vue')) :
    typeof define === 'function' && define.amd ? define(['exports', 'vue'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.bundle = {}, global.Vue));
})(this, (function (exports, Vue) { 'use strict';

    function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

    var Vue__default = /*#__PURE__*/_interopDefaultLegacy(Vue);

    const consoleHello = message => {
      console.log(message);
    };

    (function ($) {
      const CDG = {
        onreadyFunctions: function () {
          consoleHello('customisations-for-cg-woo-grid is ready');
          $(window).on('resize', function () {
            //
          });
        },
        onloadFunctions: function () {
          consoleHello('customisations-for-cg-woo-grid is loaded');
        }
      };
      CDG.onreadyFunctions();
      $(window).on('load', function () {
        CDG.onloadFunctions();
      });
    })(jQuery);

    console.log('customisations-for-cg-woo-grid: VUE Test loaded');

    var script = {
      name: 'counter',
      data() {
        return {
          // Initialized to zero to begin
          count: 0,
        }
      },
      methods: {
        increment() {
          this.count += 1;
        },
        decrement() {
          this.count -= 1;
        }
      }
    };

    function render(_ctx, _cache, $props, $setup, $data, $options) {
      return (Vue.openBlock(), Vue.createElementBlock("div", null, [
        Vue.createTextVNode(Vue.toDisplayString($data.count) + " ", 1 /* TEXT */),
        Vue.createElementVNode("button", {
          onClick: _cache[0] || (_cache[0] = (...args) => ($options.decrement && $options.decrement(...args)))
        }, "-"),
        Vue.createElementVNode("button", {
          onClick: _cache[1] || (_cache[1] = (...args) => ($options.increment && $options.increment(...args)))
        }, "+")
      ]))
    }

    script.render = render;
    script.__file = "src/js/templates/Test.vue";

    var tinyEmitter = {exports: {}};

    function E () {
      // Keep this empty so it's easier to inherit from
      // (via https://github.com/lipsmack from https://github.com/scottcorgan/tiny-emitter/issues/3)
    }

    E.prototype = {
      on: function (name, callback, ctx) {
        var e = this.e || (this.e = {});

        (e[name] || (e[name] = [])).push({
          fn: callback,
          ctx: ctx
        });

        return this;
      },

      once: function (name, callback, ctx) {
        var self = this;
        function listener () {
          self.off(name, listener);
          callback.apply(ctx, arguments);
        }
        listener._ = callback;
        return this.on(name, listener, ctx);
      },

      emit: function (name) {
        var data = [].slice.call(arguments, 1);
        var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
        var i = 0;
        var len = evtArr.length;

        for (i; i < len; i++) {
          evtArr[i].fn.apply(evtArr[i].ctx, data);
        }

        return this;
      },

      off: function (name, callback) {
        var e = this.e || (this.e = {});
        var evts = e[name];
        var liveEvents = [];

        if (evts && callback) {
          for (var i = 0, len = evts.length; i < len; i++) {
            if (evts[i].fn !== callback && evts[i].fn._ !== callback)
              liveEvents.push(evts[i]);
          }
        }

        // Remove event from queue to prevent memory leak
        // Suggested by https://github.com/lazd
        // Ref: https://github.com/scottcorgan/tiny-emitter/commit/c6ebfaa9bc973b33d110a84a307742b7cf94c953#commitcomment-5024910

        (liveEvents.length)
          ? e[name] = liveEvents
          : delete e[name];

        return this;
      }
    };

    tinyEmitter.exports = E;
    tinyEmitter.exports.TinyEmitter = E;

    var Emitter = tinyEmitter.exports;

    //vue libraries are already loaded by cg-woo-grid plugin
    console.log('VUE Main loaded');
    var emitter = new Emitter();
    const config = {
      api: {
        index: REST_POSTS_URL
      },
      nonce: REST_NONCE,
      loadingif: LOADING_GIF
    };
    (function ($) {
      $(window).on('load', function () {
        if ($("#appTest").length) {
          console.log('customisations-for-cg-woo-grid: Loading VUE Test');
          Vue__default["default"].createApp(script).mount('#appTest');
        }
      });
    })(jQuery);

    exports.config = config;
    exports.emitter = emitter;

    Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=sadoje.js.map
