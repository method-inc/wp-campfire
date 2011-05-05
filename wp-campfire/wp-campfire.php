<?php
/*
Plugin Name: WP-Campfire
Plugin URI: http://labs.skookum.com/wordpress/wp-campfire/
Description: Notify your coworkers about a new blog post through the Basecamp Campfire group-chat service
Version: 0.1
Author: Skookum
Author URI: http://skookum.com
License: GPL2

Copyright 2010  Skookum  (email : info@skookum.com)

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

add_action('admin_menu', 'wpcampfire_menu');
add_action('publish_post', 'wpcampfire_notify_campfire', 99);

function wpcampfire_menu()
{
	add_options_page('WP-Campfire Options', 'WP-Campfire', 'manage_options', 'wp-campfire', 'wpcampfire_options');

	if(function_exists('curl_exec'))
	{
		add_action( 'admin_init', 'register_wpcampfiresettings' );
		add_action( 'admin_init', 'wpcampfire_add_meta_box');
	}
}

function wpcampfire_notify_campfire($post_id)
{
	// this is only called on the publish_post hook
	if (get_option('wpcampfire_emable_option', 1) == 0
		|| $post_id == 0
		|| get_post_meta($post_id, 'wpcampfire_sent', true) == '1'
		|| get_post_meta($post_id, 'wpcampfire_send', true) == 'no'
	) {
		return;
	}
	$post = get_post($post_id);

	// check for private posts
	if ($post->post_status == 'private') return;
	
	//Send the notification to the campfire room.
	include('icecube.class.php');
	
	$wpcampfire_url = 'https://' . get_option('wpcampfire_url') . '.campfirenow.com';
	$wpcampfire_authtoken = get_option('wpcampfire_api_key');
	$wpcampfire_room_id = get_option('wpcampfire_room_id');
	
	$userdata = get_userdata($post->post_author);
	$wpcampfire_author = $userdata->display_name;
	
	$say = str_replace(
		array('{title}', '{link}', '{author}'), 
		array(get_the_title($post_id), get_permalink($post_id), $wpcampfire_author), 
		get_option('wpcampfire_text_pattern')
	);
			
	$icecube = new icecube($wpcampfire_url, $wpcampfire_authtoken, false);
	$icecube->speak($say, $wpcampfire_room_id);

	add_post_meta($post_id, 'wpcampfire_sent', '1', true);
}

function register_wpcampfiresettings()
{
	register_setting( 'wpcampfire-settings', 'wpcampfire_url' );
	register_setting( 'wpcampfire-settings', 'wpcampfire_api_key' );
	register_setting( 'wpcampfire-settings', 'wpcampfire_room_id' );
	register_setting( 'wpcampfire-settings', 'wpcampfire_text_pattern' );	
}

function wpcampfire_options()
{
	if (!current_user_can('manage_options'))
	{
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
?>
<div class="wrap">
	<h2>
		<img src="<?php echo plugins_url('/images/campfire-logo-small.png', __FILE__); ?>" alt="Campfire Logo" class="alignleft" />
		WP-Campfire Options
	</h2>
	
	<?php if(function_exists('curl_exec')) { ?>
	
	<form method="post" action="options.php">
	    <?php settings_fields( 'wpcampfire-settings' ); ?>
	    <table class="form-table">

	        <tr valign="top">
	        <th scope="row">Your Campfire URL:</th>
	        <td>https://<input type="text" name="wpcampfire_url" value="<?php echo get_option('wpcampfire_url'); ?>" />.campfirenow.com</td>
	        </tr>
	         
	        <tr valign="top">
	        <th scope="row">Your Campfire API Key:<br /><small>Find your API Key by clicking "My Info" at the top-right of the campfire interface.</small></th>
	        <td><input type="text" name="wpcampfire_api_key" value="<?php echo get_option('wpcampfire_api_key'); ?>" /></td>
	        </tr>
	         
	        <tr valign="top">
	        <th scope="row">Room ID:<br /><small>Find your room ID by looking at the URL while in that room. The ID is number at the end of the URL.</small></th>
	        <td><input type="text" name="wpcampfire_room_id" value="<?php echo get_option('wpcampfire_room_id'); ?>" /></td>
	        </tr>
	        
	        <tr valign="top">
	        <th scope="row">Text Pattern:<br />
	        	<small>You can use these variables:<br />
	        		{title}<br />
	        		{link}<br />
	        		{author}
	        	</small>
	        </th>
	        <td><textarea name="wpcampfire_text_pattern"><?php echo get_option('wpcampfire_text_pattern', 'New blog post by {author}: {title} - {link}'); ?></textarea></td>
	        </tr>
	    </table>
	    
	    <p class="submit">
	    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
	    </p>
	
	</form>
	
	<?php } else { //Output a nice error about curl 
	?>
		<div class="error">
			<p>It appears that you do not have <a href="http://php.net/curl" target="_blank" rel="nofollow">cURL</a> support installed in your version of PHP.</p>
			<p>Ask your hosting provider to enable it in order to use the WP-Campfire plugin.</p>
			<p>We're sorry for any inconvenience this may cause, but unfortunately, that's the way it is.</p>
		</div>
	<?php } ?>
	<div class="attribution">
		<small>The "Campfire" logo and name are trademarks of <a href="http://37signals.com/" target="_blank" rel="nofollow">37signals, LLC</a>.</small>
	</div>
</div>

<?php
} //Close wpcampfire_options() function


function wpcampfire_meta_box()
{
	global $post;
	$notify = get_post_meta($post->ID, 'wpcampfire_send', true);

	echo '<p>' . __('Send publish notification to Campfire?', 'wpcampfire') . '<br />
			<input type="radio" name="wpcampfire_send" id="wpcampfire_send_yes" value="yes" ' . checked('yes', $notify, false) . ' /> <label for="wpcampfire_send_yes">' . __('Yes', 'wpcampfire') . '</label> &nbsp;&nbsp;
			<input type="radio" name="wpcampfire_send" id="wpcampfire_send_no" value="no" ' . checked('no', $notify, false) . ' /> <label for="wpcampfire_send_no">' . __('No', 'wpcampfire') . '</label></p>';
	do_action('wpcampfire_post_options');
}

function wpcampfire_add_meta_box()
{
	if (get_option('wpcampfire_emable_option', 1) == 1)
		add_meta_box('wpcampfire_post_form', __('WP-Campfire', 'wpcampfire'), 'wpcampfire_meta_box', 'post', 'side');
}

function wpcampfire_store_post_options($post_id, $post = false)
{
	$post = get_post($post_id);

	if (!$post || $post->post_type == 'revision')
		return;

	$notify_meta = get_post_meta($post_id, 'wpcampfire_send', true);
	$posted_meta = $_POST['wpcampfire_send'];

	$save = false;
	if (!empty($posted_meta))
	{
		$posted_meta == 'yes' ? $meta = 'yes' : $meta = 'no';
		$save = true;
	}
	else if (empty($notify_meta))
	{
		get_option('wpcampfire_emable_option', 1) ? $meta = 'yes' : $meta = 'no';
		$save = true;
	}
	
	if ($save)
	{
		update_post_meta($post_id, 'wpcampfire_send', $meta);
	}
}

add_action('draft_post', 'wpcampfire_store_post_options', 1, 2);
add_action('publish_post', 'wpcampfire_store_post_options', 1, 2);
add_action('save_post', 'wpcampfire_store_post_options', 1, 2);

?>