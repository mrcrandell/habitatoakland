<?php
/*
Author: Eddie Machado
URL: http://themble.com/bones/

This is where you can drop your custom functions or
just edit things like thumbnail sizes, header images,
sidebars, comments, etc.
*/

// Load Vendor
require __DIR__.'/vendor/autoload.php';
// Load Dotenv
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
// Load Contact Contact
require_once __DIR__.'/vendor/constantcontact/constantcontact/src/Ctct/autoload.php';

// LOAD BONES CORE (if you remove this, the theme will break)
require_once( 'library/bones.php' );

// CUSTOMIZE THE WORDPRESS ADMIN (off by default)
require_once( 'library/admin.php' );

/*********************
LAUNCH BONES
Let's get everything up and running.
*********************/

function bones_ahoy() {

  //Allow editor style.
  add_editor_style( get_stylesheet_directory_uri() . '/library/css/editor-style.css' );

  // let's get language support going, if you need it
  load_theme_textdomain( 'bonestheme', get_template_directory() . '/library/translation' );

  // USE THIS TEMPLATE TO CREATE CUSTOM POST TYPES EASILY
  require_once( 'library/custom-post-type.php' );

  // launching operation cleanup
  add_action( 'init', 'bones_head_cleanup' );
  // A better title
  add_filter( 'wp_title', 'rw_title', 10, 3 );
  // remove WP version from RSS
  add_filter( 'the_generator', 'bones_rss_version' );
  // remove pesky injected css for recent comments widget
  add_filter( 'wp_head', 'bones_remove_wp_widget_recent_comments_style', 1 );
  // clean up comment styles in the head
  add_action( 'wp_head', 'bones_remove_recent_comments_style', 1 );
  // clean up gallery output in wp
  add_filter( 'gallery_style', 'bones_gallery_style' );

  // enqueue base scripts and styles
  add_action( 'wp_enqueue_scripts', 'bones_scripts_and_styles', 999 );
  // ie conditional wrapper

  // launching this stuff after theme setup
  bones_theme_support();

  // adding sidebars to Wordpress (these are created in functions.php)
  add_action( 'widgets_init', 'bones_register_sidebars' );

  // cleaning up random code around images
  add_filter( 'the_content', 'bones_filter_ptags_on_images' );
  // cleaning up excerpt
  add_filter( 'excerpt_more', 'bones_excerpt_more' );

} /* end bones ahoy */

// let's get this party started
add_action( 'after_setup_theme', 'bones_ahoy' );


/************* OEMBED SIZE OPTIONS *************/

if ( ! isset( $content_width ) ) {
	$content_width = 680;
}

/************* THUMBNAIL SIZE OPTIONS *************/

// Thumbnail sizes
add_image_size( 'bones-thumb-600', 600, 150, true );
add_image_size( 'bones-thumb-300', 300, 100, true );

/*
to add more sizes, simply copy a line from above
and change the dimensions & name. As long as you
upload a "featured image" as large as the biggest
set width or height, all the other sizes will be
auto-cropped.

To call a different size, simply change the text
inside the thumbnail function.

For example, to call the 300 x 100 sized image,
we would use the function:
<?php the_post_thumbnail( 'bones-thumb-300' ); ?>
for the 600 x 150 image:
<?php the_post_thumbnail( 'bones-thumb-600' ); ?>

You can change the names and dimensions to whatever
you like. Enjoy!
*/

add_filter( 'image_size_names_choose', 'bones_custom_image_sizes' );

function bones_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'bones-thumb-600' => __('600px by 150px'),
        'bones-thumb-300' => __('300px by 100px'),
    ) );
}

/*
The function above adds the ability to use the dropdown menu to select
the new images sizes you have just created from within the media manager
when you add media to your content blocks. If you add more image sizes,
duplicate one of the lines in the array and name it according to your
new image size.
*/

/************* THEME CUSTOMIZE *********************/

