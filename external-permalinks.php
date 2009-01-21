<?php
/*
Plugin Name: External Permalinks
Plugin URI: http://www.improvingtheweb.com/wordpress-plugins/external-permalinks/
Description: Allows you to point a post to an external location. Ideal for guest post or cross promoting posts across blogs.
Version: 1.0
Author: Improving The Web
Author URI: http://www.improvingtheweb.com/
*/

if (is_admin()) {
	register_activation_hook(__FILE__, 'ep_install');
	register_deactivation_hook(__FILE__, 'ep_uninstall');
		
	require dirname(__FILE__) . '/admin.php';
} else {
	add_action('wp', 'ep_wp');
}

function ep_install() {
	if (!get_option('ep_options')) {
		add_option('ep_options', array('redirect_comments_page' => 1, 'show_notice_in_the_loop' => 0, 'show_notice_in_rss_feed' => 1, 'notice_text' => 'Read the rest of this entry', 'show_excerpt' => 0));
	}
}

function ep_uninstall() {
	delete_option('ep_options');
}
	
function ep_wp() {
	global $ep_options;
	
	$ep_options = get_option('ep_options');
	
	add_filter('the_permalink', 'ep_the_permalink'); 

	if ($ep_options['redirect_comments_page']) {
		add_filter('post_link', 'ep_post_link', 10, 2);
	} 

	add_filter('the_content', 'ep_the_content');

	if (is_single() && $ep_options['redirect_comments_page']) {
		global $post;
		
		if ($external_link = get_post_meta($post->ID, '_external_permalink', true)) {
			$post->external_link = $external_link;
			
			header('Location: ' . $external_link);
		}
	} 
}

function ep_the_content($text) {
	global $ep_options, $post;
			
	if ($external_link = get_post_meta($post->ID, '_external_permalink', true)) {
		if ($post->post_excerpt) {
			$text = '<p>' . $post->post_excerpt . '</p>';
		} else if ($ep_options['show_excerpt']) {
			if (preg_match('/<!--more(.*?)?-->/', $post->post_content, $matches)) {
				$text = explode($matches[0], $content, 2);
			} else 	if (strlen($text) > 1000) {
				$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $post->post_content); 
				$text = preg_replace('@<![\s\S]*?--[ \t\n\r]*>@', '', $text); 
				$text = strip_tags($text);
				
				$text = substr($text, 0, 1000) . '...';
			}
		} 
		
		if (is_feed()) {		
			if (!$ep_options['show_notice_in_rss_feed']) {
				return $text;
			}
		} else {
			if (!$ep_options['show_notice_in_the_loop']) {
				if (!is_single()) {
					return $text;
				}
			}
		}
		
		if (is_single() || !preg_match('/<!--more(.*?)?-->/', $post->post_content) ) {	
			$text .= '<p><a class="more-link" href="' . $external_link . '">' . $ep_options['notice_text'] . ' &raquo;</a></p>';
		}
	}
	
	return $text;
}

function ep_the_excerpt($excerpt) {
	global $ep_options, $post;
		
	if (is_feed()) {
		if (!$ep_options['show_notice_in_rss_feed']) {
			return $text;
		}
	} else {
		if (!$ep_options['show_notice_in_the_loop']) {
			if (!is_single()) {
				return $text;
			}
		}
	}
		
	if ($external_link = get_post_meta($post->ID, '_external_permalink', true)) {
		$text .= '<p><a class="more-link" href="' . $external_link . '">' . $ep_options['notice_text'] . ' &raquo;</a></p>';
	}
	
	return $excerpt;
}

function ep_the_permalink($permalink) {
   	global $ep_options, $wp_query, $post;
		
	if ($ep_options['redirect_comments_page']) {
		if ($wp_query->in_the_loop) {
			return $permalink;
		}
	}
		    
	$external_link = get_post_meta($post->ID, '_external_permalink', true);
   
 	return ($external_link) ? $external_link : $permalink;  
}

function ep_post_link($permalink, $post) {
	global $wp_query, $post;
	
	if (!$wp_query->in_the_loop || is_single()) { 
		return $permalink;
	} 
		
	$external_link = get_post_meta($post->ID, '_external_permalink', true);
	
	return ($external_link) ? $external_link : $permalink;
}
?>