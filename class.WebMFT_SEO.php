<?php

class WebMFT_SEO {
	public $meta_key = 'views';

	protected $option_name = 'webmft_option';
    protected $options;
	protected static $inst;

	static function init(){
		if (is_null(self::$inst)) self::$inst = is_admin() ? new WebMFT_SEO_Admin() : new self;
		return self::$inst;
	}

	function __construct(){
        $this->plugin_name      = 'webmft-seo-useful';
        // $this->api_url          = 'http://wpshop.biz/api.php';
        // $this->api_update_url   = 'https://api.wpgenerator.ru/wp-update-server/?action=get_metadata&slug=' . $this->plugin_name;
        // $this->check_license    = $this->check_license();

		$this->options = ($opt = get_option($this->option_name))? $opt : $this->def_opt();

		add_action('wp_head', 'webmft_seo');
		add_filter('widget_text', 'do_shortcode');

		if (!is_admin() && isset($this->options['postview_is'])){
			add_action('wp_enqueue_scripts', create_function('','wp_enqueue_script("jquery");'));
			add_action('wp_footer', array( &$this, 'show_js'), 99);
		}

		if (!is_admin() && isset($this->options['postmeta_is'])){
			add_filter('pre_get_document_title', array (&$this, 'header_title'));
			add_action('wp_head', array (&$this, 'header_meta'), 1);
		}

		add_action('widgets_init', array (&$this, 'register_webmft_widgets'));

		add_shortcode('webmft_post_most_viewed', array (&$this, 'post_most_viewed'));
		add_shortcode('webmft_post_prev', array (&$this, 'post_prev'));

        /**
         * Add css and js files
         */
    	add_action('wp_enqueue_scripts', array( $this, 'enqueue_site_styles') );
		if (is_admin()){
    		add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
        	add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		}
	}

	function def_opt(){
		return array(
			// 'postmeta_is' => 0, // Выключено для совместимости с теми сайтами где уже установлен плагин
			'postview_is' => 'on',
			'postview_who_count' => 'not_administrators',
			'postview_hold_sec' => 2,
		);
	}

	function register_webmft_widgets() {
		register_widget('WEBMFT_PostMostViewed_Widget');
		register_widget('WEBMFT_PostNext_Widget');
		register_widget('WEBMFT_PostPrev_Widget');
	}

	function show_js(){
		// allow manage script show. In the filter maybe you need to set custom $wp_query->queried_object
		$force_show = apply_filters('webmft_seo_postviews_force_show_js', false);

		if (!$force_show){
			if (is_attachment() || is_front_page()) return;
			if (!( is_singular() || is_tax() || is_category() || is_tag())) return;
		}

		$should_count = 0;
		switch ($this->options['postview_who_count']) {
			case 'all': $should_count = 1;
				break;
			case 'not_logged_users':
				if (!is_user_logged_in())
					$should_count = 1;
				break;
			case 'logged_users':
				if (is_user_logged_in())
					$should_count = 1;
				break;
			case 'not_administrators':
				if (!current_user_can('manage_options'))
					$should_count = 1;
				break;
			default : $should_count = 0;
		}

		if (!$should_count) return;

		global $post, $wpdb;

		$queri = get_queried_object();

		// post
		if (isset($queri->post_type) && isset($post->ID)){
			$view_type = 'post_view';

			$_sql = $wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1", $post->ID, $this->meta_key );

			// create if not exists
			if (!$row = $wpdb->get_row($_sql)){
				if (add_post_meta($post->ID, $this->meta_key, '0', true))
					$row = $wpdb->get_row($_sql);
			}
		} elseif (isset($queri->term_id) && $wpdb->termmeta){
			$view_type = 'term_view';

			$_sql = $wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->termmeta WHERE term_id = %d AND meta_key = %s LIMIT 1", $queri->term_id, $this->meta_key);

			// create if not exists
			if (!$row = $wpdb->get_row($_sql)){
				if (add_term_meta($queri->term_id, $this->meta_key, '0', true))
					$row = $wpdb->get_row($_sql);
			}
		}

		if (!isset($view_type) || ! $row) return;

		$relpath = '';

		ob_start();
		?>
		<script>setTimeout(function(){
			jQuery.post(
				'<?php echo MFT_URL . 'ajax-request.php' ?>',
				{meta_id:'<?php echo $row->meta_id ?>', view_type:'<?php echo $view_type ?>', relpath:'<?php echo $relpath ?>'},
				function(result){jQuery('.ajax_views').html(result);}
			);
		}, <?php echo ($this->options['postview_hold_sec'] * 1000) ?>);
		</script>
		<?php
		$script = apply_filters('webmft_seo_postviews_script', ob_get_clean());

		echo preg_replace('~[\r\n\t]~', '', $script)."\n";

		do_action('after_webmft_seo_postviews_show_js');
	}

	function header_title(){
		global $post;
		if (is_front_page()){
			$mv_titl = $this->options['postmeta_front_title'];
		} else {
			$mv_titl = get_post_meta($post->ID, '_webmft_title', true);
		}

		return $mv_titl;
	}

	function header_meta(){
		global $post;

		if(is_front_page()){
			$mv_desc = $this->options['postmeta_front_description'];
			$mv_keys = $this->options['postmeta_front_keywords'];
		} else {
			$mv_desc = get_post_meta($post->ID, '_webmft_description', true);
			$mv_keys = get_post_meta($post->ID, '_webmft_keywords', true);
		}

		echo '<meta name="description" content="'.$mv_desc.'">
';
		echo '<meta name="keywords" content="'.$mv_keys.'">
';
	}

	function robots_custom(){
		global $post;

		if($_SERVER['REQUEST_URI']=='/robots.txt'){
			require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

			/*
			add_filter('robots_txt', 'add_robotstxt');
			function add_robotstxt($text){
				$text .= "Disallow: *\/comments2222222222";
				return $text;
			}
			*/
			do_robots();

			exit;
		}
	}

    /**
     * Register the Stylesheets for the admin area
     * Register the JavaScript for the admin area
     *
     */
    public function enqueue_admin_styles() {
        wp_enqueue_style($this->plugin_name, MFT_URL . 'inc/css/webmft-admin-seo.css', array(), $this->version, 'all');
    }
	public function enqueue_admin_scripts() {
        wp_enqueue_script($this->plugin_name, MFT_URL . 'inc/js/webmft-admin-seo.js', array('jquery'), $this->version, false);
    }

    /**
     * Register the Stylesheets for the site
     * Register the JavaScript for the site
     *
     */
    public function enqueue_site_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'inc/css/webmft-site-seo.css', array(), $this->version, 'all');
    }
	public function enqueue_site_scripts() {
        wp_enqueue_script($this->plugin_name, MFT_URL . 'inc/js/webmft-site-seo.js', array('jquery'), $this->version, false);
    }
}
?>
