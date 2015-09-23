# Wanderlist

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

### Adding places

### Overview page

When you first activate Wanderlist, the plugin will automatically make a new page, and attempt to add it to your primary menu. If it hasn't been added to your primary menu, you can find this page at `/travels/`.

This page uses the overview shortcode, `[wanderlist overview]`, to show a dashboard-style overview of your travels. Eventually, this will be configurable via shortcode settings, but for now, it's rather static.

This page will show you an overview of your travels: countries you've visited, some statistics, a list of recent travels, a list of upcoming travels, and a full list of all your trips.

Still working on expanding this functionality. :)

## Settings

### Mapbox options

You can select the default Mapbox map ID you'd like to use, allowing you to easily swap map styles. Mapbox has [fourteen styles](https://www.mapbox.com/developers/api/maps/) that you can choose from, or you can make your own custom style using [Mapbox Studio](https://www.mapbox.com/mapbox-studio/).

To use a custom map ID, enter the ID: `mapbox.pirates`

You can also set your preferred marker and line colour by entering their hex codes here. Make sure to include the `#`!

### Using a custom date format

Wanderlist will default to using the date format as set in Options → General.

You can use an alternate date format instead, by entering a [PHP-formatted date string](https://codex.wordpress.org/Formatting_Date_and_Time) on the settings page.

### Hide links to place posts

By default, whenever Wanderlist display a list of places you've visited, it also shows a link to more information. Since the places act like posts, you can add photos, text, video—or anything you'd like—to give more information about your impressions of a place and the adventures you've had there.

If you're jetsetting around, you may not want to do this for every place, or you may want to write regular blog posts instead.

To hide the automatic link to the place's post, check the "Hide links to place posts" box in your settings page.

### Showing places you loved most

You can mark places you loved, and they'll appear in the front-end of your site with a little heart next to them. How adorable!

First, you need to choose a tag to use. From your settings panel, choose a tag you'd like to use under "Tag for "loved" places". Then, tag any place you loved with the tag you selected, and that post will appear as a loved post.


### Setting your home location(s)

### Customizing the way your site looks

Wanderlist has been tested with all the major Twenty themes, from Twenty Ten to Twenty Sixteen, but since it relies heavily on front-end templates, it may not integrate perfectly with all themes.

However, it's been built so you can easily change the way it looks to suit your liking. If you can edit your theme templates, you can edit Wanderlist's templates!

### Using your own custom templates

Wanderlist uses a series of templates for different pages. These templates can be found in the `templates` folder within the main plugin folder. The following templates are currently available:

- single-wanderlist-location.php *(singular place posts)*
- taxonomy-wanderlist-trip.php *(trip overview pages)*

To modify these, copy the template files you'd like to change from the plugin into the root of your theme directory, keeping the same filenames. If these files are present in your theme, Wanderlist will override the default templates with your custom templates. You can modify the plugin files directly, but you'll lose your changes when the plugin is updated, so it's best to add the custom templates to your theme instead—this way, you won't lose any of your work.

### Removing the default CSS styles

Wanderlist comes with its own CSS styles. If you find they're conflicting, or you'd like full control over your CSS, you can write your own instead.

To dequeue Wanderlist's custom CSS, copy the following line into your theme's `functions.php`

```
function flare_dequeue_plugin_styles()  {
	wp_dequeue_style( 'wanderlist-style' );
}
add_action( 'wp_print_styles', 'flare_dequeue_plugin_styles', 100 );
```

SCSS source files for Wanderlist's styles are included in the plugin for you to use as a starting point for your own styles. You can find them in the `assets/scss` folder.
