var pageInitialized = false; // prevent ready function being called multiple times

var action = null;
var page = null;
var params = null;
var nr_threads = null;

async function table_action(event) {
	for(var n = event.target; n.nodeName != 'TD'; n = n.parentNode);
	var n_inner = n.innerHTML;
	n.innerHTML = "Loading...";
	var response = await ajax_click(event);
	if(response.toLowerCase().startsWith("<tr>")) {
		for(var row_html = n; row_html.nodeName != 'TR'; row_html=row_html.parentNode);
		row_html.innerHTML = response;
	}
	else {
		n.innerHTML = n_inner;
	}
}

async function ajax_click(event) {
	event.preventDefault();
	action = $(event.target).attr('action');
	page = $(event.target).attr('page');
	params = $(event.target).attr('params');
	single = $(event.target).attr('single') || false;
	nr_threads = $(event.target).attr('threads');
	if(nr_threads == undefined) {
		nr_threads = 1;
	}
	if(single)
		 return make_ajax_call(1, single); 

	for(var i = 0; i < nr_threads; i++) {
		make_ajax_call(i, single);
		await new Promise(r => setTimeout(r, 2000));
	}
}

function make_ajax_call(id, single=false, amount_left=null, last_item=null) {
	var data = {
		'id': id,
		'action': (page+'_route'),
		'doaction': action,
		'params': params,
		'total_threads': nr_threads,
		'last_item': last_item
	};
	if(amount_left != null) {
		data.amount_left = amount_left;
	}
	if(!single && todo_count == null) {
		init_progress();
	}
	var ajax_debug = window.location.search.substr(1).startsWith('debug') ? '?XDEBUG_SESSION_START' : "";
	
	return jQuery.post(ajaxurl+ajax_debug, data)
		.fail(function(xhr, status, error) {
			console.log(xhr);
			progress_log('<strong style="color:#F00">Error: ' + error + '</strong>'+xhr.responseText);
			stop();
    	}).done(function(response) {
			console.log(response);
			
			if(single) {
				return response;
			}
			var json = JSON.parse(response);
			var percentage = show_progress(json.id, json.amount_left, json.current);
			
			if(percentage < 100) {
				setTimeout(() => { make_ajax_call(json.id, false, json.amount_left, json.last_item); }, 10);
			}
			else {
				progress_log("Finished");
			}
		});
}

function ajax_call(action, params, page=null) {
	if(page == null) {
		const urlParams = new URLSearchParams(window.location.search);
		page = urlParams.get('page');
	}
	var data = {
		'id': 1,
		'action': (page+'_route'),
		'doaction': action,
		'params': params
	};
	var ajax_debug = window.location.search.substr(1).startsWith('debug') ? '?XDEBUG_SESSION_START' : "";
	return jQuery.post(ajaxurl+ajax_debug, data, function(response) {
		console.log(response);
		return response;
	});
}

function init_progress() {
	// Show Stop button and divs
	$('#eyeseet-content').html("<button id='stop_progress' class='button'>Stop</button><button id='clear_progress' class='button'>Clear</button><div id='progress'>loading...</div><div id='progress_log'></div>");
	$('#stop_progress').click(function() {
		stop();
	});
	$('#clear_progress').click(function() {
		$('#progress_log').html('');
		if(action == null) { // stopped
			$('#eyeseet-content').html('');
		}
	});
}

function progress_log(tolog) {
	$('#progress_log').html(tolog+'<br>'+$('#progress_log').html());
	$('#progress_log .button.ajax').click(ajax_click);
}

function stop() {
	action = null;
	todo_count = null;
	progress_log('Stopped');
}

var todo_count = null;
function show_progress(id, count, current) {
	// initialize
	if(todo_count == null && // new start
		action != null) { // not stopped
		
		init_progress();
		
		todo_count=count+1;
	}
	
	// update progress log
	if(current) {
		progress_log(id+": "+current);
	}
	
	// update progress bar
	if(action != null) {
		var percentage = (todo_count-count)/todo_count*100;
		console.log(percentage);
		// Progress bar
		$('#progress').html('<div id="progressbar" class="ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="'+percentage+'"><div class="ui-progressbar-value ui-widget-header ui-corner-left" style="width: '+percentage+'%;"></div></div>');
		return percentage;
	}
}

jQuery(document).ready(function($) {
	if(pageInitialized) return;
	pageInitialized = true;
	window.$ = $;
	$(".button.ajax").click(ajax_click);
	$( '.eyeseet-color-picker' ).wpColorPicker({palettes:false});
	
	// The "Upload" button
    $('.eyeseet-upload-image').click(function() {
        var send_attachment_prev = wp.media.editor.send.attachment;
		var button = $(this);
        wp.media.editor.send.attachment = function(props, attachment) {
            $(button).parent().prevAll('.eyeseet-image').attr('src', attachment.url);
            $(button).parent().prevAll('.eyeseet-input-image').val(attachment.url);
            wp.media.editor.send.attachment = send_attachment_prev;
        }
        wp.media.editor.open(button);
        return false;
    });

    // The "Remove" button (remove the value from input type='hidden')
    $('.eyeseet-remove-image').click(function() {
        var answer = confirm('Are you sure?');
        if (answer == true) {
            $(this).parent().prevAll('.eyeseet-image').attr('src', '');
            $(this).parent().prevAll('.eyeseet-input-image').val('');
        }
        return false;
    });	
});