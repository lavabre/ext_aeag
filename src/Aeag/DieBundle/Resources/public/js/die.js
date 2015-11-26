/*******************************************************************************
 * Formulaire de contact. --------------------------------------
 */

$(document).ready(function() {

	$('form:input').hover(function() {
		$(this).removeClass("symfony-form-field");
		$(this).addClass("symfony-form-field-select");
	}, function() {
		$(this).removeClass("symfony-form-field-select")
		$(this).addClass("symfony-form-field");
	});

});
