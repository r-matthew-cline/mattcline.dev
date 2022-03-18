<?php
/*
Plugin Name: Divi Code Snippet Module
Plugin URI: 
Description: Adds a code snippet module to the Divi Builder
Author: Dan Mossop
Version: 1.0.7
Author URI: http://www.divibooster.com
*/

define('DMB_CODE_SNIPPET_VERSION', '1.0.7');
define('DMB_CODE_SNIPPET_VERSION_OPTION', 'dmb_code_snippet_version');

// === Updates ===
$updatename = 'divi-module-code-snippet';
$updateurl = 'https://dansupdates.com/?action=get_metadata&slug='.$updatename;
include_once(dirname(__FILE__).'/wp-plugin-update-class/plugin-update-checker.php');
try {
	$MyUpdateChecker = new DMWP_PluginUpdateChecker_1_0_0($updateurl, __FILE__, $updatename);
} catch (Exception $e) { echo "Update error: ".$e->getMessage(); exit; }

// Includes
//include_once(dirname(__FILE__).'/divi-custom-module-class/divi-builder-custom-module.class.php');
include_once(dirname(__FILE__).'/classes/db_cs_ET_Builder_Module.php');
include_once(dirname(__FILE__).'/compatibility/cdn-enabler.php');

// Load the admin JS
add_action('admin_enqueue_scripts', 'db_cs_enqueue_admin_js');
function db_cs_enqueue_admin_js() {
    wp_enqueue_script('divi-code-snippet-module', plugins_url('admin/admin.js', __FILE__), array('et_pb_admin_js', 'jquery'), DMB_CODE_SNIPPET_VERSION);
}

