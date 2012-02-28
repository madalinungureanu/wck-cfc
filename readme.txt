=== WCK Custom Fields Creator === 

Contributors: reflectionmedia, madalin.ungureanu
Donate link: http://www.cozmoslabs.com/wordpress-creation-kit/custom=fields-creator/
Tags: custom fields creator, meata boxes, repeater fields, ajax, post meta, custom fields, repeater
Requires at least: 3.1
Tested up to: 3.3.1
Stable tag: 1.0

WCK Custom Fields Creator allows you to easily create custom meta boxes for WordPress without any programming knowledge. It supports repeater fields and uses AJAX to handle data.
 
== Description ==

WCK Custom Fields Creator offers an UI for setting up custom meta boxes for your posts, pages or custom post types.

Features:

* Support for Repeater Fields.
* Drag and Drop to sort the Repeater Fields.
* Support for all input fields: text, textarea, select, checkbox, radio.
* Image / File upload supported via the WordPress Media Uploader.
* Possibility to target only certain page-templates, target certain custom post types and even unique ID’s.
* All data handling is done with ajax
* Data is saved as postmeta

== Installation ==

1. Upload the wck-cfc folder to the '/wp-content/plugins/' directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Then navigate to WCK => Custom Fields Creator tab and start creating your meta boxes

== Frequently Asked Questions ==

= How do I display the contents of the meta box in the frontend? =

Let’s consider we have a meta box with the following arguments:
- Meta name: books
- Post Type: post
And we also have two fields deffined:
- A text field with the Field Title: Book name
- And another text field with the Field Title: Author name

You will notice that slugs will automatically be created for the two text fields. For “Book name” the slug will be “book-name” and for “Author name” the slug will be “author-name”

Let’s see what the code for displaying the meta box values in single.php of your theme would be:

`<?php $books = get_post_meta( $post->ID, 'books', true ); 
foreach( $books as $book){
	echo $book['book-name'];
	echo $book['author-name'];
}?>`

So as you can see the Meta Name “books” is used as the $key parameter of the funtion get_post_meta() and the slugs of the text fields are used as keys for the resulting array. Basically CFC stores the entries as post meta in a multidimensioanl array. In our case the array would be:

`<?php array( array( "book-name" => "The Hitchhiker's Guide To The Galaxy", "author-name" => "Douglas Adams" ),  array( "book-name" => "Ender's Game", "author-name" => "Orson Scott Card" ) );?>`

This is true even for single entries.

== Screenshots ==
1. List of Meta boxes: screenshot-1.jpg
2. Meta box arguments: screenshot-2.jpg
3. Meta box fields: screenshot-3.jpg
4. Some defined fields: screenshot-4.jpg