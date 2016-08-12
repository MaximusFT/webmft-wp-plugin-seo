<?php

class WebMFT_Post_SEO {
	const OPT_NAME = 'webmft_post_seo';

	public $opt;

	protected static $inst;

	static function init(){
		if(is_null(self::$inst))
			self::$inst = new self;
		return self::$inst;
	}

	function __construct(){
		$this->opt = ($opt = get_option( self::OPT_NAME ))? $opt : $this->def_opt();

		add_action('wp_head', 'webmft_post_seo');
		add_filter('widget_text', 'do_shortcode');
		add_shortcode( 'webmft_post_most_viewed', array (&$this, 'webmft_post_most_viewed'));
		add_shortcode( 'webmft_post_prev', array (&$this, 'webmft_post_prev'));
		add_shortcode( 'webmft_post_next', array (&$this, 'webmft_post_next'));
	}

	function def_opt(){
		return array(
			'test' => 'test',
		);
	}

	function webmft_post_most_viewed($args = ''){
		global $wpdb,$post;
		parse_str($args, $i);
		$num    = isset($i['num']) ? $i['num']:5;
		$key    = isset($i['key']) ? $i['key']:'views';
		$order  = isset($i['order']) ? 'ASC':'DESC';
		$cache  = isset($i['cache']) ? 1:0;
		$days   = isset($i['days']) ? (int)$i['days']:0;
		$echo   = isset($i['echo']) ? 1:0;
		$format = isset($i['format']) ? stripslashes($i['format']):0;
		$cur_postID = $post->ID;

		if($cache) {
			$cache_key = (string) md5(__FUNCTION__ . serialize($args));
			if ($cache_out = wp_cache_get($cache_key)){
				if ($echo) return print($cache_out);
				else return $cache_out;
			}
		}

		if($days){
			$AND_days = "AND post_date > CURDATE() - INTERVAL $days DAY";
			if( strlen($days) == 4 )
				$AND_days = "AND YEAR(post_date)=" . $days;
		}

		$sql = "SELECT p.ID, p.post_title, p.post_date, p.guid, p.comment_count, (pm.meta_value+0) AS views
		FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON (pm.post_id = p.ID)
		WHERE pm.meta_key = '$key' $AND_days AND p.post_type = 'post' AND p.post_status = 'publish'
		ORDER BY views $order LIMIT $num";
		$res = $wpdb->get_results($sql);

		if(!$res) return false;

		foreach($res as $val){
			if ((int)$val->ID == (int)$cur_postID) $classActive = "active";
			else $classActive = '';
			$title = $val->post_title;
			$out .= '<li class="'.$classActive.'"><a class="item" href="'.get_permalink($val->ID).'" title="'.$val->views.' views: '.$title.'">'.$title.'</a></li>';
		}

		if($cache) wp_cache_add($cache_key, $out);

		return '<ul>'. $out .'</ul>';
	}

	function webmft_post_prev(){
		global $post, $wpdb;
		$cache = 1;

		$cache_key = (string) md5( __FUNCTION__ . $post->ID );
		$cache_flag = __FUNCTION__;
		if ($cache && $cache_out = wp_cache_get($cache_key, $cache_flag)) return $cache_out;

		$sql = "
			SELECT ID, post_title, post_date, comment_count, guid
			FROM $wpdb->posts p
			WHERE p.ID < $post->ID
				AND p.post_status = 'publish'
				AND p.post_type = 'post'
			ORDER BY p.ID DESC
			LIMIT 1
		";
		$res = $wpdb->get_results($sql);

		if (!$res){
			$sql = "
				SELECT ID, post_title, post_date, comment_count, guid
				FROM $wpdb->posts p
				WHERE p.ID > $post->ID
					AND p.post_status = 'publish'
					AND p.post_type = 'post'
				ORDER BY p.ID DESC
				LIMIT 1
			";
			$res = $wpdb->get_results($sql);
		}

		if(!$res) return false;

		foreach ($res as $val){

			$sql = "
				SELECT w.meta_value
				FROM $wpdb->postmeta w
					LEFT JOIN $wpdb->postmeta p ON (p.meta_value = w.post_id)
				WHERE p.meta_key = '_thumbnail_id'
					AND p.post_id = $val->ID
					AND w.meta_key = '_wp_attached_file'
				LIMIT 1
			";
			// var_dump($sql);
			$resTH = $wpdb->get_results($sql);
			$temp = str_replace('.jpg', '-150x150.jpg', $resTH[0]->meta_value);
			$temp = str_replace('.png', '-150x150.png', $resTH[0]->meta_value);
			$title = stripslashes($val->post_title);
			//get_permalink($val->ID) меняем на $val->guid если настроено поле guid

			$out .= '<li><a class="item" href="'.get_permalink($val->ID).'" title="'.$title.'"><img src="/wp-content/uploads/'.$temp.'" alt="'.$title.'"></a></li>';
		}

		if($cache) wp_cache_add($cache_key, $out, $cache_flag);

		return '<ul class="list-unstyled" style="padding-left:0;list-style:none;">'. $out .'</ul>';
	}

	function webmft_post_next(){
		global $post, $wpdb;
		$cache = 1;

		$cache_key = (string) md5( __FUNCTION__ . $post->ID );
		$cache_flag = __FUNCTION__;
		if ($cache && $cache_out = wp_cache_get($cache_key, $cache_flag)) return $cache_out;

		$sql = "
			SELECT ID, post_title, post_date, comment_count, guid
			FROM $wpdb->posts p
			WHERE p.ID > $post->ID AND p.post_status = 'publish' AND p.post_type = 'post'
			ORDER BY p.ID ASC
			LIMIT 5
		";

		$res = $wpdb->get_results($sql);

		$count_res = count($res);
		if (!$res || $count_res < 5){
			$sql = "
				SELECT ID, post_title, post_date, comment_count, guid
				FROM $wpdb->posts p
				WHERE p.ID < $post->ID AND p.post_status = 'publish' AND p.post_type = 'post'
				ORDER BY p.ID ASC
				LIMIT ".(5 - $count_res)."
			";
			$res2 = $wpdb->get_results($sql);
			$res = array_merge($res,$res2);
		}

		if(!$res) return false;

		foreach ($res as $val){
			$title = stripslashes($val->post_title);
			//get_permalink($val->ID) меняем на $val->guid если настроено поле guid
			$out .= '<li><a class="item" href="'.get_permalink($val->ID).'" title="'.$title.'">'.$title.'</a></li>';
		}

		if ($cache) wp_cache_add($cache_key, $out, $cache_flag);

		return '<ul>'. $out .'</ul>';
	}
}
?>
