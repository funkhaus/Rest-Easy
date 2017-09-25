Rest-Easy is a Wordpress plugin designed to Rest-ify your site with zero effort and powerful customization.

Rest-Easy allows users and devs to do the following out of the box:
1. Request any page on a Wordpress site with either:
    * the `CONTENT_TYPE: application/json` header, or
    * `?contentType=json` as a query string in the URL
1. Expect the response to be a JSON object with relevant information about that page (and the site as a whole), including:
    * Site title, description, and menus
    * Page ID, title, content, permalink, neighbors, and more

Developers also have access to a wide array of filters to customize the information dumped onto a page.

## Example

### Basic:
Install Rest-Easy, then navigate to your site and run the following in your JS console:

```js
fetch('/?contentType=json')
    .then(res => { return res.json() })
    .then(json => console.log(json))
```

This example fetches the current page of your site and returns its data as a JSON object. Right away, you've got a working RESTful API with plenty of detailed information at your disposal.

### Using Filters
Let's say you want to make a custom field called `_my_custom_field` available in the JSON object. Add the following to your `functions.php` file:

```php
function add_custom_field($input){
    global $post;
    $input['_my_custom_field'] = $post->_my_custom_field;

    return $input;
}
add_filter('rez_serialize_post', 'add_custom_field');
```
Now, load a page on your site and run the same JS code as above. You'll see your custom field in `page[0]._my_custom_field`.

## API
### Flow
Rest-Easy's entry point is `core.php`, where it:

1. Checks each request's `CONTENT_TYPE` and query strings for a JSON request
1. Echoes the JSON-encoded results of `builders.php`'s `rez_build_all_data` function
1. Exits

### Filters
* `rez_build_all_data` - Highest level data builder. Returns:
    ```
    array(
        // key      => filter
        'site'      => rez_build_site_data,
        'meta'      => rez_build_meta_data,
        'page'      => rez_build_page_data
    )
    ```
* `rez_build_site_data` - Builds general information about the site:
    ```
    array(
        'themeUrl'      => 'URL of current WordPress theme',
        'name'          => 'Site name',
        'description'   => 'Site description',
        'menus'         => array(
            // Array of all menus on the site run through 'rez_serialize_menu' filter
        )
    )
    ```

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
