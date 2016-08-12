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

	function webmft_post_most_viewed($args=''){
		global $wpdb,$post;
		parse_str($args, $i);
		$num    = isset($i['num']) ? $i['num']:25;
		$key    = isset($i['key']) ? $i['key']:'views';
		$order  = isset($i['order']) ? 'ASC':'DESC';
		$cache  = isset($i['cache']) ? 1:0;
		$days   = isset($i['days']) ? (int)$i['days']:0;
		$echo   = isset($i['echo']) ? 1:0;
		$format = isset($i['format']) ? stripslashes($i['format']):0;
		$cur_postID = $post->ID;

		if( $cache ){ $cache_key = (string) md5( __FUNCTION__ . serialize($args) );
			if ( $cache_out = wp_cache_get($cache_key) ){ //получаем и отдаем кеш если он есть
				if ($echo) return print($cache_out); else return $cache_out;
			}
		}

		if( $days ){
			$AND_days = "AND post_date > CURDATE() - INTERVAL $days DAY";
			if( strlen($days)==4 )
				$AND_days = "AND YEAR(post_date)=" . $days;
		}

		$sql = "SELECT p.ID, p.post_title, p.post_date, p.guid, p.comment_count, (pm.meta_value+0) AS views
		FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta pm ON (pm.post_id = p.ID)
		WHERE pm.meta_key = '$key' $AND_days
			AND p.post_type = 'post'
			AND p.post_status = 'publish'
		ORDER BY views $order LIMIT $num";
		$results = $wpdb->get_results($sql);
		if( !$results ) return false;

		$out= '<ul>';
		preg_match( '!{date:(.*?)}!', $format, $date_m );
		foreach( $results as $pst ){
			$x == 'li1' ? $x = 'li2' : $x = 'li1';
			if ( (int)$pst->ID == (int)$cur_postID ) $x .= " current-item";
			$Title = $pst->post_title;
			$a1 = "<a href='". get_permalink($pst->ID) ."' title='{$pst->views} просмотров: $Title'>";
			$a2 = "</a>";
			$comments = $pst->comment_count;
			$views = $pst->views;
			if( $format ){
				$date = apply_filters('the_time', mysql2date($date_m[1],$pst->post_date));
				$Sformat = str_replace ($date_m[0], $date, $format);
				$Sformat = str_replace(array('{a}','{title}','{/a}','{comments}','{views}'), array($a1,$Title,$a2,$comments,$views), $Sformat);
			}
			else $Sformat = $a1.$Title.$a2;
			$out .= "<li class='$x'>$Sformat</li>";
		}
		$out .= "</ul>";

		if( $cache ) wp_cache_add($cache_key, $out);

		if( $echo )
			return print $out;
		else
			return $out;
	}

	function webmft_post_prev($post_num=1, $format='{date:j.M.Y} - {a}{title}{/a}', $cache=1, $post_type='post'){
		global $post, $wpdb;

		$cache_key = (string) md5( __FUNCTION__ . $post->ID );
		$cache_flag = __FUNCTION__;
		if ($cache && $cache_out = wp_cache_get($cache_key, $cache_flag)) return $cache_out;
		$sql = "
			SELECT ID, post_title, post_date, comment_count, guid
			FROM $wpdb->posts p
			WHERE p.ID < {$post->ID}
				AND p.post_status = 'publish'
				AND p.post_type = '$post_type'
			ORDER BY p.ID DESC
			LIMIT 1
		";
		$res = $wpdb->get_results($sql);

		$count_res = count($res);
		// если количество меньше нужного, делаем 2-й запрос (кольцевая перелинковка)
		if (!$res){
			$sql = "
				SELECT ID, post_title, post_date, comment_count, guid
				FROM $wpdb->posts p
				WHERE p.ID > {$post->ID}
					AND p.post_status = 'publish'
					AND p.post_type = '$post_type'
				ORDER BY p.ID DESC
				LIMIT 1
			";
			$res = $wpdb->get_results($sql);
		}

		if(!$res) return false;
		// Формировка вывода
		if ($format) preg_match ('!\{date:(.*?)\}!', $format, $date_m);
		foreach ($res as $pst){
			$x = ($x == 'li1') ?  'li2' : 'li1';
			$Title = stripslashes($pst->post_title);
			$a = "<a href='". get_permalink($pst->ID) ."' title='{$Title}'>"; //get_permalink($pst->ID) меняем на $pst->guid если настроено поле guid

			if($format){
				$Sformat = strtr($format, array(
					'{title}'     => $Title
					,'{a}'        => $a
					,'{/a}'       => '</a>'
					,'{comments}' => ($pst->comment_count==0) ? '' : $pst->comment_count
				));
				if($date_m)
					$Sformat = str_replace($date_m[0], apply_filters('the_time', mysql2date($date_m[1], $pst->post_date)), $Sformat);
			}
			else
				$Sformat = "$a$Title</a>";

			$out .= "\t<li class='$x'>$Sformat</li>\n";
		}

		if($cache) wp_cache_add($cache_key, $out, $cache_flag);

		return '<ul>'. $out .'</ul>';
	}

	function webmft_post_next($post_num=5, $format='{date:j.M.Y} - {a}{title}{/a}', $cache=1, $post_type='post'){
		global $post, $wpdb;

		$cache_key = (string) md5( __FUNCTION__ . $post->ID );
		$cache_flag = __FUNCTION__;
		if ($cache && $cache_out = wp_cache_get($cache_key, $cache_flag)) return $cache_out;

		$sql = "
			SELECT ID, post_title, post_date, comment_count, guid
			FROM $wpdb->posts p
			WHERE p.ID > {$post->ID}
				AND p.post_status = 'publish'
				AND p.post_type = '$post_type'
			ORDER BY p.ID ASC
			LIMIT 5
		";

		$res = $wpdb->get_results($sql);

		$count_res = count($res);
		// если количество меньше нужного, делаем 2-й запрос (кольцевая перелинковка)
		if (!$res || $count_res < 5){
			$sql = "
				SELECT ID, post_title, post_date, comment_count, guid
				FROM $wpdb->posts p
				WHERE p.ID < {$post->ID}
					AND p.post_status = 'publish'
					AND p.post_type = '$post_type'
				ORDER BY p.ID ASC
				LIMIT ".(5 - $count_res)."
			";

			$res2 = $wpdb->get_results($sql);

			$res = array_merge($res,$res2);
		}

		if(!$res) return false;
		// Формировка вывода
		if ($format) preg_match ('!\{date:(.*?)\}!', $format, $date_m);
		foreach ($res as $pst){
			$x = ($x == 'li1') ?  'li2' : 'li1';
			$Title = stripslashes($pst->post_title);
			$a = "<a href='". get_permalink($pst->ID) ."' title='{$Title}'>"; //get_permalink($pst->ID) меняем на $pst->guid если настроено поле guid

			if($format){
				$Sformat = strtr($format, array(
					'{title}'     => $Title
					,'{a}'        => $a
					,'{/a}'       => '</a>'
					,'{comments}' => ($pst->comment_count==0) ? '' : $pst->comment_count
				));
				if($date_m)
					$Sformat = str_replace($date_m[0], apply_filters('the_time', mysql2date($date_m[1], $pst->post_date)), $Sformat);
			}
			else
				$Sformat = "$a$Title</a>";

			$out .= "\t<li class='$x'>$Sformat</li>\n";
		}

		if($cache) wp_cache_add($cache_key, $out, $cache_flag);

		return '<ul>'. $out .'</ul>';
	}
}
?>
