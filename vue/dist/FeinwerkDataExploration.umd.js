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

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=template&id=25ab30fa

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
  class: "fwde-section"
};
const _hoisted_8 = {
  class: "fwde-section__title"
};
const _hoisted_9 = {
  class: "fwde-section__count"
};
const _hoisted_10 = {
  class: "fwde-section"
};
const _hoisted_11 = {
  class: "fwde-section__title"
};
const _hoisted_12 = {
  class: "fwde-card fwde-main"
};
const _hoisted_13 = {
  class: "fwde-card__header"
};
const _hoisted_14 = {
  class: "fwde-card__heading"
};
const _hoisted_15 = {
  class: "fwde-card__title"
};
const _hoisted_16 = {
  key: 0,
  class: "fwde-card__changed"
};
const _hoisted_17 = {
  class: "fwde-card__body"
};
const _hoisted_18 = {
  key: 5,
  class: "pk-emptyDataTable"
};
function render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_ActivityIndicator = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ActivityIndicator");
  const _component_Alert = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("Alert");
  const _component_SavedPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SavedPicker");
  const _component_DimensionPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("DimensionPicker");
  const _component_MetricPicker = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("MetricPicker");
  const _component_FilterEditor = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FilterEditor");
  const _component_ExplorationToolbar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ExplorationToolbar");
  const _component_SaveDialog = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SaveDialog");
  const _component_ResultChart = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ResultChart");
  const _component_ResultTable = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("ResultTable");
  const _component_FeedbackBar = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("FeedbackBar");
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
    onOpen: _ctx.openSaved
  }, null, 8, ["saved", "current", "login", "onOpen"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_5, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Dimensions')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_DimensionPicker, {
    ref: "dimensionPicker",
    catalog: _ctx.catalog,
    state: _ctx.state,
    onChange: _cache[0] || (_cache[0] = $event => _ctx.update({
      dimensions: $event
    }))
  }, null, 8, ["catalog", "state"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_8, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Metrics')) + " ", 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", _hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_MetricsCount', _ctx.state.metrics.length, 5)), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_MetricPicker, {
    catalog: _ctx.catalog,
    state: _ctx.state,
    goals: _ctx.goals,
    "action-scope": _ctx.actionScope,
    onChange: _cache[1] || (_cache[1] = $event => _ctx.update({
      metrics: $event
    })),
    onGoal: _cache[2] || (_cache[2] = $event => _ctx.update({
      idGoal: $event === '' ? null : Number($event)
    }))
  }, null, 8, ["catalog", "state", "goals", "action-scope"])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h3", _hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Filters')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FilterEditor, {
    key: _ctx.filterEditorKey,
    catalog: _ctx.catalog,
    state: _ctx.state,
    "action-scope": _ctx.actionScope,
    onChange: _cache[3] || (_cache[3] = $event => _ctx.update({
      filters: $event
    }, true))
  }, null, 8, ["catalog", "state", "action-scope"]))])], 8, _hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("section", _hoisted_12, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("header", _hoisted_13, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_14, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("h2", _hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.title), 1), _ctx.savedChanged ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", _hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedChanged')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ExplorationToolbar, {
    class: "fwde-card__actions",
    state: _ctx.state,
    "max-rows": _ctx.maxRows,
    view: _ctx.view,
    "can-chart": _ctx.canChart,
    onLimit: _cache[4] || (_cache[4] = $event => _ctx.update({
      limit: $event
    })),
    onView: _cache[5] || (_cache[5] = $event => _ctx.update({
      view: $event
    }))
  }, null, 8, ["state", "max-rows", "view", "can-chart"]), _ctx.savedLoaded ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_SaveDialog, {
    key: 0,
    class: "fwde-card__save",
    current: _ctx.currentSaved,
    "suggested-name": _ctx.resultName,
    "can-share": _ctx.canShare,
    busy: _ctx.saveBusy,
    error: _ctx.saveError,
    onOpen: _cache[6] || (_cache[6] = $event => _ctx.saveError = ''),
    onSave: _ctx.saveExploration,
    onDelete: _ctx.deleteCurrent
  }, null, 8, ["current", "suggested-name", "can-share", "busy", "error", "onSave", "onDelete"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", _hoisted_17, [_ctx.showRawDataWarning ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_Alert, {
    key: 0,
    severity: "warning"
  }, {
    default: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withCtx"])(() => [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RawDataWarning', _ctx.rawDataFrom)) + " ", 1), _ctx.rawDataRange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "fwde-alert-link",
      onClick: _cache[7] || (_cache[7] = $event => _ctx.showFrom(_ctx.rawDataRange))
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
      onClick: _cache[8] || (_cache[8] = (...args) => _ctx.run && _ctx.run(...args))
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
      onClick: _cache[9] || (_cache[9] = $event => _ctx.showFrom(_ctx.suggestedPeriod.date))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.suggestedPeriodLabel), 1)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
      key: 1
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.noSmallerPeriodKey)), 1)], 64))]),
    _: 1
  })) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_ActivityIndicator, {
    loading: _ctx.loading,
    "loading-message": _ctx.translate('FeinwerkDataExploration_LoadingExploration')
  }, null, 8, ["loading", "loading-message"]), !_ctx.loading && !_ctx.error && _ctx.rows && !_ctx.rows.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_18, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('CoreHome_ThereIsNoDataForThisReport')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.error && _ctx.rows && _ctx.rows.length && _ctx.view !== 'table' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ResultChart, {
    key: 6,
    rows: _ctx.rows,
    state: _ctx.state,
    catalog: _ctx.catalog,
    metric: _ctx.chartMetric,
    "action-scope": _ctx.actionScope,
    "currency-symbol": _ctx.currencySymbol,
    "hour-labels": _ctx.hourLabels,
    "period-days": _ctx.periodDays,
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
      'fwde-main__stale': _ctx.loading
    }),
    onMetric: _cache[10] || (_cache[10] = $event => _ctx.update({
      chartMetric: $event
    }))
  }, null, 8, ["rows", "state", "catalog", "metric", "action-scope", "currency-symbol", "hour-labels", "period-days", "class"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.error && _ctx.rows && _ctx.rows.length && _ctx.view !== 'chart' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_ResultTable, {
    key: 7,
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
  }, null, 8, ["rows", "state", "catalog", "action-scope", "currency-symbol", "visits-log-enabled", "class", "onSort", "onRowFilter", "onRowBreakdown", "onRowVisits"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), !_ctx.error && _ctx.rows ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createBlock"])(_component_FeedbackBar, {
    key: 8,
    "id-site": _ctx.idSite,
    topic: "free_form_table",
    question: _ctx.translate('FeinwerkDataExploration_FeedbackQuestion_free_form_table'),
    feedback: _ctx.feedback
  }, null, 8, ["id-site", "question", "feedback"])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=template&id=25ab30fa

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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=template&id=7f56d2e9

const DimensionPickervue_type_template_id_7f56d2e9_hoisted_1 = {
  class: "fwde-picker"
};
const DimensionPickervue_type_template_id_7f56d2e9_hoisted_2 = {
  class: "fwde-field__label"
};
const DimensionPickervue_type_template_id_7f56d2e9_hoisted_3 = ["aria-expanded", "onClick"];
const DimensionPickervue_type_template_id_7f56d2e9_hoisted_4 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const DimensionPickervue_type_template_id_7f56d2e9_hoisted_5 = {
  key: 0,
  class: "fwde-picker__panel"
};
function DimensionPickervue_type_template_id_7f56d2e9_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchableList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchableList");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DimensionPickervue_type_template_id_7f56d2e9_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])([0, 1], slot => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
      key: slot,
      class: "fwde-picker__slot"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", DimensionPickervue_type_template_id_7f56d2e9_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(slot === 0 ? 'FeinwerkDataExploration_Dimension' : 'FeinwerkDataExploration_SecondDimension')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-picker__current",
      "aria-expanded": _ctx.openSlot === slot ? 'true' : 'false',
      onClick: $event => _ctx.openSlot = _ctx.openSlot === slot ? null : slot
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.currentName(slot)), 1), DimensionPickervue_type_template_id_7f56d2e9_hoisted_4], 8, DimensionPickervue_type_template_id_7f56d2e9_hoisted_3), _ctx.openSlot === slot ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", DimensionPickervue_type_template_id_7f56d2e9_hoisted_5, [slot === 1 && _ctx.dimensions.length === 2 ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: 0,
      type: "button",
      class: "btn-flat fwde-picker__none",
      onClick: _cache[0] || (_cache[0] = $event => _ctx.choose(1, null))
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NoSecondDimension')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchableList, {
      items: _ctx.items(slot),
      selected: _ctx.dimensions[slot] ? [_ctx.dimensions[slot]] : [],
      "search-label": _ctx.translate('FeinwerkDataExploration_SearchDimensions'),
      onToggle: $event => _ctx.choose(slot, $event)
    }, null, 8, ["items", "selected", "search-label", "onToggle"])])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
  }), 64))]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=template&id=7f56d2e9

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=template&id=22080c68

