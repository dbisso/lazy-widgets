=== Lazy Widget ===
Contributors: dbisso
Tags: widgets, lazy load
Requires at least: 4.1
Tested up to: 4.2.2
Stable tag: 0.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows widgets to be lazy loaded after the main page load has completed.

== Description ==

Loading multiple widget on each page can slow things down. This plugin interrupts the
display callback of each widget and replaces it with a placeholder. Once the main page
content has loaded, an AJAX call retrieves the HTML for each widget and updates the page.

Limitations:

- Does not take into account the original page query. If your widget requires any query-based
logic (eg `is_home()`, `is_tax()` etc.) then it may not work intended.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Appearence > Widgets and for each active widget, choose whether it should be lazy loaded.


== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 0.1.0 =
Initial release