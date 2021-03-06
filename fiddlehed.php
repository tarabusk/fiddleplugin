<?php
/*
Plugin Name: FiddleHed
Description: Add features to FiddleHed Website
Author:      tarabusk
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

function fiddlehed_breadcrumbs () {

  /* === OPTIONS === */
  $text['home']     = 'Home'; // text for the 'Home' link
  $text['category'] = 'Archive by Category "%s"'; // text for a category page
  $text['search']   = 'Search Results for "%s" Query'; // text for a search results page
  $text['tag']      = 'Posts Tagged "%s"'; // text for a tag page
  $text['author']   = 'Articles Posted by %s'; // text for an author page
  $text['404']      = 'Error 404'; // text for the 404 page
  $text['page']     = 'Page %s'; // text 'Page N'
  $text['cpage']    = 'Comment Page %s'; // text 'Comment Page N'

  $wrap_before    = '<div class="fiddlehed-breadcrumbs" itemscope itemtype="http://schema.org/BreadcrumbList">'; // the opening wrapper tag
  $wrap_after     = '</div><!-- .breadcrumbs -->'; // the closing wrapper tag
  $sep            = '›'; // separator between crumbs
  $sep_before     = '<span class="sep">'; // tag before separator
  $sep_after      = '</span>'; // tag after separator
  $show_home_link = 1; // 1 - show the 'Home' link, 0 - don't show
  $show_on_home   = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
  $show_current   = 1; // 1 - show current page title, 0 - don't show
  $before         = '<span class="current">'; // tag before the current crumb
  $after          = '</span>'; // tag after the current crumb
  /* === END OF OPTIONS === */

  global $post;
  $home_url       = home_url('/');
  $link_before    = '<span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">';
  $link_after     = '</span>';
  $link_attr      = ' itemprop="item"';
  $link_in_before = '<span itemprop="name">';
  $link_in_after  = '</span>';
  $link           = $link_before . '<a href="%1$s"' . $link_attr . '>' . $link_in_before . '%2$s' . $link_in_after . '</a>' . $link_after;
  $frontpage_id   = get_option('page_on_front');
  $parent_id      = ($post) ? $post->post_parent : '';
  $sep            = ' ' . $sep_before . $sep . $sep_after . ' ';
  $home_link      = $link_before . '<a href="' . $home_url . '"' . $link_attr . ' class="home">' . $link_in_before . $text['home'] . $link_in_after . '</a>' . $link_after;

  if (is_home() || is_front_page()) {

    if ($show_on_home) echo $wrap_before . $home_link . $wrap_after;

  } else {

    echo $wrap_before;
    if ($show_home_link) echo $home_link;

    if ( is_category() ) {
      $cat = get_category(get_query_var('cat'), false);
      if ($cat->parent != 0) {
        $cats = get_category_parents($cat->parent, TRUE, $sep);
        $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
        $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
        if ($show_home_link) echo $sep;
        echo $cats;
      }
      if ( get_query_var('paged') ) {
        $cat = $cat->cat_ID;
        echo $sep . sprintf($link, get_category_link($cat), get_cat_name($cat)) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
      } else {
        if ($show_current) echo $sep . $before . sprintf($text['category'], single_cat_title('', false)) . $after;
      }

    } elseif ( is_search() ) {
      if (have_posts()) {
        if ($show_home_link && $show_current) echo $sep;
        if ($show_current) echo $before . sprintf($text['search'], get_search_query()) . $after;
      } else {
        if ($show_home_link) echo $sep;
        echo $before . sprintf($text['search'], get_search_query()) . $after;
      }

    } elseif ( is_day() ) {
      if ($show_home_link) echo $sep;
      echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . $sep;
      echo sprintf($link, get_month_link(get_the_time('Y'), get_the_time('m')), get_the_time('F'));
      if ($show_current) echo $sep . $before . get_the_time('d') . $after;

    } elseif ( is_month() ) {
      if ($show_home_link) echo $sep;
      echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y'));
      if ($show_current) echo $sep . $before . get_the_time('F') . $after;

    } elseif ( is_year() ) {
      if ($show_home_link && $show_current) echo $sep;
      if ($show_current) echo $before . get_the_time('Y') . $after;

    } elseif (is_singular( 'memberpressproduct' ) ) {
      $post_type = get_post_type_object(get_post_type());
      echo $sep . $before . $post_type->label . $after;

    } elseif ( is_single() && !is_attachment() ) {
      if ($show_home_link) echo $sep;
      if ( get_post_type() != 'post' ) {
        $post_type = get_post_type_object(get_post_type());
        $slug = $post_type->rewrite;
        printf($link, $home_url . $slug['slug'] . '/', $post_type->labels->singular_name);
        if ($show_current) echo $sep . $before . get_the_title() . $after;
      } else {
        $cat = get_the_category(); $cat = $cat[0];
        $cats = get_category_parents($cat, TRUE, $sep);
        if (!$show_current || get_query_var('cpage')) $cats = preg_replace("#^(.+)$sep$#", "$1", $cats);
        $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
        echo $cats;
        if ( get_query_var('cpage') ) {
          echo $sep . sprintf($link, get_permalink(), get_the_title()) . $sep . $before . sprintf($text['cpage'], get_query_var('cpage')) . $after;
        } else {
          if ($show_current) echo $before . get_the_title() . $after;
        }
      }

    // custom post type
    } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
      $post_type = get_post_type_object(get_post_type());
      if ( get_query_var('paged') ) {
        echo $sep . sprintf($link, get_post_type_archive_link($post_type->name), $post_type->label) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
      } else {
        if ($show_current) echo $sep . $before . $post_type->label . $after;
      }

    } elseif ( is_attachment() ) {
      if ($show_home_link) echo $sep;
      $parent = get_post($parent_id);
      $cat = get_the_category($parent->ID); $cat = $cat[0];
      if ($cat) {
        $cats = get_category_parents($cat, TRUE, $sep);
        $cats = preg_replace('#<a([^>]+)>([^<]+)<\/a>#', $link_before . '<a$1' . $link_attr .'>' . $link_in_before . '$2' . $link_in_after .'</a>' . $link_after, $cats);
        echo $cats;
      }
      printf($link, get_permalink($parent), $parent->post_title);
      if ($show_current) echo $sep . $before . get_the_title() . $after;

    } elseif ( is_page() && !$parent_id ) {
      if ($show_current) echo $sep . $before . get_the_title() . $after;

    } elseif ( is_page() && $parent_id ) {
      if ($show_home_link) echo $sep;
      if ($parent_id != $frontpage_id) {
        $breadcrumbs = array();
        while ($parent_id) {
          $page = get_page($parent_id);
          if ($parent_id != $frontpage_id) {
            $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
          }
          $parent_id = $page->post_parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        for ($i = 0; $i < count($breadcrumbs); $i++) {
          echo $breadcrumbs[$i];
          if ($i != count($breadcrumbs)-1) echo $sep;
        }
      }
      if ($show_current) echo $sep . $before . get_the_title() . $after;

    } elseif ( is_tag() ) {
      if ( get_query_var('paged') ) {
        $tag_id = get_queried_object_id();
        $tag = get_tag($tag_id);
        echo $sep . sprintf($link, get_tag_link($tag_id), $tag->name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
      } else {
        if ($show_current) echo $sep . $before . sprintf($text['tag'], single_tag_title('', false)) . $after;
      }

    } elseif ( is_author() ) {
      global $author;
      $author = get_userdata($author);
      if ( get_query_var('paged') ) {
        if ($show_home_link) echo $sep;
        echo sprintf($link, get_author_posts_url($author->ID), $author->display_name) . $sep . $before . sprintf($text['page'], get_query_var('paged')) . $after;
      } else {
        if ($show_home_link && $show_current) echo $sep;
        if ($show_current) echo $before . sprintf($text['author'], $author->display_name) . $after;
      }

    } elseif ( is_404() ) {
      if ($show_home_link && $show_current) echo $sep;
      if ($show_current) echo $before . $text['404'] . $after;

    } elseif ( has_post_format() && !is_singular() ) {
      if ($show_home_link) echo $sep;
      echo get_post_format_string( get_post_format() );
    }

    echo $wrap_after;

  }
} // end of fiddlehed_breadcrumbs()


