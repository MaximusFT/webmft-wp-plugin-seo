<?php 
if( ! defined('WP_UNINSTALL_PLUGIN') ) exit;

// проверка пройдена успешно. Начиная от сюда удаляем опции и все остальное.

delete_option('webmft_postviews');
delete_option('webmft_post_seo');