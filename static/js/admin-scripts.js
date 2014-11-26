/*
 global learning_make_categories_focused
 */
jQuery(document).ready(function () {
	var selector = learning_make_categories_focused.selector;
	var clas = learning_make_categories_focused.clas;

	jQuery(selector).parent('li').addClass(clas);
});