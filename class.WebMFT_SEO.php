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
				add_action('wp_footer', array( $this, 'show_js'), 99);
			}

			if (isset($this->options['postmeta_is'])){
				add_filter('pre_get_document_title', array (&$this, 'header_title'));
				add_action('wp_head', array( $this, 'header_meta'), 1);
				add_action('wp_head', array( $this, 'header_noindex'), 1);
			}

			if (isset($this->options['analytics_yandex_is'])){
				add_action( 'wp_footer', array( $this, 'analytics_yandex' ) );
			}
			if (isset($this->options['analytics_piwik_is'])){
				add_action( 'wp_footer', array( $this, 'analytics_piwik' ) );
			}

			/**
			 * Add external link to $content
			 */

			if (isset($this->options['extlinks_is'])){
				add_filter('the_content', array( $this, 'add_link_to_content'));
				add_action('wp_head', array( $this, 'add_link_to_content_css'), 99);
			}

			/**
			 * Activate GoTo
			 */
			register_activation_hook(__FILE__, array( $this, 'webmft_goto_activate'));
			register_deactivation_hook(__FILE__, array( $this, 'webmft_goto_deactivate'));
			add_action('init', array( $this, 'webmft_goto_rules'));
			add_filter('query_vars', array( $this, 'webmft_goto_query_vars'));
			add_filter('template_redirect', array( $this, 'webmft_goto_display'));
		}

		remove_action('wp_head', 'wp_print_scripts');
		add_action('wp_footer', 'wp_print_scripts', 5);
		remove_action('wp_head', 'wp_print_head_scripts', 9);
		add_action('wp_footer', 'wp_print_head_scripts', 5);
		// remove_action('wp_head', 'wp_enqueue_scripts', 1);
		// add_action('wp_footer', 'wp_enqueue_scripts', 5);

		add_action('widgets_init', array (&$this, 'register_webmft_widgets'));

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
		$robots_meta = $noindex . ' ' . $nofollow;
		if ( $robots_meta == 'index follow' ) {
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
	 * @return null|object|WP_Post
	 */
	function get_queried_object() {
		static $p = null;
		global $wp_query, $post;
		if ( null !== $p ) {
			return $p;
		}
		if ( is_object( $post ) ) {
			$p = $post;
		} else {
			if ( ! $wp_query ) {
				return null;
			}
			$p = $wp_query->get_queried_object();
		}

		return $p;
	}
	/**
	 * @return bool|null
	 */
	function is_static_posts_page() {
		static $is_posts_page = null;
		if ( $is_posts_page !== null ) {
			return $is_posts_page;
		}
		$post          = $this->get_queried_object();
		$is_posts_page = ( get_option( 'show_on_front' ) == 'page' && is_home() && ! empty( $post ) && $post->ID == get_option( 'page_for_posts' ) );

		return $is_posts_page;
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

	/**
	 * webmft_GoTo
	 */
	function webmft_goto_activate() {
	    webmft_goto_rules();
	    flush_rewrite_rules();
	}

	function webmft_goto_deactivate() {
	    flush_rewrite_rules();
	}

	function webmft_goto_rules() {
		if ('' == $this->options['goto_setup_link']) $goto_setup_link = 'goto';
		else $goto_setup_link = $this->options['goto_setup_link'];
	    add_rewrite_rule(''.$goto_setup_link.'/?([^/]*)', 'index.php?pagename='.$goto_setup_link.'&provider=$matches[1]', 'top');
	}

	function webmft_goto_query_vars($vars) {
	    $vars[] = 'provider';
	    return $vars;
	}

	function webmft_goto_display() {
		if ('' == $this->options['goto_setup_link']) $goto_setup_link = 'goto';
		else $goto_setup_link = $this->options['goto_setup_link'];

	    $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	    $urlTemp = parse_url($url, PHP_URL_PATH);
	    $urlArr = explode('/', $urlTemp);
	    $cust_page = $urlArr[1];
	    $provider = $urlArr[2];
	    if ($goto_setup_link == $cust_page && '' == $provider):
	    	echo '<script>window.location.replace("'.$this->options['goto_provider_def'].'");</script>';
	        exit;
	    elseif ($goto_setup_link == $cust_page && '' != $provider):
	    	echo '<script>window.location.replace("'.$this->options['goto_provider_'.$provider].'");</script>';
	        exit;
	    endif;
	}

	function add_link_to_content($text) {
	    global $post;
	    $content_title = apply_filters( 'single_post_title', $post->post_title, $post);
	    $current_category = single_cat_title('', false);
	    if (get_post_type($post->ID) == 'post') {
	        if (in_category($current_category)) {
	            return $text;
	        } elseif (in_category('News')) {
	            return $text;
	        } else {
	            $text = preg_replace('/(<img.+?>)/iu','<ul class="grid cs-style-3"><li><figure>$1<figcaption><h3>'. $content_title .'</h3><a href="http://www.getbitcoinslotsgambling.xyz/casino-network" target="_blank">Play for real money</a></figcaption></figure></li></ul>', $text, 1);
	            echo $text.'<div><a href="/goto/1/" target="_blank" class="play-for">Play '.$content_title.' for real money</a></div>';
	        }
	    } else {
	        return $text;
	    }
	}
	function add_link_to_content_css() {
		echo '<style>.play-for{-moz-box-shadow:0 10px 14px -7px #276873;-webkit-box-shadow:0 10px 14px -7px #276873;box-shadow:0 10px 14px -7px #276873;background:-webkit-gradient(linear,left top,left bottom,color-stop(.05,#599bb3),color-stop(1,#408c99));background:-moz-linear-gradient(top,#599bb3 5%,#408c99 100%);background:-webkit-linear-gradient(top,#599bb3 5%,#408c99 100%);background:-o-linear-gradient(top,#599bb3 5%,#408c99 100%);background:-ms-linear-gradient(top,#599bb3 5%,#408c99 100%);background:linear-gradient(to bottom,#599bb3 5%,#408c99 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#599bb3\', endColorstr=\'#408c99\', GradientType=0);background-color:#599bb3;-moz-border-radius:8px;-webkit-border-radius:8px;border-radius:8px;display:inline-block;cursor:pointer;color:#fff;font-family:Arial;font-size:15px;font-weight:700;padding:13px 32px;text-decoration:none;text-shadow:0 1px 0 #3d768a}.play-for:hover{background:-webkit-gradient(linear,left top,left bottom,color-stop(.05,#408c99),color-stop(1,#599bb3));background:-moz-linear-gradient(top,#408c99 5%,#599bb3 100%);background:-webkit-linear-gradient(top,#408c99 5%,#599bb3 100%);background:-o-linear-gradient(top,#408c99 5%,#599bb3 100%);background:-ms-linear-gradient(top,#408c99 5%,#599bb3 100%);background:linear-gradient(to bottom,#408c99 5%,#599bb3 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#408c99\', endColorstr=\'#599bb3\', GradientType=0);background-color:#408c99}.play-for:active{position:relative;top:1px}.grid figure,.grid li{position:relative;margin:0}.cs-style-3 figure,.cs-style-4 figure>div{overflow:hidden}.grid{padding:10px 20px 0;max-width:1300px;margin:0 auto;list-style:none;text-align:center}.grid li{display:inline-block;padding:20px;text-align:left}.grid figure img{max-width:100%;display:block;position:relative}.grid figcaption{position:absolute;top:0;left:0;padding:5px 0 0 15px;background:#2c3f52;color:#ed4e6e}.grid figcaption h3{margin:0;padding:0;color:#fff}.grid figcaption span:before{content:"by "}.grid figcaption a{text-align:center;padding:5px 10px;border-radius:2px;display:inline-block;background:#ed4e6e;color:#fff}.cs-style-1 figcaption{height:100%;width:100%;opacity:0;text-align:center;-webkit-backface-visibility:hidden;-moz-backface-visibility:hidden;backface-visibility:hidden;-webkit-transition:-webkit-transform .3s,opacity .3s;-moz-transition:-moz-transform .3s,opacity .3s;transition:transform .3s,opacity .3s}.cs-style-4 figcaption,.cs-style-5 figcaption{-webkit-backface-visibility:hidden;-moz-backface-visibility:hidden}.cs-style-1 figure.cs-hover figcaption,.no-touch .cs-style-1 figure:hover figcaption{opacity:1;-webkit-transform:translate(15px,15px);-moz-transform:translate(15px,15px);-ms-transform:translate(15px,15px);transform:translate(15px,15px)}.cs-style-1 figcaption h3{margin-top:70px}.cs-style-1 figcaption span{display:block}.cs-style-1 figcaption a{margin-top:30px}.cs-style-2 figure img{z-index:10;-webkit-transition:-webkit-transform .4s;-moz-transition:-moz-transform .4s;transition:transform .4s}.cs-style-2 figure.cs-hover img,.no-touch .cs-style-2 figure:hover img{-webkit-transform:translateY(-90px);-moz-transform:translateY(-90px);-ms-transform:translateY(-90px);transform:translateY(-90px)}.cs-style-2 figcaption{height:90px;width:100%;top:auto;bottom:0}.cs-style-2 figcaption a{position:absolute;right:20px;top:30px}.cs-style-3 figure img{-webkit-transition:-webkit-transform .4s;-moz-transition:-moz-transform .4s;transition:transform .4s}.cs-style-3 figure.cs-hover img,.no-touch .cs-style-3 figure:hover img{-webkit-transform:translateY(-50px);-moz-transform:translateY(-50px);-ms-transform:translateY(-50px);transform:translateY(-50px)}.cs-style-3 figcaption{height:100px;width:100%;top:auto;bottom:0;opacity:0;-webkit-transform:translateY(100%);-moz-transform:translateY(100%);-ms-transform:translateY(100%);transform:translateY(100%);-webkit-transition:-webkit-transform .4s,opacity .1s .3s;-moz-transition:-moz-transform .4s,opacity .1s .3s;transition:transform .4s,opacity .1s .3s}.cs-style-3 figcaption a,.cs-style-4 figcaption a,.cs-style-5 figure a,.cs-style-6 figcaption a,.cs-style-7 figcaption a{position:absolute;bottom:20px;right:20px}.cs-style-3 figure.cs-hover figcaption,.no-touch .cs-style-3 figure:hover figcaption{opacity:1;-webkit-transform:translateY(0);-moz-transform:translateY(0);-ms-transform:translateY(0);transform:translateY(0);-webkit-transition:-webkit-transform .4s,opacity .1s;-moz-transition:-moz-transform .4s,opacity .1s;transition:transform .4s,opacity .1s}.cs-style-4 li{-webkit-perspective:1700px;-moz-perspective:1700px;perspective:1700px;-webkit-perspective-origin:0 50%;-moz-perspective-origin:0 50%;perspective-origin:0 50%}.cs-style-4 figure{-webkit-transform-style:preserve-3d;-moz-transform-style:preserve-3d;transform-style:preserve-3d}.cs-style-4 figure img{-webkit-transition:-webkit-transform .4s;-moz-transition:-moz-transform .4s;transition:transform .4s}.cs-style-4 figure.cs-hover img,.no-touch .cs-style-4 figure:hover img{-webkit-transform:translateX(25%);-moz-transform:translateX(25%);-ms-transform:translateX(25%);transform:translateX(25%)}.cs-style-4 figcaption{height:100%;width:50%;opacity:0;backface-visibility:hidden;-webkit-transform-origin:0 0;-moz-transform-origin:0 0;transform-origin:0 0;-webkit-transform:rotateY(-90deg);-moz-transform:rotateY(-90deg);transform:rotateY(-90deg);-webkit-transition:-webkit-transform .4s,opacity .1s .3s;-moz-transition:-moz-transform .4s,opacity .1s .3s;transition:transform .4s,opacity .1s .3s}.cs-style-5 figcaption,.cs-style-6 figcaption,.cs-style-7 figcaption{height:100%;width:100%}.cs-style-4 figure.cs-hover figcaption,.no-touch .cs-style-4 figure:hover figcaption{opacity:1;-webkit-transform:rotateY(0);-moz-transform:rotateY(0);transform:rotateY(0);-webkit-transition:-webkit-transform .4s,opacity .1s;-moz-transition:-moz-transform .4s,opacity .1s;transition:transform .4s,opacity .1s}.cs-style-5 figure img{z-index:10;-webkit-transition:-webkit-transform .4s;-moz-transition:-moz-transform .4s;transition:transform .4s}.cs-style-5 figure.cs-hover img,.no-touch .cs-style-5 figure:hover img{-webkit-transform:scale(.4);-moz-transform:scale(.4);-ms-transform:scale(.4);transform:scale(.4)}.cs-style-5 figcaption{opacity:0;-webkit-transform:scale(.7);-moz-transform:scale(.7);-ms-transform:scale(.7);transform:scale(.7);backface-visibility:hidden;-webkit-transition:-webkit-transform .4s,opacity .4s;-moz-transition:-moz-transform .4s,opacity .4s;transition:transform .4s,opacity .4s}.cs-style-5 figure.cs-hover figcaption,.no-touch .cs-style-5 figure:hover figcaption{-webkit-transform:scale(1);-moz-transform:scale(1);-ms-transform:scale(1);transform:scale(1);opacity:1}.cs-style-6 figure img{z-index:10;-webkit-transition:-webkit-transform .4s;-moz-transition:-moz-transform .4s;transition:transform .4s}.cs-style-6 figure.cs-hover img,.no-touch .cs-style-6 figure:hover img{-webkit-transform:translateY(-50px) scale(.5);-moz-transform:translateY(-50px) scale(.5);-ms-transform:translateY(-50px) scale(.5);transform:translateY(-50px) scale(.5)}.cs-style-6 figcaption h3{margin-top:60%}.cs-style-7 li:first-child{z-index:6}.cs-style-7 li:nth-child(2){z-index:5}.cs-style-7 li:nth-child(3){z-index:4}.cs-style-7 li:nth-child(4){z-index:3}.cs-style-7 li:nth-child(5){z-index:2}.cs-style-7 li:nth-child(6){z-index:1}.cs-style-7 figure img{z-index:10}.cs-style-7 figcaption{opacity:0;-webkit-backface-visibility:hidden;-moz-backface-visibility:hidden;backface-visibility:hidden;-webkit-transition:opacity .3s,height .3s,box-shadow .3s;-moz-transition:opacity .3s,height .3s,box-shadow .3s;transition:opacity .3s,height .3s,box-shadow .3s;box-shadow:0 0 0 0 #2c3f52}.cs-style-7 figure.cs-hover figcaption,.no-touch .cs-style-7 figure:hover figcaption{opacity:1;height:130%;box-shadow:0 0 0 10px #2c3f52}.cs-style-7 figcaption h3{margin-top:86%}.cs-style-7 figcaption a,.cs-style-7 figcaption h3,.cs-style-7 figcaption span{opacity:0;-webkit-transition:opacity 0s;-moz-transition:opacity 0s;transition:opacity 0s}.cs-style-7 figure.cs-hover figcaption a,.cs-style-7 figure.cs-hover figcaption h3,.cs-style-7 figure.cs-hover figcaption span,.no-touch .cs-style-7 figure:hover figcaption a,.no-touch .cs-style-7 figure:hover figcaption h3,.no-touch .cs-style-7 figure:hover figcaption span{-webkit-transition:opacity .3s .2s;-moz-transition:opacity .3s .2s;transition:opacity .3s .2s;opacity:1}@media screen and (max-width:31.5em){.grid{padding:5px 10px 0}.grid li{width:100%;min-width:300px}}</style>';
		echo '<script>;window.Modernizr=function(a,b,c){function w(a){j.cssText=a}function x(a,b){return w(m.join(a+";")+(b||""))}function y(a,b){return typeof a===b}function z(a,b){return!!~(""+a).indexOf(b)}function A(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:y(f,"function")?f.bind(d||b):f}return!1}var d="2.6.2",e={},f=!0,g=b.documentElement,h="modernizr",i=b.createElement(h),j=i.style,k,l={}.toString,m=" -webkit- -moz- -o- -ms- ".split(" "),n={},o={},p={},q=[],r=q.slice,s,t=function(a,c,d,e){var f,i,j,k,l=b.createElement("div"),m=b.body,n=m||b.createElement("body");if(parseInt(d,10))while(d--)j=b.createElement("div"),j.id=e?e[d]:h+(d+1),l.appendChild(j);return f=["&#173;",\'<style id="s\',h,\'">\',a,"</style>"].join(""),l.id=h,(m?l:n).innerHTML+=f,n.appendChild(l),m||(n.style.background="",n.style.overflow="hidden",k=g.style.overflow,g.style.overflow="hidden",g.appendChild(n)),i=c(l,a),m?l.parentNode.removeChild(l):(n.parentNode.removeChild(n),g.style.overflow=k),!!i},u={}.hasOwnProperty,v;!y(u,"undefined")&&!y(u.call,"undefined")?v=function(a,b){return u.call(a,b)}:v=function(a,b){return b in a&&y(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=r.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(r.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(r.call(arguments)))};return e}),n.touch=function(){var c;return"ontouchstart"in a||a.DocumentTouch&&b instanceof DocumentTouch?c=!0:t(["@media (",m.join("touch-enabled),("),h,")","{#modernizr{top:9px;position:absolute}}"].join(""),function(a){c=a.offsetTop===9}),c};for(var B in n)v(n,B)&&(s=B.toLowerCase(),e[s]=n[B](),q.push((e[s]?"":"no-")+s));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)v(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof f!="undefined"&&f&&(g.className+=" "+(b?"":"no-")+a),e[a]=b}return e},w(""),i=k=null,function(a,b){function k(a,b){var c=a.createElement("p"),d=a.getElementsByTagName("head")[0]||a.documentElement;return c.innerHTML="x<style>"+b+"</style>",d.insertBefore(c.lastChild,d.firstChild)}function l(){var a=r.elements;return typeof a=="string"?a.split(" "):a}function m(a){var b=i[a[g]];return b||(b={},h++,a[g]=h,i[h]=b),b}function n(a,c,f){c||(c=b);if(j)return c.createElement(a);f||(f=m(c));var g;return f.cache[a]?g=f.cache[a].cloneNode():e.test(a)?g=(f.cache[a]=f.createElem(a)).cloneNode():g=f.createElem(a),g.canHaveChildren&&!d.test(a)?f.frag.appendChild(g):g}function o(a,c){a||(a=b);if(j)return a.createDocumentFragment();c=c||m(a);var d=c.frag.cloneNode(),e=0,f=l(),g=f.length;for(;e<g;e++)d.createElement(f[e]);return d}function p(a,b){b.cache||(b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag()),a.createElement=function(c){return r.shivMethods?n(c,a,b):b.createElem(c)},a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+l().join().replace(/\w+/g,function(a){return b.createElem(a),b.frag.createElement(a),\'c("\'+a+\'")\'})+");return n}")(r,b.frag)}function q(a){a||(a=b);var c=m(a);return r.shivCSS&&!f&&!c.hasCSS&&(c.hasCSS=!!k(a,"article,aside,figcaption,figure,footer,header,hgroup,nav,section{display:block}mark{background:#FF0;color:#000}")),j||p(a,c),a}var c=a.html5||{},d=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,e=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,f,g="_html5shiv",h=0,i={},j;(function(){try{var a=b.createElement("a");a.innerHTML="<xyz></xyz>",f="hidden"in a,j=a.childNodes.length==1||function(){b.createElement("a");var a=b.createDocumentFragment();return typeof a.cloneNode=="undefined"||typeof a.createDocumentFragment=="undefined"||typeof a.createElement=="undefined"}()}catch(c){f=!0,j=!0}})();var r={elements:c.elements||"abbr article aside audio bdi canvas data datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video",shivCSS:c.shivCSS!==!1,supportsUnknownElements:j,shivMethods:c.shivMethods!==!1,type:"default",shivDocument:q,createElement:n,createDocumentFragment:o};a.html5=r,q(b)}(this,b),e._version=d,e._prefixes=m,e.testStyles=t,g.className=g.className.replace(/(^|\s)no-js(\s|$)/,"$1$2")+(f?" js "+q.join(" "):""),e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};
		!function(e){function n(e){return new RegExp("(^|\\\s+)"+e+"(\\\s+|$)")}function t(e,n){var t=s(e,n)?c:a;t(e,n)}if(Modernizr.touch){var s,a,c;"classList"in document.documentElement?(s=function(e,n){return e.classList.contains(n)},a=function(e,n){e.classList.add(n)},c=function(e,n){e.classList.remove(n)}):(s=function(e,t){return n(t).test(e.className)},a=function(e,n){s(e,n)||(e.className=e.className+" "+n)},c=function(e,t){e.className=e.className.replace(n(t)," ")});var o={hasClass:s,addClass:a,removeClass:c,toggleClass:t,has:s,add:a,remove:c,toggle:t};"function"==typeof define&&define.amd?define(o):e.classie=o,[].slice.call(document.querySelectorAll("ul.grid > li > figure")).forEach(function(e,n){e.querySelector("figcaption > a").addEventListener("touchstart",function(e){e.stopPropagation()},!1),e.addEventListener("touchstart",function(e){o.toggle(this,"cs-hover")},!1)})}}(window);</script>';

		if (!empty($this->options['extlinks_custom_css']) )
			echo '<style>'.$this->options['extlinks_custom_css'].'</style>';
	}

}
?>
