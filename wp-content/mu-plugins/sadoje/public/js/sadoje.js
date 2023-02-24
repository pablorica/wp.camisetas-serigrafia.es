(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('vue'), require('vue-cookies')) :
  typeof define === 'function' && define.amd ? define(['exports', 'vue', 'vue-cookies'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.bundle = {}, global.Vue, global.VueCookies));
})(this, (function (exports, Vue, VueCookies) { 'use strict';

  function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

  var Vue__default = /*#__PURE__*/_interopDefaultLegacy(Vue);
  var VueCookies__default = /*#__PURE__*/_interopDefaultLegacy(VueCookies);

  console.log('customisations-for-cg-woo-grid: VUE Test loaded');

  var script$1 = {
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

                  //console.log('serialized');
                  //console.log(self.serialized);

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
              cookieObj.descStamping = '';
              cookieObj.baseprices   = self.baseprices;

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
                          cookieObj.front.area       = self.rows[keyFront].area1;
                          cookieObj.front.whitePrice = self.rows[keyFront].white_price1;
                          cookieObj.front.colorPrice = self.rows[keyFront].color_price1;
                      }
                      if(self.areaFront === '2') {
                          cookieObj.front.area       = self.rows[keyFront].area2;
                          cookieObj.front.whitePrice = self.rows[keyFront].white_price2;
                          cookieObj.front.colorPrice = self.rows[keyFront].color_price2;
                      }
                  }

                  cookieObj.descStamping += '\nEstampación en pecho' 
                      +' ('+ cookieObj.front.stamping
                      + ' ' + cookieObj.front.area + ') ';
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
                          cookieObj.rear.area       = self.rows[keyRear].area1;
                          cookieObj.rear.whitePrice = self.rows[keyRear].white_price1;
                          cookieObj.rear.colorPrice = self.rows[keyRear].color_price1;
                      }
                      if(self.areaRear === '2') {
                          cookieObj.rear.area       = self.rows[keyRear].area2;
                          cookieObj.rear.whitePrice = self.rows[keyRear].white_price2;
                          cookieObj.rear.colorPrice = self.rows[keyRear].color_price2;
                      }
                  }

                  cookieObj.descStamping += '\nEstampación en espalda' 
                      +' ('+ cookieObj.rear.stamping
                      + ' ' + cookieObj.rear.area + ') ';
              }

              cookieObj.bagged = self.bagged;
              if(self.bagged > 0 ) {
                  cookieObj.descStamping += '\nEmbolsado';
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

                  if(typeof oldCookieObj.description !== 'undefined') {

                      cookieObj.description = [
                          oldCookieObj.description,
                          cookieObj.descStamping
                      ];
                      delete oldCookieObj.description;
                  } else {
                      cookieObj.description = [
                          '',
                          cookieObj.descStamping
                      ];
                  }
                  

                  delete oldCookieObj.front;
                  delete oldCookieObj.rear;
                  

                  cookieObj = {...oldCookieObj,...cookieObj};
              } else {
                  cookieObj.description = [
                      '',
                      cookieObj.descStamping
                  ];
              }
              delete cookieObj.descStamping;

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

  const _hoisted_1$1 = { class: "cdg_table" };
  const _hoisted_2$1 = /*#__PURE__*/Vue.createElementVNode("span", { class: "cdg_alert" }, "1", -1 /* HOISTED */);
  const _hoisted_3$1 = /*#__PURE__*/Vue.createElementVNode("br", null, null, -1 /* HOISTED */);
  const _hoisted_4$1 = { value: "" };
  const _hoisted_5$1 = ["value", "innerHTML"];
  const _hoisted_6$1 = /*#__PURE__*/Vue.createElementVNode("br", null, null, -1 /* HOISTED */);
  const _hoisted_7$1 = { value: "" };
  const _hoisted_8$1 = ["value", "innerHTML"];
  const _hoisted_9$1 = /*#__PURE__*/Vue.createElementVNode("div", { style: {"clear":"both","float":"none"} }, null, -1 /* HOISTED */);
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

  function render$1(_ctx, _cache, $props, $setup, $data, $options) {
    return (Vue.openBlock(), Vue.createElementBlock("table", _hoisted_1$1, [
      Vue.createElementVNode("tbody", null, [
        Vue.createElementVNode("tr", null, [
          Vue.createElementVNode("th", null, [
            _hoisted_2$1,
            Vue.createTextVNode(Vue.toDisplayString($data.wording.title1), 1 /* TEXT */)
          ]),
          Vue.createElementVNode("td", null, [
            Vue.createElementVNode("div", null, [
              Vue.createElementVNode("b", null, Vue.toDisplayString($data.wording.titleFront1), 1 /* TEXT */),
              _hoisted_3$1,
              Vue.withDirectives(Vue.createElementVNode("select", {
                "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => (($data.indexFront) = $event)),
                onChange: _cache[1] || (_cache[1] = $event => ($options.changeCookie()))
              }, [
                Vue.createElementVNode("option", _hoisted_4$1, Vue.toDisplayString($data.wording.defaultSelect1), 1 /* TEXT */),
                (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList($data.rows, (row, index) => {
                  return (Vue.openBlock(), Vue.createElementBlock("option", {
                    value: index,
                    innerHTML: row.stamping
                  }, null, 8 /* PROPS */, _hoisted_5$1))
                }), 256 /* UNKEYED_FRAGMENT */))
              ], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [
                [Vue.vModelSelect, $data.indexFront]
              ])
            ]),
            Vue.createElementVNode("div", null, [
              Vue.createElementVNode("b", null, Vue.toDisplayString($data.wording.titleRear1), 1 /* TEXT */),
              _hoisted_6$1,
              Vue.withDirectives(Vue.createElementVNode("select", {
                "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => (($data.indexRear) = $event)),
                onChange: _cache[3] || (_cache[3] = $event => ($options.changeCookie()))
              }, [
                Vue.createElementVNode("option", _hoisted_7$1, Vue.toDisplayString($data.wording.defaultSelect1), 1 /* TEXT */),
                (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList($data.rows, (row, index) => {
                  return (Vue.openBlock(), Vue.createElementBlock("option", {
                    value: index,
                    innerHTML: row.stamping
                  }, null, 8 /* PROPS */, _hoisted_8$1))
                }), 256 /* UNKEYED_FRAGMENT */))
              ], 544 /* HYDRATE_EVENTS, NEED_PATCH */), [
                [Vue.vModelSelect, $data.indexRear]
              ])
            ]),
            _hoisted_9$1
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

  script$1.render = render$1;
  script$1.__file = "src/js/templates/Budgeteer.vue";

  console.log('customisations-for-cg-woo-grid: VUE Estimator loaded');

  var script = {
      name: 'estimator',
      data() {
          return {
              apiurl:             '',
              serwording:         '',
              wording:            {},
              prices:             '',
              selectoptions:      [],
              selectoptionsinrow: [],
              rows:               [],
              columns:            [],
              indexFront:         '',
              areaFront:          '',
              indexRear:          '',
              areaRear:           '',
              bagged:             0,
              baggedtitle:        '',
              baggedwording:      '',
              cookieName:         "cgWooGrid",
              totalProducts:      0
          }
      },
      mounted: function () {

          let element        = this.$el;
          this.apiurl        = element.parentElement.dataset.apiurl;
          this.imprint       = element.parentElement.dataset.imprint;
          this.prices        = element.parentElement.dataset.prices;
          

          this.serwording    = element.parentElement.dataset.wording;

          this.baggedprice   = element.parentElement.dataset.baggedprice;
          this.baggedtitle   = element.parentElement.dataset.baggedtitle;
          this.baggedwording = element.parentElement.dataset.baggedwording;
          
          this.buildEstimator(); 
          
      },
      methods: {

          buildEstimator: function() {
              //console.log('buildEstimator');
              let self   = this;
              let getURL = self.apiurl+'/encode';

              if(self.prices !== ''){

                  //console.log('prices');
                  //console.log(self.prices);

                  axios.post(getURL,{
                          serialized: self.prices
                      })
                      .then(function (response) {
                          //console.log(response);
                          self.selectoptions = response.data;
                          //console.log(response.data);
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
          updateSelects: function(
              rowindex,
              colindex,
              colslug
          ) {

              console.log("updateSelects row:"+rowindex+" col:"+colindex+" "+colslug);
              let self   = this;
              console.log("selected "+colslug+": "+self.rows[rowindex][colindex]);

              console.log("Current saved rows");
              console.log(self.rows);

              let nextColumnIndex = colindex+1;
              if(nextColumnIndex < self.columns[rowindex].length) {
                  //delete values of selects on the right of the current element, if any
                  for( let i = nextColumnIndex; i < self.columns[rowindex].length; i++) {
                      console.log("deleting currentColumns id "+rowindex+" options "+i);
                      self.columns[rowindex][i].options = [];
                      console.log("deleting self.rows id "+rowindex+"  values "+i);
                      self.rows[rowindex][i] = null;
                  }
                  
                  //Set the values of the current select
                  console.log('next column:');
                  console.log(nextColumnIndex);
                  let nextColumn = self.columns[rowindex][nextColumnIndex];
                  console.log('next column slug');
                  console.log(nextColumn.slug);

                  if(self.columns[rowindex][colindex].all) {
                      // The current select can have any value, 
                      // we must use one of the previous select to filter the values
                      colslug  = self.columns[rowindex][colindex].all.slug;
                      colindex = self.columns[rowindex][colindex].all.index;
                  }

                  console.log('options to filter');
                  console.log(self.selectoptionsinrow);
                  console.log('filter by');
                  console.log(colslug);
                  
                  let selected = self.selectoptionsinrow.filter(function (el) {
                      return el[colslug] == self.rows[rowindex][colindex];
                  });
                  console.log('filtered otions');
                  console.log(selected);
                  if(!selected.length) {
                      
                      // Filter again but this time using the original options value, 
                      // becasue the current row options was returning false
                      console.log('default options to filter');
                      console.log(self.selectoptions);


                      selected = self.selectoptions.filter(function (el) {
                          return el[colslug] == self.rows[rowindex][colindex];
                      });
                  }
                  self.selectoptionsinrow = selected;
                  

                  let nextuniqueoptions = [...new Set(selected.map(item => item[nextColumn.slug]))];
                  
                  console.log('next column values');
                  console.log(nextuniqueoptions);
                  if(nextuniqueoptions[0] === "all") {
                      nextColumn.options = self.columns[rowindex][nextColumnIndex].default;
                      nextColumn.all    = {'index':colindex, 'slug':colslug};
                  } else {
                      nextColumn.options = nextuniqueoptions;
                  }
                  self.columns[rowindex][nextColumnIndex] = nextColumn;

                  console.log("Current saved columns");
                  console.log(self.columns);
              }
          },
          addRow: function(event) {
              event.preventDefault();
              let self = this;

              let getURL = self.apiurl+'/encode';

              if(self.imprint !== ''){

                  console.log('imprint');
                  console.log(self.imprint);

                  axios.post(getURL,{
                          serialized: self.imprint
                      })
                      .then(function (response) {
                          //console.log(response);
                          //self.columns.push(response.data)
                          console.log(response.data);
                          let defaultSelects = []; 
                          for (let i = 0; i < response.data.length; i++) {
                              const item = response.data[i];

                              if(item.imprint_hide) {
                                  continue;
                              }

                              const defaultValues = item.imprint_values
                                                  .split('|')
                                                  .map(function(ele) {
                                                      return ele.trim();
                                                  });

                              let imprintObject = {
                                  slug:       item.imprint,
                                  title:      item.imprint_label,
                                  default:    defaultValues,   
                                  options:    [],
                                  all:        false
                              };
                              if(i === 0) {
                                  imprintObject.options = defaultValues; 
                              }

                              defaultSelects.push(imprintObject);
                          }

                          console.log('defaultSelects');
                          console.log(defaultSelects);

                          self.columns.push(defaultSelects);
                      })
                      .catch(function (error) {
                          console.log(error);
                      });
              }

              self.selectoptionsinrow = self.selectoptions;

              //Default columns
              /*
              self.columns.push([
                  {
                      slug:'estampacion',
                      title:'Tipo de estampación',
                      default:['Serigrafía','Bordados', 'Digital todo color'],
                      options:['Serigrafía','Bordados', 'Digital todo color'],
                      all:null
                  },
                  {
                      slug:'tamano',
                      title:'Tamaño',
                      default:['Pequeño','Grande'],
                      options:[],
                      all:false
                  },
                  {
                      slug:'tintas',
                      title:'Tintas',
                      default:['1 Tinta','2 Tintas','3 Tintas','4 tintas'],
                      options:[],
                      all:false
                  },
                  {
                      slug:'ubicacion',
                      title:'Ubicación',
                      default:['Pecho','Espalda','Manga derecha','Manga izquierda'],
                      options:[],
                      all:false
                  }
              ]);
              */
              self.rows.push({});

              console.log("Current created columns");
              console.log(self.columns);
          },
          deleteRow: function(rowid, event) {
              event.preventDefault();
              let self = this;
              //alert('Delete ' + row)
              self.rows.splice(rowid, 1);
              self.columns.splice(rowid, 1);
              //self.updateRows()
              console.log("Current saved rows");
              console.log(self.rows);
          },
          changeCookie: function() {
              let self      = this;
              let cookieObj = {};
              console.log('addOn changeCookie');
              cookieObj.descStamping = '';
              cookieObj.baseprices   = self.baseprices;

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
                          cookieObj.front.area       = self.rows[keyFront].area1;
                          cookieObj.front.whitePrice = self.rows[keyFront].white_price1;
                          cookieObj.front.colorPrice = self.rows[keyFront].color_price1;
                      }
                      if(self.areaFront === '2') {
                          cookieObj.front.area       = self.rows[keyFront].area2;
                          cookieObj.front.whitePrice = self.rows[keyFront].white_price2;
                          cookieObj.front.colorPrice = self.rows[keyFront].color_price2;
                      }
                  }

                  cookieObj.descStamping += '\nEstampación en pecho' 
                      +' ('+ cookieObj.front.stamping
                      + ' ' + cookieObj.front.area + ') ';
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
                          cookieObj.rear.area       = self.rows[keyRear].area1;
                          cookieObj.rear.whitePrice = self.rows[keyRear].white_price1;
                          cookieObj.rear.colorPrice = self.rows[keyRear].color_price1;
                      }
                      if(self.areaRear === '2') {
                          cookieObj.rear.area       = self.rows[keyRear].area2;
                          cookieObj.rear.whitePrice = self.rows[keyRear].white_price2;
                          cookieObj.rear.colorPrice = self.rows[keyRear].color_price2;
                      }
                  }

                  cookieObj.descStamping += '\nEstampación en espalda' 
                      +' ('+ cookieObj.rear.stamping
                      + ' ' + cookieObj.rear.area + ') ';
              }

              cookieObj.bagged = self.bagged;
              if(self.bagged > 0 ) {
                  cookieObj.descStamping += '\nEmbolsado';
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

                  if(typeof oldCookieObj.description !== 'undefined') {

                      cookieObj.description = [
                          oldCookieObj.description,
                          cookieObj.descStamping
                      ];
                      delete oldCookieObj.description;
                  } else {
                      cookieObj.description = [
                          '',
                          cookieObj.descStamping
                      ];
                  }
                  

                  delete oldCookieObj.front;
                  delete oldCookieObj.rear;
                  

                  cookieObj = {...oldCookieObj,...cookieObj};
              } else {
                  cookieObj.description = [
                      '',
                      cookieObj.descStamping
                  ];
              }
              delete cookieObj.descStamping;

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
  const _hoisted_2 = ["id"];
  const _hoisted_3 = ["onUpdate:modelValue", "onChange"];
  const _hoisted_4 = /*#__PURE__*/Vue.createElementVNode("option", {
    disabled: "",
    value: ""
  }, " Seleccione un valor ", -1 /* HOISTED */);
  const _hoisted_5 = ["value"];
  const _hoisted_6 = { style: {"text-align":"right"} };
  const _hoisted_7 = ["onClick"];
  const _hoisted_8 = { colspan: "6" };
  const _hoisted_9 = { style: {"text-align":"right"} };

  function render(_ctx, _cache, $props, $setup, $data, $options) {
    return (Vue.openBlock(), Vue.createElementBlock("table", _hoisted_1, [
      Vue.createElementVNode("tbody", null, [
        (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList($data.rows, (row, rindex) => {
          return (Vue.openBlock(), Vue.createElementBlock("tr", {
            id: 'row_'+rindex,
            class: Vue.normalizeClass((_ctx.index % 2 == 0)?'':'alternate')
          }, [
            (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList($data.columns[rindex], (column, cindex) => {
              return (Vue.openBlock(), Vue.createElementBlock("td", null, [
                Vue.createTextVNode(Vue.toDisplayString(column.title) + " ", 1 /* TEXT */),
                Vue.withDirectives(Vue.createElementVNode("select", {
                  "onUpdate:modelValue": $event => ((row[cindex]) = $event),
                  onChange: $event => ($options.updateSelects(rindex,cindex,column.slug))
                }, [
                  _hoisted_4,
                  (Vue.openBlock(true), Vue.createElementBlock(Vue.Fragment, null, Vue.renderList(column.options, (option, vindex) => {
                    return (Vue.openBlock(), Vue.createElementBlock("option", { value: option }, Vue.toDisplayString(option), 9 /* TEXT, PROPS */, _hoisted_5))
                  }), 256 /* UNKEYED_FRAGMENT */))
                ], 40 /* PROPS, HYDRATE_EVENTS */, _hoisted_3), [
                  [Vue.vModelSelect, row[cindex]]
                ])
              ]))
            }), 256 /* UNKEYED_FRAGMENT */)),
            Vue.createElementVNode("td", _hoisted_6, [
              Vue.createElementVNode("button", {
                class: "button button-secondary button-delete",
                style: {"background":"#DC3232","color":"#FFF","border-color":"#FFF"},
                onClick: $event => ($options.deleteRow(rindex, $event))
              }, "Borrar", 8 /* PROPS */, _hoisted_7)
            ])
          ], 10 /* CLASS, PROPS */, _hoisted_2))
        }), 256 /* UNKEYED_FRAGMENT */))
      ]),
      Vue.createElementVNode("tfoot", null, [
        Vue.createElementVNode("tr", null, [
          Vue.createElementVNode("th", _hoisted_8, [
            Vue.createElementVNode("p", _hoisted_9, [
              Vue.createElementVNode("button", {
                class: "button button-secondary",
                onClick: _cache[0] || (_cache[0] = $event => ($options.addRow($event)))
              }, "Añadir personalización")
            ])
          ])
        ])
      ])
    ]))
  }

  script.render = render;
  script.__file = "src/js/templates/Estimator.vue";

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
  Vue__default["default"].createApp(script$1).use(VueCookies__default["default"]).mount('#appBudgeteer');
  Vue__default["default"].createApp(script).use(VueCookies__default["default"]).mount('#appEstimator');

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

  exports.config = config;
  exports.emitter = emitter;

  Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=sadoje.js.map
