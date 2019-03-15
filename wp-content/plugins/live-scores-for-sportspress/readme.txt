=== Live Scores for SportsPress ===
Contributors: ibenic, freemius
Tags: sportspress, live
Requires at least: 4.0
Tested up to: 4.9.6
Stable tag: 1.5.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Add Live Scores feature to SportsPress. Give your visitors the ability to view the results without refreshing your page.

== Description ==

Treat your visitors with live scores directly on your site. This plugin is an extension for SportsPress.

Define the structure of your sport and deliver real-time minutes to your visitors. 

You can define periods that track minutes such as 1st & 2nd Half in Soccer and periods which do not track time (and even pause it) such as Timeouts (Basketball, Handball) or Penalties (after 120 minutes in soccer)

[youtube https://www.youtube.com/watch?v=FPNgeMbE25U]

Currently Available Live Templates:

- Event List
- Event Blocks

Single Event:

- Live Results
- Front From to edit live results and status
- Scorers (Add players who scored)

Widgets:

- Live Event List

Admin:

- Live Events Page where you can manage all live events
- Minutes Corrections

**PRO Feaures:**

- Live Event Commentary - add comments on the event page so your visitors know what is happening on the field
- Custom Commentary Icons
- Front Commentary Form
- Live Notifications Integration
- Commentary Custom Colors
- Auto Start an Event
- Commentary Player (Add a player picture to the commentary)

== Installation ==

Install this plugin from the WordPress Plugins page or:

1. Upload live-scores-for-sportspress.zip from the 'Plugins' menu
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to SportsPress > Settings > Live and change the update frequency

== Frequently Asked Questions ==

= Will there be other templates available? =

Yes. I am working on enabling all event templates such as blocks.

= Will templates from SportsPress PRO be available? =

That is also under development and will be inside the PRO version.

== Screenshots ==

1. Live Event Details
2. Live Commentary on the event page 
3. Live Commentary on the admin side
4. Integrations Screen
5. Scorespro Soccer Live Events

== Changelog ==

= 1.5.0 =
* Added Scorespro live scores for Basketball, Volleyball and Hockey
* Added Option to Display Scorer Number
* Added Option to Display Scored Points instead of minutes
* Added Option to define scored Points when adding Scorer
* PRO: Added option to add Scorer info directly to Commentary (Admin)
* PRO: Added Player dropdown in Commentary Form on Front

= 1.4.0 =
* Added: Scorers - add players who scored with their minutes. Updates the box score automatically.
* Adding Live Part on an event in case you need more.
* PRO: Auto Start an Event.
* PRO: Players can be added to Commentary (picture).
* PRO: Updated notifications with local storage.

= 1.3.0 =
* Added: Minutes Correction if something was not recorded correctly before.
* PRO: Added Commentary Colors. For example, Goal commentary should stand out.

= 1.2.0 =
* Fixed: ScoresPro Free Soccer Feed
* Added: Live Matches Page under SportsPress Matches
* PRO: Added Live Notifications Integration

= 1.1.1 =
* Fixed: HTML error in Premium Commentary Form
* Fixed: Live Event Widget Columns Display
* Added: Live Event Widget choice for displaying the calendar title

= 1.1.0 =
* Fixed: JavaScript Error in handling result updates when they are in a link.
* Added: Live Event List Widget.

= 1.0.1 =
* Fixed: Admin Live Parts are now working.

= 1.0.0 =
* Fixed: Admin Live Part Configuration URL for Add New and View All now link correctly
* Fixed: Live Results Form now starts only the first half. There was a bug where all halves started on the start of the event.
* Added Live Results Form on the Event Page to manage it from the front.
* PRO: Added Commentary Icons to Configuration Page with Colors
* PRO: If there are custom Commentary Icons, the Commentary Form will use them instead of the default ones
* PRO: Added Commentary Form on the Front
 
= 0.4.1 =
* Fixed Admin Live Form where all live parts were triggered od live start. Now it only triggers the first one.
* Fixed Live Status display where the paused periods were not displaying the message if they had it.

= 0.4.0 =
* Added Live Results as a block on Event Details
* Added Integration support
* Added Free Scorespro Live Scores - Soccer
* Fixed JavaScript where multiple results from events did not return to normal size

= 0.3.0 =
* Added Event Blocks Live Template

= 0.2.0 =
* Added Live Parts Configurations on Live page
* Fixed escaping the commentary output

= 0.1.2 =
* Adding an option to replace the event list with a live event list on the event list page
* Fixed Bug - Live Commentary can now be excluded from the event page (PRO)

= 0.1.1 =
* Loading all only when SportsPress is active

= 0.1 =
* First initial push
* Code may vary and change by next updates
