/**
 *
 */

(function ($) {

	'use strict'

	$(function () {

		// level
		$('#CustomerLevel')
			.on('change', function () {
				var value = $(this).val()
				if (value == 'A') {
					$('body').css('background-color', '#ffff80')
				} else if (value == 'D') {
					$('body').css('background-color', '#ff8080')
				} else {
					$('body').css('background-color', '')
				}
			})
			.trigger('change')

		//var iOS = /(iPad|iPhone|iPod)/g.test(navigator.userAgent)
		//var chrome = document.userAgent.indexOf('chrome')

		// date input support

		$('form').on('submit', function () {

			var e = $(':focus').get(0)
			if (e && e.blur) {
				e.blur()
			}

		})

		//if (chrome) {
		//	$('input[type=date]').attr('type', 'text').addClass('date')
		//	$('input[type=time]').attr('type', 'text').addClass('time')
		//}

		function formatInputDate(date) {
			return formatDate(date)
			//
			//if (!date) {
			//	return ''
			//}
			//
			//return date.getFullYear()
			//	+ String(date.getMonth() + 101).substr(1)
			//	+ String(date.getDate() + 100).substr(1)
			//
		}

		function formatDate(date) {

			if (!date) {
				return ''
			}

			return date.getFullYear()
				+ '/' + String(date.getMonth() + 101).substr(1)
				+ '/' + String(date.getDate() + 100).substr(1)

		}

		function parseDate(text) {

			text = String(text).trim()
			if (text.length == 0) {
				return null
			}

			try {

				text = text.replace(/[\uff01-\uff5e]/g, function (_) {
					return String.fromCharCode(_.charCodeAt(0) - 0xfee0)
				})

				var date
				var m

				if (/^\d{1,2}$/.test(text)) { // #, ##

					date = new Date()
					date.setDate(text)

				} else if (m = /^(\d{4})?(\d{1,2})(\d{2})$/.exec(text)) {

					date = new Date()
					if (m[1]) { date.setFullYear(m[1]) }
					date.setMonth(m[2] - 1, m[3])

				} else if (m = /^(?:(\d{1,4})\/)?(\d{1,2})\/(\d{1,2})$/.exec(text)) {

					date = new Date()
					if (m[1]) { date.setFullYear(m[1]) }
					date.setMonth(m[2] - 1, m[3])

				} else {

					date = new Date(text)
					if (isNaN(date.valueOf())) {
						throw new RangeError()
					}

				}

				return date

			} catch (e) {

				return null

			}

		}

		function formatInputTime(date) {
			return formatTime(date)

			//if (!date) {
			//	return ''
			//}
			//
			//return String(date.getHours() + 100).substr(1)
			//	+ String(date.getMinutes() + 100).substr(1)
			//
		}

		function formatTime(date) {

			if (!date) {
				return ''
			}

			return String(date.getHours()) + ':' + String(date.getMinutes() + 100).substr(1)

		}

		function parseTime(text) {

			text = String(text).trim()
			if (text.length == 0) {
				return null
			}

			try {
				var date
				var m

				text = text.replace(/[\uff01-\uff5e]/g, function (_) {
					return String.fromCharCode(_.charCodeAt(0) - 0xfee0)
				})

				if (/^\d$/.test(text)) {

					date = new Date()
					date.setMinutes(text)

				} else if (m = /^(\d{1,2})?(\d{2})$/.exec(text)) {

					date = new Date()
					if (m[1]) { date.setHours(m[1]) }
					if (m[2]) { date.setMinutes(m[2]) }

				} else if (m = /^(\d{1,2})?:(\d{1,2})(?::?\d{1,2})?$/.exec(text)) {

					date = new Date()
					if (m[1]) { date.setHours(m[1]) }
					if (m[2]) { date.setMinutes(m[2]) }

				} else {

					date = new Date(text)
					if (isNaN(date.valueOf())) {
						throw new RangeError()
					}

				}

				return date

			} catch (e) {
				return null
			}

		}

		$('input[type=date], input.date').on({
			focus: function (e) {
				var _ = $(this)
				var date = parseDate(_.val())
				_.val(formatInputDate(date))
				if (e.originalEvent && e.originalEvent.originalEvent && /^key/.test(e.originalEvent.originalEvent.type)) {
					_.select()
				}
			},
			blur: function () {
				var _ = $(this)
				var date = parseDate(_.val())
				_.val(formatDate(date))
			},
			keydown: function (e) {

				if (e.ctrlKey) {
					return
				}

				switch (e.which) {
					//case 0: // default
					//case 8: // Backspace
					//case 9: // Tab
					//case 13: // CR
					//case 27: // ESC
					//case 37: // Left
					//case 39: // Right
					//case 46: // DEL
					//case 0x30: // 0
					//case 0x31: // 1
					//case 0x32: // 2
					//case 0x33: // 3
					//case 0x34: // 4
					//case 0x35: // 5
					//case 0x36: // 6
					//case 0x37: // 7
					//case 0x38: // 8
					//case 0x39: // 9
					//case 0x2d: // -
					//case 0x2f: // /
					//case 0x60: // Numpad 0
					//case 0x61: // Numpad 1
					//case 0x62: // Numpad 2
					//case 0x63: // Numpad 3
					//case 0x64: // Numpad 4
					//case 0x65: // Numpad 5
					//case 0x66: // Numpad 6
					//case 0x67: // Numpad 7
					//case 0x68: // Numpad 8
					//case 0x69: // Numpad 9
					//case 0x6d: // Numpad -
					//case 0x6f: // Numpad /
					//	return

					case 38: // Up
						var _ = $(this)
						var date = parseDate(_.val())
						if (!date) {
							date = new Date()
						} else {
							date.setDate(date.getDate() + 1)
						}
						_.val(formatInputDate(date))
						break;

					case 40: // Down
						var _ = $(this)
						var date = parseDate(_.val())
						if (!date) {
							date = new Date()
						} else {
							date.setDate(date.getDate() - 1)
						}
						_.val(formatInputDate(date))
						break;

				}

				// e.preventDefault()
			}

		}).each(function () {

			// 初期表示
			var _ = $(this)
			var date = parseDate(_.val())
			_.val(formatDate(date))

		})

		$('input[type=time], input.time').on({
			focus: function (e) {
				var _ = $(this)
				var date = parseTime(_.val())
				_.val(formatInputTime(date))
				if (e.originalEvent && e.originalEvent.originalEvent && /^key/.test(e.originalEvent.originalEvent.type)) {
					_.select()
				}
			},
			blur: function () {
				var _ = $(this)
				var date = parseTime(_.val())
				_.val(formatTime(date))
			},
			keydown: function (e) {

				if (e.ctrlKey) {
					return
				}

				switch (e.which) {
					//case 0: // default
					//case 8: // Backspace
					//case 9: // Tab
					//case 13: // CR
					//case 27: // ESC
					//case 37: // Left
					//case 39: // Right
					//case 46: // DEL
					//case 0x30: // 0
					//case 0x31: // 1
					//case 0x32: // 2
					//case 0x33: // 3
					//case 0x34: // 4
					//case 0x35: // 5
					//case 0x36: // 6
					//case 0x37: // 7
					//case 0x38: // 8
					//case 0x39: // 9
					//case 0x60: // Numpad 0
					//case 0x61: // Numpad 1
					//case 0x62: // Numpad 2
					//case 0x63: // Numpad 3
					//case 0x64: // Numpad 4
					//case 0x65: // Numpad 5
					//case 0x66: // Numpad 6
					//case 0x67: // Numpad 7
					//case 0x68: // Numpad 8
					//case 0x69: // Numpad 9
					//case 0x3a: // :
					//	return

					case 38: // Up
						var _ = $(this)
						var date = parseTime(_.val())
						if (!date) {
							date = new Date()
						} else {
							date.setMinutes(date.getMinutes() + 1)
						}
						_.val(formatInputTime(date))
						break;

					case 40: // Down
						var _ = $(this)
						var date = parseTime(_.val())
						if (!date) {
							date = new Date()
						} else {
							date.setMinutes(date.getMinutes() - 1)
						}
						_.val(formatInputTime(date))
						break;

				}

				// e.preventDefault()
			}

		}).each(function () {

			// 初期表示
			var _ = $(this)
			var date = parseTime(_.val())
			_.val(formatTime(date))

		})

	})

})(window.jQuery)