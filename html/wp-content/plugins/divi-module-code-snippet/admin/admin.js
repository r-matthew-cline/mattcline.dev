jQuery(function($){
		
	// Event: module:settings:open
	$(document).on('mouseup', '.et-pb-settings, .et-pb-modal-preview-template.active, .et-pb-all-modules > li, .et-pb-clone-module', function(e) {
		$(document).trigger('module:settings:opening');
	});			
	$(document).on('dblclick', '.et_pb_module_block', function(e) {
		$(document).trigger('module:settings:opening');
	});	
	$(document).on('module:settings:opening', function(e) {
		
		if (document.module_settings_opening) {
			document.module_settings_opening = Date.now(); // Update the start time if existing setInterval
			
		} else {
			document.module_settings_opening = Date.now();
			var check_for_open_settings = setInterval(function() {
				
				if (Date.now() - document.module_settings_opening > 10000) { // Timed out
					document.module_settings_opening = false;
					clearInterval(check_for_open_settings); 
					
				} else if ($('.et_pb_module_settings').is(":visible")) { // Settings open
					document.module_settings_opening = false;
					clearInterval(check_for_open_settings);
					$(document).trigger('module:settings:open');
				}
			}, 200);
		}
	});	
	
	// Event: module:settings:save
	if (typeof ET_PageBuilder === 'object' && 'Events' in ET_PageBuilder) {
		ET_PageBuilder.Events.on("et-modal-settings:save", function(){ 
			$(document).trigger('module:settings:save');
		});
	} else { // Backwards compatibility (prior to Divi 2.4.5ish)
		$(document).on("mousedown", '.et-pb-modal-save, .et-pb-modal-preview-template:not(.active), .et-pb-modal-save-template',function() {
			$(document).trigger('module:settings:save');
		});
	}
	
	// Event: {$module_slug}:settings:open
	$(document).on("module:settings:open", function(){ 
		$(document).trigger($('.et_pb_module_settings').data('module_type')+':settings:open');
	});
	
	// Event: {$module_slug}:settings:save
	$(document).on("module:settings:save", function(){ // Since Divi 2.4.5ish
		$(document).trigger($('.et_pb_module_settings').data('module_type')+':settings:save');
	});	

	// === Application code ===

	// Module settings just opened
	$(document).on('et_pb_dmb_code_snippet:settings:open', function(e) {
		
		// Base64 decode the code snippet when the settings page is opened
		var codebox = $('#et_pb_raw_content');
		var codeval = codebox.attr('value').replace(/(<([^>]+)>)/ig,""); // Strip html tags added by visual builder
		var base64_decoded;
		// Base64 decode unicode - see https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
		try { 
			base64_decoded = decodeURIComponent(Array.prototype.map.call(atob(codeval), function(c) {
				return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
			}).join(''));
		} catch (err) { // Not base64 encoded
			base64_decoded = codeval;
		}
		codebox.attr('spellcheck',false);
		codebox.css('color','inherit');
		codebox.attr('value', base64_decoded);
	});			
	
	// Module settings about to close (or switching to preview
	$(document).on('et_pb_dmb_code_snippet:settings:save', function() {

		// Base64 encode the code snippet when the settings page is closed
		var codebox = $('#et_pb_raw_content');
		var preview_active = $('.et-pb-modal-preview-template.active').length;
		if (!preview_active) { // if not already encoded
			codebox.css('color','transparent'); // hide text when encoded
			var codeval = codebox.attr('value');
			// Base64 encode unicode - see https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/Base64_encoding_and_decoding
			var base64_encoded = btoa(encodeURIComponent(codeval).replace(/%([0-9A-F]{2})/g, function(match, p1) {
				return String.fromCharCode('0x' + p1);
			}));
			codebox.attr('value', base64_encoded);
		}
	});
});