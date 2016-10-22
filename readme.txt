# WebMFT: Plugin SEO useful

* Contributors: maximusft
* Donate link: https://ma-x.im
* Tags: postviews, prev post, next post, most viewed
* Requires at least: 4.0
* Tested up to: 4.6
* Stable tag: 1.7.5
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description

New release! Widget witout Shortcode!

Counts visits of post or tax term. Plugin take 3 sidebar shortcodes for: Prev post (img from thumb), Next posts, Most viewed.
Control Meta and Title
Yandex Analytic

## Installation

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)


## Changelog

= 1.2 =
* Add to Post Meta & Title, View and Admin

= 1.0 =
* First release

## AutoInstall to Widget
<dl>
  <dt>Table</dt>
  <dd>wp_options</dd>

  <dt>Column</dt>
  <dd>option_name</dd>

  <dt>Column value</dt>
  <dd>widget_text</dd>

  <dt>Nex Column "option_name"</dt>
  <dd>a:4:{i:2;a:3:{s:5:"title";s:11:"Most viewed";s:4:"text";s:25:"[webmft_post_most_viewed]";s:6:"filter";b:0;}i:3;a:3:{s:5:"title";s:9:"Prev post";s:4:"text";s:18:"[webmft_post_prev]";s:6:"filter";b:0;}i:4;a:3:{s:5:"title";s:9:"Post next";s:4:"text";s:18:"[webmft_post_next]";s:6:"filter";b:0;}s:12:"_multiwidget";i:1;}</dd>
</dl>