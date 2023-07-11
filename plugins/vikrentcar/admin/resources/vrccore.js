/**
 * VikRentCar Core v1.3.0
 * Copyright (C) 2022 E4J s.r.l. All Rights Reserved.
 * http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * https://vikwp.com | https://e4j.com
 */

window['VRCCore'] = class VRCCore {

	/**
	 * Proxy to support static injection of params.
	 */
	constructor(params) {
		if (typeof params === 'object') {
			VRCCore.setOptions(params);
		}
	}

	/**
	 * Inject options by overriding default properties.
	 * 
	 * @param 	object 	params
	 * 
	 * @return 	self
	 */
	static setOptions(params) {
		if (typeof params === 'object') {
			VRCCore.options = Object.assign(VRCCore.options, params);
		}

		return VRCCore;
	}

	/**
	 * Parses an AJAX response error object.
	 * 
	 * @param 	object  err
	 * 
	 * @return  bool
	 */
	static isConnectionLostError(err) {
		if (!err || !err.hasOwnProperty('status')) {
			return false;
		}

		return (
			err.statusText == 'error'
			&& err.status == 0
			&& (err.readyState == 0 || err.readyState == 4)
			&& (!err.hasOwnProperty('responseText') || err.responseText == '')
		);
	}

	/**
	 * Ensures AJAX requests that fail due to connection errors are retried automatically.
	 * 
	 * @param 	string  	url
	 * @param 	object 		data
	 * @param 	function 	success
	 * @param 	function 	failure
	 * @param 	number 		attempt
	 */
	static doAjax(url, data, success, failure, attempt) {
		const AJAX_MAX_ATTEMPTS = 3;

		if (attempt === undefined) {
			attempt = 1;
		}

		return jQuery.ajax({
			type: 'POST',
			url: url,
			data: data
		}).done(function(resp) {
			if (success !== undefined) {
				// launch success callback function
				success(resp);
			}
		}).fail(function(err) {
			/**
			 * If the error is caused by a site connection lost, and if the number
			 * of retries is lower than max attempts, retry the same AJAX request.
			 */
			if (attempt < AJAX_MAX_ATTEMPTS && VRCCore.isConnectionLostError(err)) {
				// delay the retry by half second
				setTimeout(function() {
					// re-launch same request and increase number of attempts
					console.log('Retrying previous AJAX request');
					VRCCore.doAjax(url, data, success, failure, (attempt + 1));
				}, 500);
			} else {
				// launch the failure callback otherwise
				if (failure !== undefined) {
					failure(err);
				}
			}

			// always log the error in console
			console.log('AJAX request failed' + (err.status == 500 ? ' (' + err.responseText + ')' : ''), err);
		});
	}

	/**
	 * Matches a keyword against a text.
	 * 
	 * @param 	string 	search 	the keyword to search.
	 * @param 	string 	text 	the text to compare.
	 * 
	 * @return 	bool
	 */
	static matchString(search, text) {
		return ((text + '').indexOf(search) >= 0);
	}

	/**
	 * Given a date-time string, returns a Date object representation.
	 * 
	 * @param 	string 	dtime_str 	the date-time string in "Y-m-d H:i:s" format.
	 */
	static getDateTimeObject(dtime_str) {
		// instantiate a new date object
		var date_obj = new Date();

		// parse date-time string
		let dtime_parts = dtime_str.split(' ');
		let date_parts  = dtime_parts[0].split('-');
		if (dtime_parts.length != 2 || date_parts.length != 3) {
			// invalid military format
			return date_obj;
		}
		let time_parts = dtime_parts[1].split(':');

		// set accurate date-time values
		date_obj.setFullYear(date_parts[0]);
		date_obj.setMonth((parseInt(date_parts[1]) - 1));
		date_obj.setDate(parseInt(date_parts[2]));
		date_obj.setHours(parseInt(time_parts[0]));
		date_obj.setMinutes(parseInt(time_parts[1]));
		date_obj.setSeconds(0);
		date_obj.setMilliseconds(0);

		// return the accurate date object
		return date_obj;
	}

	/**
	 * Helper method used to copy the text of an
	 * input element within the clipboard.
	 *
	 * Clipboard copy will take effect only in case the
	 * function is handled by a DOM event explicitly
	 * triggered by the user, such as a "click".
	 *
	 * @param 	mixed 	input  The input containing the text to copy.
	 *
	 * @return 	Promise
	 */
	static copyToClipboard(input) {
		// register and return promise
		return new Promise((resolve, reject) => {
			// define a fallback function
			var fallback = function(input) {
				// focus the input
				input.focus();
				// select the text inside the input
				input.select();

				try {
					// try to copy with shell command
					var copy = document.execCommand('copy');

					if (copy) {
						// copied successfully
						resolve(copy);
					} else {
						// unable to copy
						reject(copy);
					}
				} catch (error) {
					// unable to exec the command
					reject(error);
				}
			};

			// look for navigator clipboard
			if (!navigator || !navigator.clipboard) {
				// navigator clipboard not supported, use fallback
				fallback(input);
				return;
			}

			// try to copy within the clipboard by using the navigator
			navigator.clipboard.writeText(input.value).then(() => {
				// copied successfully
				resolve(true);
			}).catch((error) => {
				// revert to the fallback
				fallback(input);
			});
		});
	}

	/**
	 * Debounce technique to group a flurry of events into one single event.
	 */
	static debounceEvent(func, wait, immediate) {
		var timeout;
		return function() {
			var context = this, args = arguments;
			var later = function() {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) {
				func.apply(context, args);
			}
		}
	}

	/**
	 * Throttle guarantees a constant flow of events at a given time interval.
	 * Runs immediately when the event takes place, but can be delayed.
	 */
	static throttleEvent(method, delay) {
		var time = Date.now();
		return function() {
			if ((time + delay - Date.now()) < 0) {
				method();
				time = Date.now();
			}
		}
	}
}

/**
 * These used to be private static properties (static #options),
 * but they are only supported by quite recent browsers (especially Safari).
 * It's too risky, so we decided to keep the class properties public
 * without declaring them as static inside the class declaration.
 * 
 * @var  object
 */
VRCCore.options = {
	platform: 				null,
	base_uri: 				null,
	widget_ajax_uri: 		null,
	current_page: 			null,
	current_page_uri: 		null,
	client: 				'admin',
	admin_widgets: 			[],
	active_listeners: 		{},
};
