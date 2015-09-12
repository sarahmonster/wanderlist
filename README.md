# wanderlist

Wanderlist is a WordPress plugin for travellers and nomads, to help you track & display your travels.

## Getting started

Install the plugin by uploading it to your plugins directory (usually `wp-content/plugins`). Before you start adding places, you'll want go to the settings page and customise the plugin to your liking.

Your settings page can be found in your WordPress admin panel → Wanderlist → Settings

### Getting a Mapbox API key

Wanderlist uses Mapbox to serve beautiful, easily-to-customize maps. In order to use it, you'll need to sign up for a free Mapbox account.

First, you'll need to [sign up for a free Mapbox account](https://www.mapbox.com/signup/)

Once that's all taken care of, navigate to [Account → Apps](https://www.mapbox.com/account/apps/) to generate a new API key. Make sure to choose a **public** key and give it a memorable name. Your new API key should look something like this:

`pk.xX13xX7332b2389csa6ST23uhgwq7iuhdasy1276`

Copy this entire line, **including** the `pk.` prefix. Enter it in your settings page at WP Admin → Wanderlist → Settings.

Note: For the moment, stuff won't work as expected if you don't have a Mapbox API key entered correctly. I'll build in check to ensure the API keys have been properly entered later on, but for now, please ensure that you've entered the full public API key for your account, *including the pk. prefix*! If you use Mapbox's rather temperamental "copy to clipboard" button, it won't copy the `pk.` prefix, so for now, you'll want to manually add it.

### Using a custom date format

Wanderlist will default to using the date format as set in Options → General.

You can use an alternate date format instead, by entering a [PHP-formatted date string](https://codex.wordpress.org/Formatting_Date_and_Time) on the settings page.





The first thing you'll want to do is set your "home" location.