/*
  A good tutorial for creating your own Sections, Controls and Settings:
  http://code.tutsplus.com/series/a-guide-to-the-wordpress-theme-customizer--wp-33722

  Good articles on modifying the default options:
  http://natko.com/changing-default-wordpress-theme-customization-api-sections/
  http://code.tutsplus.com/tutorials/digging-into-the-theme-customizer-components--wp-27162

  To do:
  - Create a js for the postmessage transport method
  - Create some sanitize functions to sanitize inputs
  - Create some boilerplate Sections, Controls and Settings
*/

function bones_theme_customizer($wp_customize) {
  // $wp_customize calls go here.
  //
  // Uncomment the below lines to remove the default customize sections

  // $wp_customize->remove_section('title_tagline');
  // $wp_customize->remove_section('colors');
  // $wp_customize->remove_section('background_image');
  // $wp_customize->remove_section('static_front_page');
  // $wp_customize->remove_section('nav');

  // Uncomment the below lines to remove the default controls
  // $wp_customize->remove_control('blogdescription');

  // Uncomment the following to change the default section titles
  // $wp_customize->get_section('colors')->title = __( 'Theme Colors' );
  // $wp_customize->get_section('background_image')->title = __( 'Images' );
}

add_action( 'customize_register', 'bones_theme_customizer' );

/************* ACTIVE SIDEBARS ********************/

