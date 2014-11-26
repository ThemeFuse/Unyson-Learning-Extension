var fwQuizBuilder = {};

/**
 * @param {String} [prefix]
 */
fwQuizBuilder.uniqueShortcode = function (prefix) {
	prefix = prefix || 'shortcode_';

	var shortcode = prefix + fw.randomMD5().substring(0, 7);

	shortcode = shortcode.replace(/-/g, '_');

	return shortcode;
};

fwQuizBuilder.esc_attr = function (string) {
	return string.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
};