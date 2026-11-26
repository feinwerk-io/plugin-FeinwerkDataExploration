(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("CoreHome"), require("vue"));
	else if(typeof define === 'function' && define.amd)
		define(["CoreHome", ], factory);
	else if(typeof exports === 'object')
		exports["FeinwerkDataExploration"] = factory(require("CoreHome"), require("vue"));
	else
		root["FeinwerkDataExploration"] = factory(root["CoreHome"], root["Vue"]);
})((typeof self !== 'undefined' ? self : this), function(__WEBPACK_EXTERNAL_MODULE__19dc__, __WEBPACK_EXTERNAL_MODULE__8bbf__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "plugins/FeinwerkDataExploration/vue/dist/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "fae3");
/******/ })
/************************************************************************/
/******/ ({

/***/ "19dc":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__19dc__;

/***/ }),

/***/ "8bbf":
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE__8bbf__;

/***/ }),

/***/ "fae3":
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

// EXPORTS
__webpack_require__.d(__webpack_exports__, "ExplorationPage", function() { return /* reexport */ ExplorationPage; });
__webpack_require__.d(__webpack_exports__, "FunnelPage", function() { return /* reexport */ FunnelPage; });
__webpack_require__.d(__webpack_exports__, "SavedReportFooter", function() { return /* reexport */ SavedReportFooter; });
__webpack_require__.d(__webpack_exports__, "KpiCard", function() { return /* reexport */ KpiCard; });

// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/setPublicPath.js
// This file is imported into lib/wc client bundles.

if (typeof window !== 'undefined') {
  var currentScript = window.document.currentScript
  if (false) { var getCurrentScript; }

  var src = currentScript && currentScript.src.match(/(.+\/)[^/]+\.js(\?.*)?$/)
  if (src) {
    __webpack_require__.p = src[1] // eslint-disable-line
  }
}

// Indicate to webpack that this file can be concatenated
/* harmony default export */ var setPublicPath = (null);

// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","root":"Vue"}
var external_commonjs_vue_commonjs2_vue_root_Vue_ = __webpack_require__("8bbf");

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=template&id=fd0063f0

const _hoisted_1 = {
  class: "fwde-page"
};
const _hoisted_2 = {
  key: 2,
  class: "fwde-page__layout"
};
const _hoisted_3 = ["aria-label"];
const _hoisted_4 = {
  key: 0,
  class: "fwde-section fwde-section--saved"
};
const _hoisted_5 = {
  class: "fwde-section"
};
const _hoisted_6 = {
  class: "fwde-section__title"
};
const _hoisted_7 = {
  class: "fwde-section__count"
};
const _hoisted_8 = {
  class: "fwde-section"
};
const _hoisted_9 = {
  class: "fwde-section__title"
};
const _hoisted_10 = {
  class: "fwde-section__count"
};
const _hoisted_11 = {
  class: "fwde-section"
};
const _hoisted_12 = {
  class: "fwde-section__title"
};
const _hoisted_13 = {
  key: 0,
  class: "fwde-filtersummary"
};
const _hoisted_14 = {
  class: "fwde-page__content"
};
const _hoisted_15 = {
  class: "fwde-card fwde-main"
};
const _hoisted_16 = {
  class: "fwde-card__header"
};
const _hoisted_17 = {
  class: "fwde-card__heading"
};
const _hoisted_18 = {
  class: "fwde-card__title"
};
const _hoisted_19 = {
  key: 0,
  class: "fwde-card__changed"
};
const _hoisted_20 = {
  key: 0,
  class: "fwde-card__chips"
};
const _hoisted_21 = {
  class: "fwde-chip fwde-chip--period"
};
const _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-calendar",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_23 = {
  class: "fwde-chip fwde-chip--segment"
};
const _hoisted_24 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-segment",
  "aria-hidden": "true"
}, null, -1);
const _hoisted_25 = {
  class: "fwde-card__body"
};
const _hoisted_26 = {
  key: 2,
  class: "fwde-archived-hint"
};
const _hoisted_27 = {
  key: 1,
  class: "fwde-archived-place"
};
const _hoisted_28 = {
  key: 6,
  class: "pk-emptyDataTable fwde-empty"
};
const _hoisted_29 = {
  class: "fwde-empty__message"
};
const _hoisted_30 = {
  key: 7,
  class: "fwde-compare__hint"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_SavedPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SavedPicker");
  const _component_DimensionPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DimensionPicker");
  const _component_PivotPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PivotPicker");
  const _component_MetricPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MetricPicker");
  const _component_FilterPanel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FilterPanel");
  const _component_ExplorationToolbar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ExplorationToolbar");
  const _component_SaveDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveDialog");
  const _component_ResultChart = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ResultChart");
  const _component_CompareTable = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("CompareTable");
  const _component_PivotTable = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PivotTable");
  const _component_ResultTable = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ResultTable");
  const _component_FeedbackBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeedbackBar");
  const _component_PromoLine = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PromoLine");
  const _component_PremiumBanner = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PremiumBanner");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_1, [!_ctx.catalog && !_ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ActivityIndicator, {
    key: 0,
    loading: true
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadError), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.catalog && _ctx.state ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("aside", {
    class: "fwde-card fwde-sidebar",
    "aria-label": _ctx.translate('FeinwerkDataExploration_Setup')
  }, [_ctx.savedLoaded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", _hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SavedPicker, {
    saved: _ctx.saved,
    current: _ctx.currentSaved,
    login: _ctx.login,
    "can-copy": _ctx.canCopy,
    "can-manage": _ctx.canManage,
    "menu-sections": _ctx.menuSections,
    onOpen: _ctx.openSaved,
    onDeleted: _cache[0] || (_cache[0] = $event => _ctx.savedDeleted($event)),
    onChanged: _cache[1] || (_cache[1] = $event => _ctx.loadSavedList())
  }, null, 8, ["saved", "current", "login", "can-copy", "can-manage", "menu-sections", "onOpen"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Dimensions')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_MetricsCount', _ctx.state.dimensions.length, _ctx.MAX_DIMENSIONS)), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DimensionPicker, {
    ref: "dimensionPicker",
    catalog: _ctx.catalog,
    state: _ctx.state,
    onChange: _cache[2] || (_cache[2] = $event => _ctx.update({
      dimensions: $event
    }))
  }, null, 8, ["catalog", "state"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PivotPicker, {
    catalog: _ctx.catalog,
    state: _ctx.state,
    comparing: _ctx.comparing,
    onChange: _cache[3] || (_cache[3] = $event => _ctx.update($event))
  }, null, 8, ["catalog", "state", "comparing"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Metrics')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricsCount), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MetricPicker, {
    catalog: _ctx.catalog,
    state: _ctx.state,
    "action-scope": _ctx.actionScope,
    onChange: _cache[4] || (_cache[4] = $event => _ctx.update({
      metrics: $event
    }))
  }, null, 8, ["catalog", "state", "action-scope"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Filters')), 1), _ctx.filterLines.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", _hoisted_13, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.filterLines, (line, i) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: i,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-filtersummary__item', {
        'fwde-filtersummary__match': line.join
      }])
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(line.text), 3);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button fwde-filters__edit",
    "aria-haspopup": "dialog",
    onClick: _cache[5] || (_cache[5] = $event => _ctx.filtersOpen = true)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.filterLines.length ? 'icon-edit' : 'icon-add'),
    "aria-hidden": "true"
  }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.filterLines.length ? 'FeinwerkDataExploration_EditFilters' : 'FeinwerkDataExploration_AddFilter')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FilterPanel, {
    open: _ctx.filtersOpen,
    "onUpdate:open": _cache[6] || (_cache[6] = $event => _ctx.filtersOpen = $event),
    catalog: _ctx.catalog,
    state: _ctx.state,
    "action-scope": _ctx.actionScope,
    onApply: _cache[7] || (_cache[7] = $event => _ctx.update($event))
  }, null, 8, ["open", "catalog", "state", "action-scope"])])], 8, _hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", _hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), _ctx.savedChanged ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedChanged')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ExplorationToolbar, {
    class: "fwde-card__actions",
    state: _ctx.state,
    "max-rows": _ctx.maxRows,
    view: _ctx.view,
    "can-chart": _ctx.canChart,
    "export-url-for": _ctx.comparing ? _ctx.compareExportUrl : undefined,
    onLimit: _cache[8] || (_cache[8] = $event => _ctx.update({
      limit: $event
    })),
    onView: _cache[9] || (_cache[9] = $event => _ctx.update({
      view: $event
    }))
  }, null, 8, ["state", "max-rows", "view", "can-chart", "export-url-for"]), _ctx.savedLoaded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveDialog, {
    key: 0,
    class: "fwde-card__save",
    current: _ctx.currentSaved,
    "suggested-name": _ctx.resultName,
    "can-share": _ctx.canShare,
    busy: _ctx.saveBusy,
    error: _ctx.saveError,
    "menu-sections": _ctx.menuSections,
    metrics: _ctx.saveMetrics,
    "history-lost-from": _ctx.historyLostFrom,
    onOpen: _cache[10] || (_cache[10] = $event => _ctx.saveError = ''),
    onSave: _ctx.saveExploration,
    onDelete: _ctx.deleteCurrent
  }, null, 8, ["current", "suggested-name", "can-share", "busy", "error", "menu-sections", "metrics", "history-lost-from", "onSave", "onDelete"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.chips ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_20, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_21, [_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.chips.period), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_23, [_hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.chips.segment), 1)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_25, [_ctx.showRawDataWarning ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 0,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RawDataWarning', _ctx.rawDataFrom)) + " ", 1), _ctx.rawDataRange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwde-alert-link",
      onClick: _cache[11] || (_cache[11] = $event => _ctx.showFrom(_ctx.rawDataRange))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ShowFromFirstCompleteDay')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.largePeriodHint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.largePeriodHint), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 2,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error) + " ", 1), _ctx.canRetry ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwde-alert-link fwde-retry",
      onClick: _cache[12] || (_cache[12] = (...args) => _ctx.run && _ctx.run(...args))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_TryAgain')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.slowHint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 3,
    severity: "info"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.slowHint), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.suggestedPeriod ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 4,
    severity: "info"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.suggestedPeriod.date ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SmallerPeriodHint')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-alert-link fwde-suggested-period",
      onClick: _cache[13] || (_cache[13] = $event => _ctx.showFrom(_ctx.suggestedPeriod.date))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.suggestedPeriodLabel), 1)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.noSmallerPeriodKey)), 1)], 64)), _ctx.archivedHint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_26, [_ctx.archivedHint === 'open' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwde-alert-link fwde-archived-report",
      onClick: _cache[14] || (_cache[14] = (...args) => _ctx.openArchivedReport && _ctx.openArchivedReport(...args))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ArchivedOpenReport')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_27, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ArchivedPlaceInMenu')), 1))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.loading,
    "loading-message": _ctx.translate('FeinwerkDataExploration_LoadingExploration')
  }, null, 8, ["loading", "loading-message"]), _ctx.comparing && _ctx.comparison.cut ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 5,
    severity: "info"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.compareLimitText), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columnErrors, (message, n) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
      key: `column-${n}`,
      severity: "warning"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(message), 1)]),
      _: 2
    }, 1024);
  }), 128)), !_ctx.loading && !_ctx.error && _ctx.ownRows && !_ctx.ownRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_28, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", _hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.empty.message)), 1), _ctx.empty.editFilters ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwde-button fwde-empty__edit",
    "aria-haspopup": "dialog",
    onClick: _cache[15] || (_cache[15] = $event => _ctx.filtersOpen = true)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_EditFilters')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.comparing && _ctx.compareHint && !_ctx.error && _ctx.ownRows && _ctx.ownRows.length && _ctx.view !== 'table' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_30, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.compareHint)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showsTrend ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 8
  }, [_ctx.trendError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 0,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.trendError), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.trendErrors, (message, n) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
      key: `trend-${n}`,
      severity: "warning"
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(message), 1)]),
      _: 2
    }, 1024);
  }), 128))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.error && _ctx.ownRows && _ctx.ownRows.length && _ctx.view !== 'table' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ResultChart, {
    key: 9,
    rows: _ctx.fullRows && _ctx.fullRowsKey === _ctx.lastDefinition ? _ctx.fullRows : _ctx.ownRows,
    "row-limit": _ctx.fullRows && _ctx.fullRowsKey === _ctx.lastDefinition ? _ctx.maxRows : 0,
    state: _ctx.state,
    catalog: _ctx.catalog,
    metric: _ctx.chartMetric,
    "action-scope": _ctx.actionScope,
    "currency-symbol": _ctx.currencySymbol,
    "hour-labels": _ctx.hourLabels,
    "period-days": _ctx.periodDays,
    "chart-type": _ctx.chartType,
    "show-type-switch": _ctx.showTypeSwitch,
    "trend-rows": _ctx.trendRows,
    "trend-loading": _ctx.trendLoading,
    "max-lines": _ctx.maxTrendLines,
    "compare-columns": _ctx.chartCompareColumns,
    "donut-reason": _ctx.donutReason,
    "others-value": _ctx.donutOthers,
    "others-note": _ctx.donutOthersNote,
    "pivot-values": _ctx.pivotValues,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'fwde-main__stale': _ctx.loading
    }),
    onMetric: _cache[16] || (_cache[16] = $event => _ctx.update({
      chartMetric: $event
    })),
    onChartType: _cache[17] || (_cache[17] = $event => _ctx.update({
      chartType: $event
    }))
  }, null, 8, ["rows", "row-limit", "state", "catalog", "metric", "action-scope", "currency-symbol", "hour-labels", "period-days", "chart-type", "show-type-switch", "trend-rows", "trend-loading", "max-lines", "compare-columns", "donut-reason", "others-value", "others-note", "pivot-values", "class"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.comparing && !_ctx.error && _ctx.rows && _ctx.ownRows && _ctx.ownRows.length && _ctx.view !== 'chart' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_CompareTable, {
    key: 10,
    rows: _ctx.rows,
    state: _ctx.state,
    catalog: _ctx.catalog,
    columns: _ctx.comparison.columns,
    "column-errors": _ctx.columnErrors,
    "action-scope": _ctx.actionScope,
    "currency-symbol": _ctx.currencySymbol,
    "visits-log-enabled": _ctx.visitsLogEnabled,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'fwde-main__stale': _ctx.loading
    }),
    onSort: _ctx.sortBy,
    onRowFilter: _ctx.filterToRow,
    onRowBreakdown: _ctx.breakDownRow,
    onRowVisits: _ctx.showRowVisits
  }, null, 8, ["rows", "state", "catalog", "columns", "column-errors", "action-scope", "currency-symbol", "visits-log-enabled", "class", "onSort", "onRowFilter", "onRowBreakdown", "onRowVisits"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pivoting && !_ctx.error && _ctx.rows && _ctx.ownRows && _ctx.ownRows.length && _ctx.view !== 'chart' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PivotTable, {
    key: 11,
    rows: _ctx.rows,
    state: _ctx.state,
    catalog: _ctx.catalog,
    "action-scope": _ctx.actionScope,
    "currency-symbol": _ctx.currencySymbol,
    "visits-log-enabled": _ctx.visitsLogEnabled,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'fwde-main__stale': _ctx.loading
    }),
    onSort: _ctx.sortBy,
    onRowFilter: _ctx.filterToRow,
    onRowBreakdown: _ctx.breakDownRow,
    onRowVisits: _ctx.showRowVisits
  }, null, 8, ["rows", "state", "catalog", "action-scope", "currency-symbol", "visits-log-enabled", "class", "onSort", "onRowFilter", "onRowBreakdown", "onRowVisits"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.comparing && !_ctx.pivoting && !_ctx.error && _ctx.rows && _ctx.rows.length && _ctx.view !== 'chart' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ResultTable, {
    key: 12,
    rows: _ctx.rows,
    state: _ctx.state,
    catalog: _ctx.catalog,
    "action-scope": _ctx.actionScope,
    "currency-symbol": _ctx.currencySymbol,
    "visits-log-enabled": _ctx.visitsLogEnabled,
    cut: _ctx.tableCut,
    others: _ctx.others,
    "others-state": _ctx.othersState,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'fwde-main__stale': _ctx.loading
    }),
    onSort: _ctx.sortBy,
    onRowFilter: _ctx.filterToRow,
    onRowBreakdown: _ctx.breakDownRow,
    onRowVisits: _ctx.showRowVisits,
    onOthersCalculate: _ctx.calculateOthers
  }, null, 8, ["rows", "state", "catalog", "action-scope", "currency-symbol", "visits-log-enabled", "cut", "others", "others-state", "class", "onSort", "onRowFilter", "onRowBreakdown", "onRowVisits", "onOthersCalculate"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.error && _ctx.rows ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FeedbackBar, {
    key: 13,
    "id-site": _ctx.idSite,
    topic: "free_form_table",
    question: _ctx.translate('FeinwerkDataExploration_FeedbackQuestion_free_form_table'),
    feedback: _ctx.feedback
  }, null, 8, ["id-site", "question", "feedback"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.error && _ctx.rows ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PromoLine, {
    key: 14,
    promo: _ctx.promo,
    "dismiss-label": _ctx.translate('FeinwerkDataExploration_PromoDismiss')
  }, null, 8, ["promo", "dismiss-label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PremiumBanner, {
    premium: _ctx.premium,
    label: _ctx.translate('FeinwerkDataExploration_PremiumLabel'),
    "more-label": _ctx.translate('FeinwerkDataExploration_PremiumMore'),
    "dismiss-label": _ctx.translate('FeinwerkDataExploration_PremiumDismiss')
  }, null, 8, ["premium", "label", "more-label", "dismiss-label"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=template&id=fd0063f0

// EXTERNAL MODULE: external "CoreHome"
var external_CoreHome_ = __webpack_require__("19dc");

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/hints.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/** A changed exploration runs after this pause, so a few quick clicks start one request. */
const RUN_DELAY_MS = 400;
/**
 * Whether to warn before exploring: the period has more than a quarter of the visits an
 * exploration may read. Above the threshold the exploration refuses with its own message.
 */
function isLargePeriod(visits, maxVisits) {
  return maxVisits > 0 && visits > maxVisits / 4 && visits <= maxVisits;
}
/**
 * Whether to offer the most recent days that fit: the period has more visits than an exploration
 * reads, or it could not be counted (e.g. longer than an exploration covers).
 */
function needsSmallerPeriod(info, countFailed) {
  return info ? info.visits > info.maxVisits : countFailed;
}
/**
 * Whether "try again" can help: only when the instance was busy or the time limit stopped the
 * query; any other error would come back the same way.
 *
 * @param busy the translated busy message
 * @param timeoutStart the translated time limit message up to its number of seconds
 */
function canRetry(message, busy, timeoutStart) {
  return message !== '' && (message === busy || timeoutStart !== '' && message.startsWith(timeoutStart));
}
/**
 * The text when even the most recent day is above the visit threshold. A segment does not help (the
 * threshold counts all visits of the period); only the threshold setting does.
 */
function noSmallerPeriodKey(isSuperUser) {
  return isSuperUser ? 'FeinwerkDataExploration_SmallerPeriodNoneSuperUser' : 'FeinwerkDataExploration_SmallerPeriodNone';
}
/**
 * Whether a finished request was slow enough to say how to make it faster: at least 40 % of the
 * time limit and at least 3 seconds; 10 seconds when there is no limit.
 */
function isSlow(elapsedMs, timeoutSeconds) {
  if (timeoutSeconds <= 0) {
    return elapsedMs >= 10000;
  }
  return elapsedMs >= 3000 && elapsedMs >= timeoutSeconds * 400;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/period.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function toDate(ymd) {
  return new Date(`${ymd}T00:00:00Z`);
}
function toYmd(date) {
  return date.toISOString().substring(0, 10);
}
function addDays(ymd, days) {
  const date = toDate(ymd);
  date.setUTCDate(date.getUTCDate() + days);
  return toYmd(date);
}
function resolveDay(date, today) {
  if (date === 'today' || date === 'now') {
    return today;
  }
  if (date === 'yesterday') {
    return addDays(today, -1);
  }
  return /^\d{4}-\d{2}-\d{2}$/.test(date) ? date : null;
}
/**
 * The first day (Y-m-d) of a Matomo period, or null when it cannot be told.
 */
function periodStart(period, date, today) {
  if (period === 'range') {
    const last = /^(last|previous)(\d+)$/.exec(date);
    if (last) {
      const count = Number(last[2]);
      return addDays(today, last[1] === 'last' ? 1 - count : -count);
    }
    return resolveDay(date.split(',')[0], today);
  }
  const day = resolveDay(date, today);
  if (day === null) {
    return null;
  }
  if (period === 'day') {
    return day;
  }
  if (period === 'week') {
    const weekday = (toDate(day).getUTCDay() + 6) % 7; // Monday = 0
    return addDays(day, -weekday);
  }
  if (period === 'month') {
    return `${day.substring(0, 8)}01`;
  }
  return null;
}
function lastDayOfMonth(day) {
  const date = toDate(`${day.substring(0, 8)}01`);
  date.setUTCMonth(date.getUTCMonth() + 1);
  date.setUTCDate(0);
  return toYmd(date);
}
function periodEnd(period, date, start, today) {
  if (period === 'range') {
    const last = /^(last|previous)(\d+)$/.exec(date);
    if (last) {
      return last[1] === 'last' ? today : addDays(today, -1);
    }
    return resolveDay(date.split(',')[1] || '', today);
  }
  if (period === 'day') {
    return start;
  }
  if (period === 'week') {
    return addDays(start, 6);
  }
  return period === 'month' ? lastDayOfMonth(start) : null;
}
/**
 * Every day (Y-m-d) of a Matomo period up to today, or null when it cannot be told (or is longer
 * than two months, more than an exploration covers).
 */
function period_periodDays(period, date, today) {
  const start = periodStart(period, date, today);
  const end = start === null ? null : periodEnd(period, date, start, today);
  if (start === null || end === null) {
    return null;
  }
  const last = end < today ? end : today;
  const days = [];
  for (let day = start; day <= last; day = addDays(day, 1)) {
    if (days.length >= 62) {
      return null;
    }
    days.push(day);
  }
  return days;
}
/**
 * A date range from the given day to the end of the period (for "show from the first complete
 * day"), or null when the period cannot be read or ends before that day.
 */
function rangeFromDay(period, date, day, today) {
  const start = periodStart(period, date, today);
  const end = start === null ? null : periodEnd(period, date, start, today);
  if (start === null || end === null) {
    return null;
  }
  const last = end < today ? end : today;
  return last < day ? null : `${day},${last}`;
}
/** Whether the period starts before the earliest complete day of raw data. */
function startsBefore(period, date, earliest, today) {
  const start = periodStart(period, date, today);
  return earliest !== null && start !== null && start < earliest;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/pageHints.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/** The hints both exploration pages (free-form table, funnel) show around their result. */
/** Before exploring a period with many visits; '' when it is small enough. */
function largePeriodHint(info) {
  if (!info || !isLargePeriod(info.visits, info.maxVisits)) {
    return '';
  }
  const visits = external_CoreHome_["NumberFormatter"].formatNumber(info.visits, 0, 0);
  return info.timeoutSeconds > 0 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LargePeriodHint', visits, String(info.timeoutSeconds)) : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LargePeriodHintNoLimit', visits);
}
/** After a slow result; '' otherwise. */
function slowHint(elapsedMs, info, loading, error) {
  const timeout = info ? info.timeoutSeconds : 0;
  if (loading || error || elapsedMs === null || !isSlow(elapsedMs, timeout)) {
    return '';
  }
  const seconds = external_CoreHome_["NumberFormatter"].formatNumber(elapsedMs / 1000, 0, 0);
  return timeout > 0 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SlowQueryHint', seconds, String(timeout)) : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SlowQueryHintNoLimit', seconds);
}
function suggestedPeriodLabel(days) {
  return days === 1 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LoadLastDay') : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LoadLastDays', String(days));
}
/** A busy or timed-out request may be tried again. */
function retryAllowed(error) {
  // both role variants of the time limit message start with the same sentence
  const timeout = Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ErrorTimeout', '\u0000');
  return canRetry(error, Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ErrorBusy'), timeout.includes('\u0000') ? timeout.slice(0, timeout.indexOf('\u0000')) : '');
}
function noSmallerPeriodKeyForUser() {
  const {
    piwik
  } = window;
  return noSmallerPeriodKey(!!(piwik && piwik.hasSuperUserAccess));
}
function pageHints_today() {
  return new Date().toISOString().substring(0, 10);
}
/** The part of the period that still has raw data, null when all of it has. */
function rawDataRange(rawDataFrom) {
  if (!rawDataFrom) {
    return null;
  }
  const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
  return rangeFromDay(String(parsed.period), String(parsed.date), rawDataFrom, pageHints_today());
}
/** The period starts before the first day with complete raw data. */
function showsRawDataWarning(rawDataFrom) {
  const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
  return startsBefore(String(parsed.period), String(parsed.date), rawDataFrom, pageHints_today());
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=template&id=87f3a9c4

const DimensionPickervue_type_template_id_87f3a9c4_hoisted_1 = {
  class: "fwde-picker"
};
const DimensionPickervue_type_template_id_87f3a9c4_hoisted_2 = {
  class: "fwde-field__label"
};
const DimensionPickervue_type_template_id_87f3a9c4_hoisted_3 = ["aria-expanded", "onClick"];
const DimensionPickervue_type_template_id_87f3a9c4_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const DimensionPickervue_type_template_id_87f3a9c4_hoisted_5 = {
  key: 0,
  class: "fwde-picker__panel"
};
const DimensionPickervue_type_template_id_87f3a9c4_hoisted_6 = ["onClick"];
function DimensionPickervue_type_template_id_87f3a9c4_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchableList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchableList");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DimensionPickervue_type_template_id_87f3a9c4_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.slots, slot => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: slot,
      class: "fwde-picker__slot"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DimensionPickervue_type_template_id_87f3a9c4_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.SLOT_KEYS[slot])), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      ref_for: true,
      ref: `current${slot}`,
      type: "button",
      class: "fwde-picker__current",
      "aria-expanded": _ctx.openSlot === slot ? 'true' : 'false',
      onClick: $event => _ctx.openSlot = _ctx.openSlot === slot ? null : slot
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.currentName(slot)), 1), DimensionPickervue_type_template_id_87f3a9c4_hoisted_4], 8, DimensionPickervue_type_template_id_87f3a9c4_hoisted_3), _ctx.openSlot === slot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DimensionPickervue_type_template_id_87f3a9c4_hoisted_5, [slot > 0 && _ctx.dimensions.length > slot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "btn-flat fwde-picker__none",
      onClick: $event => _ctx.choose(slot, null)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NoSecondDimension')), 9, DimensionPickervue_type_template_id_87f3a9c4_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchableList, {
      items: _ctx.items(slot),
      selected: _ctx.dimensions[slot] ? [_ctx.dimensions[slot]] : [],
      "search-label": _ctx.translate('FeinwerkDataExploration_SearchDimensions'),
      onToggle: $event => _ctx.choose(slot, $event),
      onClose: $event => _ctx.close(slot)
    }, null, 8, ["items", "selected", "search-label", "onToggle", "onClose"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=template&id=87f3a9c4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=template&id=50597c1d

const SearchableListvue_type_template_id_50597c1d_hoisted_1 = {
  class: "fwde-list__groups"
};
const SearchableListvue_type_template_id_50597c1d_hoisted_2 = {
  class: "fwde-list__group-name"
};
const SearchableListvue_type_template_id_50597c1d_hoisted_3 = ["title"];
const SearchableListvue_type_template_id_50597c1d_hoisted_4 = ["type", "name", "checked", "disabled", "data-key", "onChange"];
const SearchableListvue_type_template_id_50597c1d_hoisted_5 = {
  class: "fwde-list__label"
};
const SearchableListvue_type_template_id_50597c1d_hoisted_6 = {
  key: 0,
  class: "fwde-list__reason"
};
const SearchableListvue_type_template_id_50597c1d_hoisted_7 = {
  key: 0,
  class: "fwde-list__empty"
};
function SearchableListvue_type_template_id_50597c1d_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchInput");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "fwde-list",
    onKeydown: _cache[1] || (_cache[1] = (...args) => _ctx.onKeydown && _ctx.onKeydown(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchInput, {
    modelValue: _ctx.query,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.query = $event),
    placeholder: _ctx.searchLabel,
    "show-clear": true
  }, null, 8, ["modelValue", "placeholder"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SearchableListvue_type_template_id_50597c1d_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: group.name,
      class: "fwde-list__group"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SearchableListvue_type_template_id_50597c1d_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(group.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.items, item => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
        key: item.key
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          'fwde-list__item': true,
          'fwde-list__item--selected': _ctx.selected.includes(item.key),
          'fwde-list__item--disabled': _ctx.isDisabled(item)
        }),
        title: _ctx.itemTitle(item)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
        type: _ctx.multiple ? 'checkbox' : 'radio',
        class: "fwde-list__input",
        name: _ctx.inputName,
        checked: _ctx.selected.includes(item.key),
        disabled: _ctx.isDisabled(item),
        "data-key": item.key,
        onChange: $event => _ctx.$emit('toggle', item.key)
      }, null, 40, SearchableListvue_type_template_id_50597c1d_hoisted_4), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SearchableListvue_type_template_id_50597c1d_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item.label), 1), item.reason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SearchableListvue_type_template_id_50597c1d_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(item.reason)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, SearchableListvue_type_template_id_50597c1d_hoisted_3)]);
    }), 128))])]);
  }), 128)), !_ctx.groups.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SearchableListvue_type_template_id_50597c1d_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NoMatches')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 32);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=template&id=50597c1d

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=script&lang=ts


let instances = 0;
/* harmony default export */ var SearchableListvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SearchInput: external_CoreHome_["SearchInput"]
  },
  props: {
    items: {
      type: Array,
      required: true
    },
    selected: {
      type: Array,
      required: true
    },
    multiple: {
      type: Boolean,
      default: false
    },
    searchLabel: {
      type: String,
      required: true
    }
  },
  emits: ['toggle', 'close'],
  data() {
    instances += 1;
    return {
      query: '',
      inputName: `fwde-list-${instances}`
    };
  },
  computed: {
    groups() {
      const needle = this.query.trim().toLowerCase();
      const groups = [];
      this.items.forEach(item => {
        if (needle && !`${item.label} ${item.group}`.toLowerCase().includes(needle)) {
          return;
        }
        let group = groups.find(g => g.name === item.group);
        if (!group) {
          group = {
            name: item.group,
            items: []
          };
          groups.push(group);
        }
        group.items.push(item);
      });
      return groups;
    }
  },
  methods: {
    itemTitle(item) {
      if (item.reason) {
        return `${item.label}: ${Object(external_CoreHome_["translate"])(item.reason)}`;
      }
      return item.description ? `${item.label}: ${item.description}` : item.label;
    },
    translate: external_CoreHome_["translate"],
    isDisabled(item) {
      return item.reason !== null && !this.selected.includes(item.key);
    },
    /**
     * Keyboard (design 2026-11-20): the arrow keys move the focus over the items that can be
     * chosen (and from the first one back to the search), Enter chooses the focused item or, in
     * the search, the first match, Escape asks the picker to close. Native radios would select
     * on the arrow keys, so those are handled here.
     */
    onKeydown(event) {
      const root = this.$el;
      const inputs = Array.from(root.querySelectorAll('input.fwde-list__input:not(:disabled)'));
      const search = root.querySelector('input:not(.fwde-list__input)');
      const focused = document.activeElement;
      const index = focused ? inputs.indexOf(focused) : -1;
      if (event.key === 'Escape') {
        event.preventDefault();
        this.$emit('close');
      } else if (event.key === 'ArrowDown' && inputs.length) {
        event.preventDefault();
        this.focusInput(inputs[index < 0 ? 0 : Math.min(index + 1, inputs.length - 1)]);
      } else if (event.key === 'ArrowUp' && (index >= 0 || inputs.length)) {
        event.preventDefault();
        if (index <= 0) {
          if (search) {
            search.focus();
          }
        } else {
          this.focusInput(inputs[index - 1]);
        }
      } else if (event.key === 'Enter') {
        let target = null;
        if (index >= 0) {
          target = inputs[index];
        } else if (focused === search) {
          // from the search Enter adds: the first match not chosen yet (a focused item toggles,
          // like Space)
          target = inputs.find(input => !input.checked) || null;
        }
        const itemKey = target ? target.getAttribute('data-key') : null;
        if (itemKey) {
          event.preventDefault();
          this.$emit('toggle', itemKey);
        }
      }
    },
    focusInput(input) {
      input.focus();
      if (typeof input.scrollIntoView === 'function') {
        input.scrollIntoView({
          block: 'nearest'
        });
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue



SearchableListvue_type_script_lang_ts.render = SearchableListvue_type_template_id_50597c1d_render

/* harmony default export */ var SearchableList = (SearchableListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/metricMeta.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const META = {
  nb_visits: ['count', 'sum'],
  nb_uniq_visitors: ['count', 'distinct'],
  nb_actions: ['count', 'sum'],
  bounce_rate: ['rate', 'ratio'],
  avg_time_on_site: ['seconds', 'average'],
  nb_actions_per_visit: ['number', 'average'],
  max_actions: ['count', 'max'],
  sum_visit_length: ['seconds', 'sum'],
  nb_conversions: ['count', 'sum'],
  conversion_rate: ['rate', 'ratio'],
  revenue: ['money', 'sum'],
  nb_visits_converted: ['count', 'sum'],
  revenue_per_visit: ['money', 'average'],
  nb_pageviews: ['count', 'sum'],
  nb_uniq_pageviews: ['count', 'distinct'],
  nb_events: ['count', 'sum'],
  sum_event_value: ['number', 'sum'],
  avg_event_value: ['number', 'average'],
  nb_downloads: ['count', 'sum'],
  nb_outlinks: ['count', 'sum'],
  nb_searches: ['count', 'sum']
};
const GOAL_METRICS = {
  goal: {
    nb_conversions: ['count', 'sum'],
    conversion_rate: ['rate', 'ratio'],
    revenue: ['money', 'sum']
  },
  ecommerceOrder: {
    nb_conversions: ['count', 'sum'],
    revenue: ['money', 'sum'],
    items: ['count', 'sum'],
    avg_order_revenue: ['money', 'average'],
    conversion_rate: ['rate', 'ratio']
  },
  ecommerceAbandonedCart: {
    nb_conversions: ['count', 'sum'],
    revenue: ['money', 'sum']
  }
};
const GOAL_ID = /^goal_(\d+|ecommerceOrder|ecommerceAbandonedCart)_([a-z_]+)$/;
/** Unit and aggregation of a metric, null for an unknown id. */
function metricMeta(id) {
  let entry = META[id];
  if (!entry) {
    const match = id.match(GOAL_ID);
    if (match) {
      entry = GOAL_METRICS[/^\d+$/.test(match[1]) ? 'goal' : match[1]][match[2]];
    }
  }
  return entry ? {
    unit: entry[0],
    aggregation: entry[1]
  } : null;
}
/** A fraction from 0 to 1: shown and entered in percent. */
function isRate(id) {
  var _metricMeta;
  return ((_metricMeta = metricMeta(id)) === null || _metricMeta === void 0 ? void 0 : _metricMeta.unit) === 'rate';
}
/** An amount in the website's currency. */
function isMoney(id) {
  var _metricMeta2;
  return ((_metricMeta2 = metricMeta(id)) === null || _metricMeta2 === void 0 ? void 0 : _metricMeta2.unit) === 'money';
}
/** A duration in seconds: shown as time, entered in seconds. */
function isSeconds(id) {
  var _metricMeta3;
  return ((_metricMeta3 = metricMeta(id)) === null || _metricMeta3 === void 0 ? void 0 : _metricMeta3.unit) === 'seconds';
}
/** Counts and sums: a share of the column total and a bar in the cell make sense. */
function hasShare(id) {
  var _metricMeta4;
  const aggregation = (_metricMeta4 = metricMeta(id)) === null || _metricMeta4 === void 0 ? void 0 : _metricMeta4.aggregation;
  return aggregation === 'sum' || aggregation === 'distinct';
}
/** A missing row or point counts 0 (counts and sums); rates, averages and maxima leave a gap. */
function metricMeta_zeroForMissing(id) {
  return hasShare(id);
}
/** Whole numbers (counts, seconds, maxima): an axis steps by whole numbers. */
function isInteger(id) {
  const meta = metricMeta(id);
  return meta !== null && (meta.unit === 'count' || meta.unit === 'seconds' || meta.aggregation === 'max');
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/labels.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/** The label columns of the row dimensions, in order (ExplorationDefinition::LABEL_COLUMNS). */
const LABEL_COLUMNS = ['label', 'secondary_label', 'tertiary_label'];
/** The label columns of a table with that many row dimensions. */
function labelColumns(dimensionCount) {
  return LABEL_COLUMNS.slice(0, Math.max(1, Math.min(dimensionCount, LABEL_COLUMNS.length)));
}
/** The row metadata of the stored value behind each label column (Explorer::toDataTable). */
const STORED_VALUES = ['fwde_value', 'fwde_value2', 'fwde_value3'];
/** the heading of each dimension group in the dimension lists */
const GROUP_KEYS = {
  acquisition: 'FeinwerkDataExploration_GroupAcquisition',
  visitor: 'FeinwerkDataExploration_GroupVisitor',
  technology: 'FeinwerkDataExploration_GroupTechnology',
  time: 'FeinwerkDataExploration_GroupTime',
  visit: 'FeinwerkDataExploration_GroupVisit',
  behaviour: 'FeinwerkDataExploration_GroupBehaviour',
  custom: 'FeinwerkDataExploration_GroupCustom'
};
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/filterGroups.ts


/** conditions in all groups together (filter groups, 2026-11-23) */
const MAX_FILTERS = 10;
const MAX_GROUPS = 5;
/** Long enough for a stored URL ("filter to this value" in the row menu). */
const MAX_FILTER_TEXT = 1000;
/** the longest regular expression of "matches" */
const MAX_PATTERN = 200;
/** in the order the filter panel offers them: each condition next to its negation */
const TEXT_OPERATORS = ['contains', 'notContains', 'equals', 'notEquals', 'startsWith', 'notStartsWith', 'endsWith', 'notEndsWith', 'matches', 'empty', 'notEmpty'];
/**
 * conditions without a value (Peter, 2026-11-25: "Es muss auch eine Bedingung 'nicht leer'
 * geben"): empty is what the table shows as "... not defined" or "Unknown"
 */
const VALUELESS_OPERATORS = ['empty', 'notEmpty'];
const NUMBER_OPERATORS = ['gt', 'gte', 'lt', 'lte'];
function isValidFilter(filter, dimensionCount, metrics) {
  if (VALUELESS_OPERATORS.includes(filter.operator)) {
    return labelColumns(dimensionCount).includes(filter.target);
  }
  if (TEXT_OPERATORS.includes(filter.operator)) {
    const text = filter.value.trim();
    const max = filter.operator === 'matches' ? MAX_PATTERN : MAX_FILTER_TEXT;
    return labelColumns(dimensionCount).includes(filter.target) && text !== '' && text.length <= max;
  }
  if (NUMBER_OPERATORS.includes(filter.operator)) {
    return metrics.includes(filter.target) && filter.value.trim() !== '' && Number.isFinite(Number(filter.value));
  }
  return false;
}
const GROUP_PREFIX = /^(all|any):/;
/**
 * One condition "target~operator~value" with the value URI-encoded; only the first two "~"
 * separate.
 */
function parseCondition(entry) {
  const first = entry.indexOf('~');
  const second = first < 0 ? -1 : entry.indexOf('~', first + 1);
  if (second < 0) {
    return null;
  }
  try {
    const operator = entry.substring(first + 1, second);
    return {
      target: entry.substring(0, first),
      operator,
      // "is empty" and "is not empty" have no value; one in the link is ignored
      value: VALUELESS_OPERATORS.includes(operator) ? '' : decodeURIComponent(entry.substring(second + 1))
    };
  } catch (e) {
    return null;
  }
}
/**
 * The filters of a link: groups joined by "|", each "[any:]condition;condition" ("all" without a
 * prefix), fwdeMatch the switch between the groups. A value with neither "|" nor a prefix is a
 * link from before filter groups: one group whose switch is fwdeMatch. Broken conditions, the
 * groups they empty and what passes the limits are dropped.
 */
function parseFilterParam(value, match, dimensionCount, metrics) {
  const raw = String(value || '');
  const parts = raw === '' ? [] : raw.split('|');
  const legacy = parts.length === 1 && !GROUP_PREFIX.test(parts[0]);
  const filters = [];
  const groupMatch = [];
  parts.forEach(part => {
    const prefix = part.match(GROUP_PREFIX);
    let switchValue = prefix && prefix[1] === 'any' ? 'any' : 'all';
    if (legacy) {
      switchValue = match === 'any' ? 'any' : 'all';
    }
    if (groupMatch.length >= MAX_GROUPS) {
      return;
    }
    const group = groupMatch.length;
    let kept = 0;
    (prefix ? part.substring(prefix[0].length) : part).split(';').forEach(entry => {
      const condition = parseCondition(entry);
      if (condition && filters.length < MAX_FILTERS && isValidFilter(condition, dimensionCount, metrics)) {
        filters.push(Object.assign(Object.assign({}, condition), {}, {
          value: condition.value.trim(),
          group
        }));
        kept += 1;
      }
    });
    if (kept) {
      groupMatch.push(switchValue);
    }
  });
  return {
    filters,
    groupMatch,
    match: !legacy && groupMatch.length > 1 && match === 'any' ? 'any' : 'all'
  };
}
/** The groups in order, each with its switch and its conditions. */
function groupsOf(filters, groupMatch) {
  const indexes = [...new Set(filters.map(f => f.group))].sort((a, b) => a - b);
  return indexes.map(group => ({
    match: groupMatch[group] === 'any' ? 'any' : 'all',
    filters: filters.filter(f => f.group === group)
  }));
}
/** fwdeFilters of the link (the new form only: an "any" group carries its prefix). */
function filterParam(filters, groupMatch) {
  return groupsOf(filters, groupMatch).map(group => (group.match === 'any' ? 'any:' : '') + group.filters.map(f => `${f.target}~${f.operator}~${encodeURIComponent(f.value)}`).join(';')).join('|');
}
/** fwdeMatch of the link: "any" only between two groups or more. */
function matchParam(filters, match) {
  return match === 'any' && groupsOf(filters, []).length > 1 ? 'any' : '';
}
/** The API's filterGroups (rates as fractions), null without filters. */
function filterGroupsDefinition(state) {
  if (!state.filters.length) {
    return null;
  }
  return {
    match: state.match,
    groups: groupsOf(state.filters, state.groupMatch).map(group => ({
      match: group.match,
      filters: group.filters.map(f => {
        let {
          value
        } = f;
        if (NUMBER_OPERATORS.includes(f.operator)) {
          // rates are shown and entered in percent, sent as fractions
          value = isRate(f.target) ? Number(f.value) / 100 : Number(f.value);
        }
        return {
          target: f.target,
          operator: f.operator,
          value
        };
      })
    }))
  };
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/pivot.ts
/** How many of the pivot's values become columns (ExplorationDefinition::PIVOT_LIMITS). */
const PIVOT_LIMITS = [3, 5, 10];
const PIVOT_DEFAULT_LIMIT = 5;
/** pivot values times metrics (ExplorationDefinition::MAX_PIVOT_CELLS) */
const MAX_PIVOT_CELLS = 60;
/** dimensions that cannot be the pivot: too many values (they serve as row dimensions) */
const NO_PIVOT = ['date', 'hour'];
/** the pivot that shows all its seven values, in weekday order */
const PIVOT_WEEKDAY = 'weekday';
/** The columns per metric a pivot makes: Weekday its seven days, otherwise its limit. */
function pivotColumns(pivot, limit) {
  if (!pivot) {
    return 0;
  }
  return pivot === PIVOT_WEEKDAY ? 7 : limit;
}
/** The pivot cells of a row: values times metrics (Others and Total not counted). */
function pivotCells(state) {
  return pivotColumns(state.pivot, state.pivotLimit) * state.metrics.length;
}
/** Whether two dimensions can be in one table: action dimensions need an action kind in common. */
function commonActions(a, b) {
  return !a || !b || !a.kinds.length || !b.kinds.length || a.kinds.some(kind => b.kinds.includes(kind));
}
/**
 * The pivot of a link or saved exploration, as the server reads a saved state
 * (SavedStateDefinition::pivot): dropped when its dimension is unknown, a row dimension, Date or
 * Hour, or (Weekday) when its seven days pass the cell limit; a limit other than 3, 5 or 10 is 5,
 * then the largest that keeps the cells within the limit.
 */
function parsePivot(rawPivot, rawLimit, dimensions, metricCount, catalog) {
  const none = {
    pivot: null,
    pivotLimit: PIVOT_DEFAULT_LIMIT
  };
  const id = String(rawPivot || '');
  if (!id || dimensions.includes(id) || NO_PIVOT.includes(id) || !catalog.dimensions.some(d => d.id === id)) {
    return none;
  }
  if (id === PIVOT_WEEKDAY) {
    return 7 * metricCount <= MAX_PIVOT_CELLS ? {
      pivot: id,
      pivotLimit: PIVOT_DEFAULT_LIMIT
    } : none;
  }
  const asked = Number(rawLimit);
  const limit = PIVOT_LIMITS.includes(asked) ? asked : PIVOT_DEFAULT_LIMIT;
  const fitting = [...PIVOT_LIMITS].reverse().find(option => option <= limit && option * metricCount <= MAX_PIVOT_CELLS);
  return fitting ? {
    pivot: id,
    pivotLimit: fitting
  } : none;
}
/**
 * Why a dimension cannot be the pivot (translation key), or null: a row dimension, Date or Hour,
 * an action dimension without actions in common with the row dimensions, a Weekday whose seven
 * days pass the cell limit.
 */
function pivotReasons(state, catalog) {
  const rows = catalog.dimensions.filter(d => state.dimensions.includes(d.id));
  const reasons = {};
  catalog.dimensions.forEach(dimension => {
    let reason = null;
    if (state.dimensions.includes(dimension.id)) {
      reason = 'FeinwerkDataExploration_ReasonAlreadyChosen';
    } else if (NO_PIVOT.includes(dimension.id)) {
      reason = 'FeinwerkDataExploration_ReasonPivotTime';
    } else if (rows.some(row => !commonActions(row, dimension))) {
      reason = 'FeinwerkDataExploration_ReasonNoCommonActions';
    } else if (dimension.id === PIVOT_WEEKDAY && 7 * state.metrics.length > MAX_PIVOT_CELLS) {
      reason = 'FeinwerkDataExploration_ReasonPivotCells';
    }
    reasons[dimension.id] = reason;
  });
  return reasons;
}
/** Why a number of pivot values cannot be chosen (translation key), or null. */
function pivotLimitReasons(state) {
  const reasons = {};
  PIVOT_LIMITS.forEach(limit => {
    reasons[limit] = limit * state.metrics.length > MAX_PIVOT_CELLS ? 'FeinwerkDataExploration_ReasonPivotCells' : null;
  });
  return reasons;
}
/** The pivot of a getExploration result: its total row's fwde_pivot, the rows without it. */
function parsePivotResult(rows) {
  const total = rows.find(row => row.fwde_total) || null;
  const pivot = total ? total.fwde_pivot : undefined;
  return {
    dimension: pivot && pivot.dimension ? String(pivot.dimension) : null,
    values: pivot && Array.isArray(pivot.values) ? pivot.values.map(value => ({
      label: String(value.label),
      value: value.value,
      segment: value.segment || null
    })) : [],
    others: !!(pivot && pivot.others),
    total,
    rows: rows.filter(row => !row.fwde_total)
  };
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/state.ts



// the filter rules live in filterGroups.ts (filter groups, 2026-11-23); the page imports them here

const DEFAULT_DIMENSIONS = ['channelType'];
const DEFAULT_METRICS = ['nb_visits', 'bounce_rate', 'nb_conversions'];
const LIMIT_OPTIONS = [10, 25, 50, 100, 250, 500, 1000];
/** ExplorationDefinition::MAX_METRICS (more metrics, 2026-11-24) */
const MAX_METRICS = 10;
/** row dimensions (ExplorationDefinition::MAX_DIMENSIONS; three since rows and pivot) */
const MAX_DIMENSIONS = 3;
const VIEWS = ['table', 'chart', 'both'];
const CHART_TYPES = ['bar', 'trend', 'donut'];
function knownIds(value, known, max) {
  const ids = [];
  String(value || '').split(',').forEach(id => {
    if (known.includes(id) && !ids.includes(id) && ids.length < max) {
      ids.push(id);
    }
  });
  return ids;
}
/** the all-goals metrics an older link narrowed to one goal with fwdeGoal */
const ALL_GOALS_METRICS = ['nb_conversions', 'conversion_rate', 'revenue'];
/**
 * The hash of a link from before more metrics (2026-11-24) with a goal chosen (fwdeGoal=N): the
 * all-goals metrics become goal N's own metrics wherever the hash names metrics (list, sorting,
 * chart, row filter targets), the same numbers. Unchanged when the website no longer has goal N:
 * then the metrics stay "all goals".
 */
function withGoalMetrics(hash, metricIds) {
  const goal = Number(hash.fwdeGoal);
  if (!Number.isInteger(goal) || goal <= 0 || !metricIds.includes(`goal_${goal}_nb_conversions`)) {
    return hash;
  }
  const map = id => ALL_GOALS_METRICS.includes(id) ? `goal_${goal}_${id}` : id;
  const mapped = Object.assign({}, hash);
  ['fwdeMetrics', 'fwdeSort', 'fwdeChart'].forEach(key => {
    if (hash[key]) {
      mapped[key] = String(hash[key]).split(',').map(map).join(',');
    }
  });
  if (hash.fwdeFilters) {
    // groups by "|", an optional "all:"/"any:" prefix, conditions by ";": "target~operator~value"
    mapped.fwdeFilters = String(hash.fwdeFilters).split('|').map(group => {
      const prefix = (group.match(/^(all|any):/) || [''])[0];
      return prefix + group.substring(prefix.length).split(';').map(condition => {
        const cut = condition.indexOf('~');
        return cut < 0 ? condition : map(condition.substring(0, cut)) + condition.substring(cut);
      }).join(';');
    }).join('|');
  }
  return mapped;
}
/** The count that fits a dimension's actions, e.g. pageviews for page URLs. */
const KIND_METRICS = {
  pageview: 'nb_pageviews',
  event: 'nb_events',
  download: 'nb_downloads',
  outlink: 'nb_outlinks',
  search: 'nb_searches'
};
/**
 * Metrics an exploration starts with when the link names none (e.g. "Open in exploration" of a
 * standard report): the count of the dimension's first action kind and visits; otherwise the
 * general defaults. Metrics the catalog does not have are left out.
 */
function defaultMetricsFor(dimensionId, catalog) {
  const dimension = catalog.dimensions.find(d => d.id === dimensionId);
  const kindMetric = dimension !== null && dimension !== void 0 && dimension.kinds.length ? KIND_METRICS[dimension.kinds[0]] : undefined;
  const ids = kindMetric ? [kindMetric, 'nb_visits'] : DEFAULT_METRICS;
  const metricIds = catalog.metrics.map(m => m.id);
  return ids.filter(id => metricIds.includes(id));
}
/**
 * The exploration state from the hash parameters; invalid parts fall back to valid values.
 */
function parseState(rawHash, catalog, limits) {
  const dimensionIds = catalog.dimensions.map(d => d.id);
  const metricIds = catalog.metrics.map(m => m.id);
  const hash = withGoalMetrics(rawHash, metricIds);
  let dimensions = knownIds(hash.fwdeDims, dimensionIds, MAX_DIMENSIONS);
  if (!dimensions.length) {
    dimensions = knownIds(DEFAULT_DIMENSIONS.join(','), dimensionIds, 2);
    if (!dimensions.length && dimensionIds.length) {
      dimensions = [dimensionIds[0]];
    }
  }
  let metrics = knownIds(hash.fwdeMetrics, metricIds, MAX_METRICS);
  if (!metrics.length) {
    metrics = defaultMetricsFor(dimensions[0] || '', catalog);
  }
  const view = String(hash.fwdeView);
  const limit = Number(hash.fwdeLimit);
  const sortColumns = [...metrics, ...labelColumns(dimensions.length)];
  const sort = String(hash.fwdeSort || '');
  return Object.assign(Object.assign({
    dimensions,
    metrics,
    sort: sortColumns.includes(sort) ? sort : metrics[0],
    sortOrder: hash.fwdeOrder === 'asc' ? 'asc' : 'desc',
    limit: LIMIT_OPTIONS.includes(limit) && limit <= limits.maxRows ? limit : limits.defaultRows
  }, parseFilterParam(hash.fwdeFilters, hash.fwdeMatch, dimensions.length, metrics)), {}, {
    view: VIEWS.includes(view) ? view : null,
    chartMetric: metrics.includes(String(hash.fwdeChart)) ? String(hash.fwdeChart) : null,
    chartType: CHART_TYPES.includes(hash.fwdeChartType) ? hash.fwdeChartType : null
  }, parsePivot(hash.fwdePivot, hash.fwdePivotLimit, dimensions, metrics.length, catalog));
}
/**
 * Every key toHashParams writes: the page reloads when one of them changes (a key missing here
 * would change the link and nothing else).
 */
const STATE_HASH_KEYS = ['fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters', 'fwdeView', 'fwdeChart', 'fwdeChartType', 'fwdeMatch', 'fwdePivot', 'fwdePivotLimit'];
/**
 * All parameters, empty ones too: the hash is merged, so an omitted parameter would keep its old
 * value.
 */
function toHashParams(state) {
  return {
    fwdeDims: state.dimensions.join(','),
    fwdeMetrics: state.metrics.join(','),
    // the older goal choice: always cleared, the goal's own metrics replace it
    fwdeGoal: '',
    fwdeSort: state.sort,
    fwdeOrder: state.sortOrder,
    fwdeLimit: String(state.limit),
    fwdeFilters: filterParam(state.filters, state.groupMatch),
    fwdeView: state.view || '',
    fwdeChart: state.chartMetric || '',
    fwdeChartType: state.chartType || '',
    fwdeMatch: matchParam(state.filters, state.match),
    fwdePivot: state.pivot || '',
    fwdePivotLimit: state.pivot ? String(state.pivotLimit) : ''
  };
}
/**
 * The definition for FeinwerkDataExploration.getExploration. While Matomo compares segments or
 * periods the pivot stays in the state but is not sent (pivot and comparison are exclusive, the
 * comparison wins).
 */
function toDefinition(state, comparing = false) {
  const definition = {
    dimensions: state.dimensions,
    metrics: state.metrics,
    sort: state.sort,
    sortOrder: state.sortOrder,
    limit: state.limit
  };
  const filterGroups = filterGroupsDefinition(state);
  if (filterGroups) {
    definition.filterGroups = filterGroups;
  }
  if (state.pivot && !comparing) {
    definition.pivot = {
      dimension: state.pivot,
      limit: state.pivotLimit
    };
  }
  return definition;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/availability.ts


/**
 * Why a dimension cannot go into a slot (translation key), or null: it is in another slot or the
 * pivot, or it is an action dimension without actions in common with another chosen one.
 */
function dimensionReasons(state, catalog, slot) {
  const otherIds = state.dimensions.filter((_, i) => i !== slot);
  const others = catalog.dimensions.filter(d => otherIds.includes(d.id) || d.id === state.pivot);
  const reasons = {};
  catalog.dimensions.forEach(dimension => {
    let reason = null;
    if (otherIds.includes(dimension.id) || dimension.id === state.pivot) {
      reason = 'FeinwerkDataExploration_ReasonAlreadyChosen';
    } else if (others.some(other => !commonActions(other, dimension))) {
      reason = 'FeinwerkDataExploration_ReasonNoCommonActions';
    }
    reasons[dimension.id] = reason;
  });
  return reasons;
}
/**
 * Why a metric cannot be chosen (translation key), or null. An action metric counts rows of one
 * kind, and every chosen action dimension must be able to have that kind, otherwise the column is
 * always 0.
 */
function metricReasons(state, catalog) {
  const actionDimensions = catalog.dimensions.filter(d => (state.dimensions.includes(d.id) || d.id === state.pivot) && d.kinds.length);
  const columns = pivotColumns(state.pivot, state.pivotLimit);
  const reasons = {};
  catalog.metrics.forEach(metric => {
    let reason = null;
    if (!state.metrics.includes(metric.id) && state.metrics.length >= MAX_METRICS) {
      reason = 'FeinwerkDataExploration_ReasonMaxMetrics';
    } else if (!state.metrics.includes(metric.id) && columns * (state.metrics.length + 1) > MAX_PIVOT_CELLS) {
      // rows and pivot (2026-11-24): pivot values times metrics at most 60
      reason = 'FeinwerkDataExploration_ReasonPivotCells';
    } else if (metric.kind !== null && actionDimensions.some(d => !d.kinds.includes(metric.kind))) {
      reason = 'FeinwerkDataExploration_ReasonMetricNeverMatches';
    }
    reasons[metric.id] = reason;
  });
  return reasons;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=script&lang=ts






/** the label of each slot */
const SLOT_KEYS = ['FeinwerkDataExploration_Dimension', 'FeinwerkDataExploration_SecondDimension', 'FeinwerkDataExploration_ThirdDimension'];
/* harmony default export */ var DimensionPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SearchableList: SearchableList
  },
  props: {
    catalog: {
      type: Object,
      required: true
    },
    state: {
      type: Object,
      required: true
    }
  },
  emits: ['change'],
  data() {
    return {
      openSlot: null,
      SLOT_KEYS
    };
  },
  computed: {
    dimensions() {
      return this.state.dimensions;
    },
    /** the chosen slots and one empty slot after them (rows and pivot: up to three) */
    slots() {
      const count = Math.min(MAX_DIMENSIONS, Math.max(2, this.dimensions.length + 1));
      return Array.from({
        length: count
      }, (_, i) => i);
    }
  },
  methods: {
    /** Escape in the list: close it and give the focus back to its button */
    close(slot) {
      this.openSlot = null;
      this.$nextTick(() => {
        const button = this.$refs[`current${slot}`];
        const element = Array.isArray(button) ? button[0] : button;
        if (element) {
          element.focus();
        }
      });
    },
    translate: external_CoreHome_["translate"],
    currentName(slot) {
      const id = this.dimensions[slot];
      const dimension = this.catalog.dimensions.find(d => d.id === id);
      if (dimension) {
        return dimension.name;
      }
      return Object(external_CoreHome_["translate"])(slot > 0 ? 'FeinwerkDataExploration_NoSecondDimension' : 'FeinwerkDataExploration_ChooseDimension');
    },
    items(slot) {
      const reasons = dimensionReasons(this.state, this.catalog, slot);
      return this.catalog.dimensions.map(d => ({
        key: d.id,
        label: d.name,
        group: GROUP_KEYS[d.group] ? Object(external_CoreHome_["translate"])(GROUP_KEYS[d.group]) : d.group,
        reason: reasons[d.id]
      }));
    },
    choose(slot, id) {
      const dimensions = [...this.dimensions];
      if (id === null) {
        // "None" takes this slot and the ones after it away
        dimensions.splice(slot);
      } else {
        dimensions[slot] = id;
      }
      this.openSlot = null;
      this.$emit('change', dimensions.filter(d => !!d));
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue



DimensionPickervue_type_script_lang_ts.render = DimensionPickervue_type_template_id_87f3a9c4_render

/* harmony default export */ var DimensionPicker = (DimensionPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=template&id=3bad9cc2

const MetricPickervue_type_template_id_3bad9cc2_hoisted_1 = {
  class: "fwde-picker"
};
function MetricPickervue_type_template_id_3bad9cc2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchableList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchableList");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricPickervue_type_template_id_3bad9cc2_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchableList, {
    items: _ctx.items,
    selected: _ctx.state.metrics,
    multiple: true,
    "search-label": _ctx.translate('FeinwerkDataExploration_SearchMetrics'),
    onToggle: _ctx.toggle
  }, null, 8, ["items", "selected", "search-label", "onToggle"])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=template&id=3bad9cc2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=script&lang=ts




const MetricPickervue_type_script_lang_ts_GROUP_KEYS = {
  visits: 'FeinwerkDataExploration_MetricGroupVisits',
  goals: 'FeinwerkDataExploration_MetricGroupGoals',
  ecommerce: 'Goals_Ecommerce',
  actions: 'FeinwerkDataExploration_MetricGroupActions'
};
const SCOPE_GROUPS = {
  visit: 'visits',
  goal: 'goals',
  action: 'actions'
};
/** the list's order (more metrics, 2026-11-24): each goal's own heading follows "Goals" */
const GROUP_ORDER = ['visits', 'goals', 'goal', 'ecommerce', 'actions'];
/** the metrics of all goals together: the list says so */
const MetricPickervue_type_script_lang_ts_ALL_GOALS_METRICS = ['nb_conversions', 'conversion_rate', 'revenue'];
/* harmony default export */ var MetricPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SearchableList: SearchableList
  },
  props: {
    catalog: {
      type: Object,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    }
  },
  emits: ['change'],
  computed: {
    items() {
      const reasons = metricReasons(this.state, this.catalog);
      const entries = this.catalog.metrics.map((m, index) => {
        const group = m.group || SCOPE_GROUPS[m.scope];
        let label = this.actionScope && m.nameInActionScope ? m.nameInActionScope : m.name;
        let heading = Object(external_CoreHome_["translate"])(MetricPickervue_type_script_lang_ts_GROUP_KEYS[group]);
        let rank = GROUP_ORDER.indexOf(group);
        if (m.idGoal) {
          // a goal's metric is named "<goal name>: <column>" (MetricDefinition::label): the goal
          // becomes the heading, the search finds it there too
          const cut = m.name.lastIndexOf(': ');
          heading = Object(external_CoreHome_["translate"])('FeinwerkDataExploration_MetricGroupGoal', m.name.substring(0, cut));
          label = m.name.substring(cut + 2);
          rank = GROUP_ORDER.indexOf('goal');
        } else if (MetricPickervue_type_script_lang_ts_ALL_GOALS_METRICS.includes(m.id)) {
          label = Object(external_CoreHome_["translate"])('FeinwerkDataExploration_MetricAllGoals', label);
        }
        const item = {
          key: m.id,
          label,
          group: heading,
          reason: reasons[m.id],
          description: m.description
        };
        return {
          rank,
          index,
          item
        };
      });
      return entries.sort((a, b) => a.rank - b.rank || a.index - b.index).map(e => e.item);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    toggle(id) {
      const metrics = this.state.metrics.includes(id) ? this.state.metrics.filter(m => m !== id) : [...this.state.metrics, id];
      if (metrics.length) {
        this.$emit('change', metrics);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue



MetricPickervue_type_script_lang_ts.render = MetricPickervue_type_template_id_3bad9cc2_render

/* harmony default export */ var MetricPicker = (MetricPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FilterPanel.vue?vue&type=template&id=734baa9a

const FilterPanelvue_type_template_id_734baa9a_hoisted_1 = {
  type: "submit",
  form: "fwde-filterpanel-form",
  class: "btn fwde-filters__apply"
};
function FilterPanelvue_type_template_id_734baa9a_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_FilterEditor = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FilterEditor");
  const _component_SidePanel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SidePanel");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SidePanel, {
    open: _ctx.open,
    title: _ctx.translate('FeinwerkDataExploration_Filters'),
    "close-label": _ctx.translate('General_Close'),
    "scope-class": "fwde-page fwde-filterpanel",
    "onUpdate:open": _ctx.onPanel
  }, {
    footer: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-button fwde-filters__cancel",
      onClick: _cache[4] || (_cache[4] = (...args) => _ctx.cancel && _ctx.cancel(...args))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", FilterPanelvue_type_template_id_734baa9a_hoisted_1, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Apply')), 1)]),
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
      id: "fwde-filterpanel-form",
      class: "fwde-filterpanel__form",
      novalidate: "",
      onSubmit: _cache[3] || (_cache[3] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.apply && _ctx.apply(...args), ["prevent"]))
    }, [_ctx.open ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FilterEditor, {
      ref: "editor",
      key: _ctx.session,
      catalog: _ctx.catalog,
      state: _ctx.draftState,
      "action-scope": _ctx.actionScope,
      "start-with-row": true,
      onChange: _cache[0] || (_cache[0] = $event => _ctx.draft.filters = $event),
      onMatch: _cache[1] || (_cache[1] = $event => _ctx.draft.match = $event),
      onGroupMatch: _cache[2] || (_cache[2] = $event => _ctx.draft.groupMatch = $event)
    }, null, 8, ["catalog", "state", "action-scope"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 32)]),
    _: 1
  }, 8, ["open", "title", "close-label", "onUpdate:open"]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterPanel.vue?vue&type=template&id=734baa9a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=template&id=6942d0f2

const FilterEditorvue_type_template_id_6942d0f2_hoisted_1 = {
  class: "fwde-filters"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_2 = {
  key: 0,
  class: "fwde-filters__legend"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_3 = {
  class: "fwde-filters__field fwde-filters__field--target"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_4 = {
  class: "fwde-field__label"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_5 = ["value", "onChange"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_6 = ["value"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_7 = {
  class: "fwde-filters__field fwde-filters__field--operator"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_8 = {
  class: "fwde-field__label"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_9 = ["value", "onChange"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_10 = ["value"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_11 = {
  key: 0,
  class: "fwde-filters__field fwde-filters__field--value"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_12 = {
  class: "fwde-field__label"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_13 = ["type", "step", "maxlength", "value", "aria-invalid", "aria-describedby", "list", "onInput", "onFocus"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_14 = ["id"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_15 = ["value"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_16 = ["id"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_17 = {
  key: 2,
  class: "fwde-filters__help"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_18 = ["id"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_19 = ["title", "onClick"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_20 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-close",
  "aria-hidden": "true"
}, null, -1);
const FilterEditorvue_type_template_id_6942d0f2_hoisted_21 = {
  class: "fwde-visually-hidden"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_22 = ["disabled", "title", "onClick"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_23 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add",
  "aria-hidden": "true"
}, null, -1);
const FilterEditorvue_type_template_id_6942d0f2_hoisted_24 = {
  class: "fwde-filters__actions"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_25 = ["disabled", "title"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_26 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add",
  "aria-hidden": "true"
}, null, -1);
const FilterEditorvue_type_template_id_6942d0f2_hoisted_27 = ["disabled", "title"];
const FilterEditorvue_type_template_id_6942d0f2_hoisted_28 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add",
  "aria-hidden": "true"
}, null, -1);
const FilterEditorvue_type_template_id_6942d0f2_hoisted_29 = {
  key: 1,
  class: "fwde-filters__count"
};
const FilterEditorvue_type_template_id_6942d0f2_hoisted_30 = {
  key: 2,
  class: "fwde-filters__limit"
};
function FilterEditorvue_type_template_id_6942d0f2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SegmentedSwitch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentedSwitch");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FilterEditorvue_type_template_id_6942d0f2_hoisted_1, [_ctx.grouped ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SegmentedSwitch, {
    key: 0,
    class: "fwde-filters__groups-match",
    options: _ctx.groupsMatchOptions,
    "model-value": _ctx.outer,
    label: _ctx.translate('FeinwerkDataExploration_FilterGroupsMatch'),
    "onUpdate:modelValue": _ctx.setOuter
  }, null, 8, ["options", "model-value", "label", "onUpdate:modelValue"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.grouped ? 'fieldset' : 'div'), {
      key: group.index,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.grouped ? 'fwde-filters__group' : 'fwde-filters__list')
    }, {
      default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.grouped ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("legend", FilterEditorvue_type_template_id_6942d0f2_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterGroup', group.number)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), group.rows.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SegmentedSwitch, {
        key: 1,
        class: "fwde-filters__match",
        options: _ctx.matchOptions,
        "model-value": _ctx.groupSwitches[group.index] || 'all',
        label: _ctx.translate('FeinwerkDataExploration_FilterMatch'),
        "onUpdate:modelValue": $event => _ctx.setGroupMatch(group.index, $event)
      }, null, 8, ["options", "model-value", "label", "onUpdate:modelValue"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.rows, ({
        row,
        index
      }) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
          key: row.id,
          class: "fwde-filters__row"
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", FilterEditorvue_type_template_id_6942d0f2_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_6942d0f2_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterColumn')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
          class: "browser-default fwde-select",
          value: row.target,
          onChange: $event => _ctx.setTarget(index, $event)
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.targets, target => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
            key: target.key,
            value: target.key
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(target.label), 9, FilterEditorvue_type_template_id_6942d0f2_hoisted_6);
        }), 128))], 40, FilterEditorvue_type_template_id_6942d0f2_hoisted_5)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", FilterEditorvue_type_template_id_6942d0f2_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_6942d0f2_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterCondition')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
          class: "browser-default fwde-select",
          value: row.operator,
          onChange: $event => _ctx.setOperator(index, $event)
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.operatorsFor(row.target), operator => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
            key: operator,
            value: operator
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.OPERATOR_KEYS[operator])), 9, FilterEditorvue_type_template_id_6942d0f2_hoisted_10);
        }), 128))], 40, FilterEditorvue_type_template_id_6942d0f2_hoisted_9)]), !_ctx.VALUELESS_OPERATORS.includes(row.operator) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("label", FilterEditorvue_type_template_id_6942d0f2_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_6942d0f2_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterValue')) + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.unitSuffix(row.target)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
          class: "browser-default fwde-input",
          type: _ctx.isNumberTarget(row.target) ? 'number' : 'text',
          step: _ctx.isNumberTarget(row.target) ? 'any' : undefined,
          maxlength: row.operator === 'matches' ? _ctx.MAX_PATTERN : _ctx.MAX_FILTER_TEXT,
          value: row.value,
          "aria-invalid": row.problem ? 'true' : undefined,
          "aria-describedby": _ctx.describedBy(row),
          list: _ctx.listId(row),
          autocomplete: "off",
          onInput: $event => _ctx.setValue(index, $event),
          onFocus: $event => _ctx.suggest(row)
        }, null, 40, FilterEditorvue_type_template_id_6942d0f2_hoisted_13), _ctx.listId(row) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("datalist", {
          key: 0,
          id: _ctx.listId(row)
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.suggestions[row.id], suggestion => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
            key: suggestion,
            value: suggestion
          }, null, 8, FilterEditorvue_type_template_id_6942d0f2_hoisted_15);
        }), 128))], 8, FilterEditorvue_type_template_id_6942d0f2_hoisted_14)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), row.operator === 'matches' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
          key: 1,
          id: `fwde-filter-help-${row.id}`,
          class: "fwde-filters__help"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterPatternHelp')), 9, FilterEditorvue_type_template_id_6942d0f2_hoisted_16)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.VALUELESS_OPERATORS.includes(row.operator) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", FilterEditorvue_type_template_id_6942d0f2_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterEmptyHelp')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), row.problem ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", {
          key: 3,
          id: `fwde-filter-error-${row.id}`,
          class: "fwde-filters__error"
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.problemText(row)), 9, FilterEditorvue_type_template_id_6942d0f2_hoisted_18)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
          type: "button",
          class: "fwde-icon-button fwde-filters__remove",
          title: _ctx.translate('FeinwerkDataExploration_RemoveFilter'),
          onClick: $event => _ctx.remove(index)
        }, [FilterEditorvue_type_template_id_6942d0f2_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_6942d0f2_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RemoveFilter')), 1)], 8, FilterEditorvue_type_template_id_6942d0f2_hoisted_19)]);
      }), 128)), _ctx.grouped ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 2,
        type: "button",
        class: "fwde-button fwde-filters__add",
        disabled: _ctx.atConditionLimit,
        title: _ctx.atConditionLimit ? _ctx.conditionLimitText : undefined,
        onClick: $event => _ctx.add(group.index)
      }, [FilterEditorvue_type_template_id_6942d0f2_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterAddCondition')), 1)], 8, FilterEditorvue_type_template_id_6942d0f2_hoisted_22)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
      _: 2
    }, 1032, ["class"]);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FilterEditorvue_type_template_id_6942d0f2_hoisted_24, [!_ctx.grouped ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwde-button fwde-filters__add",
    disabled: _ctx.atConditionLimit,
    title: _ctx.atConditionLimit ? _ctx.conditionLimitText : undefined,
    onClick: _cache[0] || (_cache[0] = $event => _ctx.add(_ctx.groups.length ? _ctx.groups[0].index : 0))
  }, [FilterEditorvue_type_template_id_6942d0f2_hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_AddFilter')), 1)], 8, FilterEditorvue_type_template_id_6942d0f2_hoisted_25)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button fwde-filters__add-group",
    disabled: _ctx.atGroupLimit,
    title: _ctx.atGroupLimit ? _ctx.groupLimitText : undefined,
    onClick: _cache[1] || (_cache[1] = (...args) => _ctx.addGroup && _ctx.addGroup(...args))
  }, [FilterEditorvue_type_template_id_6942d0f2_hoisted_28, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterAddGroup')), 1)], 8, FilterEditorvue_type_template_id_6942d0f2_hoisted_27), _ctx.rows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", FilterEditorvue_type_template_id_6942d0f2_hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.countText), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.atGroupLimit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", FilterEditorvue_type_template_id_6942d0f2_hoisted_30, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.groupLimitText), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=template&id=6942d0f2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SegmentedSwitch.vue?vue&type=template&id=3a173bc2

const SegmentedSwitchvue_type_template_id_3a173bc2_hoisted_1 = ["aria-label"];
const SegmentedSwitchvue_type_template_id_3a173bc2_hoisted_2 = ["aria-checked", "tabindex", "disabled", "title", "onClick"];
function SegmentedSwitchvue_type_template_id_3a173bc2_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: "fwde-segmented",
    role: "radiogroup",
    tabindex: "-1",
    "aria-label": _ctx.label,
    onKeydown: _cache[0] || (_cache[0] = (...args) => _ctx.onKey && _ctx.onKey(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.options, (option, i) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: option.value,
      type: "button",
      role: "radio",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-segmented__option", {
        'fwde-segmented__option--active': option.value === _ctx.modelValue,
        'fwde-segmented__option--disabled': !!option.disabled
      }]),
      "aria-checked": option.value === _ctx.modelValue ? 'true' : 'false',
      tabindex: i === _ctx.activeIndex ? 0 : -1,
      disabled: !!option.disabled,
      title: option.title || option.label,
      onClick: $event => _ctx.choose(i)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.label), 11, SegmentedSwitchvue_type_template_id_3a173bc2_hoisted_2);
  }), 128))], 40, SegmentedSwitchvue_type_template_id_3a173bc2_hoisted_1);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SegmentedSwitch.vue?vue&type=template&id=3a173bc2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SegmentedSwitch.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * A radio group, the chosen option filled. Tab reaches the chosen option; the arrow keys (around
 * the ends), Home and End choose another one that is not disabled.
 */
/* harmony default export */ var SegmentedSwitchvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    options: {
      type: Array,
      required: true
    },
    modelValue: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    }
  },
  emits: ['update:modelValue'],
  computed: {
    /** the chosen option, else the first that can be chosen (so Tab still reaches the group) */
    activeIndex() {
      const chosen = this.options.findIndex(o => o.value === this.modelValue);
      if (chosen >= 0) {
        return chosen;
      }
      return Math.max(0, this.options.findIndex(o => !o.disabled));
    }
  },
  methods: {
    choose(index) {
      const option = this.options[index];
      // the chosen one again is no change (an arrow key with every other option disabled)
      if (option && !option.disabled && option.value !== this.modelValue) {
        this.$emit('update:modelValue', option.value);
      }
    },
    /** the next option to choose from index in direction step (1 or -1), around the ends */
    nextEnabled(index, step) {
      const count = this.options.length;
      for (let n = 1; n <= count; n += 1) {
        const i = (index + step * n + count * n) % count;
        if (!this.options[i].disabled) {
          return i;
        }
      }
      return index;
    },
    onKey(event) {
      const forward = this.nextEnabled(this.activeIndex, 1);
      const back = this.nextEnabled(this.activeIndex, -1);
      const moves = {
        ArrowRight: forward,
        ArrowDown: forward,
        ArrowLeft: back,
        ArrowUp: back,
        Home: this.nextEnabled(this.options.length - 1, 1),
        End: this.nextEnabled(0, -1)
      };
      const next = moves[event.key];
      if (next === undefined) {
        return;
      }
      event.preventDefault();
      this.choose(next);
      // from the DOM: Vue does not promise the order of refs collected in a v-for
      const group = event.currentTarget;
      const option = group ? group.querySelectorAll('button')[next] : undefined;
      if (option) {
        option.focus();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SegmentedSwitch.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SegmentedSwitch.vue



SegmentedSwitchvue_type_script_lang_ts.render = SegmentedSwitchvue_type_template_id_3a173bc2_render

/* harmony default export */ var SegmentedSwitch = (SegmentedSwitchvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/filterSummary.ts



/** the condition's text per operator (the filter editor's choices and the summary) */
const OPERATOR_KEYS = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  notContains: 'FeinwerkDataExploration_OperatorNotContains',
  notEquals: 'FeinwerkDataExploration_OperatorNotEquals',
  startsWith: 'FeinwerkDataExploration_OperatorStartsWith',
  endsWith: 'FeinwerkDataExploration_OperatorEndsWith',
  notStartsWith: 'FeinwerkDataExploration_OperatorNotStartsWith',
  notEndsWith: 'FeinwerkDataExploration_OperatorNotEndsWith',
  matches: 'FeinwerkDataExploration_OperatorMatches',
  empty: 'FeinwerkDataExploration_OperatorEmpty',
  notEmpty: 'FeinwerkDataExploration_OperatorNotEmpty',
  gt: 'FeinwerkDataExploration_OperatorGt',
  gte: 'FeinwerkDataExploration_OperatorGte',
  lt: 'FeinwerkDataExploration_OperatorLt',
  lte: 'FeinwerkDataExploration_OperatorLte'
};
/**
 * The columns a filter can test, by the names the table shows: the dimensions ("label",
 * "secondary_label", "tertiary_label"), then the metrics (next to an action dimension by their
 * "with this action" name).
 */
function filterTargets(state, catalog, actionScope) {
  const name = id => {
    var _catalog$dimensions$f;
    return ((_catalog$dimensions$f = catalog.dimensions.find(d => d.id === id)) === null || _catalog$dimensions$f === void 0 ? void 0 : _catalog$dimensions$f.name) || id;
  };
  const targets = state.dimensions.map((id, i) => ({
    key: LABEL_COLUMNS[i],
    label: name(id)
  }));
  state.metrics.forEach(id => {
    const metric = catalog.metrics.find(m => m.id === id);
    const label = metric && actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
    targets.push({
      key: id,
      label
    });
  });
  return targets;
}
/**
 * One line per applied filter for the left column (UI item 4): column, condition and value, a text
 * in quotes, a rate with "%" and the visit duration with "s" as they are entered.
 */
function filterSummary(state, catalog, actionScope, translate) {
  const labels = Object.fromEntries(filterTargets(state, catalog, actionScope).map(t => [t.key, t.label]));
  return state.filters.map(filter => {
    const condition = translate(OPERATOR_KEYS[filter.operator] || filter.operator);
    if (filter.operator === 'empty' || filter.operator === 'notEmpty') {
      return `${labels[filter.target] || filter.target} ${condition}`;
    }
    const isMetric = state.metrics.includes(filter.target);
    let value = isMetric ? String(filter.value) : `"${filter.value}"`;
    if (isRate(filter.target)) {
      value += ' %';
    } else if (isSeconds(filter.target)) {
      value += ' s';
    }
    return `${labels[filter.target] || filter.target} ${condition} ${value}`;
  });
}
/**
 * The applied filters for the left column (filter groups, 2026-11-23): one group reads as before
 * (a line per condition, "Match any" below from two on); from two groups on one line per group,
 * its conditions joined by "and" or "or", and a line "and" / "or" between the groups.
 */
function filterSummaryLines(state, catalog, actionScope, translate) {
  const texts = filterSummary(state, catalog, actionScope, translate);
  const groups = groupsOf(state.filters, state.groupMatch);
  const join = match => translate(match === 'any' ? 'FeinwerkDataExploration_FilterJoinOr' : 'FeinwerkDataExploration_FilterJoinAnd');
  if (groups.length < 2) {
    const lines = texts.map(text => ({
      text,
      join: false
    }));
    if (groups.length === 1 && groups[0].match === 'any' && texts.length > 1) {
      lines.push({
        text: translate('FeinwerkDataExploration_FilterMatchAny'),
        join: true
      });
    }
    return lines;
  }
  const lines = [];
  groups.forEach((group, i) => {
    if (i > 0) {
      lines.push({
        text: join(state.match),
        join: true
      });
    }
    const conditionTexts = group.filters.map(filter => texts[state.filters.indexOf(filter)]);
    lines.push({
      text: conditionTexts.join(` ${join(group.match)} `),
      join: false
    });
  });
  return lines;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/suggestions.ts
function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Suggestions for a filter's value (Peter, 2026-11-25: "felder wo sinnvoll mit
 * autovervollständigen", from Matomo's reports): the server asks the dimension's archived Matomo
 * report (FeinwerkDataExploration.getFilterSuggestions), never the raw data. Here: after a typing
 * pause, from two characters on (the fixed lists from the first, also for an empty field), one
 * request at a time (an outdated one is cancelled), each answer cached for the page. A failing
 * request suggests nothing; the field works as before. In its own module: it needs CoreHome's
 * AjaxHelper only (Matomo 5's spec runner knows no more of CoreHome).
 */
/** the dimensions whose every value the plugin knows (LabelFilterResolver::STATIC_DOMAINS) */
const FIXED_DIMENSIONS = ['weekday', 'hour', 'visitorType', 'visitConverted', 'channelType'];
/** characters typed before a dimension with a Matomo report is asked */
const MIN_CHARS = 2;
/** the typing pause in milliseconds */
const DELAY = 250;
/** the conditions that compare a text: "matches" takes a pattern, "is (not) empty" no value */
const SUGGESTING = ['contains', 'notContains', 'equals', 'notEquals', 'startsWith', 'notStartsWith', 'endsWith', 'notEndsWith'];
function suggestsFor(operator) {
  return SUGGESTING.includes(operator);
}
function loadSuggestions(dimension, text, abort) {
  return external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getFilterSuggestions',
    dimension,
    text
  }, {
    createErrorNotification: false,
    abortController: abort
  });
}
class SuggestionSource {
  constructor(load = loadSuggestions, delay = DELAY) {
    _defineProperty(this, "cache", new Map());
    _defineProperty(this, "timer", 0);
    _defineProperty(this, "pending", null);
    _defineProperty(this, "load", void 0);
    _defineProperty(this, "delay", void 0);
    this.load = load;
    this.delay = delay;
  }
  /** stops a waiting or running request: its answer never comes (another column, condition) */
  cancel() {
    window.clearTimeout(this.timer);
    if (this.pending) {
      this.pending.abort();
      this.pending = null;
    }
  }
  /** answers with the suggestions for the typed text: from the cache at once, else after a pause */
  ask(dimension, text, answer) {
    const typed = text.trim();
    this.cancel();
    if (!FIXED_DIMENSIONS.includes(dimension) && typed.length < MIN_CHARS) {
      answer([]);
      return;
    }
    const key = `${dimension}\u0001${typed.toLowerCase()}`;
    const cached = this.cache.get(key);
    if (cached) {
      answer(cached);
      return;
    }
    this.timer = window.setTimeout(() => {
      const abort = new AbortController();
      this.pending = abort;
      let request;
      try {
        request = this.load(dimension, typed, abort);
      } catch (e) {
        // a loader that throws at once counts as a failed request
        request = Promise.reject(e);
      }
      request.then(values => {
        if (abort.signal.aborted) {
          return;
        }
        const list = Array.isArray(values) ? values.map(String) : [];
        this.cache.set(key, list);
        this.pending = null;
        answer(list);
      }).catch(() => {
        if (!abort.signal.aborted) {
          this.pending = null;
          answer([]);
        }
      });
    }, this.delay);
  }
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=script&lang=ts








const DEBOUNCE_MS = 600;
let lastRowId = 0;
function nextRowId() {
  lastRowId += 1;
  return lastRowId;
}
/* harmony default export */ var FilterEditorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SegmentedSwitch: SegmentedSwitch
  },
  props: {
    catalog: {
      type: Object,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    },
    /** without filters, start with one empty row to fill (the filter panel) */
    startWithRow: {
      type: Boolean,
      default: false
    }
  },
  emits: ['change', 'match', 'groupMatch'],
  data() {
    return {
      rows: [],
      /** each group's switch, by group index */
      groupSwitches: [],
      /** the switch between the groups */
      outer: 'all',
      timer: 0,
      OPERATOR_KEYS: OPERATOR_KEYS,
      VALUELESS_OPERATORS: VALUELESS_OPERATORS,
      /** suggestions for each row's value, by row id (2026-11-25) */
      suggestions: {},
      source: null,
      MAX_FILTERS: MAX_FILTERS,
      MAX_FILTER_TEXT: MAX_FILTER_TEXT,
      MAX_PATTERN: MAX_PATTERN
    };
  },
  created() {
    this.source = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["markRaw"])(new SuggestionSource());
    this.rows = this.state.filters.map(f => Object.assign(Object.assign({}, f), {}, {
      id: nextRowId(),
      touched: true,
      problem: ''
    }));
    this.groupSwitches = (this.state.groupMatch || []).slice();
    this.outer = this.state.match;
    if (this.startWithRow && !this.rows.length) {
      this.add(0);
    }
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  computed: {
    matchOptions() {
      return [{
        value: 'all',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterMatchAll')
      }, {
        value: 'any',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterMatchAny')
      }];
    },
    groupsMatchOptions() {
      return [{
        value: 'all',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterGroupsMatchAll')
      }, {
        value: 'any',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterGroupsMatchAny')
      }];
    },
    targets() {
      return filterTargets(this.state, this.catalog, this.actionScope);
    },
    /** the groups in order, each with its rows and their index in this.rows */
    groups() {
      // no array spread in .vue files: Matomo 5's Jest compiles them with an old tslib
      const indexes = Array.from(new Set(this.rows.map(row => row.group))).sort((a, b) => a - b);
      return indexes.map((group, i) => ({
        index: group,
        number: i + 1,
        rows: this.rows.map((row, index) => ({
          row,
          index
        })).filter(entry => entry.row.group === group)
      }));
    },
    grouped() {
      return this.groups.length > 1;
    },
    atConditionLimit() {
      return this.rows.length >= MAX_FILTERS;
    },
    atGroupLimit() {
      return this.groups.length >= MAX_GROUPS || this.atConditionLimit;
    },
    countText() {
      return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterConditionCount', this.rows.length, MAX_FILTERS);
    },
    conditionLimitText() {
      return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterLimitConditions', MAX_FILTERS);
    },
    groupLimitText() {
      return this.groups.length >= MAX_GROUPS ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FilterLimitGroups', MAX_GROUPS) : this.conditionLimitText;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    isNumberTarget(target) {
      return this.state.metrics.includes(target);
    },
    /** Rates are entered in percent like the table shows them, the visit duration in seconds. */
    unitSuffix(target) {
      if (isRate(target)) {
        return ` (${Object(external_CoreHome_["translate"])('FeinwerkDataExploration_UnitPercent')})`;
      }
      if (isSeconds(target)) {
        return ` (${Object(external_CoreHome_["translate"])('FeinwerkDataExploration_UnitSeconds')})`;
      }
      return '';
    },
    operatorsFor(target) {
      return this.isNumberTarget(target) ? NUMBER_OPERATORS : TEXT_OPERATORS;
    },
    problemText(row) {
      return row.problem === 'FeinwerkDataExploration_FilterPatternTooLong' ? Object(external_CoreHome_["translate"])(row.problem, MAX_PATTERN) : Object(external_CoreHome_["translate"])(row.problem);
    },
    describedBy(row) {
      const ids = [row.operator === 'matches' ? `fwde-filter-help-${row.id}` : '', row.problem ? `fwde-filter-error-${row.id}` : ''].filter(id => id !== '');
      return ids.length ? ids.join(' ') : undefined;
    },
    add(group) {
      if (this.atConditionLimit) {
        return;
      }
      if (this.groupSwitches[group] === undefined) {
        this.groupSwitches[group] = 'all';
      }
      // the new row goes after the last row of its group, so the rows stay in group order
      let at = this.rows.length;
      this.rows.forEach((row, index) => {
        if (row.group === group) {
          at = index + 1;
        }
      });
      this.rows.splice(at, 0, {
        id: nextRowId(),
        target: 'label',
        operator: 'contains',
        value: '',
        group,
        touched: false,
        problem: ''
      });
    },
    addGroup() {
      if (this.atGroupLimit) {
        return;
      }
      const group = this.rows.reduce((max, row) => Math.max(max, row.group + 1), 0);
      this.groupSwitches[group] = 'all';
      this.add(group);
      // the focus goes to the new group's first field
      this.$nextTick(() => {
        const groups = this.$el.querySelectorAll('.fwde-filters__group');
        const last = groups[groups.length - 1];
        const select = last ? last.querySelector('select') : null;
        if (select) {
          select.focus();
        }
      });
    },
    remove(index) {
      const {
        group
      } = this.rows[index];
      this.rows.splice(index, 1);
      if (!this.rows.some(row => row.group === group)) {
        // the group is gone: the later groups move up, their switches with them
        this.rows.forEach(row => {
          if (row.group > group) {
            row.group -= 1;
          }
        });
        this.groupSwitches.splice(group, 1);
      }
      this.emitNow();
      // the focus stays in the editor (the removed button took it along): the next row, the one
      // before, or "Add filter"
      this.$nextTick(() => {
        const root = this.$el;
        const rows = root.querySelectorAll('.fwde-filters__row');
        const row = rows[Math.min(index, rows.length - 1)];
        const target = row ? row.querySelector('select') : root.querySelector('.fwde-filters__add');
        if (target) {
          target.focus();
        }
      });
    },
    setTarget(index, event) {
      const row = this.rows[index];
      row.target = event.target.value;
      row.touched = true;
      row.problem = '';
      if (!this.operatorsFor(row.target).includes(row.operator)) {
        [row.operator] = this.operatorsFor(row.target);
        row.value = '';
      }
      if (this.source) {
        this.source.cancel();
      }
      this.suggestions[row.id] = [];
      this.emitNow();
    },
    setOperator(index, event) {
      this.rows[index].operator = event.target.value;
      if (VALUELESS_OPERATORS.includes(this.rows[index].operator)) {
        // "is empty" and "is not empty" have no value field (2026-11-25)
        this.rows[index].value = '';
      }
      this.suggest(this.rows[index]);
      this.rows[index].touched = true;
      this.rows[index].problem = '';
      this.emitNow();
    },
    /**
     * the datalist of a row whose condition compares a text on a dimension: one id from the start,
     * so the browser opens the list as soon as its options arrive (UI review 2026-11-25, W1)
     */
    listId(row) {
      const onDimension = labelColumns(this.state.dimensions.length).includes(row.target);
      return onDimension && suggestsFor(row.operator) ? `fwde-suggest-${row.id}` : undefined;
    },
    /** asks for suggestions for a row's value: a text condition on a dimension only */
    suggest(row) {
      const index = labelColumns(this.state.dimensions.length).indexOf(row.target);
      if (index < 0 || !suggestsFor(row.operator) || !this.source) {
        if (this.source) {
          this.source.cancel();
        }
        this.suggestions[row.id] = [];
        return;
      }
      this.source.ask(this.state.dimensions[index], row.value, values => {
        this.suggestions[row.id] = values;
      });
    },
    setValue(index, event) {
      const row = this.rows[index];
      row.value = event.target.value;
      this.suggest(row);
      row.touched = true;
      row.problem = '';
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => this.emitNow(), DEBOUNCE_MS);
    },
    setGroupMatch(group, value) {
      this.groupSwitches[group] = value;
      this.emitNow();
    },
    setOuter(value) {
      this.outer = value;
      this.$emit('match', value);
    },
    /**
     * Marks every row the user changed but left without a usable value, says what is missing and
     * moves the focus there (the panel's Apply, UI review 2026-11-23). An untouched empty row (the
     * starter row, or one just added) is no filter yet and passes. A regular expression is checked
     * for its length only: its syntax is the database's, the server checks it (final review,
     * 2026-11-24). Returns true when nothing is missing.
     */
    checkComplete() {
      this.rows.forEach(row => {
        const filter = {
          target: row.target,
          operator: row.operator,
          value: row.value.trim()
        };
        const usable = isValidFilter(filter, this.state.dimensions.length, this.state.metrics);
        const meant = row.touched || row.value.trim() !== '';
        let problem = '';
        if (row.operator === 'matches' && filter.value.length > MAX_PATTERN) {
          problem = 'FeinwerkDataExploration_FilterPatternTooLong';
        } else if (!usable && meant) {
          problem = this.isNumberTarget(row.target) ? 'FeinwerkDataExploration_FilterNeedsNumber' : 'FeinwerkDataExploration_FilterNeedsValue';
        }
        row.problem = problem;
      });
      const first = this.rows.findIndex(row => row.problem !== '');
      if (first < 0) {
        return true;
      }
      this.$nextTick(() => {
        const inputs = this.$el.querySelectorAll('.fwde-filters__field--value input');
        if (inputs[first]) {
          inputs[first].focus();
        }
      });
      return false;
    },
    /** hands over what is typed now, without waiting for the typing pause (the panel's Apply) */
    flush() {
      this.emitNow();
    },
    /**
     * The usable conditions with their groups numbered from 0 in order (a group left without a
     * usable condition drops out), and the switch of each.
     */
    emitNow() {
      window.clearTimeout(this.timer);
      const valid = this.rows.map(row => ({
        target: row.target,
        operator: row.operator,
        value: row.value.trim(),
        group: row.group
      })).filter(f => isValidFilter(f, this.state.dimensions.length, this.state.metrics));
      const kept = Array.from(new Set(valid.map(f => f.group))).sort((a, b) => a - b);
      const filters = valid.map(f => Object.assign(Object.assign({}, f), {}, {
        group: kept.indexOf(f.group)
      }));
      const groupMatch = kept.map(group => this.groupSwitches[group] || 'all');
      if (JSON.stringify(filters) !== JSON.stringify(this.state.filters)) {
        this.$emit('change', filters);
      }
      if (JSON.stringify(groupMatch) !== JSON.stringify(this.state.groupMatch)) {
        this.$emit('groupMatch', groupMatch);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue



FilterEditorvue_type_script_lang_ts.render = FilterEditorvue_type_template_id_6942d0f2_render

/* harmony default export */ var FilterEditor = (FilterEditorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SidePanel.vue?vue&type=template&id=a5f0288c

const SidePanelvue_type_template_id_a5f0288c_hoisted_1 = ["aria-labelledby"];
const SidePanelvue_type_template_id_a5f0288c_hoisted_2 = {
  class: "fwde-sidepanel__header"
};
const SidePanelvue_type_template_id_a5f0288c_hoisted_3 = ["id"];
const SidePanelvue_type_template_id_a5f0288c_hoisted_4 = ["aria-label", "title"];
const SidePanelvue_type_template_id_a5f0288c_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  "aria-hidden": "true"
}, "×", -1);
const SidePanelvue_type_template_id_a5f0288c_hoisted_6 = [SidePanelvue_type_template_id_a5f0288c_hoisted_5];
const SidePanelvue_type_template_id_a5f0288c_hoisted_7 = {
  class: "fwde-sidepanel__body"
};
const SidePanelvue_type_template_id_a5f0288c_hoisted_8 = {
  key: 0,
  class: "fwde-sidepanel__footer"
};
function SidePanelvue_type_template_id_a5f0288c_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Teleport"], {
    to: "body"
  }, [_ctx.open ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-sidepanel__backdrop", _ctx.scopeClass]),
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.requestClose && _ctx.requestClose(...args))
  }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Transition"], {
    name: "fwde-sidepanel"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.open ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", {
      key: 0,
      ref: "panel",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-sidepanel", _ctx.scopeClass]),
      role: "dialog",
      "aria-modal": "true",
      tabindex: "-1",
      "aria-labelledby": _ctx.titleId,
      onKeydown: _cache[2] || (_cache[2] = (...args) => _ctx.onKeydown && _ctx.onKeydown(...args))
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", SidePanelvue_type_template_id_a5f0288c_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", {
      id: _ctx.titleId,
      class: "fwde-sidepanel__title"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 9, SidePanelvue_type_template_id_a5f0288c_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-sidepanel__close",
      "aria-label": _ctx.closeLabel,
      title: _ctx.closeLabel,
      onClick: _cache[1] || (_cache[1] = (...args) => _ctx.requestClose && _ctx.requestClose(...args))
    }, SidePanelvue_type_template_id_a5f0288c_hoisted_6, 8, SidePanelvue_type_template_id_a5f0288c_hoisted_4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SidePanelvue_type_template_id_a5f0288c_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")]), _ctx.$slots.footer ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("footer", SidePanelvue_type_template_id_a5f0288c_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "footer")])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 42, SidePanelvue_type_template_id_a5f0288c_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 3
  })]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SidePanel.vue?vue&type=template&id=a5f0288c

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SidePanel.vue?vue&type=script&lang=ts
/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), ' + 'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
/** the class on <html> that locks the page scroll while any panel is open */
const OPEN_CLASS = 'fwde-sidepanel-open';
let openPanels = 0;
let lastId = 0;
/* harmony default export */ var SidePanelvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    open: {
      type: Boolean,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    /** the close button's label (aria-label and tooltip) */
    closeLabel: {
      type: String,
      required: true
    },
    /** classes for the panel and the backdrop, e.g. the page's scope for its own styles */
    scopeClass: {
      type: String,
      default: ''
    },
    /**
     * where the focus goes when it opens: its first control ("first"), or the dialog itself
     * ("panel") when that control acts on change (UI review 2026-11-26 F5)
     */
    initialFocus: {
      type: String,
      default: 'first'
    }
  },
  emits: ['update:open'],
  data() {
    lastId += 1;
    return {
      titleId: `fwde-sidepanel-title-${lastId}`,
      opener: null,
      locked: false
    };
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.onOpen();
      } else {
        this.onClose();
      }
    }
  },
  mounted() {
    if (this.open) {
      this.onOpen();
    }
  },
  beforeUnmount() {
    if (this.open) {
      this.onClose();
    }
  },
  methods: {
    requestClose() {
      this.$emit('update:open', false);
    },
    onOpen() {
      const active = document.activeElement;
      this.opener = active instanceof HTMLElement && active !== document.body ? active : null;
      this.lock();
      document.addEventListener('keydown', this.onDocumentKeydown, true);
      this.$nextTick(() => {
        const panel = this.$refs.panel;
        const first = this.initialFocus === 'panel' ? undefined : this.focusables(true)[0];
        if (first) {
          first.focus();
        } else if (panel) {
          panel.focus();
        }
      });
    },
    onClose() {
      this.unlock();
      document.removeEventListener('keydown', this.onDocumentKeydown, true);
      const {
        opener
      } = this;
      this.opener = null;
      if (opener && document.contains(opener)) {
        opener.focus();
      }
    },
    lock() {
      if (!this.locked) {
        this.locked = true;
        openPanels += 1;
        document.documentElement.classList.add(OPEN_CLASS);
      }
    },
    unlock() {
      if (this.locked) {
        this.locked = false;
        openPanels = Math.max(0, openPanels - 1);
        if (openPanels === 0) {
          document.documentElement.classList.remove(OPEN_CLASS);
        }
      }
    },
    /** the panel's focusable elements; bodyFirst: those of the body, else all (the close button) */
    focusables(bodyFirst) {
      const panel = this.$refs.panel;
      if (!panel) {
        return [];
      }
      // tab stops only: not a switch's other options (tabindex -1), nothing hidden
      const all = Array.from(panel.querySelectorAll(FOCUSABLE)).filter(el => el.tabIndex >= 0 && !el.closest('[hidden]'));
      if (!bodyFirst) {
        return all;
      }
      const body = all.filter(el => el.closest('.fwde-sidepanel__body'));
      return body.length ? body : all;
    },
    /**
     * Keys while the focus is outside the panel, e.g. on <body> after the focused control was
     * removed (a filter row, a button swapped for its confirmation): Escape still closes, Tab comes
     * back into the panel.
     */
    onDocumentKeydown(event) {
      const panel = this.$refs.panel;
      if (!panel || panel.contains(event.target)) {
        return;
      }
      if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        this.requestClose();
      } else if (event.key === 'Tab') {
        event.preventDefault();
        const items = this.focusables(false);
        const target = event.shiftKey ? items[items.length - 1] : items[0];
        (target || panel).focus();
      }
    },
    onKeydown(event) {
      if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        this.requestClose();
        return;
      }
      if (event.key !== 'Tab') {
        return;
      }
      const items = this.focusables(false);
      if (!items.length) {
        event.preventDefault();
        return;
      }
      const first = items[0];
      const last = items[items.length - 1];
      const active = document.activeElement;
      const panel = this.$refs.panel;
      if (event.shiftKey && (active === first || active === panel)) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SidePanel.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SidePanel.vue



SidePanelvue_type_script_lang_ts.render = SidePanelvue_type_template_id_a5f0288c_render

/* harmony default export */ var SidePanel = (SidePanelvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FilterPanel.vue?vue&type=script&lang=ts




/* harmony default export */ var FilterPanelvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FilterEditor: FilterEditor,
    SidePanel: SidePanel
  },
  props: {
    open: {
      type: Boolean,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    },
    /** the applied exploration: the draft starts from its filters and match */
    state: {
      type: Object,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    }
  },
  emits: ['update:open', 'apply'],
  data() {
    return {
      /** a new editor (rows from the applied filters) every time the panel opens */
      session: 0,
      draft: {
        filters: [],
        match: 'all',
        groupMatch: []
      }
    };
  },
  computed: {
    draftState() {
      return Object.assign(Object.assign({}, this.state), {}, {
        filters: this.draft.filters,
        match: this.draft.match,
        groupMatch: this.draft.groupMatch
      });
    }
  },
  watch: {
    open(isOpen) {
      if (isOpen) {
        this.reset();
      }
    }
  },
  created() {
    if (this.open) {
      this.reset();
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    reset() {
      this.draft = {
        filters: this.state.filters.map(filter => Object.assign({}, filter)),
        match: this.state.match,
        groupMatch: this.state.groupMatch.slice()
      };
      this.session += 1;
    },
    apply() {
      const editor = this.$refs.editor;
      // a filter left half done stays in the panel with what is missing (UI review 2026-11-23)
      if (editor && editor.checkComplete && !editor.checkComplete()) {
        return;
      }
      if (editor && editor.flush) {
        editor.flush();
      }
      // the switch between the groups matters from two groups on
      const groupCount = this.draft.groupMatch.length;
      this.$emit('apply', {
        filters: this.draft.filters,
        groupMatch: this.draft.groupMatch,
        match: groupCount > 1 ? this.draft.match : 'all'
      });
      this.$emit('update:open', false);
    },
    cancel() {
      this.$emit('update:open', false);
    },
    onPanel(isOpen) {
      if (!isOpen) {
        this.cancel();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterPanel.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterPanel.vue



FilterPanelvue_type_script_lang_ts.render = FilterPanelvue_type_template_id_734baa9a_render

/* harmony default export */ var FilterPanel = (FilterPanelvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/viewChips.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * The period and segment the result covers, for the chips in the result header (UI item 5): the
 * first entry of Matomo's segment and period lists, a segment Matomo has no name for (titled
 * unknownTitle, Matomo's "Unknown") by its definition. Nothing while comparing: Matomo's
 * comparison bar names every column then.
 */
function viewChips(segments, periods, unknownTitle) {
  if (segments.length !== 1 || periods.length !== 1) {
    return null;
  }
  const [segment] = segments;
  const segmentTitle = segment.title === unknownTitle && segment.params.segment ? segment.params.segment : segment.title;
  return {
    period: periods[0].title,
    segment: segmentTitle
  };
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/emptyState.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * What an empty result says (UI review 2026-11-23, "say what to do"): with filters set, that no row
 * matches them, with a button that opens the filter panel; without filters, Matomo's own message.
 */
function emptyState(filterCount) {
  return filterCount > 0 ? {
    message: 'FeinwerkDataExploration_NoRowsMatchFilters',
    editFilters: true
  } : {
    message: 'CoreHome_ThereIsNoDataForThisReport',
    editFilters: false
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=template&id=82588702

const ResultTablevue_type_template_id_82588702_hoisted_1 = {
  class: "fwde-table"
};
const ResultTablevue_type_template_id_82588702_hoisted_2 = ["aria-sort"];
const ResultTablevue_type_template_id_82588702_hoisted_3 = ["title", "onClick"];
const ResultTablevue_type_template_id_82588702_hoisted_4 = {
  class: "fwde-table__menu"
};
const ResultTablevue_type_template_id_82588702_hoisted_5 = {
  class: "fwde-visually-hidden"
};
const ResultTablevue_type_template_id_82588702_hoisted_6 = ["title"];
const ResultTablevue_type_template_id_82588702_hoisted_7 = {
  class: "fwde-ellipsis__head"
};
const ResultTablevue_type_template_id_82588702_hoisted_8 = {
  class: "fwde-ellipsis__tail"
};
const ResultTablevue_type_template_id_82588702_hoisted_9 = {
  class: "fwde-table__menu"
};
const ResultTablevue_type_template_id_82588702_hoisted_10 = ["title"];
const ResultTablevue_type_template_id_82588702_hoisted_11 = ["colspan"];
const ResultTablevue_type_template_id_82588702_hoisted_12 = {
  key: 0,
  class: "fwde-table__others-loading"
};
const ResultTablevue_type_template_id_82588702_hoisted_13 = {
  key: 0,
  class: "fwde-table__others-failed"
};
const ResultTablevue_type_template_id_82588702_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
  class: "fwde-table__menu"
}, null, -1);
const ResultTablevue_type_template_id_82588702_hoisted_15 = {
  key: 0,
  class: "fwde-table__overcount"
};
const ResultTablevue_type_template_id_82588702_hoisted_16 = {
  class: "fwde-table__footer"
};
const ResultTablevue_type_template_id_82588702_hoisted_17 = {
  key: 1,
  class: "fwde-table__limit"
};
function ResultTablevue_type_template_id_82588702_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RowMenu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RowMenu");
  const _component_TableScroller = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TableScroller");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ResultTablevue_type_template_id_82588702_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TableScroller, {
    onLayout: _ctx.onLayout
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["dataTable fwde-table__table", {
        'fwde-table__table--natural': _ctx.naturalFirst
      }])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, column => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
        key: column.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          sortable: true,
          columnSorted: _ctx.state.sort === column.key,
          'fwde-table__number': column.isMetric,
          'fwde-table__metric': column.isMetric,
          label: !column.isMetric,
          'fwde-table__dimension': !column.isMetric,
          'fwde-table__dimension-last': column.key === _ctx.lastDimension,
          'fwde-table__dimension--fit': column.key === 'label' && _ctx.firstColumnFits
        }),
        "aria-sort": _ctx.ariaSort(column.key)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "fwde-table__sort",
        title: _ctx.translate('FeinwerkDataExploration_SortBy', column.label),
        onClick: $event => _ctx.$emit('sort', column.key)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(column.label), 1), _ctx.state.sort === column.key ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 0,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`sortIcon ${_ctx.state.sortOrder}`),
        "aria-hidden": "true"
      }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, ResultTablevue_type_template_id_82588702_hoisted_3)], 10, ResultTablevue_type_template_id_82588702_hoisted_2);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", ResultTablevue_type_template_id_82588702_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultTablevue_type_template_id_82588702_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowActions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedRows, (row, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, column => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
          key: column.key
        }, [column.isMetric ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: 0,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            'fwde-table__number': true,
            'fwde-table__metric': true,
            'fwde-cellbar': _ctx.barStyle(column.key, row) !== null
          }),
          style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.barStyle(column.key, row) || undefined)
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(column.key, row[column.key])), 7)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: 1,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            label: true,
            'fwde-table__label': true,
            'fwde-table__dimension': true,
            'fwde-table__dimension-last': column.key === _ctx.lastDimension,
            'fwde-table__dimension--fit': column.key === 'label' && _ctx.firstColumnFits
          }),
          title: String(row[column.key])
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.ellipsisClasses(row[column.key]))
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultTablevue_type_template_id_82588702_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.labelPart(row[column.key], 0)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultTablevue_type_template_id_82588702_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.labelPart(row[column.key], 1)), 1)], 2)], 10, ResultTablevue_type_template_id_82588702_hoisted_6))], 64);
      }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ResultTablevue_type_template_id_82588702_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RowMenu, {
        label: _ctx.rowLabel(row),
        "can-break-down": _ctx.state.dimensions.length < _ctx.MAX_DIMENSIONS,
        "visits-reason": _ctx.visitsReason(row),
        "filter-reason": _ctx.filterReason(row),
        onFilter: $event => _ctx.$emit('row-filter', row),
        onBreakdown: $event => _ctx.$emit('row-breakdown', row),
        onVisits: $event => _ctx.$emit('row-visits', row)
      }, null, 8, ["label", "can-break-down", "visits-reason", "filter-reason", "onFilter", "onBreakdown", "onVisits"])])]);
    }), 128)), _ctx.showOthers ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: 0,
      class: "fwde-table__others",
      title: _ctx.translate('FeinwerkDataExploration_OthersTitle')
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, column => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
        key: column.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          label: true,
          'fwde-table__label': true,
          'fwde-table__dimension': true,
          'fwde-table__dimension-last': column.key === _ctx.lastDimension,
          'fwde-table__dimension--fit': column.key === 'label' && _ctx.firstColumnFits
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(column.key === 'label' ? _ctx.translate('General_Others') : ''), 3);
    }), 128)), _ctx.others && _ctx.othersState === 'done' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricColumns, column => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
        key: column.key,
        class: "fwde-table__number fwde-table__metric"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formatRest(column.key, _ctx.others[column.key])), 1);
    }), 128)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
      key: 1,
      class: "fwde-table__others-action",
      colspan: _ctx.metricColumns.length
    }, [_ctx.othersState === 'loading' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ResultTablevue_type_template_id_82588702_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_LoadingData')), 1)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [_ctx.othersState === 'failed' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ResultTablevue_type_template_id_82588702_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_OthersFailed')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-button fwde-table__others-calculate",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.$emit('others-calculate'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_OthersCalculate')), 1)], 64))], 8, ResultTablevue_type_template_id_82588702_hoisted_11)), ResultTablevue_type_template_id_82588702_hoisted_14], 8, ResultTablevue_type_template_id_82588702_hoisted_10)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 2)]),
    _: 1
  }, 8, ["onLayout"]), _ctx.overcountKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ResultTablevue_type_template_id_82588702_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.overcountKey)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", ResultTablevue_type_template_id_82588702_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.rowCountKey(_ctx.dataRows.length), _ctx.formatNumber(_ctx.dataRows.length))), 1), _ctx.dataRows.length >= _ctx.state.limit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ResultTablevue_type_template_id_82588702_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_LimitReached')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=template&id=82588702

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/format.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * An hour as a time of day: Matomo's hour labels are bare numbers in some languages ("14" in
 * English with a 24-hour clock), which do not read as a time; those become "14:00". Labels such
 * as "2 PM" or "14 Uhr" stay.
 */
function formatHour(label) {
  return /^\d{1,2}$/.test(label) ? `${label.padStart(2, '0')}:00` : label;
}
/**
 * A day (YYYY-MM-DD) in words with its month and year, in the user's language ("May 26, 2026",
 * "26. Mai 2026"); anything else stays (UI review 2026-11-26 F1).
 */
function formatLongDate(value, language) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }
  try {
    return new Intl.DateTimeFormat(language || 'en', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      timeZone: 'UTC'
    }).format(new Date(`${value}T00:00:00Z`));
  } catch (e) {
    return value;
  }
}
/**
 * A day (YYYY-MM-DD) as Matomo's graphs write it, in the user's language: on the axis short
 * ("Tue, Sep 15"), in the tooltip in full ("Tuesday, September 15, 2026"); anything else stays.
 */
function formatDay(value, language, full = false) {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return value;
  }
  const options = full ? {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    timeZone: 'UTC'
  } : {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    timeZone: 'UTC'
  };
  try {
    return new Intl.DateTimeFormat(language || 'en', options).format(new Date(`${value}T00:00:00Z`));
  } catch (e) {
    return value;
  }
}
/** By the metric's unit: rates in percent, money in the website's currency, seconds as time. */
function formatMetric(metricId, value, f) {
  if (isRate(metricId)) {
    return f.percent(Math.round(value * 10000) / 100);
  }
  if (isMoney(metricId)) {
    return f.currency(value);
  }
  if (isSeconds(metricId)) {
    return f.duration(value);
  }
  return f.number(value);
}
/** M:SS below one hour, H:MM:SS from one hour. */
function formatDuration(seconds) {
  const total = Math.max(0, Math.round(seconds));
  const hours = Math.floor(total / 3600);
  const minutes = Math.floor(total % 3600 / 60);
  const secs = String(total % 60).padStart(2, '0');
  return hours > 0 ? `${hours}:${String(minutes).padStart(2, '0')}:${secs}` : `${minutes}:${secs}`;
}
/** The text of the table footer: "1 row", otherwise "%s rows". */
function rowCountKey(count) {
  return count === 1 ? 'FeinwerkDataExploration_RowCountOne' : 'FeinwerkDataExploration_RowCount';
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/sorting.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/** A sorted copy, by the visible label: case-insensitive, numbers in natural order. */
function sortByLabel(rows, column, order, locale) {
  const direction = order === 'asc' ? 1 : -1;
  return [...rows].sort((a, b) => {
    var _a$column, _b$column;
    return direction * String((_a$column = a[column]) !== null && _a$column !== void 0 ? _a$column : '').localeCompare(String((_b$column = b[column]) !== null && _b$column !== void 0 ? _b$column : ''), locale, {
      numeric: true,
      sensitivity: 'base'
    });
  });
}
/**
 * The database sorts dimensions by the stored value (e.g. browser codes). When every row is there,
 * the browser sorts by the label users see.
 */
function shouldSortClientSide(rowCount, limit, sortColumn) {
  return LABEL_COLUMNS.includes(sortColumn) && rowCount < limit;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/RowMenu.vue?vue&type=template&id=3a25e4fc

const RowMenuvue_type_template_id_3a25e4fc_hoisted_1 = {
  ref: "root",
  class: "fwde-rowmenu"
};
const RowMenuvue_type_template_id_3a25e4fc_hoisted_2 = ["aria-expanded", "aria-label", "title"];
const RowMenuvue_type_template_id_3a25e4fc_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-more-verti",
  "aria-hidden": "true"
}, null, -1);
const RowMenuvue_type_template_id_3a25e4fc_hoisted_4 = [RowMenuvue_type_template_id_3a25e4fc_hoisted_3];
const RowMenuvue_type_template_id_3a25e4fc_hoisted_5 = ["disabled", "title"];
const RowMenuvue_type_template_id_3a25e4fc_hoisted_6 = {
  key: 0
};
const RowMenuvue_type_template_id_3a25e4fc_hoisted_7 = ["disabled", "title"];
const RowMenuvue_type_template_id_3a25e4fc_hoisted_8 = {
  key: 1
};
const RowMenuvue_type_template_id_3a25e4fc_hoisted_9 = {
  class: "fwde-rowmenu__reason"
};
const RowMenuvue_type_template_id_3a25e4fc_hoisted_10 = ["disabled", "title"];
const RowMenuvue_type_template_id_3a25e4fc_hoisted_11 = {
  key: 0,
  class: "fwde-rowmenu__reason"
};
function RowMenuvue_type_template_id_3a25e4fc_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", RowMenuvue_type_template_id_3a25e4fc_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    ref: "toggle",
    type: "button",
    class: "fwde-icon-button fwde-rowmenu__toggle",
    "aria-haspopup": "true",
    "aria-expanded": _ctx.open ? 'true' : 'false',
    "aria-label": _ctx.translate('FeinwerkDataExploration_RowMenu', _ctx.label),
    title: _ctx.translate('FeinwerkDataExploration_RowMenu', _ctx.label),
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.toggle && _ctx.toggle(...args))
  }, RowMenuvue_type_template_id_3a25e4fc_hoisted_4, 8, RowMenuvue_type_template_id_3a25e4fc_hoisted_2), _ctx.open ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", {
    key: 0,
    class: "fwde-menu__list fwde-rowmenu__list",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.menuStyle)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-menu__item fwde-rowmenu__filter",
    disabled: !!_ctx.filterReason,
    title: _ctx.filterReason,
    onClick: _cache[1] || (_cache[1] = $event => _ctx.choose('filter'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowFilter')), 9, RowMenuvue_type_template_id_3a25e4fc_hoisted_5)]), _ctx.canBreakDown ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", RowMenuvue_type_template_id_3a25e4fc_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-menu__item fwde-rowmenu__breakdown",
    disabled: !!_ctx.filterReason,
    title: _ctx.filterReason,
    onClick: _cache[2] || (_cache[2] = $event => _ctx.choose('breakdown'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowBreakDown')), 9, RowMenuvue_type_template_id_3a25e4fc_hoisted_7)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.filterReason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", RowMenuvue_type_template_id_3a25e4fc_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", RowMenuvue_type_template_id_3a25e4fc_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.filterReason), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("li", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-menu__item fwde-rowmenu__visits",
    disabled: !!_ctx.visitsReason,
    title: _ctx.visitsReason,
    onClick: _cache[3] || (_cache[3] = $event => _ctx.choose('visits'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowShowVisits')), 9, RowMenuvue_type_template_id_3a25e4fc_hoisted_10), _ctx.visitsReason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", RowMenuvue_type_template_id_3a25e4fc_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.visitsReason), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/RowMenu.vue?vue&type=template&id=3a25e4fc

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/RowMenu.vue?vue&type=script&lang=ts


/* harmony default export */ var RowMenuvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    label: {
      type: String,
      required: true
    },
    canBreakDown: {
      type: Boolean,
      required: true
    },
    /** why the visits cannot be shown, empty when they can */
    visitsReason: {
      type: String,
      default: ''
    },
    /** why the row cannot be filtered to (or broken down), empty when it can */
    filterReason: {
      type: String,
      default: ''
    }
  },
  emits: ['filter', 'breakdown', 'visits'],
  data() {
    return {
      open: false,
      menuStyle: {}
    };
  },
  watch: {
    // listeners only while open: a table has up to 1,000 rows, each with a menu
    open(isOpen) {
      if (isOpen) {
        document.addEventListener('click', this.onDocumentClick);
        document.addEventListener('keydown', this.onKeydown);
        // the menu is placed over the page (the table scrolls sideways and would cut it off)
        window.addEventListener('scroll', this.close, true);
      } else {
        this.removeListeners();
      }
    }
  },
  beforeUnmount() {
    this.removeListeners();
  },
  methods: {
    translate: external_CoreHome_["translate"],
    toggle(event) {
      if (this.open) {
        this.open = false;
        return;
      }
      const box = event.currentTarget.getBoundingClientRect();
      this.menuStyle = {
        position: 'fixed',
        top: `${Math.round(box.bottom + 4)}px`,
        left: `${Math.max(8, Math.round(box.right - 240))}px`,
        right: 'auto'
      };
      this.open = true;
    },
    close() {
      this.open = false;
    },
    removeListeners() {
      document.removeEventListener('click', this.onDocumentClick);
      document.removeEventListener('keydown', this.onKeydown);
      window.removeEventListener('scroll', this.close, true);
    },
    choose(action) {
      this.open = false;
      this.$emit(action);
    },
    onDocumentClick(event) {
      const root = this.$refs.root;
      if (this.open && root && !root.contains(event.target)) {
        this.open = false;
      }
    },
    onKeydown(event) {
      if (event.key === 'Escape') {
        this.open = false;
        const toggle = this.$refs.toggle;
        if (toggle) {
          toggle.focus();
        }
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/RowMenu.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/RowMenu.vue



RowMenuvue_type_script_lang_ts.render = RowMenuvue_type_template_id_3a25e4fc_render

/* harmony default export */ var RowMenu = (RowMenuvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/TableScroller.vue?vue&type=template&id=4d66cb14

function TableScrollervue_type_template_id_4d66cb14_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-table__frame", {
      'fwde-table__frame--more-left': _ctx.edges.left,
      'fwde-table__frame--more-right': _ctx.edges.right
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    ref: "scroller",
    class: "fwde-table__scroller",
    onScrollPassive: _cache[0] || (_cache[0] = (...args) => _ctx.measure && _ctx.measure(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderSlot"])(_ctx.$slots, "default")], 544)], 2);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/TableScroller.vue?vue&type=template&id=4d66cb14

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/scrollEdges.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Which sides of a box hide content, from its scroll position and widths (pixels). A pixel of
 * rounding counts as nothing hidden, so a table that just fits shows no shadow.
 */
function scrollEdges(scrollLeft, clientWidth, scrollWidth) {
  const hidden = scrollWidth - clientWidth;
  if (hidden <= 1) {
    return {
      left: false,
      right: false
    };
  }
  return {
    left: scrollLeft > 1,
    right: scrollLeft < hidden - 1
  };
}
/**
 * Whether a table with two dimensions shows its first one at its natural width (as wide as its
 * longest value, so the second sits right next to it and takes the free width; Peter, 2026-11-23)
 * or lets long values shrink (the look when room is short). A table that does not fit in natural
 * width shrinks; one that shrank tries the natural width again only when its box gets wider, so
 * the two never alternate.
 */
function roomyAfter(roomy, natural, layout, lastWidth) {
  if (layout.overflow && natural) {
    return false;
  }
  if (!roomy && layout.width > lastWidth + 1) {
    return true;
  }
  return roomy;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/TableScroller.vue?vue&type=script&lang=ts


/* harmony default export */ var TableScrollervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  emits: ['layout'],
  data() {
    return {
      edges: {
        left: false,
        right: false
      },
      /** the last layout reported ("overflow|width") */
      reported: '',
      observer: null
    };
  },
  mounted() {
    window.addEventListener('resize', this.measure);
    if (typeof ResizeObserver !== 'undefined') {
      this.observer = new ResizeObserver(() => this.measure());
      const scroller = this.$refs.scroller;
      this.observer.observe(scroller);
      if (scroller.firstElementChild) {
        this.observer.observe(scroller.firstElementChild);
      }
    }
    this.measure();
  },
  updated() {
    this.measure();
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.measure);
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  methods: {
    measure() {
      const scroller = this.$refs.scroller;
      if (!scroller) {
        return;
      }
      const next = scrollEdges(scroller.scrollLeft, scroller.clientWidth, scroller.scrollWidth);
      if (next.left !== this.edges.left || next.right !== this.edges.right) {
        this.edges = next;
      }
      const layout = {
        overflow: scroller.scrollWidth - scroller.clientWidth > 1,
        width: scroller.clientWidth
      };
      const key = `${layout.overflow}|${layout.width}`;
      if (key !== this.reported) {
        this.reported = key;
        this.$emit('layout', layout);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/TableScroller.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/TableScroller.vue



TableScrollervue_type_script_lang_ts.render = TableScrollervue_type_template_id_4d66cb14_render

/* harmony default export */ var TableScroller = (TableScrollervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/rows.ts


/**
 * The value a filter on the row's dimension matches: the stored value the API sends for
 * dimensions filtered on it (the label is URL- and HTML-decoded), else the label.
 */
function filterValue(row, index) {
  const stored = row[STORED_VALUES[index]];
  if (stored !== undefined && stored !== null) {
    return String(stored);
  }
  return String(row[LABEL_COLUMNS[index]]);
}
/** The label columns the row has, in order: one per row dimension. */
function rowLabelIndexes(row, dimensionCount) {
  return LABEL_COLUMNS.map((_, i) => i).filter(i => i === 0 || i < dimensionCount && row[LABEL_COLUMNS[i]] !== undefined);
}
/** the label columns the API marks as without a value ("… not defined") */
function emptyLabels(row) {
  const marked = row.fwde_empty_labels;
  return Array.isArray(marked) ? marked.map(String) : [];
}
/**
 * The row's dimension values as "equals" conditions of the given group; a column without a value
 * as "is empty" (2026-11-25).
 */
function rowConditions(state, row, group) {
  const empty = emptyLabels(row);
  return rowLabelIndexes(row, state.dimensions.length).map(i => empty.includes(LABEL_COLUMNS[i]) ? {
    target: LABEL_COLUMNS[i],
    operator: 'empty',
    value: '',
    group
  } : {
    target: LABEL_COLUMNS[i],
    operator: 'equals',
    value: filterValue(row, i),
    group
  });
}
/**
 * The conditions of one "all" list after "filter to this row": the row's dimension values as
 * "equals" conditions, in place of conditions on the same columns; the oldest give way at the
 * limit.
 */
function filtersForRow(state, row) {
  const added = rowConditions(state, row, 0);
  const targets = added.map(f => f.target);
  const kept = state.filters.filter(f => !targets.includes(f.target)).map(f => Object.assign(Object.assign({}, f), {}, {
    group: 0
  }));
  return [...kept, ...added].slice(-MAX_FILTERS);
}
/**
 * The filters after "filter to this value" or "break down" (filter groups, 2026-11-23): one "all"
 * list (or one condition) takes the row in (filtersForRow); with groups joined by "all", or one
 * "any" group, the row becomes a group of its own: "(A or B) and the row"; with groups joined by
 * "any", or when a new group would pass the limits, the row's conditions replace the filters
 * (another group would widen the table).
 */
function rowFilterState(state, row) {
  const groups = groupsOf(state.filters, state.groupMatch);
  const oneList = groups.length === 1 && (groups[0].match === 'all' || groups[0].filters.length < 2);
  if (groups.length === 0 || oneList) {
    return {
      filters: filtersForRow(state, row),
      groupMatch: ['all'],
      match: 'all'
    };
  }
  const added = rowConditions(state, row, groups.length);
  const fits = groups.length < MAX_GROUPS && state.filters.length + added.length <= MAX_FILTERS;
  if (state.match === 'all' && fits) {
    // the groups renumbered in order, the row's group after them
    const filters = groups.flatMap((group, i) => group.filters.map(f => Object.assign(Object.assign({}, f), {}, {
      group: i
    })));
    return {
      filters: [...filters, ...added],
      groupMatch: [...groups.map(group => group.match), 'all'],
      match: 'all'
    };
  }
  return {
    filters: rowConditions(state, row, 0),
    groupMatch: ['all'],
    match: 'all'
  };
}
/**
 * Whether "filter to this value" can select the row: not a value longer than a filter takes. A row
 * without a value ("… not defined") is selected by "is empty" on its empty columns (2026-11-25);
 * without the API's list of those columns (an older answer) it cannot.
 */
function canFilterTo(row) {
  const empty = emptyLabels(row);
  if (row.fwde_no_value && empty.length === 0) {
    return false;
  }
  const values = rowLabelIndexes(row, LABEL_COLUMNS.length).filter(i => !empty.includes(LABEL_COLUMNS[i])).map(i => filterValue(row, i));
  return values.every(value => value.length <= MAX_FILTER_TEXT);
}
/** The visits of a row within the page's segment, or null when the row has no segment. */
function combinedSegment(pageSegment, rowSegment) {
  if (!rowSegment) {
    return null;
  }
  return pageSegment ? `${pageSegment};${rowSegment}` : rowSegment;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/cellBars.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
function positive(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }
  const number = Number(value);
  return Number.isFinite(number) && number > 0 ? number : null;
}
/**
 * The largest value of the given columns in the data rows (the total row left out): what a cell
 * bar is measured against. 0 when there is none.
 */
function barMaximum(rows, keys) {
  let maximum = 0;
  rows.forEach(row => {
    if (row.fwde_total) {
      return;
    }
    keys.forEach(key => {
      const value = positive(row[key]);
      if (value !== null && value > maximum) {
        maximum = value;
      }
    });
  });
  return maximum;
}
/** The width of a cell's bar in percent, one decimal; null for no bar (no value, 0 or less). */
function cellBarWidth(value, maximum) {
  const number = positive(value);
  if (number === null || maximum <= 0) {
    return null;
  }
  return Math.round(Math.min(100, number / maximum * 100) * 10) / 10;
}
let segmenter;
/** The label's characters as a reader sees them: an emoji or a flag is one character. */
function characters(label) {
  if (segmenter === undefined) {
    const {
      Segmenter
    } = Intl;
    segmenter = typeof Segmenter === 'function' ? new Segmenter(undefined, {
      granularity: 'grapheme'
    }) : null;
  }
  if (!segmenter) {
    return Array.from(label); // code points: browsers without Intl.Segmenter
  }
  return Array.from(segmenter.segment(label), part => part.segment);
}
/**
 * A label split for shortening in the middle: the head shrinks with an ellipsis, the tail keeps
 * the last characters (the end of a URL). Short labels stay whole.
 */
function headAndTail(label, tail = 14) {
  if (label.length <= tail + 6) {
    return [label, ''];
  }
  const chars = characters(label);
  if (chars.length <= tail + 6) {
    return [label, ''];
  }
  // a space at the split would vanish at the end or start of a line: keep it as a no-break space
  return [chars.slice(0, chars.length - tail).join('').replace(/ $/, '\u00a0'), chars.slice(chars.length - tail).join('').replace(/^ /, '\u00a0')];
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/additivity.ts

/**
 * Whether the rows of a table add up to its total (design 2026-11-20): every counted unit of
 * every chosen metric belongs to exactly one row. Only then an "Others" row and a donut are exact;
 * otherwise the table shows a hint. The same rule as Engine/Additivity.php.
 */
/** what a distinct count counts (it can fall into several rows whatever the dimensions) */
const DISTINCT_UNIT = {
  nb_uniq_visitors: 'visitor',
  nb_uniq_pageviews: 'visit'
};
/** The unit that can count in several rows, null when the rows add up. */
function overcountUnit(state, catalog) {
  const distinct = state.metrics.find(id => {
    var _metricMeta;
    return ((_metricMeta = metricMeta(id)) === null || _metricMeta === void 0 ? void 0 : _metricMeta.aggregation) === 'distinct';
  });
  if (distinct) {
    return DISTINCT_UNIT[distinct] || 'visit';
  }
  const dimensions = state.dimensions.map(id => catalog.dimensions.find(d => d.id === id));
  const metrics = state.metrics.map(id => catalog.metrics.find(m => m.id === id));
  const actionTable = dimensions.some(d => (d === null || d === void 0 ? void 0 : d.scope) === 'action') || metrics.some(m => (m === null || m === void 0 ? void 0 : m.scope) === 'action');
  // the time dimensions read the action time in an action table, so a visit can span several
  const actionDimension = dimensions.some(d => (d === null || d === void 0 ? void 0 : d.scope) === 'action' || actionTable && (d === null || d === void 0 ? void 0 : d.group) === 'time');
  if (!actionDimension) {
    return null;
  }
  return metrics.some(m => m && m.scope !== 'action') ? 'visit' : null;
}
function addsUp(state, catalog) {
  return overcountUnit(state, catalog) === null;
}
/**
 * Whether a count of the table can add up to more than its Total across the pivot's values (rows
 * and pivot, 2026-11-24): a distinct count, or a visit count under a pivot that a visit can have
 * several values of (an action dimension, or a time dimension read at the action time). Rates and
 * averages never add up and need no hint. The same rule as Additivity::addsUpAcross.
 */
function pivotOvercounts(state, catalog) {
  const pivot = catalog.dimensions.find(d => d.id === state.pivot);
  if (!pivot) {
    return false;
  }
  const dimensions = state.dimensions.map(id => catalog.dimensions.find(d => d.id === id));
  const metrics = state.metrics.map(id => catalog.metrics.find(m => m.id === id));
  const actionTable = pivot.scope === 'action' || dimensions.some(d => (d === null || d === void 0 ? void 0 : d.scope) === 'action') || metrics.some(m => (m === null || m === void 0 ? void 0 : m.scope) === 'action');
  const perAction = pivot.scope === 'action' || actionTable && pivot.group === 'time';
  return state.metrics.some(id => {
    const meta = metricMeta(id);
    if ((meta === null || meta === void 0 ? void 0 : meta.aggregation) === 'distinct') {
      return true;
    }
    if (meta && meta.aggregation !== 'sum') {
      return false;
    }
    const metric = catalog.metrics.find(m => m.id === id);
    return perAction && !!metric && metric.scope !== 'action';
  });
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=script&lang=ts













/* harmony default export */ var ResultTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    RowMenu: RowMenu,
    TableScroller: TableScroller
  },
  props: {
    rows: {
      type: Array,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    },
    currencySymbol: {
      type: String,
      required: true
    },
    /** Matomo's visits log is on for this site */
    visitsLogEnabled: {
      type: Boolean,
      default: false
    },
    /** rows were cut (row limit reached or a number filter): the exact rest can be asked for */
    cut: {
      type: Boolean,
      default: false
    },
    /** the exact rest by metric id ("Others", on demand), null until calculated */
    others: {
      type: Object,
      default: null
    },
    othersState: {
      type: String,
      default: 'idle'
    }
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits', 'others-calculate'],
  data() {
    return {
      MAX_DIMENSIONS: MAX_DIMENSIONS,
      /** two dimensions: the first at its natural width while the table fits (Peter, 2026-11-23) */
      roomy: true,
      /** the scroller's last width; a shrunk table tries again only when it widens */
      boxWidth: 0
    };
  },
  computed: {
    /** "Others" only below a cut table whose rows add up (design 2026-11-20) */
    showOthers() {
      // the server may find no rest after all (a final answer without values): then no row
      const noRest = this.othersState === 'done' && !this.others;
      return this.cut && !noRest && addsUp(this.state, this.catalog);
    },
    dimensionColumns() {
      return this.columns.filter(column => !column.isMetric);
    },
    metricColumns() {
      return this.columns.filter(column => column.isMetric);
    },
    /** why the rows can add up to more than the total (design 2026-11-20), '' when they add up */
    overcountKey() {
      const unit = overcountUnit(this.state, this.catalog);
      if (!unit) {
        return '';
      }
      return unit === 'visitor' ? 'FeinwerkDataExploration_OvercountHintVisitor' : 'FeinwerkDataExploration_OvercountHintVisit';
    },
    columns() {
      const dimensionName = id => {
        var _this$catalog$dimensi;
        return ((_this$catalog$dimensi = this.catalog.dimensions.find(d => d.id === id)) === null || _this$catalog$dimensi === void 0 ? void 0 : _this$catalog$dimensi.name) || id;
      };
      const columns = this.state.dimensions.map((id, i) => ({
        key: LABEL_COLUMNS[i],
        label: dimensionName(id),
        isMetric: false
      }));
      this.state.metrics.forEach(id => {
        const metric = this.catalog.metrics.find(m => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
        columns.push({
          key: id,
          label,
          isMetric: true
        });
      });
      return columns;
    },
    /**
     * With two dimensions and only short values in the first one, the first column is only as
     * wide as its values: the free width goes behind the second dimension, so both dimensions stay
     * together on the left (Peter, 2026-11-17). A first dimension with long values (page URLs)
     * keeps the free width, it needs it.
     */
    firstDimensionFits() {
      return this.state.dimensions.length > 1 && this.rows.every(row => headAndTail(String(row.label))[1] === '');
    },
    /**
     * the first of two dimension columns is only as wide as its values: always for short values,
     * for long ones while the table has room; the second dimension then takes the free width
     */
    firstColumnFits() {
      return this.state.dimensions.length > 1 && (this.firstDimensionFits || this.roomy);
    },
    /** long first values shown on one line at their natural width (up to the usual 32em) */
    naturalFirst() {
      return this.state.dimensions.length > 1 && !this.firstDimensionFits && this.roomy;
    },
    /** the key of the last dimension column: a line separates the dimensions from the metrics */
    lastDimension() {
      return LABEL_COLUMNS[this.state.dimensions.length - 1];
    },
    /** what each count's bar is measured against: the column's largest value (not the sorting) */
    barMaxima() {
      const maxima = {};
      this.state.metrics.filter(id => hasShare(id)).forEach(id => {
        maxima[id] = barMaximum(this.dataRows, [id]);
      });
      return maxima;
    },
    /** the rows without a total row: one stays from a comparison until the new result arrives */
    dataRows() {
      return this.rows.filter(row => !row.fwde_total);
    },
    sortedRows() {
      if (!shouldSortClientSide(this.dataRows.length, this.state.limit, this.state.sort)) {
        return this.dataRows;
      }
      return sortByLabel(this.dataRows, this.state.sort, this.state.sortOrder, external_CoreHome_["Matomo"].language);
    },
    formatters() {
      return {
        number: v => external_CoreHome_["NumberFormatter"].formatNumber(v, 2, 0),
        percent: v => external_CoreHome_["NumberFormatter"].formatPercent(v, 2, 0),
        currency: v => external_CoreHome_["NumberFormatter"].formatCurrency(v, this.currencySymbol, 2, 2),
        duration: v => formatDuration(v)
      };
    }
  },
  watch: {
    // a new result tries the natural width again
    rows() {
      this.roomy = true;
    }
  },
  methods: {
    onLayout(layout) {
      this.roomy = roomyAfter(this.roomy, this.naturalFirst, layout, this.boxWidth);
      this.boxWidth = layout.width;
    },
    /** head (0) or tail (1) of a dimension value, shortened in the middle by the stylesheet */
    labelPart(value, part) {
      return headAndTail(String(value))[part];
    },
    /** a long value (with a tail) may shrink when the table runs out of room; a short one never */
    ellipsisClasses(value) {
      return {
        'fwde-ellipsis': true,
        'fwde-ellipsis--long': headAndTail(String(value))[1] !== ''
      };
    },
    /** the cell's bar behind a count or sum; null for rates, averages and empty values */
    barStyle(metricId, row) {
      if (!(metricId in this.barMaxima)) {
        return null;
      }
      const width = cellBarWidth(row[metricId], this.barMaxima[metricId]);
      return width === null ? null : {
        '--fwde-bar': `${width}%`
      };
    },
    rowLabel(row) {
      return LABEL_COLUMNS.filter(column => row[column] !== undefined).map(column => String(row[column])).join(' · ');
    },
    filterReason(row) {
      return canFilterTo(row) ? '' : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoFilterForRow');
    },
    visitsReason(row) {
      if (!this.visitsLogEnabled) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_VisitsLogDisabled');
      }
      return row.segment ? '' : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoVisitsForRow');
    },
    translate: external_CoreHome_["translate"],
    rowCountKey: rowCountKey,
    formatNumber(value) {
      return external_CoreHome_["NumberFormatter"].formatNumber(value, 0, 0);
    },
    format(metricId, value) {
      return formatMetric(metricId, Number(value), this.formatters);
    },
    /** a rate or average of an empty rest has no value: an empty cell, not "0 %" */
    formatRest(metricId, value) {
      return value === null || value === undefined ? '' : this.format(metricId, value);
    },
    ariaSort(key) {
      if (this.state.sort !== key) {
        return 'none';
      }
      return this.state.sortOrder === 'asc' ? 'ascending' : 'descending';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue



ResultTablevue_type_script_lang_ts.render = ResultTablevue_type_template_id_82588702_render

/* harmony default export */ var ResultTable = (ResultTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/CompareTable.vue?vue&type=template&id=955e3b2c

const CompareTablevue_type_template_id_955e3b2c_hoisted_1 = {
  class: "fwde-table fwde-compare"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_2 = ["colspan", "aria-sort"];
const CompareTablevue_type_template_id_955e3b2c_hoisted_3 = ["title", "onClick"];
const CompareTablevue_type_template_id_955e3b2c_hoisted_4 = {
  class: "fwde-table__menu",
  rowspan: "2"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_5 = {
  class: "fwde-visually-hidden"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_6 = ["title"];
const CompareTablevue_type_template_id_955e3b2c_hoisted_7 = ["data-name"];
const CompareTablevue_type_template_id_955e3b2c_hoisted_8 = {
  class: "fwde-compare__segment"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_9 = {
  key: 0,
  class: "fwde-compare__period"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_10 = {
  key: 0,
  class: "fwde-compare__total"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_11 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
  class: "fwde-table__menu"
}, null, -1);
const CompareTablevue_type_template_id_955e3b2c_hoisted_12 = ["title"];
const CompareTablevue_type_template_id_955e3b2c_hoisted_13 = {
  class: "fwde-ellipsis__head"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_14 = {
  class: "fwde-ellipsis__tail"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_15 = {
  class: "fwde-compare__value"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_16 = {
  key: 0,
  class: "fwde-compare__extra"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_17 = {
  class: "fwde-table__menu"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_18 = {
  key: 0,
  class: "fwde-table__overcount"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_19 = {
  class: "fwde-table__footer"
};
const CompareTablevue_type_template_id_955e3b2c_hoisted_20 = {
  key: 1,
  class: "fwde-table__limit"
};
function CompareTablevue_type_template_id_955e3b2c_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RowMenu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RowMenu");
  const _component_TableScroller = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TableScroller");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", CompareTablevue_type_template_id_955e3b2c_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TableScroller, {
    onLayout: _ctx.onLayout
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["dataTable fwde-table__table fwde-compare__table", {
        'fwde-table__table--natural': _ctx.naturalFirst
      }])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, dimension => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
        key: dimension.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          label: true,
          'fwde-table__dimension': true,
          'fwde-table__dimension-last': dimension.key === _ctx.lastDimension,
          'fwde-table__dimension--fit': dimension.key === 'label' && _ctx.firstColumnFits
        }),
        rowspan: "2"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dimension.label), 3);
    }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricColumns, metric => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
        key: metric.key,
        colspan: _ctx.columns.length,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          'fwde-compare__group': true,
          sortable: true,
          columnSorted: _ctx.state.sort === metric.key
        }),
        "aria-sort": _ctx.ariaSort(metric.key)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "fwde-table__sort",
        title: _ctx.translate('FeinwerkDataExploration_SortBy', metric.label),
        onClick: $event => _ctx.$emit('sort', metric.key)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.label), 1), _ctx.state.sort === metric.key ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 0,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`sortIcon ${_ctx.state.sortOrder}`),
        "aria-hidden": "true"
      }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, CompareTablevue_type_template_id_955e3b2c_hoisted_3)], 10, CompareTablevue_type_template_id_955e3b2c_hoisted_2);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", CompareTablevue_type_template_id_955e3b2c_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowActions')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricColumns, metric => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: metric.key
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, (column, n) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
          key: `${metric.key}-${n}`,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            'fwde-table__number': true,
            'fwde-compare__column': true,
            'fwde-compare__group-start': n === 0
          }),
          title: column.title
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.dotClasses(n)),
          "data-name": _ctx.dotName(n),
          "aria-hidden": "true"
        }, null, 10, CompareTablevue_type_template_id_955e3b2c_hoisted_7), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(column.segmentTitle || column.title), 1), column.periodTitle ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(column.periodTitle), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, CompareTablevue_type_template_id_955e3b2c_hoisted_6);
      }), 128))], 64);
    }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.total ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", CompareTablevue_type_template_id_955e3b2c_hoisted_10, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, dimension => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
        key: dimension.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          label: true,
          'fwde-table__label': true,
          'fwde-table__dimension': true,
          'fwde-table__dimension-last': dimension.key === _ctx.lastDimension,
          'fwde-table__dimension--fit': dimension.key === 'label' && _ctx.firstColumnFits
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dimension.key === 'label' ? _ctx.total.label : ''), 3);
    }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricColumns, metric => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: metric.key
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, (column, n) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: `${metric.key}-${n}`,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            'fwde-table__number': true,
            'fwde-compare__cell': true,
            'fwde-compare__group-start': n === 0
          })
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.columnErrors[n] ? '' : _ctx.value(_ctx.total, metric.key, n)), 3);
      }), 128))], 64);
    }), 128)), CompareTablevue_type_template_id_955e3b2c_hoisted_11])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedRows, (row, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, dimension => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: dimension.key,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            label: true,
            'fwde-table__label': true,
            'fwde-table__dimension': true,
            'fwde-table__dimension-last': dimension.key === _ctx.lastDimension,
            'fwde-table__dimension--fit': dimension.key === 'label' && _ctx.firstColumnFits
          }),
          title: String(row[dimension.key])
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.ellipsisClasses(row[dimension.key]))
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.labelPart(row[dimension.key], 0)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.labelPart(row[dimension.key], 1)), 1)], 2)], 10, CompareTablevue_type_template_id_955e3b2c_hoisted_12);
      }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricColumns, metric => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
          key: metric.key
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, (column, n) => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
            key: `${metric.key}-${n}`,
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
              'fwde-table__number': true,
              'fwde-compare__cell': true,
              'fwde-compare__group-start': n === 0,
              'fwde-cellbar': _ctx.barStyle(row, metric.key, n) !== null
            }),
            style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.barStyle(row, metric.key, n) || undefined)
          }, [!_ctx.columnErrors[n] ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
            key: 0
          }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.value(row, metric.key, n)), 1), _ctx.extra(row, metric.key, column, n) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", CompareTablevue_type_template_id_955e3b2c_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.extra(row, metric.key, column, n)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 6);
        }), 128))], 64);
      }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", CompareTablevue_type_template_id_955e3b2c_hoisted_17, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RowMenu, {
        label: _ctx.rowLabel(row),
        "can-break-down": _ctx.state.dimensions.length < _ctx.MAX_DIMENSIONS,
        "visits-reason": _ctx.visitsReason(row),
        "filter-reason": _ctx.filterReason(row),
        onFilter: $event => _ctx.$emit('row-filter', row),
        onBreakdown: $event => _ctx.$emit('row-breakdown', row),
        onVisits: $event => _ctx.$emit('row-visits', row)
      }, null, 8, ["label", "can-break-down", "visits-reason", "filter-reason", "onFilter", "onBreakdown", "onVisits"])])]);
    }), 128))])], 2)]),
    _: 1
  }, 8, ["onLayout"]), _ctx.overcountKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", CompareTablevue_type_template_id_955e3b2c_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.overcountKey)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", CompareTablevue_type_template_id_955e3b2c_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.rowCountKey(_ctx.dataRows.length), _ctx.formatNumber(_ctx.dataRows.length))), 1), _ctx.dataRows.length >= _ctx.state.limit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", CompareTablevue_type_template_id_955e3b2c_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_LimitReached')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/CompareTable.vue?vue&type=template&id=955e3b2c

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/comparison.ts
/**
 * The table's columns in Matomo's order (periods outer, segments inner), at most maxColumns; cut
 * tells the page to show the limit hint. A segment Matomo has no name for (its title is
 * unknownTitle, Matomo's "Unknown") is titled by its definition, as the export names it.
 */
function comparisonColumns(segments, periods, maxColumns, unknownTitle = '') {
  if (!segments.length || !periods.length) {
    return {
      columns: [],
      cut: false
    };
  }
  const all = [];
  const segmentTitle = segment => unknownTitle !== '' && segment.title === unknownTitle && segment.params.segment ? segment.params.segment : segment.title;
  periods.forEach((period, p) => {
    segments.forEach((segment, s) => {
      let kind = 'both';
      let title = segmentTitle(segment);
      if (p === 0 && s === 0) {
        kind = 'own';
        title = periods.length > 1 ? `${title}, ${period.title}` : title;
      } else if (p === 0) {
        kind = 'segment';
      } else if (s === 0) {
        kind = 'period';
        title = period.title;
      } else {
        title = `${title}, ${period.title}`;
      }
      all.push({
        segment: segment.params.segment || '',
        period: period.params.period || '',
        date: period.params.date || '',
        title,
        segmentTitle: segmentTitle(segment),
        periodTitle: periods.length > 1 ? period.title : '',
        kind
      });
    });
  });
  return {
    columns: all.slice(0, maxColumns),
    cut: all.length > maxColumns
  };
}
function isComparing(columns) {
  return columns.length > 1;
}
/**
 * A column's name in the charts (legend, lines, tooltips), the same for every column: "segment,
 * period" while periods are compared, else the segment; both from Matomo's comparison list, as
 * the table's column titles (Peter, 2026-11-17: the labels were mixed, and a segment showed once
 * by its name, once by its definition).
 */
function columnLabel(column) {
  const segment = column.segmentTitle || column.title;
  return column.periodTitle ? `${segment}, ${column.periodTitle}` : segment;
}
function valueKey(metric, n) {
  return n === 0 ? metric : `${metric}__c${n}`;
}
function shareKey(metric, n) {
  return `${valueKey(metric, n)}__share`;
}
function changeKey(metric, n) {
  return `${valueKey(metric, n)}__change`;
}
/** The total row, or the row's keys from the API (labels are not unique, e.g. "Unknown"). */
function rowKey(row) {
  var _row$secondary_label, _row$tertiary_label;
  if (row.fwde_total) {
    return '\u0000total';
  }
  return typeof row.fwde_key === 'string' ? row.fwde_key : `${row.label}\u0000${(_row$secondary_label = row.secondary_label) !== null && _row$secondary_label !== void 0 ? _row$secondary_label : ''}\u0000${(_row$tertiary_label = row.tertiary_label) !== null && _row$tertiary_label !== void 0 ? _row$tertiary_label : ''}`;
}
/**
 * The rows with the response's first comparison column (…__c1) as column n, matched by the rows'
 * keys and the total row; the given rows stay unchanged.
 */
function mergeColumn(rows, response, n) {
  const byKey = new Map(response.map(row => [rowKey(row), row]));
  return rows.map(row => {
    const from = byKey.get(rowKey(row));
    const merged = Object.assign({}, row);
    if (from) {
      Object.keys(from).forEach(key => {
        const match = key.match(/^(.+)__c1(__share|__change)?$/);
        if (match) {
          merged[`${match[1]}__c${n}${match[2] || ''}`] = from[key];
        }
      });
    }
    return merged;
  });
}
/**
 * The comparison columns still to request after the first response (own view and totals): none
 * when the own view has no rows.
 */
function remainingColumns(rows, columns) {
  if (!rows.some(row => !row.fwde_total)) {
    return [];
  }
  return columns.map((c, n) => n).filter(n => n > 0);
}
/** The rows without comparison columns: the first request asks for the own view as its column 1. */
function withoutComparisonColumns(rows) {
  return rows.map(row => {
    const own = {};
    Object.keys(row).forEach(key => {
      if (!/__c\d+(__share|__change)?$/.test(key)) {
        own[key] = row[key];
      }
    });
    return own;
  });
}
/**
 * Requests the comparison columns one after another; stops as soon as a newer setup started
 * (latest() false), so outdated full-size queries are not asked for. A failing column is reported
 * and the next one is still asked for.
 */
function requestColumns(indices, request, latest, onColumn, onError) {
  return indices.reduce((previous, n) => previous.then(() => {
    if (!latest()) {
      return undefined;
    }
    return request(n).then(rows => {
      if (latest()) {
        onColumn(n, rows);
      }
    }).catch(e => {
      if (latest()) {
        onError(n, e);
      }
    });
  }), Promise.resolve());
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/CompareTable.vue?vue&type=script&lang=ts














/** colours of the column dots: the plugin's series; Matomo's comparison colour wins */
const SERIES_COLOURS = 10;
/**
 * The free-form table while comparing (comparison design 3): one column group per metric, one
 * column per comparison, the total row first; counts and sums with their share and a bar, period
 * columns with the change of the page's period against them.
 */
/* harmony default export */ var CompareTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    RowMenu: RowMenu,
    TableScroller: TableScroller
  },
  props: {
    /** the total row (fwde_total) first, then the own rows */
    rows: {
      type: Array,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    },
    columns: {
      type: Array,
      required: true
    },
    /** by column index: the message of a column that could not be computed */
    columnErrors: {
      type: Object,
      default: () => ({})
    },
    actionScope: {
      type: Boolean,
      required: true
    },
    currencySymbol: {
      type: String,
      required: true
    },
    visitsLogEnabled: {
      type: Boolean,
      default: false
    }
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits'],
  data() {
    return {
      MAX_DIMENSIONS: MAX_DIMENSIONS,
      /** two dimensions: the first at its natural width while the table fits (Peter, 2026-11-23) */
      roomy: true,
      /** the scroller's last width; a shrunk table tries again only when it widens */
      boxWidth: 0
    };
  },
  computed: {
    /** why the rows can add up to more than the total (design 2026-11-20), '' when they add up */
    overcountKey() {
      const unit = overcountUnit(this.state, this.catalog);
      if (!unit) {
        return '';
      }
      return unit === 'visitor' ? 'FeinwerkDataExploration_OvercountHintVisitor' : 'FeinwerkDataExploration_OvercountHintVisit';
    },
    dimensionColumns() {
      const name = id => {
        var _this$catalog$dimensi;
        return ((_this$catalog$dimensi = this.catalog.dimensions.find(d => d.id === id)) === null || _this$catalog$dimensi === void 0 ? void 0 : _this$catalog$dimensi.name) || id;
      };
      return this.state.dimensions.map((id, i) => ({
        key: LABEL_COLUMNS[i],
        label: name(id)
      }));
    },
    metricColumns() {
      return this.state.metrics.map(id => {
        const metric = this.catalog.metrics.find(m => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
        return {
          key: id,
          label
        };
      });
    },
    lastDimension() {
      return LABEL_COLUMNS[this.state.dimensions.length - 1];
    },
    /**
     * With two dimensions and only short values in the first one, the first column is only as
     * wide as its values: the free width goes behind the second dimension, so both dimensions stay
     * together on the left (Peter, 2026-11-17). A first dimension with long values (page URLs)
     * keeps the free width, it needs it.
     */
    firstDimensionFits() {
      return this.state.dimensions.length > 1 && this.rows.every(row => headAndTail(String(row.label))[1] === '');
    },
    /**
     * the first of two dimension columns is only as wide as its values: always for short values,
     * for long ones while the table has room; the second dimension then takes the free width
     */
    firstColumnFits() {
      return this.state.dimensions.length > 1 && (this.firstDimensionFits || this.roomy);
    },
    /** long first values shown on one line at their natural width (up to the usual 32em) */
    naturalFirst() {
      return this.state.dimensions.length > 1 && !this.firstDimensionFits && this.roomy;
    },
    /**
     * What each count's bars are measured against: the metric's largest value in all its columns,
     * so a smaller segment shows smaller bars (sorting does not change it).
     */
    barMaxima() {
      const maxima = {};
      this.state.metrics.filter(id => hasShare(id)).forEach(id => {
        maxima[id] = barMaximum(this.rows, this.columns.map((_, n) => valueKey(id, n)));
      });
      return maxima;
    },
    total() {
      return this.rows.find(row => row.fwde_total) || null;
    },
    dataRows() {
      return this.rows.filter(row => !row.fwde_total);
    },
    sortedRows() {
      if (!shouldSortClientSide(this.dataRows.length, this.state.limit, this.state.sort)) {
        return this.dataRows;
      }
      return sortByLabel(this.dataRows, this.state.sort, this.state.sortOrder, external_CoreHome_["Matomo"].language);
    },
    formatters() {
      return {
        number: v => external_CoreHome_["NumberFormatter"].formatNumber(v, 2, 0),
        percent: v => external_CoreHome_["NumberFormatter"].formatPercent(v, 2, 0),
        currency: v => external_CoreHome_["NumberFormatter"].formatCurrency(v, this.currencySymbol, 2, 2),
        duration: v => formatDuration(v)
      };
    }
  },
  watch: {
    // a new result tries the natural width again
    rows() {
      this.roomy = true;
    }
  },
  methods: {
    onLayout(layout) {
      this.roomy = roomyAfter(this.roomy, this.naturalFirst, layout, this.boxWidth);
      this.boxWidth = layout.width;
    },
    translate: external_CoreHome_["translate"],
    rowCountKey: rowCountKey,
    formatNumber(value) {
      return external_CoreHome_["NumberFormatter"].formatNumber(value, 0, 0);
    },
    value(row, metric, n) {
      const raw = row[valueKey(metric, n)];
      if (raw === null || raw === undefined || raw === '') {
        return '';
      }
      return formatMetric(metric, Number(raw), this.formatters);
    },
    /** the share (own and segment columns, counts and sums) or the change (period columns) */
    extra(row, metric, column, n) {
      if (column.kind === 'period' || column.kind === 'both') {
        const change = row[changeKey(metric, n)];
        if (change === null || change === undefined) {
          return '';
        }
        const formatted = external_CoreHome_["NumberFormatter"].formatPercent(Number(change), 1, 0);
        return Number(change) > 0 ? `+${formatted}` : formatted;
      }
      const share = row[shareKey(metric, n)];
      if (!hasShare(metric) || share === null || share === undefined) {
        return '';
      }
      return external_CoreHome_["NumberFormatter"].formatPercent(Number(share), 1, 0);
    },
    /** head (0) or tail (1) of a dimension value, shortened in the middle by the stylesheet */
    labelPart(value, part) {
      return headAndTail(String(value))[part];
    },
    /** a long value (with a tail) may shrink when the table runs out of room; a short one never */
    ellipsisClasses(value) {
      return {
        'fwde-ellipsis': true,
        'fwde-ellipsis--long': headAndTail(String(value))[1] !== ''
      };
    },
    /** the cell's bar behind a count or sum of a column that has values; null otherwise */
    barStyle(row, metric, n) {
      if (this.columnErrors[n] || row.fwde_total || !(metric in this.barMaxima)) {
        return null;
      }
      const width = cellBarWidth(row[valueKey(metric, n)], this.barMaxima[metric]);
      return width === null ? null : {
        '--fwde-bar': `${width}%`
      };
    },
    /** the dot of a column in Matomo's comparison colour (its comparison box, the trend's lines) */
    dotClasses(n) {
      return ['fwde-compare__dot', 'fwde-chart__series', `fwde-chart__series-${n % SERIES_COLOURS + 1}`, 'comparison-series-color'];
    },
    dotName(n) {
      return external_CoreHome_["ComparisonsStoreInstance"].getSeriesColorName(n, 0);
    },
    rowLabel(row) {
      return LABEL_COLUMNS.filter(column => row[column] !== undefined).map(column => String(row[column])).join(' · ');
    },
    filterReason(row) {
      return canFilterTo(row) ? '' : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoFilterForRow');
    },
    visitsReason(row) {
      if (!this.visitsLogEnabled) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_VisitsLogDisabled');
      }
      return row.segment ? '' : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoVisitsForRow');
    },
    ariaSort(key) {
      if (this.state.sort !== key) {
        return 'none';
      }
      return this.state.sortOrder === 'asc' ? 'ascending' : 'descending';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/CompareTable.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/CompareTable.vue



CompareTablevue_type_script_lang_ts.render = CompareTablevue_type_template_id_955e3b2c_render

/* harmony default export */ var CompareTable = (CompareTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PivotTable.vue?vue&type=template&id=8f383e26

const PivotTablevue_type_template_id_8f383e26_hoisted_1 = {
  class: "fwde-table fwde-compare fwde-pivot"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_2 = {
  class: "dataTable fwde-table__table fwde-compare__table"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_3 = ["aria-sort"];
const PivotTablevue_type_template_id_8f383e26_hoisted_4 = ["title", "onClick"];
const PivotTablevue_type_template_id_8f383e26_hoisted_5 = ["colspan", "title"];
const PivotTablevue_type_template_id_8f383e26_hoisted_6 = {
  class: "fwde-table__menu",
  rowspan: "2"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_7 = {
  class: "fwde-visually-hidden"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_8 = ["aria-sort"];
const PivotTablevue_type_template_id_8f383e26_hoisted_9 = ["title", "onClick"];
const PivotTablevue_type_template_id_8f383e26_hoisted_10 = {
  key: 1
};
const PivotTablevue_type_template_id_8f383e26_hoisted_11 = {
  key: 0,
  class: "fwde-compare__total"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_12 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", {
  class: "fwde-table__menu"
}, null, -1);
const PivotTablevue_type_template_id_8f383e26_hoisted_13 = ["title"];
const PivotTablevue_type_template_id_8f383e26_hoisted_14 = {
  class: "fwde-ellipsis__head"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_15 = {
  class: "fwde-ellipsis__tail"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_16 = {
  class: "fwde-table__menu"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_17 = {
  key: 0,
  class: "fwde-table__overcount"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_18 = {
  key: 1,
  class: "fwde-table__overcount fwde-pivot__overcount"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_19 = {
  class: "fwde-table__footer"
};
const PivotTablevue_type_template_id_8f383e26_hoisted_20 = {
  key: 2,
  class: "fwde-table__limit"
};
function PivotTablevue_type_template_id_8f383e26_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RowMenu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RowMenu");
  const _component_TableScroller = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("TableScroller");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PivotTablevue_type_template_id_8f383e26_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_TableScroller, null, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", PivotTablevue_type_template_id_8f383e26_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, dimension => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
        key: dimension.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          label: true,
          sortable: true,
          columnSorted: _ctx.state.sort === dimension.key,
          'fwde-table__dimension': true,
          'fwde-table__dimension-last': dimension.key === _ctx.lastDimension
        }),
        "aria-sort": _ctx.ariaSort(dimension.key),
        rowspan: "2"
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "fwde-table__sort",
        title: _ctx.translate('FeinwerkDataExploration_SortBy', dimension.label),
        onClick: $event => _ctx.$emit('sort', dimension.key)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dimension.label), 1), _ctx.state.sort === dimension.key ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
        key: 0,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`sortIcon ${_ctx.state.sortOrder}`),
        "aria-hidden": "true"
      }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, PivotTablevue_type_template_id_8f383e26_hoisted_4)], 10, PivotTablevue_type_template_id_8f383e26_hoisted_3);
    }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
        key: group.key,
        colspan: group.metrics.length,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          'fwde-compare__group': true,
          'fwde-pivot__group': true,
          'fwde-pivot__group--total': group.key === 'total'
        }),
        title: group.title
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(group.title), 11, PivotTablevue_type_template_id_8f383e26_hoisted_5);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", PivotTablevue_type_template_id_8f383e26_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PivotTablevue_type_template_id_8f383e26_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowActions')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: group.key
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.metrics, (metric, m) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
          key: `${group.key}-${metric.id}`,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            'fwde-table__number': true,
            'fwde-compare__column': true,
            'fwde-compare__group-start': m === 0,
            sortable: group.key === 'total',
            columnSorted: group.key === 'total' && _ctx.state.sort === metric.id
          }),
          "aria-sort": group.key === 'total' ? _ctx.ariaSort(metric.id) : undefined
        }, [group.key === 'total' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
          key: 0,
          type: "button",
          class: "fwde-table__sort",
          title: _ctx.translate('FeinwerkDataExploration_SortBy', metric.label),
          onClick: $event => _ctx.$emit('sort', metric.id)
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.label), 1), _ctx.state.sort === metric.id ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
          key: 0,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`sortIcon ${_ctx.state.sortOrder}`),
          "aria-hidden": "true"
        }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, PivotTablevue_type_template_id_8f383e26_hoisted_9)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PivotTablevue_type_template_id_8f383e26_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.label), 1))], 10, PivotTablevue_type_template_id_8f383e26_hoisted_8);
      }), 128))], 64);
    }), 128))])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [_ctx.pivot.total ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", PivotTablevue_type_template_id_8f383e26_hoisted_11, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, dimension => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
        key: dimension.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
          label: true,
          'fwde-table__label': true,
          'fwde-table__dimension': true,
          'fwde-table__dimension-last': dimension.key === _ctx.lastDimension
        })
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(dimension.key === 'label' ? _ctx.pivot.total.label : ''), 3);
    }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: group.key
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.metrics, (metric, m) => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: `${group.key}-${metric.id}`,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            'fwde-table__number': true,
            'fwde-compare__cell': true,
            'fwde-pivot__cell': true,
            'fwde-compare__group-start': m === 0
          })
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(metric.id, _ctx.pivot.total[_ctx.cellKey(group.key, metric.id)])), 3);
      }), 128))], 64);
    }), 128)), PivotTablevue_type_template_id_8f383e26_hoisted_12])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedRows, (row, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: index
      }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dimensionColumns, dimension => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
          key: dimension.key,
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
            label: true,
            'fwde-table__label': true,
            'fwde-table__dimension': true,
            'fwde-table__dimension-last': dimension.key === _ctx.lastDimension
          }),
          title: String(row[dimension.key])
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
          class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.ellipsisClasses(row[dimension.key]))
        }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PivotTablevue_type_template_id_8f383e26_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.labelPart(row[dimension.key], 0)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PivotTablevue_type_template_id_8f383e26_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.labelPart(row[dimension.key], 1)), 1)], 2)], 10, PivotTablevue_type_template_id_8f383e26_hoisted_13);
      }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
          key: group.key
        }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.metrics, (metric, m) => {
          return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
            key: `${group.key}-${metric.id}`,
            class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
              'fwde-table__number': true,
              'fwde-compare__cell': true,
              'fwde-pivot__cell': true,
              'fwde-compare__group-start': m === 0,
              'fwde-cellbar': _ctx.barStyle(_ctx.cellKey(group.key, metric.id), row) !== null
            }),
            style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.barStyle(_ctx.cellKey(group.key, metric.id), row) || undefined)
          }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(metric.id, row[_ctx.cellKey(group.key, metric.id)])), 7);
        }), 128))], 64);
      }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", PivotTablevue_type_template_id_8f383e26_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RowMenu, {
        label: _ctx.rowLabel(row),
        "can-break-down": _ctx.state.dimensions.length < _ctx.MAX_DIMENSIONS,
        "visits-reason": _ctx.visitsReason(row),
        "filter-reason": _ctx.filterReason(row),
        onFilter: $event => _ctx.$emit('row-filter', row),
        onBreakdown: $event => _ctx.$emit('row-breakdown', row),
        onVisits: $event => _ctx.$emit('row-visits', row)
      }, null, 8, ["label", "can-break-down", "visits-reason", "filter-reason", "onFilter", "onBreakdown", "onVisits"])])]);
    }), 128))])])]),
    _: 1
  }), _ctx.overcountKey ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", PivotTablevue_type_template_id_8f383e26_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.overcountKey)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.pivotOvercount ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", PivotTablevue_type_template_id_8f383e26_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_PivotOvercountHint')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", PivotTablevue_type_template_id_8f383e26_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.rowCountKey(_ctx.pivot.rows.length), _ctx.formatNumber(_ctx.pivot.rows.length))), 1), _ctx.pivot.rows.length >= _ctx.state.limit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", PivotTablevue_type_template_id_8f383e26_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_LimitReached')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PivotTable.vue?vue&type=template&id=8f383e26

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PivotTable.vue?vue&type=script&lang=ts













/**
 * A pivoted result (rows and pivot, 2026-11-24): the row dimensions on the left; one column group
 * per pivot value, then "Others" (the metrics whose rest is exact) and "Total", each with the
 * metrics; the total row first. Sorting by the row labels or a metric's Total.
 */
/* harmony default export */ var PivotTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    RowMenu: RowMenu,
    TableScroller: TableScroller
  },
  props: {
    rows: {
      type: Array,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    },
    currencySymbol: {
      type: String,
      required: true
    },
    /** Matomo's visits log is on for this site */
    visitsLogEnabled: {
      type: Boolean,
      default: false
    }
  },
  emits: ['sort', 'row-filter', 'row-breakdown', 'row-visits'],
  data() {
    return {
      MAX_DIMENSIONS: MAX_DIMENSIONS
    };
  },
  computed: {
    pivot() {
      return parsePivotResult(this.rows);
    },
    /** why the rows can add up to more than the total, as the plain table says it; '' when not */
    overcountKey() {
      const unit = overcountUnit(this.state, this.catalog);
      if (!unit) {
        return '';
      }
      return unit === 'visitor' ? 'FeinwerkDataExploration_OvercountHintVisitor' : 'FeinwerkDataExploration_OvercountHintVisit';
    },
    /** a count can add up to more than its Total across the columns (final review, 2026-11-24) */
    pivotOvercount() {
      return pivotOvercounts(this.state, this.catalog);
    },
    dimensionColumns() {
      return this.state.dimensions.map((id, i) => {
        var _this$catalog$dimensi;
        return {
          key: LABEL_COLUMNS[i],
          label: ((_this$catalog$dimensi = this.catalog.dimensions.find(d => d.id === id)) === null || _this$catalog$dimensi === void 0 ? void 0 : _this$catalog$dimensi.name) || id
        };
      });
    },
    lastDimension() {
      return LABEL_COLUMNS[this.state.dimensions.length - 1];
    },
    metrics() {
      return this.state.metrics.map(id => {
        const metric = this.catalog.metrics.find(m => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
        return {
          id,
          label
        };
      });
    },
    groups() {
      const groups = this.pivot.values.map((value, n) => ({
        key: `p${n + 1}`,
        title: value.label,
        metrics: this.metrics
      }));
      const total = this.pivot.total || {};
      const others = this.metrics.filter(metric => `${metric.id}__p_others` in total);
      if (this.pivot.others && others.length) {
        groups.push({
          key: 'p_others',
          title: Object(external_CoreHome_["translate"])('General_Others'),
          metrics: others
        });
      }
      groups.push({
        key: 'total',
        title: Object(external_CoreHome_["translate"])('General_Total'),
        metrics: this.metrics
      });
      return groups;
    },
    /** what each count's bar is measured against: its column's largest value */
    barMaxima() {
      const maxima = {};
      this.groups.forEach(group => {
        group.metrics.filter(metric => hasShare(metric.id)).forEach(metric => {
          const key = this.cellKey(group.key, metric.id);
          maxima[key] = barMaximum(this.pivot.rows, [key]);
        });
      });
      return maxima;
    },
    sortedRows() {
      const {
        rows
      } = this.pivot;
      if (!shouldSortClientSide(rows.length, this.state.limit, this.state.sort)) {
        return rows;
      }
      return sortByLabel(rows, this.state.sort, this.state.sortOrder, external_CoreHome_["Matomo"].language);
    },
    formatters() {
      return {
        number: v => external_CoreHome_["NumberFormatter"].formatNumber(v, 2, 0),
        percent: v => external_CoreHome_["NumberFormatter"].formatPercent(v, 2, 0),
        currency: v => external_CoreHome_["NumberFormatter"].formatCurrency(v, this.currencySymbol, 2, 2),
        duration: v => formatDuration(v)
      };
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    rowCountKey: rowCountKey,
    /** the column of a metric in a group: the plain metric is the row's Total */
    cellKey(group, metric) {
      return group === 'total' ? metric : `${metric}__${group}`;
    },
    /** a cell without a value (a rate without visits) stays empty, not "0 %" */
    format(metricId, value) {
      if (value === null || value === undefined || value === '') {
        return '';
      }
      return formatMetric(metricId, Number(value), this.formatters);
    },
    formatNumber(value) {
      return external_CoreHome_["NumberFormatter"].formatNumber(value, 0, 0);
    },
    labelPart(value, part) {
      return headAndTail(String(value))[part];
    },
    ellipsisClasses(value) {
      return {
        'fwde-ellipsis': true,
        'fwde-ellipsis--long': headAndTail(String(value))[1] !== ''
      };
    },
    barStyle(key, row) {
      if (!(key in this.barMaxima)) {
        return null;
      }
      const width = cellBarWidth(row[key], this.barMaxima[key]);
      return width === null ? null : {
        '--fwde-bar': `${width}%`
      };
    },
    rowLabel(row) {
      return LABEL_COLUMNS.filter(column => row[column] !== undefined).map(column => String(row[column])).join(' · ');
    },
    filterReason(row) {
      return canFilterTo(row) ? '' : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoFilterForRow');
    },
    visitsReason(row) {
      if (!this.visitsLogEnabled) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_VisitsLogDisabled');
      }
      return row.segment ? '' : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoVisitsForRow');
    },
    ariaSort(key) {
      if (this.state.sort !== key) {
        return 'none';
      }
      return this.state.sortOrder === 'asc' ? 'ascending' : 'descending';
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PivotTable.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PivotTable.vue



PivotTablevue_type_script_lang_ts.render = PivotTablevue_type_template_id_8f383e26_render

/* harmony default export */ var PivotTable = (PivotTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PivotPicker.vue?vue&type=template&id=76a57371

const PivotPickervue_type_template_id_76a57371_hoisted_1 = {
  class: "fwde-picker fwde-pivot-picker"
};
const PivotPickervue_type_template_id_76a57371_hoisted_2 = {
  class: "fwde-picker__slot"
};
const PivotPickervue_type_template_id_76a57371_hoisted_3 = {
  class: "fwde-field__label"
};
const PivotPickervue_type_template_id_76a57371_hoisted_4 = ["disabled", "aria-expanded"];
const PivotPickervue_type_template_id_76a57371_hoisted_5 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const PivotPickervue_type_template_id_76a57371_hoisted_6 = {
  key: 0,
  class: "fwde-pivot-picker__paused"
};
const PivotPickervue_type_template_id_76a57371_hoisted_7 = {
  key: 1,
  class: "fwde-picker__panel"
};
const PivotPickervue_type_template_id_76a57371_hoisted_8 = {
  key: 0,
  class: "fwde-pivot-picker__limit"
};
function PivotPickervue_type_template_id_76a57371_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchableList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchableList");
  const _component_SegmentedSwitch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentedSwitch");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PivotPickervue_type_template_id_76a57371_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PivotPickervue_type_template_id_76a57371_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PivotPickervue_type_template_id_76a57371_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Pivot')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    ref: "current",
    type: "button",
    class: "fwde-picker__current",
    disabled: _ctx.comparing,
    "aria-expanded": _ctx.open ? 'true' : 'false',
    onClick: _cache[0] || (_cache[0] = $event => _ctx.open = !_ctx.open)
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.currentName), 1), PivotPickervue_type_template_id_76a57371_hoisted_5], 8, PivotPickervue_type_template_id_76a57371_hoisted_4), _ctx.comparing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", PivotPickervue_type_template_id_76a57371_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_PivotPaused')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.open && !_ctx.comparing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PivotPickervue_type_template_id_76a57371_hoisted_7, [_ctx.state.pivot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "btn-flat fwde-picker__none",
    onClick: _cache[1] || (_cache[1] = $event => _ctx.choose(null))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NoSecondDimension')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchableList, {
    items: _ctx.items,
    selected: _ctx.state.pivot ? [_ctx.state.pivot] : [],
    "search-label": _ctx.translate('FeinwerkDataExploration_SearchDimensions'),
    onToggle: _cache[2] || (_cache[2] = $event => _ctx.choose($event)),
    onClose: _ctx.close
  }, null, 8, ["items", "selected", "search-label", "onClose"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.state.pivot && _ctx.state.pivot !== _ctx.PIVOT_WEEKDAY && !_ctx.comparing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PivotPickervue_type_template_id_76a57371_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
    options: _ctx.limitOptions,
    "model-value": String(_ctx.state.pivotLimit),
    label: _ctx.translate('FeinwerkDataExploration_PivotValues'),
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.$emit('change', {
      pivot: _ctx.state.pivot,
      pivotLimit: Number($event)
    }))
  }, null, 8, ["options", "model-value", "label"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PivotPicker.vue?vue&type=template&id=76a57371

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PivotPicker.vue?vue&type=script&lang=ts






/**
 * "Columns (pivot)" (rows and pivot, 2026-11-24): a dimension whose top values become column
 * groups, and how many (3, 5, 10). Off while Matomo compares: the comparison wins, the pivot stays
 * in the link and comes back when the comparison ends.
 */
/* harmony default export */ var PivotPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SearchableList: SearchableList,
    SegmentedSwitch: SegmentedSwitch
  },
  props: {
    catalog: {
      type: Object,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    /** Matomo compares segments or periods */
    comparing: {
      type: Boolean,
      default: false
    }
  },
  emits: ['change'],
  data() {
    return {
      open: false,
      PIVOT_WEEKDAY: PIVOT_WEEKDAY
    };
  },
  computed: {
    currentName() {
      const dimension = this.catalog.dimensions.find(d => d.id === this.state.pivot);
      return dimension ? dimension.name : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_NoSecondDimension');
    },
    items() {
      const reasons = pivotReasons(this.state, this.catalog);
      return this.catalog.dimensions.map(d => ({
        key: d.id,
        label: d.name,
        group: GROUP_KEYS[d.group] ? Object(external_CoreHome_["translate"])(GROUP_KEYS[d.group]) : d.group,
        reason: reasons[d.id]
      }));
    },
    limitOptions() {
      const reasons = pivotLimitReasons(this.state);
      return PIVOT_LIMITS.map(limit => ({
        value: String(limit),
        label: String(limit),
        disabled: reasons[limit] !== null,
        title: reasons[limit] ? Object(external_CoreHome_["translate"])(reasons[limit]) : undefined
      }));
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    choose(id) {
      this.open = false;
      this.$emit('change', {
        pivot: id,
        pivotLimit: id === this.state.pivot ? this.state.pivotLimit : PIVOT_DEFAULT_LIMIT
      });
    },
    /** Escape in the list: close it and give the focus back to its button */
    close() {
      this.open = false;
      this.$nextTick(() => {
        const button = this.$refs.current;
        if (button) {
          button.focus();
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PivotPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PivotPicker.vue



PivotPickervue_type_script_lang_ts.render = PivotPickervue_type_template_id_76a57371_render

/* harmony default export */ var PivotPicker = (PivotPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/funnelState.ts
/** As FunnelStep::OPERATORS in PHP: the conditions of the text steps. */
const OPERATORS_BY_TYPE = {
  pageUrl: ['contains', 'equals', 'startsWith'],
  pageTitle: ['contains', 'equals', 'startsWith'],
  eventCategory: ['contains', 'equals'],
  eventAction: ['contains', 'equals'],
  eventName: ['contains', 'equals'],
  siteSearch: ['contains', 'equals'],
  download: ['contains', 'equals'],
  outlink: ['contains', 'equals']
};
const STEP_TYPES = ['pageUrl', 'pageTitle', 'eventCategory', 'eventAction', 'eventName', 'siteSearch', 'download', 'outlink', 'goal', 'anyGoal'];
const MAX_STEP_TEXT = 1000;
function isGoalType(type) {
  return type === 'goal' || type === 'anyGoal';
}
function emptyStep() {
  return {
    type: 'pageUrl',
    operator: 'contains',
    value: '',
    idGoal: null
  };
}
function goalStep(type, idGoal) {
  return {
    type,
    operator: '',
    value: '',
    idGoal
  };
}
/**
 * The steps of the hash parameter fwdeSteps: "type~operator~value;…" with the value URI-encoded
 * (only the first two "~" separate), goals "goal~~3" and "anyGoal~~". Invalid entries are left
 * out; a goal the website does not have becomes a goal step still to choose; more than maxSteps
 * are cut and fewer than two padded with empty steps. Texts stay as typed (not trimmed).
 */
function parseSteps(value, goalIds, maxSteps) {
  const steps = [];
  String(value || '').split(';').forEach(entry => {
    const first = entry.indexOf('~');
    const second = first < 0 ? -1 : entry.indexOf('~', first + 1);
    if (second < 0 || steps.length >= maxSteps) {
      return;
    }
    const type = entry.substring(0, first);
    const operator = entry.substring(first + 1, second);
    let text;
    try {
      text = decodeURIComponent(entry.substring(second + 1));
    } catch (e) {
      return;
    }
    if (type === 'anyGoal') {
      steps.push(goalStep('anyGoal', null));
    } else if (type === 'goal') {
      const id = Number(text);
      steps.push(goalStep('goal', Number.isInteger(id) && goalIds.includes(id) ? id : null));
    } else if ((OPERATORS_BY_TYPE[type] || []).includes(operator)) {
      steps.push({
        type: type,
        operator: operator,
        value: text,
        idGoal: null
      });
    }
  });
  while (steps.length < 2) {
    steps.push(emptyStep());
  }
  return steps;
}
function toStepsParam(steps) {
  return steps.map(step => {
    if (step.type === 'goal') {
      return `goal~~${step.idGoal === null ? '' : step.idGoal}`;
    }
    if (step.type === 'anyGoal') {
      return 'anyGoal~~';
    }
    return `${step.type}~${step.operator}~${encodeURIComponent(step.value)}`;
  }).join(';');
}
function isComplete(step) {
  if (step.type === 'goal') {
    return step.idGoal !== null;
  }
  if (step.type === 'anyGoal') {
    return true;
  }
  const text = step.value.trim();
  return text !== '' && text.length <= MAX_STEP_TEXT;
}
function canRun(steps) {
  return steps.length >= 2 && steps.every(isComplete);
}
/** The definition for FeinwerkDataExploration.getFunnel. */
function toFunnelDefinition(steps) {
  return {
    steps: steps.map(step => {
      if (step.type === 'goal') {
        return {
          type: 'goal',
          idGoal: step.idGoal
        };
      }
      if (step.type === 'anyGoal') {
        return {
          type: 'anyGoal'
        };
      }
      return {
        type: step.type,
        operator: step.operator,
        value: step.value.trim()
      };
    })
  };
}
/**
 * The step after its type was changed: text kept between text types, the operator kept where the
 * new type allows it, goal steps without operator and text.
 */
function withType(step, type) {
  if (type === 'goal' || type === 'anyGoal') {
    return goalStep(type, null);
  }
  const operators = OPERATORS_BY_TYPE[type];
  return {
    type,
    operator: operators.includes(step.operator) ? step.operator : 'contains',
    value: isGoalType(step.type) ? '' : step.value,
    idGoal: null
  };
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/exportLink.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



/**
 * A download link for the current exploration, built like Matomo's own report export links.
 */
function exportUrl(state, format, columns = []) {
  const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
  const params = {
    module: 'API',
    method: 'FeinwerkDataExploration.getExploration',
    format,
    idSite: parsed.idSite,
    period: parsed.period,
    date: parsed.date,
    segment: parsed.segment || '',
    // with comparison columns the pivot is paused (the comparison wins)
    definition: JSON.stringify(toDefinition(state, columns.length > 1)),
    readableColumns: 1,
    filter_limit: -1,
    language: external_CoreHome_["Matomo"].language,
    token_auth: external_CoreHome_["Matomo"].token_auth,
    force_api_session: 1
  };
  if (columns.length > 1) {
    // all comparison columns in one request
    params.comparisons = JSON.stringify(columns.slice(1).map(c => ({
      segment: c.segment,
      period: c.period,
      date: c.date
    })));
  }
  return `${window.location.href.split(/[?#]/)[0]}?${external_CoreHome_["MatomoUrl"].stringify(params)}`;
}
/**
 * A download link for the current funnel, with the columns named in the user's language.
 */
function funnelExportUrl(steps, format) {
  const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
  const params = {
    module: 'API',
    method: 'FeinwerkDataExploration.getFunnel',
    format,
    idSite: parsed.idSite,
    period: parsed.period,
    date: parsed.date,
    segment: parsed.segment || '',
    definition: JSON.stringify(toFunnelDefinition(steps)),
    readableColumns: 1,
    filter_limit: -1,
    language: external_CoreHome_["Matomo"].language,
    token_auth: external_CoreHome_["Matomo"].token_auth,
    force_api_session: 1
  };
  return `${window.location.href.split(/[?#]/)[0]}?${external_CoreHome_["MatomoUrl"].stringify(params)}`;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=template&id=2f6db02e

const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_1 = {
  class: "fwde-toolbar"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_2 = {
  key: 1,
  class: "fwde-toolbar__limit"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_3 = ["value"];
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_4 = ["value"];
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_5 = {
  key: 2,
  ref: "exportMenu",
  class: "fwde-menu"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_6 = ["aria-expanded"];
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export",
  "aria-hidden": "true"
}, null, -1);
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_9 = {
  key: 0,
  class: "fwde-menu__list"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_10 = ["href"];
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_11 = {
  ref: "copyBox",
  class: "fwde-copybox"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_12 = ["title", "aria-label"];
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_13 = {
  key: 0,
  class: "fwde-menu__list fwde-copylink"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_14 = {
  class: "fwde-copylink__label"
};
const ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_15 = ["value"];
function ExplorationToolbarvue_type_template_id_2f6db02e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SegmentedSwitch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentedSwitch");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_1, [_ctx.state ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SegmentedSwitch, {
    key: 0,
    class: "fwde-toolbar__view",
    options: _ctx.viewSwitch,
    "model-value": _ctx.view,
    label: _ctx.translate('FeinwerkDataExploration_View'),
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.$emit('view', $event))
  }, null, 8, ["options", "model-value", "label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.state ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("label", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Rows')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default fwde-select",
    value: String(_ctx.state.limit),
    onChange: _cache[1] || (_cache[1] = (...args) => _ctx.onLimit && _ctx.onLimit(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.limitOptions, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option,
      value: String(option)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option), 9, ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_4);
  }), 128))], 40, ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_3)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.state || _ctx.exportUrlFor ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button",
    "aria-haspopup": "true",
    "aria-expanded": _ctx.exportOpen ? 'true' : 'false',
    onClick: _cache[2] || (_cache[2] = $event => _ctx.exportOpen = !_ctx.exportOpen)
  }, [ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Export')) + " ", 1), ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_8], 8, ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_6), _ctx.exportOpen ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_9, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.formats, format => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: format
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "fwde-menu__item",
      href: _ctx.exportHref(format),
      target: "_blank",
      rel: "noreferrer noopener",
      onClick: _cache[3] || (_cache[3] = $event => _ctx.exportOpen = false)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ExportAs', format)), 9, ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_10)]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-icon-button fwde-toolbar__copy",
    title: _ctx.copyText,
    "aria-label": _ctx.copyText,
    onClick: _cache[4] || (_cache[4] = (...args) => _ctx.copyLink && _ctx.copyLink(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.copied ? 'icon-ok' : 'icon-content-copy'),
    "aria-hidden": "true"
  }, null, 2)], 8, ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_12), _ctx.manualLink ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_CopyLinkByHand')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    ref: "manualInput",
    class: "browser-default fwde-input",
    type: "text",
    readonly: "",
    value: _ctx.manualLink,
    onFocus: _cache[5] || (_cache[5] = (...args) => _ctx.selectAll && _ctx.selectAll(...args)),
    onKeydown: _cache[6] || (_cache[6] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])((...args) => _ctx.closeManual && _ctx.closeManual(...args), ["stop"]), ["escape"]))
  }, null, 40, ExplorationToolbarvue_type_template_id_2f6db02e_hoisted_15)])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=template&id=2f6db02e

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=script&lang=ts





/* harmony default export */ var ExplorationToolbarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SegmentedSwitch: SegmentedSwitch
  },
  props: {
    /** the free-form table; null for pages without view switch and row limit (funnel) */
    state: {
      type: Object,
      default: null
    },
    maxRows: {
      type: Number,
      default: 0
    },
    view: {
      type: String,
      default: 'table'
    },
    canChart: {
      type: Boolean,
      default: false
    },
    /** the export link of a page without state (funnel) */
    exportUrlFor: {
      type: Function,
      default: undefined
    }
  },
  emits: ['limit', 'view'],
  data() {
    return {
      copied: false,
      /** the link shown for copying by hand when the browser refuses ('' = hidden) */
      manualLink: '',
      timer: 0,
      exportOpen: false,
      formats: ['CSV', 'TSV']
    };
  },
  mounted() {
    document.addEventListener('click', this.onDocumentClick);
    document.addEventListener('keydown', this.onKeydown);
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
    document.removeEventListener('click', this.onDocumentClick);
    document.removeEventListener('keydown', this.onKeydown);
  },
  computed: {
    limitOptions() {
      return LIMIT_OPTIONS.filter(option => option <= this.maxRows);
    },
    viewOptions() {
      return [{
        view: 'table',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ViewTable')
      }, {
        view: 'chart',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ViewChart')
      }, {
        view: 'both',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ViewBoth')
      }];
    },
    /** Chart and Both need rows: disabled with the reason as tooltip */
    viewSwitch() {
      return this.viewOptions.map(option => {
        const needsRows = option.view !== 'table' && !this.canChart;
        return {
          value: option.view,
          label: option.label,
          disabled: needsRows,
          title: needsRows ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartNeedsRows') : undefined
        };
      });
    },
    copyText() {
      return Object(external_CoreHome_["translate"])(this.copied ? 'FeinwerkDataExploration_LinkCopied' : 'FeinwerkDataExploration_CopyLink');
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    exportHref(format) {
      if (this.exportUrlFor) {
        return this.exportUrlFor(format);
      }
      return this.state ? exportUrl(this.state, format) : '';
    },
    onLimit(event) {
      this.$emit('limit', Number(event.target.value));
    },
    onDocumentClick(event) {
      const copyBox = this.$refs.copyBox;
      if (this.manualLink && copyBox && !copyBox.contains(event.target)) {
        this.manualLink = '';
      }
      const menu = this.$refs.exportMenu;
      if (this.exportOpen && menu && !menu.contains(event.target)) {
        this.exportOpen = false;
      }
    },
    onKeydown(event) {
      if (event.key === 'Escape') {
        this.exportOpen = false;
        this.manualLink = '';
      }
    },
    /**
     * The clipboard first; without it (Matomo over http) or when the browser refuses, the older
     * copy command; when that fails too, the link selected in a field to copy by hand (UI review
     * 2026-11-23: never a silent failure, the user would paste an old clipboard).
     */
    copyLink() {
      const url = window.location.href;
      this.manualLink = '';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(() => this.copiedNow()).catch(() => this.copyWithCommand(url));
        return;
      }
      this.copyWithCommand(url);
    },
    copyWithCommand(url) {
      let copied = false;
      if (typeof document.execCommand === 'function') {
        const field = document.createElement('textarea');
        field.value = url;
        field.setAttribute('readonly', '');
        field.style.position = 'fixed';
        field.style.top = '-1000px';
        document.body.appendChild(field);
        field.select();
        try {
          copied = document.execCommand('copy');
        } catch (e) {
          copied = false;
        }
        document.body.removeChild(field);
      }
      if (copied) {
        this.copiedNow();
        return;
      }
      this.manualLink = url;
      this.$nextTick(() => {
        const input = this.$refs.manualInput;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },
    copiedNow() {
      this.copied = true;
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => {
        this.copied = false;
      }, 2500);
    },
    selectAll(event) {
      event.target.select();
    },
    closeManual() {
      this.manualLink = '';
      const button = this.$el.querySelector('.fwde-toolbar__copy');
      if (button) {
        button.focus();
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue



ExplorationToolbarvue_type_script_lang_ts.render = ExplorationToolbarvue_type_template_id_2f6db02e_render

/* harmony default export */ var ExplorationToolbar = (ExplorationToolbarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=template&id=7b2556fd

const SavedPickervue_type_template_id_7b2556fd_hoisted_1 = {
  class: "fwde-saved"
};
const SavedPickervue_type_template_id_7b2556fd_hoisted_2 = {
  class: "fwde-field__label",
  for: "fwde-saved-select"
};
const SavedPickervue_type_template_id_7b2556fd_hoisted_3 = {
  class: "fwde-saved__row"
};
const SavedPickervue_type_template_id_7b2556fd_hoisted_4 = ["value"];
const SavedPickervue_type_template_id_7b2556fd_hoisted_5 = {
  value: ""
};
const SavedPickervue_type_template_id_7b2556fd_hoisted_6 = ["label"];
const SavedPickervue_type_template_id_7b2556fd_hoisted_7 = ["value"];
const SavedPickervue_type_template_id_7b2556fd_hoisted_8 = ["label"];
const SavedPickervue_type_template_id_7b2556fd_hoisted_9 = ["value"];
const SavedPickervue_type_template_id_7b2556fd_hoisted_10 = {
  key: 0,
  class: "fwde-saved__copy"
};
const SavedPickervue_type_template_id_7b2556fd_hoisted_11 = ["aria-expanded"];
function SavedPickervue_type_template_id_7b2556fd_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ManageSavedDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ManageSavedDialog");
  const _component_SidePanel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SidePanel");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SavedPickervue_type_template_id_7b2556fd_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SavedPickervue_type_template_id_7b2556fd_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedExplorations')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SavedPickervue_type_template_id_7b2556fd_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    id: "fwde-saved-select",
    class: "browser-default fwde-select fwde-saved__select",
    value: _ctx.current ? String(_ctx.current.id) : '',
    onChange: _cache[0] || (_cache[0] = (...args) => _ctx.onChange && _ctx.onChange(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", SavedPickervue_type_template_id_7b2556fd_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NewExploration')), 1), _ctx.groups.mine.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
    key: 0,
    label: _ctx.translate('FeinwerkDataExploration_MyExplorations')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups.mine, saved => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: saved.id,
      value: String(saved.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(saved.shared ? _ctx.translate('FeinwerkDataExploration_SavedShared', _ctx.displayName(saved)) : _ctx.displayName(saved)), 9, SavedPickervue_type_template_id_7b2556fd_hoisted_7);
  }), 128))], 8, SavedPickervue_type_template_id_7b2556fd_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.groups.shared.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
    key: 1,
    label: _ctx.translate('FeinwerkDataExploration_SharedByOthers')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups.shared, saved => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: saved.id,
      value: String(saved.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.ownedBy(saved)), 9, SavedPickervue_type_template_id_7b2556fd_hoisted_9);
  }), 128))], 8, SavedPickervue_type_template_id_7b2556fd_hoisted_8)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 40, SavedPickervue_type_template_id_7b2556fd_hoisted_4), _ctx.canCopy && _ctx.copy && _ctx.copyStore && _ctx.current ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SavedPickervue_type_template_id_7b2556fd_hoisted_10, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.copy.action), {
    "action-form-data": {
      idSaved: _ctx.current.id
    },
    "modal-store": _ctx.copyStore,
    "is-action-visible": true,
    "is-action-enabled": true
  }, null, 8, ["action-form-data", "modal-store"])), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveDynamicComponent"])(_ctx.copy.modal), {
    "modal-store": _ctx.copyStore
  }, null, 8, ["modal-store"]))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.showManage ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwde-saved__manage",
    "aria-haspopup": "dialog",
    "aria-expanded": _ctx.managing ? 'true' : 'false',
    onClick: _cache[1] || (_cache[1] = $event => _ctx.managing = true)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ManageExplorations')), 9, SavedPickervue_type_template_id_7b2556fd_hoisted_11)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showManage || _ctx.managing ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SidePanel, {
    key: 1,
    open: _ctx.managing,
    title: _ctx.translate('FeinwerkDataExploration_ManageExplorations'),
    "close-label": _ctx.translate('General_Close'),
    "scope-class": "fwde-page fwde-managepanel",
    "initial-focus": "panel",
    "onUpdate:open": _cache[4] || (_cache[4] = $event => _ctx.managing = $event)
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ManageSavedDialog, {
      saved: _ctx.saved,
      remove: _ctx.removeSaved,
      place: _ctx.placeSaved,
      "menu-sections": _ctx.menuSections,
      login: _ctx.login,
      onDeleted: _cache[2] || (_cache[2] = $event => _ctx.$emit('deleted', $event)),
      onPlaced: _cache[3] || (_cache[3] = $event => _ctx.$emit('changed', $event))
    }, null, 8, ["saved", "remove", "place", "menu-sections", "login"])]),
    _: 1
  }, 8, ["open", "title", "close-label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=template&id=7b2556fd

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/api.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */



const OPTIONS = {
  createErrorNotification: false
};
/** The API HTML-escapes exception messages. */
function errorText(error) {
  const message = error instanceof Error ? error.message : String(error);
  return external_CoreHome_["Matomo"].helper.htmlDecode(message);
}
function loadCatalog() {
  return Promise.all([external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getAvailableDimensions',
    filter_limit: -1
  }, OPTIONS), external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getAvailableMetrics',
    filter_limit: -1
  }, OPTIONS)]).then(([dimensions, metrics]) => ({
    dimensions,
    metrics
  }));
}
/** The visits of the period in the URL, with the limits that apply (for the warnings). */
function loadPeriodVisits() {
  return external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getPeriodVisits'
  }, OPTIONS);
}
/** The most recent days of the period in the URL that an exploration may read; empty when none. */
function loadSuggestedPeriod() {
  return external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getSuggestedPeriod'
  }, OPTIONS);
}
function loadSaved() {
  return external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getSavedExplorations',
    filter_limit: -1
  }, OPTIONS).then(list => Array.isArray(list) ? list : []);
}
function saveBody(fields) {
  return {
    name: fields.name,
    state: JSON.stringify(fields.state),
    segmentDefinition: fields.segment,
    shared: fields.shared ? '1' : '0'
  };
}
/**
 * Saved explorations can have menu entries of their own (saved reports), so Matomo's menu is read
 * again after a change succeeded, as Goals does after a goal changed (UI review 2026-11-24). A menu
 * that cannot be reloaded never fails the change.
 */
function withMenuReload(change) {
  return change.then(result => {
    try {
      external_CoreHome_["ReportingMenuStore"].reloadMenuItems().catch(() => undefined);
    } catch (e) {
      // no reporting menu on this page: nothing to reload
    }
    return result;
  });
}
function addSaved(fields) {
  return withMenuReload(external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.addSavedExploration'
  }, saveBody(fields), OPTIONS));
}
function updateSaved(id, fields) {
  return withMenuReload(external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.updateSavedExploration'
  }, Object.assign(Object.assign({}, saveBody(fields)), {}, {
    idSaved: String(id)
  }), OPTIONS));
}
/** Changes the menu place of a saved exploration (owner, super user, admin for a shared one). */
function setSavedMenu(id, menu) {
  return withMenuReload(external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.setSavedExplorationMenu'
  }, {
    idSaved: String(id),
    menu
  }, OPTIONS));
}
function deleteSaved(id) {
  return withMenuReload(external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.deleteSavedExploration'
  }, {
    idSaved: String(id)
  }, OPTIONS));
}
/** The site's goals; an empty list when they cannot be read. */
function loadGoals() {
  return external_CoreHome_["AjaxHelper"].fetch({
    method: 'Goals.getGoals',
    filter_limit: -1
  }, OPTIONS).then(goals => (goals || []).map(g => ({
    id: Number(g.idgoal),
    name: g.name
  }))).catch(() => []);
}
/**
 * Runs the exploration for the period and segment in the URL. The definition goes into the POST
 * body; idSite, period, date and segment are added by AjaxHelper.
 */
function runExploration(state, abortController) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.getExploration',
    format: 'json',
    filter_limit: -1
  }, {
    definition: JSON.stringify(toDefinition(state))
  }, Object.assign(Object.assign({}, OPTIONS), {}, {
    abortController
  }));
}
/** A KPI card for the dashboard's period and segment (idSite, period, date, segment: the URL's). */
function loadKpiCard(idSaved) {
  return external_CoreHome_["AjaxHelper"].fetch({
    method: 'FeinwerkDataExploration.getKpiCard',
    idSaved: String(idSaved)
  }, OPTIONS);
}
/**
 * The exact rest of the cut table ("Others", design 2026-11-20), asked on demand: null when nothing
 * was cut. Period, date and segment from the URL as for runExploration.
 */
function runOthers(state) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.getOthers',
    format: 'json'
  },
  // the rest of the rows needs no pivot columns
  {
    definition: JSON.stringify(toDefinition(state, true))
  }, OPTIONS).then(result => result && result.others ? result.others : null);
}
/**
 * Runs the funnel for the period and segment in the URL (POST body as runExploration).
 */
function runFunnel(steps, abortController) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.getFunnel',
    format: 'json',
    filter_limit: -1
  }, {
    definition: JSON.stringify(toFunnelDefinition(steps))
  }, Object.assign(Object.assign({}, OPTIONS), {}, {
    abortController
  }));
}
/**
 * The exploration with one comparison column (…__c1) and, with it, the total row and the own
 * view's shares; the page asks for each column separately.
 */
function runComparison(state, column, abortController) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.getExploration',
    format: 'json',
    filter_limit: -1
  }, {
    // pivot and comparison are exclusive: the comparison wins
    definition: JSON.stringify(toDefinition(state, true)),
    comparisons: JSON.stringify([{
      segment: column.segment,
      period: column.period,
      date: column.date
    }])
  }, Object.assign(Object.assign({}, OPTIONS), {}, {
    abortController
  }));
}
/**
 * The trend (trend design): without a column the first rows of the table per day or hour; with a
 * comparison column the own total (line 0) and that column's total (line 1).
 */
function runTrend(state, column, abortController) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.getTrend',
    format: 'json',
    filter_limit: -1
  }, {
    // the trend follows the rows, never the pivot
    definition: JSON.stringify(toDefinition(state, true)),
    comparisons: column === null ? '' : JSON.stringify([{
      segment: column.segment,
      period: column.period,
      date: column.date
    }])
  }, Object.assign(Object.assign({}, OPTIONS), {}, {
    abortController
  }));
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ManageSavedDialog.vue?vue&type=template&id=539073b4

const ManageSavedDialogvue_type_template_id_539073b4_hoisted_1 = {
  class: "fwde-manage"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_2 = {
  key: 0,
  class: "fwde-manage__empty"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_3 = {
  class: "fwde-manage__heading"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_4 = {
  class: "fwde-manage__table"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_5 = {
  key: 0
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_6 = {
  key: 1
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_7 = {
  class: "fwde-visually-hidden"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_8 = ["data-id"];
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_9 = {
  class: "fwde-manage__name"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_10 = {
  class: "fwde-manage__kind"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_11 = {
  key: 0
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_12 = {
  key: 1
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_13 = ["aria-label", "value", "disabled", "onChange"];
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_14 = {
  value: ""
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_15 = ["value"];
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_16 = {
  key: 1
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_17 = {
  class: "fwde-manage__actions"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_18 = {
  class: "fwde-manage__confirm"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_19 = {
  key: 0,
  class: "fwde-manage__deletehint"
};
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_20 = ["disabled", "onClick"];
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_21 = ["disabled", "onClick"];
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_22 = ["onClick"];
const ManageSavedDialogvue_type_template_id_539073b4_hoisted_23 = {
  key: 1,
  class: "fwde-manage__error",
  role: "alert"
};
function ManageSavedDialogvue_type_template_id_539073b4_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ManageSavedDialogvue_type_template_id_539073b4_hoisted_1, [!_ctx.groups.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ManageSavedDialogvue_type_template_id_539073b4_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ManageEmpty')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", {
      key: group.key,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-manage__group', `fwde-manage__${group.key}`])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", ManageSavedDialogvue_type_template_id_539073b4_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(group.heading)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", ManageSavedDialogvue_type_template_id_539073b4_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Name')), 1), group.key === 'others' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", ManageSavedDialogvue_type_template_id_539073b4_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ColumnOwner')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ColumnChanged')), 1), _ctx.menuSections.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", ManageSavedDialogvue_type_template_id_539073b4_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ShowInMenu')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ManageSavedDialogvue_type_template_id_539073b4_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowActions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.items, item => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
        key: item.id,
        "data-id": item.id
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ManageSavedDialogvue_type_template_id_539073b4_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ManageSavedDialogvue_type_template_id_539073b4_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.kindText(item, group.key)), 1)]), group.key === 'others' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", ManageSavedDialogvue_type_template_id_539073b4_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item.owner), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item.updated.slice(0, 10)), 1), _ctx.menuSections.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", ManageSavedDialogvue_type_template_id_539073b4_hoisted_12, [item.kind === 'table' && item.canPlace && _ctx.place ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("select", {
        key: 0,
        class: "browser-default fwde-select fwde-manage__menu",
        "aria-label": _ctx.translate('FeinwerkDataExploration_ShowInMenu'),
        value: item.menu || '',
        disabled: _ctx.busy,
        onChange: $event => _ctx.changePlace(item, $event)
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", ManageSavedDialogvue_type_template_id_539073b4_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_MenuNone')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menuSections, section => {
        return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
          key: section.id,
          value: section.id
        }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(section.name), 9, ManageSavedDialogvue_type_template_id_539073b4_hoisted_15);
      }), 128))], 40, ManageSavedDialogvue_type_template_id_539073b4_hoisted_13)) : item.kind === 'table' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageSavedDialogvue_type_template_id_539073b4_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.sectionName(item.menu)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ManageSavedDialogvue_type_template_id_539073b4_hoisted_17, [_ctx.confirming === item.id ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: 0
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ManageSavedDialogvue_type_template_id_539073b4_hoisted_18, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(item.shared ? 'FeinwerkDataExploration_ManageSharedConfirm' : 'FeinwerkDataExploration_SavedDeleteConfirm', item.name)) + " ", 1), item.kind !== 'funnel' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ManageSavedDialogvue_type_template_id_539073b4_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_DeleteAlsoRemoves')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "fwde-button fwde-manage__confirm-yes",
        disabled: _ctx.busy,
        onClick: $event => _ctx.confirmDelete(item)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 9, ManageSavedDialogvue_type_template_id_539073b4_hoisted_20), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
        type: "button",
        class: "fwde-button fwde-manage__confirm-no",
        disabled: _ctx.busy,
        onClick: $event => _ctx.ask(null, item.id)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 9, ManageSavedDialogvue_type_template_id_539073b4_hoisted_21)], 64)) : item.canDelete ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
        key: 1,
        type: "button",
        class: "fwde-button fwde-manage__delete",
        onClick: $event => _ctx.ask(item.id, item.id)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 9, ManageSavedDialogvue_type_template_id_539073b4_hoisted_22)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])], 8, ManageSavedDialogvue_type_template_id_539073b4_hoisted_8);
    }), 128))])])], 2);
  }), 128)), _ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ManageSavedDialogvue_type_template_id_539073b4_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ManageSavedDialog.vue?vue&type=template&id=539073b4

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ManageSavedDialog.vue?vue&type=script&lang=ts


/* harmony default export */ var ManageSavedDialogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    saved: {
      type: Array,
      required: true
    },
    /** deletes one exploration; rejects with an Error whose message the dialog shows */
    remove: {
      type: Function,
      required: true
    },
    /** changes a shared exploration's menu place (Peter, 2026-11-24: admins may) */
    place: {
      type: Function,
      default: null
    },
    menuSections: {
      type: Array,
      default: () => []
    },
    /** the user's login: their own explorations come first, under "My explorations" */
    login: {
      type: String,
      default: ''
    }
  },
  emits: ['deleted', 'placed'],
  data() {
    return {
      confirming: null,
      busy: false,
      error: ''
    };
  },
  computed: {
    /** the sections with something in them: mine, then the shared ones of others I may change */
    groups() {
      const mine = this.saved.filter(item => item.owner === this.login);
      const others = this.saved.filter(item => item.owner !== this.login && item.shared && (item.canDelete || item.canPlace));
      const groups = [{
        key: 'mine',
        heading: 'FeinwerkDataExploration_MyExplorations',
        items: mine
      }, {
        key: 'others',
        heading: 'FeinwerkDataExploration_SharedByOthers',
        items: others
      }];
      return groups.filter(group => group.items.length > 0);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    /** the kind; among my own ones also whether it is shared (all of the others' ones are) */
    kindText(item, group) {
      const kind = Object(external_CoreHome_["translate"])(this.kindKey(item));
      return group === 'mine' && item.shared ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SavedShared', kind) : kind;
    },
    kindKey(item) {
      if (item.kind === 'funnel') {
        return 'FeinwerkDataExploration_Funnel';
      }
      return item.kind === 'kpi' ? 'FeinwerkDataExploration_SaveAsKpiCard' : 'FeinwerkDataExploration_FreeFormTable';
    },
    /**
     * Shows or hides the confirmation of one row; the focus goes along (to "Delete" in the
     * confirmation, or back to the row's "Delete"), so it never falls out of the panel.
     */
    ask(confirming, id) {
      this.confirming = confirming;
      this.$nextTick(() => {
        const row = this.$el.querySelector(`tr[data-id="${id}"]`);
        const target = row ? row.querySelector(confirming === null ? '.fwde-manage__delete' : '.fwde-manage__confirm-yes') : null;
        if (target) {
          target.focus();
        }
      });
    },
    sectionName(menu) {
      const section = this.menuSections.find(s => s.id === menu);
      return section ? section.name : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_MenuNone');
    },
    changePlace(item, event) {
      const menu = event.target.value;
      this.busy = true;
      this.error = '';
      this.place(item.id, menu).then(() => {
        this.$emit('placed', item.id);
      }).catch(e => {
        this.error = e instanceof Error ? e.message : String(e);
      }).finally(() => {
        this.busy = false;
      });
    },
    confirmDelete(item) {
      this.busy = true;
      this.error = '';
      this.remove(item.id).then(() => {
        this.confirming = null;
        this.$emit('deleted', item.id);
      }).catch(e => {
        this.error = e instanceof Error ? e.message : String(e);
      }).finally(() => {
        this.busy = false;
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ManageSavedDialog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ManageSavedDialog.vue



ManageSavedDialogvue_type_script_lang_ts.render = ManageSavedDialogvue_type_template_id_539073b4_render

/* harmony default export */ var ManageSavedDialog = (ManageSavedDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/saved.ts


const COMPARES = ['none', 'previous', 'weekday', 'average4'];
const LINES = [0, 7, 30];
const CHANGES = ['percent', 'absolute'];
/** a card's own keys, next to its exploration's */
const CARD_KEYS = ['fwdeKpiMetric', 'fwdeKpiCompare', 'fwdeKpiLine', 'fwdeKpiChange'];
const TABLE_KEYS = ['fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters', 'fwdeView', 'fwdeChart', 'fwdeChartType', 'fwdeMatch', 'fwdePivot', 'fwdePivotLimit'];
/** The hash keys of each kind of exploration. */
const KIND_KEYS = {
  table: TABLE_KEYS,
  funnel: ['fwdeSteps'],
  kpi: TABLE_KEYS.concat(CARD_KEYS)
};
/** Every key a saved exploration may set (fwdeKind: 'funnel' or 'kpi', absent for tables). */
const STATE_KEYS = [...KIND_KEYS.table, 'fwdeKind', ...KIND_KEYS.funnel, ...CARD_KEYS];
/** The page (Matomo subcategory) of each kind: a KPI card opens as its exploration. */
const SUBCATEGORY = {
  table: 'FeinwerkDataExploration_FreeFormTable',
  funnel: 'FeinwerkDataExploration_Funnel',
  kpi: 'FeinwerkDataExploration_FreeFormTable'
};
function savedKind(saved) {
  if (saved.state.fwdeKind === 'funnel') {
    return 'funnel';
  }
  return saved.state.fwdeKind === 'kpi' ? 'kpi' : 'table';
}
/** A card's state: its exploration's (without a menu place, a card has none) plus its settings. */
function withCard(state, card) {
  const rest = Object.assign({}, state);
  delete rest.fwdeMenu;
  return Object.assign(Object.assign({}, rest), {}, {
    fwdeKind: 'kpi',
    fwdeKpiMetric: card.metric,
    fwdeKpiCompare: card.compare,
    fwdeKpiLine: String(card.line),
    fwdeKpiChange: card.change
  });
}
/**
 * The save panel's start for a card: a saved card's settings (a metric the exploration no longer
 * has: its first one), the defaults otherwise (Peter, 2026-11-24: "ja, passt so").
 */
function cardDefaults(current, metricIds) {
  const state = current && savedKind(current) === 'kpi' ? current.state : {};
  const compare = COMPARES.find(c => c === state.fwdeKpiCompare) || 'average4';
  const line = LINES.includes(Number(state.fwdeKpiLine)) && state.fwdeKpiLine !== undefined ? Number(state.fwdeKpiLine) : 30;
  return {
    saveAs: current && savedKind(current) === 'kpi' ? 'kpi' : 'table',
    card: {
      metric: metricIds.includes(state.fwdeKpiMetric) ? state.fwdeKpiMetric : metricIds[0] || '',
      compare,
      line,
      change: CHANGES.find(c => c === state.fwdeKpiChange) || 'percent'
    }
  };
}
/**
 * The hash that opens a saved exploration on its page: the state of its kind (every other state
 * key emptied), its segment, its id and the page. The state is checked like any link when the page
 * reads it.
 */
function savedHashParams(saved) {
  const kind = savedKind(saved);
  // a card opens as its exploration: its own keys stay out of the link
  const linkKeys = KIND_KEYS[kind === 'kpi' ? 'table' : kind];
  const params = {};
  STATE_KEYS.forEach(key => {
    const value = saved.state[key];
    params[key] = linkKeys.includes(key) && typeof value === 'string' ? value : '';
  });
  params.segment = saved.segment || '';
  params.fwdeSaved = String(saved.id);
  params.subcategory = SUBCATEGORY[kind];
  return params;
}
/** The part of the page's hash parameters a save stores; a funnel also stores its kind. */
function stateToSave(params, kind = 'table') {
  const state = kind === 'funnel' ? {
    fwdeKind: 'funnel'
  } : {};
  KIND_KEYS[kind].forEach(key => {
    if (typeof params[key] === 'string' && params[key] !== '') {
      state[key] = params[key];
    }
  });
  return state;
}
/**
 * Whether the funnel page shows other steps or another segment than the saved funnel: both step
 * lists are read like a link.
 */
function funnelDiffersFromSaved(saved, currentSteps, currentSegment, goalIds, maxSteps) {
  const normalized = value => toStepsParam(parseSteps(value, goalIds, maxSteps));
  return normalized(currentSteps) !== normalized(saved.state.fwdeSteps) || (saved.segment || '') !== (currentSegment || '');
}
/** The user's own explorations and those shared by others, each in the API's order (by name). */
function groupSaved(list, login) {
  return {
    mine: list.filter(s => s.owner === login),
    shared: list.filter(s => s.owner !== login)
  };
}
/**
 * The saved exploration the link points to (fwdeSaved), or null; with a kind, only one of that
 * kind (Matomo's menu keeps the hash, so a table's id can reach the funnel page and the reverse).
 */
function findSaved(list, id, kind = null) {
  if (!id) {
    return null;
  }
  const found = list.find(s => String(s.id) === String(id)) || null;
  const kinds = kind === null || Array.isArray(kind) ? kind : [kind];
  return found && kinds && !kinds.includes(savedKind(found)) ? null : found;
}
/**
 * What the save dialog starts with: the open exploration's name and visibility when the user may
 * change it; a copy of someone else's exploration starts private, and so does every exploration
 * of a user who may not share (no write access).
 */
/** where a new saved exploration shows as a report (saved reports, 2026-11-24) */
const DEFAULT_MENU = 'FeinwerkDataExploration_Explorations';
function dialogDefaults(current, suggestedName, canShare) {
  if (current && current.canEdit) {
    return {
      name: current.name,
      shared: canShare && current.shared,
      menu: current.menu || ''
    };
  }
  return {
    name: (current ? current.name : suggestedName).slice(0, 100),
    shared: false,
    menu: DEFAULT_MENU
  };
}
/** The saved state with its menu place (never part of the page's link). */
function withMenu(state, menu) {
  return menu ? Object.assign(Object.assign({}, state), {}, {
    fwdeMenu: menu
  }) : state;
}
/**
 * Whether the page shows something other than the saved exploration (a later change, or an older
 * link): both states are read like a link, so defaults and removed dimensions compare alike.
 */
function differsFromSaved(saved, currentParams, currentSegment, catalog, limits) {
  const normalized = params => JSON.stringify(stateToSave(toHashParams(parseState(params, catalog, limits))));
  return normalized(currentParams) !== normalized(saved.state) || (saved.segment || '') !== (currentSegment || '');
}
/** A link that names a saved exploration but carries none of its settings (e.g. a bookmark). */
function linkNamesOnlySaved(hash) {
  return !!hash.fwdeSaved && STATE_KEYS.every(key => !hash[key]);
}
function copySupport(core) {
  const store = core.EntityDuplicatorStore;
  if (!core.EntityDuplicatorAction || !core.EntityDuplicatorModal || !store || typeof store.buildStoreInstance !== 'function') {
    return null;
  }
  const build = store.buildStoreInstance;
  return {
    action: core.EntityDuplicatorAction,
    modal: core.EntityDuplicatorModal,
    buildStore: (entityType, adapter) => build.call(store, entityType, adapter)
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=script&lang=ts






const {
  translate: SavedPickervue_type_script_lang_ts_translate
} = external_CoreHome_;
/* harmony default export */ var SavedPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ManageSavedDialog: ManageSavedDialog,
    SidePanel: SidePanel
  },
  props: {
    saved: {
      type: Array,
      required: true
    },
    current: {
      type: Object,
      default: null
    },
    login: {
      type: String,
      required: true
    },
    /** write access on some website: Matomo's copy dialog lists only those */
    canCopy: {
      type: Boolean,
      default: false
    },
    /** admin access to the website: also manage others' shared explorations (design 2026-11-20) */
    canManage: {
      type: Boolean,
      default: false
    },
    /** Matomo's sections a saved exploration can show in as a report (saved reports, 2026-11-24) */
    menuSections: {
      type: Array,
      default: () => []
    }
  },
  emits: ['open', 'deleted', 'changed'],
  data() {
    return {
      managing: false
    };
  },
  computed: {
    groups() {
      return groupSaved(this.saved, this.login);
    },
    /** admins manage the website's shared ones, everyone their own (Peter, 2026-11-25) */
    showManage() {
      return this.canManage || this.groups.mine.length > 0;
    },
    /** null on Matomo before 5.14: no copy action */
    copy() {
      return copySupport(external_CoreHome_);
    },
    copyStore() {
      return this.copy ? this.copy.buildStore(SavedPickervue_type_script_lang_ts_translate('FeinwerkDataExploration_SavedExplorationEntity'), {
        method: 'FeinwerkDataExploration.copySavedExploration'
      }) : null;
    }
  },
  methods: {
    /** the manage dialog's delete, with Matomo's error text as the message */
    removeSaved(id) {
      return deleteSaved(id).catch(e => {
        throw new Error(errorText(e));
      });
    },
    /** the manage dialog's menu place, with Matomo's error text as the message */
    placeSaved(id, menu) {
      return setSavedMenu(id, menu).catch(e => {
        throw new Error(errorText(e));
      });
    },
    translate: SavedPickervue_type_script_lang_ts_translate,
    ownedBy(saved) {
      return SavedPickervue_type_script_lang_ts_translate('FeinwerkDataExploration_SavedBy', this.displayName(saved), saved.owner);
    },
    /** funnels and KPI cards are named as such; tables by their name */
    displayName(saved) {
      const kind = savedKind(saved);
      if (kind === 'funnel') {
        return SavedPickervue_type_script_lang_ts_translate('FeinwerkDataExploration_SavedFunnel', saved.name);
      }
      return kind === 'kpi' ? SavedPickervue_type_script_lang_ts_translate('FeinwerkDataExploration_SavedKpiCard', saved.name) : saved.name;
    },
    onChange(event) {
      const id = event.target.value;
      this.$emit('open', this.saved.find(s => String(s.id) === id) || null);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue



SavedPickervue_type_script_lang_ts.render = SavedPickervue_type_template_id_7b2556fd_render

/* harmony default export */ var SavedPicker = (SavedPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/archiveShape.ts
/**
 * Archived saved explorations (design 2026-11-25). The parts of a saved state that shape the
 * archived data, as the server's version (Archive/ArchivedExploration.php): dimensions, metrics,
 * filters, goal, pivot dimension and segment. How it is shown (sort, row limit, view, chart, menu
 * place, pivot limit) keeps the archive and its history.
 */
const SHAPE_KEYS = ['fwdeDims', 'fwdeMetrics', 'fwdeFilters', 'fwdeMatch', 'fwdeGoal', 'fwdePivot'];
function archiveShape(state, segment) {
  return JSON.stringify([...SHAPE_KEYS.map(key => {
    var _state$key;
    return String((_state$key = state[key]) !== null && _state$key !== void 0 ? _state$key : '');
  }), segment || '']);
}
/**
 * What the page offers for a period an exploration cannot cover: the report of an archived one,
 * opened from its menu place ("open"), or the hint to give it one ("place"); nothing for others.
 */
function archivedReportHint(saved) {
  if (!saved || !saved.archived || saved.kind !== 'table') {
    return '';
  }
  return saved.menu ? 'open' : 'place';
}
/**
 * The first day a changed archived exploration's history reaches again, for the save panel's
 * warning (final review 2026-11-26): '' while the shape is unchanged, for explorations that are
 * not archived, and when nothing is lost ($from null: archiving off or a young website).
 */
function historyLostFrom(saved, state, segment, from) {
  if (!saved || !saved.archived || !from) {
    return '';
  }
  return archiveShape(state, segment) === archiveShape(saved.state, saved.segment || '') ? '' : from;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=template&id=b495f440

const SaveDialogvue_type_template_id_b495f440_hoisted_1 = {
  class: "fwde-savedialog"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_2 = ["aria-expanded"];
const SaveDialogvue_type_template_id_b495f440_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-save",
  "aria-hidden": "true"
}, null, -1);
const SaveDialogvue_type_template_id_b495f440_hoisted_4 = {
  key: 0,
  class: "fwde-savedialog__note"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_5 = {
  class: "fwde-field__label",
  for: "fwde-savedialog-name"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_6 = {
  key: 1,
  class: "fwde-savedialog__kind"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_7 = {
  class: "fwde-field__label"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_8 = {
  key: 2,
  class: "fwde-savedialog__card"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_9 = {
  class: "fwde-field__label",
  for: "fwde-savedialog-kpimetric"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_10 = ["value"];
const SaveDialogvue_type_template_id_b495f440_hoisted_11 = {
  class: "fwde-field__label",
  for: "fwde-savedialog-kpicompare"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_12 = ["value"];
const SaveDialogvue_type_template_id_b495f440_hoisted_13 = {
  class: "fwde-field__label"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_14 = {
  class: "fwde-field__label"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_15 = {
  key: 3,
  class: "fwde-savedialog__place"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_16 = {
  class: "fwde-field__label",
  for: "fwde-savedialog-menu"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_17 = {
  value: ""
};
const SaveDialogvue_type_template_id_b495f440_hoisted_18 = ["value"];
const SaveDialogvue_type_template_id_b495f440_hoisted_19 = {
  class: "fwde-savedialog__visibility"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_20 = {
  class: "fwde-field__label"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_21 = {
  class: "fwde-savedialog__option"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_22 = {
  class: "fwde-list__label"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_23 = ["disabled"];
const SaveDialogvue_type_template_id_b495f440_hoisted_24 = {
  class: "fwde-list__label"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_25 = {
  key: 0,
  class: "fwde-savedialog__note"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_26 = {
  key: 4,
  class: "fwde-savedialog__error",
  role: "alert"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_27 = {
  key: 5,
  class: "fwde-savedialog__delete"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_28 = ["disabled"];
const SaveDialogvue_type_template_id_b495f440_hoisted_29 = {
  key: 0,
  class: "fwde-savedialog__deletehint"
};
const SaveDialogvue_type_template_id_b495f440_hoisted_30 = ["disabled"];
const _hoisted_31 = {
  key: 6,
  class: "alert alert-warning fwde-savedialog__history",
  role: "note"
};
const _hoisted_32 = ["type", "form", "disabled"];
const _hoisted_33 = ["type", "form", "disabled"];
const _hoisted_34 = ["disabled"];
function SaveDialogvue_type_template_id_b495f440_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SegmentedSwitch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentedSwitch");
  const _component_SidePanel = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SidePanel");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    ref: "toggle",
    type: "button",
    class: "fwde-button fwde-savedialog__toggle",
    "aria-haspopup": "dialog",
    "aria-expanded": _ctx.open ? 'true' : 'false',
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.toggle && _ctx.toggle(...args))
  }, [SaveDialogvue_type_template_id_b495f440_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Save')), 1)], 8, SaveDialogvue_type_template_id_b495f440_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SidePanel, {
    open: _ctx.open,
    title: _ctx.translate('FeinwerkDataExploration_SaveExploration'),
    "close-label": _ctx.translate('General_Close'),
    "scope-class": "fwde-page fwde-savepanel",
    "onUpdate:open": _ctx.onPanel
  }, {
    footer: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-button fwde-savedialog__cancel",
      onClick: _cache[14] || (_cache[14] = $event => _ctx.close())
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1), _ctx.canChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: _ctx.historyLostFrom ? 'submit' : 'button',
      form: _ctx.historyLostFrom ? 'fwde-savedialog-form' : undefined,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([_ctx.historyLostFrom ? 'btn' : 'fwde-button', "fwde-savedialog__asnew"]),
      disabled: _ctx.busy,
      onClick: _cache[15] || (_cache[15] = $event => _ctx.historyLostFrom ? undefined : _ctx.save(true))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SaveAsNew')), 11, _hoisted_32), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: _ctx.historyLostFrom ? 'button' : 'submit',
      form: _ctx.historyLostFrom ? undefined : 'fwde-savedialog-form',
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([_ctx.historyLostFrom ? 'fwde-button' : 'btn', "fwde-savedialog__changes"]),
      disabled: _ctx.busy,
      onClick: _cache[16] || (_cache[16] = $event => _ctx.historyLostFrom ? _ctx.save(false) : undefined)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SaveChanges')), 11, _hoisted_33)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 1,
      type: "submit",
      form: "fwde-savedialog-form",
      class: "btn fwde-savedialog__new",
      disabled: _ctx.busy
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Save')), 9, _hoisted_34))]),
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("form", {
      id: "fwde-savedialog-form",
      class: "fwde-savedialog__form",
      onSubmit: _cache[13] || (_cache[13] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.save(!_ctx.canChange || !!_ctx.historyLostFrom), ["prevent"]))
    }, [_ctx.current && !_ctx.current.canEdit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SaveDialogvue_type_template_id_b495f440_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedNotYours', _ctx.current.owner)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_b495f440_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedName')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      id: "fwde-savedialog-name",
      ref: "name",
      "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.name = $event),
      type: "text",
      class: "browser-default fwde-input",
      maxlength: "100",
      required: ""
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.name]]), _ctx.metrics.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SaveAs')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
      options: _ctx.kindOptions,
      "model-value": _ctx.saveAs,
      label: _ctx.translate('FeinwerkDataExploration_SaveAs'),
      "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.saveAs = $event)
    }, null, 8, ["options", "model-value", "label"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.saveAs === 'kpi' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_b495f440_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_KpiMetric')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
      id: "fwde-savedialog-kpimetric",
      "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.card.metric = $event),
      class: "browser-default fwde-select"
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metrics, metric => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: metric.id,
        value: metric.id
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(metric.name), 9, SaveDialogvue_type_template_id_b495f440_hoisted_10);
    }), 128))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelSelect"], _ctx.card.metric]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_b495f440_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_KpiCompare')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
      id: "fwde-savedialog-kpicompare",
      "onUpdate:modelValue": _cache[4] || (_cache[4] = $event => _ctx.card.compare = $event),
      class: "browser-default fwde-select"
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.compareOptions, option => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: option.value,
        value: option.value
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.label), 9, SaveDialogvue_type_template_id_b495f440_hoisted_12);
    }), 128))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelSelect"], _ctx.card.compare]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_KpiLine')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
      options: _ctx.lineOptions,
      "model-value": String(_ctx.card.line),
      label: _ctx.translate('FeinwerkDataExploration_KpiLine'),
      "onUpdate:modelValue": _cache[5] || (_cache[5] = $event => _ctx.card.line = Number($event))
    }, null, 8, ["options", "model-value", "label"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_KpiChange')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SegmentedSwitch, {
      options: _ctx.changeOptions,
      "model-value": _ctx.card.change,
      label: _ctx.translate('FeinwerkDataExploration_KpiChange'),
      "onUpdate:modelValue": _cache[6] || (_cache[6] = $event => _ctx.card.change = $event)
    }, null, 8, ["options", "model-value", "label"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.menuSections.length && _ctx.saveAs !== 'kpi' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_15, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_b495f440_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ShowInMenu')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
      id: "fwde-savedialog-menu",
      "onUpdate:modelValue": _cache[7] || (_cache[7] = $event => _ctx.menu = $event),
      class: "browser-default fwde-select fwde-savedialog__menu"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", SaveDialogvue_type_template_id_b495f440_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_MenuNone')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.menuSections, section => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: section.id,
        value: section.id
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(section.name), 9, SaveDialogvue_type_template_id_b495f440_hoisted_18);
    }), 128))], 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelSelect"], _ctx.menu]])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("fieldset", SaveDialogvue_type_template_id_b495f440_hoisted_19, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("legend", SaveDialogvue_type_template_id_b495f440_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedVisibility')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_b495f440_hoisted_21, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      "onUpdate:modelValue": _cache[8] || (_cache[8] = $event => _ctx.shared = $event),
      type: "radio",
      name: "fwde-savedialog-shared",
      value: false
    }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.shared]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SaveDialogvue_type_template_id_b495f440_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_VisibilityPrivate')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-savedialog__option", {
        'fwde-savedialog__option--disabled': !_ctx.canShare
      }])
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      "onUpdate:modelValue": _cache[9] || (_cache[9] = $event => _ctx.shared = $event),
      type: "radio",
      name: "fwde-savedialog-shared",
      value: true,
      disabled: !_ctx.canShare
    }, null, 8, SaveDialogvue_type_template_id_b495f440_hoisted_23), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.shared]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SaveDialogvue_type_template_id_b495f440_hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_VisibilityShared')), 1)], 2), !_ctx.canShare ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SaveDialogvue_type_template_id_b495f440_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedShareNeedsWrite')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SaveDialogvue_type_template_id_b495f440_hoisted_26, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.canChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveDialogvue_type_template_id_b495f440_hoisted_27, [!_ctx.confirmDelete ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      ref: "deleteAsk",
      type: "button",
      class: "fwde-button fwde-savedialog__deleteask",
      disabled: _ctx.busy,
      onClick: _cache[10] || (_cache[10] = $event => _ctx.askDelete(true))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 9, SaveDialogvue_type_template_id_b495f440_hoisted_28)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedDeleteConfirm', _ctx.currentName)), 1), _ctx.current && _ctx.savedKind(_ctx.current) !== 'funnel' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SaveDialogvue_type_template_id_b495f440_hoisted_29, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_DeleteAlsoRemoves')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      ref: "deleteYes",
      type: "button",
      class: "fwde-button fwde-savedialog__deleteyes",
      disabled: _ctx.busy,
      onClick: _cache[11] || (_cache[11] = $event => _ctx.$emit('delete'))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 9, SaveDialogvue_type_template_id_b495f440_hoisted_30), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-button fwde-savedialog__deleteno",
      onClick: _cache[12] || (_cache[12] = $event => _ctx.askDelete(false))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1)], 64))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.canChange && _ctx.historyLostFrom ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_31, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ArchiveHistoryLost', _ctx.historyLostFrom)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 32)]),
    _: 1
  }, 8, ["open", "title", "close-label", "onUpdate:open"])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=template&id=b495f440

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=script&lang=ts





/* harmony default export */ var SaveDialogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    SidePanel: SidePanel,
    SegmentedSwitch: SegmentedSwitch
  },
  props: {
    current: {
      type: Object,
      default: null
    },
    /** the name a new exploration gets suggested (the result title) */
    suggestedName: {
      type: String,
      required: true
    },
    /** at least write access: may share with the website's users */
    canShare: {
      type: Boolean,
      default: false
    },
    busy: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    /** Matomo's sections a saved exploration can show in as a report; none: no choice (funnels) */
    menuSections: {
      type: Array,
      default: () => []
    },
    /** the exploration's metrics with their names: with them it can be saved as a KPI card too */
    metrics: {
      type: Array,
      default: () => []
    },
    /**
     * an archived exploration whose change starts its history again, where raw data is deleted:
     * the first day that can be rebuilt (archived saved explorations, 2026-11-25); empty otherwise
     */
    historyLostFrom: {
      type: String,
      default: ''
    }
  },
  emits: ['save', 'delete', 'open'],
  data() {
    return {
      open: false,
      name: '',
      shared: false,
      menu: '',
      confirmDelete: false,
      /** an exploration or a KPI card (2026-11-24) */
      saveAs: 'table',
      card: {
        metric: '',
        compare: 'average4',
        line: 30,
        change: 'percent'
      },
      /**
       * the name the panel suggested last; while the name is still it, it follows the kind and
       * the card's metric
       */
      autoName: ''
    };
  },
  computed: {
    currentName() {
      return this.current ? this.current.name : '';
    },
    /** "Save changes" keeps the kind: a table never turns into a card or back (then: a new save) */
    canChange() {
      if (!this.current || !this.current.canEdit) {
        return false;
      }
      return !this.metrics.length || savedKind(this.current) === this.saveAs;
    },
    kindOptions() {
      return [{
        value: 'table',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SaveAsExploration')
      }, {
        value: 'kpi',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SaveAsKpiCard')
      }];
    },
    compareOptions() {
      return [{
        value: 'average4',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiCompareAverage4')
      }, {
        value: 'weekday',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiCompareWeekday')
      }, {
        value: 'previous',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiComparePrevious')
      }, {
        value: 'none',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiCompareNone')
      }];
    },
    lineOptions() {
      return [{
        value: '0',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiLineOff')
      }, {
        value: '7',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiLine7')
      }, {
        value: '30',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiLine30')
      }];
    },
    changeOptions() {
      return [{
        value: 'percent',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiChangePercent')
      }, {
        value: 'absolute',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiChangeAbsolute')
      }];
    }
  },
  watch: {
    // UI review 2026-11-24 (F6): a new card is named after its metric, a name the user typed stays
    saveAs() {
      this.suggestName();
    },
    'card.metric': function cardMetric() {
      this.suggestName();
    },
    // a finished save or delete closes the dialog
    busy(isBusy, wasBusy) {
      if (wasBusy && !isBusy && !this.error) {
        this.close();
      }
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    toggle() {
      if (this.open) {
        this.close();
        return;
      }
      ({
        name: this.name,
        shared: this.shared,
        menu: this.menu
      } = dialogDefaults(this.current, this.suggestedName, this.canShare));
      ({
        saveAs: this.saveAs,
        card: this.card
      } = cardDefaults(this.current, this.metrics.map(metric => metric.id)));
      // a saved exploration keeps its name; a new one starts with the suggestion
      this.autoName = this.current ? '' : this.name;
      this.confirmDelete = false;
      this.open = true;
      this.$emit('open');
      this.$nextTick(() => {
        const input = this.$refs.name;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },
    savedKind: savedKind,
    /**
     * while the name is still the panel's suggestion: the card's metric for a KPI card, else the
     * result title
     */
    suggestName() {
      if (!this.open || this.name !== this.autoName) {
        return;
      }
      const metric = this.metrics.find(m => m.id === this.card.metric);
      this.name = this.saveAs === 'kpi' && metric ? metric.name : this.suggestedName;
      this.autoName = this.name;
    },
    /** closes the panel; focus back on Save (also where a click does not focus a button) */
    close() {
      this.open = false;
      const toggle = this.$refs.toggle;
      if (toggle) {
        toggle.focus();
      }
    },
    /** Delete swaps for its confirmation and back; the focus goes along (it stays in the panel) */
    askDelete(ask) {
      this.confirmDelete = ask;
      this.$nextTick(() => {
        const target = this.$refs[ask ? 'deleteYes' : 'deleteAsk'];
        if (target) {
          target.focus();
        }
      });
    },
    /** the panel asks to close: Escape, the dimmed page or its close button */
    onPanel(isOpen) {
      if (!isOpen) {
        this.close();
      }
    },
    save(asNew) {
      if (this.name.trim() === '') {
        return;
      }
      const event = {
        name: this.name.trim(),
        shared: this.shared,
        asNew,
        menu: this.menuSections.length && this.saveAs !== 'kpi' ? this.menu : ''
      };
      if (this.saveAs === 'kpi') {
        event.card = Object.assign({}, this.card);
      }
      this.$emit('save', event);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue



SaveDialogvue_type_script_lang_ts.render = SaveDialogvue_type_template_id_b495f440_render

/* harmony default export */ var SaveDialog = (SaveDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue?vue&type=template&id=571877ce

const FeedbackBarvue_type_template_id_571877ce_hoisted_1 = {
  key: 0,
  class: "fwde-feedback"
};
const FeedbackBarvue_type_template_id_571877ce_hoisted_2 = ["title"];
const FeedbackBarvue_type_template_id_571877ce_hoisted_3 = ["title", "aria-label", "onClick"];
const FeedbackBarvue_type_template_id_571877ce_hoisted_4 = ["d"];
const FeedbackBarvue_type_template_id_571877ce_hoisted_5 = {
  key: 1,
  class: "fwde-feedback__thanks",
  role: "status"
};
const FeedbackBarvue_type_template_id_571877ce_hoisted_6 = ["href", "title"];
const FeedbackBarvue_type_template_id_571877ce_hoisted_7 = {
  viewBox: "0 0 24 24",
  width: "14",
  height: "14",
  "aria-hidden": "true",
  class: "fwde-feedback__help"
};
const FeedbackBarvue_type_template_id_571877ce_hoisted_8 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("circle", {
  cx: "12",
  cy: "12",
  r: "9"
}, null, -1);
const FeedbackBarvue_type_template_id_571877ce_hoisted_9 = ["d"];
function FeedbackBarvue_type_template_id_571877ce_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.feedback.enabled || _ctx.feedback.supportUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FeedbackBarvue_type_template_id_571877ce_hoisted_1, [_ctx.feedback.enabled && !_ctx.vote ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "fwde-feedback__question",
    title: _ctx.question
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.question), 9, FeedbackBarvue_type_template_id_571877ce_hoisted_2), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.choices, choice => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: choice,
      type: "button",
      class: "fwde-feedback__thumb",
      title: _ctx.label(choice),
      "aria-label": _ctx.label(choice),
      onClick: $event => _ctx.send(choice)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
      viewBox: "0 0 24 24",
      width: "14",
      height: "14",
      "aria-hidden": "true",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`fwde-feedback__icon--${choice}`)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
      d: _ctx.thumbPath
    }, null, 8, FeedbackBarvue_type_template_id_571877ce_hoisted_4)], 2))], 8, FeedbackBarvue_type_template_id_571877ce_hoisted_3);
  }), 128))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.feedback.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", FeedbackBarvue_type_template_id_571877ce_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.thanks), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.feedback.supportUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 2,
    class: "fwde-feedback__support",
    href: _ctx.feedback.supportUrl,
    target: "_blank",
    rel: "noopener noreferrer",
    title: _ctx.tr('SupportTitle')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", FeedbackBarvue_type_template_id_571877ce_hoisted_7, [FeedbackBarvue_type_template_id_571877ce_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    d: _ctx.helpPath
  }, null, 8, FeedbackBarvue_type_template_id_571877ce_hoisted_9)])), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('Support')), 1)], 8, FeedbackBarvue_type_template_id_571877ce_hoisted_6)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue?vue&type=template&id=571877ce

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/feedback.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const FEEDBACK_PLUGIN = 'FeinwerkDataExploration';
function storageKey(topic) {
  return `${FEEDBACK_PLUGIN}.feedback.${topic}`;
}
/** This browser's vote on the question for this plugin version (a convenience only), or ''. */
function storedVote(topic, version, storage) {
  try {
    const stored = JSON.parse(storage.getItem(storageKey(topic)) || '{}');
    return stored && stored.version === version && (stored.vote === 'up' || stored.vote === 'down') ? stored.vote : '';
  } catch (e) {
    return '';
  }
}
function rememberVote(topic, version, vote, storage) {
  try {
    storage.setItem(storageKey(topic), JSON.stringify({
      version,
      vote
    }));
  } catch (e) {
    // private mode or blocked storage: the thanks is shown anyway
  }
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue?vue&type=script&lang=ts



/** a thumb outline (24 x 24); the down thumb is the same, rotated */
const THUMB_PATH = 'M3 10h4v10H3zM7 10l4-7c1.3 0 2.2 1.1 2 2.4L12.4 9H19a2 2 0 0 1 2 2.3l-1.2 7A2 2 0 0 1 ' + '17.8 20H7';
/** question mark inside the help circle (24 x 24) */
const HELP_PATH = 'M9.5 9.5a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .8-1 1.5v.7M12 17h.01';
function browserStorage() {
  try {
    return window.localStorage;
  } catch (e) {
    return null;
  }
}
/* harmony default export */ var FeedbackBarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    idSite: {
      type: Number,
      required: true
    },
    /** the store "topic": one of the plugin's PluginFeedback::TOPICS */
    topic: {
      type: String,
      required: true
    },
    /** the question, already translated */
    question: {
      type: String,
      required: true
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    }
  },
  data() {
    const storage = browserStorage();
    return {
      vote: storage ? storedVote(this.topic, this.feedback.version, storage) : ''
    };
  },
  computed: {
    choices() {
      return ['up', 'down'];
    },
    thumbPath() {
      return THUMB_PATH;
    },
    helpPath() {
      return HELP_PATH;
    },
    /** after a vote the thanks takes the place of question and thumbs (one line); empty before */
    thanks() {
      if (!this.vote) {
        return '';
      }
      return this.vote === 'up' ? this.tr('FeedbackThanks') : this.tr('FeedbackThanksDown');
    }
  },
  methods: {
    tr(key) {
      return Object(external_CoreHome_["translate"])(`${FEEDBACK_PLUGIN}_${key}`);
    },
    label(choice) {
      return choice === 'up' ? this.tr('FeedbackYes') : this.tr('FeedbackNo');
    },
    send(vote) {
      this.vote = vote;
      const storage = browserStorage();
      if (storage) {
        rememberVote(this.topic, this.feedback.version, vote, storage);
      }
      // Best effort: the vote is a hint for Feinwerk, a failed request needs no error message.
      external_CoreHome_["AjaxHelper"].post({
        method: `${FEEDBACK_PLUGIN}.sendFeedback`,
        idSite: this.idSite
      }, {
        topic: this.topic,
        vote
      }, {
        createErrorNotification: false
      }).catch(() => undefined);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue



FeedbackBarvue_type_script_lang_ts.render = FeedbackBarvue_type_template_id_571877ce_render

/* harmony default export */ var FeedbackBar = (FeedbackBarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PromoLine.vue?vue&type=template&id=719f49b0

const PromoLinevue_type_template_id_719f49b0_hoisted_1 = ["data-fwpromo-id", "data-fwpromo-days", "data-fwpromo-demo"];
const PromoLinevue_type_template_id_719f49b0_hoisted_2 = ["title"];
const PromoLinevue_type_template_id_719f49b0_hoisted_3 = ["href", "target", "rel", "title"];
const PromoLinevue_type_template_id_719f49b0_hoisted_4 = {
  class: "fwpromo-line__link-full"
};
const PromoLinevue_type_template_id_719f49b0_hoisted_5 = {
  class: "fwpromo-line__link-short"
};
const PromoLinevue_type_template_id_719f49b0_hoisted_6 = ["title", "aria-label"];
const PromoLinevue_type_template_id_719f49b0_hoisted_7 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  "aria-hidden": "true"
}, "×", -1);
const PromoLinevue_type_template_id_719f49b0_hoisted_8 = [PromoLinevue_type_template_id_719f49b0_hoisted_7];
function PromoLinevue_type_template_id_719f49b0_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_FeinwerkLogo = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeinwerkLogo");
  return _ctx.promo ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "fwpromo-line",
    "data-fwpromo-id": _ctx.promo.id,
    "data-fwpromo-placement": "line",
    "data-fwpromo-days": _ctx.promo.dismissDays,
    "data-fwpromo-demo": _ctx.promo.demo ? '1' : undefined
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FeinwerkLogo), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "fwpromo-line__text",
    title: _ctx.promo.text
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.promo.text), 9, PromoLinevue_type_template_id_719f49b0_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "fwpromo-line__link",
    href: _ctx.promo.url,
    target: _ctx.promo.external === false ? undefined : '_blank',
    rel: _ctx.promo.external === false ? undefined : 'noopener',
    title: _ctx.linkTitle || undefined
  }, [_ctx.promo.linkShort ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PromoLinevue_type_template_id_719f49b0_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.promo.link), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", PromoLinevue_type_template_id_719f49b0_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.promo.linkShort), 1)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.promo.link), 1)], 64))], 8, PromoLinevue_type_template_id_719f49b0_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwpromo-line__dismiss fwpromo-dismiss",
    title: _ctx.dismissLabel,
    "aria-label": _ctx.dismissLabel
  }, PromoLinevue_type_template_id_719f49b0_hoisted_8, 8, PromoLinevue_type_template_id_719f49b0_hoisted_6)], 8, PromoLinevue_type_template_id_719f49b0_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PromoLine.vue?vue&type=template&id=719f49b0

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FeinwerkLogo.vue?vue&type=template&id=04b7d090

const FeinwerkLogovue_type_template_id_04b7d090_hoisted_1 = {
  class: "fwlogo",
  "aria-hidden": "true"
};
const FeinwerkLogovue_type_template_id_04b7d090_hoisted_2 = {
  class: "fwlogo__mark",
  viewBox: "0 0 48 48",
  width: "18",
  height: "18",
  focusable: "false"
};
const FeinwerkLogovue_type_template_id_04b7d090_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("circle", {
  class: "fwlogo__core",
  cx: "24",
  cy: "24",
  r: "5.4"
}, null, -1);
const FeinwerkLogovue_type_template_id_04b7d090_hoisted_4 = ["opacity"];
const FeinwerkLogovue_type_template_id_04b7d090_hoisted_5 = ["cx", "cy", "r"];
const FeinwerkLogovue_type_template_id_04b7d090_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "fwlogo__word"
}, [/*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])("feinwerk"), /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "fwlogo__sub"
}, " plugins")], -1);
function FeinwerkLogovue_type_template_id_04b7d090_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", FeinwerkLogovue_type_template_id_04b7d090_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", FeinwerkLogovue_type_template_id_04b7d090_hoisted_2, [FeinwerkLogovue_type_template_id_04b7d090_hoisted_3, (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.LOGO_RINGS, ring => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: ring.opacity,
      class: "fwlogo__dots",
      opacity: ring.opacity
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(ring.dots, ([cx, cy], n) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("circle", {
        key: n,
        cx: cx,
        cy: cy,
        r: ring.r
      }, null, 8, FeinwerkLogovue_type_template_id_04b7d090_hoisted_5);
    }), 128))], 8, FeinwerkLogovue_type_template_id_04b7d090_hoisted_4);
  }), 128))])), FeinwerkLogovue_type_template_id_04b7d090_hoisted_6]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeinwerkLogo.vue?vue&type=template&id=04b7d090

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FeinwerkLogo.vue?vue&type=script&lang=ts

/** the dots of the store's feinwerk-plugins-mark.svg, as in the kit's _feinwerkLogo.twig */
const LOGO_RINGS = [{
  opacity: '0.9',
  r: '2.9',
  dots: [[16, 24], [32, 24], [24, 16], [24, 32]]
}, {
  opacity: '0.55',
  r: '2.5',
  dots: [[16, 16], [32, 16], [16, 32], [32, 32]]
}, {
  opacity: '0.35',
  r: '2.1',
  dots: [[8, 24], [40, 24], [24, 8], [24, 40]]
}];
/* harmony default export */ var FeinwerkLogovue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  data() {
    return {
      LOGO_RINGS
    };
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeinwerkLogo.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeinwerkLogo.vue



FeinwerkLogovue_type_script_lang_ts.render = FeinwerkLogovue_type_template_id_04b7d090_render

/* harmony default export */ var FeinwerkLogo = (FeinwerkLogovue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PromoLine.vue?vue&type=script&lang=ts


/* harmony default export */ var PromoLinevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FeinwerkLogo: FeinwerkLogo
  },
  props: {
    promo: {
      type: Object,
      default: null
    },
    dismissLabel: {
      type: String,
      required: true
    }
  },
  computed: {
    /** the admin note; with a short form the full link text first (kit _promo.twig, version 6) */
    linkTitle() {
      if (!this.promo) {
        return '';
      }
      const note = this.promo.note || '';
      if (!this.promo.linkShort) {
        return note;
      }
      return note ? `${this.promo.link}. ${note}` : this.promo.link;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PromoLine.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PromoLine.vue



PromoLinevue_type_script_lang_ts.render = PromoLinevue_type_template_id_719f49b0_render

/* harmony default export */ var PromoLine = (PromoLinevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PremiumBanner.vue?vue&type=template&id=6c811999

const PremiumBannervue_type_template_id_6c811999_hoisted_1 = {
  key: 0,
  class: "fwde-premium-slot"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_2 = ["data-fwpromo-id", "data-fwpromo-days", "data-fwpromo-demo", "aria-label"];
const PremiumBannervue_type_template_id_6c811999_hoisted_3 = {
  class: "fwde-premium__head"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_4 = {
  key: 0,
  class: "fwde-premium__art"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_5 = ["src"];
const PremiumBannervue_type_template_id_6c811999_hoisted_6 = {
  class: "fwde-premium__intro"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_7 = {
  class: "fwde-premium__label"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_8 = {
  class: "fwde-premium__name"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_9 = ["title"];
const PremiumBannervue_type_template_id_6c811999_hoisted_10 = {
  key: 1,
  class: "fwde-premium__note"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_11 = {
  class: "fwde-premium__actions"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_12 = ["href", "target", "rel"];
const PremiumBannervue_type_template_id_6c811999_hoisted_13 = {
  key: 0,
  class: "fwde-premium__price"
};
const PremiumBannervue_type_template_id_6c811999_hoisted_14 = ["title", "aria-label"];
const PremiumBannervue_type_template_id_6c811999_hoisted_15 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  "aria-hidden": "true"
}, "×", -1);
const PremiumBannervue_type_template_id_6c811999_hoisted_16 = [PremiumBannervue_type_template_id_6c811999_hoisted_15];
function PremiumBannervue_type_template_id_6c811999_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_FeinwerkLogo = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeinwerkLogo");
  return _ctx.premium && _ctx.premium.plugins.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_1, [_cache[0] || (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["setBlockTracking"])(-1), _cache[0] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", Object(external_commonjs_vue_commonjs2_vue_root_Vue_["mergeProps"])({
    class: "fwde-premium"
  }, _ctx.placeAttributes), [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.premium.plugins, plugin => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", {
      key: plugin.id,
      class: "fwde-premium__item",
      "data-fwpromo-id": plugin.id,
      "data-fwpromo-days": _ctx.premium.days,
      "data-fwpromo-demo": _ctx.premium.demo ? '1' : undefined,
      "aria-label": _ctx.label
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_3, [plugin.image ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PremiumBannervue_type_template_id_6c811999_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("img", {
      src: plugin.image,
      alt: "",
      loading: "lazy"
    }, null, 8, PremiumBannervue_type_template_id_6c811999_hoisted_5)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.name), 1)])]), plugin.description ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: 0,
      class: "fwde-premium__text",
      title: plugin.description
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.description), 9, PremiumBannervue_type_template_id_6c811999_hoisted_9)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), plugin.external === '1' && _ctx.premium.note ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.premium.note), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", PremiumBannervue_type_template_id_6c811999_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "fwde-premium__cta",
      href: plugin.url,
      target: plugin.external === '1' ? '_blank' : undefined,
      rel: plugin.external === '1' ? 'noopener' : undefined
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.moreLabel) + " →", 9, PremiumBannervue_type_template_id_6c811999_hoisted_12), plugin.price ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", PremiumBannervue_type_template_id_6c811999_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(plugin.price), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FeinwerkLogo)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-premium__close",
      "data-fwpromo-close": "",
      title: _ctx.dismissLabel,
      "aria-label": _ctx.dismissLabel
    }, PremiumBannervue_type_template_id_6c811999_hoisted_16, 8, PremiumBannervue_type_template_id_6c811999_hoisted_14)], 8, PremiumBannervue_type_template_id_6c811999_hoisted_2);
  }), 128))], 16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["setBlockTracking"])(1), _cache[0])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PremiumBanner.vue?vue&type=template&id=6c811999

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/PremiumBanner.vue?vue&type=script&lang=ts


/* harmony default export */ var PremiumBannervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FeinwerkLogo: FeinwerkLogo
  },
  props: {
    premium: {
      type: Object,
      default: null
    },
    label: {
      type: String,
      required: true
    },
    moreLabel: {
      type: String,
      required: true
    },
    dismissLabel: {
      type: String,
      required: true
    }
  },
  computed: {
    /**
     * a pausable place, or in demo mode (pauseDays 0) the plain rotation group (kit
     * _premiumBanner.twig)
     */
    placeAttributes() {
      const pauseDays = this.premium ? this.premium.pauseDays : 0;
      return pauseDays > 0 ? {
        'data-fwpromo-pausable': 'banner',
        'data-fwpromo-pause': 'fwde-banner',
        'data-fwpromo-pause-days': String(pauseDays)
      } : {
        'data-fwpromo-rotation': 'banner'
      };
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PremiumBanner.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/PremiumBanner.vue



PremiumBannervue_type_script_lang_ts.render = PremiumBannervue_type_template_id_6c811999_render

/* harmony default export */ var PremiumBanner = (PremiumBannervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=template&id=3f67992e

const ResultChartvue_type_template_id_3f67992e_hoisted_1 = {
  ref: "root",
  class: "fwde-chart"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_2 = {
  class: "fwde-chart__header"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_3 = {
  class: "fwde-chart__metric"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_4 = {
  class: "fwde-field__label"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_5 = ["value"];
const ResultChartvue_type_template_id_3f67992e_hoisted_6 = ["value"];
const ResultChartvue_type_template_id_3f67992e_hoisted_7 = {
  key: 1,
  class: "fwde-chart__note"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_8 = {
  key: 1,
  class: "fwde-chart__donut"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_9 = ["aria-label"];
const ResultChartvue_type_template_id_3f67992e_hoisted_10 = ["d"];
const ResultChartvue_type_template_id_3f67992e_hoisted_11 = {
  class: "fwde-chart__donut-total",
  x: "100",
  y: "93",
  "text-anchor": "middle",
  "dominant-baseline": "central"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_12 = {
  class: "fwde-chart__donut-metric",
  x: "100",
  y: "118",
  "text-anchor": "middle",
  "dominant-baseline": "central"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_13 = {
  class: "fwde-chart__donut-legend"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_14 = {
  class: "fwde-chart__donut-name"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_15 = {
  class: "fwde-chart__donut-value"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_16 = {
  class: "fwde-chart__donut-share"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_17 = {
  key: 2,
  class: "fwde-chart__plot"
};
const ResultChartvue_type_template_id_3f67992e_hoisted_18 = ["width", "height", "viewBox", "aria-label", "tabindex"];
const ResultChartvue_type_template_id_3f67992e_hoisted_19 = {
  key: 0
};
const ResultChartvue_type_template_id_3f67992e_hoisted_20 = ["x1", "x2", "y1", "y2"];
const ResultChartvue_type_template_id_3f67992e_hoisted_21 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_22 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_23 = ["data-name", "x", "y", "width", "height"];
const ResultChartvue_type_template_id_3f67992e_hoisted_24 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_25 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_26 = ["x", "y", "width", "height"];
const ResultChartvue_type_template_id_3f67992e_hoisted_27 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_28 = {
  key: 1
};
const ResultChartvue_type_template_id_3f67992e_hoisted_29 = ["x1", "x2", "y1", "y2"];
const ResultChartvue_type_template_id_3f67992e_hoisted_30 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_31 = ["x", "y"];
const ResultChartvue_type_template_id_3f67992e_hoisted_32 = ["x1", "x2", "y1", "y2"];
const ResultChartvue_type_template_id_3f67992e_hoisted_33 = ["data-name"];
const ResultChartvue_type_template_id_3f67992e_hoisted_34 = ["d"];
const _hoisted_35 = ["cx", "cy"];
const _hoisted_36 = ["x", "y", "width", "height", "onMouseenter", "onFocus"];
const _hoisted_37 = {
  class: "ui-tooltip-content"
};
const _hoisted_38 = {
  class: "evolution-tooltip-header"
};
const _hoisted_39 = ["data-name"];
const _hoisted_40 = {
  key: 3,
  class: "fwde-chart__axis-title"
};
const _hoisted_41 = {
  key: 4,
  class: "fwde-chart__legend"
};
const _hoisted_42 = ["data-name"];
function ResultChartvue_type_template_id_3f67992e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SegmentedSwitch = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SegmentedSwitch");
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ResultChartvue_type_template_id_3f67992e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ResultChartvue_type_template_id_3f67992e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", ResultChartvue_type_template_id_3f67992e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultChartvue_type_template_id_3f67992e_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ChartMetric')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default fwde-select",
    value: _ctx.metric,
    onChange: _cache[0] || (_cache[0] = (...args) => _ctx.onMetric && _ctx.onMetric(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricOptions, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option.id,
      value: option.id
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.name), 9, ResultChartvue_type_template_id_3f67992e_hoisted_6);
  }), 128))], 40, ResultChartvue_type_template_id_3f67992e_hoisted_5)]), _ctx.showTypeSwitch ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SegmentedSwitch, {
    key: 0,
    class: "fwde-chart__type",
    options: _ctx.typeSwitch,
    "model-value": _ctx.chartType,
    label: _ctx.translate('FeinwerkDataExploration_ChartType'),
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.$emit('chart-type', $event))
  }, null, 8, ["options", "model-value", "label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.note ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ResultChartvue_type_template_id_3f67992e_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.note), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.chartType === 'trend' && _ctx.trendLoading ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ActivityIndicator, {
    key: 0,
    loading: true,
    "loading-message": _ctx.translate('FeinwerkDataExploration_LoadingTrend')
  }, null, 8, ["loading-message"])) : _ctx.chartType === 'donut' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ResultChartvue_type_template_id_3f67992e_hoisted_8, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
    class: "fwde-chart__donut-svg",
    viewBox: "0 0 200 200",
    width: "200",
    height: "200",
    role: "img",
    "aria-label": _ctx.donutLabel
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.donut.slices, (slice, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("path", {
      key: index,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-chart__slice', `fwde-chart__series-${index % _ctx.SERIES_COLOURS + 1}`]),
      d: _ctx.donutPath(index)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.sliceTitle(slice)), 1)], 10, ResultChartvue_type_template_id_3f67992e_hoisted_10);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", ResultChartvue_type_template_id_3f67992e_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(_ctx.donut.total)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", ResultChartvue_type_template_id_3f67992e_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.metricName), 1)], 8, ResultChartvue_type_template_id_3f67992e_hoisted_9)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", ResultChartvue_type_template_id_3f67992e_hoisted_13, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.donut.slices, (slice, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: index
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-chart__swatch', `fwde-chart__series-${index % _ctx.SERIES_COLOURS + 1}`]),
      "aria-hidden": "true"
    }, null, 2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultChartvue_type_template_id_3f67992e_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.sliceName(slice)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultChartvue_type_template_id_3f67992e_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(slice.value)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultChartvue_type_template_id_3f67992e_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.sharePercent(slice.share)), 1)]);
  }), 128))])])) : _ctx.chartType !== 'trend' || _ctx.model.series.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ResultChartvue_type_template_id_3f67992e_hoisted_17, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
    class: "fwde-chart__svg",
    width: _ctx.width,
    height: _ctx.height,
    viewBox: `0 0 ${_ctx.width} ${_ctx.height}`,
    role: "img",
    "aria-label": _ctx.ariaLabel,
    tabindex: _ctx.model.type === 'line' ? 0 : undefined,
    onMouseleave: _cache[2] || (_cache[2] = $event => _ctx.hoverIndex = null),
    onBlur: _cache[3] || (_cache[3] = $event => _ctx.hoverIndex = null),
    onFocus: _cache[4] || (_cache[4] = (...args) => _ctx.onFocus && _ctx.onFocus(...args)),
    onKeydown: _cache[5] || (_cache[5] = (...args) => _ctx.onKey && _ctx.onKey(...args))
  }, [_ctx.model.type === 'bar' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", ResultChartvue_type_template_id_3f67992e_hoisted_19, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.scale.ticks, tick => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `grid${tick}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("line", {
      class: "fwde-chart__grid",
      x1: _ctx.barX(tick),
      x2: _ctx.barX(tick),
      y1: _ctx.TOP,
      y2: _ctx.barAreaBottom
    }, null, 8, ResultChartvue_type_template_id_3f67992e_hoisted_20), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__tick",
      x: _ctx.barX(tick),
      y: _ctx.barAreaBottom + 16,
      "text-anchor": "middle"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formatTick(tick)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_21)]);
  }), 128)), _ctx.model.series.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.categories, (category, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `group${index}`,
      class: "fwde-chart__group"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__category",
      x: _ctx.labelWidth,
      y: _ctx.barY(index) + _ctx.bandHeight / 2,
      "text-anchor": "end",
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.truncateLabel(category, _ctx.labelChars)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_22), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series, (series, seriesIndex) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
        key: `group${index}-${seriesIndex}`
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.groupTitle(series, series.points[index])), 1), series.points[index].value !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: 0
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("rect", {
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-chart__bar', ..._ctx.seriesClasses(seriesIndex, series)]),
        "data-name": _ctx.seriesColourName(seriesIndex, series),
        x: Math.min(_ctx.barX(0), _ctx.barX(series.points[index].value || 0)),
        y: _ctx.groupBarY(index, seriesIndex),
        width: Math.max(1, Math.abs(_ctx.barX(series.points[index].value || 0) - _ctx.barX(0))),
        height: _ctx.GROUP_BAR_HEIGHT - 2,
        rx: "2"
      }, null, 10, ResultChartvue_type_template_id_3f67992e_hoisted_23), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
        class: "fwde-chart__value fwde-chart__value--grouped",
        x: Math.max(_ctx.barX(0), _ctx.barX(series.points[index].value || 0)) + 6,
        y: _ctx.groupBarY(index, seriesIndex) + (_ctx.GROUP_BAR_HEIGHT - 2) / 2,
        "dominant-baseline": "central"
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(series.points[index].value)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_24)], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
    }), 128))]);
  }), 128)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series.length > 1 ? [] : _ctx.model.series[0].points, (point, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `bar${index}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(point.label) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(point.value)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__category",
      x: _ctx.labelWidth,
      y: _ctx.barY(index) + _ctx.BAR_HEIGHT / 2,
      "text-anchor": "end",
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.truncateLabel(point.label, _ctx.labelChars)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_25), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("rect", {
      class: "fwde-chart__bar fwde-chart__series-1",
      x: Math.min(_ctx.barX(0), _ctx.barX(point.value || 0)),
      y: _ctx.barY(index) + 3,
      width: Math.max(1, Math.abs(_ctx.barX(point.value || 0) - _ctx.barX(0))),
      height: _ctx.BAR_HEIGHT - 6,
      rx: "2"
    }, null, 8, ResultChartvue_type_template_id_3f67992e_hoisted_26), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__value",
      x: Math.max(_ctx.barX(0), _ctx.barX(point.value || 0)) + 6,
      y: _ctx.barY(index) + _ctx.BAR_HEIGHT / 2,
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(point.value)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_27)]);
  }), 128))])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", ResultChartvue_type_template_id_3f67992e_hoisted_28, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.scale.ticks, tick => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `grid${tick}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("line", {
      class: "fwde-chart__grid",
      x1: _ctx.LINE_LEFT,
      x2: _ctx.width - _ctx.LINE_RIGHT,
      y1: _ctx.lineY(tick),
      y2: _ctx.lineY(tick)
    }, null, 8, ResultChartvue_type_template_id_3f67992e_hoisted_29), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__tick fwde-chart__value-tick",
      x: _ctx.LINE_LEFT - 8,
      y: _ctx.lineY(tick),
      "text-anchor": "end",
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formatTick(tick)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_30)]);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.categories, (category, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("text", {
      key: `cat${index}`,
      class: "fwde-chart__tick fwde-chart__category-tick",
      x: _ctx.lineX(index),
      y: _ctx.LINE_HEIGHT - 10,
      "text-anchor": "middle"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.axisLabel(category)), 9, ResultChartvue_type_template_id_3f67992e_hoisted_31)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index % _ctx.categoryStep === 0]]);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.categories, (category, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: `daygrid${index}`
    }, [index % _ctx.categoryStep === 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("line", {
      key: 0,
      class: "fwde-chart__grid fwde-chart__day-grid",
      x1: _ctx.lineX(index),
      x2: _ctx.lineX(index),
      y1: _ctx.LINE_TOP,
      y2: _ctx.LINE_HEIGHT - _ctx.LINE_BOTTOM
    }, null, 8, ResultChartvue_type_template_id_3f67992e_hoisted_32)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series, (series, seriesIndex) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `series${seriesIndex}`,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.seriesClasses(seriesIndex, series)),
      "data-name": _ctx.seriesColourName(seriesIndex, series)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'fwde-chart__line': true,
        'fwde-chart__line--dashed': series.dashed
      }),
      d: _ctx.linePath(series.points.map(p => p.value), _ctx.lineX, _ctx.lineY)
    }, null, 10, ResultChartvue_type_template_id_3f67992e_hoisted_34), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(series.points, (point, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: `point${index}`
      }, [point.value !== null && _ctx.showsDot(series.points, index) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("circle", {
        key: 0,
        class: "fwde-chart__point",
        cx: _ctx.lineX(index),
        cy: _ctx.lineY(point.value),
        r: "3.5"
      }, null, 8, _hoisted_35)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
    }), 128))], 10, ResultChartvue_type_template_id_3f67992e_hoisted_33);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.categories, (category, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("rect", {
      key: `hover${index}`,
      class: "fwde-chart__hover",
      x: _ctx.hoverX(index),
      y: _ctx.LINE_TOP,
      width: _ctx.hoverWidth,
      height: _ctx.LINE_HEIGHT - _ctx.LINE_TOP - _ctx.LINE_BOTTOM,
      tabindex: "-1",
      onMouseenter: $event => _ctx.hoverIndex = index,
      onFocus: $event => _ctx.hoverIndex = index
    }, null, 40, _hoisted_36);
  }), 128))]))], 40, ResultChartvue_type_template_id_3f67992e_hoisted_18)), _ctx.model.type === 'line' && _ctx.hoverIndex !== null && _ctx.tooltipRows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "ui-tooltip fwde-chart__tooltip",
    style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])(_ctx.tooltipStyle),
    role: "presentation"
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_37, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_38, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tooltipTitle), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.tooltipRows, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: row.key,
      class: "fwde-chart__tooltip-row"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([...row.classes, 'tooltip-series-color']),
      "data-name": row.colourName
    }, null, 10, _hoisted_39), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("strong", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.value), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.label), 1)]);
  }), 128))])], 4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.axisTitle && _ctx.model.type === 'line' && !(_ctx.chartType === 'trend' && _ctx.trendLoading) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", _hoisted_40, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.axisTitle), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showsLegend && !(_ctx.chartType === 'trend' && _ctx.trendLoading) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", _hoisted_41, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series, (series, seriesIndex) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: `${seriesIndex}-${series.label}`,
      class: "fwde-chart__legend-item"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])([..._ctx.seriesClasses(seriesIndex, series), 'fwde-chart__swatch', {
        'fwde-chart__swatch--dashed': series.dashed
      }]),
      "data-name": _ctx.seriesColourName(seriesIndex, series),
      "aria-hidden": "true"
    }, null, 10, _hoisted_42), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(series.label), 1)]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=template&id=3f67992e

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/chart.ts





/** First dimensions shown as a line over time instead of bars. */
const TIME_DIMENSIONS = ['date', 'hour', 'weekday'];
const NICE_STEPS = [1, 2, 2.5, 5, 10];
/** The title of the result: the names of its dimensions, e.g. "Channel type × Browser". */
function resultTitle(state, catalog) {
  return state.dimensions.map(id => {
    var _catalog$dimensions$f;
    return ((_catalog$dimensions$f = catalog.dimensions.find(d => d.id === id)) === null || _catalog$dimensions$f === void 0 ? void 0 : _catalog$dimensions$f.name) || id;
  }).join(' × ');
}
/** Readable steps between axis labels: days, weeks, fortnights; hours in parts of a day. */
const LABEL_STEPS = {
  day: [1, 2, 7, 14, 28],
  hour: [1, 2, 3, 4, 6, 12]
};
/**
 * Every how many categories the time axis is labelled: the smallest readable step whose labels
 * do not overlap (days weekly as in Matomo's graphs, always on the first day's weekday). When even
 * the largest step does not fit, the first and the last category, or only the first.
 *
 * @param plotWidth the width from the first to the last category, in pixels
 * @param labelWidth the width one label needs, gap included
 */
function axisLabelStep(count, plotWidth, labelWidth, kind) {
  if (count <= 1) {
    return 1;
  }
  const spacing = plotWidth / (count - 1);
  const steps = kind === 'other' ? Array.from({
    length: count - 1
  }, (_, i) => i + 1) : LABEL_STEPS[kind].filter(step => step < count);
  const fitting = steps.find(step => step * spacing >= labelWidth);
  if (fitting !== undefined) {
    return fitting;
  }
  return plotWidth >= labelWidth ? count - 1 : count;
}
/** A trend is possible without a time dimension (with one, the chart has a time axis already). */
function canTrend(state) {
  return !state.dimensions.some(id => TIME_DIMENSIONS.includes(id));
}
/** Two rows or more; one row when its trend can be shown (with the state). */
function canChart(rowCount, state) {
  return rowCount >= 2 || rowCount === 1 && state !== undefined && canTrend(state);
}
/**
 * Both where a chart makes sense, the table otherwise; a chosen view only while it is possible.
 * One row (its trend) shows the table unless a chart view was chosen.
 */
function effectiveView(state, rowCount) {
  if (rowCount >= 2) {
    return state.view || 'both';
  }
  return canChart(rowCount, state) ? state.view || 'table' : 'table';
}
/** The chosen chart metric, else the sorted metric, else the first metric. */
function chartMetricFor(state) {
  if (state.chartMetric && state.metrics.includes(state.chartMetric)) {
    return state.chartMetric;
  }
  return state.metrics.includes(state.sort) ? state.sort : state.metrics[0];
}
function finite(value) {
  return Number.isFinite(value) ? value : 0;
}
/** Rounds to the precision of the step, so ticks print as 0.3 and not 0.30000000000000004. */
function round(value, step) {
  const decimals = Math.min(20, Math.max(0, -Math.floor(Math.log10(step)) + 2));
  return Number(value.toFixed(decimals));
}
const UNIT_SCALE = {
  min: 0,
  max: 1,
  ticks: [0, 0.2, 0.4, 0.6, 0.8, 1]
};
/**
 * An axis from zero (or the smallest negative value) to a round maximum with about `tickCount`
 * steps; whole-number steps for counts.
 */
function niceScale(minValue, maxValue, tickCount = 5, integer = false) {
  const low = Math.min(finite(minValue), 0);
  const high = Math.max(finite(maxValue), 0);
  const span = high - low;
  if (span < 1e-9) {
    return UNIT_SCALE;
  }
  if (!Number.isFinite(span) || Math.max(-low, high) > 1e300) {
    // too large for round steps (e.g. an event value near the largest number)
    return {
      min: low,
      max: high,
      ticks: [low, high]
    };
  }
  const raw = span / Math.max(1, tickCount);
  const magnitude = 10 ** Math.floor(Math.log10(raw));
  const candidates = NICE_STEPS.map(s => s * magnitude).filter(s => !integer || Number.isInteger(s));
  const fallback = integer ? Math.max(1, Math.ceil(raw)) : 10 * magnitude;
  const step = candidates.find(s => s >= raw) || fallback;
  const min = round(Math.floor(low / step) * step, step);
  const max = round(Math.ceil(high / step) * step, step);
  const count = Math.min(50, Math.round((max - min) / step));
  const ticks = Array.from({
    length: count + 1
  }, (_, i) => round(min + i * step, step));
  return {
    min,
    max,
    ticks
  };
}
/** a cell's number, or null for a missing or empty one (never NaN) */
function numberOrNull(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }
  const number = Number(value);
  return Number.isFinite(number) ? number : null;
}
function chart_valueOf(row, metric) {
  const value = Number(row[metric]);
  return Number.isFinite(value) ? value : 0;
}
/** Position on the time axis: dates as they are, hours and weekdays as Matomo orders them. */
function timeOrder(dimension, weekdayNames, hourLabels) {
  if (dimension === 'hour') {
    return label => {
      const index = hourLabels.indexOf(label);
      return index < 0 ? parseInt(label, 10) : index;
    };
  }
  if (dimension === 'weekday') {
    return label => {
      const index = weekdayNames.indexOf(label);
      return index < 0 ? weekdayNames.length : index;
    };
  }
  return label => label;
}
function compareKeys(a, b) {
  if (typeof a === 'number' && typeof b === 'number') {
    return (Number.isNaN(a) ? Infinity : a) - (Number.isNaN(b) ? Infinity : b);
  }
  return String(a).localeCompare(String(b));
}
function rowLabel(row) {
  return LABEL_COLUMNS.filter(column => row[column] !== undefined).map(column => String(row[column])).join(' · ');
}
function barModel(rows, metric, maxBars, tickCount) {
  const top = [...rows].sort((a, b) => chart_valueOf(b, metric) - chart_valueOf(a, metric)).slice(0, maxBars);
  const values = top.map(row => chart_valueOf(row, metric));
  return {
    type: 'bar',
    categories: top.map(rowLabel),
    series: [{
      label: metric,
      points: top.map(row => ({
        label: rowLabel(row),
        value: chart_valueOf(row, metric)
      }))
    }],
    omittedRows: Math.max(0, rows.length - maxBars),
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: niceScale(Math.min(...values), Math.max(...values), tickCount, isInteger(metric))
  };
}
function lineModel(rows, metric, order, /** every value of the time axis when missing values are real zeros, else null */
allCategories, zeroForMissing, complete, maxSeries, tickCount) {
  const present = rows.map(row => String(row.label));
  const categories = [...new Set([...(allCategories || []), ...present])].sort((a, b) => compareKeys(order(a), order(b)));
  const hasSecond = rows.some(row => row.secondary_label !== undefined);
  const groups = new Map();
  rows.forEach(row => {
    const key = hasSecond ? String(row.secondary_label) : metric;
    groups.set(key, [...(groups.get(key) || []), row]);
  });
  // the largest values of the second dimension by their total, ties by name
  const ranked = [...groups.entries()].map(([label, groupRows]) => ({
    label,
    groupRows,
    total: groupRows.reduce((sum, row) => sum + chart_valueOf(row, metric), 0)
  })).sort((a, b) => b.total - a.total || a.label.localeCompare(b.label));
  const kept = ranked.slice(0, maxSeries);
  const missing = zeroForMissing ? 0 : null;
  const series = kept.map(({
    label,
    groupRows
  }) => ({
    label,
    points: categories.map(category => {
      const row = groupRows.find(r => String(r.label) === category);
      return {
        label: category,
        value: row ? chart_valueOf(row, metric) : missing
      };
    })
  }));
  const values = series.flatMap(s => s.points.map(p => p.value)).filter(value => value !== null);
  return {
    type: 'line',
    categories,
    series,
    omittedRows: 0,
    omittedSeries: ranked.length - kept.length,
    incomplete: !complete,
    gaps: complete && series.some(s => s.points.some(p => p.value === null)),
    scale: niceScale(Math.min(0, ...values), Math.max(0, ...values), tickCount, isInteger(metric))
  };
}
/**
 * The bar chart of a pivot (rows and pivot, 2026-11-24): the largest rows by the metric's total,
 * one bar per pivot value (no bars for Others and Total).
 */
function pivotBarModel(rows, values, metric, maxBars, tickCount) {
  const top = [...rows].sort((a, b) => {
    var _numberOrNull, _numberOrNull2;
    return ((_numberOrNull = numberOrNull(b[metric])) !== null && _numberOrNull !== void 0 ? _numberOrNull : 0) - ((_numberOrNull2 = numberOrNull(a[metric])) !== null && _numberOrNull2 !== void 0 ? _numberOrNull2 : 0);
  }).slice(0, maxBars);
  const series = values.map((value, n) => ({
    label: value.label,
    points: top.map(row => ({
      label: rowLabel(row),
      value: numberOrNull(row[`${metric}__p${n + 1}`])
    }))
  }));
  const numbers = series.flatMap(s => s.points.map(p => p.value)).filter(value => value !== null);
  return {
    type: 'bar',
    categories: top.map(rowLabel),
    series,
    omittedRows: Math.max(0, rows.length - maxBars),
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: niceScale(Math.min(0, ...numbers), Math.max(0, ...numbers), tickCount, isInteger(metric))
  };
}
/**
 * The bar chart while comparing: the largest rows of the own view by the metric, one series per
 * comparison column (own view first), coloured as Matomo's comparison series; a compared period
 * carries its change for the tooltip.
 */
function compareBarModel(rows, columns, metric, options) {
  const {
    maxBars = 10,
    tickCount = 5
  } = options;
  const data = rows.filter(row => !row.fwde_total);
  const top = [...data].sort((a, b) => {
    var _numberOrNull3, _numberOrNull4;
    return ((_numberOrNull3 = numberOrNull(b[metric])) !== null && _numberOrNull3 !== void 0 ? _numberOrNull3 : 0) - ((_numberOrNull4 = numberOrNull(a[metric])) !== null && _numberOrNull4 !== void 0 ? _numberOrNull4 : 0);
  }).slice(0, maxBars);
  const series = columns.map((column, n) => ({
    label: columnLabel(column),
    comparison: true,
    points: top.map(row => {
      const point = {
        label: rowLabel(row),
        value: numberOrNull(row[valueKey(metric, n)])
      };
      if (column.kind === 'period' || column.kind === 'both') {
        point.change = numberOrNull(row[changeKey(metric, n)]);
      }
      return point;
    })
  }));
  const values = series.flatMap(s => s.points.map(p => p.value)).filter(value => value !== null);
  return {
    type: 'bar',
    categories: top.map(rowLabel),
    series,
    omittedRows: Math.max(0, data.length - maxBars),
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: niceScale(Math.min(0, ...values), Math.max(0, ...values), tickCount, isInteger(metric))
  };
}
/**
 * The hint above a compared chart (translation key, '' for none): grouped bars show every column
 * themselves; the lines of a time dimension and the trend show column totals; a time dimension in
 * second place shows the own view.
 */
function compareChartHint(state, chartType, columnCount) {
  const [first, second] = state.dimensions;
  const singleTime = TIME_DIMENSIONS.includes(first) && second === undefined;
  if (chartType === 'trend' || singleTime && columnCount > 1) {
    return 'FeinwerkDataExploration_CompareTrendHint';
  }
  if (state.dimensions.some(id => TIME_DIMENSIONS.includes(id))) {
    return 'FeinwerkDataExploration_CompareChartHint';
  }
  return columnCount > 1 ? '' : 'FeinwerkDataExploration_CompareChartHint';
}
/**
 * The chart of a result: a line over time when a dimension is a time dimension (the first one when
 * both are; one line per value of the other dimension), otherwise one bar per row. Values are never
 * added up, so every metric works.
 *
 * A missing point is zero only when the result is complete (not cut at the row limit), no metric
 * filter removed rows and the metric is a count or a sum; then every hour, weekday and day of the
 * period is on the axis. Otherwise it is a gap.
 */
function chartModel(rows, state, metric, options) {
  const {
    weekdayNames,
    hourLabels = [],
    periodDays = null,
    complete = true,
    tickCount = 5,
    maxBars = 20,
    pivotValues = []
  } = options;
  let {
    maxSeries = 5
  } = options;
  const own = rows.filter(row => !row.fwde_total);
  // the first time dimension is the axis, one line per value of the others ("Browser x Date": one
  // line per browser, trend design 3)
  const axis = state.dimensions.findIndex(id => TIME_DIMENSIONS.includes(id));
  if (pivotValues.length && (axis < 0 || state.dimensions.length > 1)) {
    return pivotBarModel(own, pivotValues, metric, Math.min(maxBars, 10), tickCount);
  }
  if (axis < 0) {
    return barModel(own, metric, maxBars, tickCount);
  }
  const dimension = state.dimensions[axis];
  const columns = labelColumns(state.dimensions.length);
  let data = own;
  if (pivotValues.length) {
    // a single time dimension with a pivot: one line per pivot value
    maxSeries = pivotValues.length;
    data = own.flatMap(row => pivotValues.map((value, n) => ({
      label: row.label,
      secondary_label: value.label,
      [metric]: row[`${metric}__p${n + 1}`]
    })));
  } else if (columns.length > 1) {
    data = own.map(row => Object.assign(Object.assign({}, row), {}, {
      label: row[columns[axis]],
      secondary_label: columns.filter((_, i) => i !== axis).map(c => String(row[c])).join(' · ')
    }));
  }
  const metricFilter = state.filters.some(f => NUMBER_OPERATORS.includes(f.operator));
  const zeroForMissing = complete && !metricFilter && metricMeta_zeroForMissing(metric);
  let allCategories = null;
  if (zeroForMissing) {
    if (dimension === 'hour' && hourLabels.length === 24) {
      allCategories = hourLabels;
    } else if (dimension === 'weekday' && weekdayNames.length === 7) {
      allCategories = weekdayNames;
    } else if (dimension === 'date') {
      allCategories = periodDays;
    }
  }
  return lineModel(data, metric, timeOrder(dimension, weekdayNames, hourLabels), allCategories, zeroForMissing, complete, maxSeries, tickCount);
}
function coordinate(value) {
  return String(Math.round(value * 10) / 10);
}
/** SVG path through the values; a null value ends the line, the next value starts a new one. */
function linePath(values, x, y) {
  const parts = [];
  let drawing = false;
  values.forEach((value, index) => {
    if (value === null) {
      drawing = false;
      return;
    }
    parts.push(`${drawing ? 'L' : 'M'}${coordinate(x(index))},${coordinate(y(value))}`);
    drawing = true;
  });
  return parts.join(' ');
}
/**
 * A label cut to a number of characters with an ellipsis in the middle, so labels that start alike
 * (URLs) keep their different ends.
 */
function truncateLabel(label, maxChars) {
  if (label.length <= maxChars) {
    return label;
  }
  const keep = Math.max(0, maxChars - 1);
  const tail = Math.floor(keep / 2);
  return `${label.slice(0, keep - tail)}…${tail > 0 ? label.slice(-tail) : ''}`;
}
const DONUT_OTHERS_LABEL = 'General_Others';
/**
 * The donut of one count or sum: the largest rows (at most maxSlices) and "Others" for the rest
 * of the shown rows plus the exact rest of a cut table (others, null when not calculated).
 */
function donutModel(rows, metric, others, maxSlices = 9) {
  const values = rows.map(row => ({
    label: rowLabel(row),
    value: Number(row[metric]) || 0
  })).filter(entry => entry.value > 0).sort((a, b) => b.value - a.value);
  const top = values.slice(0, maxSlices);
  const rest = values.slice(maxSlices).reduce((sum, entry) => sum + entry.value, 0) + (others !== null && others > 0 ? others : 0);
  const parts = rest > 0 ? [...top, {
    label: DONUT_OTHERS_LABEL,
    value: rest,
    others: true
  }] : top;
  const total = parts.reduce((sum, entry) => sum + entry.value, 0);
  return {
    slices: parts.map(entry => Object.assign(Object.assign({}, entry), {}, {
      share: total > 0 ? entry.value / total : 0
    })),
    total
  };
}
/**
 * Why the donut cannot show the chart metric (a lang key), null when it can: only counts and
 * sums, only rows that add up to the total, not while comparing.
 */
function donutBlocker(state, catalog, metric, comparing) {
  if (comparing) {
    return 'FeinwerkDataExploration_DonutNotWhileComparing';
  }
  if (state.pivot) {
    // with a pivot the chart offers bars only (rows and pivot, 2026-11-24)
    return 'FeinwerkDataExploration_ReasonNotWithPivot';
  }
  if (!hasShare(metric)) {
    return 'FeinwerkDataExploration_DonutNeedsCount';
  }
  if (!addsUp(state, catalog)) {
    return 'FeinwerkDataExploration_DonutNeedsSum';
  }
  return null;
}
/**
 * The chart that shows: the chosen donut while it qualifies (donutReason null) and there are two
 * rows or more, else the trend or the bars as chartTypeFor decides.
 */
function effectiveChartType(state, rowCount, donutReason, pivoting = false) {
  // a time dimension draws a line over time and offers no switch, so no donut there either
  if (state.chartType === 'donut' && rowCount >= 2 && donutReason === null && canTrend(state)) {
    return 'donut';
  }
  // with a pivot the chart offers bars only (rows and pivot, 2026-11-24)
  if (!canTrend(state) || pivoting) {
    return 'bar';
  }
  return rowCount === 1 || state.chartType === 'trend' ? 'trend' : 'bar';
}
/**
 * The SVG path of one ring segment from startShare to startShare + share of the circle (0 at the
 * top, clockwise); a share of the whole circle as two half rings.
 */
function donutArc(startShare, share, outer, inner, centre) {
  if (share >= 0.9999) {
    return `${donutArc(0, 0.5, outer, inner, centre)} ${donutArc(0.5, 0.5, outer, inner, centre)}`;
  }
  const point = (radius, at) => {
    const angle = at * 2 * Math.PI - Math.PI / 2;
    const x = centre + radius * Math.cos(angle);
    const y = centre + radius * Math.sin(angle);
    return `${Math.round(x * 100) / 100} ${Math.round(y * 100) / 100}`;
  };
  const end = startShare + share;
  const large = share > 0.5 ? 1 : 0;
  return `M ${point(outer, startShare)} A ${outer} ${outer} 0 ${large} 1 ${point(outer, end)} ` + `L ${point(inner, end)} A ${inner} ${inner} 0 ${large} 0 ${point(inner, startShare)} Z`;
}
/**
 * Whether the page asks for the exact rest because of the donut (design 2026-11-20, on demand):
 * only while a donut of a cut table is on screen (not in the table view) and nothing runs.
 */
function donutNeedsOthers(page) {
  return page.chartType === 'donut' && page.view !== 'table' && page.cut && page.othersState === 'idle' && !page.loading;
}
/**
 * The page's rest after the server answered: final either way. "No rest" (the server found the
 * table not cut) is an answer too, so the donut never asks again for the same table.
 */
function othersAnswered(answer) {
  return {
    others: answer,
    othersState: 'done'
  };
}
/**
 * Whether the lines of a time dimension need the exploration's every row (Peter, 2026-11-24, to
 * the proposal "the chart fetches its lines separately, with every day"): the table reached its
 * row limit, which is below the largest one; not while comparing (the lines show column totals).
 */
function needsFullTimeRows(state, rowCount, maxRows, comparing, pivoting = false) {
  // a pivot draws bars of the table's rows, lines only over a single time dimension
  return !comparing && !(pivoting && state.dimensions.length > 1) && state.dimensions.some(id => TIME_DIMENSIONS.includes(id)) && rowCount >= state.limit && state.limit < maxRows;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/trend.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */





function trend_numberOrNull(value) {
  if (value === null || value === undefined || value === '') {
    return null;
  }
  const number = Number(value);
  return Number.isFinite(number) ? number : null;
}
function timeLabel(time, hourLabels) {
  return /^\d{1,2}$/.test(time) ? hourLabels[Number(time)] || time : time;
}
function scaleOf(series, metric, tickCount) {
  const values = series.flatMap(s => s.points.map(p => p.value)).filter(value => value !== null);
  return niceScale(Math.min(0, ...values), Math.max(0, ...values), tickCount, isInteger(metric));
}
function isOtherPeriod(kind) {
  return kind === 'period' || kind === 'both';
}
/**
 * The trend chart (trend design 3): one line per line of getTrend, in its order. The axis has the
 * days or hours of line 0 (the page's period); point i of every line is that line's i-th day or
 * hour, so a compared period lines up by position: a shorter one ends early, a longer one is cut.
 */
function trendModel(rows, metric, options) {
  const {
    hourLabels,
    tableRows,
    tickCount = 5
  } = options;
  const lines = new Map();
  rows.forEach(row => {
    const line = Number(row.fwde_line);
    lines.set(line, [...(lines.get(line) || []), row]);
  });
  const ordered = [...lines.entries()].sort((a, b) => a[0] - b[0]).map(([, lineRows]) => lineRows);
  const first = ordered[0] || [];
  const categories = first.map(row => timeLabel(String(row.time), hourLabels));
  const series = ordered.map(lineRows => {
    const head = lineRows[0];
    const label = LABEL_COLUMNS.filter(column => head[column] !== undefined && head[column] !== null).map(column => String(head[column])).join(' · ');
    return {
      label,
      dashed: isOtherPeriod(head.fwde_kind),
      comparison: head.fwde_kind !== undefined && head.fwde_kind !== null,
      line: Number(head.fwde_line),
      points: categories.map((category, i) => {
        const row = lineRows[i];
        if (!row) {
          return {
            label: category,
            value: null
          };
        }
        const time = String(row.time);
        return {
          label: category,
          value: trend_numberOrNull(row[metric]),
          date: time !== String(first[i].time) ? timeLabel(time, hourLabels) : undefined
        };
      })
    };
  });
  const comparing = rows.some(row => row.fwde_kind !== undefined && row.fwde_kind !== null);
  return {
    type: 'line',
    categories,
    series,
    // no lines yet (loading, an error): nothing is omitted
    omittedRows: comparing || !series.length ? 0 : Math.max(0, tableRows - series.length),
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: scaleOf(series, metric, tickCount)
  };
}
/**
 * A table with one time dimension while comparing (trend design 3): one line per comparison column
 * from the table's values, over the table's rows in time order; the total row is left out.
 */
function compareTimeModel(rows, columns, state, metric, options) {
  const {
    weekdayNames,
    hourLabels = [],
    tickCount = 5
  } = options;
  const order = timeOrder(state.dimensions[0], weekdayNames, hourLabels);
  const data = rows.filter(row => !row.fwde_total).sort((a, b) => compareKeys(order(String(a.label)), order(String(b.label))));
  const categories = data.map(row => String(row.label));
  const series = columns.map((column, n) => ({
    label: columnLabel(column),
    dashed: isOtherPeriod(column.kind),
    comparison: true,
    points: data.map(row => ({
      label: String(row.label),
      value: trend_numberOrNull(row[valueKey(metric, n)])
    }))
  }));
  return {
    type: 'line',
    categories,
    series,
    omittedRows: 0,
    omittedSeries: 0,
    incomplete: false,
    gaps: false,
    scale: scaleOf(series, metric, tickCount)
  };
}
/** The trend for one row (bars of one row say nothing) and where chosen, bars otherwise. */
function chartTypeFor(state, rowCount) {
  // never the donut here: the page asks effectiveChartType with the donut's reason
  return effectiveChartType(state, rowCount, 'not-checked');
}
/**
 * The requests of a trend: 0 (the rows) without comparing, else one per comparison column
 * (1 … n), each answered with the own total (line 0) and that column (line 1).
 */
function trendColumns(columns) {
  return columns.length > 1 ? columns.slice(1).map((_, i) => i + 1) : [0];
}
/**
 * The lines with response n merged in: the rows response as it is; for a comparison column its
 * line 1 as line n, and line 0 (the own total) from the first response that brings it.
 */
function mergeTrendLines(lines, response, n) {
  if (n === 0) {
    return response;
  }
  const hasOwn = lines.some(row => Number(row.fwde_line) === 0);
  const own = hasOwn ? [] : response.filter(row => Number(row.fwde_line) === 0);
  const column = response.filter(row => Number(row.fwde_line) === 1).map(row => Object.assign(Object.assign({}, row), {}, {
    fwde_line: n
  }));
  return [...lines, ...own, ...column];
}
/** While comparing, the own line (0) is missing when every comparison request failed. */
function needsOwnLine(lines, columns) {
  return columns.length > 1 && !lines.some(row => Number(row.fwde_line) === 0);
}
/** The lines with the own line (0) of a response added, unless they have it. */
function withOwnLine(lines, response) {
  if (lines.some(row => Number(row.fwde_line) === 0)) {
    return lines;
  }
  return [...response.filter(row => Number(row.fwde_line) === 0), ...lines];
}
/**
 * A trend request still counts while it is the newest and the page still shows the setup it was
 * asked for (another definition or comparison makes it void, so its next requests are not sent).
 */
function trendIsCurrent(request, page) {
  return request.sequence === page.sequence && request.key === page.definition && !page.aborted;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=script&lang=ts







const TOP = 8;
const BAR_HEIGHT = 26;
/** grouped bars while comparing: one bar per comparison column, a gap between the rows */
const GROUP_BAR_HEIGHT = 16;
const GROUP_GAP = 10;
const AXIS_HEIGHT = 26;
// Matomo's evolution graph: a flat plot, three value ticks, a date label about every 150 pixels
const LINE_HEIGHT = 210;
const LINE_TICKS = 2;
const LINE_LEFT = 64;
const LINE_RIGHT = 16;
const LINE_TOP = 12;
const LINE_BOTTOM = 34;
const VALUE_SPACE = 80;
const CHAR_WIDTH = 7;
/** colours of lines and legend (--fwde-series-1 to -10, with dark variants) */
const ResultChartvue_type_script_lang_ts_SERIES_COLOURS = 10;
/* harmony default export */ var ResultChartvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    SegmentedSwitch: SegmentedSwitch
  },
  props: {
    rows: {
      type: Array,
      required: true
    },
    state: {
      type: Object,
      required: true
    },
    catalog: {
      type: Object,
      required: true
    },
    metric: {
      type: String,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    },
    currencySymbol: {
      type: String,
      required: true
    },
    hourLabels: {
      type: Array,
      default: () => []
    },
    periodDays: {
      type: Array,
      default: null
    },
    /** bars or the trend (trend design 3); the page decides (chartTypeFor) */
    chartType: {
      type: String,
      default: 'bar'
    },
    /** the "Bars | Trend" switch: a trend is possible and the table has two rows or more */
    showTypeSwitch: {
      type: Boolean,
      default: false
    },
    trendRows: {
      type: Array,
      default: null
    },
    trendLoading: {
      type: Boolean,
      default: false
    },
    /** lines of a line chart (free: 5) */
    maxLines: {
      type: Number,
      default: 5
    },
    /**
     * the row limit of the rows given: the page's own request for every row of a time dimension
     * (2026-11-24) asks with the largest limit; 0 = the table's
     */
    rowLimit: {
      type: Number,
      default: 0
    },
    /** comparison columns: one line per column (time dimension) or one bar per column and row */
    compareColumns: {
      type: Array,
      default: () => []
    },
    /** why the donut cannot show the chart metric (a lang key), null when it can (donutBlocker) */
    donutReason: {
      type: String,
      default: null
    },
    /** the exact rest of a cut table for the chart metric ("Others"), null when not calculated */
    othersValue: {
      type: Number,
      default: null
    },
    /** lang key: why the donut's "Others" lacks the cut rows (loading, failed); '' when complete */
    othersNote: {
      type: String,
      default: ''
    },
    /** the pivot's values (rows and pivot, 2026-11-24): one bar or line per value, bars only */
    pivotValues: {
      type: Array,
      default: () => []
    }
  },
  emits: ['metric', 'chart-type'],
  data() {
    return {
      width: 800,
      observer: null,
      TOP,
      BAR_HEIGHT,
      GROUP_BAR_HEIGHT,
      LINE_HEIGHT,
      LINE_LEFT,
      LINE_RIGHT,
      LINE_TOP,
      LINE_BOTTOM,
      SERIES_COLOURS: ResultChartvue_type_script_lang_ts_SERIES_COLOURS,
      /** the day (category) under the mouse: its tooltip and dots */
      hoverIndex: null
    };
  },
  mounted() {
    const root = this.$refs.root;
    this.measure(root);
    if (typeof ResizeObserver !== 'undefined') {
      this.observer = new ResizeObserver(() => this.measure(root));
      this.observer.observe(root);
    }
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  computed: {
    model() {
      if (this.chartType === 'trend') {
        const trend = trendModel(this.trendRows || [], this.metric, {
          hourLabels: this.hourLabels,
          tableRows: this.rows.length,
          tickCount: LINE_TICKS
        });
        // comparison line n is column n: named as table and bars name it, not by the server
        const columns = this.compareColumns;
        return columns.length > 1 ? Object.assign(Object.assign({}, trend), {}, {
          series: trend.series.map(series => {
            const column = series.line === undefined ? undefined : columns[series.line];
            return series.comparison && column ? Object.assign(Object.assign({}, series), {}, {
              label: columnLabel(column)
            }) : series;
          })
        }) : trend;
      }
      const weekdays = [1, 2, 3, 4, 5, 6, 7].map(day => Object(external_CoreHome_["translate"])(`Intl_Day_Long_StandAlone_${day}`));
      const options = {
        weekdayNames: weekdays,
        hourLabels: this.hourLabels,
        periodDays: this.periodDays,
        complete: this.rows.length < (this.rowLimit || this.state.limit),
        // about 70 pixels per axis label; the line chart's axis is vertical and always fits five
        tickCount: this.state.dimensions.some(id => TIME_DIMENSIONS.includes(id)) ? LINE_TICKS : Math.min(5, Math.max(2, Math.floor(this.barPlotWidth / 70))),
        maxSeries: this.maxLines,
        pivotValues: this.pivotValues
      };
      if (this.compareColumns.length > 1) {
        const {
          dimensions
        } = this.state;
        if (TIME_DIMENSIONS.includes(dimensions[0]) && dimensions.length === 1) {
          return compareTimeModel(this.rows, this.compareColumns, this.state, this.metric, options);
        }
        if (!dimensions.some(id => TIME_DIMENSIONS.includes(id))) {
          return compareBarModel(this.rows, this.compareColumns, this.metric, {
            maxBars: 10,
            tickCount: options.tickCount
          });
        }
      }
      return chartModel(this.rows, this.state, this.metric, options);
    },
    /** lines of several rows, values or comparison columns: a legend and the line's name */
    showsLegend() {
      if (this.model.type === 'bar') {
        return this.model.series.length > 1;
      }
      return this.state.dimensions.length > 1 || this.pivotValues.length > 0 || this.chartType === 'trend' || this.compareColumns.length > 1;
    },
    /** the height of one row: a single bar, or one bar per comparison column */
    bandHeight() {
      const count = this.model.series.length;
      return count > 1 ? count * GROUP_BAR_HEIGHT + GROUP_GAP : BAR_HEIGHT;
    },
    /** the donut disabled with its reason as tooltip */
    typeSwitch() {
      return this.typeOptions.map(option => ({
        value: option.type,
        label: option.label,
        disabled: option.reason !== null,
        title: option.reason ? Object(external_CoreHome_["translate"])(option.reason) : undefined
      }));
    },
    typeOptions() {
      const options = [{
        type: 'bar',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartTypeBars'),
        reason: null
      }];
      if (canTrend(this.state)) {
        options.push({
          type: 'trend',
          label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartTypeTrend'),
          // with a pivot the chart offers bars only (rows and pivot, 2026-11-24)
          reason: this.pivotValues.length ? 'FeinwerkDataExploration_ReasonNotWithPivot' : null
        });
      }
      options.push({
        type: 'donut',
        label: Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartTypeDonut'),
        reason: this.donutReason
      });
      return options;
    },
    /** the donut of the chart metric (design 2026-11-20): the largest rows and "Others" */
    donut() {
      return donutModel(this.rows, this.metric, this.othersValue, 9);
    },
    donutLabel() {
      const option = this.metricOptions.find(o => o.id === this.metric);
      const name = Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartTypeDonut');
      return option ? `${name}: ${option.name}` : name;
    },
    metricOptions() {
      return this.state.metrics.map(id => {
        const metric = this.catalog.metrics.find(m => m.id === id);
        const name = metric && this.actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
        return {
          id,
          name
        };
      });
    },
    metricName() {
      var _this$metricOptions$f;
      return ((_this$metricOptions$f = this.metricOptions.find(m => m.id === this.metric)) === null || _this$metricOptions$f === void 0 ? void 0 : _this$metricOptions$f.name) || this.metric;
    },
    height() {
      if (this.model.type === 'line') {
        return LINE_HEIGHT;
      }
      return TOP + this.model.categories.length * this.bandHeight + AXIS_HEIGHT;
    },
    labelWidth() {
      return Math.min(Math.round(this.width * 0.4), 280);
    },
    labelChars() {
      return Math.max(4, Math.floor((this.labelWidth - 8) / CHAR_WIDTH));
    },
    barPlotWidth() {
      return Math.max(40, this.width - this.labelWidth - 12 - VALUE_SPACE);
    },
    barAreaBottom() {
      return TOP + this.model.categories.length * this.bandHeight;
    },
    /** what the time axis shows: days, the hours of a day, or other values (weekdays) */
    axisKind() {
      const {
        categories
      } = this.model;
      if (!categories.length) {
        return 'other';
      }
      if (categories.every(c => /^\d{4}-\d{2}-\d{2}$/.test(c))) {
        return 'day';
      }
      const isHour = c => this.hourLabels.includes(c) || /^\d{1,2}$/.test(c);
      return categories.every(isHour) ? 'hour' : 'other';
    },
    /** the one day whose hours the axis shows, '' otherwise */
    hoursOfDay() {
      const days = this.periodDays || [];
      return this.axisKind === 'hour' && days.length === 1 ? days[0] : '';
    },
    /** under the axis, when it shows hours: whose hours they are */
    axisTitle() {
      return this.hoursOfDay ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_TrendHours', this.dayLabel(this.hoursOfDay, true)) : '';
    },
    tooltipTitle() {
      const category = this.model.categories[this.hoverIndex === null ? 0 : this.hoverIndex] || '';
      if (this.axisKind !== 'hour') {
        return this.dayLabel(category, true);
      }
      const hour = formatHour(category);
      return this.hoursOfDay ? `${this.dayLabel(this.hoursOfDay, true)}, ${hour}` : hour;
    },
    /** every how many days (hours, values) the axis has a label: readable steps that fit */
    categoryStep() {
      const {
        categories
      } = this.model;
      const labels = categories.map(category => this.axisLabel(category));
      // about 6.5 pixels per character at 11 pixels, and a gap
      const labelWidth = Math.max(0, ...labels.map(label => label.length)) * 6.5 + 16;
      const plotWidth = this.width - LINE_LEFT - LINE_RIGHT;
      return axisLabelStep(categories.length, plotWidth, labelWidth, this.axisKind);
    },
    note() {
      if (this.chartType === 'donut') {
        return this.othersNote ? Object(external_CoreHome_["translate"])(this.othersNote) : '';
      }
      if (this.chartType === 'trend') {
        return this.model.omittedRows > 0 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_TrendFirstRows', String(this.model.series.length), String(this.rows.length)) : '';
      }
      if (this.model.incomplete) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartIncomplete');
      }
      if (this.model.gaps) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartGaps');
      }
      if (this.model.omittedRows > 0) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartOmittedRows', String(this.model.categories.length));
      }
      if (this.model.omittedSeries > 0) {
        const dimension = this.catalog.dimensions.find(d => d.id === this.state.dimensions[1]);
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartOmittedSeries', String(this.model.series.length), (dimension === null || dimension === void 0 ? void 0 : dimension.name) || '');
      }
      return '';
    },
    ariaLabel() {
      return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartLabel', this.metricName, String(this.model.categories.length));
    },
    /** the width of the hover area of one day */
    hoverWidth() {
      const count = this.model.categories.length;
      const plotWidth = this.width - LINE_LEFT - LINE_RIGHT;
      return count < 2 ? plotWidth : plotWidth / (count - 1);
    },
    /** the hovered day's value of every line that has one, as Matomo's tooltip lists them */
    tooltipRows() {
      const index = this.hoverIndex;
      if (index === null) {
        return [];
      }
      return this.model.series.flatMap((series, seriesIndex) => {
        const point = series.points[index];
        if (!point || point.value === null) {
          return [];
        }
        return [{
          key: `${seriesIndex}`,
          label: point.date ? `${series.label} (${this.dayLabel(point.date)})` : series.label,
          value: this.format(point.value),
          classes: this.seriesClasses(seriesIndex, series),
          colourName: this.seriesColourName(seriesIndex, series)
        }];
      });
    },
    /** next to the hovered day, on its left in the right half of the chart */
    tooltipStyle() {
      const index = this.hoverIndex === null ? 0 : this.hoverIndex;
      const x = this.lineX(index);
      const top = `${LINE_TOP}px`;
      return x > this.width / 2 ? {
        right: `${Math.round(this.width - x + 12)}px`,
        top,
        left: 'auto'
      } : {
        left: `${Math.round(x + 12)}px`,
        top
      };
    },
    formatters() {
      return {
        number: v => external_CoreHome_["NumberFormatter"].formatNumber(v, 2, 0),
        percent: v => external_CoreHome_["NumberFormatter"].formatPercent(v, 2, 0),
        currency: v => external_CoreHome_["NumberFormatter"].formatCurrency(v, this.currencySymbol, 2, 2),
        duration: v => formatDuration(v)
      };
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    linePath: linePath,
    truncateLabel: truncateLabel,
    measure(root) {
      const width = Math.round(root.clientWidth);
      if (width > 0 && width !== this.width) {
        this.width = width;
      }
    },
    onMetric(event) {
      this.$emit('metric', event.target.value);
    },
    donutPath(index) {
      const before = this.donut.slices.slice(0, index).reduce((sum, slice) => sum + slice.share, 0);
      return donutArc(before, this.donut.slices[index].share, 90, 56, 100);
    },
    sliceName(slice) {
      return slice.others ? Object(external_CoreHome_["translate"])(slice.label) : slice.label;
    },
    sharePercent(share) {
      return external_CoreHome_["NumberFormatter"].formatPercent(Math.round(share * 1000) / 10);
    },
    sliceTitle(slice) {
      const share = this.sharePercent(slice.share);
      return `${this.sliceName(slice)}: ${this.format(slice.value)} (${share})`;
    },
    format(value) {
      return value === null ? '' : formatMetric(this.metric, value, this.formatters);
    },
    formatTick(value) {
      return formatMetric(this.metric, value, this.formatters);
    },
    dayLabel(value, full = false) {
      return formatDay(value, external_CoreHome_["Matomo"].language, full);
    },
    /** a label of the time axis: a short day, an hour as a time of day, or the value */
    axisLabel(category) {
      return this.axisKind === 'hour' ? formatHour(category) : truncateLabel(this.dayLabel(category), 16);
    },
    /** a dot on the hovered day, and on points without a line (no neighbour) */
    showsDot(points, index) {
      return points[index].value !== null && (index === this.hoverIndex || this.isolated(points, index));
    },
    /** a point without a neighbour has no line: it keeps its dot */
    isolated(points, index) {
      const before = index > 0 ? points[index - 1].value : null;
      const after = index < points.length - 1 ? points[index + 1].value : null;
      return points[index].value !== null && before === null && after === null;
    },
    /** colours: the plugin's series (Matomo's palette), or Matomo's colours of a comparison */
    seriesClasses(seriesIndex, series) {
      const colour = this.colourIndex(seriesIndex, series) % ResultChartvue_type_script_lang_ts_SERIES_COLOURS + 1;
      const classes = ['fwde-chart__series', `fwde-chart__series-${colour}`];
      return series.comparison ? [...classes, 'comparison-series-color'] : classes;
    },
    seriesColourName(seriesIndex, series) {
      return series.comparison ? external_CoreHome_["ComparisonsStoreInstance"].getSeriesColorName(this.colourIndex(seriesIndex, series), 0) : undefined;
    },
    /** a comparison line keeps its column's colour, also when a line before it is missing */
    colourIndex(seriesIndex, series) {
      return series.comparison && series.line !== undefined ? series.line : seriesIndex;
    },
    /** keyboard: the tooltip starts at the first day */
    onFocus() {
      if (this.model.type === 'line' && this.hoverIndex === null && this.model.categories.length) {
        this.hoverIndex = 0;
      }
    },
    /** keyboard: the arrow keys move the tooltip from day to day, Escape closes it */
    onKey(event) {
      if (this.model.type !== 'line' || this.hoverIndex === null) {
        return;
      }
      const last = this.model.categories.length - 1;
      if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
        const step = event.key === 'ArrowRight' ? 1 : -1;
        this.hoverIndex = Math.min(last, Math.max(0, this.hoverIndex + step));
        event.preventDefault();
      } else if (event.key === 'Escape') {
        this.hoverIndex = null;
      }
    },
    hoverX(index) {
      return Math.max(LINE_LEFT, this.lineX(index) - this.hoverWidth / 2);
    },
    barX(value) {
      const {
        min,
        max
      } = this.model.scale;
      return this.labelWidth + 12 + (value - min) / (max - min) * this.barPlotWidth;
    },
    barY(index) {
      return TOP + index * this.bandHeight;
    },
    /** the top of a comparison column's bar in its row */
    groupBarY(index, seriesIndex) {
      return this.barY(index) + GROUP_GAP / 2 + seriesIndex * GROUP_BAR_HEIGHT;
    },
    /** a grouped bar's tooltip: column, row, value, and a compared period's change */
    groupTitle(series, point) {
      const value = point.value === null ? '' : this.format(point.value);
      let title = `${series.label} · ${point.label}: ${value}`;
      if (point.change !== undefined && point.change !== null) {
        const change = external_CoreHome_["NumberFormatter"].formatPercent(point.change, 1, 0);
        title += ` (${point.change > 0 ? `+${change}` : change})`;
      }
      return title;
    },
    lineX(index) {
      const plotWidth = this.width - LINE_LEFT - LINE_RIGHT;
      const count = this.model.categories.length;
      return count < 2 ? LINE_LEFT + plotWidth / 2 : LINE_LEFT + index * plotWidth / (count - 1);
    },
    lineY(value) {
      const {
        min,
        max
      } = this.model.scale;
      const plotHeight = LINE_HEIGHT - LINE_TOP - LINE_BOTTOM;
      return LINE_TOP + (max - value) / (max - min) * plotHeight;
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue



ResultChartvue_type_script_lang_ts.render = ResultChartvue_type_template_id_3f67992e_render

/* harmony default export */ var ResultChart = (ResultChartvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/requestGuard.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Tells whether an answer belongs to the latest request, so an older answer never replaces a newer
 * table.
 */
function createRequestGuard() {
  let latest = 0;
  return {
    start: () => {
      latest += 1;
      return latest;
    },
    isLatest: id => id === latest
  };
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/runQueue.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * At most one exploration of the page runs at a time. Aborting a request in the browser does not
 * stop its query on the server, so a new request next to it would only take another of the
 * instance's slots ("other explorations are running"); the newest change runs when it ends.
 */
function createRunQueue() {
  let running = false;
  let pending = false;
  return {
    request: () => {
      if (running) {
        pending = true;
        return false;
      }
      running = true;
      return true;
    },
    finish: () => {
      if (pending) {
        pending = false;
        return true;
      }
      running = false;
      return false;
    },
    isPending: () => pending
  };
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=script&lang=ts



































const HASH_KEYS = STATE_HASH_KEYS.concat([
// Matomo's comparison of segments and periods (comparison design)
'compareSegments', 'comparePeriods', 'compareDates']);
/* harmony default export */ var ExplorationPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  inheritAttrs: false,
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"],
    DimensionPicker: DimensionPicker,
    MetricPicker: MetricPicker,
    FilterPanel: FilterPanel,
    ResultTable: ResultTable,
    PivotTable: PivotTable,
    PivotPicker: PivotPicker,
    CompareTable: CompareTable,
    ResultChart: ResultChart,
    ExplorationToolbar: ExplorationToolbar,
    SavedPicker: SavedPicker,
    SaveDialog: SaveDialog,
    FeedbackBar: FeedbackBar,
    PromoLine: PromoLine,
    PremiumBanner: PremiumBanner
  },
  props: {
    idSite: {
      type: Number,
      required: true
    },
    maxRows: {
      type: Number,
      required: true
    },
    defaultRows: {
      type: Number,
      required: true
    },
    /** at least write access to the site: may share saved explorations */
    canShare: {
      type: Boolean,
      default: false
    },
    canCopy: {
      type: Boolean,
      default: false
    },
    /** admin access to the website: manage its shared explorations */
    canManage: {
      type: Boolean,
      default: false
    },
    /** Matomo's sections a saved exploration can show in as a report (saved reports, 2026-11-24) */
    menuSections: {
      type: Array,
      default: () => []
    },
    archiveHistoryFrom: {
      type: String,
      default: null
    },
    promo: {
      type: Object,
      default: null
    },
    /** the premium banner below the result (PremiumPromo); null: none, the tip line may show */
    premium: {
      type: Object,
      default: null
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    },
    maxDays: {
      type: Number,
      required: true
    },
    rawDataFrom: {
      type: String,
      default: null
    },
    currencySymbol: {
      type: String,
      default: ''
    },
    hourLabels: {
      type: Array,
      default: () => []
    },
    /** columns per metric when comparing, the own one included (free: 4) */
    maxCompareColumns: {
      type: Number,
      default: 4
    },
    /** lines of a line chart (free: 5) */
    maxTrendLines: {
      type: Number,
      default: 5
    }
  },
  data() {
    return {
      MAX_DIMENSIONS: MAX_DIMENSIONS,
      saved: [],
      savedLoaded: false,
      saveBusy: false,
      saveError: '',
      catalog: null,
      state: null,
      rows: null,
      loading: false,
      error: '',
      loadError: '',
      filtersOpen: false,
      lastDefinition: '',
      periodVisits: null,
      elapsedMs: null,
      suggestedPeriod: null,
      runTimer: 0,
      abortController: null,
      guard: createRequestGuard(),
      others: null,
      othersState: 'idle',
      othersGuard: createRequestGuard(),
      queue: createRunQueue(),
      unmounted: false,
      columnErrors: {},
      trendRows: null,
      trendLoading: false,
      trendError: '',
      trendErrors: {},
      trendKey: '',
      trendSequence: 0,
      trendAbort: null,
      fullRows: null,
      fullRowsKey: '',
      fullRowsAbort: null
    };
  },
  created() {
    this.loadSavedList(true);
    loadCatalog().then(catalog => {
      this.catalog = catalog;
      this.readState();
    }).catch(e => {
      this.loadError = errorText(e);
    });
    // warnings only: without them the page works as before
    loadPeriodVisits().then(periodVisits => {
      this.periodVisits = periodVisits;
      this.offerSmallerPeriod(false);
    }).catch(() => {
      this.offerSmallerPeriod(true);
    });
  },
  beforeUnmount() {
    this.unmounted = true;
    window.clearTimeout(this.runTimer);
    if (this.abortController) {
      this.abortController.abort();
    }
    if (this.trendAbort) {
      this.trendAbort.abort();
    }
  },
  computed: {
    /** "n of 10" (more metrics, 2026-11-24) */
    metricsCount() {
      const chosen = this.state ? this.state.metrics.length : 0;
      return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_MetricsCount', String(chosen), String(MAX_METRICS));
    },
    /** the table was cut: the row limit is reached or a number filter removed rows */
    tableCut() {
      if (!this.state || !this.rows) {
        return false;
      }
      const shown = this.rows.filter(row => !row.fwde_total).length;
      return shown >= this.state.limit || this.state.filters.some(filter => NUMBER_OPERATORS.includes(filter.operator));
    },
    hashState() {
      const hash = external_CoreHome_["MatomoUrl"].hashParsed.value;
      return HASH_KEYS.map(key => {
        var _hash$key;
        return String((_hash$key = hash[key]) !== null && _hash$key !== void 0 ? _hash$key : '');
      }).join('|');
    },
    /** Matomo's comparison controls: the table's columns (the own one first) */
    comparison() {
      return comparisonColumns(external_CoreHome_["ComparisonsStoreInstance"].getSegmentComparisons(), external_CoreHome_["ComparisonsStoreInstance"].getPeriodComparisons(), this.maxCompareColumns, Object(external_CoreHome_["translate"])('General_Unknown'));
    },
    comparing() {
      return isComparing(this.comparison.columns);
    },
    /** a pivot applies (rows and pivot, 2026-11-24): chosen and Matomo does not compare */
    pivoting() {
      return !!this.state && !!this.state.pivot && !this.comparing;
    },
    /** the exploration's metrics with the names the table shows: a KPI card picks one of them */
    saveMetrics() {
      const {
        catalog,
        state
      } = this;
      if (!catalog || !state) {
        return [];
      }
      return state.metrics.map(id => {
        const metric = catalog.metrics.find(m => m.id === id);
        const name = metric && this.actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
        return {
          id,
          name
        };
      });
    },
    /** the pivot's values of the result, for the chart */
    pivotValues() {
      return this.pivoting && this.rows ? parsePivotResult(this.rows).values : [];
    },
    compareLimitText() {
      return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_CompareLimit', String(this.maxCompareColumns));
    },
    /** the rows without the total row */
    ownRows() {
      return this.rows ? this.rows.filter(row => !row.fwde_total) : null;
    },
    actionScope() {
      const {
        catalog,
        state
      } = this;
      if (!catalog || !state) {
        return false;
      }
      return catalog.dimensions.some(d => state.dimensions.includes(d.id) && d.scope === 'action') || catalog.metrics.some(m => state.metrics.includes(m.id) && m.scope === 'action');
    },
    /** an empty result says what to do: with filters set, edit them (UI review 2026-11-23) */
    empty() {
      return emptyState(this.state ? this.state.filters.length : 0);
    },
    /** period and segment of the result (UI item 5); none while comparing */
    chips() {
      return viewChips(external_CoreHome_["ComparisonsStoreInstance"].getSegmentComparisons(), external_CoreHome_["ComparisonsStoreInstance"].getPeriodComparisons(), Object(external_CoreHome_["translate"])('General_Unknown'));
    },
    /** the applied filters for the left column (UI item 4; groups 2026-11-23) */
    filterLines() {
      if (!this.state || !this.catalog) {
        return [];
      }
      return filterSummaryLines(this.state, this.catalog, this.actionScope, external_CoreHome_["translate"]);
    },
    view() {
      const rowCount = this.ownRows ? this.ownRows.length : 0;
      return this.state ? effectiveView(this.state, rowCount) : 'table';
    },
    canChart() {
      return this.state ? canChart(this.ownRows ? this.ownRows.length : 0, this.state) : false;
    },
    chartType() {
      return this.state ? effectiveChartType(this.state, this.ownRows ? this.ownRows.length : 0, this.donutReason, this.pivoting) : 'bar';
    },
    /**
     * "Bars | Trend | Donut": two rows or more where a trend is possible; with a time dimension the
     * chart is already a line over time (one row: its trend)
     */
    showTypeSwitch() {
      return !!this.state && canTrend(this.state) && !!this.ownRows && this.ownRows.length >= 2;
    },
    /** why the donut cannot show the chart metric (a lang key), null when it can (2026-11-20) */
    donutReason() {
      return this.state && this.catalog ? donutBlocker(this.state, this.catalog, this.chartMetric, this.comparing) : 'FeinwerkDataExploration_DonutNeedsCount';
    },
    /** the donut of a cut table needs the exact rest: asked automatically while the donut shows */
    donutNeedsOthers() {
      return donutNeedsOthers({
        chartType: this.chartType,
        view: this.view,
        cut: this.tableCut,
        othersState: this.othersState,
        loading: this.loading
      });
    },
    /** the exact rest of the chart metric for the donut, null until calculated */
    donutOthers() {
      if (!this.tableCut || !this.others || this.othersState !== 'done') {
        return null;
      }
      const value = this.others[this.chartMetric];
      return typeof value === 'number' ? value : null;
    },
    /** why the donut's "Others" lacks the cut rows, '' when it is complete */
    donutOthersNote() {
      if (!this.tableCut || this.othersState === 'done') {
        return '';
      }
      return this.othersState === 'failed' ? 'FeinwerkDataExploration_OthersFailed' : 'FeinwerkDataExploration_OthersLoading';
    },
    showsTrend() {
      return !this.error && !!this.ownRows && this.ownRows.length > 0 && this.view !== 'table' && this.chartType === 'trend';
    },
    /** the comparison columns for the chart: lines of a time dimension, grouped bars otherwise */
    chartCompareColumns() {
      return this.comparing ? this.comparison.columns : [];
    },
    /** the hint above a compared chart; none for grouped bars (they show every column) */
    compareHint() {
      const {
        state,
        chartType,
        chartCompareColumns
      } = this;
      return state ? compareChartHint(state, chartType, chartCompareColumns.length) : '';
    },
    /** the setup the visible trend needs, '' while none is visible or the table still loads */
    trendRequestKey() {
      return this.showsTrend && !this.loading ? this.lastDefinition : '';
    },
    /** the definition whose every row the chart's time lines need ('' none) */
    fullRowsRequestKey() {
      if (!this.state || this.loading || this.error || !this.ownRows || this.view === 'table') {
        return '';
      }
      const {
        length
      } = this.ownRows;
      return needsFullTimeRows(this.state, length, this.maxRows, this.comparing, this.pivoting) ? this.lastDefinition : '';
    },
    chartMetric() {
      return this.state ? chartMetricFor(this.state) : '';
    },
    largePeriodHint() {
      return largePeriodHint(this.periodVisits);
    },
    slowHint() {
      return slowHint(this.elapsedMs, this.periodVisits, this.loading, this.error);
    },
    suggestedPeriodLabel() {
      var _this$suggestedPeriod;
      return suggestedPeriodLabel(((_this$suggestedPeriod = this.suggestedPeriod) === null || _this$suggestedPeriod === void 0 ? void 0 : _this$suggestedPeriod.days) || 0);
    },
    canRetry() {
      return retryAllowed(this.error);
    },
    noSmallerPeriodKey() {
      return noSmallerPeriodKeyForUser();
    },
    visitsLogEnabled() {
      const {
        piwik
      } = window;
      return !!(piwik && piwik.visitorLogEnabled);
    },
    title() {
      return this.currentSaved ? this.currentSaved.name : this.resultName;
    },
    resultName() {
      return this.catalog && this.state ? resultTitle(this.state, this.catalog) : '';
    },
    currentSaved() {
      // a KPI card opens as its exploration on this page (2026-11-24)
      return findSaved(this.saved, String(external_CoreHome_["MatomoUrl"].parsed.value.fwdeSaved || ''), ['table', 'kpi']);
    },
    /** The page shows another setup than the open saved exploration (changed, or an older link). */
    savedChanged() {
      if (!this.currentSaved || !this.state || !this.catalog) {
        return false;
      }
      const limits = {
        defaultRows: this.defaultRows,
        maxRows: this.maxRows
      };
      const segment = String(external_CoreHome_["MatomoUrl"].parsed.value.segment || '');
      return differsFromSaved(this.currentSaved, toHashParams(this.state), segment, this.catalog, limits);
    },
    login() {
      const {
        piwik
      } = window;
      return String(piwik && piwik.userLogin || '');
    },
    periodDays() {
      const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
      const today = new Date().toISOString().substring(0, 10);
      return period_periodDays(String(parsed.period), String(parsed.date), today);
    },
    rawDataRange() {
      return rawDataRange(this.rawDataFrom);
    },
    showRawDataWarning() {
      return showsRawDataWarning(this.rawDataFrom);
    },
    /** the archived exploration's report hint for a period it cannot cover (archived reports) */
    archivedHint() {
      return archivedReportHint(this.currentSaved);
    },
    /**
     * the first day a changed archived exploration rebuilds (archive_history_months or the raw
     * data's start): a change of what shapes its archive starts its history again (the panel warns)
     */
    historyLostFrom() {
      const hash = external_CoreHome_["MatomoUrl"].hashParsed.value;
      const from = historyLostFrom(this.currentSaved, hash, String(hash.segment || ''), this.archiveHistoryFrom);
      return from ? formatLongDate(from, external_CoreHome_["Matomo"].language) : '';
    }
  },
  watch: {
    donutNeedsOthers(needed) {
      if (needed) {
        this.calculateOthers();
      }
    },
    hashState() {
      // back/forward or a pasted link changed the exploration parameters
      this.readState();
    },
    trendRequestKey(key) {
      if (key !== '' && key !== this.trendKey) {
        this.loadTrend(key);
      }
    },
    fullRowsRequestKey(key) {
      if (key !== '' && key !== this.fullRowsKey) {
        this.loadFullRows(key);
      }
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    readState() {
      if (!this.catalog) {
        return;
      }
      const limits = {
        defaultRows: this.defaultRows,
        maxRows: this.maxRows
      };
      const state = parseState(external_CoreHome_["MatomoUrl"].parsed.value, this.catalog, limits);
      this.state = state;
      // "Explore this row" from a report: open the second-dimension picker once
      if (external_CoreHome_["MatomoUrl"].parsed.value.fwdeBreakdown === '1') {
        this.openNextDimension();
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          fwdeBreakdown: ''
        }));
      }
      const definition = JSON.stringify([toDefinition(state, this.comparing), this.comparing ? this.comparison.columns : []]);
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '';
        this.lastDefinition = definition;
        // a trend of the old setup is void: its open and further requests stop
        if (this.trendAbort) {
          this.trendAbort.abort();
        }
        // so are the chart's full rows
        if (this.fullRowsAbort) {
          this.fullRowsAbort.abort();
        }
        this.fullRows = null;
        this.fullRowsKey = '';
        // a short pause, so a few quick changes start one request; the table is dimmed meanwhile
        window.clearTimeout(this.runTimer);
        if (first) {
          this.run();
        } else {
          this.loading = true;
          this.runTimer = window.setTimeout(() => this.run(), RUN_DELAY_MS);
        }
      }
    },
    /** a change of the exploration: validated, then into the URL hash (which runs it) */
    update(change) {
      if (!this.state || !this.catalog) {
        return;
      }
      const next = Object.assign(Object.assign({}, this.state), change);
      const limits = {
        defaultRows: this.defaultRows,
        maxRows: this.maxRows
      };
      // re-validate: e.g. a removed dimension makes filters and the sort column on it invalid
      const valid = parseState(toHashParams(next), this.catalog, limits);
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), toHashParams(valid)), {}, {
        fwdeBreakdown: ''
      }));
    },
    /** Asks for the most recent days that fit when the period is too large or too long. */
    offerSmallerPeriod(countFailed) {
      if (!needsSmallerPeriod(this.periodVisits, countFailed)) {
        return;
      }
      loadSuggestedPeriod().then(suggestion => {
        this.suggestedPeriod = suggestion || {};
      }).catch(() => undefined);
    },
    /** The archived exploration's own menu page, for the same period (archived reports). */
    openArchivedReport() {
      const saved = this.currentSaved;
      if (saved && saved.menu) {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          category: saved.menu,
          subcategory: `fwdeSaved${saved.id}`
        }));
      }
    },
    /** Another period for the same exploration: a range of days (the page reloads its widget). */
    showFrom(range) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        period: 'range',
        date: range
      }));
    },
    /**
     * The saved explorations the user may open; without them the page works as before.
     *
     * @param openFromLink a link that names only a saved exploration (a bookmark) opens its setup
     */
    loadSavedList(openFromLink = false) {
      return loadSaved().then(list => {
        this.saved = list;
        this.savedLoaded = true;
        const linked = this.currentSaved;
        if (openFromLink && linked && linkNamesOnlySaved(external_CoreHome_["MatomoUrl"].hashParsed.value)) {
          this.openSaved(linked);
        }
      }).catch(() => undefined);
    },
    /**
     * A delete in the manage panel: the list again, and the open exploration, when it was the one,
     * stays on screen as a new one, as after a delete in the save panel (deleteCurrent).
     */
    savedDeleted(id) {
      this.loadSavedList().then(() => {
        const hash = external_CoreHome_["MatomoUrl"].hashParsed.value;
        if (String(hash.fwdeSaved || '') === String(id)) {
          external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, hash), {}, {
            fwdeSaved: ''
          }));
        }
      });
    },
    /** Opens a saved exploration (its state and segment), or a new one with the default state. */
    openSaved(saved) {
      const hash = Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value);
      if (saved) {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, hash), savedHashParams(saved)));
        return;
      }
      STATE_KEYS.forEach(key => {
        hash[key] = '';
      });
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, hash), {}, {
        fwdeSaved: ''
      }));
    },
    saveExploration(event) {
      if (!this.state) {
        return;
      }
      const explored = stateToSave(toHashParams(this.state));
      const fields = {
        name: event.name,
        // the menu place goes into the saved state, never into the link (saved reports); a KPI card
        // keeps its settings instead and has no menu place (2026-11-24)
        state: event.card ? withCard(explored, event.card) : withMenu(explored, event.menu),
        segment: String(external_CoreHome_["MatomoUrl"].parsed.value.segment || ''),
        shared: event.shared
      };
      const current = this.currentSaved;
      // a saved funnel is never overwritten by a table, a table never by a card (and back)
      const request = current && current.canEdit && !event.asNew && savedKind(current) === (event.card ? 'kpi' : 'table') ? updateSaved(current.id, fields).then(() => current.id) : addSaved(fields).then(result => Number(result.id));
      this.saveBusy = true;
      this.saveError = '';
      request.then(id => this.loadSavedList().then(() => {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          fwdeSaved: String(id)
        }));
      })).catch(e => {
        this.saveError = errorText(e);
      }).finally(() => {
        this.saveBusy = false;
      });
    },
    deleteCurrent() {
      const current = this.currentSaved;
      if (!current) {
        return;
      }
      this.saveBusy = true;
      this.saveError = '';
      deleteSaved(current.id).then(() => this.loadSavedList()).then(() => {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          fwdeSaved: ''
        }));
      }).catch(e => {
        this.saveError = errorText(e);
      }).finally(() => {
        this.saveBusy = false;
      });
    },
    filterToRow(row) {
      if (this.state) {
        this.update(rowFilterState(this.state, row));
      }
    },
    breakDownRow(row) {
      this.filterToRow(row);
      this.openNextDimension();
    },
    /** the dimension picker's next empty slot ("break down", "Explore this row") */
    openNextDimension() {
      this.$nextTick(() => {
        const picker = this.$refs.dimensionPicker;
        if (picker) {
          const chosen = this.state ? this.state.dimensions.length : 1;
          picker.openSlot = Math.min(MAX_DIMENSIONS - 1, chosen);
          if (picker.$el) {
            picker.$el.scrollIntoView({
              block: 'nearest'
            });
          }
        }
      });
    },
    /** Matomo's segmented visits log: the row's visits within the page's segment. */
    showRowVisits(row) {
      const segment = combinedSegment(String(external_CoreHome_["MatomoUrl"].parsed.value.segment || ''), row.segment ? String(row.segment) : null);
      const log = window.SegmentedVisitorLog;
      if (segment && log) {
        log.show('FeinwerkDataExploration.getExploration', segment, {});
      }
    },
    sortBy(column) {
      if (!this.state) {
        return;
      }
      const isDescending = this.state.sort === column && this.state.sortOrder === 'desc';
      const sortOrder = isDescending ? 'asc' : 'desc';
      this.update({
        sort: column,
        sortOrder
      });
    },
    run() {
      if (!this.state) {
        return;
      }
      this.loading = true;
      this.error = '';
      // one exploration at a time: a change while one runs starts when it ends (see runQueue)
      if (this.queue.request()) {
        this.start();
      }
    },
    /**
     * The comparison table, column by column (comparison design 5): first the own view with its
     * total and shares (asked as its own column 1), then each comparison in its own request; a
     * column that fails gets a hint, the others stay.
     */
    runCompared(state, latest, abortController) {
      const {
        columns
      } = this.comparison;
      return runComparison(state, columns[0], abortController).then(first => {
        if (!latest()) {
          return undefined;
        }
        this.rows = withoutComparisonColumns(first);
        this.columnErrors = {};
        return requestColumns(remainingColumns(this.rows, columns), n => runComparison(state, columns[n], abortController), () => latest() && !abortController.signal.aborted, (n, rows) => {
          if (this.rows) {
            this.rows = mergeColumn(this.rows, rows, n);
          }
        }, (n, e) => {
          this.columnErrors = Object.assign(Object.assign({}, this.columnErrors), {}, {
            [n]: errorText(e)
          });
        });
      });
    },
    /**
     * The trend (trend design 3): only while it is visible, after the table; without comparing one
     * request for the first rows, otherwise one per comparison column. A newer setup makes older
     * answers void; a failing column keeps the other lines.
     */
    loadTrend(key) {
      if (!this.state) {
        return;
      }
      if (this.trendAbort) {
        this.trendAbort.abort();
      }
      const abortController = new AbortController();
      this.trendAbort = abortController;
      this.trendSequence += 1;
      const sequence = this.trendSequence;
      const latest = () => !this.unmounted && trendIsCurrent({
        sequence,
        key
      }, {
        sequence: this.trendSequence,
        definition: this.lastDefinition,
        aborted: abortController.signal.aborted
      });
      const {
        state
      } = this;
      const {
        columns
      } = this.comparison;
      this.trendKey = key;
      this.trendRows = null;
      this.trendError = '';
      this.trendErrors = {};
      this.trendLoading = true;
      let lines = [];
      requestColumns(trendColumns(columns), n => runTrend(state, n === 0 ? null : columns[n], abortController), latest, (n, rows) => {
        lines = mergeTrendLines(lines, rows, n);
        this.trendRows = lines;
      }, (n, e) => {
        if (n === 0) {
          this.trendError = errorText(e);
          // the same setup may be asked again (switch away and back)
          this.trendKey = '';
        } else {
          this.trendErrors = Object.assign(Object.assign({}, this.trendErrors), {}, {
            [n]: errorText(e)
          });
        }
      }).then(() => {
        // every comparison failed: the own line still shows
        if (!latest() || !needsOwnLine(lines, columns)) {
          return undefined;
        }
        return runTrend(state, columns[0], abortController).then(rows => {
          if (latest()) {
            lines = withOwnLine(lines, rows);
            this.trendRows = lines;
          }
        }).catch(() => undefined);
      }).finally(() => {
        if (latest()) {
          this.trendLoading = false;
        }
      });
    },
    /**
     * Every row of the exploration (the largest row limit) for the chart's time lines: the table
     * keeps its limit. On failure the chart stays with the table's rows and its hint.
     */
    loadFullRows(key) {
      if (!this.state) {
        return;
      }
      if (this.fullRowsAbort) {
        this.fullRowsAbort.abort();
      }
      const abortController = new AbortController();
      this.fullRowsAbort = abortController;
      this.fullRowsKey = key;
      this.fullRows = null;
      runExploration(Object.assign(Object.assign({}, this.state), {}, {
        limit: this.maxRows
      }), abortController).then(rows => {
        if (!this.unmounted && !abortController.signal.aborted && this.lastDefinition === key) {
          this.fullRows = rows;
        }
      }).catch(() => {
        if (this.lastDefinition === key) {
          // the same setup may be asked again later
          this.fullRowsKey = '';
        }
      });
    },
    compareExportUrl(format) {
      return this.state ? exportUrl(this.state, format, this.comparison.columns) : '';
    },
    /** the exact rest of the shown table, asked by "Calculate" (design 2026-11-20: on demand) */
    calculateOthers() {
      if (!this.state) {
        return;
      }
      const id = this.othersGuard.start();
      this.othersState = 'loading';
      runOthers(this.state).then(others => {
        if (this.othersGuard.isLatest(id)) {
          ({
            others: this.others,
            othersState: this.othersState
          } = othersAnswered(others));
        }
      }).catch(() => {
        if (this.othersGuard.isLatest(id)) {
          this.othersState = 'failed';
        }
      });
    },
    start() {
      if (!this.state || this.unmounted) {
        this.queue.finish();
        return;
      }
      // a new table: an earlier rest no longer belongs to it, and a pending one is dropped
      this.othersGuard.start();
      this.others = null;
      this.othersState = 'idle';
      const abortController = new AbortController();
      this.abortController = abortController;
      const id = this.guard.start();
      const started = Date.now();
      this.elapsedMs = null;
      const latest = () => this.guard.isLatest(id) && !this.queue.isPending();
      const request = this.comparing ? this.runCompared(this.state, latest, abortController) : runExploration(this.state, abortController).then(rows => {
        if (latest()) {
          this.rows = rows;
        }
      });
      request.then(() => {
        if (latest()) {
          this.elapsedMs = Date.now() - started;
        }
      }).catch(e => {
        if (this.guard.isLatest(id) && !this.queue.isPending() && !abortController.signal.aborted) {
          this.error = errorText(e);
        }
      }).finally(() => {
        if (this.queue.finish()) {
          this.start();
        } else if (this.guard.isLatest(id)) {
          this.loading = false;
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue



ExplorationPagevue_type_script_lang_ts.render = render

/* harmony default export */ var ExplorationPage = (ExplorationPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FunnelPage.vue?vue&type=template&id=3361cca2

const FunnelPagevue_type_template_id_3361cca2_hoisted_1 = {
  class: "fwde-page"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_2 = {
  key: 2,
  class: "fwde-page__layout"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_3 = {
  class: "fwde-card fwde-sidebar"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_4 = {
  key: 0,
  class: "fwde-section fwde-section--saved"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_5 = {
  class: "fwde-section"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_6 = {
  class: "fwde-section__title"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_7 = {
  class: "fwde-page__content"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_8 = {
  class: "fwde-card fwde-main"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_9 = {
  class: "fwde-card__header"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_10 = {
  class: "fwde-card__heading"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_11 = {
  class: "fwde-card__title"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_12 = {
  key: 0,
  class: "fwde-card__changed"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_13 = {
  class: "fwde-card__body"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_14 = {
  key: 5,
  class: "fwde-funnel__hint"
};
const FunnelPagevue_type_template_id_3361cca2_hoisted_15 = {
  key: 6,
  class: "pk-emptyDataTable"
};
function FunnelPagevue_type_template_id_3361cca2_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_SavedPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SavedPicker");
  const _component_StepEditor = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("StepEditor");
  const _component_ExplorationToolbar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ExplorationToolbar");
  const _component_SaveDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveDialog");
  const _component_FunnelResult = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FunnelResult");
  const _component_FeedbackBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeedbackBar");
  const _component_PromoLine = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PromoLine");
  const _component_PremiumBanner = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("PremiumBanner");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_1, [!_ctx.steps && !_ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ActivityIndicator, {
    key: 0,
    loading: true
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.loadError ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.loadError), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.steps ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("aside", FunnelPagevue_type_template_id_3361cca2_hoisted_3, [_ctx.savedLoaded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("section", FunnelPagevue_type_template_id_3361cca2_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SavedPicker, {
    saved: _ctx.saved,
    current: _ctx.currentSaved,
    login: _ctx.login,
    "can-copy": _ctx.canCopy,
    "can-manage": _ctx.canManage,
    onOpen: _ctx.openSaved,
    onDeleted: _cache[0] || (_cache[0] = $event => _ctx.loadSavedList())
  }, null, 8, ["saved", "current", "login", "can-copy", "can-manage", "onOpen"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", FunnelPagevue_type_template_id_3361cca2_hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", FunnelPagevue_type_template_id_3361cca2_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelSteps')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_StepEditor, {
    steps: _ctx.steps,
    goals: _ctx.goals,
    "max-steps": _ctx.maxSteps,
    onUpdate: _ctx.update
  }, null, 8, ["steps", "goals", "max-steps", "onUpdate"])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", FunnelPagevue_type_template_id_3361cca2_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", FunnelPagevue_type_template_id_3361cca2_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", FunnelPagevue_type_template_id_3361cca2_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), _ctx.savedChanged ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", FunnelPagevue_type_template_id_3361cca2_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedChanged')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ExplorationToolbar, {
    class: "fwde-card__actions",
    "export-url-for": _ctx.complete ? _ctx.exportUrlFor : undefined
  }, null, 8, ["export-url-for"]), _ctx.savedLoaded && _ctx.complete ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveDialog, {
    key: 0,
    class: "fwde-card__save",
    current: _ctx.currentSaved,
    "suggested-name": _ctx.suggestedName,
    "can-share": _ctx.canShare,
    busy: _ctx.saveBusy,
    error: _ctx.saveError,
    onOpen: _cache[1] || (_cache[1] = $event => _ctx.saveError = ''),
    onSave: _ctx.saveFunnel,
    onDelete: _ctx.deleteCurrent
  }, null, 8, ["current", "suggested-name", "can-share", "busy", "error", "onSave", "onDelete"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_13, [_ctx.showRawDataWarning ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 0,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RawDataWarning', _ctx.rawDataFrom)) + " ", 1), _ctx.rawDataRange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwde-alert-link",
      onClick: _cache[2] || (_cache[2] = $event => _ctx.showFrom(_ctx.rawDataRange))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ShowFromFirstCompleteDay')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.largePeriodHint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 1,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.largePeriodHint), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 2,
    severity: "danger"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error) + " ", 1), _ctx.canRetry ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwde-alert-link fwde-retry",
      onClick: _cache[3] || (_cache[3] = (...args) => _ctx.run && _ctx.run(...args))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_TryAgain')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.slowHint ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 3,
    severity: "info"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.slowHint), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.suggestedPeriod ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 4,
    severity: "info"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [_ctx.suggestedPeriod.date ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 0
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SmallerPeriodHint')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-alert-link fwde-suggested-period",
      onClick: _cache[4] || (_cache[4] = $event => _ctx.showFrom(_ctx.suggestedPeriod.date))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.suggestedPeriodLabel), 1)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.noSmallerPeriodKey)), 1)], 64))]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.complete ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelNoSteps')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.loading,
    "loading-message": _ctx.translate('FeinwerkDataExploration_LoadingExploration')
  }, null, 8, ["loading", "loading-message"]), _ctx.complete && !_ctx.loading && !_ctx.error && _ctx.rows && _ctx.isEmpty ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FunnelPagevue_type_template_id_3361cca2_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ThereIsNoDataForThisReport')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.complete && !_ctx.error && _ctx.rows && !_ctx.isEmpty ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FunnelResult, {
    key: 7,
    rows: _ctx.rows,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'fwde-main__stale': _ctx.loading
    })
  }, null, 8, ["rows", "class"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.complete && !_ctx.error && _ctx.rows ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FeedbackBar, {
    key: 8,
    "id-site": _ctx.idSite,
    topic: "funnel",
    question: _ctx.translate('FeinwerkDataExploration_FeedbackQuestion_funnel'),
    feedback: _ctx.feedback
  }, null, 8, ["id-site", "question", "feedback"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.complete && !_ctx.error && _ctx.rows ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_PromoLine, {
    key: 9,
    promo: _ctx.promo,
    "dismiss-label": _ctx.translate('FeinwerkDataExploration_PromoDismiss')
  }, null, 8, ["promo", "dismiss-label"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_PremiumBanner, {
    premium: _ctx.premium,
    label: _ctx.translate('FeinwerkDataExploration_PremiumLabel'),
    "more-label": _ctx.translate('FeinwerkDataExploration_PremiumMore'),
    "dismiss-label": _ctx.translate('FeinwerkDataExploration_PremiumDismiss')
  }, null, 8, ["premium", "label", "more-label", "dismiss-label"])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FunnelPage.vue?vue&type=template&id=3361cca2

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/StepEditor.vue?vue&type=template&id=e7f4b98a

const StepEditorvue_type_template_id_e7f4b98a_hoisted_1 = {
  class: "fwde-steps"
};
const StepEditorvue_type_template_id_e7f4b98a_hoisted_2 = {
  class: "fwde-steps__list"
};
const StepEditorvue_type_template_id_e7f4b98a_hoisted_3 = {
  class: "fwde-step__number",
  "aria-hidden": "true"
};
const StepEditorvue_type_template_id_e7f4b98a_hoisted_4 = {
  class: "fwde-step__fields"
};
const StepEditorvue_type_template_id_e7f4b98a_hoisted_5 = ["value", "aria-label", "onChange"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_6 = ["value"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_7 = ["value", "aria-label", "onChange"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_8 = ["value"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_9 = ["value", "maxlength", "aria-label", "onInput"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_10 = ["value", "aria-label", "onChange"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_11 = {
  value: ""
};
const StepEditorvue_type_template_id_e7f4b98a_hoisted_12 = ["value"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_13 = ["disabled", "title", "aria-label", "onClick"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-close",
  "aria-hidden": "true"
}, null, -1);
const StepEditorvue_type_template_id_e7f4b98a_hoisted_15 = [StepEditorvue_type_template_id_e7f4b98a_hoisted_14];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_16 = {
  class: "fwde-steps__add"
};
const StepEditorvue_type_template_id_e7f4b98a_hoisted_17 = ["disabled"];
const StepEditorvue_type_template_id_e7f4b98a_hoisted_18 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add",
  "aria-hidden": "true"
}, null, -1);
const StepEditorvue_type_template_id_e7f4b98a_hoisted_19 = {
  key: 0,
  class: "fwde-steps__limit"
};
function StepEditorvue_type_template_id_e7f4b98a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", StepEditorvue_type_template_id_e7f4b98a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", StepEditorvue_type_template_id_e7f4b98a_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.local, (step, i) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: i,
      class: "fwde-step"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", StepEditorvue_type_template_id_e7f4b98a_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(i + 1), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StepEditorvue_type_template_id_e7f4b98a_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
      class: "browser-default fwde-select fwde-step__type",
      value: step.type,
      "aria-label": _ctx.translate('FeinwerkDataExploration_FunnelStepType', String(i + 1)),
      onChange: $event => _ctx.setType(i, $event)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.types, type => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: type,
        value: type
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.typeKey(type))), 9, StepEditorvue_type_template_id_e7f4b98a_hoisted_6);
    }), 128))], 40, StepEditorvue_type_template_id_e7f4b98a_hoisted_5), !_ctx.isGoalType(step.type) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("select", {
      key: 0,
      class: "browser-default fwde-select fwde-step__operator",
      value: step.operator,
      "aria-label": _ctx.translate('FeinwerkDataExploration_FilterCondition'),
      onChange: $event => _ctx.setOperator(i, $event)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.operators(step), operator => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: operator,
        value: operator
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.operatorKey(operator))), 9, StepEditorvue_type_template_id_e7f4b98a_hoisted_8);
    }), 128))], 40, StepEditorvue_type_template_id_e7f4b98a_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.isGoalType(step.type) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("input", {
      key: 1,
      type: "text",
      class: "browser-default fwde-input fwde-step__value",
      value: step.value,
      maxlength: _ctx.maxText,
      "aria-label": _ctx.translate('FeinwerkDataExploration_FilterValue'),
      onInput: $event => _ctx.setValue(i, $event)
    }, null, 40, StepEditorvue_type_template_id_e7f4b98a_hoisted_9)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), step.type === 'goal' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("select", {
      key: 2,
      class: "browser-default fwde-select fwde-step__goal",
      value: step.idGoal === null ? '' : String(step.idGoal),
      "aria-label": _ctx.translate('FeinwerkDataExploration_FunnelTypeGoal'),
      onChange: $event => _ctx.setGoal(i, $event)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", StepEditorvue_type_template_id_e7f4b98a_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelChooseGoal')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.goals, goal => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: goal.id,
        value: String(goal.id)
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.name), 9, StepEditorvue_type_template_id_e7f4b98a_hoisted_12);
    }), 128))], 40, StepEditorvue_type_template_id_e7f4b98a_hoisted_10)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-icon-button fwde-step__remove",
      disabled: _ctx.local.length <= 2,
      title: _ctx.translate('FeinwerkDataExploration_FunnelRemoveStep'),
      "aria-label": _ctx.translate('FeinwerkDataExploration_FunnelRemoveStep'),
      onClick: $event => _ctx.remove(i)
    }, StepEditorvue_type_template_id_e7f4b98a_hoisted_15, 8, StepEditorvue_type_template_id_e7f4b98a_hoisted_13)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", StepEditorvue_type_template_id_e7f4b98a_hoisted_16, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button fwde-steps__add-button",
    disabled: _ctx.local.length >= _ctx.maxSteps,
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.add && _ctx.add(...args))
  }, [StepEditorvue_type_template_id_e7f4b98a_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelAddStep')), 1)], 8, StepEditorvue_type_template_id_e7f4b98a_hoisted_17), _ctx.local.length >= _ctx.maxSteps ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", StepEditorvue_type_template_id_e7f4b98a_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelMaxSteps', String(_ctx.maxSteps))), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/StepEditor.vue?vue&type=template&id=e7f4b98a

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/StepEditor.vue?vue&type=script&lang=ts



/** As the filter editor: the page follows the typed text after this pause. */
const TYPING_PAUSE_MS = 600;
/** Translation keys, written out so the translations test finds them. */
const TYPE_KEYS = {
  pageUrl: 'FeinwerkDataExploration_FunnelTypePageUrl',
  pageTitle: 'FeinwerkDataExploration_FunnelTypePageTitle',
  eventCategory: 'FeinwerkDataExploration_FunnelTypeEventCategory',
  eventAction: 'FeinwerkDataExploration_FunnelTypeEventAction',
  eventName: 'FeinwerkDataExploration_FunnelTypeEventName',
  siteSearch: 'FeinwerkDataExploration_FunnelTypeSiteSearch',
  download: 'FeinwerkDataExploration_FunnelTypeDownload',
  outlink: 'FeinwerkDataExploration_FunnelTypeOutlink',
  goal: 'FeinwerkDataExploration_FunnelTypeGoal',
  anyGoal: 'FeinwerkDataExploration_FunnelTypeAnyGoal'
};
const StepEditorvue_type_script_lang_ts_OPERATOR_KEYS = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  startsWith: 'FeinwerkDataExploration_OperatorStartsWith'
};
/* harmony default export */ var StepEditorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    steps: {
      type: Array,
      required: true
    },
    goals: {
      type: Array,
      default: () => []
    },
    maxSteps: {
      type: Number,
      required: true
    }
  },
  emits: ['update'],
  data() {
    return {
      maxText: MAX_STEP_TEXT,
      /** the steps as edited: typing changes them at once and the page after a pause */
      local: this.steps.map(s => Object.assign({}, s)),
      timer: 0,
      typing: false
    };
  },
  watch: {
    steps(steps) {
      // a link, the back button or a saved funnel; while typing the field keeps what is typed
      if (!this.typing) {
        this.local = steps.map(s => Object.assign({}, s));
      }
    }
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  computed: {
    /** goal steps only where the website has goals */
    types() {
      return STEP_TYPES.filter(type => type !== 'goal' || this.goals.length > 0);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    isGoalType: isGoalType,
    typeKey(type) {
      return TYPE_KEYS[type];
    },
    operatorKey(operator) {
      return StepEditorvue_type_script_lang_ts_OPERATOR_KEYS[operator];
    },
    operators(step) {
      return OPERATORS_BY_TYPE[step.type] || [];
    },
    /** a choice or a button: the page gets the steps at once (with any text still pending) */
    emitWith(index, step) {
      this.emitNow(this.local.map((s, i) => i === index ? step : s));
    },
    emitNow(steps) {
      window.clearTimeout(this.timer);
      this.typing = false;
      this.local = steps;
      this.$emit('update', steps);
    },
    setType(index, event) {
      const type = event.target.value;
      this.emitWith(index, withType(this.local[index], type));
    },
    setOperator(index, event) {
      const operator = event.target.value;
      this.emitWith(index, Object.assign(Object.assign({}, this.local[index]), {}, {
        operator
      }));
    },
    /** typing: one update after a pause, so a key is neither a history entry nor a query */
    setValue(index, event) {
      const {
        value
      } = event.target;
      this.local = this.local.map((s, i) => i === index ? Object.assign(Object.assign({}, s), {}, {
        value
      }) : s);
      this.typing = true;
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => this.emitNow(this.local), TYPING_PAUSE_MS);
    },
    setGoal(index, event) {
      const {
        value
      } = event.target;
      this.emitWith(index, Object.assign(Object.assign({}, this.local[index]), {}, {
        idGoal: value === '' ? null : Number(value)
      }));
    },
    add() {
      if (this.local.length < this.maxSteps) {
        this.emitNow([...this.local, emptyStep()]);
      }
    },
    remove(index) {
      if (this.local.length > 2) {
        this.emitNow(this.local.filter((s, i) => i !== index));
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/StepEditor.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/StepEditor.vue



StepEditorvue_type_script_lang_ts.render = StepEditorvue_type_template_id_e7f4b98a_render

/* harmony default export */ var StepEditor = (StepEditorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FunnelResult.vue?vue&type=template&id=710f493a

const FunnelResultvue_type_template_id_710f493a_hoisted_1 = {
  class: "fwde-funnel"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_2 = {
  class: "fwde-funnel__bars"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_3 = {
  class: "fwde-funnel__head"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_4 = {
  class: "fwde-funnel__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_5 = {
  class: "fwde-funnel__label"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_6 = {
  class: "fwde-funnel__visits"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_7 = {
  class: "fwde-funnel__share"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_8 = {
  class: "fwde-funnel__track",
  "aria-hidden": "true"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_9 = {
  key: 0,
  class: "fwde-funnel__dropoffs"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_10 = {
  class: "fwde-table"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_11 = {
  class: "fwde-table__scroller"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_12 = {
  class: "dataTable fwde-table__table fwde-funnel__table"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_13 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_14 = {
  class: "label"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_15 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_16 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_17 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_18 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_19 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_20 = {
  class: "fwde-table__label"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_21 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_22 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_23 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_24 = {
  class: "fwde-table__number"
};
const FunnelResultvue_type_template_id_710f493a_hoisted_25 = {
  key: 0,
  class: "fwde-funnel__total"
};
function FunnelResultvue_type_template_id_710f493a_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FunnelResultvue_type_template_id_710f493a_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ol", FunnelResultvue_type_template_id_710f493a_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.rows, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: row.step,
      class: "fwde-funnel__step"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelResultvue_type_template_id_710f493a_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FunnelResultvue_type_template_id_710f493a_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.step), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FunnelResultvue_type_template_id_710f493a_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.label), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FunnelResultvue_type_template_id_710f493a_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.number(row.nb_visits)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FunnelResultvue_type_template_id_710f493a_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.percent(row.share_of_start)), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelResultvue_type_template_id_710f493a_hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      class: "fwde-funnel__bar",
      style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
        width: `${_ctx.barPercent(row)}%`
      })
    }, null, 4)]), row.step > 1 && row.nb_dropoffs > 0 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FunnelResultvue_type_template_id_710f493a_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelDropoffs', _ctx.number(row.nb_dropoffs))), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 128))]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelResultvue_type_template_id_710f493a_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FunnelResultvue_type_template_id_710f493a_hoisted_11, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", FunnelResultvue_type_template_id_710f493a_hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", FunnelResultvue_type_template_id_710f493a_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelColumnStep')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", FunnelResultvue_type_template_id_710f493a_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelColumnCondition')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", FunnelResultvue_type_template_id_710f493a_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_ColumnNbVisits')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", FunnelResultvue_type_template_id_710f493a_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelColumnShareOfStart')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", FunnelResultvue_type_template_id_710f493a_hoisted_17, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelColumnShareOfPrevious')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", FunnelResultvue_type_template_id_710f493a_hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelColumnDropoffs')), 1)])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.rows, row => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: row.step
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FunnelResultvue_type_template_id_710f493a_hoisted_19, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.step), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FunnelResultvue_type_template_id_710f493a_hoisted_20, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(row.label), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FunnelResultvue_type_template_id_710f493a_hoisted_21, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.number(row.nb_visits)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FunnelResultvue_type_template_id_710f493a_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.percent(row.share_of_start)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FunnelResultvue_type_template_id_710f493a_hoisted_23, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.percent(row.share_of_previous)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", FunnelResultvue_type_template_id_710f493a_hoisted_24, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.number(row.nb_dropoffs)), 1)]);
  }), 128))])])])]), _ctx.total !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", FunnelResultvue_type_template_id_710f493a_hoisted_25, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FunnelTotal', _ctx.percent(_ctx.total), String(_ctx.rows.length))), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FunnelResult.vue?vue&type=template&id=710f493a

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/funnelResult.ts
/** The share of step 1 that reached the last step, null when step 1 has no visits. */
function totalRate(rows) {
  if (!rows.length || rows[0].nb_visits <= 0) {
    return null;
  }
  return rows[rows.length - 1].share_of_start;
}
/** The width of a step's bar: its share of step 1, within 0 to 100. */
function barPercent(row) {
  return Math.max(0, Math.min(100, row.share_of_start));
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FunnelResult.vue?vue&type=script&lang=ts



/* harmony default export */ var FunnelResultvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    rows: {
      type: Array,
      required: true
    }
  },
  computed: {
    total() {
      return totalRate(this.rows);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    barPercent: barPercent,
    number(value) {
      return external_CoreHome_["NumberFormatter"].formatNumber(value, 0, 0);
    },
    percent(value) {
      return external_CoreHome_["NumberFormatter"].formatPercent(value, 1, 0);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FunnelResult.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FunnelResult.vue



FunnelResultvue_type_script_lang_ts.render = FunnelResultvue_type_template_id_710f493a_render

/* harmony default export */ var FunnelResult = (FunnelResultvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FunnelPage.vue?vue&type=script&lang=ts


















/**
 * The funnel page (spec 3): steps in the hash parameter fwdeSteps, so a link, the back button and
 * Matomo's period selector and segment editor drive it as they drive the free-form table.
 */
/* harmony default export */ var FunnelPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  inheritAttrs: false,
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"],
    StepEditor: StepEditor,
    FunnelResult: FunnelResult,
    ExplorationToolbar: ExplorationToolbar,
    SavedPicker: SavedPicker,
    SaveDialog: SaveDialog,
    FeedbackBar: FeedbackBar,
    PromoLine: PromoLine,
    PremiumBanner: PremiumBanner
  },
  props: {
    idSite: {
      type: Number,
      required: true
    },
    maxSteps: {
      type: Number,
      required: true
    },
    maxDays: {
      type: Number,
      required: true
    },
    rawDataFrom: {
      type: String,
      default: null
    },
    canShare: {
      type: Boolean,
      default: false
    },
    canCopy: {
      type: Boolean,
      default: false
    },
    /** admin access to the website: manage its shared explorations */
    canManage: {
      type: Boolean,
      default: false
    },
    promo: {
      type: Object,
      default: null
    },
    /** the premium banner below the result (PremiumPromo); null: none, the tip line may show */
    premium: {
      type: Object,
      default: null
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    }
  },
  data() {
    return {
      saved: [],
      savedLoaded: false,
      saveBusy: false,
      saveError: '',
      goals: [],
      steps: null,
      rows: null,
      loading: false,
      error: '',
      loadError: '',
      lastDefinition: '',
      periodVisits: null,
      elapsedMs: null,
      suggestedPeriod: null,
      runTimer: 0,
      abortController: null,
      guard: createRequestGuard(),
      queue: createRunQueue(),
      unmounted: false
    };
  },
  created() {
    this.loadSavedList(true);
    loadGoals().then(goals => {
      this.goals = goals;
      this.readState();
    }).catch(e => {
      this.loadError = errorText(e);
    });
    // warnings only: without them the page works as before
    loadPeriodVisits().then(periodVisits => {
      this.periodVisits = periodVisits;
      this.offerSmallerPeriod(false);
    }).catch(() => {
      this.offerSmallerPeriod(true);
    });
  },
  beforeUnmount() {
    this.unmounted = true;
    window.clearTimeout(this.runTimer);
    if (this.abortController) {
      this.abortController.abort();
    }
  },
  computed: {
    hashState() {
      var _MatomoUrl$hashParsed;
      return String((_MatomoUrl$hashParsed = external_CoreHome_["MatomoUrl"].hashParsed.value.fwdeSteps) !== null && _MatomoUrl$hashParsed !== void 0 ? _MatomoUrl$hashParsed : '');
    },
    complete() {
      return !!this.steps && canRun(this.steps);
    },
    isEmpty() {
      return !this.rows || !this.rows.length || this.rows[0].nb_visits === 0;
    },
    currentSaved() {
      return findSaved(this.saved, String(external_CoreHome_["MatomoUrl"].parsed.value.fwdeSaved || ''), 'funnel');
    },
    title() {
      return this.currentSaved ? this.currentSaved.name : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_Funnel');
    },
    /** The page shows other steps or another segment than the open saved funnel. */
    savedChanged() {
      const current = this.currentSaved;
      if (!current || !this.steps || savedKind(current) !== 'funnel') {
        return false;
      }
      return funnelDiffersFromSaved(current, toStepsParam(this.steps), String(external_CoreHome_["MatomoUrl"].parsed.value.segment || ''), this.goals.map(g => g.id), this.maxSteps);
    },
    /** e.g. "/products > /cart > Order", the name a new saved funnel is offered */
    suggestedName() {
      const short = step => {
        if (step.type === 'anyGoal') {
          return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_FunnelTypeAnyGoal');
        }
        if (step.type === 'goal') {
          const goal = this.goals.find(g => g.id === step.idGoal);
          return goal ? goal.name : '';
        }
        return step.value.trim();
      };
      return (this.steps || []).map(short).join(' > ').slice(0, 100);
    },
    login() {
      const {
        piwik
      } = window;
      return String(piwik && piwik.userLogin || '');
    },
    largePeriodHint() {
      return largePeriodHint(this.periodVisits);
    },
    slowHint() {
      return slowHint(this.elapsedMs, this.periodVisits, this.loading, this.error);
    },
    suggestedPeriodLabel() {
      var _this$suggestedPeriod;
      return suggestedPeriodLabel(((_this$suggestedPeriod = this.suggestedPeriod) === null || _this$suggestedPeriod === void 0 ? void 0 : _this$suggestedPeriod.days) || 0);
    },
    canRetry() {
      return retryAllowed(this.error);
    },
    noSmallerPeriodKey() {
      return noSmallerPeriodKeyForUser();
    },
    rawDataRange() {
      return rawDataRange(this.rawDataFrom);
    },
    showRawDataWarning() {
      return showsRawDataWarning(this.rawDataFrom);
    }
  },
  watch: {
    hashState() {
      // back/forward or a pasted link changed the steps
      this.readState();
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    exportUrlFor(format) {
      return funnelExportUrl(this.steps || [], format);
    },
    readState() {
      const goalIds = this.goals.map(g => g.id);
      const steps = parseSteps(external_CoreHome_["MatomoUrl"].parsed.value.fwdeSteps, goalIds, this.maxSteps);
      this.steps = steps;
      if (!canRun(steps)) {
        window.clearTimeout(this.runTimer);
        this.lastDefinition = '';
        this.loading = false;
        return;
      }
      const definition = JSON.stringify(toFunnelDefinition(steps));
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '' && this.rows === null;
        this.lastDefinition = definition;
        // a short pause, so typing starts one request; the result is dimmed meanwhile
        window.clearTimeout(this.runTimer);
        if (first) {
          this.run();
        } else {
          this.loading = true;
          this.runTimer = window.setTimeout(() => this.run(), RUN_DELAY_MS);
        }
      }
    },
    update(steps) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        fwdeSteps: toStepsParam(steps)
      }));
    },
    /** Asks for the most recent days that fit when the period is too large or too long. */
    offerSmallerPeriod(countFailed) {
      if (!needsSmallerPeriod(this.periodVisits, countFailed)) {
        return;
      }
      loadSuggestedPeriod().then(suggestion => {
        this.suggestedPeriod = suggestion || {};
      }).catch(() => undefined);
    },
    showFrom(range) {
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
        period: 'range',
        date: range
      }));
    },
    /**
     * The saved explorations of both kinds; without them the page works as before.
     *
     * @param openFromLink a link that names only a saved funnel (a bookmark) opens its steps
     */
    loadSavedList(openFromLink = false) {
      return loadSaved().then(list => {
        this.saved = list;
        this.savedLoaded = true;
        const linked = this.currentSaved;
        if (openFromLink && linked && linkNamesOnlySaved(external_CoreHome_["MatomoUrl"].hashParsed.value)) {
          this.openSaved(linked);
        }
      }).catch(() => undefined);
    },
    /** Opens a saved exploration on its page (a table opens the table page), or a new funnel. */
    openSaved(saved) {
      const hash = Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value);
      if (saved) {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, hash), savedHashParams(saved)));
        return;
      }
      STATE_KEYS.forEach(key => {
        hash[key] = '';
      });
      external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, hash), {}, {
        fwdeSaved: ''
      }));
    },
    saveFunnel(event) {
      if (!this.steps) {
        return;
      }
      const fields = {
        name: event.name,
        state: stateToSave({
          fwdeSteps: toStepsParam(this.steps)
        }, 'funnel'),
        segment: String(external_CoreHome_["MatomoUrl"].parsed.value.segment || ''),
        shared: event.shared
      };
      const current = this.currentSaved;
      const request = current && current.canEdit && !event.asNew && savedKind(current) === 'funnel' ? updateSaved(current.id, fields).then(() => current.id) : addSaved(fields).then(result => Number(result.id));
      this.saveBusy = true;
      this.saveError = '';
      request.then(id => this.loadSavedList().then(() => {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          fwdeSaved: String(id)
        }));
      })).catch(e => {
        this.saveError = errorText(e);
      }).finally(() => {
        this.saveBusy = false;
      });
    },
    deleteCurrent() {
      const current = this.currentSaved;
      if (!current) {
        return;
      }
      this.saveBusy = true;
      this.saveError = '';
      deleteSaved(current.id).then(() => this.loadSavedList()).then(() => {
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          fwdeSaved: ''
        }));
      }).catch(e => {
        this.saveError = errorText(e);
      }).finally(() => {
        this.saveBusy = false;
      });
    },
    run() {
      if (!this.steps || !canRun(this.steps)) {
        return;
      }
      this.loading = true;
      this.error = '';
      // one funnel at a time: a change while one runs starts when it ends (see runQueue)
      if (this.queue.request()) {
        this.start();
      }
    },
    start() {
      if (!this.steps || this.unmounted) {
        this.queue.finish();
        return;
      }
      const abortController = new AbortController();
      this.abortController = abortController;
      const id = this.guard.start();
      const started = Date.now();
      this.elapsedMs = null;
      runFunnel(this.steps, abortController).then(rows => {
        if (this.guard.isLatest(id) && !this.queue.isPending()) {
          this.rows = rows;
          this.elapsedMs = Date.now() - started;
        }
      }).catch(e => {
        if (this.guard.isLatest(id) && !this.queue.isPending() && !abortController.signal.aborted) {
          this.error = errorText(e);
        }
      }).finally(() => {
        if (this.queue.finish()) {
          this.start();
        } else if (this.guard.isLatest(id)) {
          this.loading = false;
        }
      });
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FunnelPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FunnelPage.vue



FunnelPagevue_type_script_lang_ts.render = FunnelPagevue_type_template_id_3361cca2_render

/* harmony default export */ var FunnelPage = (FunnelPagevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SavedReportFooter.vue?vue&type=template&id=08f76566

const SavedReportFootervue_type_template_id_08f76566_hoisted_1 = {
  key: 0,
  class: "fwde-page fwde-saved-footer"
};
const SavedReportFootervue_type_template_id_08f76566_hoisted_2 = ["href"];
function SavedReportFootervue_type_template_id_08f76566_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_FeedbackBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeedbackBar");
  return !_ctx.embedded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SavedReportFootervue_type_template_id_08f76566_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
    class: "fwde-saved-footer__open",
    href: _ctx.exploreUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_OpenInExploration')), 9, SavedReportFootervue_type_template_id_08f76566_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FeedbackBar, {
    "id-site": _ctx.idSite,
    topic: "saved_report",
    question: _ctx.translate('FeinwerkDataExploration_FeedbackQuestion_saved_report'),
    feedback: _ctx.shownFeedback
  }, null, 8, ["id-site", "question", "feedback"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedReportFooter.vue?vue&type=template&id=08f76566

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/embed.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
/**
 * Whether the page itself is an embed (Widgetize, disableLink=1), seen by the website's own
 * viewers: then no feedback question and no link into Matomo (house rule "Feedback under every
 * report"). The server checks its request too (Widgets/WidgetProps::isEmbedded), but Matomo loads
 * the widgets of an embedded container or dashboard with their own module, so only the page's
 * address tells (release review 2026-11-25).
 */
function embeddedPage(search) {
  const params = new URLSearchParams(search);
  return (params.get('module') || '').trim() === 'Widgetize' || params.get('disableLink') === '1';
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SavedReportFooter.vue?vue&type=script&lang=ts




/**
 * The footer under a saved exploration's report (saved reports, 2026-11-24), on its menu page and
 * in its dashboard widget: "Open in exploration" and the feedback line; nothing in an embed.
 */
/* harmony default export */ var SavedReportFootervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FeedbackBar: FeedbackBar
  },
  props: {
    idSite: {
      type: Number,
      required: true
    },
    /** the free-form table with the saved exploration, same website and period */
    exploreUrl: {
      type: String,
      required: true
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    }
  },
  data() {
    // an embed (Widgetize) shows no footer: its request does not say so, the page's address does
    return {
      embedded: embeddedPage(window.location.search),
      inDashboard: false
    };
  },
  computed: {
    /** a dashboard widget has no support link (house rule), the report's own menu page keeps it */
    shownFeedback() {
      return this.inDashboard ? Object.assign(Object.assign({}, this.feedback), {}, {
        supportUrl: ''
      }) : this.feedback;
    }
  },
  mounted() {
    // Matomo's dashboard puts a header (.widgetTop) over each widget; a menu page has none
    const el = this.$el;
    const widget = el && el.closest ? el.closest('.widget') : null;
    this.inDashboard = !!(widget && widget.querySelector('.widgetTop'));
  },
  methods: {
    translate: external_CoreHome_["translate"]
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedReportFooter.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedReportFooter.vue



SavedReportFootervue_type_script_lang_ts.render = SavedReportFootervue_type_template_id_08f76566_render

/* harmony default export */ var SavedReportFooter = (SavedReportFootervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/KpiCard.vue?vue&type=template&id=0b54f2b6

const KpiCardvue_type_template_id_0b54f2b6_hoisted_1 = {
  class: "fwde-page fwde-kpi-widget"
};
function KpiCardvue_type_template_id_0b54f2b6_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_KpiCardView = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("KpiCardView");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KpiCardvue_type_template_id_0b54f2b6_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.loading
  }, null, 8, ["loading"]), _ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 0,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error), 1)]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.card ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_KpiCardView, {
    key: 1,
    card: _ctx.card,
    "id-site": _ctx.idSite,
    "explore-url": _ctx.exploreUrl,
    "currency-symbol": _ctx.currencySymbol,
    feedback: _ctx.feedback
  }, null, 8, ["card", "id-site", "explore-url", "currency-symbol", "feedback"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/KpiCard.vue?vue&type=template&id=0b54f2b6

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/KpiCardView.vue?vue&type=template&id=19779886

const KpiCardViewvue_type_template_id_19779886_hoisted_1 = ["title"];
const KpiCardViewvue_type_template_id_19779886_hoisted_2 = {
  key: 1,
  class: "fwde-kpi__metric"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_3 = {
  class: "fwde-kpi__main"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_4 = {
  class: "fwde-kpi__value"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_5 = {
  key: 0,
  class: "fwde-kpi__partial"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_6 = {
  key: 2,
  class: "fwde-kpi__compare"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_7 = ["title"];
const KpiCardViewvue_type_template_id_19779886_hoisted_8 = {
  key: 3,
  class: "fwde-kpi__note"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_9 = {
  key: 4,
  class: "fwde-kpi__spark"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_10 = ["viewBox", "aria-label"];
const KpiCardViewvue_type_template_id_19779886_hoisted_11 = ["d"];
const KpiCardViewvue_type_template_id_19779886_hoisted_12 = ["title"];
const KpiCardViewvue_type_template_id_19779886_hoisted_13 = {
  key: 5,
  class: "fwde-kpi__note"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_14 = {
  key: 6,
  class: "fwde-kpi__footer"
};
const KpiCardViewvue_type_template_id_19779886_hoisted_15 = ["href"];
function KpiCardViewvue_type_template_id_19779886_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_FeedbackBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeedbackBar");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-page', 'fwde-kpi', {
      'fwde-kpi--in-widget': _ctx.hasHeader
    }])
  }, [!_ctx.hasHeader ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
    key: 0,
    class: "fwde-kpi__title",
    title: _ctx.card.metricName
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.card.title), 9, KpiCardViewvue_type_template_id_19779886_hoisted_1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.showMetric ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KpiCardViewvue_type_template_id_19779886_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.card.metricName), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", KpiCardViewvue_type_template_id_19779886_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", KpiCardViewvue_type_template_id_19779886_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.valueText), 1), _ctx.card.partial ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", KpiCardViewvue_type_template_id_19779886_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_KpiSoFar', _ctx.card.until || '')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.card.comparison ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KpiCardViewvue_type_template_id_19779886_hoisted_6, [_ctx.change ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
    key: 0,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-kpi__change', `fwde-kpi__change--${_ctx.tone}`])
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.change), 3)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: "fwde-kpi__baseline",
    title: _ctx.baselineTitle
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.label), 9, KpiCardViewvue_type_template_id_19779886_hoisted_7)])) : _ctx.card.comparisonNote ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", KpiCardViewvue_type_template_id_19779886_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.card.comparisonNote), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.card.line && _ctx.card.line.length > 1 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KpiCardViewvue_type_template_id_19779886_hoisted_9, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
    viewBox: `0 0 ${_ctx.WIDTH} ${_ctx.HEIGHT}`,
    preserveAspectRatio: "none",
    role: "img",
    "aria-label": _ctx.lineLabel
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
    class: "fwde-kpi__line",
    d: _ctx.path
  }, null, 8, KpiCardViewvue_type_template_id_19779886_hoisted_11)], 8, KpiCardViewvue_type_template_id_19779886_hoisted_10)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.dots, dot => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", {
      key: dot.day,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(['fwde-kpi__dot', `fwde-kpi__dot--${dot.kind}`]),
      style: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeStyle"])({
        left: `${dot.left}%`,
        top: `${dot.top}%`
      }),
      title: dot.title
    }, null, 14, KpiCardViewvue_type_template_id_19779886_hoisted_12);
  }), 128))])) : _ctx.card.lineNote ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", KpiCardViewvue_type_template_id_19779886_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.card.lineNote), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.embedded && (_ctx.exploreUrl || _ctx.feedback.enabled) ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", KpiCardViewvue_type_template_id_19779886_hoisted_14, [_ctx.exploreUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 0,
    class: "fwde-kpi__open",
    href: _ctx.exploreUrl
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_OpenInExploration')), 9, KpiCardViewvue_type_template_id_19779886_hoisted_15)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_FeedbackBar, {
    "id-site": _ctx.idSite,
    topic: "kpi_card",
    question: _ctx.translate('FeinwerkDataExploration_FeedbackQuestion_kpi_card'),
    feedback: _ctx.feedback
  }, null, 8, ["id-site", "question", "feedback"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 2);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/KpiCardView.vue?vue&type=template&id=19779886

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/kpi.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function arrow(sign) {
  if (sign > 0) {
    return '▲';
  }
  return sign < 0 ? '▼' : '±';
}
/** "▲ 12 %" or "▼ 140" as the card's setting says; '' without a comparison or a change. */
function changeText(card, formatters) {
  const {
    comparison
  } = card;
  if (!comparison) {
    return '';
  }
  if (card.changeAs === 'absolute') {
    if (comparison.absolute === null) {
      return '';
    }
    const amount = formatMetric(card.metric, Math.abs(comparison.absolute), formatters);
    return `${arrow(Math.sign(comparison.absolute))} ${amount}`;
  }
  if (comparison.change === null) {
    return '';
  }
  const percent = Math.round(Math.abs(comparison.change) * 100);
  return `${arrow(percent === 0 ? 0 : Math.sign(comparison.change))} ${formatters.percent(percent)}`;
}
/** Whether the change is good news: up for most metrics, down where less is better. */
function changeTone(card) {
  const absolute = card.comparison ? card.comparison.absolute : null;
  if (absolute === null || absolute === 0) {
    return 'neutral';
  }
  const up = absolute > 0;
  return up !== card.lowerIsBetter ? 'good' : 'bad';
}
/** Matomo's name of a day's weekday (Intl_Day_Long_StandAlone_1 is Monday). */
function weekdayKey(day) {
  const weekday = new Date(`${day}T00:00:00Z`).getUTCDay();
  return `Intl_Day_Long_StandAlone_${weekday === 0 ? 7 : weekday}`;
}
/** What the change is measured against, e.g. "vs. ⌀ Wednesday, last 4 weeks"; '' without one. */
function comparisonLabel(card, translate) {
  const {
    comparison
  } = card;
  if (!comparison) {
    return '';
  }
  const {
    kind,
    period,
    used
  } = comparison;
  if (period === 'day' && kind === 'weekday') {
    return translate('FeinwerkDataExploration_KpiVsWeekday', translate(weekdayKey(card.lastDay)));
  }
  if (kind === 'average4') {
    if (period === 'day') {
      return translate('FeinwerkDataExploration_KpiVsAverageWeekdays', translate(weekdayKey(card.lastDay)), used);
    }
    return translate(period === 'week' ? 'FeinwerkDataExploration_KpiVsAverageWeeks' : 'FeinwerkDataExploration_KpiVsAverageMonths', used);
  }
  const keys = {
    day: 'FeinwerkDataExploration_KpiVsPreviousDay',
    week: 'FeinwerkDataExploration_KpiVsPreviousWeek',
    month: 'FeinwerkDataExploration_KpiVsPreviousMonth'
  };
  return translate(keys[period] || keys.day);
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/KpiCardView.vue?vue&type=script&lang=ts







const WIDTH = 300;
const HEIGHT = 44;
const PAD = 3;
/** a day of the card (YYYY-MM-DD) in words in Matomo's language, e.g. "Wed, Nov 4" (UI W7) */
function dayText(day) {
  try {
    return new Intl.DateTimeFormat(external_CoreHome_["Matomo"].language || 'en', {
      weekday: 'short',
      day: 'numeric',
      month: 'short',
      timeZone: 'UTC'
    }).format(new Date(`${day}T00:00:00Z`));
  } catch (e) {
    return day;
  }
}
/**
 * A KPI card (2026-11-24): its title, the number for the dashboard's period ("so far" when it
 * includes today), the change against its comparison, the line of the last days with the
 * comparison days marked, and the footer (Open in exploration, the feedback thumbs; no support
 * link, the dashboard widget rule).
 */
/* harmony default export */ var KpiCardViewvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    FeedbackBar: FeedbackBar
  },
  props: {
    card: {
      type: Object,
      required: true
    },
    idSite: {
      type: Number,
      required: true
    },
    /** "Open in exploration"; empty in an embed, which shows no footer (UI review 2026-11-24) */
    exploreUrl: {
      type: String,
      required: true
    },
    /** the website's currency symbol */
    currencySymbol: {
      type: String,
      default: ''
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    }
  },
  data() {
    // an embedded dashboard loads the card with its own module: the page's address tells
    return {
      WIDTH,
      HEIGHT,
      embedded: embeddedPage(window.location.search),
      hasHeader: false
    };
  },
  mounted() {
    // on a dashboard Matomo's widget header names the card (Peter, 2026-11-25: the widget looked
    // crowded with the name twice); an embed of the card alone has no header and keeps the name
    const widget = this.$el.closest ? this.$el.closest('.widget') : null;
    this.hasHeader = !!(widget && widget.querySelector('.widgetTop'));
  },
  computed: {
    /**
     * the metric above the number, unless the card's name (in the card or in the dashboard's
     * header) already is its name (UI review 2026-11-24)
     */
    showMetric() {
      const fold = text => text.trim().toLocaleLowerCase();
      return fold(this.card.metricName) !== '' && fold(this.card.title) !== fold(this.card.metricName);
    },
    formatters() {
      return {
        number: v => external_CoreHome_["NumberFormatter"].formatNumber(v, 2, 0),
        percent: v => external_CoreHome_["NumberFormatter"].formatPercent(v, 2, 0),
        currency: v => external_CoreHome_["NumberFormatter"].formatCurrency(v, this.currencySymbol, 2, 2),
        duration: v => formatDuration(v)
      };
    },
    valueText() {
      return this.card.value === null ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_KpiNoValue') : formatMetric(this.card.metric, this.card.value, this.formatters);
    },
    change() {
      return changeText(this.card, this.formatters);
    },
    tone() {
      return changeTone(this.card);
    },
    label() {
      return comparisonLabel(this.card, external_CoreHome_["translate"]);
    },
    /** the baseline's value and its days, for the tooltip */
    baselineTitle() {
      const {
        comparison
      } = this.card;
      if (!comparison) {
        return '';
      }
      const days = comparison.spans.filter(span => span.value !== null).map(span => span.firstDay === span.lastDay ? dayText(span.firstDay) : `${dayText(span.firstDay)}/${dayText(span.lastDay)}`);
      return `${formatMetric(this.card.metric, comparison.baseline, this.formatters)} (${days.join(', ')})`;
    },
    values() {
      return (this.card.line || []).map(point => point.value);
    },
    scale() {
      const numbers = this.values.filter(v => v !== null);
      const min = numbers.length ? Math.min(...numbers) : 0;
      const max = numbers.length ? Math.max(...numbers) : 1;
      return {
        min,
        max: max === min ? min + 1 : max
      };
    },
    path() {
      return linePath(this.values, this.x, this.y);
    },
    /** the compared days (hollow) and the last value (filled), in percent of the line's box */
    dots() {
      const line = this.card.line || [];
      const last = line.length - 1;
      return line.map((point, i) => ({
        point,
        i
      })).filter(({
        point,
        i
      }) => point.value !== null && (point.baseline || i === last)).map(({
        point,
        i
      }) => ({
        day: point.day,
        kind: i === last ? 'end' : 'day',
        left: Math.round(this.x(i) / WIDTH * 10000) / 100,
        top: Math.round(this.y(point.value) / HEIGHT * 10000) / 100,
        title: `${dayText(point.day)}: ${formatMetric(this.card.metric, point.value, this.formatters)}`
      }));
    },
    lineLabel() {
      const line = this.card.line || [];
      return line.length ? `${this.card.metricName}: ${dayText(line[0].day)}/${dayText(line[line.length - 1].day)}` : '';
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    x(index) {
      const count = Math.max(1, (this.card.line || []).length - 1);
      return PAD + index * (WIDTH - 2 * PAD) / count;
    },
    y(value) {
      const {
        min,
        max
      } = this.scale;
      return HEIGHT - 6 - (value - min) / (max - min) * (HEIGHT - 6 - PAD);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/KpiCardView.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/KpiCardView.vue



KpiCardViewvue_type_script_lang_ts.render = KpiCardViewvue_type_template_id_19779886_render

/* harmony default export */ var KpiCardView = (KpiCardViewvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/KpiCard.vue?vue&type=script&lang=ts




/**
 * A KPI card's dashboard widget (2026-11-24): asks FeinwerkDataExploration.getKpiCard for the
 * dashboard's period and segment and shows the card, or why it cannot.
 */
/* harmony default export */ var KpiCardvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"],
    KpiCardView: KpiCardView
  },
  props: {
    idSite: {
      type: Number,
      required: true
    },
    idSaved: {
      type: Number,
      required: true
    },
    exploreUrl: {
      type: String,
      required: true
    },
    currencySymbol: {
      type: String,
      default: ''
    },
    feedback: {
      type: Object,
      default: () => ({
        enabled: false,
        supportUrl: '',
        version: ''
      })
    }
  },
  data() {
    return {
      card: null,
      loading: true,
      error: ''
    };
  },
  mounted() {
    loadKpiCard(this.idSaved).then(card => {
      this.card = card;
    }).catch(e => {
      this.error = errorText(e);
    }).finally(() => {
      this.loading = false;
    });
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/KpiCard.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/KpiCard.vue



KpiCardvue_type_script_lang_ts.render = KpiCardvue_type_template_id_0b54f2b6_render

/* harmony default export */ var KpiCard = (KpiCardvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/index.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */




// CONCATENATED MODULE: ./node_modules/@vue/cli-service/lib/commands/build/entry-lib-no-default.js




/***/ })

/******/ });
});
//# sourceMappingURL=FeinwerkDataExploration.umd.js.map