// Sidebars & Widgetizes Areas
function bones_register_sidebars() {
	register_sidebar(array(
		'id' => 'sidebar1',
		'name' => __( 'Sidebar 1', 'bonestheme' ),
		'description' => __( 'The first (primary) sidebar.', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	/*
	to add more sidebars or widgetized areas, just copy
	and edit the above sidebar code. In order to call
	your new sidebar just use the following code:

	Just change the name to whatever your new
	sidebar's id is, for example:

	register_sidebar(array(
		'id' => 'sidebar2',
		'name' => __( 'Sidebar 2', 'bonestheme' ),
		'description' => __( 'The second (secondary) sidebar.', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	To call the sidebar in your template, you can just copy
	the sidebar.php file and rename it to your sidebar's name.
	So using the above example, it would be:
	sidebar-sidebar2.php

	*/
} // don't remove this bracket!


/************* COMMENT LAYOUT *********************/

// Comment Layout
function bones_comments( $comment, $args, $depth ) {
   $GLOBALS['comment'] = $comment; ?>
  <div id="comment-<?php comment_ID(); ?>" <?php comment_class('cf'); ?>>
    <article  class="cf">
      <header class="comment-author vcard">
        <?php
        /*
          this is the new responsive optimized comment image. It used the new HTML5 data-attribute to display comment gravatars on larger screens only. What this means is that on larger posts, mobile sites don't have a ton of requests for comment images. This makes load time incredibly fast! If you'd like to change it back, just replace it with the regular wordpress gravatar call:
          echo get_avatar($comment,$size='32',$default='<path_to_url>' );
        */
        ?>
        <?php // custom gravatar call ?>
        <?php
          // create variable
          $bgauthemail = get_comment_author_email();
        ?>
        <img data-gravatar="http://www.gravatar.com/avatar/<?php echo md5( $bgauthemail ); ?>?s=40" class="load-gravatar avatar avatar-48 photo" height="40" width="40" src="<?php echo get_template_directory_uri(); ?>/library/images/nothing.gif" />
        <?php // end custom gravatar call ?>
        <?php printf(__( '<cite class="fn">%1$s</cite> %2$s', 'bonestheme' ), get_comment_author_link(), edit_comment_link(__( '(Edit)', 'bonestheme' ),'  ','') ) ?>
        <time datetime="<?php echo comment_time('Y-m-j'); ?>"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php comment_time(__( 'F jS, Y', 'bonestheme' )); ?> </a></time>

      </header>
      <?php if ($comment->comment_approved == '0') : ?>
        <div class="alert alert-info">
          <p><?php _e( 'Your comment is awaiting moderation.', 'bonestheme' ) ?></p>
        </div>
      <?php endif; ?>
      <section class="comment_content cf">
        <?php comment_text() ?>
      </section>
      <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
    </article>
  <?php // </li> is added by WordPress automatically ?>
<?php
} // don't remove this bracket!


/*
This is a modification of a function found in the
twentythirteen theme where we can declare some
external fonts. If you're using Google Fonts, you
can replace these fonts, change it in your scss files
and be up and running in seconds.
*/
function bones_fonts() {
  wp_enqueue_style('googleFonts', 'https://fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic|Crimson+Text:400,400italic,600,600italic,700italic,700');
}
add_action('wp_enqueue_scripts', 'bones_fonts');

// Add Social Media Shortcode
function social_media_shortcode() {
    // Code
    return '<ul class="list-inline margin-bottom-25">
            <li><a href="https://www.facebook.com/HabitatforHumanityOC?ref=ts&fref=ts" target="_blank"><i class="fa fa-facebook-square fa-2x"></i></a></li>
            <li><a href="https://twitter.com/HabitatOakldCty?lang=en" target="_blank"><i class="fa fa-twitter-square fa-2x"></i></a></li>
            <li><a href="https://www.linkedin.com/company/habitat-for-humanity-oakland-county" target="_blank"><i class="fa fa-linkedin-square fa-2x"></i></a></li>
            <li><a href="https://plus.google.com/u/0/b/103131641179733973708/?pageId=103131641179733973708" target="_blank"><i class="fa fa-google-plus-square fa-2x"></i></a></li>
            <li><a href="https://www.instagram.com/habitatoaklandcty/" target="_blank"><i class="fa fa-instagram fa-2x"></i></a></li>
            <li><a href="https://www.flickr.com/photos/132443299@N02/" target="_blank"><i class="fa fa-flickr fa-2x"></i></a></li>
            <li><a href="https://www.youtube.com/channel/UCushl9LrGy6mFhPjbEcXbpA" target="_blank"><i class="fa fa-youtube-square fa-2x" aria-hidden="true"></i></a></li>
        </ul>';
}
add_shortcode( 'social_media', 'social_media_shortcode' );

// Add Pontiac Restore Social Media Shortcode
function pontiac_restore_social_media_shortcode() {
    // Code
    return '<ul class="list-inline margin-bottom-25">
            <li><a href="https://www.facebook.com/restorepontiac/?fref=ts" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
            <li><a href="https://twitter.com/RestorePontiac" target="_blank"><i class="fa fa-twitter-square"></i></a></li>
            <li><a href="http://www.linkedin.com/company/12903889?trk=tyah&trkInfo=clickedVertical%3Ashowcase%2CclickedEntityId%3A12903889%2Cidx%3A2-1-2%2CtarId%3A1475516898676%2Ctas%3AReStore%20Pontiac" target="_blank"><i class="fa fa-linkedin-square"></i></a></li>
            <li><a href="https://plus.google.com/u/0/b/112175553785285138336/?pageId=112175553785285138336" target="_blank"><i class="fa fa-google-plus-square"></i></a></li>
            <li><a href="https://www.youtube.com/channel/UCvRhWxE0j7Sw_JcCoucPZiw" target="_blank"><i class="fa fa-youtube-square" aria-hidden="true"></i></a></li>
        </ul>';
}
add_shortcode( 'pontiac_restore_social_media', 'pontiac_restore_social_media_shortcode' );

// Add Farmington Restore Social Media Shortcode
function farmington_restore_social_media_shortcode() {
    // Code
    return '<ul class="list-inline margin-bottom-25">
            <li><a href="https://www.facebook.com/restorefarmington/?fref=ts" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
            <li><a href="https://twitter.com/ReStoreFrmgton" target="_blank"><i class="fa fa-twitter-square"></i></a></li>
            <li><a href="http://www.linkedin.com/company/restore-farmington?trk=biz-brand-tree-co-name" target="_blank"><i class="fa fa-linkedin-square"></i></a></li>
            <li><a href="https://plus.google.com/u/0/b/103537744017941926285/" target="_blank"><i class="fa fa-google-plus-square"></i></a></li>
            <li><a href="https://www.youtube.com/channel/UCvRhWxE0j7Sw_JcCoucPZiw" target="_blank"><i class="fa fa-youtube-square" aria-hidden="true"></i></a></li>
        </ul>';
}
add_shortcode( 'farmington_restore_social_media', 'farmington_restore_social_media_shortcode' );

// Custom Excerpt
function my_excerpt($excerpt_length = 55, $id = false, $echo = true) {

    $text = '';

    if($id) {
        $the_post = & get_post( $my_id = $id );
        $text = ($the_post->post_excerpt) ? $the_post->post_excerpt : $the_post->post_content;
    } else {
        global $post;
        $text = ($post->post_excerpt) ? $post->post_excerpt : get_the_content('');
    }

    $text = strip_shortcodes( $text );
    $text = apply_filters('the_content', $text);
    $text = str_replace(']]>', ']]&gt;', $text);
    $text = strip_tags($text);

    $excerpt_more = ' ' . '...';
    $words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
    if ( count($words) > $excerpt_length ) {
        array_pop($words);
        $text = implode(' ', $words);
        $text = $text . $excerpt_more;
    } else {
        $text = implode(' ', $words);
    }
    if($echo)
        echo apply_filters('the_content', $text);
    else
        return $text;
}

function get_my_excerpt($excerpt_length = 55, $id = false, $echo = false) {
    return my_excerpt($excerpt_length, $id, $echo);
}

// Register custom navigation walker
require_once('wp_bootstrap_navwalker.php');

register_nav_menus( array(
    'primary' => __( 'Primary Menu', 'bones' ),
) );

//Page Slug Body Class
function add_slug_body_class( $classes ) {
  global $post;
  if ( isset( $post ) ) {
    $classes[] = $post->post_type . '-' . $post->post_name;
  }
  return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );

// Recaptcha Verification
require get_template_directory() . '/inc/recaptcha-verification.php';

// Form Database Instert
//require get_template_directory() . '/inc/form-insert.php';

// Newsletter Sign Up
require get_template_directory() . '/inc/shortcode-newsletter-signup.php';

// Homeownership Form
require get_template_directory() . '/inc/shortcode-homeownership-form.php';

// Company Team Building Form
require get_template_directory() . '/inc/shortcode-company-team-building-form.php';

// Golf Signup Form
require get_template_directory() . '/inc/shortcode-golf-signup-form.php';

// Rock the Block Form
require get_template_directory() . '/inc/shortcode-rock-the-block-form.php';

// Comment Customization
function wpb_move_comment_field_to_bottom( $fields ) {
    $comment_field = $fields['comment'];
    unset( $fields['comment'] );
    $fields['comment'] = $comment_field;
    return $fields;
}
add_filter( 'comment_form_fields', 'wpb_move_comment_field_to_bottom' );

// Recaptcha on Comments
function frontend_recaptcha_script() {
    wp_register_script("recaptcha", "https://www.google.com/recaptcha/api.js");
    wp_enqueue_script("recaptcha");
}
add_action("wp_enqueue_scripts", "frontend_recaptcha_script");
function verify_comment_captcha( $commentdata ) {
    if (isset($_POST['g-recaptcha-response'])) {
        $response = verifyCaptcha($_POST['g-recaptcha-response']);
        if ($response->success) {
            return $commentdata;
        } else {
            wp_die("<strong>ERROR</strong>: Please check the recaptcha box.", 'Recaptcha Error', array('response' => 200, 'back_link' => true));
        }
    } else {
        wp_die("<strong>ERROR</strong>: Please check the recaptcha box.", 'Recaptcha Error', array('response' => 200, 'back_link' => true));
    }
}
add_filter("preprocess_comment", "verify_comment_captcha", 10, 2 );

// Restore Event Buttons
function event_buttons_restore_shortcode( $attr ) {
    $restoreIdObj = get_category_by_slug('restores-of-oakland-county');
    $restoreId = $restoreIdObj->term_id;
    $eventsIdObj = get_category_by_slug('event');
    $eventsId = $eventsIdObj->term_id;

    // Get Events
    query_posts( array( 'category__and' => array( $restoreId, $eventsId ) ) );
    ob_start();
    get_template_part( 'event-buttons' );
    return ob_get_clean();
}
add_shortcode( 'event_buttons_restore', 'event_buttons_restore_shortcode' );

/* DON'T DELETE THIS CLOSING TAG */ ?>