/************************************/
/* Customizing Editor WordPress     */
/************************************/

function fiddlehed_mce4_options($init) {
  $default_colours = '
  "000000", "Black",
  "993300", "Burnt orange",
  "333300", "Dark olive",
  "003300", "Dark green",
  "003366", "Dark azure",
  "000080", "Navy Blue",
  "333399", "Indigo",
  "333333", "Very dark gray",
  "800000", "Maroon",
  "FF6600", "Orange",
  "808000", "Olive",
  "008000", "Green",
  "008080", "Teal",
  "0000FF", "Blue",
  "666699", "Grayish blue",
  "808080", "Gray",
  "FF0000", "Red",
  "FF9900", "Amber",
  "99CC00", "Yellow green",
  "339966", "Sea green",
  "33CCCC", "Turquoise",
  "3366FF", "Royal blue",
  "800080", "Purple",
  "999999", "Medium gray",
  "FF00FF", "Magenta",
  "FFCC00", "Gold",
  "FFFF00", "Yellow",
  "00FF00", "Lime",
  "00FFFF", "Aqua",
  "00CCFF", "Sky blue",
  "993366", "Red violet",
  "FFFFFF", "White",
  "FF99CC", "Pink",
  "FFCC99", "Peach",
  "FFFF99", "Light yellow",
  "CCFFCC", "Pale green",
  "CCFFFF", "Pale cyan",
  "99CCFF", "Light sky blue",
  "CC99FF", "Plum"
  ';

  $custom_colours = '
      "990034", "Level Title FiddleHed",
      "666666", "Gray FiddleHed",
      "5a5a5f", "Gray Modules FiddleHed",
      "427e7c", "Greenish FiddleHed",

      "990034", "Colored note - Red FiddleHed",
      "6a7918", "Colored note - Green FiddleHed",
      "5085b6", "Colored note - Blue Greenish FiddleHed",
      "6b3a73", "Colored note - Purple Greenish FiddleHed"


  ';

    $init['textcolor_map'] = '['.$custom_colours.','.$default_colours.']';
    $init['textcolor_rows'] = 6;
    return $init;
}
add_filter('tiny_mce_before_init', 'fiddlehed_mce4_options');

