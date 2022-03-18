<?php 

// The CDN Enabler plugin naively replaces URLs in the page with their CDN equivalent. 
// The code below adds exceptions for URLs appearing within code snippets, to prevent the code snippet from being rewritten

add_action('template_redirect', array(new dbcodesnippet_cdn_enabler_fix(), 'pre_get_options'), 9);

class dbcodesnippet_cdn_enabler_fix {
	
	private $excluded_urls = array();
	
	public function __construct() {}
	
	public function pre_get_options() {
		
		// Get the post content
		global $post;
		if (!isset($post) || !isset($post->ID)) { return; }
		$content = get_post_field('post_content', $post->ID);
		
		// Get and store the content to exclude from CDN Enabler URL replacement
		preg_match_all('#\[et_pb_dmb_code_snippet.*?\](.*?)\[\/et_pb_dmb_code_snippet\]#', $content, $matches);
		$excluded_content = empty($matches[1])?'':implode(' ', array_map('base64_decode', $matches[1]));
		
		// Get URLs to exclude
		$this->excluded_urls = $this->get_urls($excluded_content);
		
		// Filter the CDN Enabler option to add the exclusions
		add_filter('option_cdn_enabler', array($this, 'add_exclusions'));
	}
	
	function add_exclusions($options) {
	
		if (!is_array($options)) { return $options; }
		
		
		// Add the excluded URLs to the CDN Enabler option
		$urls = implode(',', $this->excluded_urls);
		$options['excludes'] = empty($options['excludes'])?$urls:$options['excludes'].','.$urls;
		
		return $options;
	}
	
	// Extract a list of URLs from the excluded content that CDN enabler could rewrite
	// - Based on CDN Enabler's own regex code (v1.0.7)
	public function get_urls($content) {

		$options = wp_parse_args(
			get_option('cdn_enabler', array()),
			array(
				'url'             => get_option('home'),
				'dirs'            => 'wp-content,wp-includes',
				'excludes'        => '.php',
				'relative'        => 1,
				'https'           => 0,
				'keycdn_api_key'  => '',
				'keycdn_zone_id'  => '',
			)
		);

		// check if HTTPS and use CDN over HTTPS enabled
		if (!$options['https'] && isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') {
			return array();
		}
		
		// get dir scope in regex format
		$input = explode(',', $options['dirs']);
		if ($options['dirs'] == '' || count($input) < 1) {
			return 'wp\-content|wp\-includes';
		}
		$dirs = implode('|', array_map('quotemeta', array_map('trim', $input)));
		
		$url = quotemeta(get_option('home'));
		$relative_url = substr($url, strpos($url, '//'));
		
		$blog_url = $options['https']
			? '(https?:|)'.$relative_url
			: '(http:|)'.$relative_url;

		// regex rule start
		$regex_rule = '#(?<=[(\"\'])';

		// check if relative paths
		if ($options['relative']) {
			$regex_rule .= '(?:'.$blog_url.')?';
		} else {
			$regex_rule .= $blog_url;
		}

		// regex rule end
		$regex_rule .= '/(?:((?:'.$dirs.')[^\"\')]+)|([^/\"\']+\.[^/\"\')]+))(?=[\"\')])#';
		
		$matches = array();
		preg_match_all($regex_rule, $content, $matches);
		
		return $matches[0];
	}
}

