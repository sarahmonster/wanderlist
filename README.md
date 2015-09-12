# wanderlist

Wanderlist is a WordPress plugin for travellers and nomads, to help you track & display your travels.

## Getting started

Install the plugin by uploading it to your plugins directory (usually `wp-content/plugins`). Before you start adding places, you'll want to hook up the plugin with an API key. It's super-simple.

### Getting a Mapbox API key

Wanderlist uses Mapbox to serve beautiful, easily-to-customize maps. In order to use it, you'll need to sign up for a free Mapbox account.

First, you'll need to [sign up for a free Mapbox account](https://www.mapbox.com/signup/)

Once that's all taken care of, navigate to [Account → Apps](https://www.mapbox.com/account/apps/) to generate a new API key. Make sure to choose a **public** key and give it a memorable name. Your new API key should look something like this:

`pk.xX13xX7332b2389csa6ST23uhgwq7iuhdasy1276`

Copy this entire line, **including** the `pk.` prefix. Enter it in your settings page at WP Admin → Wanderlist → Settings.

Note: For the moment, stuff won't work as expected if you don't have a Mapbox API key entered correctly. I'll build in check to ensure the API keys have been properly entered later on, but for now, please ensure that you've entered the full public API key for your account, *including the pk. prefix*! If you use Mapbox's rather temperamental "copy to clipboard" button, it won't copy the `pk.` prefix, so for now, you'll want to manually add it.

### Setting up your settings

Wanderlist has only a few basic options for now, but you'll want to set them up before you get rolling.

The first thing you'll want to do is set your "home" location.
