=== About ===
name: Find Location
website: http://apps.ushahidi.com
description: Improves the functionality of the "Find Location" Button. Needs to be used with a theme that supports this plugin. Specifically it needs the Liberian flavored version of Ushahidi's default theme.
version: 1.0
requires: 2.02 - Liberia Flavored
tested up to: 2.02
author: John Etherton
author website: http://johnetherton.com

== Description ==
Improves the functionality of the "Find Location" Button. Needs to be used with a theme that supports this plugin. This plugin will replace the functionality of the
reports/geocode controller, so you need to replace the .put url in the reports_edit_js.php. You also need a placeLocation(lat, lon, name) method in the javascript. 
Check out, https://github.com/jetherton/Ushahidi_Web/blob/liberia_2.0/themes/default/views/admin/reports_edit_js.php for an example.

* Lets you set the region/country results are biased towards
* Lets you add text to the end of a search. For instance you could add ", Mexico" so that all searches are "something, Mexico"