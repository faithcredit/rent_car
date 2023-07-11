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
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/view/content-timeline.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/view/content-timeline.js":
/*!*****************************************!*\
  !*** ./src/js/view/content-timeline.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("var contentTimelineHandler = function contentTimelineHandler($scope, $) {\n  var contentBlock = $(\".eael-content-timeline-block\");\n  var horizontalTimeline = $scope.find('.eael-horizontal-timeline-track').length;\n\n  if (horizontalTimeline) {\n    $('.eael-horizontal-timeline-track', $scope).on('scroll', function (e) {\n      var scrollLeftPosition = e.currentTarget.scrollLeft;\n      var containerWidth = $('.eael-content-timeline-container', $scope).width();\n      var containerLeft = $('.eael-content-timeline-container', $scope).offset().left;\n      $(\".eael-horizontal-timeline-item\", $scope).each(function () {\n        var itemLeft = $(this).offset().left;\n\n        if (itemLeft > -50 && itemLeft < containerLeft + containerWidth + scrollLeftPosition - 100) {\n          $(this).addClass('is-active');\n        } else {\n          if ($(this).hasClass('is-active')) {\n            $(this).removeClass('is-active');\n          }\n        }\n      });\n    });\n  }\n\n  $(window).on(\"scroll\", function () {\n    contentBlock.each(function () {\n      if ($(this).find(\".eael-highlight\")) {\n        // Calculate screen middle position, top offset and line height and\n        // change line height dynamically\n        var lineEnd = contentBlock.height() * 0.15 + window.innerHeight / 2;\n        var topOffset = $(this).offset().top;\n        var lineHeight = window.scrollY + lineEnd * 1.3 - topOffset;\n        $(this).find(\".eael-content-timeline-inner\").css(\"height\", lineHeight + \"px\");\n      }\n    });\n\n    if (this.oldScroll > this.scrollY == false) {\n      this.oldScroll = this.scrollY; // Scroll Down\n\n      $(\".eael-content-timeline-block.eael-highlight\").prev().find(\".eael-content-timeline-inner\").removeClass(\"eael-muted\").addClass(\"eael-highlighted\");\n    } else if (this.oldScroll > this.scrollY == true) {\n      this.oldScroll = this.scrollY; // Scroll Up\n\n      $(\".eael-content-timeline-block.eael-highlight\").find(\".eael-content-timeline-inner\").addClass(\"eael-prev-highlighted\");\n      $(\".eael-content-timeline-block.eael-highlight\").next().find(\".eael-content-timeline-inner\").removeClass(\"eael-highlighted\").removeClass(\"eael-prev-highlighted\").addClass(\"eael-muted\");\n    }\n  });\n  setLinePosition();\n\n  function setLinePosition() {\n    var _$firstPoint$position, _$lastPoint$position;\n\n    var $line = $scope.find('.eael-horizontal-timeline__line'),\n        $firstPoint = $scope.find('.eael-horizontal-timeline-item__point-content:first'),\n        $lastPoint = $scope.find('.eael-horizontal-timeline-item__point-content:last'),\n        firstPointLeftPos = ((_$firstPoint$position = $firstPoint.position()) === null || _$firstPoint$position === void 0 ? void 0 : _$firstPoint$position.left) + parseInt($firstPoint.css('marginLeft')),\n        lastPointLeftPos = ((_$lastPoint$position = $lastPoint.position()) === null || _$lastPoint$position === void 0 ? void 0 : _$lastPoint$position.left) + parseInt($lastPoint.css('marginLeft')),\n        pointWidth = $firstPoint.outerWidth();\n\n    if (firstPointLeftPos && lastPointLeftPos && pointWidth) {\n      $line.css({\n        'left': '45px',\n        'width': Math.abs(lastPointLeftPos - firstPointLeftPos)\n      });\n    }\n  }\n};\n\njQuery(window).on(\"elementor/frontend/init\", function () {\n  elementorFrontend.hooks.addAction(\"frontend/element_ready/eael-content-timeline.default\", contentTimelineHandler);\n});\n\n//# sourceURL=webpack:///./src/js/view/content-timeline.js?");

/***/ })

/******/ });