$(document).ready(function() {
	$('.add_mls_button').click(function() {
		$('.add_mls').show();
		$('.add_mls_button').hide();
	})
	$('.cancel').click(function() {
		$('.add_mls_button').show();
		$('.add_mls').hide();
	})
	$(".updateProp").click(function(){
		$('.update_prop').show();
		$('.updateProp').hide();
	});
	$('.add_mls_button').click(function() {
		$('.add_mls').show();
		$('.add_mls_button').hide();
	})
})

function showinput(a_type){
	$('input.'+[a_type]).show();
	$('span.'+[a_type]).hide();
	$('a.'+[a_type]).hide();
}
function showdate(a_type){
	$('input#'+[a_type]).datepicker();
	$('span.'+[a_type]).hide();
	$('input#'+[a_type]).show();
}
$(function() {
	$( "#datepicker" ).datepicker();
});
$(".updatePropImg").click(function(){
	$('.update_prop_img').show();
	$('.updatePropImg').hide();
});