// Load module
function load_DMB_Module_Code_Snippet() {
	
	if (class_exists('db_cs_ET_Builder_Module')) { 

		class DMB_Module_Code_Snippet extends db_cs_ET_Builder_Module {
			
			var $color_schemes = array(
					'agate'=>'Agate',
					'androidstudio'=>'Android Studio',
					'arduino-light'=>'Arduino Light',
					'arta'=>'Arta',
					'ascetic'=>'Ascetic',
					'atelier-cave-dark'=>'Cave (Dark)',
					'atelier-cave-light'=>'Cave (Light)',
					'atelier-dune-dark'=>'Dune (Dark)',
					'atelier-dune-light'=>'Dune (Light)',
					'atelier-estuary-dark'=>'Estuary (Dark)',
					'atelier-estuary-light'=>'Estuary (Light)',
					'atelier-forest-dark'=>'Forest (Dark)',
					'atelier-forest-light'=>'Forest (Light)',
					'atelier-heath-dark'=>'Heath (Dark)',
					'atelier-heath-light'=>'Heath (Light)',
					'atelier-lakeside-dark'=>'Lakeside (Dark)',
					'atelier-lakeside-light'=>'Lakeside (Light)',
					'atelier-plateau-dark'=>'Plateau (Dark)',
					'atelier-plateau-light'=>'Plateau (Light)',
					'atelier-savanna-dark'=>'Savanna (Dark)',
					'atelier-savanna-light'=>'Savana (Light)',
					'atelier-seaside-dark'=>'Seaside (Dark)',
					'atelier-seaside-light'=>'Seaside (Light)',
					'atelier-sulphurpool-dark'=>'Sulphur Pool (Dark)',
					'atelier-sulphurpool-light'=>'Sulphur Pool (Light)',
					'brown-paper'=>'Brown Paper',
					'codepen-embed'=>'Codepen Embed',
					'color-brewer'=>'Color Brewer',
					'dark'=>'Dark',
					'darkula'=>'Darkula',
					'docco'=>'Docco',
					'dracula'=>'Dracula',
					'far'=>'Far',
					'foundation'=>'Foundation',
					'github-gist'=>'Github Gist',
					'github'=>'Github',
					'googlecode'=>'Google Code',
					'grayscale'=>'Grayscale',
					'gruvbox-dark'=>'Gruvbox (Dark)',
					'gruvbox-light'=>'Gruvbox (Light)',
					'hopscotch'=>'Hopscotch',
					'hybrid'=>'Hybrid',
					'idea'=>'Idea',
					'ir-black'=>'IR Black',
					'kimbie-dark'=>'Kimbie (Dark)',
					'kimbie-light'=>'Kimbie (Light)',
					'magula'=>'Magula',
					'mono-blue'=>'Mono Blue',
					'monokai-sublime'=>'Monokai Sublime',
					'monokai'=>'Monokai',
					'obsidian'=>'Obsidian',
					'paraiso-dark'=>'Paraiso (Dark)',
					'paraiso-light'=>'Paraiso (Light)',
					'pojoaque'=>'Pojoaque',
					'qtcreator-dark'=>'QT Creator (Dark)',
					'qtcreator-light'=>'QT Creator (Light)',
					'railscasts'=>'Railcasts',
					'rainbow'=>'Rainbow',
					'school-book'=>'School Book',
					'solarized-dark'=>'Solarized (Dark)',
					'solarized-light'=>'Solarized (Light)',
					'sunburst'=>'Sunburst',
					'tomorrow-night-blue'=>'Tomorrow Night (Blue)',
					'tomorrow-night-bright'=>'Tomorrow Night (Bright)',
					'tomorrow-night-eighties'=>'Tomorrow Night (Eighties)',
					'tomorrow-night'=>'Tomorrow Night',
					'tomorrow'=>'Tomorrow',
					'vs'=>'Visual Studio',
					'xcode'=>'XCode',
					'xt256'=>'XT256',
					'zenburn'=>'Zenburn'
				);
			
			function init() {
				
				$this->set_slug('et_pb_dmb_code_snippet'); 
			
				$this->name            = 'Code Snippet';
				$this->icon 		   = '&#x65;'; // Icon code from https://www.elegantthemes.com/blog/resources/elegant-icon-font
				
				//$this->fb_support = true;
				
				// === Handle updates ===
				$old = get_option(DMB_CODE_SNIPPET_VERSION_OPTION);
				$new = DMB_CODE_SNIPPET_VERSION;
				
				if ($old!=$new) { // If updated
					$this->remove_from_local_storage(); // Clear local storage
					update_option(DMB_CODE_SNIPPET_VERSION_OPTION, $new); // Update the stored version number
				} 
				// ======
				
				$this->use_row_content = true; // use_RAW_content
				
				$this->languages = array(
					'Auto Detect'=>'',
					'1C'=>'1c',
					'Access logs'=>'accesslog',
					'ARM assembler'=>'armasm',
					'AVR assembler'=>'avrasm',
					'ActionScript'=>'actionscript',
					'Apache'=>'apache',
					'AppleScript'=>'applescript',
					'AsciiDoc'=>'asciidoc',
					'AspectJ'=>'aspectj',
					'AutoHotkey'=>'autohotkey',
					'AutoIt'=>'autoit',
					'Axapta'=>'axapta',
					'Bash'=>'bash',
					'Basic'=>'basic',
					'BNF'=>'bnf',
					'C'=>'c',
					'C#'=>'cs',
					'C++'=>'cpp',
					'C/AL'=>'cal',
					'Cache Object Script'=>'cos',
					'CMake'=>'cmake',
					'CSP'=>'csp',
					'CSS'=>'css',
					'Cap\'n Proto'=>'capnproto',
					'Clojure'=>'clojure',
					'CoffeeScript'=>'coffeescript',
					'Crmsh'=>'crmsh',
					'Crystal'=>'crystal',
					'D'=>'d',
					'DNS Zone file'=>'dns',
					'DOS'=>'dos',
					'Dart'=>'dart',
					'Delphi'=>'delphi',
					'Diff'=>'diff',
					'Django'=>'django',
					'Dockerfile'=>'dockerfile',
					'DTS (Device Tree)'=>'dts',
					'Dust'=>'dust',
					'Elixir'=>'elixir',
					'Elm'=>'elm',
					'Erlang'=>'erlang',
					'F#'=>'fsharp',
					'FIX'=>'fix',
					'Fortran'=>'fortran',
					'G-Code'=>'gcode',
					'Gams'=>'gams',
					'GAUSS'=>'gauss',
					'Gherkin'=>'gherkin',
					'Go'=>'go',
					'Golo'=>'golo',
					'Gradle'=>'gradle',
					'Groovy'=>'groovy',
					'HTML / XML'=>'html',
					'HTTP'=>'http',
					'Haml'=>'haml',
					'Handlebars'=>'handlebars',
					'Haskell'=>'haskell',
					'Haxe'=>'haxe',
					'Ini'=>'ini',
					'Inform7'=>'inform7',
					'IRPF90'=>'irpf90',
					'JSON'=>'json',
					'Java'=>'java',
					'JavaScript'=>'javascript',
					'Lasso'=>'lasso',
					'Less'=>'less',
					'Lisp'=>'lisp',
					'LiveCode Server'=>'livecodeserver',
					'LiveScript'=>'livescript',
					'Lua'=>'lua',
					'Makefile'=>'makefile',
					'Markdown'=>'markdown',
					'Mathematica'=>'mathematica',
					'Matlab'=>'matlab',
					'Maxima'=>'maxima',
					'Maya Embedded Language'=>'mel',
					'Mercury'=>'mercury',
					'Mizar'=>'mizar',
					'Mojolicious'=>'mojolicious',
					'Monkey'=>'monkey',
					'Moonscript'=>'moonscript',
					'NSIS'=>'nsis',
					'Nginx'=>'nginx',
					'Nimrod'=>'nimrod',
					'Nix'=>'nix',
					'OCaml'=>'ocaml',
					'Objective C'=>'objectivec',
					'OpenGL Shading Language'=>'glsl',
					'OpenSCAD'=>'openscad',
					'Oracle Rules Language'=>'ruleslanguage',
					'Oxygene'=>'oxygene',
					'PF'=>'pf',
					'PHP'=>'php',
					'Parser3'=>'parser3',
					'Perl'=>'perl',
					'PowerShell'=>'powershell',
					'Processing'=>'processing',
					'Prolog'=>'prolog',
					'Protocol Buffers'=>'protobuf',
					'Puppet'=>'puppet',
					'Python'=>'python',
					'Python profiler results'=>'profile',
					'Q'=>'k',
					'QML'=>'qml',
					'R'=>'r',
					'RenderMan RIB'=>'rib',
					'RenderMan RSL'=>'rsl',
					'Roboconf'=>'graph',
					'Ruby'=>'ruby',
					'Rust'=>'rust',
					'SCSS'=>'scss',
					'SQL'=>'sql',
					'STEP Part 21'=>'p21',
					'Scala'=>'scala',
					'Scheme'=>'scheme',
					'Scilab'=>'scilab',
					'Smali'=>'smali',
					'Smalltalk'=>'smalltalk',
					'Stan'=>'stan',
					'Stata'=>'stata',
					'Stylus'=>'stylus',
					'Swift'=>'swift',
					'Tcl'=>'tcl',
					'TeX'=>'tex',
					'Thrift'=>'thrift',
					'TP'=>'tp',
					'Twig'=>'twig',
					'TypeScript'=>'typescript',
					'VB.Net'=>'vbnet',
					'VBScript'=>'vbscript',
					'VHDL'=>'vhdl',
					'Vala'=>'vala',
					'Verilog'=>'verilog',
					'Vim Script'=>'vim',
					'x86 Assembly'=>'x86asm',
					'XL'=>'xl',
					'XML'=>'xml',
					'XQuery'=>'xpath',
					'Zephir'=>'zephir'
				);
				$this->languages = array_flip($this->languages);
				
				// Sort the styles and add a default option
				asort($this->color_schemes);
				$this->color_schemes = array_merge(array('default'=>'Default'), $this->color_schemes); 
				
				// === General Settings tab ===
				$this->set_tab('general');
				
				$this->add_text_field('title', 'Title', array(
					'description'     => 'Optional title for the code box.',
				));
			
				$this->add_textarea_field('raw_content', 'Code', array(
					'description'     => 'Enter the snippet of code you want to display.'
				));
				
				$this->add_skipped_field('max_width_tablet');
				$this->add_skipped_field('max_width_phone');
				
				$this->add_multiple_checkbox_field('disabled_on', 'Disable on', array(
					'options' => array('phone'=>'Phone', 'tablet'=>'Tablet', 'desktop'=>'Desktop'),
					'additional_att'  => 'disable_on',
					'description'     => 'This will disable the module on selected devices',
				));
				
				$this->add_admin_label_field();
				
				// === Advanced Settings tab === 
				$this->set_tab('advanced');
			
				// Code Styling
				$this->add_heading('code_styles', 'Code Styling');
				
				$this->add_select_field('language', 'Language', array(
					'options' => $this->languages,
					'description' => 'The language your code is written in (used to determine highlighting).'
				));
				
				$this->add_select_field('style', 'Style', array(
					'options' => $this->color_schemes,
					'description' => 'The color scheme to use for highlighting.',
				));
			
				$this->add_yes_no_field('linenums', 'Show line numbers');
				
				$this->add_yes_no_field('usetabwidth', 'Custom Tab Width', array(
					'children' => array('tabwidth')
				));
				
				$this->add_range_field('tabwidth', 'Tab Width (in spaces)', array(
					'default'	=> 8,
					'description' => 'Note: tabs will be converted to spaces in some browsers (e.g. IE)'
				));
				
				$this->add_font_options('body', 'Code', array(
					'css' => array(
						'main' => "{$this->main_css_element} pre code",
						'line_height' => "{$this->main_css_element} pre code",
					),
				));
				
				// Header Styling
				$this->add_heading('heading_styles', 'Heading Styling');
				
				$this->add_font_options('header', 'Header', array(
					'css' => array(
						'main' => "{$this->main_css_element} header"
					),
				));
				
				// Module Styling
				$this->add_heading('module_styles', 'Module Styling');
				
				$this->add_border_option();
				
				
				// === Custom CSS tab ===
				$this->set_tab('custom_css');
				
				$this->add_css_id_field();
				$this->add_css_class_field();
				
				$this->add_custom_css_box('codesnippet_heading', 'Heading', 'header');
				$this->add_custom_css_box('codesnippet_linenums', 'Line Numbers', 'pre code[data-linenums="on"]:before');
				$this->add_custom_css_box('codesnippet_codearea', 'Code Area', 'pre code.hljs');
				
				// === Create the module ===
				
				$this->prepare();
			}
			
			// Divi 3.1+
			public function get_settings_modal_toggles() {
				return $this->options_toggles;
			}

			// Divi 3.1+ - replaces advanced field types added above (which are kept for backwards compatibility
			public function get_advanced_fields_config() {
				return array(
					'background' => array(
						'css' => array(
							'main' => "{$this->main_css_element} > pre > code",
							'important' => true,
						),
					),
					'margin_padding' => array(
						'css' => array(
							'important' => 'all',
						),
					),	
					'fonts' => array(
						'body' => array(
							'css' => array(
								'main' => "{$this->main_css_element} pre code",
								'line_height' => "{$this->main_css_element} pre code",
							),
							'label' => 'Code',
							'toggle_slug' => 'code_styles',
							'tab_slug' => 'advanced'
						),
						'header' => array(
							'css' => array(
								'main' => "{$this->main_css_element} header"
							),
							'label' => 'Header',
							'toggle_slug' => 'heading_styles',
							'tab_slug' => 'advanced'
						),
					),
				);
			}
			
			function module_used() {
				// Load syntax highlighting script and styles
				wp_enqueue_style($this->slug.'-highlightjs-syntax-styles', plugins_url('/highlightjs/styles.min.css' , __FILE__ ));
				wp_enqueue_script($this->slug.'-highlightjs-syntax-highlighter', plugins_url('/highlightjs/highlight.pack.js', __FILE__), array('jquery')); 
			}
			
			function user_css() { 				
?>				.et_pb_dmb_code_snippet pre, #et_builder_outer_content .et_pb_dmb_code_snippet pre {
					margin: 0;
					padding: 0;
				}
				.et_pb_dmb_code_snippet pre code, #et_builder_outer_content .et_pb_dmb_code_snippet pre code{
					margin: 0;
					font-family: monospace;
					padding: 1em; 
					white-space: pre; /* don't wordwrap */
					word-wrap: normal; /* don't wordwrap on ie */
					display: block;
					overflow-x: auto;
					visibility: hidden;
				}
				.et_pb_dmb_code_snippet pre code:before, #et_builder_outer_content .et_pb_dmb_code_snippet pre code:before{
					font-family: monospace;
				}
				.et_pb_dmb_code_snippet pre code span, #et_builder_outer_content .et_pb_dmb_code_snippet pre code span {
					font-size: 100%; 
				}
				.et_pb_dmb_code_snippet.et_pb_dmb_code_snippet_linenums pre code:before, #et_builder_outer_content .et_pb_dmb_code_snippet.et_pb_dmb_code_snippet_linenums pre code:before{
					float: left; 
					text-align:right; 
					padding: 1em; 
					margin: -1em 1em -1em -1em;
				}
				.et_pb_dmb_code_snippet header, #et_builder_outer_content .et_pb_dmb_code_snippet header {
					margin: 0;
				}
				
				/* Display straight away in visual builder */
				.et-fb .et_pb_dmb_code_snippet, 
				.et-fb #et_builder_outer_content .et_pb_dmb_code_snippet,
				.et-fb .et_pb_dmb_code_snippet pre code, 
				.et-fb #et_builder_outer_content .et_pb_dmb_code_snippet pre code {
					visibility: visible !important;
				}
