<?php
/**
 * Widget: Post next posts
 * Description: Show next X posts relative Current post
 */
class WEBMFT_PostNext_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'',
			'_Latest posts',
			array('description' => 'Show next X posts relative Current post', /*'classname' => 'my_widget',*/ )
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
		// $cache = 1;

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

		if(!$res) {
			echo $args['after_widget'];
			return false;
		}

		foreach ($res as $val){
			$title = stripslashes($val->post_title);
			//get_permalink($val->ID) меняем на $val->guid если настроено поле guid
			$out .= '<div><a class="item" href="'.get_permalink($val->ID).'" title="'.$title.'">'.$title.'</a></div>';
		}

		$out = '<div class="post-next">'. $out .'</div>';

		if ($cache) wp_cache_add($cache_key, $out, $cache_flag);

		echo $out;
		echo $args['after_widget'];
	}

	function form ($instance) {
		$title = @$instance['title']? : 'Latest posts';
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
