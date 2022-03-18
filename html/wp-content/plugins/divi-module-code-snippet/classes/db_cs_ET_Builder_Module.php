<?php // Custom Divi Module class by Dan Mossop

add_action('wp_loaded', 'load_db_cs_ET_Builder_Module');

function load_db_cs_ET_Builder_Module() {

	if (class_exists('ET_Builder_Module')) {
		
		class db_cs_ET_Builder_Module extends ET_Builder_Module {
			
			var $slug, $main_css_element, $function_name;
			var $fields = array();
			var $classes = array();
			var $advanced_options = array();
			var $toggle_slugs = array();
			var $current_tab = 'general';
			var $current_toggle = '';
			var $options_toggles;
			
			public function __construct() {
				
				// Disable caching if global DMB_DISABLE_LOCAL_CACHING constant defined.
				if (defined('DMB_CACHE_MODULES_LOCALLY')) { $this->clear_cache(); }
				
				// Set the toggle defaults
				$toggle_defaults = array('settings'=>array('toggles_disabled'=>true), 'toggles'=>array());
				$this->options_toggles = array('advanced'=>$toggle_defaults, 'general'=>$toggle_defaults);
				
				// Apply the builder timeout fix
				add_filter('et_builder_get_child_modules', array($this, 'fix_builder_timeout_error'));
				
				// Call ET_Builder_Module constructor
				parent::__construct();
				
				// Apply the admin CSS / JS
				add_action('admin_head', array($this, 'output_admin_css'));
					
				
				// Workaround for toggle_slug not fully supported
				foreach($this->toggle_slugs as $k=>$v) {
					if (empty($this->fields_unprocessed[$k])) { $this->fields_unprocessed[$k] = array(); }
					$this->fields_unprocessed[$k]['toggle_slug'] = $v;
				}
				
				// Hook to run code on posts / pages the module is actually used in
				add_action('the_posts', array($this, 'module_used_in_post'));
			}
			
			function module_used_in_post($posts) {
				if (!empty($posts) and is_array($posts)) {
					foreach ($posts as $post) {
						if (has_shortcode($post->post_content, $this->slug)) { // shortcode used in page
							
							// Output the user CSS / JS
							add_action('wp_head', array($this, 'output_user_css'));		
							add_action('wp_head', array($this, 'output_user_js'));
							
							// Run module-specific code
							$this->module_used();
							
							return $posts;
						}
					}
				}
				return $posts;
			}
			
			// To be overridden as needed - runs on pages / posts which contain the module
			function module_used(){}
			
			// === Updates ===
			function handle_plugin_update($version) {
				$version_option = $this->slug.'_version';
				$old = get_option($version_option);
				
				if ($old!=$version) { // If updated
					$this->clear_cache();
					update_option($version_option, $version); // Update the stored version number
					$this->updated($old, $version);
				} 
			}
			
			// Override with actions to be performed on update
			function updated($old, $new){}
			
			// === JS ===
			
			// Override methods
			function user_js(){}
			
			function output_user_js() {
?>						<script>jQuery(function($){try {<?php $this->user_js(); ?>}catch(err){}});</script>
<?php				}
			
			// === CSS ===
			
			// Output user css
			function output_user_css() { 
?>						<style><?php $this->user_css(); ?></style>
<?php		 		} 
			
			// Output admin css
			function output_admin_css() { 
?>						<style><?php $this->module_listing_css(); ?></style>
<?php		 		} 
			
			// Module-specific css
			function user_css() {} 
						
			// Module listing CSS
			function module_listing_css() { 
				
				// Set icon
				if (!empty($this->icon)) { 
					$icon = preg_replace('/&#x([^;]*);?/', '\\1', $this->icon); 
?>							.et-pb-all-modules-tab li.<?php esc_attr_e($this->slug); ?>:before { 
						font-family: 'ETmodules'; 
						content: '\<?php esc_attr_e($icon); ?>'; 
					} 
<?php					} 
				
			}
			
			// === Tabs and toggles === 
			function add_heading($slug, $text) {
				$this->options_toggles[$this->current_tab]['toggles'][$slug] = esc_html__($text, 'et_builder');
				$this->current_toggle = $slug;
			}
			
			function set_tab($slug) {
				$this->current_tab = $slug;
			}
			
			// === Patches ===
	
			// Builder sometimes times out with custom modules due to a type error - this fixes it
			function fix_builder_timeout_error($children) {
				if (empty($children)) { $children = array(); }
				return $children;
			}
			
			// === Config ===
			
			function set_slug($slug) {
				$this->slug = $slug;
				$this->main_css_element = '%%order_class%%.'.$slug;
			}
			
			// === Fields ===
			
			function add_custom_css_box($id, $label, $selector) {
				$this->custom_css_options[$id] = array('label'=>__($label, 'et_builder'), 'selector'=>$selector);
			}
			
			function add_field($id, $label='', $settings=array()) {	
				
				// Set defaults
				$defaults = array(
					'option_category'=>'configuration', // Role editor setting type: e.g. configuration | layout | color_option | button | font_option
				);
				if ($this->current_tab == 'advanced') { 
					$defaults['tab_slug'] = 'advanced';
				}
				if (!empty($this->current_toggle)) { 
					$defaults['toggle_slug'] = $this->current_toggle;
				}
				$settings = array_merge($defaults, $settings);
			
				// Handle escaping / translation
				if (!empty($label)) { 
					$settings['label'] = esc_html__($label, 'et_builder');
				}
				if (!empty($settings['description'])) {
					$settings['description'] = esc_html__($settings['description'], 'et_builder');
				}
				
				// Add to fields list
				$this->fields[$id] = $settings;
			}
			
			// Skipped fields
			function add_skipped_field($id) {
				$this->fields[$id] = array(
					'type' => 'skip',
				);
			}
			
			function add_on_off_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'yes_no_button',
					'options' => array(
						'off'  => esc_html__('Off', 'et_builder'),
						'on' => esc_html__('On', 'et_builder'),
					)
				);
				$settings = array_merge($defaults, $settings);
				$this->add_field($id, $label, $settings);
			}
			
			function add_yes_no_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'yes_no_button',
					'options' => array(
						'off'  => esc_html__( 'No', 'et_builder' ),
						'on' => esc_html__( 'Yes', 'et_builder' ),
					),
				);
				$settings = array_merge($defaults, $settings);
				$this->add_field($id, $label, $settings);
			}
			
			function add_select_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'select'
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_options_field($id, $label, $settings);
			}
			
			function add_upload_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'upload',
					'upload_button_text' => 'Upload an image',
					'choose_text'        => 'Choose an Image',
					'update_text'        => 'Set as Image',
				);
				$settings = array_merge($defaults, $settings);	

				// Apply escaping / translation
				foreach(array('upload_button_text', 'choose_text', 'update_text') as $k) { 
					if (!empty($settings[$k])) { $settings[$k] = __($settings[$k], 'et_builder'); }
				}
								
				$this->add_field($id, $label, $settings);
			}
			
			function add_video_upload_field($id, $label='', $settings=array()) {
				$defaults = array(
					'data_type'          => 'video',
					'upload_button_text' => 'Upload a video',
					'choose_text'        => 'Choose a Video File',
					'update_text'        => 'Set as Video',
				);
				$settings = array_merge($defaults, $settings);									
				$this->add_upload_field($id, $label, $settings);
			}
			
			function add_text_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'text'
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_field($id, $label, $settings);
			}
			
			function add_font_icon_field($id, $label='', $settings=array()) {
				$defaults = array(
					'class'               => array( 'et-pb-font-icon' ),
					'renderer'            => 'et_pb_get_font_icon_list',
					'renderer_with_field' => true
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_text_field($id, $label, $settings);
			}
			
			function add_textarea_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'textarea'
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_field($id, $label, $settings);
			}
			
			function add_color_alpha_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'color-alpha',
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_field($id, $label, $settings);
			}
			
			function add_color_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'color',
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_field($id, $label, $settings);
			}
			
			function add_range_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type'            => 'range',
					'range_settings'  => array('min'=>'0', 'max'=>'100', 'step'=>'1'),
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_field($id, $label, $settings);
			}
			
			function add_content_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'tiny_mce'
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_field($id, $label, $settings);
			}
			
			function add_multiple_checkbox_field($id, $label='', $settings=array()) {
				$defaults = array(
					'type' => 'multiple_checkboxes'
				);
				$settings = array_merge($defaults, $settings);				
				$this->add_options_field($id, $label, $settings);
			}
			
			function add_options_field($id, $label='', $settings=array()) {
				$defaults = array(
					'options' => array()
				);
				$settings = array_merge($defaults, $settings);
				
				// Apply escaping / translation
				foreach($settings['options'] as $k=>$v) { 
					$settings['options'][$k] = __($v, 'et_builder'); 
				}
				
				$this->add_field($id, $label, $settings);
			}
			
			function add_admin_label_field() {
				$this->add_text_field('admin_label', 'Admin Label', array(
					'description' => 'This will change the label of the module in the builder for easy identification.'
				));
			}
			
			function add_css_id_field() {
				$this->add_text_field('module_id', 'CSS ID', array(
					'tab_slug'        => 'custom_css',
					'option_class'    => 'et_pb_custom_css_regular',
				));
			}
			
			function add_css_class_field() {
				$this->add_text_field('module_class', 'CSS Class', array(
					'tab_slug'        => 'custom_css',
					'option_class'    => 'et_pb_custom_css_regular',
				));
			}
			
			// === Advanced settings ===
			
			function add_font_options($id, $label, $settings=array()) {
				
				$fontfields = array('font', 'font_size', 'text_color', 'line_height', 'letter_spacing');
				
				// Set defaults
				$defaults = array();
				if (!empty($this->current_toggle)) { 
					$defaults['toggle_slug'] = $this->current_toggle;
				}
				$settings = array_merge($defaults, $settings);
				
				// Set default values for font-size and line-height (to prevent overly large mobile defaults)
				if (empty($settings['font_size'])) { $settings['font_size'] = array(); }
				$settings['font_size']['default'] = '14px';
				if (empty($settings['line_height'])) { $settings['line_height'] = array(); }
				$settings['line_height']['default'] = '1.6em';
				
				// Implement global toggle slug setting
				if (!empty($settings['toggle_slug'])) { // check if toggle slug set globally
					foreach($fontfields as $f) {
						if (!isset($settings['hide_'.$f]) or $settings['hide_'.$f]==false) { // if not hidden
							if (empty($settings[$f])) { $settings[$f] = array(); }
							$settings[$f]['toggle_slug'] = $settings['toggle_slug'];
						}
					}
				}
				
				// Builder does not support toggle_slug setting on some fields (e.g. text_color)
				// We can fix this by adding the toggle_slug once the parent constructor has run
				// For now, save them somewhere the constructor can access them
				foreach($fontfields as $f) {
					if (!empty($settings[$f]['toggle_slug'])) {
						$this->toggle_slugs[$id.'_'.$f] = $settings[$f]['toggle_slug'];
					}
				}
				
				$this->add_advanced_option_font('fonts', $id, $label, $settings);
			}
			
			function add_advanced_option_font($type, $id, $label='', $settings=array()) {
				if (!isset($this->advanced_options[$type])) { 
					$this->advanced_options[$type] = array(); 
				}
				$this->advanced_options[$type][$id] = $settings;
				$this->advanced_options[$type][$id]['label'] = $this->escape($label);
			}
			
			
			function add_advanced_option($type, $settings=array()) {
				$defaults = array();
				if (!empty($this->current_toggle)) { 
					$defaults['toggle_slug'] = $this->current_toggle;
				}
				$settings = array_merge($defaults, $settings);	
				$this->advanced_options[$type] = $settings; 		
			}
			
			function add_button_options($settings=array()) {
				$this->add_advanced_option('button', $settings);
			}
			
			function add_background_option($settings=array()) {
				
				// Apply toggle slug
				if (!empty($settings['toggle_slug'])) { 
					$this->toggle_slugs['background_color'] = $settings['toggle_slug'];
					$this->toggle_slugs['background_image'] = $settings['toggle_slug'];
				}
				
				$this->add_advanced_option('background', $settings);
			}
			
			function add_border_option($settings=array()) {
				$defaults = array(
					'css'=>array(
						'important'=>true // needed to override border in Extra
					)
				);
				$settings = array_merge($defaults, $settings);
				
				// Apply toggle slug
				if (!empty($settings['toggle_slug'])) { 
					$this->toggle_slugs['use_border_color'] = $settings['toggle_slug'];
					$this->toggle_slugs['border_color'] = $settings['toggle_slug'];
					$this->toggle_slugs['border_width'] = $settings['toggle_slug'];
					$this->toggle_slugs['border_style'] = $settings['toggle_slug'];
				}
				
				$this->add_advanced_option('border', $settings);
			}
			
			function add_margin_padding_option($settings=array()) {
				
				// Apply toggle slug
				if (!empty($settings['toggle_slug'])) { 
					$this->toggle_slugs['custom_margin'] = $settings['toggle_slug'];
					$this->toggle_slugs['custom_margin_tablet'] = $settings['toggle_slug'];
					$this->toggle_slugs['custom_margin_phone'] = $settings['toggle_slug'];
					$this->toggle_slugs['custom_padding'] = $settings['toggle_slug'];
					$this->toggle_slugs['custom_padding_tablet'] = $settings['toggle_slug'];
					$this->toggle_slugs['custom_padding_phone'] = $settings['toggle_slug'];
				}
				
				$this->add_advanced_option('custom_margin_padding', $settings);
			}
			
			// === Shortcode functions ===
			
			// Wrap the shortcode callback
			function shortcode_callback($atts, $content=null, $function_name) {
				
				$this->function_name = $function_name;
				
				// Add module order class
				$this->shortcode_atts['module_class'] = ET_Builder_Element::add_module_order_class($this->shortcode_atts['module_class'], $function_name);
			
			
				$id = $this->shortcode_atts['module_id'];
				$classes = empty($this->shortcode_atts['module_class'])?array():array($this->shortcode_atts['module_class']);
			
				// Call the module's handler, with Divi's modified parameters
				return $this->shortcode_fn($id, $classes, $this->shortcode_atts, $this->shortcode_content, $function_name);
			}
			
			// Return the html for the module, wrapped by the main module div
			function module_div($id, $classes, $content) {	

				// Add the module class
				$classes[] = $this->slug;			
				
				// escape classes
				foreach($classes as $k=>$c) { 
					$classes[$k] = esc_attr($c);
				}
				
				return sprintf(
					'<div%3$s class="et_pb_module %1$s" style="visibility:hidden">%2$s</div>', // nb: hidden until css loaded
					implode(' ', $classes),
					$content,
					empty($id)?'':sprintf(' id="%1$s"', esc_attr($id))
				);
			}
			
			function add_css($function_name, $prefix='', $css=array()) {
				foreach($css as $selector => $props) {
					foreach($props as $property => $val) {
						if (is_array($val)) { // responsive css
							et_pb_generate_responsive_css($val, $prefix.$selector, $property, $function_name);
						} else { // non-responsive css
							ET_Builder_Module::set_style($function_name, array(
								'selector' => $prefix.$selector,
								'declaration' => esc_html($property).': '.$val.';'
							));
						}
					}
				}
			}
			
			// === CSS adding functions ===
			
			function add_font_icon_css($function_name, $selector_base, $selector, $icon) {
				$sep = html_entity_decode(et_pb_process_font_icon($icon));
				$this->add_css($function_name, $selector_base, array(
					$selector => array(
						'content' => "'".preg_replace('/&#x(.*);/', '\\\\\\1', $sep)."'"
					)
				));
			}
			
			// === Internal functions ===
			
			function prepare() {			
				
				// Whitelist all fields
				$this->whitelisted_fields = array_keys($this->fields);
				
				// Set field defaults
				$this->fields_defaults = array();
				foreach($this->fields as $k=>$v) {
					if (isset($v['default'])) { // default specified
						$this->fields_defaults[$k] = (is_array($v['default']))?$v['default']:array($v['default']);
					} elseif (isset($v['options']) and count($v['options'])) { // use first option
						$keys = array_keys($v['options']);
						$this->fields_defaults[$k] = array($keys[0]); 
					}
				}
				
				// Handle field dependencies
				foreach($this->fields as $k=>$v) {
					if (!empty($v['children'])) { // this option has child options
						$affected = array();
						foreach($v['children'] as $child) {
							$affected[] = "#et_pb_$child";
							$this->fields[$k]['affects'] = array(implode(', ', $affected));
							$this->fields[$child]['depends_default'] = true;
						}
					}
				}
				
				// Apply escaping / translation
				if (!empty($this->name)) { $this->name = $this->escape($this->name); }
				if (!empty($this->child_item_text)) { $this->child_item_text = $this->escape($this->child_item_text); }		
			}
			
			// Apply escaping / divi standard translation to a value
			function escape($v) {
				return empty($v)?$v:esc_html__($v, 'et_builder'); 
			}
			
			// === Local Storage Functions ===
			
			function clear_cache() {
				add_action('admin_head', array($this, 'remove_from_local_storage'));
				add_action('wp_head', array($this, 'remove_from_local_storage'));
			}
			
			function remove_from_local_storage() { 
?>						<script>localStorage.removeItem('et_pb_templates_<?php esc_attr_e($this->slug); ?>');</script>
<?php				}
			
			// ======
			
			// Called by Divi
			function get_fields() {
				return $this->fields;
			}
		}
	}
}