<?php		}
			
			function user_js() { 
?>				// Fix tabs
				$('.et_pb_dmb_code_snippet pre code').each(function() {
					if ($(this).attr('data-tabsize')) {
						
						// get module tab size
						var tabSize = $(this).data('tabsize'); 
						
						// Set tab width via CSS where supported
						$(this).css('tab-size', tabSize.toString()); 
						
						// Otherwise, replace tab with required number of spaces
						var e = document.createElement('i');
						if(e.style.tabSize !== '' && e.style.MozTabSize !== '' && e.style.oTabSize !== '') {
							$(this).text($(this).text().replace(/\t/g, repeat(" ", tabSize)));
						}
					}
				});
				function repeat(st, n) {
				   var s = "";
				   while (--n >= 0) {
					 s += st
				   }
				   return s;
				}
				
				// Highlight code and make module visible
				$('.et_pb_dmb_code_snippet pre code').each(function(i, block) {
					hljs.highlightBlock(block);
				});
				$('.et_pb_dmb_code_snippet, .et_pb_dmb_code_snippet pre code').css('visibility', 'visible');
<?php		}
			
			function shortcode_fn($id, $classes, $atts, $content, $function_name) {
				
				// Decode to get raw code
				$content = base64_decode(strip_tags($content)); // remove html tags added by visual editor and decode
				
				$classes[] = 'et_pb_code';
				
				if ($atts['linenums']==='on') { 
					$numlines = count(explode("\n", $content));
					$classes[] = 'et_pb_dmb_code_snippet_linenums'; 
					$this->add_css($function_name, '%%order_class%% ', array(
						'pre code:before' => array(
							'content' => '"'.implode('\A ', range(1,$numlines)).'"'
							)
						)
					);
				}
				
				// double encode if visual editor active
				if (is_admin()) { 
					$content = htmlentities($content); 
				}
				
				$title = empty($atts['title'])?'':'<header>'.htmlentities($atts['title']).'</header>';
				$tabwidth = (!empty($atts['usetabwidth']) and $atts['usetabwidth']==='on' and !empty($atts['tabwidth']))?' data-tabsize="'.esc_attr($atts['tabwidth']).'"':'';
				$linenums = (!empty($atts['linenums']))?' data-linenums="'.esc_attr($atts['linenums']).'"':'';
				$code = '<pre class="'.esc_attr($atts['style']).'"><code class="hljs '.esc_attr($atts['language']).'"'.$tabwidth.$linenums.'>'.htmlentities($content).'</pre></code>';
				
				return $this->module_div($id, $classes, $title.$code);
			}
			
		}
		new DMB_Module_Code_Snippet;
	}
}
add_action('wp_loaded', 'load_DMB_Module_Code_Snippet');

function dmb_code_snippet_visual_builder_css() { 
	if (function_exists('et_fb_enabled') and et_fb_enabled()) { ?>
		<style>.et-fb .et-fb-modules-list li.et_fb_dmb_code_snippet:before { font-family: 'etModules'; content: '\65'; }</style>
		<?php 
	}
}
add_action('wp_head', 'dmb_code_snippet_visual_builder_css');


