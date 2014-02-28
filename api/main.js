$(document).ready(function() {
	$('#get').bind('click',function() {
		$.get('http://tedswebs.co.uk/poi/api/points/'+$('#pointID').val(), function(data) {	
			$('#results').html(JSON.stringify(data));
		});
	});

	// $('#update').bind('click', function() {
	// 	var postData = {	id : $("#pointID").val(),
	// 						name : $("#pointName").val(),
	// 						lng : $("#pointLng").val(),
	// 						lat : $("#pointLat").val(),
	// 						message : $("#pointMsg").val()};

	// 	$.ajax({
	// 		url: 'http://poi.dev/api/points/'+$('#pointID').val(),
	// 		type: "POST",
	// 		data: JSON.stringify(postData),
	// 		processData: true,
 //  			dataType:"json",
	// 		success: function(data) {
	// 			alert("Updated");
	// 			$('#results').html(data);
	// 		}
	// 	});
	// });
});