const SearchableListvue_type_template_id_22080c68_hoisted_1 = {
  class: "fwde-list"
};
const SearchableListvue_type_template_id_22080c68_hoisted_2 = {
  class: "fwde-list__groups"
};
const SearchableListvue_type_template_id_22080c68_hoisted_3 = {
  class: "fwde-list__group-name"
};
const SearchableListvue_type_template_id_22080c68_hoisted_4 = ["title"];
const SearchableListvue_type_template_id_22080c68_hoisted_5 = ["type", "name", "checked", "disabled", "onChange"];
const SearchableListvue_type_template_id_22080c68_hoisted_6 = {
  class: "fwde-list__label"
};
const SearchableListvue_type_template_id_22080c68_hoisted_7 = {
  key: 0,
  class: "fwde-list__reason"
};
const SearchableListvue_type_template_id_22080c68_hoisted_8 = {
  key: 0,
  class: "fwde-list__empty"
};
function SearchableListvue_type_template_id_22080c68_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchInput = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchInput");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SearchableListvue_type_template_id_22080c68_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchInput, {
    modelValue: _ctx.query,
    "onUpdate:modelValue": _cache[0] || (_cache[0] = $event => _ctx.query = $event),
    placeholder: _ctx.searchLabel,
    "show-clear": true
  }, null, 8, ["modelValue", "placeholder"]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SearchableListvue_type_template_id_22080c68_hoisted_2, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups, group => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: group.name,
      class: "fwde-list__group"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SearchableListvue_type_template_id_22080c68_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(group.name), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("ul", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(group.items, item => {
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
        onChange: $event => _ctx.$emit('toggle', item.key)
      }, null, 40, SearchableListvue_type_template_id_22080c68_hoisted_5), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SearchableListvue_type_template_id_22080c68_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(item.label), 1), item.reason ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", SearchableListvue_type_template_id_22080c68_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(item.reason)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 10, SearchableListvue_type_template_id_22080c68_hoisted_4)]);
    }), 128))])]);
  }), 128)), !_ctx.groups.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SearchableListvue_type_template_id_22080c68_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NoMatches')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=template&id=22080c68

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
  emits: ['toggle'],
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
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SearchableList.vue



SearchableListvue_type_script_lang_ts.render = SearchableListvue_type_template_id_22080c68_render

