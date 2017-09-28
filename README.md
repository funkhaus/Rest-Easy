Rest-Easy is a Wordpress plugin designed to Rest-ify your site with zero effort and powerful customization.

Rest-Easy allows users and devs to do the following out of the box:
1. Request any page on a Wordpress site with either:
    * the `CONTENT_TYPE: application/json` header, or
    * `?contentType=json` as a query string in the URL
1. Expect the response to be a JSON object with relevant information about that page (and the site as a whole), including:
    * Site title, description, and menus
    * Page ID, title, content, permalink, neighbors, and more
1. Expect that same data as a global JSON object called `jsonData` to be available on any page at load.

Developers also have access to a wide array of filters to customize the information dumped onto a page.

## Examples

### Basic:
Install Rest-Easy, then navigate to your site and run the following in your JS console:

```js
jsonData
```
You'll see all the available data from this page as a JSON object. Rest-Easy uses `wp_localize_script` to place this data on the page.

```js
fetch('/?contentType=json')
    .then(res => { return res.json() })
    .then(json => console.log(json))
```
This example fetches the current page of your site and returns its data (the contents of which are the same as `jsonData` above) as a JSON object. Right away, you've got a working RESTful API with plenty of detailed information at your disposal.

### Using Filters
Let's say you want to make a custom field called `_my_custom_field` available in the JSON data. Add the following to your `functions.php` file:

```php
function add_custom_field($input){
    global $post;
    $input['_my_custom_field'] = $post->_my_custom_field;

    return $input;
}
add_filter('rez_serialize_post', 'add_custom_field');
```
Now, load a page on your site and run the same JS code as above. You'll see your custom field in `jsonData.page[0]._my_custom_field`.

## API

### Concepts
To avoid infinite loops in page serialization, Rest-Easy uses two main concepts: __builders__ and  __serializers__.

A __builder__ will run once on a page. It combines the output of several serializers and returns that data as an associative array, which is then JSON-encoded to form `jsonData`.

A __serializer__ will take one piece of data from Wordpress and translate it into an associative array. For example, a serializer will take a post and turn it into an array with that post's title, content, permalink, and so on.

### Flow
Rest-Easy's entry point is `rest-easy.php`, where it:

1. Saving the output of the builder in `builders.php`'s `rez_build_all_data` function
1. Determines how to send the requested output to the user by:
    * checking the request's `CONTENT_TYPE` and query strings for a JSON request, echoing the `jsonData` object if one was found
    * dumping the `jsonData` object onto the page with `wp_localize_script` otherwise

### Filters
Tap into any of the filters below to add your own data. Default values are shown below.

#### Builder Filters
* `rez_build_all_data` - Highest level data builder. Returns:
    ```php
    array(
        // key      => filter
        'site'      => rez_build_site_data,
        'meta'      => rez_build_meta_data,
        'loop'      => rez_build_loop_data
    )
    ```
* `rez_build_site_data` - Builds general information about the site:
    ```php
    array(
        'themeUrl'      => 'URL of current WordPress theme',
        'name'          => 'Site name',
        'description'   => 'Site description',
        'menus'         => array(
            // Array of all menus on the site
        )
    )
    ```
* `rez_build_meta_data` - Builds meta information about the current page.
    ```php
    array(
        'self'          => 'permalink to current page',
        'is404'         => /* bool - did this request return a 404 error? */
    )
    ```
* `rez_build_loop_data` - Serializes all pages currently in [The Loop](https://codex.wordpress.org/The_Loop).
    ```php
    array(
        // Array of serialized posts, pages, etc.
        // By default, each element in the array will be the result of
        // combining 'rez_serialize_object' and 'rez_gather_related'
    )
    ```

#### Serializer Filters
* `rez_serialize_object` - Generic serializer. Knows how to serialize any object.
    ```php
    * Runs rez_serialize_attachment filter if a media attachment
    * Runs rez_serialize_menu filter if a menu
    * Runs rez_serialize_nav_item filter if a menu item
    * Runs rez_serialize_post filter if any other object type
    ```
* `rez_serialize_attachment` - Serializes a media attachment:
    ```php
    array(
        'title'             => 'title of attachment',

        // This section only runs if the Funky Colors plugin is installed
        'primary_color'     => 'primary image color from Funky Colors',
        'secondary_color'   => 'secondary image color from Funky Colors'
        // End Funky-Colors-only section

        'sizes' => array(
            // Runs for each image size defined in WP (https://developer.wordpress.org/reference/functions/add_image_size/)
            'size-slug' => array(
                'url'       => 'url to image at given size',
                'width'     => /* int - width in px */,
                'height'    => /* int - height in px */
            )
        )
    )
    ```
* `rez_serialize_menu` - Serializes a menu and its items:
    ```php
    array(
        'name'  => 'menu name',
        'slug'  => 'menu slug',
        'items' => array(
            // Array of all items in this menu run through `rez_serialize_object` filter
        )
    )
    ```
* `rez_serialize_nav_item` - Serializes a menu item:
    ```php
    array(
        'title'         => 'menu item title',
        'classes'       => 'menu item classes',
        'permalink'     => 'permalink to target',
        'relativePath'  => 'relative path to target',
        'is_external'   => /* bool - true if type label == 'Custom Link' */
    )
    ```
* `rez_serialize_post` - Generic serializer for any post type:
    ```php
    array(
        'id'            => /* int - post ID */,
        'title'         => 'post title',
        'content'       => 'content with "the_content" filters applied',
        'excerpt'       => 'post excerpt',
        'permalink'     => 'post permalink',
        'slug'          => 'post slug',
        'relativePath'  => 'relative path to post',
        'meta'          => array(
            // Contains all meta fields without leading underscore
            // $post->this_will_be_included
            // $post->_this_will_not
        ),
        'date'          => /* int - Unix timestamp of post date */
    )
    ```
* `rez_gather_related` - Gets related data for a given object:
    ```php
    array(
        'featured_attachment'   => 'the serialized featured attachment, if this object has one',
        'children'  => array(
            // children of this page, if applicable, serialized with rez_serialize_post
        ),
        'next'      => /* object - the next page in menu order, if applicable, serialized with rez_serialize_post */,
        'prev'      => /* object - the previous page in menu order, if applicable, serialized with rez_serialize_post */
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

Version: 1.2

* 1.2 - Updated formatting and documentation
* 1.1 - Renaming `rez_build_page_data` to `rez_build_loop_data`. Breaking change from 1.0!
* 1.0 - Initial release

______
builders - run once, dump out data, call many serializers and related - gather related and serialize The Loop
rename "builder" to "loop_builder"
$related - take second argument that acts as relation entry point - only called on the results of The Loop by default - called on every post in The Loop, but no deeper
