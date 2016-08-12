<?php
/*
Plugin Name: WebMFT: Plugin SEO useful
Plugin URI: https://github.com/MaximusFT/webmft-wp-plugin-seo
Description: Useful for SEO
Version: 1.0
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

// init
add_action('plugins_loaded', function(){
	WebMFT_Postviews::init();
	WebMFT_Post_SEO::init();
});

