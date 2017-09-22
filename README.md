Rest-Easy is a Wordpress plugin designed to Rest-ify your site with zero effort and powerful customization.

Rest-Easy allows users and devs to do the following out of the box:
1. Request any page on a Wordpress site with either:
    * the `CONTENT_TYPE: application/json` header, or
    * `?contentType=json` as a query string in the URL
1. Expect the response to be a JSON object with relevant information about that page (and the site as a whole), including:
    * Site title, description, and menus
    * Page ID, title, content, permalink, neighbors, and more

Developers also have access to a wide array of filters to customize the information dumped onto a page.

## API
### Flow
Rest-Easy's entry point is `core.php`, where it:

1. Checks each request's `CONTENT_TYPE` and query strings for a JSON request
1. Echoes the JSON-encoded results of `builders.php`'s `rez_build_all_data` function
1. Exits.

Start in `rez_build_all_data` to determine which filters to hook into to customize your data.

#### Filter Priority
All of Rest-Easy's filters listed below are at [priority](https://developer.wordpress.org/reference/functions/add_filter/#parameters) 1, meaning they execute before the default Wordpress priority 10. This is so that you can ensure that your custom hooks will receive an associative array instead of a WP_Post object - Rest-Easy does the initial conversion for you.

### Filters
Most of Rest-Easy's functionality comes from its custom filters, which are designed to create varying levels of serialization for posts and dump those serialized JSON objects onto the page.

TODO: Filter docs

### Utility functions
* `rez_get_next_page_id($target_post)` - Get the ID of the page/post following the `$target_post`.
* `rez_get_previous_page_id($target_post)` - Get the ID of the page/post before the `$target_post`.
* `rez_remove_siteurl($target_post)` - Remove the siteurl to retrieve the relative path.

## Integrations
Rest-Easy is built to work well with other [Funkhaus](http://funkhaus.us) plugins:
* [Funky Colors](https://github.com/funkhaus/funky-colors), which determines an image's main colors

--------

__Rest-Easy__

http://funkhaus.us

Version: 1.0

* 1.0 - Initial release
