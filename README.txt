=== WP GitHub Tools ===
Contributors: vilmosioo
Tags: github, tool, widget, repository, commit, gist
Requires at least: 3.4.2
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4K8F8YQMP84CJ

A plugin that inserts dynamic updates for any GitHub repository. 

== Description ==

Use the custom GitHub Commit widget to display a list of the latest updates from a repository. Additionally, you can use shortcodes to add commit lists or embed any gist. 
The plugin will cache the GitHub response for a certain time period (default: 1 day). You can change this value to half a day or 1 hour. To get more time frames you will need an additional plugin that extends the cron schedules.

= Shortcodes =

**[gist id='*gist_id*' ]** 

Embeds a gist in your post. Parameters:

 - *id* (required) The id of the gist you want to embed. 


**[commits repository='your-repository' count='max-count' title='your-title']** 

Displays the latest commits from your repository. Parameters:

- *repository* (required) The name of the repository you wish to get. 
- *count* (optional) The number of commits to retrieve (order by date). Default: 5
- *title* (optional) A title to display before the list (*h2*). Default: none

= PHP functions =

Feel free to use the Gihub helper class in your theme or plugin development.

`<?php WP_Github_Tools_API::can_update(); ?>`

`<?php WP_Github_Tools_API::get_repos($user); ?>`

`<?php WP_Github_Tools_API::get_user($user); ?>`

`<?php WP_Github_Tools_API::get_commits($repo, $user); ?>`

`<?php WP_Github_Tools_API::get_gists($user); ?>`

= Contribute! =

If you have suggestions for a new add-on, feel free to contact me on [Twitter](http://twitter.com/vilmosioo). Alternatively, you can fork the plugin from [Gihub](https://github.com/vilmosioo/Github-Tools-for-WordPress)
 
== Installation ==

1. Download the plugin files and upload them to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set your GitHub username using the 'GitHub Tools' page under the Tools menu
4. Ready to go!

== Screenshots ==

1. Commits shortcode in action. An un-onrdered list of the latest commits is displayed, that you can style as you please using CSS.
2. The settings page where you specify your GitHub username and refresh rate.
3. The commits widget that you can use on any sidebar to deliver live updates for your projects.

== Upgrade Notice ==
*   Version 1.0 published!

== Frequently Asked Questions ==
Send any questions directly to [me](http://twitter.com/vilmosioo)!

== Changelog ==

= 1.0 =

*   Added custom widget to display repository commits.
*   Implemented custom shortcode to display repository commits.
*   Implemented custom shortcode to embed gists.
*   Customizable cache system.
*   Live validation of GitHub usernames.