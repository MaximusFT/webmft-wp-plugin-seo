<?php

class WebMFT_Postviews {
	const OPT_NAME = 'webmft_postviews';

	public $opt;
	public $wp_load_php_path;
	public $meta_key = 'views';

	protected static $inst;

	static function init(){
		if( is_null(self::$inst) )
			self::$inst = is_admin() ? new WebMFT_Postviews_Admin() : new self;

		return self::$inst;
	}

	function __construct(){
		$this->opt = ( $opt = get_option( self::OPT_NAME ) ) ? $opt : $this->def_opt();

		if( ! is_admin() ){
			# jquery
			add_action('wp_enqueue_scripts', create_function('','wp_enqueue_script("jquery");') );
			add_action('wp_footer', array( &$this, 'show_js' ), 99 );
		}

	}

	function def_opt(){
		return array(
			'who_count' => 'not_administrators', // Чьи посещения считать? all - Всех. not_logged_users - Только гостей. logged_users - Только авторизованных пользователей. not_administrators - Всех, кроме админов.
			'hold_sec' => 2, // Задержка в секундах
			//'wp_config_relpath' => '', // относительный путь до конфигурационного файла wp-config.php
		);
	}

	function show_js(){
		// allow manage script show. In the filter maybe you need to set custom $wp_query->queried_object
		$force_show = apply_filters('webmft_postviews_force_show_js', false );

		if( ! $force_show ){
			if( is_attachment() || is_front_page() )  return;
			if( ! ( is_singular() || is_tax() || is_category() || is_tag() ) )  return;
		}

		$should_count = 0;
		switch( $this->opt['who_count'] ) {
			case 'all': $should_count = 1;
				break;
			case 'not_logged_users': 
				if( ! is_user_logged_in() ) 
					$should_count = 1;
				break;
			case 'logged_users': 
				if( is_user_logged_in() ) 
					$should_count = 1;
				break;
			case 'not_administrators': 
				if( ! current_user_can('manage_options') ) 
					$should_count = 1;
				break;
			default : $should_count = 0;
		}

		if( ! $should_count ) return; // not count...

		global $post, $wpdb;

		$queri = get_queried_object();

		// post
		if( isset($queri->post_type) && isset($post->ID) ){
			$view_type = 'post_view';

			$_sql = $wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s LIMIT 1", $post->ID, $this->meta_key );

			// create if not exists
			if( ! $row = $wpdb->get_row( $_sql ) ){
				if( add_post_meta( $post->ID, $this->meta_key, '0', true ) )
					$row = $wpdb->get_row( $_sql );
			}
		}
		// term
		elseif( isset($queri->term_id) && $wpdb->termmeta ){
			$view_type = 'term_view';

			$_sql = $wpdb->prepare("SELECT meta_id, meta_value FROM $wpdb->termmeta WHERE term_id = %d AND meta_key = %s LIMIT 1", $queri->term_id, $this->meta_key );

			// create if not exists
			if( ! $row = $wpdb->get_row( $_sql ) ){
				if( add_term_meta( $queri->term_id, $this->meta_key, '0', true ) )
					$row = $wpdb->get_row( $_sql );
			}
		}

		if( ! isset($view_type) || ! $row ) return; // just in case...

		$relpath = '';

		ob_start();
		?>
		<script>setTimeout( function(){
			jQuery.post(
				'<?php echo KAP_URL . 'ajax-request.php' ?>',
				{meta_id:'<?php echo $row->meta_id ?>', view_type:'<?php echo $view_type ?>', relpath:'<?php echo $relpath ?>'},
				function(result){jQuery('.ajax_views').html(result);}
			);
		}, <?php echo ($this->opt['hold_sec']*1000) ?>);
		</script>
		<?php
		$script = apply_filters('webmft_postviews_script', ob_get_clean() );

		echo preg_replace('~[\r\n\t]~', '', $script )."\n";

		do_action('after_webmft_postviews_show_js');
	}

}


// admin part  --------------
class WebMFT_Postviews_Admin extends WebMFT_Postviews {

	function __construct(){
		parent::__construct();

		add_action('current_screen', array( &$this, 'add_tax_field_hooks'), 99 );

		add_action('admin_menu', array( &$this, 'add_options_page') );
		add_action('admin_init', array( &$this, 'register_setting') );

		add_filter('plugin_action_links_'. KAP_BASE, array( &$this, 'settings_link' ) );
	}

	function add_tax_field_hooks(){
		if( ! @ get_current_screen()->taxonomy ) return; // execution limit...
		if( ! function_exists('get_term_meta') ) return; // do nothing to WP < 4.4

		foreach( get_taxonomies(array('public'=>true, 'query_var'=>true, 'rewrite'=>true)) as $taxname ){
			// метаполя
			add_action("{$taxname}_edit_form_fields", array( & $this, 'edit_tax_fields') );
			add_action("edited_{$taxname}", array( & $this, 'save_tax_meta') );

			// колонка
			add_filter("manage_edit-{$taxname}_columns", array( & $this, 'add_tax_columns') );
			add_filter("manage_{$taxname}_custom_column", array( & $this, 'fill_tax_columns'), 10, 3);
		}

		add_action('admin_head', array( & $this, 'col_css') );
	}

