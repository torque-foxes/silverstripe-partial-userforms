!function(e){var t={};function n(r){if(t[r])return t[r].exports;var o=t[r]={i:r,l:!1,exports:{}};return e[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var o in e)n.d(r,o,function(t){return e[t]}.bind(null,o));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=0)}([function(e,t,n){e.exports=n(1)},function(e,t,n){"use strict";function r(e){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}n.r(t);var o=document.baseURI,u=document.body.querySelector("form.userform"),c=u.querySelector("button.step-button-save"),a=u.querySelector("button.step-button-share"),i=u.querySelector("[type=submit]"),l=[],f=function(){var e=new FormData;Array.from(u.querySelectorAll("[name]:not([type=hidden]):not([type=submit])")).forEach(function(t){var n=t.getAttribute("name"),o=function(e,t){var n=e.value;if("select"===e.getAttribute("type"))return e[e.selectedIndex].value;if("radio"===e.getAttribute("type")){var r="[name=".concat(t,"]:checked"),o=document.body.querySelector(r);return null!==o?o.value:""}if("checkbox"===e.getAttribute("type")){var u='[name="'.concat(t,'"]:checked'),c=Array.from(document.body.querySelectorAll(u)),a=[];return c.length>0?(c.forEach(function(e){a.push(e.value)}),a):""}return"file"===e.getAttribute("type")&&e.files.length>0?e.files[0]:n}(t,n);e.has(n)||("object"===r(o)&&"file"===t.getAttribute("type")?e.append(n,o):"object"===r(o)?o.forEach(function(t){e.append(n,t)}):e.append(n,o))});var t=u.querySelector("[name=PartialID]");t&&e.append("PartialID",t.value);var n=new XMLHttpRequest;l.push(n),n.open("POST","".concat(o).concat("partialuserform/save"),!0),n.send(e),n.upload.loadstart=function(){c.disabled=!0,i.disabled=!0},n.onload=function(){c.disabled=!1,i.disabled=!1},n.onerror=function(){}};c.addEventListener("click",f),a.addEventListener("click",function(){}),null!==u&&(u._submit=u.submit,u.submit=function(){l.length&&l.forEach(function(e){e.abort()}),u._submit()})}]);