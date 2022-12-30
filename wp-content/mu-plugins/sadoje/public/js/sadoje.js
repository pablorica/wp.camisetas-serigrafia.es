(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('vue'), require('vue-cookies')) :
    typeof define === 'function' && define.amd ? define(['exports', 'vue', 'vue-cookies'], factory) :
    (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.bundle = {}, global.Vue, global.VueCookies));
})(this, (function (exports, Vue, VueCookies) { 'use strict';

    function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

    var Vue__default = /*#__PURE__*/_interopDefaultLegacy(Vue);
    var VueCookies__default = /*#__PURE__*/_interopDefaultLegacy(VueCookies);

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
        name: 'budgeteer',
        data() {
            return {
                apiurl:        '',
                serwording:    '',
                wording:       {},
                serialized:    '',
                rows:          [],
                indexFront:    '',
                areaFront:     '',
                indexRear:     '',
                areaRear:      '',
                bagged:         0,
                baggedtitle:   '',
                baggedwording: '',
                cookieName:    "cgWooGrid",
                totalProducts: 0
            }
        },
        mounted: function () {

            let element        = this.$el;
            this.apiurl        = element.parentElement.dataset.apiurl;
            this.serialized    = element.parentElement.dataset.serialized;
            this.serwording    = element.parentElement.dataset.wording;

            this.baggedprice   = element.parentElement.dataset.baggedprice;
            this.baggedtitle   = element.parentElement.dataset.baggedtitle;
            this.baggedwording = element.parentElement.dataset.baggedwording;
            
            this.buildBugeteer(); 
            
        },
        methods: {

            buildBugeteer: function() {
                let self   = this;
                let getURL = self.apiurl+'/encode';

                if(self.serialized !== ''){

                    axios.post(getURL,{
                            serialized: self.serialized
                        })
                        .then(function (response) {
                            //console.log(response);
                            self.rows = response.data;
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                }

                if(self.serwording !== ''){

                    axios.post(getURL,{
                            serialized: self.serwording
                        })
                        .then(function (response) {
                            //console.log(response);
                            self.wording = response.data;
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
                }
            },
            changeCookie: function() {
                let self      = this;
                let cookieObj = {};
                console.log('addOn changeCookie');

                cookieObj.bagged = self.bagged;

                if( typeof self.indexFront !== 'undefined'
                    && Number.isInteger(self.indexFront) 
                ) {
                    cookieObj.front = {};
                    let keyFront    = self.indexFront;

                    cookieObj.front.screenPrice = self.rows[keyFront].screen_price;
                    cookieObj.front.stamping    = self.rows[keyFront].stamping;

                    if( typeof self.areaFront !== 'undefined') {
                        //console.log(self.areaFront)
                        if(self.areaFront === '1') {
                            cookieObj.front.whitePrice = self.rows[keyFront].white_price1;
                            cookieObj.front.colorPrice = self.rows[keyFront].color_price1;
                        }
                        if(self.areaFront === '2') {
                            cookieObj.front.whitePrice = self.rows[keyFront].white_price2;
                            cookieObj.front.colorPrice = self.rows[keyFront].color_price2;
                        }
                    }
                }

                if( typeof self.indexRear !== 'undefined'
                    && Number.isInteger(self.indexRear) 
                ) {
                    cookieObj.rear = {};
                    let keyRear    = self.indexRear;

                    cookieObj.rear.screenPrice = self.rows[keyRear].screen_price;
                    cookieObj.rear.stamping    = self.rows[keyRear].stamping;

                    if( typeof self.areaRear !== 'undefined') {
                        //console.log(self.areaRear)
                        if(self.areaRear === '1') {
                            cookieObj.rear.whitePrice = self.rows[keyRear].white_price1;
                            cookieObj.rear.colorPrice = self.rows[keyRear].color_price1;
                        }
                        if(self.areaRear === '2') {
                            cookieObj.rear.whitePrice = self.rows[keyRear].white_price2;
                            cookieObj.rear.colorPrice = self.rows[keyRear].color_price2;
                        }
                    }
                }


                if(typeof document.getElementById("cdg_total").innerHTML !== 'undefined'){
                    self.totalProducts  = parseInt(
                        document
                        .getElementById("cdg_total")
                        .innerHTML
                    );
                    //console.log('AddOn: total products '+self.totalProducts)
                    cookieObj.totalProducts = self.totalProducts;
                }
                
                //console.log('Is cookie? '+window.$cookies.isKey(self.cookieName))
                if(window.$cookies.isKey(self.cookieName)) {
                    let oldCookieObj = window.$cookies.get(self.cookieName);

                    delete oldCookieObj.front;
                    delete oldCookieObj.rear;

                    cookieObj = {...oldCookieObj,...cookieObj};
                }
                const cookieJSON = JSON.stringify(cookieObj);
                window.$cookies.set(self.cookieName,cookieJSON);
                console.log('AddOn cookie');
                console.log(window.$cookies.get(self.cookieName));

                /* TODO this works and trigers the updateTotal function, 
                but it would be better to find a way to triger that function 
                using VUE elements , or tiny-emitter */
                document.getElementById("updateTotal").click();
            }
        }
    };

    const _hoisted_1 = { class: "cdg_table" };
    const _hoisted_2 = /*#__PURE__*/Vue.createElementVNode("span", { class: "cdg_alert" }, "1", -1 /* HOISTED */);
    const _hoisted_3 = /*#__PURE__*/Vue.createElementVNode("br", null, null, -1 /* HOISTED */);
    const _hoisted_4 = { value: "" };
    const _hoisted_5 = ["value", "innerHTML"];
    const _hoisted_6 = /*#__PURE__*/Vue.createElementVNode("br", null, null, -1 /* HOISTED */);
    const _hoisted_7 = { value: "" };
    const _hoisted_8 = ["value", "innerHTML"];
    const _hoisted_9 = /*#__PURE__*/Vue.createElementVNode("div", { style: {"clear":"both","float":"none"} }, null, -1 /* HOISTED */);
    const _hoisted_10 = /*#__PURE__*/Vue.createElementVNode("span", { class: "cdg_alert" }, "2", -1 /* HOISTED */);
    const _hoisted_11 = /*#__PURE__*/Vue.createElementVNode("br", null, null, -1 /* HOISTED */);
    const _hoisted_12 = ["disabled"];
    const _hoisted_13 = {
      key: 0,
      value: ""
    };
    const _hoisted_14 = { value: "" };
    const _hoisted_15 = ["innerHTML"];
    const _hoisted_16 = ["innerHTML"];
    const _hoisted_17 = /*#__PURE__*/Vue.createElementVNode("br", null, null, -1 /* HOISTED */);
    const _hoisted_18 = ["disabled"];
    const _hoisted_19 = {
      key: 0,
      value: ""
    };
    const _hoisted_20 = { value: "" };
    const _hoisted_21 = ["innerHTML"];
    const _hoisted_22 = ["innerHTML"];
    const _hoisted_23 = /*#__PURE__*/Vue.createElementVNode("div", { style: {"clear":"both","float":"none"} }, null, -1 /* HOISTED */);
    const _hoisted_24 = /*#__PURE__*/Vue.createElementVNode("span", { class: "cdg_alert" }, "3", -1 /* HOISTED */);
    const _hoisted_25 = { class: "cdg_radio" };
    const _hoisted_26 = ["value"];
    const _hoisted_27 = /*#__PURE__*/Vue.createElementVNode("label", { for: "yes" }, "Si", -1 /* HOISTED */);
    const _hoisted_28 = /*#__PURE__*/Vue.createElementVNode("label", { for: "no" }, "No", -1 /* HOISTED */);
    const _hoisted_29 = ["innerHTML"];

    function render(_ctx, _cache, $props, $setup, $data, $options) {
      return (Vue.openBlock(), Vue.createElementBlock("table", _hoisted_1, [
        Vue.createElementVNode("tbody", null, [
          Vue.createElementVNode("tr", null, [
            Vue.createElementVNode("th", null, [
              _hoisted_2,
              Vue.createTextVNode(Vue.toDisplayString($data.wording.title1), 1 /* TEXT */)
            ]),
            Vue.createElementVNode("td", null, [
              Vue.createElementVNode("div", null, [
                Vue.createElementVNode("b", null, Vue.toDisplayString($data.wording.titleFront1), 1 /* TEXT */),
                _hoisted_3,
                Vue.withDirectives(Vue.createElementVNode("select", {
                  "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => (($data.indexFront) = $event)),
                  onChange: _cache[1] || (_cache[1] = $event => ($options.changeCookie()))
                }, [
                  Vue.createElementVNode("option", _hoisted_4, Vue.toDisplayString($data.wording.defaultSelect1), 1 /* TEXT */),
                  (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList($data.rows, (row, index) => {
                    return (Vue.openBlock(), Vue.createElementBlock("option", {
                      value: index,
                      innerHTML: row.stamping
                    }, null, 8 /* PROPS */, _hoisted_5))
                  }), 256 /* UNKEYED_FRAGMENT */))
                ], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [
                  [Vue.vModelSelect, $data.indexFront]
                ])
              ]),
              Vue.createElementVNode("div", null, [
                Vue.createElementVNode("b", null, Vue.toDisplayString($data.wording.titleRear1), 1 /* TEXT */),
                _hoisted_6,
                Vue.withDirectives(Vue.createElementVNode("select", {
                  "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => (($data.indexRear) = $event)),
                  onChange: _cache[3] || (_cache[3] = $event => ($options.changeCookie()))
                }, [
                  Vue.createElementVNode("option", _hoisted_7, Vue.toDisplayString($data.wording.defaultSelect1), 1 /* TEXT */),
                  (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList($data.rows, (row, index) => {
                    return (Vue.openBlock(), Vue.createElementBlock("option", {
                      value: index,
                      innerHTML: row.stamping
                    }, null, 8 /* PROPS */, _hoisted_8))
                  }), 256 /* UNKEYED_FRAGMENT */))
                ], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [
                  [Vue.vModelSelect, $data.indexRear]
                ])
              ]),
              _hoisted_9
            ])
          ]),
          Vue.createElementVNode("tr", null, [
            Vue.createElementVNode("th", null, [
              _hoisted_10,
              Vue.createTextVNode(Vue.toDisplayString($data.wording.title2), 1 /* TEXT */)
            ]),
            Vue.createElementVNode("td", null, [
              Vue.createElementVNode("div", {
                class: Vue.normalizeClass((Number.isInteger($data.indexFront)? '' : 'disabled'))
              }, [
                Vue.createElementVNode("b", null, Vue.toDisplayString($data.wording.titleFront2), 1 /* TEXT */),
                _hoisted_11,
                Vue.withDirectives(Vue.createElementVNode("select", {
                  "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => (($data.areaFront) = $event)),
                  onChange: _cache[5] || (_cache[5] = $event => ($options.changeCookie())),
                  disabled: !Number.isInteger($data.indexFront)
                }, [
                  (!Number.isInteger($data.indexFront))
                    ? (Vue.openBlock(), Vue.createElementBlock("option", _hoisted_13, Vue.toDisplayString($data.wording.disabledSelect2), 1 /* TEXT */))
                    : Vue.createCommentVNode("v-if", true),
                  (Number.isInteger($data.indexFront))
                    ? (Vue.openBlock(), Vue.createElementBlock(Vue.Fragment, { key: 1 }, [
                        Vue.createElementVNode("option", _hoisted_14, Vue.toDisplayString($data.wording.defaultSelect2), 1 /* TEXT */),
                        Vue.createElementVNode("option", {
                          value: "1",
                          innerHTML: $data.rows[$data.indexFront].area1
                        }, null, 8 /* PROPS */, _hoisted_15),
                        Vue.createElementVNode("option", {
                          value: "2",
                          innerHTML: $data.rows[$data.indexFront].area2
                        }, null, 8 /* PROPS */, _hoisted_16)
                      ], 64 /* STABLE_FRAGMENT */))
                    : Vue.createCommentVNode("v-if", true)
                ], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_12), [
                  [Vue.vModelSelect, $data.areaFront]
                ])
              ], 2 /* CLASS */),
              Vue.createElementVNode("div", {
                class: Vue.normalizeClass((Number.isInteger($data.indexRear)? '' : 'disabled'))
              }, [
                Vue.createElementVNode("b", null, Vue.toDisplayString($data.wording.titleRear2), 1 /* TEXT */),
                _hoisted_17,
                Vue.withDirectives(Vue.createElementVNode("select", {
                  "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => (($data.areaRear) = $event)),
                  onChange: _cache[7] || (_cache[7] = $event => ($options.changeCookie())),
                  disabled: !Number.isInteger($data.indexRear)
                }, [
                  (!Number.isInteger($data.indexRear))
                    ? (Vue.openBlock(), Vue.createElementBlock("option", _hoisted_19, Vue.toDisplayString($data.wording.disabledSelect2), 1 /* TEXT */))
                    : Vue.createCommentVNode("v-if", true),
                  (Number.isInteger($data.indexRear))
                    ? (Vue.openBlock(), Vue.createElementBlock(Vue.Fragment, { key: 1 }, [
                        Vue.createElementVNode("option", _hoisted_20, Vue.toDisplayString($data.wording.defaultSelect2), 1 /* TEXT */),
                        Vue.createElementVNode("option", {
                          value: "1",
                          innerHTML: $data.rows[$data.indexRear].area1
                        }, null, 8 /* PROPS */, _hoisted_21),
                        Vue.createElementVNode("option", {
                          value: "2",
                          innerHTML: $data.rows[$data.indexRear].area2
                        }, null, 8 /* PROPS */, _hoisted_22)
                      ], 64 /* STABLE_FRAGMENT */))
                    : Vue.createCommentVNode("v-if", true)
                ], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_18), [
                  [Vue.vModelSelect, $data.areaRear]
                ])
              ], 2 /* CLASS */),
              _hoisted_23
            ])
          ]),
          Vue.createElementVNode("tr", null, [
            Vue.createElementVNode("th", null, [
              _hoisted_24,
              Vue.createTextVNode(" " + Vue.toDisplayString($data.baggedtitle), 1 /* TEXT */)
            ]),
            Vue.createElementVNode("td", _hoisted_25, [
              Vue.withDirectives(Vue.createElementVNode("input", {
                type: "radio",
                id: "yes",
                value: _ctx.baggedprice,
                "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => (($data.bagged) = $event)),
                onChange: _cache[9] || (_cache[9] = $event => ($options.changeCookie()))
              }, null, 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_26), [
                [Vue.vModelRadio, $data.bagged]
              ]),
              _hoisted_27,
              Vue.withDirectives(Vue.createElementVNode("input", {
                type: "radio",
                id: "no",
                value: "0",
                "onUpdate:modelValue": _cache[10] || (_cache[10] = $event => (($data.bagged) = $event)),
                onChange: _cache[11] || (_cache[11] = $event => ($options.changeCookie()))
              }, null, 544 /* HYDRATE_EVENTS, NEED_PATCH */), [
                [Vue.vModelRadio, $data.bagged]
              ]),
              _hoisted_28,
              Vue.createTextVNode("   "),
              Vue.createElementVNode("small", { innerHTML: $data.baggedwording }, null, 8 /* PROPS */, _hoisted_29)
            ])
          ])
        ])
      ]))
    }

    script.render = render;
    script.__file = "src/js/templates/Budgeteer.vue";

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
        //index: REST_POSTS_URL
      }
      //nonce:         REST_NONCE,
      //loadingif:     LOADING_GIF,
    };

    //const appTest      = Vue.createApp(Test).mount('#appTest')
    Vue__default["default"].createApp(script).use(VueCookies__default["default"]).mount('#appBudgeteer');

    exports.config = config;
    exports.emitter = emitter;

    Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=sadoje.js.map
