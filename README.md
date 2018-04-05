Rest-Easy is a Wordpress plugin designed to Rest-ify your site with zero effort and powerful customization.

## Table of Contents
1. [Installation](#installation)
1. [Tutorial](#tutorial)
    1. [Basics](#basics)
    1. [Custom Filters](#custom-filters)
1. [Core Concepts](#core-concepts)
1. [Reference](#reference)
    1. [Filters](#filters)
        1. [Builder Filters](#builder-filters)
            1. [Site Data](#site-data)
            1. [Meta Data](#meta-data)
            1. [Loop Data](#loop-data)
        1. [Serializer Filters](#serializer-filters)
            1. [Serialize Object](#serialize-object)
            1. [Serialize Attachment](#serialize-attachment)
            1. [Serialize Menu](#serialize-menu)
            1. [Serialize Nav Item](#serialize-nav-item)
            1. [Serialize Post](#serialize-post)
            1. [Gather Related](#gather-related)
    1. [Utility Functions](#utility-functions)
    1. [Integrations](#integrations)

## Installation
Rest-Easy was built as a companion to [Vuepress](https://github.com/funkhaus/vuepress), so if you're using VP, Rest-Easy will be installed automatically.

If you're not using Vuepress, or would otherwise like to install the plugin manually, follow these steps:

1. Download the [latest version of the plugin](https://github.com/funkhaus/Rest-Easy/archive/master.zip).
1. Go to your site's plugin installation page (`[your-site.com]/wp-admin/plugin-install.php`).
1. Click "Upload Plugin," then upload the .zip file from step 1.
1. Go to your site's Plugins page (`[your-site.com]/wp-admin/plugins.php`) then click "Activate" on the Rest Easy plugin.

That's it!

## Tutorial
Take a look at the [visual docs](https://codepen.io/SanderMoolin/full/JMLvBb) to see a step-by-step roadmap of a Rest-Easy response. If you're using Vuepress, the Basics section is handled automatically - you can continue reading for an idea of how Rest-Easy works, or head down to [Custom Filters](#custom-filters) for the next steps.

### Basics
Once you've installed Rest-Easy, navigate to any page on your site with `?contentType=json` added at the end of the URL.

You'll see a serialized JSON object with the data of the page you requested - a lightweight and thorough summary of the current page, with zero setup on your end!

To fetch this JSON-serialized version of a page programmatically, you can do something like this in your site's JS:

```js
fetch(myUrl + '?contentType=json')
    .then(res => { return res.json() })
    .then(json => console.log(json))
```
This example [fetches](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API) the requested page of your site and returns the same JSON object that you got with the `?contentType=json` query parameter. Right away, you've got a working RESTful API with plenty of detailed information at your disposal.

![Diagram showing flow of Rest-Easy data construction](https://i.imgur.com/GKdWBQu.png)

### Custom Filters
Rest-Easy makes some assumptions about how you'd want a page to be serialized - but what if you want to change that serialization?

Let's say you want to have a custom field called `_my_custom_field` that you want to make available in the data. Add the following to your theme's `functions.php` file:

```php
function add_custom_field($input) {
    global $post;
    $input['myCustomField'] = $post->_my_custom_field;

    return $input;
}
add_filter('rez_serialize_post', 'add_custom_field');
```

Let's go through that line-by-line.

* `function add_custom_field($input) {`

    Rest-Easy will run its default serialization on a post first, then pass the result to your custom filters as an associative array (`$input` in this example). Your filter will add, remove, or edit information, then pass the modified result to the next custom filter or, if there are none left, to the final JSON output.

* `global $post;`

    This is a reference to the WP object with which you're currently working.

* `$input['myCustomField'] = $post->_my_custom_field;`

    This line saves the value of `$post->_my_custom_field` to the `$input` array. You can give the data any name you want - here, it's camel-cased as `myCustomField`.

* `return $input;`

    This line passes the modified array along to the next custom filter or to the final JSON output.

* `add_filter('rez_serialize_post', 'add_custom_field');`

    Rest-Easy needs to know where your custom filters are defined, so we're using WordPress's `add_filter` function add the `add_custom_field` method (whose name was defined in the first line of this example) to Rest-Easy's `rez_serialize_post` filter.


Now, whenever you load a post with `_my_custom_field` defined, you'll see your custom field in `jsonData.loop[0]._my_custom_field`!

## Core Concepts
(This is under-the-hood information - don't stress about this if you're not actually developing Rest-Easy!)

To avoid infinite loops in page serialization, Rest-Easy uses two main concepts: __builders__ and  __serializers__.

A __builder__ will run once on a page. It combines the output of several serializers and returns that data as an associative array, which is then JSON-encoded to form `jsonData`.

(Note that builders are also the only functions that can gather related posts - if anything else could do so, related posts would keep building on top of themselves without staying one level deep like they do in the Loop builder.)

A __serializer__ will take one piece of data from Wordpress and translate it into an associative array. For example, a serializer will take a post and turn it into an array with that post's title, content, permalink, and so on.

Rest-Easy's entry point is `rest-easy.php`, where it:

1. Runs the serializers in `builders.php`'s `rez_build_all_data` function
1. Determines how to send the requested output to the user by:
    * checking the request's `CONTENT_TYPE` and query strings for a JSON request, echoing the `jsonData` object if one was found
    * dumping the `jsonData` object onto the page with `wp_localize_script` otherwise

## Reference

### Filters
Add custom filters to build your own data:

```php
function custom_function_name($input){
    // modify $input here...
    return $input;
}
add_filter('desired_rest_easy_filter', 'custom_function_name');
```

Default values are shown below.

#### Builder Filters
Builders run once per page. They're designed to collect serialized data, add some high-level site/meta information, and output the resulting JSON object. Most of the time, you'll only use builders when adding very general site information - region detecting, custom site-wide taglines, etc.

* `rez_build_all_data` - Highest level data builder - this is the top-level structure of the resulting JSON object. Returns:
    ```php
    array(
        // key      => filter
        'site'      => rez_build_site_data,
        'meta'      => rez_build_meta_data,
        'loop'      => rez_build_loop_data
    )
    ```

##### Site Data
* `rez_build_site_data` - Builds general information about the site:
    ```php
    array(
        'themeUrl'      => 'URL of current WordPress theme',
        'url'           => 'URL of site'
        'name'          => 'Site name',
        'description'   => 'Site description',
        'menus'         => array(
            // Array of all menus on the site
        ),
        'isMobile'      => 'Boolean - result of wp_is_mobile()'
    )
    ```

##### Meta Data
* `rez_build_meta_data` - Builds meta information about the current page.
    ```php
    array(
        'self'          => 'permalink to current page',
        'is404'         => /* bool - did this request return a 404 error? */,
        'nextPage'      => 'permalink to next page in pagination, if present',
        'previousPage'  => 'permalink to previous page in pagination, if present'
    )
    ```

##### Loop Data
* `rez_build_loop_data` - Serializes all pages currently in [The Loop](https://codex.wordpress.org/The_Loop).
    ```php
    array(
        // Array of serialized posts, pages, etc.
        // By default, each element in the array will be the result of
        // combining 'rez_serialize_object' and 'rez_gather_related'
    )
    ```

#### Serializer Filters
Serializers are designed to take any WordPress object and translate it into JSON data. Serializers should be customized when you want to change the information that comes back from a single post, page, media item, etc. Post authors, media upload dates, and custom meta fields are great candidates for custom serializers.

##### Serialize Object
* `rez_serialize_object` - Generic serializer. Knows how to serialize any object.
    ```php
    * Runs rez_serialize_attachment filter if a media attachment
    * Runs rez_serialize_menu filter if a menu
    * Runs rez_serialize_nav_item filter if a menu item
    * Runs rez_serialize_post filter, then adds `_wshop_product_id` as `productId`, if a `wps-product` (see https://github.com/funkhaus/wp-shopify)
    * Runs rez_serialize_post filter if any other object type
    ```

##### Serialize Attachment
* `rez_serialize_attachment` - Serializes a media attachment:
    ```php
    array(
        'ID'                => /* int - attachment ID */,
        'title'             => 'title of attachment',
        'alt'               => 'alt text - looks for Alt Text, then Caption, then attachment title',
        'caption'           => 'caption from WordPress',
        'description'       => 'description from WordPress',

        // This section only runs if the Funky Colors plugin is installed
        'primaryColor'      => 'primary image color from Funky Colors',
        'secondaryColor'    => 'secondary image color from Funky Colors'
        // End Funky-Colors-only section

        'postType'          => 'post type',
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

##### Serialize Menu
* `rez_serialize_menu` - Serializes a menu and its items:
    ```php
    array(
        'name'      => 'menu name',
        'slug'      => 'menu slug',
        'postType'  => 'post type',
        'items'     => array(
            // Array of all items in this menu run through `rez_serialize_object` filter
        )
    )
    ```

##### Serialize Nav Item
* `rez_serialize_nav_item` - Serializes a menu item:
    ```php
    array(
        'title'         => 'menu item title',
        'classes'       => 'menu item classes',
        'permalink'     => 'permalink to target',
        'relativePath'  => 'relative path to target',
        'isExternal'    => /* bool - true if type label == 'Custom Link' */,
        'ID'            => 'int - menu item ID',
        'children'      => 'object - results of serialize_nav_menu on submenus',
        'postType'      => 'post type'
    )
    ```

##### Serialize Post
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
            // $post->this_will_be_included_automatically
            // $post->_this_will_not
        ),
        'date'          => /* int - Unix timestamp of post date */,
        'attachedMedia' => 'serialized array of media uploaded to this page',
        'featuredAttachment'    => 'serialized featured image',
        'isFront'       => /* boolean - is this the front page? */,
        'isBlog'        => /* boolean - is this the page for posts? */,
        'isCategory'    => /* boolean - is this a category archive page? */,
        'terms'         => 'Array of all terms this post contains',
        'postType'      => 'post type'
    )
    ```

##### Gather Related
* `rez_gather_related($related, $target_post)` - Gets related data for a given object:
    ```php
    $related == array(
        'featuredAttachment'    => 'the serialized featured attachment, if this object has one',
        'children'  => array(
            // children of this page, if applicable, serialized with rez_serialize_post
        ),
        'parent'    => /* object - the parent of this page, serialized with rez_serialize_post */,
        'next'      => /* object - the next page in menu order, if applicable, serialized with rez_serialize_post */,
        'prev'      => /* object - the previous page in menu order, if applicable, serialized with rez_serialize_post */
    );

    $target_post == /* The target $post object */
    ```

### Utility functions
* `rez_get_next_page_id($target_post)` - Get the ID of the page/post following the `$target_post`.
* `rez_get_previous_page_id($target_post)` - Get the ID of the page/post before the `$target_post`.

## Integrations
Rest-Easy is built to work well with other [Funkhaus](http://funkhaus.us) plugins:
* [Funky Colors](https://github.com/funkhaus/funky-colors), which determines an image's main colors
* [WP-Shopify](https://github.com/funkhaus/wp-shopify), which simplifies connecting Shopify to a Wordpress store

--------

__Rest-Easy__

http://funkhaus.us

Version: 1.42

* 1.42 - Added parent to related post objects
* 1.41 - Added postType field
* 1.40 - Fixed a utils bug
* 1.39 - Fixed `rez_gather_related` functionality on repeated calls
* 1.38 - Added `isMobile` to site builder
* 1.37 - Consistent casing
* 1.36 - Added site URL to default site builder
* 1.35 - Added [WP-Shopify](https://github.com/funkhaus/wp-shopify) support
