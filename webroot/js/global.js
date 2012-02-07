$(function(){
	var delay = false;
	//Search
	$('#search-input').keyup(function(e){
		if (e.keyCode > 40 || e.keyCode == 8) {
			if(this.value.length >= 3) {
				$(this).addClass('loading');
				clearTimeout(delay);
				var query = this.value;
				delay = setTimeout( function() {
					$.ajax({
						url: '/products/autocomplete',
						type: 'get',
						data: { 'q' : query },
						dataType: 'html',
						success: function(data) {
							$('#search-input').removeClass('loading');
							$('#autocomplete-landing').show();
							$('#autocomplete-landing').html(data);
						}
					});
				}, 200);
			} else {
				$('#autocomplete-landing').empty();
			}
		}
	})
	
	.focus(function(){
		if($('#search-autocomplete').length) {
			$('#autocomplete-landing').show();
		}
	})
	
	.blur(function() {
		setTimeout(function() {
			$('#autocomplete-landing').hide();
		}, 200)
	})
	
	.attr('autocomplete', 'off');
	
	//Shortlist
	$('.shortlist-add').live('click',function(){
		var link = $(this);
		$.ajax({
			url: $(this).attr('href'),
			success: function(response) {
				if(response != '0') {
					$('#no-shortlist-items').fadeOut();
					$(response).appendTo('#shortlist').hide().fadeIn();
					link.text('Shortlisted')
						.addClass('shortlist-delete')
						.removeClass('shortlist-add')
						.attr('href', link.attr('href').replace('add','delete'));
					$('#shortlist-clear').fadeIn();
				}
			}
		});
		return false;
	});
	
	$('.shortlist-delete').live('click', function() {
		var link = $(this);
		$.ajax({
			url: $(this).attr('href'),
			success: function(response) {
				$('#shortlist-li-'+link.attr('id').replace('product-','')).fadeOut().remove();
				link.text('Shortlist')
					.addClass('shortlist-add')
					.removeClass('shortlist-delete')
					.attr('href', link.attr('href').replace('delete','add'));
			}
		});
		return false;
	});

	$('.shortlist-li-delete').live('click',function() {
		var li = $(this).parents('li');
		$.ajax({
			url: $(this).attr('href'),
			success: function() {
				li.fadeOut();
			}
		});
		return false;
	});
	
	// Product Page Tabs
	var tabs = $('#tabs').tabs();
	$('.review-link').click(function() { 
	    tabs.tabs('select', 2);
	    return false;
	});
});
