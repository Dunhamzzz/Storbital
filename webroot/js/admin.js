$(function() {
	$(".inline").fancybox({
		'hideOnContentClick': true
	});
	
	$('#ProductName').keyup(function() {
		$('#ProductUrlSlug').val($('#ProductName').val().replace(/ /g,'_'));
	});

	$('#weightInPounds').keyup(function(){
		$('#ProductWeightKg').val(($('#weightInPounds').val() / 2.2).toFixed(2));
	});

    // add click handler
	$('.confirm_delete').click(function(){
		// ask for confirmation
		var result = confirm('Are you sure you want to delete this?');
		
		// get parent row
		var row = $(this).parents('tr');
		
		// do ajax request
		if(result) {
			$.ajax({
				type:"POST",
				url:$(this).attr('href'),
				data:"ajax=1",
				dataType: "text",
				success:function(response){
					// hide table row on success
					if(response == true) {
						row.fadeOut();
					} else {
						alert("An error occured.");
					}
				}
			});
		}
		return false;
	});
	
	$('.popular-checkbox').click( function () {
		var tr = $(this).parents('tr');
		var action;
		if($(this).is(':checked')) {
			action = '1';
		} else {
			action = '0';
		}
		$.post(
			'/admin/products/ajax_toggle_popular',
			{'data[id]': tr.attr('id'), 'data[popular]' : action },
			function(worked){
				if(worked) {
					if(action == '1') {
						tr.animate({ backgroundColor: '#CCFFCC'}, 250);
					} else {
						tr.animate({ backgroundColor: 'white'}, 250);
					}
				} else {
					alert('Unable to complete request');
				}
			},
			'text'
		);
	});
});