=== Custom Fields Creator === 

Contributors: reflectionmedia, madalin.ungureanu
Donate link: http://www.cozmoslabs.com/wordpress-creation-kit/custom-fields-creator/
Tags: custom field, custom fields, custom fields creator, meta box, meta boxes, repeater fields, post meta, repeater
Requires at least: 3.1
Tested up to: 3.4
Stable tag: 1.0.2

WCK Custom Fields Creator - easily create custom meta boxes for WordPress. It supports normal custom fields and custom fields repeater groups.
 
== Description ==

WCK Custom Fields Creator offers an UI for setting up custom meta boxes for your posts, pages or custom post types. Uses standard custom fields to store data. 

= Features =

* Easy to create custom fields for any post type.
* Support for Repeater Fields and Repeater Groups.
* Drag and Drop to sort the Repeater Fields.
* Support for all input fields: text, textarea, select, checkbox, radio.
* Image / File upload supported via the WordPress Media Uploader.
* Possibility to target only certain page-templates, target certain custom post types and even unique ID's.
* All data handling is done with ajax
* Data is saved as postmeta

= Website =
http://www.cozmoslabs.com/wordpress-creation-kit/

= Announcement Post and Video =
http://www.cozmoslabs.com/3747-wordpress-creation-kit-a-sparkling-new-custom-field-taxonomy-and-post-type-creator/

= Documentation =
http://www.cozmoslabs.com/wordpress-creation-kit/custom-fields-creator/

= Bug Submission and Forum Support =
http://www.cozmoslabs.com/forums/forum/wordpresscreationkit/

= Please Vote and Enjoy =
Your votes really make a difference! Thanks.



== Installation ==

1. Upload the wck-cfc folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Then navigate to WCK => Custom Fields Creator tab and start creating your meta boxes

== Frequently Asked Questions ==

= How do I display my custom fields in the frontend? =

Let's consider we have a meta box with the following arguments:
- Meta name: books
- Post Type: post
And we also have two fields defined:
- A text custom field with the Field Title: Book name
- And another text custom field with the Field Title: Author name

You will notice that slugs will automatically be created for the two text fields. For 'Book name' the slug will be 'book-name' and for 'Author name' the slug will be 'author-name'

Let's see what the code for displaying the meta box values in single.php of your theme would be:

`<?php $books = get_post_meta( $post->ID, 'books', true ); 
foreach( $books as $book){
	echo $book['book-name'] . '<br/>';
	echo $book['author-name'] . '<br/>';
}?>`

So as you can see the Meta Name 'books' is used as the $key parameter of the function get_post_meta() and the slugs of the text fields are used as keys for the resulting array. Basically CFC stores the entries as custom fields in a multidimensional array. In our case the array would be:

`<?php array( array( "book-name" => "The Hitchhiker's Guide To The Galaxy", "author-name" => "Douglas Adams" ),  array( "book-name" => "Ender's Game", "author-name" => "Orson Scott Card" ) );?>`

This is true even for single entries.

== Screenshots ==
1. List of Meta boxes
2. Meta box arguments
3. Meta box with custom fields
4. Some defined custom fields

== Changelog ==

= 1.0.2 =
* Major changes to the upload field. Now it stores the attachments ID instead of the url of the file. IMPORTANT: in the backend backwards compatibility has been taken care of but on the frontend the responsibility is yours.
* Added possibility to choose whether to attach or not the upload to the post.
* Various UI improvements.
* Fixed bug when CFC box had no title.
* Fixed label bug on edit form.
* Fixed bug that broke sorting after updating an element.
* Other small bug fixes and improvements.


= 1.0.1 =
* Added link to website and documentation in readme.
* Added link to support forum in readme.
* Other small readme improvments.