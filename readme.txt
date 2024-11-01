=== Super Simple Account Enumeration Blocker ===
Contributors: gilzow
Tags: user enumeration, wpscan, security
Requires at least: 2.2
Tested up to: 4.7.3
Stable tag: 1.0
License: GPL2 or later
Blocks account enumeration attempts

== Description ==
After speaking at WordCamp St. Louis 2017 <http://wordpress.tv/2017/03/29/paul-gilzow-access-denied-keeping-yourself-off-an-attackers-radar/>,
I was asked if I could bundle the code I demo'ed in the talk into a plugin for people who aren't as comfortable writing their
own code.  As its name implies, it is super simple. There are no settings.  The entire codebase is contained in one file,
and for the most part is about 20 lines of code in length. It is fully commented and I encourage you to look at the code
to see what it does instead of blindly trusting it.

Specifically, this plugin:
* removes the redirection of a request from /?author=# to an author's pretty permalink
* changes author pretty permalinks to /?author=#
* changes author feed pretty permalinks to /?author=#&feed=<feed>
* removes author slug property from user response object for user endpoint in the REST API
* removes overly informative error message when login attempt fails

Rememer: this plugin, *by itself*, will not protect your site from being compromised.  However, it can be an important layer of
defense when used in a multilayer, defense-in-depth security strategy.


== Frequently Asked Questions ==
= What version of PHP is required? =
PHP 5.3 and newer.
= Where are the settings? =
There aren't any.  Adding settings would make it not simple! ;)

== Help and Support ==
Please post questions, request for help to the Wordpress plugins forum or
email <ssaeb@gilzow.com>. Please be sure to include 'ssaeb' in the
subject line.

== TO-DO's ==
Keep adding ways to block enumerations.

== Changelog ==
= 1.0 =
Initial Release