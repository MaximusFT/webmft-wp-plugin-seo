<?php

class WebMFT_SEO_Admin extends WebMFT_SEO {

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

	function settings_link($links){
		array_unshift( $links, '<a href="'.admin_url('admin.php?page=webmft_seo').'">'.__('Settings', 'webmft') .'</a>' );
		return $links;
	}

	function add_options_page(){
		add_menu_page( 'WebMFT: SEO', 'WebMFT: SEO', 'manage_options', 'webmft_seo', array(&$this, 'options_page_output'), 'dashicons-shield', 6);
        add_submenu_page( 'webmft_seo', 'Robots & htaccess', 'Robots & htaccess', 'manage_options', 'webmft_seo_files', array( $this, 'webmft_seo_files' ));
        add_submenu_page( 'webmft_seo', 'Список изменений', 'Список изменений', 'manage_options', 'webmft_seo_changelog', array( $this, 'webmft_seo_changelog' ));
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
                    <a class="nav-tab" id="noindex-tab" href="#top#noindex">Noindex</a>
                    <a class="nav-tab" id="analytics-tab" href="#top#analytics">Analytic`s</a>
                    <a class="nav-tab" id="hidelinks-tab" href="#top#hidelinks">GoTo</a>
                    <a class="nav-tab" id="extlinks-tab" href="#top#extlinks">Ext Links</a>
                    <a class="nav-tab" id="extposts-tab" href="#top#nupopposts">Widget on Front</a>
                    <a class="nav-tab" id="extidpost-tab" href="#top#nupopidposts">Id Posts</a>
                </h2>

                <?php
                submit_button();
                ?>

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
				<div id="extlinks" class="wp-webmft-tab js-tab-item">
                    <div class="row">
                        <div class="col-md-5">
                            <h4>Setup Links</h4>
                            <div class="form-group">
                                <label for="extlinks_is">
                                    <?php $this->display_checkbox('extlinks_is') ?>
                                        External Links is active?
                                </label>

                            </div>
                            <h4>Включение рейтинга постов</h4>
                            <div class="form-group">
                                <label for="extposts_vuvod_reting">
                                    <?php $this->display_checkbox('extposts_vuvod_reting') ?>
                                    Включить/Выключить вывод рейтинга

                                </label><br><stong class="vuvod_reting">Работает только если активна функция "External Links is active?"</stong>
                            </div>
                            <div class="col-md-12">
                                <hr><h4>Настройка цвета кнопки которая выводиться после текста поста</h4>
                            </div>
                             <div class="col-md-12">
                                    <div class="col-md-2">
                                        <h5>Цвет кнопки</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_play_for_button', 'color') ?>
                                         </div>
                                    </div>
                                    <div class="col-md-2">
                                    <h5>Цвет текста</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_play_for_button_text', 'color') ?>
                                         </div>
                                    </div>
                                    <div class="col-md-3">
                                        <h5>Цвет кнопки:hover</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_play_for_button_hover', 'color') ?>
                                         </div>
                                    </div>
                                    <div class="col-md-3">
                                    <h5>Цвет текста:hover</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_play_for_button_text_hover', 'color') ?>
                                         </div>
                                    </div>
                                </div> 
                        </div>
                        <div class="col-md-5">
                            <h4>Setup CSS</h4>
                            <p class="form-text">You need customize .btn and .btn-success</p>
                            <div class="form-group">
                                <label for="extlinks_custom_css">Custom CSS</label>
                                <?php $this->display_textarea('extlinks_custom_css') ?>
                            </div>
                        </div>
                    </div>
				</div>
                <div id="extposts" class="wp-webmft-tab js-tab-item">
                    <div class="row">
                    <div class="col-md-3">
                            <h4>Переключить язик рейтинга на русский</h4>
                            <div class="form-group">
                                <label for="extposts_yazik_retings">
                                    <?php $this->display_checkbox('extposts_yazik_retings') ?>
                                    по умолчанию английский
                                    <p>(Рейтинг: N из 5) N = просмотров</p>
                                    <p>(Rating: N out of 5) N = viewss</p>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <h4>Переключить язик мета-тегах на русский</h4>
                            <div class="form-group">
                                <label for="extposts_yazik_meta_retings">
                                    <?php $this->display_checkbox('extposts_yazik_meta_retings') ?>
                                    по умолчанию английский
                                </label>
                                <p>- page N / - страница N</p>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <h4>Управление выводом блоков</h4>
                            <div class="form-group">
                                <label for="extposts_before_text">
                                    <?php $this->display_checkbox('extposts_before_text') ?> 
                                    Если функция не активна то вывод будет производиться после текста
                                </label>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <h4>C наибольшим количеством просмотров</h4>
                            <div class="form-group">
                                <label for="extposts_casino_reviews">
                                    <?php $this->display_checkbox('extposts_casino_reviews') ?>
                                        Блок "Casino Reviews"
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_before_casino_reviews">Заголовок перед блоком</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_before_casino_reviews') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_after_casino_reviews">Заголовок после блока</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_after_casino_reviews') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_kolichestvo_postov_casino_reviews">Количество выводимых постов</label>
                                <?php $this->display_input_text('extposts_kolichestvo_postov_casino_reviews') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_id_postov_one_one">ID категорий для которых нужно вывести посты</label>
                                <?php $this->display_input_text('extposts_id_postov_one_one') ?>
                                ID записывать через запятую
                            </div>
                        </div>


                        <div class="col-md-2">
                            <h4>C наибольшим количеством просмотров</h4>
                            <div class="form-group">
                                <label for="extposts_featured_slot_machines">
                                    <?php $this->display_checkbox('extposts_featured_slot_machines') ?>
                                        Блок "Featured Slot Machines"
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_before_featured">Заголовок перед блоком</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_before_featured') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_after_featured">Заголовок после блока</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_after_featured') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_kolichestvo_postov">Количество выводимых постов</label>
                                <?php $this->display_input_text('extposts_kolichestvo_postov') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_id_postov_one">ID категорий для которых нужно вывести посты</label>
                                <?php $this->display_input_text('extposts_id_postov_one') ?>
                                ID записывать через запятую
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4>Cамые новые</h4>
                            <div class="form-group">
                                <label for="extposts_all_slots">
                                    <?php $this->display_checkbox('extposts_all_slots') ?>
                                        Блок "All Slots Reviews"
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_all_slots">Заголовок перед блоком</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_all_slots') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_all_slots_after">Заголовок после блока</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_all_slots_after') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_kolichestvo_postov_two_blok">Количество выводимых постов</label>
                                <?php $this->display_input_text('extposts_kolichestvo_postov_two_blok') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_id_categories_2_blok">ID категорий для которых нужно вывести посты</label>
                                <?php $this->display_input_text('extposts_id_categories_2_blok') ?>
                                ID записывать через запятую
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h4>Cамые новые</h4>
                            <div class="form-group">
                                <label for="extposts_gamblink_news">
                                    <?php $this->display_checkbox('extposts_gamblink_news') ?>
                                        Блок "Gambling news"
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_gamblink_news">Заголовок перед блоком</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_gamblink_news') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_nazanie_h2_gamblink_news_after">Заголовок после блока</label>
                                <?php $this->display_input_text('extposts_nazanie_h2_gamblink_news_after') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_kolichestvo_postov_news_blok">Количество выводимых постов</label>
                                <?php $this->display_input_text('extposts_kolichestvo_postov_news_blok') ?>
                            </div>
                            <div class="form-group">
                                <label for="extposts_id_categories_3_blok">ID категорий для которых нужно вывести посты</label>
                                <?php $this->display_input_text('extposts_id_categories_3_blok') ?>
                                ID записывать через запятую
                            </div>
                        </div>
                             <div class="col-md-10">
                                <h4>Categories Name</h4>
                                <?
                                $myterms = get_terms('category', 'orderby=count&hide_empty=0');
                                foreach ($myterms as $key => $value) {
                                    $catNameBefore = 'category_'.$value->slug.'_name_before';
                                    $catNameAfter = 'category_'.$value->slug.'_name_after';
                                    $catVklname = 'category_'.$value->slug.'_catVklname';
                                    $catVklPaginasia = 'category_'.$value->slug.'_catVklPaginasia';
                                    $catVklPaginasiaMeta = 'category_'.$value->slug.'_catVklPaginasiaMeta';
                                    echo '<h4>'.$value->name.'</h4>';
                                    echo '<div class="form-group"><label for="'.$catNameBefore.'">Текст до</label>';
                                    $this->display_input_text($catNameBefore);
                                    echo '</div>';

                                    echo '<div class="form-group"><label for="'.$catNameAfter.'">Текст после</label>';
                                    $this->display_input_text($catNameAfter);
                                    echo '</div>';

                                    echo '<div class="form-group"><label for="'.$catVklname.'">';
                                    $this->display_checkbox($catVklname);
                                    echo ' Включение изменения названий</label></div>';

                                    echo '<div class="form-group"><label for="'.$catVklPaginasia.'">';
                                    $this->display_checkbox($catVklPaginasia);
                                    echo ' Включить добавление приставки - page</label></div>';

                                    echo '<div class="form-group"><label for="'.$catVklPaginasiaMeta.'">';
                                    $this->display_checkbox($catVklPaginasiaMeta);
                                    echo ' Включить добавление приставки - page в мета-теги</label></div>';
                                    echo '<hr>';
                                }
                                ?>
                        </div>
                    </div>
                </div>

                <div id="extidpost" class="wp-webmft-tab js-tab-item">
                    <div class="row">
                        <div class="col-md-10">
                            <h2>Вывод постов по ID</h2>
                            <div class="form-group">
                                <label for="extposts_ids_posts">
                                    <?php $this->display_checkbox('extposts_ids_posts') ?>
                                    Активировать вывод блоков
                                </label>
                            </div>
                        </div>    
                        <div class="col-md-3">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/12/one_block.jpg" class="img_one_admin_block">
                        </div>
                        <div class="col-md-3">    
                                 <div class="form-group">
                                    <label for="extposts_ids_posts_one_style">
                                         <?php $this->display_checkbox('extposts_ids_posts_one_style') ?>
                                         Включить блок
                                    </label>
                                </div>
                                <div class="form-group">
                                <?php $this->display_input_text('extposts_id_nyhnovo_posta_ones') ?>
                                <label for="extposts_id_nyhnovo_posta_ones">ID постов которые нужно вывести, записывать через запятую</label>
                                </div>
                            <div class="col-md-5">    
                                <div class="form-group">
                                <?php $this->display_input_text('zamena_visit') ?>
                                <label for="zamena_visit">Заменяет слово "Visit"</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <?php $this->display_input_text('zamena_certified') ?>
                                    <label for="zamena_certified">Заменяет слово "Certified"</label>
                                </div>
                            </div>
                                 <div class="form-group">
                                    <?php $this->display_input_text('zamena_play_now') ?>
                                    <label for="zamena_play_now">Заменяет "Play now" на другое слово.</label>
                                </div>
                            <div class="col-md-5">    
                                <div class="form-group">
                                <?php $this->display_input_text('kolichestvo_front') ?>
                                <label for="kolichestvo_front">Количество символов спереди</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <?php $this->display_input_text('kolichestvo_back') ?>
                                    <label for="kolichestvo_back">Количество символов сзади</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="col-md-2">
                                <h5>Выбор цвета кнопки</h5>
                                <div class="form-group">
                                <?php $this->display_input_text('goto_dasdasd_1', 'color') ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <h5>Цвет при наведении</h5>
                                    <div class="form-group">
                                    <?php $this->display_input_text('goto_dasdasd_1_hover', 'color') ?>
                                    </div>
                            </div>
                       </div> 
                        <div class="col-md-5"><hr>
                            <div class="col-md-2">
                                 <h5>Цвета Visit</h5>
                                <div class="form-group">
                                <?php $this->display_input_text('goto_dasdasd_visit', 'color') ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <h5>Цвета Certified</h5>
                                    <div class="form-group">
                                    <?php $this->display_input_text('goto_dasdasd_certified', 'color') ?>
                                    </div>
                            </div>
                       </div>
                        <div class="col-md-10">
                        <hr>
                        </div>
                         <div class="col-md-3">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/11/block_two.jpg" class="img_one_admin_block">
                        </div>
                        <div class="col-md-3">    
                             <div class="form-group">
                                <label for="extposts_ids_posts_two_style">
                                    <?php $this->display_checkbox('extposts_ids_posts_two_style') ?>
                                    Включить блок
                                </label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('id_nyhnovo_posta_style_2') ?>
                            <label for="id_nyhnovo_posta_style_2">ID постов которые нужно вывести, записывать через запятую</label>
                            </div>
                             <div class="col-md-5"> 
                                <div class="form-group">
                                    <?php $this->display_input_text('zamena_play_block_2') ?>
                                    <label for="zamena_play_block_2">Заменяет слово "Play"</label>
                                </div>
                            </div>
                            <div class="col-md-5"> 
                                <div class="form-group">
                                    <?php $this->display_input_text('zamena_review_block_2') ?>
                                    <label for="zamena_review_block_2">Заменяет слово "Review"</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5"> 
                            <div class="col-md-2"> 
                                <h5>Цвет кнопки</h5>
                                <div class="form-group">
                                    <?php $this->display_input_text('goto_background_play_button', 'color') ?>
                                </div>
                                <h5>Цвет текста</h5>
                                <div class="form-group">
                                    <?php $this->display_input_text('goto_play_button', 'color') ?>
                                </div>
                            </div>
                             <div class="col-md-2"> 
                                <h5>Цвет кнопки:hover</h5>
                                <div class="form-group">
                                    <?php $this->display_input_text('goto_background_play_button_hover', 'color') ?>
                                </div>
                                 <h5>Цвет "Review"</h5>
                                <div class="form-group">
                                    <?php $this->display_input_text('goto_background_review', 'color') ?>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-10">
                         <hr>
                        </div>
                         <div class="col-md-3">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/11/block_three.jpg" style="width: 150px;padding-left: 131px;" class="img_one_admin_block">
                        </div>
                        <div class="col-md-3">    
                             <div class="form-group">
                                <label for="extposts_ids_posts_three_style">
                                    <?php $this->display_checkbox('extposts_ids_posts_three_style') ?>
                                    Включить блок
                                </label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('id_nyhnovo_posta_style_3') ?>
                            <label for="id_nyhnovo_posta_style_3">ID постов которые нужно вывести, записывать через запятую</label>
                            </div>
                            
                        </div>
                        <div class="col-md-5">  
                                 <div class="col-md-2">  
                                     <h5>Цвет кнопки</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_btn_orange_button', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                    <h5>Цвет кнопки:hover</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_btn_orange_button_hover', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет текста</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('color_btn_orange_button', 'color') ?>
                                         </div>
                                </div>
                                 <div class="col-md-2">  
                                     <h5>Цвет border</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_btn_color_border', 'color') ?>
                                         </div>
                                </div>
                        </div>
                            
                        <div class="col-md-10">
                        <hr>
                        </div>
                         <div class="col-md-3">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/11/block_four.jpg"  class="img_one_admin_block">
                        </div>
                        <div class="col-md-3">    
                             <div class="form-group">
                                <label for="extposts_ids_posts_four_style">
                                    <?php $this->display_checkbox('extposts_ids_posts_four_style') ?>
                                    Включить блок
                                </label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('id_nyhnovo_posta_style_4') ?>
                            <label for="id_nyhnovo_posta_style_4">ID постов которые нужно вывести, записывать через запятую</label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('zamena_review_block_4') ?>
                            <label for="zamena_review_block_4">Заменяет слово "Review"</label>
                            </div>
                        </div>
                        <div class="col-md-5">  
                                 <div class="col-md-2">  
                                     <h5>Цвет кнопки</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_mrg_orange_button', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                    <h5>Цвет кнопки:hover</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_mrg_orange_button_hover', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет текста</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('color_mrg_orange_button', 'color') ?>
                                         </div>
                                </div>
                                 <div class="col-md-2">  
                                     <h5>Цвет фона</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_mrg_color_border', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет текста:hover</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_mrg_color_hover_text', 'color') ?>
                                         </div>
                                </div>
                        </div>
                        <div class="col-md-10">
                        <hr>
                        </div>
                         <div class="col-md-3">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/11/block_sixten.jpg" style="width: 231px;padding-left: 75px;" class="img_one_admin_block">
                        </div>
                        <div class="col-md-3">    
                             <div class="form-group">
                                <label for="extposts_ids_posts_five_style">
                                    <?php $this->display_checkbox('extposts_ids_posts_five_style') ?>
                                    Включить блок
                                </label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('id_nyhnovo_posta_style_5') ?>
                            <label for="id_nyhnovo_posta_style_5">ID постов которые нужно вывести, записывать через запятую</label>
                            </div>
                        </div>
                        <div class="col-md-5">  
                                 <div class="col-md-2">  
                                     <h5>Цвет кнопки</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_ben_orange_button', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет текста</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('color_ben_orange_button', 'color') ?>
                                         </div>
                                </div>
                                 <div class="col-md-2">  
                                     <h5>Цвет фона</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('goto_ben_color_background', 'color') ?>
                                         </div>
                                </div>
                            </div>
                        <div class="col-md-10">
                            <hr>
                                <p>нужно доработать http://verybitcoinslotsgambling.xyz/</p>
                        </div>
                        <div class="col-md-10">
                                <div class="col-md-4">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/12/style_6.jpg" class="img_one_admin_block">
                            </div>
                            <div class="col-md-3">    
                             <div class="form-group">
                                <label for="extposts_ids_posts_six_style">
                                    <?php $this->display_checkbox('extposts_ids_posts_six_style') ?>
                                    Включить блок
                                </label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('id_nyhnovo_posta_style_6') ?>
                            <label for="id_nyhnovo_posta_style_6">ID постов которые нужно вывести, записывать через запятую</label>
                            </div>
                        </div>
                        </div>
                        <div class="col-md-10">
                        <hr>
                        </div>
                         <div class="col-md-10">
                                <div class="col-md-4">
                            <img  src="http://best7casino.us/wp-content/uploads/2016/12/style_7.jpg" class="img_one_admin_block">
                            </div>
                            <div class="col-md-3">    
                             <div class="form-group">
                                <label for="extposts_ids_posts_seven_style">
                                    <?php $this->display_checkbox('extposts_ids_posts_seven_style') ?>
                                    Включить блок
                                </label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('id_nyhnovo_posta_style_7') ?>
                            <label for="id_nyhnovo_posta_style_7">ID постов которые нужно вывести, записывать через запятую</label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('kolichestvo_simvolo') ?>
                            <label for="kolichestvo_simvolo">Количество символов</label>
                            </div>
                            <div class="form-group">
                            <?php $this->display_input_text('zamena_review_style_6') ?>
                            <label for="zamena_review_style_6">Замена "Review"</label>
                            </div>
                        </div>
                            <div class="col-md-3">  
                                <div class="col-md-2">  
                                     <h5>Цвет фона</h5>
                                         <div class="form-group">
                                             <?php $this->display_input_text('button_my_my_background', 'color') ?>
                                         </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет фона:hover</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('button_my_my_background_hover', 'color') ?>
                                        </div>
                                </div>
                                 <div class="col-md-2">  
                                     <h5>Цвет текста review</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('button_my_my_button_review', 'color') ?>
                                        </div>
                                </div>
                                 <div class="col-md-2">  
                                     <h5>Цвет текста review:hover</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('button_my_my_button_review_hover', 'color') ?>
                                        </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет кнопки</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('goto_button_my_my_button', 'color') ?>
                                        </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет кнопки:hover</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('goto_button_my_my_button_hover', 'color') ?>
                                        </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет текста кнопки</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('goto_button_my_my_text', 'color') ?>
                                        </div>
                                </div>
                                <div class="col-md-2">  
                                     <h5>Цвет текста кнопки:hover</h5>
                                        <div class="form-group">
                                            <?php $this->display_input_text('goto_button_my_my_text_hover', 'color') ?>
                                        </div>
                                </div>
                            </div>
                        </div>
                   </div>
                </div>

                <div id="extidpostsdvsdvv" class="wp-webmft-tab js-tab-item">
                </div>
			</form>
		</div>
    <?php
    }

    function webmft_seo_files(){
    ?>
        <div class="wrap">
            <h1>WebMFT: SEO useful</h1>
            <h3>Настройки</h3>

            <form method="post" action="options.php" class="js-webmft-form">
                <?php
                settings_fields('webmft_settings');  // скрытые защитные поля
                ?>

                <h2 class="nav-tab-wrapper webmft-tab-wrapper js-tab-wrapper">
                    <a class="nav-tab nav-tab-active" id="robots-tab" href="#top#robots">Edit robots.txt</a>
                    <a class="nav-tab" id="htaccess-tab" href="#top#htaccess">htaccess</a>
                </h2>
                <?php
                submit_button();
                ?>

                <div id="robots" class="wp-webmft-tab js-tab-item active">
                    <div class="row">
                        <div class="col-md-10">
                            <p><strong>Плагин WebMFT: SEO useful создает виртуальный robots.txt, для поискового робота разницы между виртуальным и физическим файлом нет</strong></p>
                            <label for="right_robots_txt">
                                <?php $this->display_checkbox('right_robots_txt') ?>
                                Создает robots.txt
                            </label>
                        </div>

                        <div class="col-md-5">
                            <label for="vuvod_block_recomendacia">
                                <br><?php $this->display_checkbox('vuvod_block_recomendacia') ?>
                                    Добавляет рекомендации плагина WebMFT: SEO useful.<br>
                                    <strong>Если вы перед этим скопировали robots.txt согласно правилам то плагин добавит рекомендации в конец, после вашего текста и вы сможете сами решить что вам нужно, удалив не нужное.</strong>
                            </label>
                        </div>
                        <div class="col-md-5">
                            <label for="delete_robots_txt">
                                <?php $this->display_checkbox('delete_robots_txt') ?>
                                    Удалить файл robots.txt с хостинга<br>
                                    <strong>Внимание!<br> Рекомендуеться снять галку сразу после удаления robots.txt<br>В противном случаее плагин будет удалять все файл robots.txt при попытке добавления их на хостинг</strong>
                            </label>
                        </div>

                        <div class="col-md-10"><hr></div>

                        <div class="col-md-5">
                            <script>$('#robots_txt_text').val('');</script>
                            <p>Автоматически создает идеальный robots.txt</p>
                            <?php $this->display_textarea_robots('robots_txt_text') ?><br>
                        </div>
                    <?php if ( file_exists(ABSPATH . 'robots.txt') ) { ?>
                        <div class="col-md-5">
                            <p class="warning_robots_txt"><strong>Внимание! Обнаружен файл robots.txt</strong></p>
                            <p><strong>Если вы читаете это сообщение - значит плагин ещё не создал свой robots.txt, для того что бы плагин заработал нужно удалить (при желании скопировать) ваш robots.txt который храниться на сервере.<br>С помощью плагина WebMFT: SEO useful вы можете сделать это не выходя из админки.</strong></p>
                            <p><strong> Для этого выполните следующее:</strong></p>
                                <ul style="list-style-type: disc;margin-left: 55px;">
                                    <li>Нажмите на кнопку "Скопировать содержимое файла в форму рядом" и нажмите "сохранить", плагин сам скопирует файл</li>
                                    <li>Нужмите кнопку удалить файл robots.txt и нажмите "сохранить"</li>
                                </ul>
                                <?php $this->display_checkbox('vuvod_block_bes_robot'); ?>
                                <label for="vuvod_block_bes_robot">Скопировать содержимое файла в форму рядом</label>
                                <p><strong>Ваш robots.txt</strong></p>
                                <textarea class="textaria_OK_robots_txt"> <?php print_r(file_get_contents(ABSPATH . 'robots.txt')); ?> </textarea>
                        </div>
                    <?php } ?>
                    </div>
                </div>

                <div id="htaccess" class="wp-webmft-tab js-tab-item active">
                    <div class="row">
                        <div class="col-md-10">
                            <?php//  require 'inc/require/htaccess_php.php'; ?>
                            <div class="wrap">
                                <h2 class="wphe-titles">WP Htaccess Editor</h2>
                                <div class="postbox wphe-box">
                                    <?
                                    if ($this->options['ht_content']) {
                                        $WPHE_new_content = $this->options['ht_content'];
                                        $WPHE_orig_path = ABSPATH.'.htaccess';    
                                        $WPHE_new_content = trim($WPHE_new_content);
                                        $WPHE_new_content = str_replace('\\\\', '\\', $WPHE_new_content);
                                        $WPHE_new_content = str_replace('\"', '"', $WPHE_new_content);
                                        $WPHE_write_success = file_put_contents($WPHE_orig_path, $WPHE_new_content, LOCK_EX);  
                                    }
                                    $this->options['ht_content'] = file_get_contents(ABSPATH . '.htaccess', false, NULL);
                                    ?>
                                    <h3 class="wphe-title">Content of the Htaccess file</h3>
                                    <?php $this->display_textarea('ht_content', 'wphe-textarea', true) ?>
                                </div>
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

    function webmft_seo_changelog(){
    ?>
        <div class="WEBMFTwrap">
            <h1>Список изменений</h1>
            <div class="WEBMFTelog">
                <div class="WEBMFT_item">
                    <strong class="WEBMFT_version">1.8.7</strong>
                    <ul>
                        <li>Обнова ещё не вышла</li>
                        <li>Добавлено: возможность создания правильного на наш взгляд robots.txt</li>
                        <li>Добавлено: возможность редактирование robots.txt с админки сайта</li>
                        <li>Добавлено: возможность вывода блоков по заданным ID на главной странице с разными стилями</li>
                        <li>Добавлено: возможность менять цвет кнопки которая выводиться после тела поста</li>
                        <li>Добавлено: возможность копирования содержимого файла robots.txt и удаления его с сервера не выходя с админки</li>
                        <li><strong>Доработать: очистку полей после действий</strong></li>
                        <li><strong>Доработать: возможность редактировать файл <a href="http://wp-kama.ru/hook/mod_rewrite_rules">.htaccess</a> c админки</strong></li>
                        <li><strong>Доработать: изменения <a href="http://www.websphererus.com/others/hide-url-wordpress-console">пути к админке через .htaccess</a> c админки</strong></li>
                        <li><strong>Доработать: <a href="http://isif-life.ru/blogovedenie/skript-yvelicheniya-izobrageniya-wordpress-bez-plaginov.html">внедрение скрипта</a>, при клике на фотку она увеличивалась</strong></li>
                        <li><strong>Доработать: заменить target='_blank' на JS\Qwery скрипт для увеличения скорости</strong></li>
                        <li><strong>Доработать: вывод блока №6</strong></li>
                        <li><strong>Доработать: автоматическую заливку на хост фоток и файлов плагина для скрытие путей плагина</strong></li>
                        <li><strong>Доработать: возможность включение <a href="http://wp-kama.ru/id_541/samyie-hlebnyie-kroshki-breabcrumbs-dlya-wordpress.html">хлебных крошек</a></strong></li>
                        <li><strong>Доработать: добавить функцию Invisible Captcha <a href="http://isif-life.ru/web/plagin-invisible-captcha-nevidimaya-kapcha-dlya-wordpress.html">из этого сайта</a></strong></li>
                        <li><strong>Доработать: ограничение на количество входов в админку</strong></li>
                        <li><strong>Доработать: двойная авторизация</strong></li>
                        <li><strong>Доработать: сделать возможность менять эффекты на фотках в постах</strong></li>
                    </ul>


                </div>
            </div>

        </div> <?php
    } 
    /**
     * Display option checkbox
     *
     * @param string $name
     */
    public function display_checkbox( $name ) {
        $checked = '';
        if (isset($this->options[$name]) && $this->options[$name] == 'on') $checked = ' checked';
        $string = '<input name="'.$this->option_name.'['.$name.']" type="checkbox" id="'.$name.'" value="on"'. $checked .'>';
        echo $string;
    }

    /**
     * Display input text field
     *
     * @param string $name
     */
    public function display_input_text( $name, $type = 'text' ) {
        $value = '';
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];
        $string = '<input name="'.$this->option_name.'['.$name.']" type="'.$type.'" id="'.$name.'" value="'. $value .'"" class="form-control">';
        echo $string;
    }

    /**
     * Display textarea field
     *
     * @param string $name
     */
    public function display_textarea( $name, $class='form-control', $wpar = false ) {
        $wrap = ($wrap) ? 'wrap="off"' : '';
        $value = '';
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];
        $string = '<textarea name="'.$this->option_name.'['.$name .']" id="'.$name.'" class="'.$class.'" rows="7" autocomplete="off" '.$wrap.'>'.$value.'</textarea>';
        echo $string;
    }

    /**
     * Display textarea field
     *
     * @param string $name
     */
    public function display_textarea_robots( $name ) {
         $value = '';
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];   
        if (!empty($this->options['delete_robots_txt']) ) { unlink(ABSPATH . 'robots.txt'); }
        if ( empty( $value ) ) {
            $plugin = new WebMFT_SEO();
            $value = $plugin->right_robots_txt( '' );
            if (!empty($this->options['vuvod_block_recomendacia']) ) { 
                $value = '';
                $string = '<textarea name="' . $this->option_name . '[' . $name . ']" id="' . $name . '" class="form-control" style="height: 500px;">'. $value .'</textarea>';
            }
            if (!empty($this->options['vuvod_block_bes_robot']) ) {
                $value = '';
                $value .= file_get_contents(ABSPATH . 'robots.txt');
                $string = '<textarea name="' . $this->option_name . '[' . $name . ']" id="' . $name . '" class="form-control" style="height: 500px;">'. $value .'</textarea>';
            }
            if (!empty($this->options['vuvod_block_bes_robot']) && !empty($this->options['vuvod_block_recomendacia']) ) {
                $value = '';
                $plugin = new WebMFT_SEO();
                $value = $plugin->right_robots_txt( '' );
                $value = file_get_contents(ABSPATH . 'robots.txt') . $value;
                $string = '<textarea name="' . $this->option_name . '[' . $name . ']" id="' . $name . '" class="form-control" >'. $value .'</textarea>';
            }
        }
        $string = '<textarea name="' . $this->option_name . '[' . $name . ']" id="' . $name . '" class="form-control" style="height: 500px;">'. $value .'</textarea>';
        echo $string;
    }










 public function display_textarea_htacess( $name ) {
         $value = '';
        if (isset($this->options[$name]) && ! empty($this->options[$name])) $value = $this->options[$name];   
      
    if ( empty( $value ) ) {
        $WPHE_orig_path = ABSPATH.'.htaccess';
        if(!file_exists($WPHE_orig_path)){
            $value = 'Htaccess file does not exists!';
            
        }else{
            
            if(!is_readable($WPHE_orig_path)){
                $value = 'Htaccess file cannot read!<';
                $success = false;
            } else
                @chmod($WPHE_orig_path, 0644);
                $WPHE_htaccess_content = file_get_contents($WPHE_orig_path);
                if($WPHE_htaccess_content === false){
                    $value = 'Htaccess file cannot read!';
                    
                }else{
             $WPHE_orig_path = file_get_contents(ABSPATH. '.htaccess');
             $value = $WPHE_orig_path;
                }
            
        }   
       
    }
        $string = '<textarea name="' . $this->option_name . '[' . $name . ']" id="' . $name . '" class="form-control" style="height: 500px;">'. $value .'</textarea>';
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
        $string  = '<input name="'.$this->option_name.'['.$name.']" type="number" ';
        if (!empty($step)) $string .= 'step="'. $step .'" ';
        if (!empty($min) || $min === 0)  $string .= 'min="'. $min .'"  ';
        if (!empty($max))  $string .= 'max="'. $max .'" ';
        $string .= 'id="'.$name.'" value="'. $value .'"" class="form-control">';
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
        $string  = '<select class="form-control" name="'.$this->option_name.'['.$name.']" id="'.$name.'">';
        if (is_array( $values )) {
            foreach ($values as $key => $value) {
                $selected = '';
                if (isset($this->options[$name]) && $this->options[$name] == $key) $selected = ' selected';

                $string .= '<option value="'.$key.'"'. $selected .'>'.$value.'</option>';
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
