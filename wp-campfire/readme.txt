=== WP-Campfire ===
Contributors: skookum, mjar81
Tags: basecamp, campfire, notification, chat
Requires at least: 3.0
Tested up to: 3.1.2
Stable tag: 0.1.1

Notify your coworkers about a new blog post through the Basecamp Campfire group-chat service.

== Description ==

Notify everyone in a specified Campfire chat room when you publish a blog post. This would mainly be used for companies whose employees hang around in the chat room all day, but don't necessarily check the website.

This plugin requires cURL support on your WordPress server.

Provided by Skookum - http://skookum.com/
Written by Mark Rickert - http://www.ear-fung.us/

== Installation ==

1. Upload the `wp-campfire` directory to the `/wp-content/plugins/` directory on your server.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Go to the WP-Campfire settings page through the 'Settings' menu in Wordpress.
1. Enter your Campfire subdomain URL.
1. Enter your Campfire API key (instructions on finding this are on the settings page).
1. Enter the Room ID you'd like to announce to. To get your room ID, login to your Campfire room and look in the address bar (after /room).
1. Enter or modify the Text Pattern you would like to use (a default text pattern is provided).
1. Save your changes
1. From now on, in the edit post screen, there will be a meta box in the right column. The default is to announce when the post is published. You can turn it off on a per-post basis.

== Screenshots ==

1. Options screen
2. Post edit meta-box

== Changelog ==

= 0.1 =
* Initial public release

== To-Do ==

Here are a few things I'd like to implement in upcoming versions:

* Ping the Campfire API to get a list of your allowed rooms and allow you to select the room's "nicename" via a dropdown instead of the Room ID.
* Add more text pattern variables.