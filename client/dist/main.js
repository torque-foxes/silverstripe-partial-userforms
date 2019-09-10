/******/ (function(modules) { // webpackBootstrap
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
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/src/js/main.js":
/*!*******************************!*\
  !*** ./client/src/js/main.js ***!
  \*******************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _partialuserforms_partialsubmission__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./partialuserforms/partialsubmission */ "./client/src/js/partialuserforms/partialsubmission.js");
/* harmony import */ var _partialuserforms_partialstorage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./partialuserforms/partialstorage */ "./client/src/js/partialuserforms/partialstorage.js");


Object(_partialuserforms_partialsubmission__WEBPACK_IMPORTED_MODULE_0__["default"])();
Object(_partialuserforms_partialstorage__WEBPACK_IMPORTED_MODULE_1__["default"])();

/***/ }),

/***/ "./client/src/js/partialuserforms/partialstorage.js":
/*!**********************************************************!*\
  !*** ./client/src/js/partialuserforms/partialstorage.js ***!
  \**********************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = (function () {// @todo, add the option to prefill
});

/***/ }),

/***/ "./client/src/js/partialuserforms/partialsubmission.js":
/*!*************************************************************!*\
  !*** ./client/src/js/partialuserforms/partialsubmission.js ***!
  \*************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var baseDomain = document.baseURI;
var submitURL = 'partialuserform/save';
var form = document.body.querySelector('form.userform');

var buttons = function buttons() {
  return Array.from(form.querySelectorAll('ul li.step-button-wrapper button'));
}; // Just get the form elements from the current step


var formElements = function formElements() {
  return Array.from(form.querySelectorAll('[aria-hidden=false] [name]:not([type=hidden]):not([type=submit])'));
};

var requests = [];

var getElementValue = function getElementValue(element, fieldName) {
  var value = element.value;

  if (element.getAttribute('type') === 'select') {
    return element[element.selectedIndex].value;
  }

  if (element.getAttribute('type') === 'radio') {
    var name = "[name=".concat(fieldName, "]:checked");
    var checkedElement = document.body.querySelector(name);
    return checkedElement !== null ? checkedElement.value : "";
  }

  if (element.getAttribute('type') === 'checkbox') {
    var _name = "[name=\"".concat(fieldName, "\"]:checked");

    var checkedElements = Array.from(document.body.querySelectorAll(_name));
    var valueArray = [];

    if (checkedElements.length > 0) {
      checkedElements.forEach(function (element) {
        valueArray.push(element.value);
      });
      return valueArray;
    }

    return "";
  }

  if (element.getAttribute('type') === 'file' && element.files.length > 0) {
    return element.files[0];
  }

  return value;
};

var submitPartial = function submitPartial() {
  var data = new FormData();
  formElements().forEach(function (element) {
    var fieldName = element.getAttribute('name');
    var value = getElementValue(element, fieldName);

    if (!data.has(fieldName)) {
      if (_typeof(value) === 'object' && element.getAttribute('type') === 'file') {
        data.append(fieldName, value);
      } else if (_typeof(value) === 'object') {
        value.forEach(function (arrayValue) {
          data.append(fieldName, arrayValue);
        });
      } else {
        data.append(fieldName, value);
      }
    }
  }); // Pass partial params if available

  var partialID = form.querySelector('[name=PartialID]');

  if (partialID) {
    data.append('PartialID', partialID.value);
  }
  /** global: XMLHttpRequest */


  var httpRequest = new XMLHttpRequest();
  requests.push(httpRequest);
  httpRequest.open('POST', "".concat(baseDomain).concat(submitURL), true);
  httpRequest.send(data);
};

var attachSubmitPartial = function attachSubmitPartial(button) {
  button.addEventListener('click', submitPartial);
};

var abortPendingSubmissions = function abortPendingSubmissions() {
  // Clear all pending partial submissions on submit
  if (form !== null) {
    form._submit = form.submit; // Save reference

    form.submit = function () {
      // Abort all requests
      if (requests.length) {
        requests.forEach(function (xhr) {
          xhr.abort();
        });
      }

      form._submit();
    };
  }
};

/* harmony default export */ __webpack_exports__["default"] = (function () {
  buttons().forEach(attachSubmitPartial);
  abortPendingSubmissions();
});

/***/ }),

/***/ 0:
/*!*************************************!*\
  !*** multi ./client/src/js/main.js ***!
  \*************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/scotthutchinson/sites/mbie/vendor/firesphere/partialuserforms/client/src/js/main.js */"./client/src/js/main.js");


/***/ })

/******/ });