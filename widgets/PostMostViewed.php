<?php
/**
 * Widget: Post most viewed
 * Description: Show 5 most viewed posts
 */
class WEBMFT_PostMostViewed_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'',
			'_Most viewed',
			array('description' => 'Show 5 most viewed posts', /*'classname' => 'my_widget',*/ )
		);
	}

	/**
	 * Вывод виджета во Фронт-энде
	 *
	 * @param array $args     аргументы виджета.
	 * @param array $instance сохраненные данные из настроек
	 */
	function widget( $args, $instance ) {
		global $post, $wpdb;

		$title = apply_filters('widget_title', $instance['title']);

		echo $args['before_widget'];
		if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$num    = isset($instance['num']) ? $instance['num']:5;
		$key    = isset($instance['key']) ? $instance['key']:'views';
		$order  = isset($instance['order']) ? 'ASC':'DESC';
		// $cache  = isset($instance['cache']) ? 1:0;
		$days   = isset($instance['days']) ? (int)$instance['days']:0;
		$echo   = isset($instance['echo']) ? 1:0;
		$format = isset($instance['format']) ? stripslashes($instance['format']):0;
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

		if(!$res) {
			echo $args['after_widget'];
			return false;
		}

		foreach($res as $val){
			if ((int)$val->ID == (int)$cur_postID) $classActive = "active";
			else $classActive = '';
			$title = $val->post_title;
			$out .= '<div class="'.$classActive.'"><a class="item" href="'.get_permalink($val->ID).'" title="'.$val->views.' views: '.$title.'">'.$title.'</a></div>';
		}

		$out = '<div class="post-most-viewed">'. $out .'</div>';

		if($cache) wp_cache_add($cache_key, $out);

		echo $out;
		echo $args['after_widget'];
	}

	function form ($instance) {
		$title = @$instance['title']? : 'Most viewed';
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	function update ($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title']))? strip_tags($new_instance['title']) : '';

		return $instance;
	}
}
?>