/************************************************/
/* Adding Links to previous and next Page       */
/************************************************/
/*
function fiddleHedSiblings($link) {
    global $post;
    $siblings = get_pages('child_of='.$post->post_parent.'&parent='.$post->post_parent);
    foreach ($siblings as $key=>$sibling){
        if ($post->ID == $sibling->ID){
            $ID = $key;
        }
    }
    $closest = array('before'=>get_permalink($siblings[$ID-1]->ID),'after'=>get_permalink($siblings[$ID+1]->ID));

    if ($link == 'before' || $link == 'after') { echo $closest[$link]; } else { return $closest; }
}
*/

/************************************************/
/* Adding option Page                           */
/************************************************/

if( function_exists('acf_add_options_page') ) {

	acf_add_options_page('Options');

}

/************************************************/
/* Adding GOOGLE ANALYTIC CODE                  */
/************************************************/

function fiddleHed_google_analytics() {
  if (class_exists('acf') ) {
    $ga_code =  get_field ('ga_tracking_code', 'option');
    if ($ga_code) { ?>
  	<script>
  		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  		ga('create', '<?php echo $ga_code; ?>', 'auto');
  		ga('send', 'pageview');

  		</script>
  <?php
    }
  }
}
add_action( 'wp_head', 'fiddleHed_google_analytics', 10 );

/************************************************/
/* Adding Code between head tags                */
/************************************************/

function fiddleHed_add_code_head() {
  if (class_exists('acf') ) {
    $head_code =  get_field ('option_add_scripts', 'option');
    if ($head_code) {
        echo $head_code;
    }
  }
}
add_action( 'wp_head', 'fiddleHed_add_code_head', 10 );

/************************************************/
/* Create a role for beta tester  (on plugin activation)
/************************************************/

function fiddleHed_add_roles_on_plugin_activation() {
       add_role( 'beta_tester', 'Beta Tester',
                array( 'read' => true,
                       'read_private_posts' => 'true',
                       'read_private_pages' => 'true',
                       'level_0' => true ) );
      // allow subscriber to read private post and pages
      // $subRole = get_role( 'subscriber' );
      // $subRole->add_cap( 'read_private_posts' );
      // $subRole->add_cap( 'read_private_pages' );
  }
  register_activation_hook( __FILE__, 'fiddleHed_add_roles_on_plugin_activation' );

  /************************************************/
  /* Redirect those who can not edit posts to home page after login
  /************************************************/