/* harmony default export */ var SearchableList = (SearchableListvue_type_script_lang_ts);
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/state.ts
const DEFAULT_DIMENSIONS = ['channelType'];
const DEFAULT_METRICS = ['nb_visits', 'bounce_rate', 'nb_conversions'];
const LIMIT_OPTIONS = [10, 25, 50, 100, 250, 500, 1000];
const MAX_METRICS = 5;
const MAX_FILTERS = 5;
/** Long enough for a stored URL ("filter to this value" in the row menu). */
const MAX_FILTER_TEXT = 1000;
const TEXT_OPERATORS = ['contains', 'equals', 'notContains'];
const NUMBER_OPERATORS = ['gt', 'lt'];
/** Shown in percent, sent to the API as fractions. */
const RATE_METRICS = ['bounce_rate', 'conversion_rate'];
const VIEWS = ['table', 'chart', 'both'];
function knownIds(value, known, max) {
  const ids = [];
  String(value || '').split(',').forEach(id => {
    if (known.includes(id) && !ids.includes(id) && ids.length < max) {
      ids.push(id);
    }
  });
  return ids;
}
function labelTargets(dimensionCount) {
  return dimensionCount === 2 ? ['label', 'secondary_label'] : ['label'];
}
function isValidFilter(filter, dimensionCount, metrics) {
  if (TEXT_OPERATORS.includes(filter.operator)) {
    const text = filter.value.trim();
    return labelTargets(dimensionCount).includes(filter.target) && text !== '' && text.length <= MAX_FILTER_TEXT;
  }
  if (NUMBER_OPERATORS.includes(filter.operator)) {
    return metrics.includes(filter.target) && filter.value.trim() !== '' && Number.isFinite(Number(filter.value));
  }
  return false;
}
/** "target~operator~value;…" with the value URI-encoded; only the first two "~" separate. */
function parseFilters(value, dimensionCount, metrics) {
  const filters = [];
  String(value || '').split(';').forEach(entry => {
    const first = entry.indexOf('~');
    const second = first < 0 ? -1 : entry.indexOf('~', first + 1);
    if (second < 0 || filters.length >= MAX_FILTERS) {
      return;
    }
    let text;
    try {
      text = decodeURIComponent(entry.substring(second + 1));
    } catch (e) {
      return;
    }
    const filter = {
      target: entry.substring(0, first),
      operator: entry.substring(first + 1, second),
      value: text
    };
    if (isValidFilter(filter, dimensionCount, metrics)) {
      filters.push(Object.assign(Object.assign({}, filter), {}, {
        value: filter.value.trim()
      }));
    }
  });
  return filters;
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
function parseState(hash, catalog, limits) {
  const dimensionIds = catalog.dimensions.map(d => d.id);
  const metricIds = catalog.metrics.map(m => m.id);
  let dimensions = knownIds(hash.fwdeDims, dimensionIds, 2);
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
  const goal = Number(hash.fwdeGoal);
  const limit = Number(hash.fwdeLimit);
  const sortColumns = [...metrics, ...labelTargets(dimensions.length)];
  const sort = String(hash.fwdeSort || '');
  return {
    dimensions,
    metrics,
    idGoal: Number.isInteger(goal) && goal > 0 ? goal : null,
    sort: sortColumns.includes(sort) ? sort : metrics[0],
    sortOrder: hash.fwdeOrder === 'asc' ? 'asc' : 'desc',
    limit: LIMIT_OPTIONS.includes(limit) && limit <= limits.maxRows ? limit : limits.defaultRows,
    filters: parseFilters(hash.fwdeFilters, dimensions.length, metrics),
    view: VIEWS.includes(view) ? view : null,
    chartMetric: metrics.includes(String(hash.fwdeChart)) ? String(hash.fwdeChart) : null
  };
}
/**
 * All parameters, empty ones too: the hash is merged, so an omitted parameter would keep its old
 * value.
 */
function toHashParams(state) {
  return {
    fwdeDims: state.dimensions.join(','),
    fwdeMetrics: state.metrics.join(','),
    fwdeGoal: state.idGoal === null ? '' : String(state.idGoal),
    fwdeSort: state.sort,
    fwdeOrder: state.sortOrder,
    fwdeLimit: String(state.limit),
    fwdeFilters: state.filters.map(f => `${f.target}~${f.operator}~${encodeURIComponent(f.value)}`).join(';'),
    fwdeView: state.view || '',
    fwdeChart: state.chartMetric || ''
  };
}
/**
 * The definition for FeinwerkDataExploration.getExploration.
 */
function toDefinition(state) {
  const definition = {
    dimensions: state.dimensions,
    metrics: state.metrics,
    sort: state.sort,
    sortOrder: state.sortOrder,
    limit: state.limit,
    filters: state.filters.map(f => {
      let {
        value
      } = f;
      if (NUMBER_OPERATORS.includes(f.operator)) {
        value = RATE_METRICS.includes(f.target) ? Number(f.value) / 100 : Number(f.value);
      }
      return {
        target: f.target,
        operator: f.operator,
        value
      };
    })
  };
  if (state.idGoal !== null) {
    definition.idGoal = state.idGoal;
  }
  return definition;
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/availability.ts

/**
 * Why a dimension cannot go into a slot (translation key), or null.
 */
function dimensionReasons(state, catalog, slot) {
  const otherId = state.dimensions[slot === 0 ? 1 : 0];
  const other = catalog.dimensions.find(d => d.id === otherId);
  const reasons = {};
  catalog.dimensions.forEach(dimension => {
    let reason = null;
    if (dimension.id === otherId) {
      reason = 'FeinwerkDataExploration_ReasonAlreadyChosen';
    } else if (other && other.kinds.length && dimension.kinds.length && !dimension.kinds.some(kind => other.kinds.includes(kind))) {
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
  const actionDimensions = catalog.dimensions.filter(d => state.dimensions.includes(d.id) && d.kinds.length);
  const reasons = {};
  catalog.metrics.forEach(metric => {
    let reason = null;
    if (!state.metrics.includes(metric.id) && state.metrics.length >= MAX_METRICS) {
      reason = 'FeinwerkDataExploration_ReasonMaxMetrics';
    } else if (metric.kind !== null && actionDimensions.some(d => !d.kinds.includes(metric.kind))) {
      reason = 'FeinwerkDataExploration_ReasonMetricNeverMatches';
    }
    reasons[metric.id] = reason;
  });
  return reasons;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/DimensionPicker.vue?vue&type=script&lang=ts




const GROUP_KEYS = {
  acquisition: 'FeinwerkDataExploration_GroupAcquisition',
  visitor: 'FeinwerkDataExploration_GroupVisitor',
  technology: 'FeinwerkDataExploration_GroupTechnology',
  time: 'FeinwerkDataExploration_GroupTime',
  visit: 'FeinwerkDataExploration_GroupVisit',
  behaviour: 'FeinwerkDataExploration_GroupBehaviour',
  custom: 'FeinwerkDataExploration_GroupCustom'
};
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
      openSlot: null
    };
  },
  computed: {
    dimensions() {
      return this.state.dimensions;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    currentName(slot) {
      const id = this.dimensions[slot];
      const dimension = this.catalog.dimensions.find(d => d.id === id);
      if (dimension) {
        return dimension.name;
      }
      return Object(external_CoreHome_["translate"])(slot === 1 ? 'FeinwerkDataExploration_NoSecondDimension' : 'FeinwerkDataExploration_ChooseDimension');
    },
    items(slot) {
      const reasons = dimensionReasons(this.state, this.catalog, slot === 0 ? 0 : 1);
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
        dimensions.splice(1);
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



DimensionPickervue_type_script_lang_ts.render = DimensionPickervue_type_template_id_7f56d2e9_render

/* harmony default export */ var DimensionPicker = (DimensionPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=template&id=ea0a2164

const MetricPickervue_type_template_id_ea0a2164_hoisted_1 = {
  class: "fwde-picker"
};
const MetricPickervue_type_template_id_ea0a2164_hoisted_2 = {
  key: 0,
  class: "fwde-picker__goal"
};
const MetricPickervue_type_template_id_ea0a2164_hoisted_3 = ["for"];
const MetricPickervue_type_template_id_ea0a2164_hoisted_4 = ["id", "value"];
const MetricPickervue_type_template_id_ea0a2164_hoisted_5 = {
  value: ""
};
const MetricPickervue_type_template_id_ea0a2164_hoisted_6 = ["value"];
function MetricPickervue_type_template_id_ea0a2164_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_SearchableList = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("SearchableList");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricPickervue_type_template_id_ea0a2164_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_SearchableList, {
    items: _ctx.items,
    selected: _ctx.state.metrics,
    multiple: true,
    "search-label": _ctx.translate('FeinwerkDataExploration_SearchMetrics'),
    onToggle: _ctx.toggle
  }, null, 8, ["items", "selected", "search-label", "onToggle"]), _ctx.hasGoalMetric && _ctx.goals.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", MetricPickervue_type_template_id_ea0a2164_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: "fwde-field__label",
    for: _ctx.goalSelectId
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Goal')), 9, MetricPickervue_type_template_id_ea0a2164_hoisted_3), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    id: _ctx.goalSelectId,
    class: "browser-default fwde-select",
    value: _ctx.state.idGoal === null ? '' : String(_ctx.state.idGoal),
    onChange: _cache[0] || (_cache[0] = (...args) => _ctx.onGoalChange && _ctx.onGoalChange(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", MetricPickervue_type_template_id_ea0a2164_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_AllGoals')), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.goals, goal => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: goal.id,
      value: String(goal.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(goal.name), 9, MetricPickervue_type_template_id_ea0a2164_hoisted_6);
  }), 128))], 40, MetricPickervue_type_template_id_ea0a2164_hoisted_4)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=template&id=ea0a2164

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/MetricPicker.vue?vue&type=script&lang=ts




const SCOPE_KEYS = {
  visit: 'FeinwerkDataExploration_MetricGroupVisits',
  goal: 'FeinwerkDataExploration_MetricGroupGoals',
  action: 'FeinwerkDataExploration_MetricGroupActions'
};
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
    goals: {
      type: Array,
      required: true
    },
    actionScope: {
      type: Boolean,
      required: true
    }
  },
  emits: ['change', 'goal'],
  data() {
    return {
      goalSelectId: `fwde-goal-${Math.random().toString(36).substring(2, 8)}`
    };
  },
  computed: {
    items() {
      const reasons = metricReasons(this.state, this.catalog);
      return this.catalog.metrics.map(m => ({
        key: m.id,
        label: this.actionScope && m.nameInActionScope ? m.nameInActionScope : m.name,
        group: Object(external_CoreHome_["translate"])(SCOPE_KEYS[m.scope]),
        reason: reasons[m.id],
        description: m.description
      }));
    },
    hasGoalMetric() {
      return this.catalog.metrics.some(m => m.scope === 'goal' && this.state.metrics.includes(m.id));
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    onGoalChange(event) {
      this.$emit('goal', event.target.value);
    },
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



MetricPickervue_type_script_lang_ts.render = MetricPickervue_type_template_id_ea0a2164_render

/* harmony default export */ var MetricPicker = (MetricPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=template&id=f75a9960

const FilterEditorvue_type_template_id_f75a9960_hoisted_1 = {
  class: "fwde-filters"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_2 = {
  class: "fwde-filters__field fwde-filters__field--target"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_3 = {
  class: "fwde-field__label"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_4 = ["value", "onChange"];
const FilterEditorvue_type_template_id_f75a9960_hoisted_5 = ["value"];
const FilterEditorvue_type_template_id_f75a9960_hoisted_6 = {
  class: "fwde-filters__field fwde-filters__field--operator"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_7 = {
  class: "fwde-field__label"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_8 = ["value", "onChange"];
const FilterEditorvue_type_template_id_f75a9960_hoisted_9 = ["value"];
const FilterEditorvue_type_template_id_f75a9960_hoisted_10 = {
  class: "fwde-filters__field fwde-filters__field--value"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_11 = {
  class: "fwde-field__label"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_12 = ["type", "maxlength", "value", "onInput"];
const FilterEditorvue_type_template_id_f75a9960_hoisted_13 = ["title", "onClick"];
const FilterEditorvue_type_template_id_f75a9960_hoisted_14 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-close",
  "aria-hidden": "true"
}, null, -1);
const FilterEditorvue_type_template_id_f75a9960_hoisted_15 = {
  class: "fwde-visually-hidden"
};
const FilterEditorvue_type_template_id_f75a9960_hoisted_16 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-add",
  "aria-hidden": "true"
}, null, -1);
function FilterEditorvue_type_template_id_f75a9960_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FilterEditorvue_type_template_id_f75a9960_hoisted_1, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.rows, (row, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", {
      key: row.id,
      class: "fwde-filters__row"
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", FilterEditorvue_type_template_id_f75a9960_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_f75a9960_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterColumn')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
      class: "browser-default fwde-select",
      value: row.target,
      onChange: $event => _ctx.setTarget(index, $event)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.targets, target => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: target.key,
        value: target.key
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(target.label), 9, FilterEditorvue_type_template_id_f75a9960_hoisted_5);
    }), 128))], 40, FilterEditorvue_type_template_id_f75a9960_hoisted_4)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", FilterEditorvue_type_template_id_f75a9960_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_f75a9960_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterCondition')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
      class: "browser-default fwde-select",
      value: row.operator,
      onChange: $event => _ctx.setOperator(index, $event)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.operatorsFor(row.target), operator => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
        key: operator,
        value: operator
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.OPERATOR_KEYS[operator])), 9, FilterEditorvue_type_template_id_f75a9960_hoisted_9);
    }), 128))], 40, FilterEditorvue_type_template_id_f75a9960_hoisted_8)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", FilterEditorvue_type_template_id_f75a9960_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_f75a9960_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_FilterValue')) + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.unitSuffix(row.target)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
      class: "browser-default fwde-input",
      type: _ctx.isNumberTarget(row.target) ? 'number' : 'text',
      maxlength: _ctx.MAX_FILTER_TEXT,
      value: row.value,
      onInput: $event => _ctx.setValue(index, $event)
    }, null, 40, FilterEditorvue_type_template_id_f75a9960_hoisted_12)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
      type: "button",
      class: "fwde-icon-button fwde-filters__remove",
      title: _ctx.translate('FeinwerkDataExploration_RemoveFilter'),
      onClick: $event => _ctx.remove(index)
    }, [FilterEditorvue_type_template_id_f75a9960_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", FilterEditorvue_type_template_id_f75a9960_hoisted_15, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RemoveFilter')), 1)], 8, FilterEditorvue_type_template_id_f75a9960_hoisted_13)]);
  }), 128)), _ctx.rows.length < _ctx.MAX_FILTERS ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwde-button fwde-filters__add",
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.add && _ctx.add(...args))
  }, [FilterEditorvue_type_template_id_f75a9960_hoisted_16, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_AddFilter')), 1)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=template&id=f75a9960

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=script&lang=ts



const OPERATOR_KEYS = {
  contains: 'FeinwerkDataExploration_OperatorContains',
  equals: 'FeinwerkDataExploration_OperatorEquals',
  notContains: 'FeinwerkDataExploration_OperatorNotContains',
  gt: 'FeinwerkDataExploration_OperatorGt',
  lt: 'FeinwerkDataExploration_OperatorLt'
};
const DEBOUNCE_MS = 600;
let lastRowId = 0;
function nextRowId() {
  lastRowId += 1;
  return lastRowId;
}
/* harmony default export */ var FilterEditorvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
  data() {
    return {
      rows: [],
      timer: 0,
      OPERATOR_KEYS,
      MAX_FILTERS: MAX_FILTERS,
      MAX_FILTER_TEXT: MAX_FILTER_TEXT
    };
  },
  created() {
    this.rows = this.state.filters.map(f => Object.assign(Object.assign({}, f), {}, {
      id: nextRowId()
    }));
  },
  beforeUnmount() {
    window.clearTimeout(this.timer);
  },
  computed: {
    targets() {
      const name = id => {
        var _this$catalog$dimensi;
        return ((_this$catalog$dimensi = this.catalog.dimensions.find(d => d.id === id)) === null || _this$catalog$dimensi === void 0 ? void 0 : _this$catalog$dimensi.name) || id;
      };
      const targets = this.state.dimensions.map((id, i) => ({
        key: i === 0 ? 'label' : 'secondary_label',
        label: name(id)
      }));
      this.state.metrics.forEach(id => {
        const metric = this.catalog.metrics.find(m => m.id === id);
        const label = metric && this.actionScope && metric.nameInActionScope ? metric.nameInActionScope : (metric === null || metric === void 0 ? void 0 : metric.name) || id;
        targets.push({
          key: id,
          label
        });
      });
      return targets;
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    isNumberTarget(target) {
      return this.state.metrics.includes(target);
    },
    /** Rates are entered in percent like the table shows them, the visit duration in seconds. */
    unitSuffix(target) {
      if (RATE_METRICS.includes(target)) {
        return ` (${Object(external_CoreHome_["translate"])('FeinwerkDataExploration_UnitPercent')})`;
      }
      if (target === 'avg_time_on_site') {
        return ` (${Object(external_CoreHome_["translate"])('FeinwerkDataExploration_UnitSeconds')})`;
      }
      return '';
    },
    operatorsFor(target) {
      return this.isNumberTarget(target) ? NUMBER_OPERATORS : TEXT_OPERATORS;
    },
    add() {
      this.rows.push({
        id: nextRowId(),
        target: 'label',
        operator: 'contains',
        value: ''
      });
    },
    remove(index) {
      this.rows.splice(index, 1);
      this.emitNow();
    },
    setTarget(index, event) {
      const row = this.rows[index];
      row.target = event.target.value;
      if (!this.operatorsFor(row.target).includes(row.operator)) {
        [row.operator] = this.operatorsFor(row.target);
        row.value = '';
      }
      this.emitNow();
    },
    setOperator(index, event) {
      this.rows[index].operator = event.target.value;
      this.emitNow();
    },
    setValue(index, event) {
      this.rows[index].value = event.target.value;
      window.clearTimeout(this.timer);
      this.timer = window.setTimeout(() => this.emitNow(), DEBOUNCE_MS);
    },
    emitNow() {
      window.clearTimeout(this.timer);
      const valid = this.rows.map(({
        target,
        operator,
        value
      }) => ({
        target,
        operator,
        value: value.trim()
      })).filter(f => isValidFilter(f, this.state.dimensions.length, this.state.metrics));
      if (JSON.stringify(valid) !== JSON.stringify(this.state.filters)) {
        this.$emit('change', valid);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FilterEditor.vue



FilterEditorvue_type_script_lang_ts.render = FilterEditorvue_type_template_id_f75a9960_render

/* harmony default export */ var FilterEditor = (FilterEditorvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=template&id=019e4c4e

const ResultTablevue_type_template_id_019e4c4e_hoisted_1 = {
  class: "fwde-table"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_2 = {
  class: "fwde-table__scroller"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_3 = {
  class: "dataTable fwde-table__table"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_4 = ["aria-sort"];
const ResultTablevue_type_template_id_019e4c4e_hoisted_5 = ["title", "onClick"];
const ResultTablevue_type_template_id_019e4c4e_hoisted_6 = {
  class: "fwde-table__menu"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_7 = {
  class: "fwde-visually-hidden"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_8 = ["title"];
const ResultTablevue_type_template_id_019e4c4e_hoisted_9 = {
  class: "fwde-table__menu"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_10 = {
  class: "fwde-table__footer"
};
const ResultTablevue_type_template_id_019e4c4e_hoisted_11 = {
  key: 0
};
function ResultTablevue_type_template_id_019e4c4e_render(_ctx, _cache, $props, $setup, $data, $options) {
  const _component_RowMenu = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["resolveComponent"])("RowMenu");
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ResultTablevue_type_template_id_019e4c4e_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ResultTablevue_type_template_id_019e4c4e_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("table", ResultTablevue_type_template_id_019e4c4e_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("thead", null, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tr", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, column => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("th", {
      key: column.key,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        sortable: true,
        columnSorted: _ctx.state.sort === column.key,
        'fwde-table__number': column.isMetric,
        label: !column.isMetric
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
    }, null, 2)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 8, ResultTablevue_type_template_id_019e4c4e_hoisted_5)], 10, ResultTablevue_type_template_id_019e4c4e_hoisted_4);
  }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("th", ResultTablevue_type_template_id_019e4c4e_hoisted_6, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultTablevue_type_template_id_019e4c4e_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_RowActions')), 1)])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("tbody", null, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.sortedRows, (row, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("tr", {
      key: index
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.columns, column => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("td", {
        key: column.key,
        class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(column.isMetric ? 'fwde-table__number' : 'label fwde-table__label'),
        title: column.isMetric ? undefined : String(row[column.key])
      }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(column.isMetric ? _ctx.format(column.key, row[column.key]) : row[column.key]), 11, ResultTablevue_type_template_id_019e4c4e_hoisted_8);
    }), 128)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("td", ResultTablevue_type_template_id_019e4c4e_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createVNode"])(_component_RowMenu, {
      label: _ctx.rowLabel(row),
      "can-break-down": _ctx.state.dimensions.length === 1,
      "visits-reason": _ctx.visitsReason(row),
      "filter-reason": _ctx.filterReason(row),
      onFilter: $event => _ctx.$emit('row-filter', row),
      onBreakdown: $event => _ctx.$emit('row-breakdown', row),
      onVisits: $event => _ctx.$emit('row-visits', row)
    }, null, 8, ["label", "can-break-down", "visits-reason", "filter-reason", "onFilter", "onBreakdown", "onVisits"])])]);
  }), 128))])])]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("p", ResultTablevue_type_template_id_019e4c4e_hoisted_10, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate(_ctx.rowCountKey(_ctx.rows.length), _ctx.formatNumber(_ctx.rows.length))) + " ", 1), _ctx.rows.length >= _ctx.state.limit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("span", ResultTablevue_type_template_id_019e4c4e_hoisted_11, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_LimitReached')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=template&id=019e4c4e

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/format.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const RATES = ['bounce_rate', 'conversion_rate'];
function formatMetric(metricId, value, f) {
  if (RATES.includes(metricId)) {
    return f.percent(Math.round(value * 10000) / 100);
  }
  if (metricId === 'revenue') {
    return f.currency(value);
  }
  if (metricId === 'avg_time_on_site') {
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
const LABEL_COLUMNS = ['label', 'secondary_label'];
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
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/rows.ts

/**
 * The value a filter on the row's dimension matches: the stored value the API sends for
 * dimensions filtered on it (the label is URL- and HTML-decoded), else the label.
 */
function filterValue(row, dimension) {
  const stored = dimension === 1 ? row.fwde_value : row.fwde_value2;
  if (stored !== undefined && stored !== null) {
    return String(stored);
  }
  return String(dimension === 1 ? row.label : row.secondary_label);
}
/**
 * The filters after "filter to this row": the row's dimension values as "equals" filters, in
 * place of filters on the same columns; the oldest filters give way when more than five.
 */
function filtersForRow(state, row) {
  const added = [{
    target: 'label',
    operator: 'equals',
    value: filterValue(row, 1)
  }];
  if (state.dimensions.length > 1 && row.secondary_label !== undefined) {
    added.push({
      target: 'secondary_label',
      operator: 'equals',
      value: filterValue(row, 2)
    });
  }
  const targets = added.map(f => f.target);
  const kept = state.filters.filter(f => !targets.includes(f.target));
  return [...kept, ...added].slice(-MAX_FILTERS);
}
/**
 * Whether "filter to this value" can select the row: not a row without a stored value ("… not
 * defined", marked by the API), not a value longer than a filter takes.
 */
function canFilterTo(row) {
  if (row.fwde_no_value) {
    return false;
  }
  const values = [filterValue(row, 1)];
  if (row.secondary_label !== undefined) {
    values.push(filterValue(row, 2));
  }
  return values.every(value => value.length <= MAX_FILTER_TEXT);
}
/** The visits of a row within the page's segment, or null when the row has no segment. */
function combinedSegment(pageSegment, rowSegment) {
  if (!rowSegment) {
    return null;
  }
  return pageSegment ? `${pageSegment};${rowSegment}` : rowSegment;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultTable.vue?vue&type=script&lang=ts






/* harmony default export */ var ResultTablevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  components: {
    RowMenu: RowMenu
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
  computed: {
    columns() {
      const dimensionName = id => {
        var _this$catalog$dimensi;
        return ((_this$catalog$dimensi = this.catalog.dimensions.find(d => d.id === id)) === null || _this$catalog$dimensi === void 0 ? void 0 : _this$catalog$dimensi.name) || id;
      };
      const columns = this.state.dimensions.map((id, i) => ({
        key: i === 0 ? 'label' : 'secondary_label',
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
    sortedRows() {
      if (!shouldSortClientSide(this.rows.length, this.state.limit, this.state.sort)) {
        return this.rows;
      }
      return sortByLabel(this.rows, this.state.sort, this.state.sortOrder, external_CoreHome_["Matomo"].language);
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
    rowLabel(row) {
      return row.secondary_label === undefined ? String(row.label) : `${row.label} · ${row.secondary_label}`;
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



ResultTablevue_type_script_lang_ts.render = ResultTablevue_type_template_id_019e4c4e_render

/* harmony default export */ var ResultTable = (ResultTablevue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=template&id=fe32e816

const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_1 = {
  class: "fwde-toolbar"
};
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_2 = ["aria-label"];
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_3 = ["aria-pressed", "disabled", "title", "onClick"];
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_4 = {
  class: "fwde-toolbar__limit"
};
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_5 = ["value"];
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_6 = ["value"];
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_7 = {
  ref: "exportMenu",
  class: "fwde-menu"
};
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_8 = ["aria-expanded"];
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_9 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-export",
  "aria-hidden": "true"
}, null, -1);
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_10 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-chevron-down",
  "aria-hidden": "true"
}, null, -1);
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_11 = {
  key: 0,
  class: "fwde-menu__list"
};
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_12 = ["href"];
const ExplorationToolbarvue_type_template_id_fe32e816_hoisted_13 = ["title", "aria-label"];
function ExplorationToolbarvue_type_template_id_fe32e816_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ExplorationToolbarvue_type_template_id_fe32e816_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", {
    class: "fwde-switch",
    role: "group",
    "aria-label": _ctx.translate('FeinwerkDataExploration_View')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.viewOptions, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: option.view,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])({
        'fwde-switch__button': true,
        'fwde-switch__button--active': _ctx.view === option.view
      }),
      "aria-pressed": _ctx.view === option.view ? 'true' : 'false',
      disabled: option.view !== 'table' && !_ctx.canChart,
      title: option.view !== 'table' && !_ctx.canChart ? _ctx.translate('FeinwerkDataExploration_ChartNeedsRows') : option.label,
      onClick: $event => _ctx.$emit('view', option.view)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.label), 11, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_3);
  }), 128))], 8, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_2), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", ExplorationToolbarvue_type_template_id_fe32e816_hoisted_4, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_Rows')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default fwde-select",
    value: String(_ctx.state.limit),
    onChange: _cache[0] || (_cache[0] = (...args) => _ctx.onLimit && _ctx.onLimit(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.limitOptions, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option,
      value: String(option)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option), 9, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_6);
  }), 128))], 40, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_5)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ExplorationToolbarvue_type_template_id_fe32e816_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button",
    "aria-haspopup": "true",
    "aria-expanded": _ctx.exportOpen ? 'true' : 'false',
    onClick: _cache[1] || (_cache[1] = $event => _ctx.exportOpen = !_ctx.exportOpen)
  }, [ExplorationToolbarvue_type_template_id_fe32e816_hoisted_9, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Export')) + " ", 1), ExplorationToolbarvue_type_template_id_fe32e816_hoisted_10], 8, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_8), _ctx.exportOpen ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ExplorationToolbarvue_type_template_id_fe32e816_hoisted_11, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.formats, format => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: format
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("a", {
      class: "fwde-menu__item",
      href: _ctx.exportUrl(_ctx.state, format),
      target: "_blank",
      rel: "noreferrer noopener",
      onClick: _cache[2] || (_cache[2] = $event => _ctx.exportOpen = false)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ExportAs', format)), 9, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_12)]);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-icon-button fwde-toolbar__copy",
    title: _ctx.copyText,
    "aria-label": _ctx.copyText,
    onClick: _cache[3] || (_cache[3] = (...args) => _ctx.copyLink && _ctx.copyLink(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(_ctx.copied ? 'icon-ok' : 'icon-content-copy'),
    "aria-hidden": "true"
  }, null, 2)], 8, ExplorationToolbarvue_type_template_id_fe32e816_hoisted_13)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=template&id=fe32e816

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/exportLink.ts
/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */


/**
 * A download link for the current exploration, built like Matomo's own report export links.
 */
function exportUrl(state, format) {
  const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
  const params = {
    module: 'API',
    method: 'FeinwerkDataExploration.getExploration',
    format,
    idSite: parsed.idSite,
    period: parsed.period,
    date: parsed.date,
    segment: parsed.segment || '',
    definition: JSON.stringify(toDefinition(state)),
    readableColumns: 1,
    filter_limit: -1,
    language: external_CoreHome_["Matomo"].language,
    token_auth: external_CoreHome_["Matomo"].token_auth,
    force_api_session: 1
  };
  return `${window.location.href.split(/[?#]/)[0]}?${external_CoreHome_["MatomoUrl"].stringify(params)}`;
}
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=script&lang=ts




/* harmony default export */ var ExplorationToolbarvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  props: {
    state: {
      type: Object,
      required: true
    },
    maxRows: {
      type: Number,
      required: true
    },
    view: {
      type: String,
      required: true
    },
    canChart: {
      type: Boolean,
      required: true
    }
  },
  emits: ['limit', 'view'],
  data() {
    return {
      copied: false,
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
    copyText() {
      return Object(external_CoreHome_["translate"])(this.copied ? 'FeinwerkDataExploration_LinkCopied' : 'FeinwerkDataExploration_CopyLink');
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    exportUrl: exportUrl,
    onLimit(event) {
      this.$emit('limit', Number(event.target.value));
    },
    onDocumentClick(event) {
      const menu = this.$refs.exportMenu;
      if (this.exportOpen && menu && !menu.contains(event.target)) {
        this.exportOpen = false;
      }
    },
    onKeydown(event) {
      if (event.key === 'Escape') {
        this.exportOpen = false;
      }
    },
    copyLink() {
      const done = () => {
        this.copied = true;
        window.clearTimeout(this.timer);
        this.timer = window.setTimeout(() => {
          this.copied = false;
        }, 2500);
      };
      if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href).then(done).catch(() => undefined);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationToolbar.vue



ExplorationToolbarvue_type_script_lang_ts.render = ExplorationToolbarvue_type_template_id_fe32e816_render

/* harmony default export */ var ExplorationToolbar = (ExplorationToolbarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=template&id=0ae9424f

const SavedPickervue_type_template_id_0ae9424f_hoisted_1 = {
  class: "fwde-saved"
};
const SavedPickervue_type_template_id_0ae9424f_hoisted_2 = {
  class: "fwde-field__label",
  for: "fwde-saved-select"
};
const SavedPickervue_type_template_id_0ae9424f_hoisted_3 = ["value"];
const SavedPickervue_type_template_id_0ae9424f_hoisted_4 = {
  value: ""
};
const SavedPickervue_type_template_id_0ae9424f_hoisted_5 = ["label"];
const SavedPickervue_type_template_id_0ae9424f_hoisted_6 = ["value"];
const SavedPickervue_type_template_id_0ae9424f_hoisted_7 = ["label"];
const SavedPickervue_type_template_id_0ae9424f_hoisted_8 = ["value"];
function SavedPickervue_type_template_id_0ae9424f_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SavedPickervue_type_template_id_0ae9424f_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SavedPickervue_type_template_id_0ae9424f_hoisted_2, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedExplorations')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    id: "fwde-saved-select",
    class: "browser-default fwde-select fwde-saved__select",
    value: _ctx.current ? String(_ctx.current.id) : '',
    onChange: _cache[0] || (_cache[0] = (...args) => _ctx.onChange && _ctx.onChange(...args))
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("option", SavedPickervue_type_template_id_0ae9424f_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_NewExploration')), 1), _ctx.groups.mine.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
    key: 0,
    label: _ctx.translate('FeinwerkDataExploration_MyExplorations')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups.mine, saved => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: saved.id,
      value: String(saved.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(saved.shared ? _ctx.translate('FeinwerkDataExploration_SavedShared', saved.name) : saved.name), 9, SavedPickervue_type_template_id_0ae9424f_hoisted_6);
  }), 128))], 8, SavedPickervue_type_template_id_0ae9424f_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.groups.shared.length ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("optgroup", {
    key: 1,
    label: _ctx.translate('FeinwerkDataExploration_SharedByOthers')
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.groups.shared, saved => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: saved.id,
      value: String(saved.id)
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedBy', saved.name, saved.owner)), 9, SavedPickervue_type_template_id_0ae9424f_hoisted_8);
  }), 128))], 8, SavedPickervue_type_template_id_0ae9424f_hoisted_7)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 40, SavedPickervue_type_template_id_0ae9424f_hoisted_3)]);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=template&id=0ae9424f

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/saved.ts

