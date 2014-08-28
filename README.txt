=== USC FB Events ===
Contributors: pcraig3
Requires at least: 3.5.1
Tested up to: 3.6
Stable tag: 0.9.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin gets events from Facebook and lists them same as clubs.  Backend menu and super-cool caching.

== Description ==

Plugin gets events from Facebook and lists them same as clubs.  Backend menu and super-cool caching.

List of features we want:

*   Need some api connection to events.
*   Need page on which events are generated.
*   Need to create database table.
*   Need to create backend menu.
*   Need to overwrite content using backend (adding tickets)

**WOULD BE AWESOME TO INTERFACE WITH REAL EVENTS PLUGIN**

== Installation ==

Like normal.  I'll send you a ZIP file and everything should work.


== Frequently Asked Questions ==

= Why doesn't it work yet. =

It almost works.


== Changelog ==

= 0.9.9 =
* Updated the CSS for the events widget.
* Made sure the caching pays nice even with that stupid APC nonsense.
* Added namespaces to my other classes.
* Events now save in DB no problem, but who knows for how long.
* Changed name of backend table for FB events.
* Changing the name of the plugin.


= 0.9.8 =
* Huge update.  Revolutionized the API calling.
* Now calling the same URL as the calendar on the old site (wasn't the same before)
* Can now pass limits and calendars through our plugins to the API so as to reduce post-processing of data
* API now takes UNIX start-times rather than urls
* Caching works in a way I'm pretty happy with

= 0.9.7 =
* Using the HTTP API now instead of cURL
* Implemented some (back-loaded) caching so that we don't have that mustang on the home page getting too tired

= 0.9.6 =
* Every event view now respects limits, and upcoming/past events are distinguished with CSS classes.

= 0.9.5 =
* Ajax widget now respects limits, some of the date formatting has been improved, and we're on a horse.

= 0.9.4 =
* Ajax widget should now load the latest (number) USC/Wave events.  Use case is for upcoming events on home page.

= 0.9.3 =
* Ajax cat shows up on public list
* Also, fixed some internal linking problems that had the plugins break on a new site.

= 0.9.2 =
* Event list for not-logged-in users wasn't working, but it is now. Ha!

= 0.9.1 =
* Applied filter.js to our (brought-in-by-ajax) event list

= 0.9.0 =
* Validating ticket URLs and displaying them optionally.  Very close to beta.

= 0.6.3 =
* 'Reset to defaults', modified tag, and admin notices fixed up

= 0.6.2 =
* Pretty sure I got all the bugs out of the datepicker.  Almost killed me.

= 0.6.1 =
* New custom fields included: functioning datepicker upcoming

= 0.6.0 =
* Breaking changes: building page with Admin Page Framework now, but lost date inserting.

= 0.5.2 =
* Form builds programatically and DATES are FORMATTING

= 0.5.1 =
* Added new columns, checked URLs on insert/update, added column values as .data in my list

= 0.5.0 =
* Updating (or clearing) Host works. Unified API/db calls.

= 0.4.2 =
* Nonce on ajaxes, loading gif, buttons disabled more consistently

= 0.4.1 =
* Setting up our list works good now.  Checkboxes filtering events as expected.

= 0.4.0 =
* DB stuff seems to be working. Censoring events doesn't do anything, but it COULD.

= 0.3.1 =
* At the point where DB stuff might be working.

= 0.3.0 =
* Admin menu pulls in and filters events using filter.js

= 0.2.1 =
* Listed events now include their cropped cover photo, their host, and date

= 0.2.0 =
* Plugin pulls events from Facebook and lists them on the page.

= 0.1.0 =
* Plugin with correct name recognizes its own shortcode

= 0.0.1 =
* Boilerplate version of plugin