/*
  function fiddleHed_loginRedirect( $redirect_to, $request_redirect_to, $user ) {
    if ( is_a( $user, 'WP_User' ) && $user->has_cap( 'edit_posts' ) === false ) {
      return get_site_url ();
    }
    return $redirect_to; }

  add_filter( 'login_redirect', 'fiddleHed_loginRedirect', 10, 3 );
*/
/************************************************/
/* Redirect those who are not authorized to read private post to login form
/************************************************/
/* might be interesting to redirect them on anoter page explaining the process later on */


add_filter( 'login_message', 'fiddleHed_my_private_page_login_message' );
function fiddleHed_my_private_page_login_message( $message ) {
	if ( isset( $_REQUEST['private'] ) && $_REQUEST['private'] == 1 )
		$message .= sprintf( '<p class="message">%s</p>', __( 'The page you tried to visit is restricted. Please log in or register to continue.' ) );
	return $message;
}


/************************************************/
/* Change Login Message
/************************************************/

function fiddleHed_login_message() {
    if (class_exists('acf') ) {
      $strMessage = get_field ('text_login', 'option');
      if ( $strMessage ){
          return "<p>".$strMessage."</p>";
      } else {
          return '';
      }
    }
}

add_filter( 'login_message',  'fiddleHed_login_message' );


/************************************************/
/*  Possibility to add code to footer
/************************************************/

function fiddleHed_add_code_footer() {
  if (class_exists('acf') ) {
    $strMessage = get_field ('code_footer', 'option');
  } else {
    $strMessage = '';
  }
  echo $strMessage;
}
add_action('wp_footer',  'fiddleHed_add_code_footer');


/**
 * Returns the translated role of the current user.
 * No role, get false.
 *
 * @return string The translated name of the current role.
 **/
function get_current_user_role() {
    global $wp_roles;

    $current_user = wp_get_current_user();
    $roles = $current_user->roles;
    $role = array_shift( $roles );

    return isset( $wp_roles->role_names[ $role ] ) ? translate_user_role( $wp_roles->role_names[ $role ] ) : FALSE;
}

/**
 * Adding css rules to nav menus  with info of status page (used for first use of private page, before membership) AND current user role AND Current page taxonomy
 *
 *
 * @Add new CSS Rules to each nav menu.
 **/
add_filter('nav_menu_css_class' , __NAMESPACE__ . '\\nav_menu_add_post_status_class' , 10 , 2);
function nav_menu_add_post_status_class($classes, $item){

  //if(current_user_can('memberpress_product_authorized_123')) { where 123 is the membership id

  $terms = get_the_terms( $item->object_id, 'fiddlehed-restriction' );
  $class_restriction = 'no-fiddlehed-restrict';
  if ( $terms && ! is_wp_error( $terms ) ) :
      $restriction_slug = array();
      foreach ( $terms as $term ) {
          $restriction_slug[] = $term->slug;
      }
      $class_restriction = join( " ", $restriction_slug );
    endif;

    $post_status = get_post_status($item->object_id);
  	$user_role   =  preg_replace('/\s+/', '', get_current_user_role());
    if ($user_role == '') {
      $user_role = 'no-role';
    } else {
      $user_role = $user_role.'-role';
    }
    $classes[] = $post_status.' '.$user_role. ' '.$class_restriction;
    return $classes;
}

/************************************************/
/*  Possibility to redirect unauthorized user to another page when landing on a private page
/************************************************/

function fiddleHed_my_private_page_404() {
	$queried_object = get_queried_object();
	if ( isset( $queried_object->post_status ) && 'private' == $queried_object->post_status && !is_user_logged_in() ) {
    if (class_exists('acf')) {
      $url_redirection_private = get_field ('url_redirection_private', 'option');
      if ($url_redirection_private && $url_redirection_private != '') {
        wp_safe_redirect( add_query_arg( 'private', '1', $url_redirection_private ));
      }
     //exit;
    }
		//exit;
	}
}
add_action( 'wp', 'fiddleHed_my_private_page_404' );