/** The page's state parameters a saved exploration holds (spec section 13; not the period). */
const STATE_KEYS = ['fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters', 'fwdeView', 'fwdeChart'];
/**
 * The hash that opens a saved exploration: its state (other state keys emptied), its segment and
 * its id. The state is checked like any link when the page reads it.
 */
function savedHashParams(saved) {
  const params = {};
  STATE_KEYS.forEach(key => {
    const value = saved.state[key];
    params[key] = typeof value === 'string' ? value : '';
  });
  params.segment = saved.segment || '';
  params.fwdeSaved = String(saved.id);
  return params;
}
/** The part of the page's hash parameters a save stores. */
function stateToSave(params) {
  const state = {};
  STATE_KEYS.forEach(key => {
    if (typeof params[key] === 'string' && params[key] !== '') {
      state[key] = params[key];
    }
  });
  return state;
}
/** The user's own explorations and those shared by others, each in the API's order (by name). */
function groupSaved(list, login) {
  return {
    mine: list.filter(s => s.owner === login),
    shared: list.filter(s => s.owner !== login)
  };
}
/** The saved exploration the link points to (fwdeSaved), or null. */
function findSaved(list, id) {
  if (!id) {
    return null;
  }
  return list.find(s => String(s.id) === String(id)) || null;
}
/**
 * What the save dialog starts with: the open exploration's name and visibility when the user may
 * change it; a copy of someone else's exploration starts private, and so does every exploration
 * of a user who may not share (no write access).
 */
