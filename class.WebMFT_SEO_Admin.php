<?php

class WebMFT_SEO_Admin extends WebMFT_SEO {

    // (<img).*src="(.*)"\s(.*>)

	function __construct(){
		parent::__construct();
        $this->options = ($opt = get_option($this->option_name))? $opt : $this->def_opt();

		add_action('admin_menu', array(&$this, 'add_options_page'));
		add_action('admin_init', array(&$this, 'register_webmft_settings') );
        add_action ('save_post', array(&$this, 'guid_write'), 100);

        add_filter('plugin_action_links_'. MFT_BASE, array( &$this, 'settings_link' ));
    }

    function guid_write( $id ){
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE  ) return false; // если это автосохранение

        global $wpdb;

        if( $id = (int) $id )
            $wpdb->query("UPDATE $wpdb->posts SET guid='". get_permalink($id) ."' WHERE ID=$id LIMIT 1");
    }

	// Settings page link in plugins table ---
	function settings_link($links){
		array_unshift( $links, '<a href="'.admin_url('admin.php?page=webmft_seo').'">'.__('Settings', 'webmft') .'</a>' );
		return $links;
	}

	function add_options_page(){
		add_menu_page( 'WebMFT: SEO', 'WebMFT: SEO', 'manage_options', 'webmft_seo', array(&$this, 'options_page_output'), 'dashicons-shield', 6);
	}

	function options_page_output(){
		?>
		<div class="wrap">
			<h1>WebMFT: SEO useful</h1>
            <h3>Настройки</h3>

            <form method="post" action="options.php" class="js-webmft-form">
				<?php
				settings_fields('webmft_settings');  // скрытые защитные поля
				?>

                <h2 class="nav-tab-wrapper webmft-tab-wrapper js-tab-wrapper">
                    <a class="nav-tab nav-tab-active" id="postview-tab" href="#top#postview">Post viewes</a>
                    <a class="nav-tab" id="postmeta-tab" href="#top#postmeta">Post Meta & Title</a>
                    <a class="nav-tab" id="noindex-tab" href="#top#noindex">Noindex Settings</a>
                    <a class="nav-tab" id="analytics-tab" href="#top#analytics">Analytic`s</a>
                    <a class="nav-tab" id="hidelinks-tab" href="#top#hidelinks">GoTo</a>
                </h2>

                <div id="postview" class="wp-webmft-tab js-tab-item active">
                    <h3>Post viewes</h3>
                    <div class="form-group">
	                    <label for="postview_is">
	                        <?php $this->display_checkbox('postview_is') ?>
	                        	Gloabal Postview is active?
	                    </label>
                	</div>
                    <div class="form-group">
	                    <label for="postview_who_count">
                        	Whose visit count? <sup class="webmft-recommend">Рекомендовано</sup>
	                    </label>
                        <?php
						$tmpA = array('all'=>__('All','webmft'),
							'not_logged_users'=>__('Only not logged users','webmft'),
							'logged_users'=>__('Only logged users','webmft'),
							'not_administrators'=>__('All, except administrators','webmft'));
                        $this->display_select('postview_who_count', $tmpA);
                        ?>
                    </div>
                    <div class="form-group">
                        <label for="postview_hold_sec">Delay in seconds</label>
                        <?php $this->display_input_number('postview_hold_sec', 1, 1, 10) ?>
                        <p class="form-text">How many seconds to delay and then count visit?</p>
                    </div>
                </div>
                <div id="postmeta" class="wp-webmft-tab js-tab-item">
                    <h3>Post Meta & Title</h3>
                    <div class="form-group">
                        <label for="postmeta_is">
                            <?php $this->display_checkbox('postmeta_is') ?>
                                Gloabal Postmeta is active?
                        </label>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                        <h4>Categories Meta</h4>
                        <?
                        $myterms = get_terms('category', 'orderby=count&hide_empty=0');
                        foreach ($myterms as $key => $value) {
                            $catTitle = 'category_'.$value->slug.'_title';
                            $catDescr = 'category_'.$value->slug.'_description';
                            echo '<h4>'.$value->name.'</h4>';
                            echo '<div class="form-group"><label for="'.$catTitle.'">Title for '.$value->name.'</label>';
                            $this->display_input_text($catTitle);
                            echo '</div>';

                            echo '<div class="form-group"><label for="'.$catDescr.'">Description for '.$value->name.'</label>';
                            $this->display_input_text($catDescr);
                            echo '</div>';
                            echo '<hr>';
                        }
                        ?>
                        </div>
                        <div class="col-md-5">
                            <h4>Meta & Title for Front page</h4>
                            <div class="form-group">
                                <label for="postmeta_front_title">Title</label>
                                <?php $this->display_input_text('postmeta_front_title') ?>
                            </div>
                            <div class="form-group">
                                <label for="postmeta_front_description">Description</label>
                                <?php $this->display_input_text('postmeta_front_description') ?>
                            </div>
                            <div class="form-group">
                                <label for="postmeta_front_keywords">Keywords</label>
                                <?php $this->display_input_text('postmeta_front_keywords') ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="noindex" class="wp-webmft-tab js-tab-item">
                    <h3>Noindex Settings</h3>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="noindex_tax">
                                    <?php $this->display_checkbox('noindex_tax') ?>
                                    Use noindex for Tax?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_category">
                                    <?php $this->display_checkbox('noindex_category') ?>
                                    Use noindex for Categories?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_archive_date">
                                    <?php $this->display_checkbox('noindex_archive_date') ?>
                                    Use noindex for Date Archives?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_archive_author">
                                    <?php $this->display_checkbox('noindex_archive_author') ?>
                                    Use noindex for Author Archives?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_tags">
                                    <?php $this->display_checkbox('noindex_tags') ?>
                                    Use noindex for Tag Archives?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_search">
                                    <?php $this->display_checkbox('noindex_search') ?>
                                    Use noindex for the Search page?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_404">
                                    <?php $this->display_checkbox('noindex_404') ?>
                                    Use noindex for the 404 page?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="noindex_paginated">
                                    <?php $this->display_checkbox('noindex_paginated') ?>
                                    Use noindex for paginated pages/posts?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="nofollow_paginated">
                                    <?php $this->display_checkbox('nofollow_paginated') ?>
                                    Use nofollow for paginated pages/posts?
                                </label>
                            </div>
                        </div>
                        <div class="col-md-5">
                        </div>
                    </div>
                </div>
                <div id="analytics" class="wp-webmft-tab js-tab-item">
                    <h3>Analytic`s</h3>
                    <div class="row">
                        <div class="col-md-5">
                            <h4>Yandex Metrica</h4>
                            <div class="form-group">
                                <label for="analytics_yandex_is">
                                    <?php $this->display_checkbox('analytics_yandex_is') ?>
                                        Gloabal Yandex Metrica is active?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="analytics_yandex_id">Yandex Metrica ID</label>
                                <?php $this->display_input_text('analytics_yandex_id') ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h4>PIWIK Metrica</h4>
                            <div class="form-group">
                                <label for="analytics_piwik_is">
                                    <?php $this->display_checkbox('analytics_piwik_is') ?>
                                        PIWIK Metrica is active?
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="analytics_piwik_id">Local or Gloabal PIWIK ID</label>
                                <?php $this->display_input_text('analytics_piwik_id') ?>
                            </div>
                            <div class="form-group">
                                <label for="analytics_piwik_url_track">URL track</label>
                                <?php $this->display_input_text('analytics_piwik_url_track') ?>
                                <p class="form-text">Example: //site.com/piwik/</p>
                            </div>
                        </div>
                    </div>
                </div>
				<div id="hidelinks" class="wp-webmft-tab js-tab-item">
                    <div class="row">
                        <div class="col-md-5">
                            <h4>Setup Links</h4>
                            <div class="form-group">
                                <label for="goto_provider_def">Link Default</label>
                                <?php $this->display_input_text('goto_provider_def') ?>
                            </div>
                            <div class="form-group">
                                <label for="goto_provider_1">Link #1</label>
                                <?php $this->display_input_text('goto_provider_1') ?>
                            </div>
                            <div class="form-group">
                                <label for="goto_provider_2">Link #2</label>
                                <?php $this->display_input_text('goto_provider_2') ?>
                            </div>
                            <div class="form-group">
                                <label for="goto_provider_3">Link #3</label>
                                <?php $this->display_input_text('goto_provider_3') ?>
                            </div>
                            <div class="form-group">
                                <label for="goto_provider_4">Link #4</label>
                                <?php $this->display_input_text('goto_provider_4') ?>
                            </div>
                            <div class="form-group">
                                <label for="goto_provider_5">Link #5</label>
                                <?php $this->display_input_text('goto_provider_5') ?>
                            </div>
                            <div class="form-group">
                                <label for="goto_provider_6">Link #6</label>
                                <?php $this->display_input_text('goto_provider_6') ?>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <h4>Setup GoTo</h4>
                            <div class="form-group">
                                <label for="goto_setup_link">Router Link</label>
                                <?php $this->display_input_text('goto_setup_link') ?>
                                <p class="form-text">Default var 'goto' => Example: '/goto/1/'</p>
                                <?php
                                if ('' == $this->options['goto_setup_link']) $goto_setup_link = 'goto';
                                else $goto_setup_link = $this->options['goto_setup_link'];
                                ?>
                                <p class="form-text">Now your GoTo Links is: '/<?php echo $goto_setup_link;?>/1/'</p>
                            </div>
                        </div>
                    </div>
				</div>
				<?php
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

    /**
     * Display option checkbox
     *
     * @param string $name
     */
    public function display_checkbox( $name ) {
        $checked = '';
        if (isset($this->options[$name]) && $this->options[$name] == 'on') $checked = ' checked';
        $string = '<input name="' . $this->option_name . '[' . $name . ']" type="checkbox" id="' . $name . '" value="on"'. $checked .'>';
        echo $string;
    }

    /**
     * Display input text field
     *
     * @param string $name
     */
    public function display_input_text( $name ) {
        $value = '';
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];
        $string = '<input name="' . $this->option_name . '[' . $name . ']" type="text" id="' . $name . '" value="'. $value .'"" class="form-control">';
        echo $string;
    }

    /**
     * Display input number field
     *
     * @param $name
     * @param $step
     * @param $min
     * @param $max
     */
    public function display_input_number( $name , $step = '', $min = '', $max = '' ) {
        $value = '';
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];
        $string  = '<input name="' . $this->option_name . '[' . $name . ']" type="number" ';
        if (!empty($step)) $string .= 'step="'. $step .'" ';
        if (!empty($min) || $min === 0)  $string .= 'min="'. $min .'"  ';
        if (!empty($max))  $string .= 'max="'. $max .'" ';
        $string .= 'id="' . $name . '" value="'. $value .'"" class="form-control">';
        echo $string;
    }

    /**
     * Display select
     *
     * @param string $name
     * @param array $values
     */
    public function display_select( $name , $values ) {
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];
        $string  = '<select class="form-control" name="' . $this->option_name . '[' . $name . ']" id="' . $name . '">';

        if (is_array( $values )) {
            foreach ($values as $key => $value) {
                $selected = '';
                if (isset($this->options[$name]) && $this->options[$name] == $key) $selected = ' selected';

                $string .= '<option value="' . $key . '"'. $selected .'>' . $value . '</option>';
            }
        }

        $string .= '</select>';
        echo $string;
    }

    /**
     * Register settings
     */
    public function register_webmft_settings() {
        register_setting( 'webmft_settings', $this->option_name, array( $this, 'sanitize_webmft_options' ) );
    }

    public function sanitize_webmft_options( $options ) {
        return $options;
    }
}
?>