/************************************************/
/*  add shortcode to display SUBSCRIBE button
/************************************************/

function fiddlehed_subscribe_sortcode ( $atts ) {

  if (class_exists('acf') && get_field ('subscribe_button_link', 'options')) {
    return '<a href="'.get_field ("subscribe_button_link", 'options' ) .'" class="button-subscribe">SUBSCRIBE</a>';
  }


}
add_shortcode( 'subscribeFiddlehed',  'fiddlehed_subscribe_sortcode' );


/************************************************/
/*  add shortcode to display customized button
/************************************************/


add_shortcode( 'ButtonFiddlehed', 'fiddlehed_customized_shortcode' );

function fiddlehed_customized_shortcode($atts = [], $content = null, $tag = '')
{
    // normalize attribute keys, lowercase
    $atts = array_change_key_case((array)$atts, CASE_LOWER);

    // override default attributes with user attributes
    $wporg_atts = shortcode_atts([
                                     'url' => 'https://fiddlehed.com/plans/subscribe/',
                                     'txt' => 'SUBSCRIBE',
                                 ], $atts, $tag);

    if ($wporg_atts['url'] != '' && (class_exists('acf'))) {
        return '<a href="'. $wporg_atts['url'] .'" class="button-subscribe">'.$wporg_atts['txt'].'</a>';
    }
}


/************************************************/
/*  add Custom taxonomy FiddleHed Access Restrictions to pages - Enable categorization for page restriction for member purpose
/************************************************/

function fiddlehed_member_init() {
	// create a new taxonomy
	register_taxonomy(
		'fiddlehed-restriction',

		'page',
		array(
			'label' => __( 'FiddleHed Access Restrictions' ),
			'rewrite' => array( 'slug' => 'access-restricition' ),
			'hierarchical' => true

		)
	);
}
add_action( 'init',   'fiddlehed_member_init' );


/************************************************/
/*  Redirect logged in users to alternative home page
/************************************************/

function fiddlehed__redirect()
    {
      if (is_front_page () && is_user_logged_in() ) {
        wp_redirect ( home_url("/home-fiddlehed") );
        exit;
      }
}
add_action( 'template_redirect', 'fiddlehed__redirect' );

/************************************************/
/*  add shortcode to display Login button
/************************************************/

function fiddlehed_button_login ( $atts ) {

  if (class_exists('acf') ) {
    return '<div class="wrapper-btn"><a class="fiddlehed-login-btn" href="'. wp_login_url(  ) .'"></a></div>';
  }


}
add_shortcode( 'loginFiddlehed',  'fiddlehed_button_login' );

/************************************************/
/*  add shortcode to display Free Lessons
/************************************************/

function fiddlehed_button_free ( $atts ) {

  if (class_exists('acf')) {
    return '<div class="wrapper-btn"><a class="fiddlehed-free-btn" href="'.get_field ("subscribe_button_link", 'options' ) .'" ></a></div>';
  }


}
add_shortcode( 'freeFiddlehed',   'fiddlehed_button_free' );


/************************************************/
/*  Customizing LogIn logo
/************************************************/

function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url('https://fiddlehed.com/app/themes/sage-8.5.1/dist/images/fiddleguy-logo.png');
        		background-repeat: no-repeat;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );


/************************************************/
/*  Shortcode display last posts
/************************************************/

function fiddlehed_posts_shortcode( $atts ) {
	extract(shortcode_atts(array(
	    'limit' => '10',
      'type' => 'post, page',
	), $atts));
  $retval = '';

  $args = array(
  	'numberposts' => $limit,
  	'post_type' => 'post',
  	'post_status' => 'publish',
  );
  $recent_posts = wp_get_recent_posts($args);
	foreach( $recent_posts as $recent ){
		$retval .= '<div class="fiddlehed-posts">';
    if ( has_post_thumbnail( $recent["ID"]) ) {
      $retval .=  get_the_post_thumbnail($recent["ID"],'thumbnail');
    }
     $retval .= '<a href="' . get_permalink($recent["ID"]) . '">' .   $recent["post_title"].'</a> </li> ';
     $retval .= '</div>';
	}
	wp_reset_query();
	return $retval;
}
add_shortcode('fiddlehed_posts',   'fiddlehed_posts_shortcode');

/* Add Pixel facebook */