function dialogDefaults(current, suggestedName, canShare) {
  if (current && current.canEdit) {
    return {
      name: current.name,
      shared: canShare && current.shared
    };
  }
  return {
    name: (current ? current.name : suggestedName).slice(0, 100),
    shared: false
  };
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=script&lang=ts



/* harmony default export */ var SavedPickervue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
    }
  },
  emits: ['open'],
  computed: {
    groups() {
      return groupSaved(this.saved, this.login);
    }
  },
  methods: {
    translate: external_CoreHome_["translate"],
    onChange(event) {
      const id = event.target.value;
      this.$emit('open', this.saved.find(s => String(s.id) === id) || null);
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SavedPicker.vue



SavedPickervue_type_script_lang_ts.render = SavedPickervue_type_template_id_0ae9424f_render

/* harmony default export */ var SavedPicker = (SavedPickervue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=template&id=16815a48

const SaveDialogvue_type_template_id_16815a48_hoisted_1 = {
  ref: "root",
  class: "fwde-savedialog"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_2 = ["aria-expanded"];
const SaveDialogvue_type_template_id_16815a48_hoisted_3 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-save",
  "aria-hidden": "true"
}, null, -1);
const SaveDialogvue_type_template_id_16815a48_hoisted_4 = ["aria-label"];
const SaveDialogvue_type_template_id_16815a48_hoisted_5 = {
  key: 0,
  class: "fwde-savedialog__note"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_6 = {
  class: "fwde-field__label",
  for: "fwde-savedialog-name"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_7 = {
  class: "fwde-savedialog__visibility"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_8 = {
  class: "fwde-field__label"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_9 = {
  class: "fwde-savedialog__option"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_10 = {
  class: "fwde-list__label"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_11 = ["disabled"];
const SaveDialogvue_type_template_id_16815a48_hoisted_12 = {
  class: "fwde-list__label"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_13 = {
  key: 0,
  class: "fwde-savedialog__note"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_14 = {
  key: 1,
  class: "fwde-savedialog__error",
  role: "alert"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_15 = {
  class: "fwde-savedialog__buttons"
};
const SaveDialogvue_type_template_id_16815a48_hoisted_16 = ["disabled"];
const SaveDialogvue_type_template_id_16815a48_hoisted_17 = ["disabled"];
const SaveDialogvue_type_template_id_16815a48_hoisted_18 = ["disabled"];
const _hoisted_19 = {
  key: 2,
  class: "fwde-savedialog__delete"
};
const _hoisted_20 = ["disabled"];
const _hoisted_21 = ["disabled"];
function SaveDialogvue_type_template_id_16815a48_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", SaveDialogvue_type_template_id_16815a48_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    ref: "toggle",
    type: "button",
    class: "fwde-button fwde-savedialog__toggle",
    "aria-haspopup": "dialog",
    "aria-expanded": _ctx.open ? 'true' : 'false',
    onClick: _cache[0] || (_cache[0] = (...args) => _ctx.toggle && _ctx.toggle(...args))
  }, [SaveDialogvue_type_template_id_16815a48_hoisted_3, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Save')), 1)], 8, SaveDialogvue_type_template_id_16815a48_hoisted_2), _ctx.open ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("form", {
    key: 0,
    class: "fwde-savedialog__panel",
    role: "dialog",
    "aria-label": _ctx.translate('FeinwerkDataExploration_SaveExploration'),
    onSubmit: _cache[9] || (_cache[9] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.save(!_ctx.canChange), ["prevent"])),
    onKeydown: _cache[10] || (_cache[10] = Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withKeys"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withModifiers"])($event => _ctx.close(), ["prevent"]), ["esc"]))
  }, [_ctx.current && !_ctx.current.canEdit ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SaveDialogvue_type_template_id_16815a48_hoisted_5, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedNotYours', _ctx.current.owner)), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_16815a48_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedName')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    id: "fwde-savedialog-name",
    ref: "name",
    "onUpdate:modelValue": _cache[1] || (_cache[1] = $event => _ctx.name = $event),
    type: "text",
    class: "browser-default fwde-input",
    maxlength: "100",
    required: ""
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelText"], _ctx.name]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("fieldset", SaveDialogvue_type_template_id_16815a48_hoisted_7, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("legend", SaveDialogvue_type_template_id_16815a48_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedVisibility')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", SaveDialogvue_type_template_id_16815a48_hoisted_9, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    "onUpdate:modelValue": _cache[2] || (_cache[2] = $event => _ctx.shared = $event),
    type: "radio",
    name: "fwde-savedialog-shared",
    value: false
  }, null, 512), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.shared]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SaveDialogvue_type_template_id_16815a48_hoisted_10, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_VisibilityPrivate')), 1)]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", {
    class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-savedialog__option", {
      'fwde-savedialog__option--disabled': !_ctx.canShare
    }])
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("input", {
    "onUpdate:modelValue": _cache[3] || (_cache[3] = $event => _ctx.shared = $event),
    type: "radio",
    name: "fwde-savedialog-shared",
    value: true,
    disabled: !_ctx.canShare
  }, null, 8, SaveDialogvue_type_template_id_16815a48_hoisted_11), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vModelRadio"], _ctx.shared]]), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", SaveDialogvue_type_template_id_16815a48_hoisted_12, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_VisibilityShared')), 1)], 2), !_ctx.canShare ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SaveDialogvue_type_template_id_16815a48_hoisted_13, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedShareNeedsWrite')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.error ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", SaveDialogvue_type_template_id_16815a48_hoisted_14, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.error), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", SaveDialogvue_type_template_id_16815a48_hoisted_15, [_ctx.canChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "submit",
    class: "btn fwde-savedialog__changes",
    disabled: _ctx.busy
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SaveChanges')), 9, SaveDialogvue_type_template_id_16815a48_hoisted_16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button fwde-savedialog__asnew",
    disabled: _ctx.busy,
    onClick: _cache[4] || (_cache[4] = $event => _ctx.save(true))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SaveAsNew')), 9, SaveDialogvue_type_template_id_16815a48_hoisted_17)], 64)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 1,
    type: "submit",
    class: "btn fwde-savedialog__new",
    disabled: _ctx.busy
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Save')), 9, SaveDialogvue_type_template_id_16815a48_hoisted_18)), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button",
    onClick: _cache[5] || (_cache[5] = $event => _ctx.close())
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1)]), _ctx.canChange ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", _hoisted_19, [!_ctx.confirmDelete ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
    key: 0,
    type: "button",
    class: "fwde-button fwde-savedialog__deleteask",
    disabled: _ctx.busy,
    onClick: _cache[6] || (_cache[6] = $event => _ctx.confirmDelete = true)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 9, _hoisted_20)) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 1
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_SavedDeleteConfirm', _ctx.currentName)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button fwde-savedialog__deleteyes",
    disabled: _ctx.busy,
    onClick: _cache[7] || (_cache[7] = $event => _ctx.$emit('delete'))
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Delete')), 9, _hoisted_21), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("button", {
    type: "button",
    class: "fwde-button",
    onClick: _cache[8] || (_cache[8] = $event => _ctx.confirmDelete = false)
  }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('General_Cancel')), 1)], 64))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 40, SaveDialogvue_type_template_id_16815a48_hoisted_4)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=template&id=16815a48

// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=script&lang=ts



/* harmony default export */ var SaveDialogvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
    }
  },
  emits: ['save', 'delete', 'open'],
  data() {
    return {
      open: false,
      name: '',
      shared: false,
      confirmDelete: false
    };
  },
  computed: {
    currentName() {
      return this.current ? this.current.name : '';
    },
    canChange() {
      return !!(this.current && this.current.canEdit);
    }
  },
  watch: {
    // a finished save or delete closes the dialog
    busy(isBusy, wasBusy) {
      if (wasBusy && !isBusy && !this.error) {
        this.close();
      }
    }
  },
  beforeUnmount() {
    document.removeEventListener('click', this.onDocumentClick);
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
        shared: this.shared
      } = dialogDefaults(this.current, this.suggestedName, this.canShare));
      this.confirmDelete = false;
      this.open = true;
      this.$emit('open');
      document.addEventListener('click', this.onDocumentClick);
      this.$nextTick(() => {
        const input = this.$refs.name;
        if (input) {
          input.focus();
          input.select();
        }
      });
    },
    /** @param returnFocus back to the button (not when the user clicked somewhere else) */
    close(returnFocus = true) {
      this.open = false;
      document.removeEventListener('click', this.onDocumentClick);
      const toggle = this.$refs.toggle;
      if (returnFocus && toggle) {
        toggle.focus();
      }
    },
    save(asNew) {
      if (this.name.trim() === '') {
        return;
      }
      this.$emit('save', {
        name: this.name.trim(),
        shared: this.shared,
        asNew
      });
    },
    onDocumentClick(event) {
      const root = this.$refs.root;
      // the path of the click, not contains(): "Delete" swaps itself for the confirmation, so the
      // clicked button is gone from the page when the click reaches the document
      if (this.open && root && !event.composedPath().includes(root)) {
        this.close(false);
      }
    }
  }
}));
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/SaveDialog.vue



SaveDialogvue_type_script_lang_ts.render = SaveDialogvue_type_template_id_16815a48_render

/* harmony default export */ var SaveDialog = (SaveDialogvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue?vue&type=template&id=13ded3c9

const FeedbackBarvue_type_template_id_13ded3c9_hoisted_1 = {
  key: 0,
  class: "fwde-feedback"
};
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_2 = {
  class: "fwde-feedback__row"
};
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_3 = ["title", "aria-label", "aria-pressed", "disabled", "onClick"];
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_4 = ["d"];
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_5 = ["href", "title"];
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_6 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "icon-help",
  "aria-hidden": "true"
}, null, -1);
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_7 = {
  key: 0,
  class: "fwde-feedback__thanks",
  role: "status"
};
const FeedbackBarvue_type_template_id_13ded3c9_hoisted_8 = {
  key: 1,
  class: "fwde-feedback__privacy"
};
function FeedbackBarvue_type_template_id_13ded3c9_render(_ctx, _cache, $props, $setup, $data, $options) {
  return _ctx.feedback.enabled || _ctx.feedback.supportUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", FeedbackBarvue_type_template_id_13ded3c9_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", FeedbackBarvue_type_template_id_13ded3c9_hoisted_2, [_ctx.feedback.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
    key: 0
  }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.question), 1), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.choices, choice => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("button", {
      key: choice,
      type: "button",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(["fwde-feedback__thumb", {
        'is-chosen': _ctx.vote === choice
      }]),
      title: _ctx.label(choice),
      "aria-label": _ctx.label(choice),
      "aria-pressed": _ctx.vote === choice ? 'true' : 'false',
      disabled: _ctx.vote !== '',
      onClick: $event => _ctx.send(choice)
    }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
      viewBox: "0 0 24 24",
      width: "18",
      height: "18",
      "aria-hidden": "true",
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`fwde-feedback__icon--${choice}`)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
      d: _ctx.thumbPath
    }, null, 8, FeedbackBarvue_type_template_id_13ded3c9_hoisted_4)], 2))], 10, FeedbackBarvue_type_template_id_13ded3c9_hoisted_3);
  }), 128))], 64)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.feedback.supportUrl ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("a", {
    key: 1,
    class: "fwde-button fwde-feedback__support",
    href: _ctx.feedback.supportUrl,
    target: "_blank",
    rel: "noopener noreferrer",
    title: _ctx.tr('SupportTitle')
  }, [FeedbackBarvue_type_template_id_13ded3c9_hoisted_6, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('Support')), 1)], 8, FeedbackBarvue_type_template_id_13ded3c9_hoisted_5)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), _ctx.feedback.enabled && _ctx.vote ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", FeedbackBarvue_type_template_id_13ded3c9_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.vote === 'up' ? _ctx.tr('FeedbackThanks') : _ctx.tr('FeedbackThanksDown')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true), _ctx.feedback.enabled ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", FeedbackBarvue_type_template_id_13ded3c9_hoisted_8, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.tr('FeedbackPrivacy')), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/FeedbackBar.vue?vue&type=template&id=13ded3c9

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



FeedbackBarvue_type_script_lang_ts.render = FeedbackBarvue_type_template_id_13ded3c9_render

/* harmony default export */ var FeedbackBar = (FeedbackBarvue_type_script_lang_ts);
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-babel/node_modules/cache-loader/dist/cjs.js??ref--13-0!./node_modules/@vue/cli-plugin-babel/node_modules/thread-loader/dist/cjs.js!./node_modules/babel-loader/lib!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist/templateLoader.js??ref--6!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=template&id=00d1afa4

