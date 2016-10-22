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
        global $wp_query, $posts;

		$this->plugin_name = 'webmft-seo-useful';
		$this->options     = ($opt = get_option($this->option_name))? $opt : $this->def_opt();

		add_action('wp_head', 'webmft_seo');
		add_filter('widget_text', 'do_shortcode');
		add_theme_support('title-tag');

		if ( !is_admin() ){
			if (isset($this->options['postview_is'])){
				add_action('wp_enqueue_scripts', create_function('','wp_enqueue_script("jquery");'));
				add_action('wp_footer', array( &$this, 'show_js'), 99);
			}

			if (isset($this->options['postmeta_is'])){
				add_filter('pre_get_document_title', array (&$this, 'header_title'));
				add_action('wp_head', array (&$this, 'header_meta'), 1);
				add_action('wp_head', array (&$this, 'header_noindex'), 1);
			}

			if (isset($this->options['analytics_yandex_is'])){
				add_action( 'wp_footer', array( $this, 'analytics_yandex' ) );
			}
			if (isset($this->options['analytics_piwik_is'])){
				add_action( 'wp_footer', array( $this, 'analytics_piwik' ) );
			}
		}

		remove_action('wp_head', 'wp_print_scripts');
		add_action('wp_footer', 'wp_print_scripts', 5);
		remove_action('wp_head', 'wp_print_head_scripts', 9);
		add_action('wp_footer', 'wp_print_head_scripts', 5);
		// remove_action('wp_head', 'wp_enqueue_scripts', 1);
		// add_action('wp_footer', 'wp_enqueue_scripts', 5);

		add_action('widgets_init', array (&$this, 'register_webmft_widgets'));

		add_shortcode('webmft_post_most_viewed', array (&$this, 'post_most_viewed'));
		add_shortcode('webmft_post_prev', array (&$this, 'post_prev'));

        /**
         * Add css and js files
         */
    	// add_action('wp_enqueue_scripts', array( $this, 'enqueue_site_styles') );
		if (is_admin()){
    		add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
        	add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		}

	}

	function analytics_yandex() {
		if (!empty($this->options['analytics_yandex_id']) )
			echo '<!-- Yandex.Metrika counter --> <script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter'.$this->options['analytics_yandex_id'].' = new Ya.Metrika({ id:'.$this->options['analytics_yandex_id'].', clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/'.$this->options['analytics_yandex_id'].'" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->';
	}
	function analytics_piwik() {
		if (!empty($this->options['analytics_piwik_id']) )
			echo '<!-- Piwik --> <script type="text/javascript">var _paq = _paq || [];_paq.push(["trackPageView"]);_paq.push(["enableLinkTracking"]);(function(){var u="'.$this->options['analytics_piwik_url_track'].'";_paq.push(["setTrackerUrl", u+"piwik.php"]);_paq.push(["setSiteId", "'.$this->options['analytics_piwik_id'].'"]);var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0];g.type="text/javascript"; g.async=true; g.defer=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);})();</script><noscript><p><img src="'.$this->options['analytics_piwik_url_track'].'piwik.php?idsite='.$this->options['analytics_piwik_id'].'" style="border:0;" alt="" /></p></noscript><!-- End Piwik Code -->';
	}

	function def_opt(){
		return array(
			'postview_is' => 'on',
			'postview_who_count' => 'all',
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
		if (is_home() && is_front_page()){
			$mv_titl = $this->options['postmeta_front_title'];
		} elseif(is_category()) {
			$thisCat = get_category(get_query_var('cat'),false);
			$mv_titl = $this->options['category_'.$thisCat->slug.'_title'];
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
		} elseif(is_category()) {
			$thisCat = get_category(get_query_var('cat'),false);
			$mv_desc = $this->options['category_'.$thisCat->slug.'_description'];
		} else {
			$mv_desc = get_post_meta($post->ID, '_webmft_description', true);
			$mv_keys = get_post_meta($post->ID, '_webmft_keywords', true);
		}

		echo '<meta name="description" content="'.$mv_desc.'">'."\n";
		echo '<meta name="keywords" content="'.$mv_keys.'">'."\n";
	}

	function header_noindex(){
		global $post;

		$robots_meta = apply_filters( 'webmft_robots_meta', $this->get_robots_meta() );
		// echo "\n".'!!!-'.$robots_meta.'-!!!'."\n";
		if (!empty($robots_meta)) {
			$meta_string .= '<meta name="robots" content="' . esc_attr( $robots_meta ) . '" />' . "\n";
		}

		$prev_next = $this->get_prev_next_links( $post );
		$prev      = apply_filters( 'webmft_prev_link', $prev_next['prev'] );
		$next      = apply_filters( 'webmft_next_link', $prev_next['next'] );
		if ( ! empty( $prev ) ) {
			$meta_string .= "<link rel='prev' href='" . esc_url( $prev ) . "' />\n";
		}
		if ( ! empty( $next ) ) {
			$meta_string .= "<link rel='next' href='" . esc_url( $next ) . "' />\n";
		}
		if ( $meta_string != null ) {
			echo "$meta_string\n";
		}
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
        // wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'inc/css/webmft-site-seo.css', array(), $this->version, 'all');
    }
	public function enqueue_site_scripts() {
        wp_enqueue_script($this->plugin_name, MFT_URL . 'inc/js/webmft-site-seo.js', array('jquery'), $this->version, false);
    }

	function get_robots_meta() {
		$webmft_option = $this->options;
		$page          = $this->get_page_number();
		$robots_meta   = $tax_noindex = '';
		if ( isset( $webmft_option['noindex_tax'] ) ) {
			$tax_noindex = $webmft_option['noindex_tax'];
		}

		if ( empty( $tax_noindex ) || ! is_array( $tax_noindex ) ) {
			$tax_noindex = array();
		}

		$noindex       = 'index';
		$nofollow      = 'follow';
		if ( ( is_category() && ! empty( $webmft_option['noindex_category'] ) )
			|| ( ! is_category() && is_archive() && ! is_tag() && ! is_tax() && ( ( is_date() && ! empty( $webmft_option['noindex_archive_date'] ) ) || ( is_author() && ! empty( $webmft_option['noindex_archive_author'] ) ) ) )
		     || ( is_tag() && ! empty( $webmft_option['noindex_tags'] ) )
		     || ( is_search() && ! empty( $webmft_option['noindex_search'] ) )
		     || ( is_404() && ! empty( $webmft_option['noindex_404'] ) )
		     || ( is_tax() && in_array( get_query_var( 'taxonomy' ), $tax_noindex ) )
		) {
			$noindex = 'noindex';
		} elseif ( is_single() || is_page() || $this->is_static_posts_page() || is_attachment() || is_category() || is_tag() || is_tax() || ($page>1) ) {

			$post_type = get_post_type();

			if ( (!empty($webmft_option['noindex_paginated'])) && $page > 1 ) {
				$noindex = 'noindex';
			}
			if ( (!empty($webmft_option['nofollow_paginated'])) && $page > 1 ) {
				$nofollow = 'nofollow';
			}
		}
		$robots_meta = $noindex . ',' . $nofollow;
		if ( $robots_meta == 'index,follow' ) {
			$robots_meta = '';
		}

		return $robots_meta;
	}

	/**
	 * Wrapper for substr() - uses mb_substr() if possible.
	 */
	function substr( $string, $start = 0, $length = 2147483647 ) {
		$args = func_get_args();
		if ( function_exists( 'mb_substr' ) ) {
			return call_user_func_array( 'mb_substr', $args );
		}

		return call_user_func_array( 'substr', $args );
	}
	function get_page_number() {
		$page = get_query_var( 'page' );
		if ( empty( $page ) ) {
			$page = get_query_var( 'paged' );
		}

		return $page;
	}
	/**
	 * @param $link
	 *
	 * @return string
	 */
	function get_paged( $link ) {
		global $wp_rewrite;
		$page      = $this->get_page_number();
		$page_name = 'page';
		if ( ! empty( $wp_rewrite ) && ! empty( $wp_rewrite->pagination_base ) ) {
			$page_name = $wp_rewrite->pagination_base;
		}
		if ( ! empty( $page ) && $page > 1 ) {
			if ( $page == get_query_var( 'page' ) ) {
				$link = trailingslashit( $link ) . "$page";
			} else {
				$link = trailingslashit( $link ) . trailingslashit( $page_name ) . $page;
			}
			$link = user_trailingslashit( $link, 'paged' );
		}

		return $link;
	}
	/**
	 * @param null $post
	 *
	 * @return array
	 */
	function get_prev_next_links( $post = null ) {
		$prev = $next = '';
		$page = $this->get_page_number();
		if ( is_home() || is_archive() || is_paged() ) {
			global $wp_query;
			$max_page = $wp_query->max_num_pages;
			if ( $page > 1 ) {
				$prev = get_previous_posts_page_link();
			}
			if ( $page < $max_page ) {
				$paged = $GLOBALS['paged'];
				if ( ! is_single() ) {
					if ( ! $paged ) {
						$paged = 1;
					}
					$nextpage = intval( $paged ) + 1;
					if ( ! $max_page || $max_page >= $nextpage ) {
						$next = get_pagenum_link( $nextpage );
					}
				}
			}
		} else if ( is_page() || is_single() ) {
			$numpages  = 1;
			$multipage = 0;
			$page      = get_query_var( 'page' );
			if ( ! $page ) {
				$page = 1;
			}
			if ( is_single() || is_page() || is_feed() ) {
				$more = 1;
			}
			$content = $post->post_content;
			if ( false !== strpos( $content, '<!--nextpage-->' ) ) {
				if ( $page > 1 ) {
					$more = 1;
				}
				$content = str_replace( "\n<!--nextpage-->\n", '<!--nextpage-->', $content );
				$content = str_replace( "\n<!--nextpage-->", '<!--nextpage-->', $content );
				$content = str_replace( "<!--nextpage-->\n", '<!--nextpage-->', $content );
				// Ignore nextpage at the beginning of the content.
				if ( 0 === strpos( $content, '<!--nextpage-->' ) ) {
					$content = substr( $content, 15 );
				}
				$pages    = explode( '<!--nextpage-->', $content );
				$numpages = count( $pages );
				if ( $numpages > 1 ) {
					$multipage = 1;
				}
			}
			if ( ! empty( $page ) ) {
				if ( $page > 1 ) {
					$prev = _wp_link_page( $page - 1 );
				}
				if ( $page + 1 <= $numpages ) {
					$next = _wp_link_page( $page + 1 );
				}
			}
			if ( ! empty( $prev ) ) {
				$prev = $this->substr( $prev, 9, - 2 );
			}
			if ( ! empty( $next ) ) {
				$next = $this->substr( $next, 9, - 2 );
			}
		}

		return array( 'prev' => $prev, 'next' => $next );
	}

}
?>
