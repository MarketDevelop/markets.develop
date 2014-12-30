/**
 * Autocomplete with IME support and keyword highlighting
 */
!function ($) {

	'use strict'

	$(function () {
		
		var mappings = {}

		!function () {

			var u
	
			// halfwidth <-> fullwidth for ASCII
			for (u = 0x21; u <= 0x7e; ++u) {
				var h = String.fromCharCode(u)
				var f = String.fromCharCode(u + 0xfee0)
				mappings[h] = mappings[f] = h + f
			}

			// spaces
			mappings[' '] = ' \u3000'
			mappings['\u3000'] = ' \u3000'
			
			// hiragana, katakana
			for (u = 0x3041; u <= 0x3094; ++u) {
				var h = String.fromCharCode(u)
				var f = String.fromCharCode(u + 0x60)
				mappings[h] = mappings[f] = h + f
			}
			
			// TODO: cluster half/full/palatalized/geminated/voiced/semi-voiced/dash/prolonged
			
		}()

		var escapeXml = function (value) { 
			return $('<div />').text(value).html()
		}

		var quoteMeta = function (value) {
			return value.replace(/\W/g, '\\$&')
		}
		
		var createFuzzyRegex = function (value) {

			var i
			
			// value to fizzy regex
			var chars = value.split('')
			var pattern = ''
			
			for (i = 0; i < chars.length; ++i) {
				var c = chars[i]
				if (c in mappings) {
					pattern += '[' + quoteMeta(mappings[c]) + ']'
				} else {
					pattern += quoteMeta(c)
				}
			}
			
			return new RegExp(pattern, 'ig')
		}

		var VK_PROCESSKEY = 229
		var VK_RETURN = 13

		$.widget('nicosys.combobox', $.ui.autocomplete, {

			options: {
				highlight: true,
				format: function (item, text) {
					return text
				}
			},

			//_create: function () {
			//
			//	// options: itemPrefix, itemSuffix, categoryLabel, maxHeight
			//	var options = this.options
			//
			//	if (!options) {
			//		options = {}
			//	}
			//	if (!options.acOptions) {
			//		options.acOptions = {}
			//	}
			//
			//	var itemPrefix = options.itemPrefix
			//	if (!itemPrefix) {
			//		itemPrefix = function (item) {
			//			return ''
			//		}
			//	}
			//	if (typeof(itemPrefix) != 'function') {
			//		itemPrefix = function () {
			//			return itemPrefix
			//		}
			//	}
			//
			//	var itemSuffix = options.itemSuffix
			//	if (!itemSuffix) {
			//		itemSuffix = function (item) {
			//			return ''
			//		}
			//	}
			//	if (typeof(itemSuffix) != 'function') {
			//		itemSuffix = function () {
			//			return itemSuffix
			//		}
			//	}
			//
			//	var categoryLabel = options.categoryLabel
			//	if (!categoryLabel) {
			//		categoryLabel = function (item) {
			//			return ''
			//		}
			//	}
			//	if (typeof(categoryLabel) != 'function') {
			//		categoryLabel = function () {
			//			return categoryLabel
			//		}
			//	}
			//
			//	//var data;
			//	//
			//	//if (/WebKit/.test(navigator.userAgent)) {
			//	//
			//	//	data = $(this)
			//	//		.autocomplete($.extend(acOptions, {
			//	//			source: url
			//	//		}))
			//	//		.data('autocomplete')
			//	//
			//	//} else {
			//	//
			//	//	data = $(this)
			//	//		.autocomplete($.extend(acOptions, {
			//	//			source: url,
			//	//			search: function (event, ui) {
			//	//				if (event.keyCode == VK_PROCESSKEY) {
			//	//					return false
			//	//				} else {
			//	//					return true
			//	//				}
			//	//			}
			//	//		}))
			//	//		.keyup(function (event) {
			//	//			if (event.keyCode == VK_RETURN) {
			//	//				$(this).autocomplete('search')
			//	//			}
			//	//		})
			//	//		.data('autocomplete')
			//	//}
			//	//
			//	//// overrides autocomplete's internal data
			//	//data._renderItem = renderItem
			//	//data._renderMenu = renderMenu
			//
			//},

			_renderMenu: function (ul, items) {

				if (this.options.highlight) {
					this._termRegex = createFuzzyRegex(this.term)
				}

				//if (this.options.maxHeight) {
				//	$(ul).css('max-height', options.maxHeight)
				//}
				// $(ul).addClass('typeahead dropdown-menu') // bootstrap

				var that = this
				var category = null

				$.each(items, function (index, item) {
					if (item.category != category) {
						ul.append('<li class="ui-menu-item ui-autocomplete-category clearfix">' + categoryLabel(item) + '</li>')
						category = item.category
					}
					that._renderItemData(ul, item) // _renderItem
				})

			},

			_renderItem: function (ul, item) {

				var term = this.term
				var label = item.label
				var text

				if (this.options.highlight) {

					// insert STX, ETX tag
					label = label.replace(
						this._termRegex,
						function (match) {
							return '\x02' + match + '\x03' // STX, ETX
						}
					)

					// escape for XML then expand tag to <strong>
					text = escapeXml(label)
						.replace(/\x02/g, '<strong style="text-decoration: underline">')
						.replace(/\x03/g, '</strong>')

				} else {

					text = escapeXml(label)

				}

				var itemHtml = this.options.format(item, text)

				return $('<li></li>')
					.append(itemHtml)
					.appendTo(ul)
			}

		})
	
	})

}(window.jQuery)
