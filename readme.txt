=== Dashboard for Pressbooks and H5P ===
Contributors: figureone
Tags: pressbooks, h5p, dashboard, widget
Requires at least: 5.3
Tested up to: 5.4.1
Requires PHP: 5.6.20
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Generates summaries of H5P content and results in a Pressbooks book.

== Description ==

Generates summaries of H5P content and results in a Pressbooks book.

View or contribute to the plugin source on Github: [https://github.com/uhm-coe/dashboard-for-pressbooks-h5p](https://github.com/uhm-coe/dashboard-for-pressbooks-h5p)

*Dashboard for Pressbooks and H5P* requires the following WordPress plugins installed:

* [Pressbooks](https://docs.pressbooks.org/installation/)
* [H5P](https://wordpress.org/plugins/h5p/)

*Dashboard for Pressbooks and H5P* provides the following features:

* **Dashboard Widget**: a new dashboard widget for instructors showing student progress. Progress can be shown by user and by chapter, and filtered by user role and a range of dates of user registration or last login. Note: last logins are tracked once this plugin is enabled, so there will be no last login times saved from before plugin activation.
* **Chapter Badges** in *Table of Contents*: a new badge appears next to chapters with embedded H5P content in the *Table of Contents*. For anonymous users, the badge shows the total number of H5P embeds in the Chapter. For logged in users, the badge shows the number of incomplete H5P embeds, or a checkmark if they are all complete. Hovering over the badge reveals a tooltip with details on each H5P embed.
* **Chapter Badges** in *Dashboard > Organize*: a new column, H5P Content, appears in the *Pressbooks Organize* dashboard showing which chapters have embedded H5P content.
* **Hide H5P Content For Anonymous Users**: a new option (shown below) to prevent anonymous users from seeing H5P Content. Use this to encourage users to log in so their results can be stored.

== Installation ==

1. Upload the `dashboard-for-pressbooks-h5p` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where is this plugin used? =

This plugin is developed for Pressbooks textbooks at the [University of Hawaiʻi OER][https://pressbooks.oer.hawaii.edu/] repository, which provides free open educational resources to students.

== Screenshots ==

1. Plugin features and settings.
2. Dashboard widget.
3. Dashboard widget showing details on a particular user and chapter.
4. Chapter badges in the table of contents.
5. Chapter badges in the Organize dashboard.
6. Example of hidden content for anonymous users.
7. Example of table of contents tooltip for anonymous users.

== Changelog ==

= 1.0.4 =
* Remove CDN dependency for tippy.js; host locally.

= 1.0.3 =
* Rename to Dashboard for Pressbooks and H5P.

= 1.0.2 =
* Add a link to the Settings page from the WordPress Plugins page.
* Update translatable strings.

= 1.0.1 =
* Fix php warning on dashboard when dependencies missing.
* Check for dependencies before performing actions.

= 1.0 =
* Initial release, Mon Jun 1, 2020.

= 0.1.0 =
* Initial development build, Wed Mar 4, 2020.
