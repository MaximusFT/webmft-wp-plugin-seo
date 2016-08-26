<?php
/*
Plugin Name: WebMFT: Plugin SEO useful
Plugin URI: https://github.com/MaximusFT/webmft-wp-plugin-seo
Description: Useful for SEO
Version: 1.3
Author: MaximusFT
Author URI: https://ma-x.im
License: GPL2

Copyright 2016  WEBMFT-WP (email: maximusft@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('MFT_PATH', plugin_dir_path( __FILE__ ) );
define('MFT_URL', plugin_dir_url( __FILE__ ) );
define('MFT_BASE', plugin_basename(__FILE__) );

require_once MFT_PATH . 'class.WebMFT_Postviews.php';
require_once MFT_PATH . 'class.WebMFT_Post_SEO.php';
require_once MFT_PATH . 'class.WebMFT_CustomField.php';

// init
add_action('plugins_loaded', function(){
	WebMFT_Postviews::init();
	WebMFT_Post_SEO::init();
	new WebMFT_CustomField( array(
		'id'     => '_seo',
		'title'  => 'SEO поля',
		'fields' => array(
			'_webmft_title' => array(
				'type'=>'text',    'title'=>'Title',       'desc'=>'Заголовок страницы (рекомендуется 70 символов)', 'attr'=>'style="width:99%;"'
			),
			'_webmft_description' => array(
				'type'=>'textarea','title'=>'Description', 'desc'=>'Описание страницы (рекомендуется 160 символов)', 'attr'=>'style="width:99%;"'
			),
			'_webmft_keywords' => array(
				'type'=>'text',    'title'=>'Keywords',    'desc'=>'Ключевые слова для записи',       'attr'=>'style="width:99%;"'
			),
/*
			'_webmft_robots' => array(
				'type'=>'radio',   'title'=>'Robots',      'options' => array(''=>'index,follow', 'noindex,nofollow'=>'noindex,nofollow')
			),
*/
		),
	));
});

function WebMFT_HeaderTitle(){
	global $post;

	if(is_front_page()){
		$mv_titl = get_post_meta(1, '_webmft_title', true);
	} else {
		$mv_titl = get_post_meta($post->ID, '_webmft_title', true);
	}

	return $mv_titl;
}

function WebMFT_HeaderMeta(){
	global $post;

	if(is_front_page()){
		$mv_desc = get_post_meta(1, '_webmft_description', true);
		$mv_keys = get_post_meta(1, '_webmft_keywords', true);

	} else {
		$mv_desc = get_post_meta($post->ID, '_webmft_description', true);
		$mv_keys = get_post_meta($post->ID, '_webmft_keywords', true);
	}

	echo '<meta name="description" content="'.$mv_desc.'">
';
	echo '<meta name="keywords" content="'.$mv_keys.'">
';
}


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


add_filter('pre_get_document_title', 'WebMFT_HeaderTitle');
add_action('wp_head', 'WebMFT_HeaderMeta', 1);