const ResultChartvue_type_template_id_00d1afa4_hoisted_1 = {
  ref: "root",
  class: "fwde-chart"
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_2 = {
  class: "fwde-chart__header"
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_3 = {
  class: "fwde-chart__metric"
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_4 = {
  class: "fwde-field__label"
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_5 = ["value"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_6 = ["value"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_7 = {
  key: 0,
  class: "fwde-chart__note"
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_8 = ["width", "height", "viewBox", "aria-label"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_9 = {
  key: 0
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_10 = ["x1", "x2", "y1", "y2"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_11 = ["x", "y"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_12 = ["x", "y"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_13 = ["x", "y", "width", "height"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_14 = ["x", "y"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_15 = {
  key: 1
};
const ResultChartvue_type_template_id_00d1afa4_hoisted_16 = ["x1", "x2", "y1", "y2"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_17 = ["x", "y"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_18 = ["x", "y"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_19 = ["d"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_20 = ["cx", "cy"];
const ResultChartvue_type_template_id_00d1afa4_hoisted_21 = {
  key: 0,
  class: "fwde-chart__legend"
};
const _hoisted_22 = /*#__PURE__*/Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", {
  class: "fwde-chart__swatch",
  "aria-hidden": "true"
}, null, -1);
function ResultChartvue_type_template_id_00d1afa4_render(_ctx, _cache, $props, $setup, $data, $options) {
  return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("div", ResultChartvue_type_template_id_00d1afa4_hoisted_1, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("div", ResultChartvue_type_template_id_00d1afa4_hoisted_2, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("label", ResultChartvue_type_template_id_00d1afa4_hoisted_3, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("span", ResultChartvue_type_template_id_00d1afa4_hoisted_4, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.translate('FeinwerkDataExploration_ChartMetric')), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("select", {
    class: "browser-default fwde-select",
    value: _ctx.metric,
    onChange: _cache[0] || (_cache[0] = (...args) => _ctx.onMetric && _ctx.onMetric(...args))
  }, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.metricOptions, option => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("option", {
      key: option.id,
      value: option.id
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(option.name), 9, ResultChartvue_type_template_id_00d1afa4_hoisted_6);
  }), 128))], 40, ResultChartvue_type_template_id_00d1afa4_hoisted_5)]), _ctx.note ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("p", ResultChartvue_type_template_id_00d1afa4_hoisted_7, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.note), 1)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)]), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("svg", {
    class: "fwde-chart__svg",
    width: _ctx.width,
    height: _ctx.height,
    viewBox: `0 0 ${_ctx.width} ${_ctx.height}`,
    role: "img",
    "aria-label": _ctx.ariaLabel
  }, [_ctx.model.type === 'bar' ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", ResultChartvue_type_template_id_00d1afa4_hoisted_9, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.scale.ticks, tick => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `grid${tick}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("line", {
      class: "fwde-chart__grid",
      x1: _ctx.barX(tick),
      x2: _ctx.barX(tick),
      y1: _ctx.TOP,
      y2: _ctx.barAreaBottom
    }, null, 8, ResultChartvue_type_template_id_00d1afa4_hoisted_10), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__tick",
      x: _ctx.barX(tick),
      y: _ctx.barAreaBottom + 16,
      "text-anchor": "middle"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formatTick(tick)), 9, ResultChartvue_type_template_id_00d1afa4_hoisted_11)]);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series[0].points, (point, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `bar${index}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(point.label) + ": " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(point.value)), 1), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__category",
      x: _ctx.labelWidth,
      y: _ctx.barY(index) + _ctx.BAR_HEIGHT / 2,
      "text-anchor": "end",
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.truncateLabel(point.label, _ctx.labelChars)), 9, ResultChartvue_type_template_id_00d1afa4_hoisted_12), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("rect", {
      class: "fwde-chart__bar fwde-chart__series-1",
      x: Math.min(_ctx.barX(0), _ctx.barX(point.value || 0)),
      y: _ctx.barY(index) + 3,
      width: Math.max(1, Math.abs(_ctx.barX(point.value || 0) - _ctx.barX(0))),
      height: _ctx.BAR_HEIGHT - 6,
      rx: "2"
    }, null, 8, ResultChartvue_type_template_id_00d1afa4_hoisted_13), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__value",
      x: Math.max(_ctx.barX(0), _ctx.barX(point.value || 0)) + 6,
      y: _ctx.barY(index) + _ctx.BAR_HEIGHT / 2,
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.format(point.value)), 9, ResultChartvue_type_template_id_00d1afa4_hoisted_14)]);
  }), 128))])) : (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", ResultChartvue_type_template_id_00d1afa4_hoisted_15, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.scale.ticks, tick => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `grid${tick}`
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("line", {
      class: "fwde-chart__grid",
      x1: _ctx.LINE_LEFT,
      x2: _ctx.width - _ctx.LINE_RIGHT,
      y1: _ctx.lineY(tick),
      y2: _ctx.lineY(tick)
    }, null, 8, ResultChartvue_type_template_id_00d1afa4_hoisted_16), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("text", {
      class: "fwde-chart__tick",
      x: _ctx.LINE_LEFT - 8,
      y: _ctx.lineY(tick),
      "text-anchor": "end",
      "dominant-baseline": "central"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.formatTick(tick)), 9, ResultChartvue_type_template_id_00d1afa4_hoisted_17)]);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.categories, (category, index) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["withDirectives"])((Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("text", {
      key: `cat${index}`,
      class: "fwde-chart__tick",
      x: _ctx.lineX(index),
      y: _ctx.LINE_HEIGHT - 10,
      "text-anchor": "middle"
    }, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.truncateLabel(category, 12)), 9, ResultChartvue_type_template_id_00d1afa4_hoisted_18)), [[external_commonjs_vue_commonjs2_vue_root_Vue_["vShow"], index % _ctx.categoryStep === 0]]);
  }), 128)), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series, (series, seriesIndex) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("g", {
      key: `series${seriesIndex}`,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`fwde-chart__series-${seriesIndex % 6 + 1}`)
    }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("path", {
      class: "fwde-chart__line",
      d: _ctx.linePath(series.points.map(p => p.value), _ctx.lineX, _ctx.lineY)
    }, null, 8, ResultChartvue_type_template_id_00d1afa4_hoisted_19), (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(series.points, (point, index) => {
      return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], {
        key: `point${index}`
      }, [point.value !== null ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("circle", {
        key: 0,
        class: "fwde-chart__point",
        cx: _ctx.lineX(index),
        cy: _ctx.lineY(point.value),
        r: "3.5"
      }, [Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementVNode"])("title", null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(_ctx.pointTitle(series.label, point.label, point.value)), 1)], 8, ResultChartvue_type_template_id_00d1afa4_hoisted_20)) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 64);
    }), 128))], 2);
  }), 128))]))], 8, ResultChartvue_type_template_id_00d1afa4_hoisted_8)), _ctx.model.type === 'line' && _ctx.hasSecondDimension ? (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("ul", ResultChartvue_type_template_id_00d1afa4_hoisted_21, [(Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(true), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])(external_commonjs_vue_commonjs2_vue_root_Vue_["Fragment"], null, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["renderList"])(_ctx.model.series, (series, seriesIndex) => {
    return Object(external_commonjs_vue_commonjs2_vue_root_Vue_["openBlock"])(), Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createElementBlock"])("li", {
      key: series.label,
      class: Object(external_commonjs_vue_commonjs2_vue_root_Vue_["normalizeClass"])(`fwde-chart__legend-item fwde-chart__series-${seriesIndex % 6 + 1}`)
    }, [_hoisted_22, Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createTextVNode"])(" " + Object(external_commonjs_vue_commonjs2_vue_root_Vue_["toDisplayString"])(series.label), 1)], 2);
  }), 128))])) : Object(external_commonjs_vue_commonjs2_vue_root_Vue_["createCommentVNode"])("", true)], 512);
}
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=template&id=00d1afa4

// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/chart.ts

/** First dimensions shown as a line over time instead of bars. */
const TIME_DIMENSIONS = ['date', 'hour', 'weekday'];
/** Metrics where a missing row is not zero (a rate or an average of no visits): lines get a gap. */
const NO_ZERO_METRICS = ['bounce_rate', 'conversion_rate', 'avg_time_on_site', 'nb_actions_per_visit', 'avg_event_value', 'max_actions'];
/** Metrics counted in whole numbers (and seconds): their axis steps by whole numbers. */
const INTEGER_METRICS = ['nb_visits', 'nb_uniq_visitors', 'nb_actions', 'nb_conversions', 'nb_pageviews', 'nb_uniq_pageviews', 'nb_events', 'nb_downloads', 'nb_outlinks', 'avg_time_on_site', 'nb_searches', 'max_actions'];
const NICE_STEPS = [1, 2, 2.5, 5, 10];
/** The title of the result: the names of its dimensions, e.g. "Channel type × Browser". */
function resultTitle(state, catalog) {
  return state.dimensions.map(id => {
    var _catalog$dimensions$f;
    return ((_catalog$dimensions$f = catalog.dimensions.find(d => d.id === id)) === null || _catalog$dimensions$f === void 0 ? void 0 : _catalog$dimensions$f.name) || id;
  }).join(' × ');
}
function canChart(rowCount) {
  return rowCount >= 2;
}
/** Both where a chart makes sense, the table otherwise; a chosen view only while it is possible. */
function effectiveView(state, rowCount) {
  if (!canChart(rowCount)) {
    return 'table';
  }
  return state.view || 'both';
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
  return row.secondary_label === undefined ? String(row.label) : `${row.label} · ${row.secondary_label}`;
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
    scale: niceScale(Math.min(...values), Math.max(...values), tickCount, INTEGER_METRICS.includes(metric))
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
    scale: niceScale(Math.min(0, ...values), Math.max(0, ...values), tickCount, INTEGER_METRICS.includes(metric))
  };
}
/**
 * The chart of a result: a line over time when the first dimension is a time dimension (one line
 * per value of the second dimension), otherwise one bar per row. Values are never added up, so
 * every metric works.
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
    maxSeries = 5
  } = options;
  const dimension = state.dimensions[0];
  if (!TIME_DIMENSIONS.includes(dimension)) {
    return barModel(rows, metric, maxBars, tickCount);
  }
  const metricFilter = state.filters.some(f => NUMBER_OPERATORS.includes(f.operator));
  const zeroForMissing = complete && !metricFilter && !NO_ZERO_METRICS.includes(metric);
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
  return lineModel(rows, metric, timeOrder(dimension, weekdayNames, hourLabels), allCategories, zeroForMissing, complete, maxSeries, tickCount);
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ResultChart.vue?vue&type=script&lang=ts




const TOP = 8;
const BAR_HEIGHT = 26;
const AXIS_HEIGHT = 26;
const LINE_HEIGHT = 300;
const LINE_LEFT = 64;
const LINE_RIGHT = 16;
const LINE_TOP = 12;
const LINE_BOTTOM = 34;
const VALUE_SPACE = 80;
const CHAR_WIDTH = 7;
/* harmony default export */ var ResultChartvue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
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
    }
  },
  emits: ['metric'],
  data() {
    return {
      width: 800,
      observer: null,
      TOP,
      BAR_HEIGHT,
      LINE_HEIGHT,
      LINE_LEFT,
      LINE_RIGHT
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
      const weekdays = [1, 2, 3, 4, 5, 6, 7].map(day => Object(external_CoreHome_["translate"])(`Intl_Day_Long_StandAlone_${day}`));
      return chartModel(this.rows, this.state, this.metric, {
        weekdayNames: weekdays,
        hourLabels: this.hourLabels,
        periodDays: this.periodDays,
        complete: this.rows.length < this.state.limit,
        // about 70 pixels per axis label; the line chart's axis is vertical and always fits five
        tickCount: TIME_DIMENSIONS.includes(this.state.dimensions[0]) ? 5 : Math.min(5, Math.max(2, Math.floor(this.barPlotWidth / 70)))
      });
    },
    hasSecondDimension() {
      return this.state.dimensions.length > 1;
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
      return TOP + this.model.categories.length * BAR_HEIGHT + AXIS_HEIGHT;
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
      return TOP + this.model.categories.length * BAR_HEIGHT;
    },
    categoryStep() {
      const fit = Math.max(1, Math.floor((this.width - LINE_LEFT - LINE_RIGHT) / 80));
      return Math.max(1, Math.ceil(this.model.categories.length / fit));
    },
    note() {
      if (this.model.incomplete) {
        return Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ChartIncomplete');
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
    format(value) {
      return value === null ? '' : formatMetric(this.metric, value, this.formatters);
    },
    formatTick(value) {
      return formatMetric(this.metric, value, this.formatters);
    },
    pointTitle(series, category, value) {
      const text = `${category}: ${this.format(value)}`;
      return this.hasSecondDimension ? `${series}, ${text}` : text;
    },
    barX(value) {
      const {
        min,
        max
      } = this.model.scale;
      return this.labelWidth + 12 + (value - min) / (max - min) * this.barPlotWidth;
    },
    barY(index) {
      return TOP + index * BAR_HEIGHT;
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



ResultChartvue_type_script_lang_ts.render = ResultChartvue_type_template_id_00d1afa4_render

/* harmony default export */ var ResultChart = (ResultChartvue_type_script_lang_ts);
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
function addSaved(fields) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.addSavedExploration'
  }, saveBody(fields), OPTIONS);
}
function updateSaved(id, fields) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.updateSavedExploration'
  }, Object.assign(Object.assign({}, saveBody(fields)), {}, {
    idSaved: String(id)
  }), OPTIONS);
}
function deleteSaved(id) {
  return external_CoreHome_["AjaxHelper"].post({
    method: 'FeinwerkDataExploration.deleteSavedExploration'
  }, {
    idSaved: String(id)
  }, OPTIONS);
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
// CONCATENATED MODULE: ./node_modules/@vue/cli-plugin-typescript/node_modules/cache-loader/dist/cjs.js??ref--15-0!./node_modules/babel-loader/lib!./node_modules/@vue/cli-plugin-typescript/node_modules/ts-loader??ref--15-2!./node_modules/@vue/cli-service/node_modules/cache-loader/dist/cjs.js??ref--1-0!./node_modules/@vue/cli-service/node_modules/vue-loader-v16/dist??ref--1-1!./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=script&lang=ts




















const HASH_KEYS = ['fwdeDims', 'fwdeMetrics', 'fwdeGoal', 'fwdeSort', 'fwdeOrder', 'fwdeLimit', 'fwdeFilters', 'fwdeView', 'fwdeChart'];
/* harmony default export */ var ExplorationPagevue_type_script_lang_ts = (Object(external_commonjs_vue_commonjs2_vue_root_Vue_["defineComponent"])({
  inheritAttrs: false,
  components: {
    ActivityIndicator: external_CoreHome_["ActivityIndicator"],
    Alert: external_CoreHome_["Alert"],
    DimensionPicker: DimensionPicker,
    MetricPicker: MetricPicker,
    FilterEditor: FilterEditor,
    ResultTable: ResultTable,
    ResultChart: ResultChart,
    ExplorationToolbar: ExplorationToolbar,
    SavedPicker: SavedPicker,
    SaveDialog: SaveDialog,
    FeedbackBar: FeedbackBar
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
    }
  },
  data() {
    return {
      saved: [],
      savedLoaded: false,
      saveBusy: false,
      saveError: '',
      catalog: null,
      goals: [],
      state: null,
      rows: null,
      loading: false,
      error: '',
      loadError: '',
      filterEditorKey: 1,
      editorFilters: '',
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
    Promise.all([loadCatalog(), loadGoals()]).then(([catalog, goals]) => {
      this.catalog = catalog;
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
      const hash = external_CoreHome_["MatomoUrl"].hashParsed.value;
      return HASH_KEYS.map(key => {
        var _hash$key;
        return String((_hash$key = hash[key]) !== null && _hash$key !== void 0 ? _hash$key : '');
      }).join('|');
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
    view() {
      return this.state ? effectiveView(this.state, this.rows ? this.rows.length : 0) : 'table';
    },
    canChart() {
      return canChart(this.rows ? this.rows.length : 0);
    },
    chartMetric() {
      return this.state ? chartMetricFor(this.state) : '';
    },
    largePeriodHint() {
      const info = this.periodVisits;
      if (!info || !isLargePeriod(info.visits, info.maxVisits)) {
        return '';
      }
      const visits = external_CoreHome_["NumberFormatter"].formatNumber(info.visits, 0, 0);
      return info.timeoutSeconds > 0 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LargePeriodHint', visits, String(info.timeoutSeconds)) : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LargePeriodHintNoLimit', visits);
    },
    slowHint() {
      const timeout = this.periodVisits ? this.periodVisits.timeoutSeconds : 0;
      if (this.loading || this.error || this.elapsedMs === null) {
        return '';
      }
      if (!isSlow(this.elapsedMs, timeout)) {
        return '';
      }
      const seconds = external_CoreHome_["NumberFormatter"].formatNumber(this.elapsedMs / 1000, 0, 0);
      return timeout > 0 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SlowQueryHint', seconds, String(timeout)) : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_SlowQueryHintNoLimit', seconds);
    },
    suggestedPeriodLabel() {
      var _this$suggestedPeriod;
      const days = ((_this$suggestedPeriod = this.suggestedPeriod) === null || _this$suggestedPeriod === void 0 ? void 0 : _this$suggestedPeriod.days) || 0;
      return days === 1 ? Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LoadLastDay') : Object(external_CoreHome_["translate"])('FeinwerkDataExploration_LoadLastDays', String(days));
    },
    canRetry() {
      // both role variants of the time limit message start with the same sentence
      const timeout = Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ErrorTimeout', '\u0000');
      return canRetry(this.error, Object(external_CoreHome_["translate"])('FeinwerkDataExploration_ErrorBusy'), timeout.includes('\u0000') ? timeout.slice(0, timeout.indexOf('\u0000')) : '');
    },
    noSmallerPeriodKey() {
      const {
        piwik
      } = window;
      return noSmallerPeriodKey(!!(piwik && piwik.hasSuperUserAccess));
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
      return findSaved(this.saved, String(external_CoreHome_["MatomoUrl"].parsed.value.fwdeSaved || ''));
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
      if (!this.rawDataFrom) {
        return null;
      }
      const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
      const today = new Date().toISOString().substring(0, 10);
      return rangeFromDay(String(parsed.period), String(parsed.date), this.rawDataFrom, today);
    },
    showRawDataWarning() {
      const parsed = external_CoreHome_["MatomoUrl"].parsed.value;
      const today = new Date().toISOString().substring(0, 10);
      return startsBefore(String(parsed.period), String(parsed.date), this.rawDataFrom, today);
    }
  },
  watch: {
    hashState() {
      // back/forward or a pasted link changed the exploration parameters
      this.readState();
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
      // rebuild the editor only for filters that changed elsewhere (link, back button, a removed
      // dimension); after its own input it keeps its rows and the focus
      const filters = JSON.stringify(state.filters);
      if (filters !== this.editorFilters) {
        this.editorFilters = filters;
        this.filterEditorKey += 1;
      }
      this.state = state;
      // "Explore this row" from a report: open the second-dimension picker once
      if (external_CoreHome_["MatomoUrl"].parsed.value.fwdeBreakdown === '1') {
        this.openSecondDimension();
        external_CoreHome_["MatomoUrl"].updateHash(Object.assign(Object.assign({}, external_CoreHome_["MatomoUrl"].hashParsed.value), {}, {
          fwdeBreakdown: ''
        }));
      }
      const definition = JSON.stringify(toDefinition(state));
      if (definition !== this.lastDefinition) {
        const first = this.lastDefinition === '';
        this.lastDefinition = definition;
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
    /**
     * @param fromEditor the filter editor's own change: it keeps its rows and focus, every other
     *                   filter change rebuilds it
     */
    update(change, fromEditor = false) {
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
      if (change.filters && fromEditor) {
        this.editorFilters = JSON.stringify(valid.filters);
      }
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
      const fields = {
        name: event.name,
        state: stateToSave(toHashParams(this.state)),
        segment: String(external_CoreHome_["MatomoUrl"].parsed.value.segment || ''),
        shared: event.shared
      };
      const current = this.currentSaved;
      const request = current && current.canEdit && !event.asNew ? updateSaved(current.id, fields).then(() => current.id) : addSaved(fields).then(result => Number(result.id));
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
        this.update({
          filters: filtersForRow(this.state, row)
        });
      }
    },
    breakDownRow(row) {
      this.filterToRow(row);
      this.openSecondDimension();
    },
    openSecondDimension() {
      this.$nextTick(() => {
        const picker = this.$refs.dimensionPicker;
        if (picker) {
          picker.openSlot = 1;
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
    start() {
      if (!this.state || this.unmounted) {
        this.queue.finish();
        return;
      }
      const abortController = new AbortController();
      this.abortController = abortController;
      const id = this.guard.start();
      const started = Date.now();
      this.elapsedMs = null;
      runExploration(this.state, abortController).then(rows => {
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
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue?vue&type=script&lang=ts
 
// CONCATENATED MODULE: ./plugins/FeinwerkDataExploration/vue/src/ExplorationPage.vue



ExplorationPagevue_type_script_lang_ts.render = render

/* harmony default export */ var ExplorationPage = (ExplorationPagevue_type_script_lang_ts);
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