	// Tax Metadata ---
	function edit_tax_fields( $term ) {
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="extra1">Просмотры</label></th>
			<td>
				<input type="number" min="0" name="termviews[views]" value="<?php echo (int) get_term_meta( $term->term_id, 'views', 1 ) ?>" style="max-width:100px;"><br />
				<!--<span class="description">Заголовок для SEO</span>-->
			</td>
		</tr>
		<?php
	}

	function save_tax_meta( $term_id ) {
		if ( ! isset($_POST['termviews']) )   return;

		$views = (int) $_POST['termviews']['views'];
		update_term_meta( $term_id, 'views', $views );
	}

	// Tax column ---
	function add_tax_columns( $columns ){
			$_columns = array();
		$i=1;
		foreach( $columns as $k => $col ){
			$_columns[ $k ] = $col;
			if( $i++ == 4 ) $_columns['views'] = '<span title="'. __('Views','kap') .'" class="dashicons dashicons-visibility"></span>';
		}

		return $_columns;
	}

	function fill_tax_columns( $out, $colname, $id ){
		if( $colname == 'views' )
			$out = ($v=get_term_meta( $id, 'views', 1 )) ? $v : '0';

		return $out;
	}

	function col_css(){
		echo '<style>.column-views{ width:50px; }</style>';
	}

	// Settings page link in plugins table ---
	function settings_link( $links ){
		array_unshift( $links, '<a href="'. admin_url('options-general.php?page=webmft_postviews') .'">'. __('Settings','kap') .'</a>' );
		return $links;
	}

	function add_options_page(){
		add_submenu_page( null, 'Kama Postviews', 'Kama Postviews', 'manage_options', 'webmft_postviews', array( &$this, 'options_page_output') );
	}

	function options_page_output(){
		?>
		<div class="wrap">
			<h2>Kama Postviews</h2>

			<form action="options.php" method="POST">
				<?php
				settings_fields('option_group');  // скрытые защитные поля
				do_settings_sections('kap_page'); // секции с настройками (опциями). У нас она всего одна 'section_id'
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register settings
	 * settings are saves as array
	 */
	function register_setting(){
		register_setting('option_group', self::OPT_NAME, array( &$this, 'option_sanitize_cb') );

		$section = 'kap_section_1';
		add_settings_section( $section, '', '', 'kap_page' ); 

		// параметры: $id, $title, $callback, $page, $section, $args
		$id = 'who_count';
		add_settings_field( $id, __('Whose visit count?','kap'), array( &$this, 'fill_field'), 'kap_page', $section, array('id'=>$id, 'desc'=>'' ) );

		$id = 'hold_sec';
		add_settings_field( $id, __('Delay in seconds','kap'), array( &$this, 'fill_field'), 'kap_page', $section, array('id'=>$id, 'desc'=>__('How many seconds to delay and then count visit?','kap') ) );

		//$id = 'wp_config_relpath';
		//add_settings_field( $id, __('Relative path to detect wp-config.php','kap'), array( &$this, 'fill_field'), 'kap_page', $section, array('id'=>$id, 'desc'=>__('Relative path between DOCUMENT_ROOT and wp-config.php <code>DOCUMENT_ROOT{relpath}wp-config.php</code>','kap') ) );
	}

	function fill_field( $args ){
		$id   = $args['id'];
		$desc = $args['desc'] ? '<p class="description">'. $args['desc'] .'</p>' : '';
		$name = self::OPT_NAME ."[$id]";
		$val  = $this->opt[ $id ];

		if( $id == 'who_count' ){
			$options = array();
			foreach(
				array('all'=>__('All','kap'), 'not_logged_users'=>__('Only not logged users','kap'), 'logged_users'=>__('Only logged users','kap'), 'not_administrators'=>__('All, except administrators','kap') )
				as $kk=>$vv ){
				$options[] = '<option value="'. $kk .'" '. selected($val, $kk, 0) .'>'. $vv .'</option>';
			}

			echo '<select name="'. $name .'">'. implode("\n", $options) .'</select>'. $desc;
		}
		elseif( $id == 'hold_sec' ){
			echo '<input type="number" min="0" name="'. $name .'" value="'. esc_attr( $val ) .'">'. $desc;
		}
		elseif( $id == 'wp_config_relpath' ){
			echo '<input type="text" name="'. $name .'" value="'. esc_attr( $val ) .'">'. $desc;
		}

	}

	## sanitize data
	function option_sanitize_cb( $options ){
		foreach( $options as $name => & $val ){
			$val = sanitize_text_field( $val );
		}

		return $options;
	}

}

