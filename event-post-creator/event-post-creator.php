<?php
/*
Plugin Name: Event Post Creator :  Ignatiuz
Description: Creates a post from a form submission.
Version: 1.0
Author: Vishal Sharma : sr. webdeveloper
*/




/*--------------------------------------------------
		--styles enqueues-----
-------------------------------------------------*/
function event_plugin_enqueue_styles() {
        wp_enqueue_style('event-event_table-styles', plugin_dir_url(__FILE__) . 'css/event_table.css?time='.time());
        wp_enqueue_style('event-single_post-styles',  plugin_dir_url(__FILE__) . 'css/single_post.css?time='.time());
        wp_enqueue_style('event-custom-styles',  plugin_dir_url(__FILE__) . 'css/custom.css?time='.time());
}
add_action('wp_enqueue_scripts', 'event_plugin_enqueue_styles');




/*--------------------------------------------------
		evnt post type register and  rewrite rules
-------------------------------------------------*/

function register_event_post_type() {
    $labels = array(
        'name'               => 'Events',
        'singular_name'      => 'Event',
        'menu_name'          => 'Events',
        'name_admin_bar'     => 'Event',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Event',
        'new_item'           => 'New Event',
        'edit_item'          => 'Edit Event',
        'view_item'          => 'View Event',
        'all_items'          => 'All Events',
        'search_items'       => 'Search Events',
        'parent_item_colon'  => 'Parent Events:',
        'not_found'          => 'No events found.',
        'not_found_in_trash' => 'No events found in Trash.'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        /*'rewrite'            => array(
            'slug' => '%author%',
            'with_front' => false,
        ),*/
        'rewrite' => array('slug' => 'event'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'editor', 'thumbnail', 'author', 'excerpt', 'comments'),
        'taxonomies'         => array('category', 'post_tag'),
    );

    register_post_type('event', $args);
}
add_action('init', 'register_event_post_type');






/*--------------------------------------------------
		over write slug rules 
-------------------------------------------------*/

// Add rewrite rules
// function epc_add_rewrite_rules() {
//     add_rewrite_rule(
//         '^event/([^/]+)/?$',
//         'index.php?post_type=event&name=$matches[1]',
//         'top'
//     );
// }
// add_action('init', 'epc_add_rewrite_rules');

// Modify event post link to include author
// function epc_event_post_link($post_link, $post) {
//     if ($post->post_type == 'event') {
//         $author = get_userdata($post->post_author);
//         if ($author) {
//             $post_link = home_url(user_trailingslashit($author->user_nicename . '/' . $post->post_name));
//         }
//     }
//     return $post_link;
// }
// add_filter('post_type_link', 'epc_event_post_link', 10, 2);

// Flush rewrite rules on activation and deactivation
// function epc_flush_rewrite_rules() {
//     epc_add_rewrite_rules();
//     flush_rewrite_rules();
// }
// register_activation_hook(__FILE__, 'epc_flush_rewrite_rules');
// register_deactivation_hook(__FILE__, 'flush_rewrite_rules');


/*--------------------------------------------------
		event forma and actions
-------------------------------------------------*/

/*event form*/
function event_form() {
    ob_start();
    include('event_form_template.php');
    return ob_get_clean();
}
add_shortcode('event_form', 'event_form');

/*event form action*/
function handle_form_submission() {
        //print_r($_POST);
    if (isset($_POST['submitEvent'])) {

        // print_r($_POST);
        // die();
        $title = sanitize_text_field($_POST['eventTitle']);
        $date = sanitize_text_field($_POST['eventDate']);
        $startTime = sanitize_text_field($_POST['eventStartTime']);
        $endTime = sanitize_text_field($_POST['eventEndTime']);
        $price = sanitize_text_field($_POST['eventPrice']);
        $description = sanitize_textarea_field($_POST['eventDescription']);
        $category = sanitize_text_field($_POST['eventCategory'][0]);

        // Handle file upload
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        $uploadedfile = $_FILES['eventImage'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $imageUrl = $movefile['url'];
        } else {
            $imageUrl = '';
        }

        $new_post = array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_author'   => get_current_user_id(),
            'post_type'     => 'event',
            'post_category' => array(get_cat_ID($category))
        );

        $post_id = wp_insert_post($new_post);

        if ($post_id) {
            if ($imageUrl) {
                $image_id = attach_image_to_post($imageUrl, $post_id);
                set_post_thumbnail($post_id, $image_id);
            }
            add_post_meta($post_id, 'event_date', $date);
            add_post_meta($post_id, 'event_start_time', $startTime);
            add_post_meta($post_id, 'event_end_time', $endTime);
            add_post_meta($post_id, 'event_price', $price);

            wp_redirect(get_permalink($post_id));
            exit;
        }
    }
}
add_action('template_redirect', 'handle_form_submission');



function attach_image_to_post($image_url, $post_id) {
    $upload_dir = wp_upload_dir();
    $image_data = file_get_contents($image_url);
    $filename = basename($image_url);
    if (wp_mkdir_p($upload_dir['path'])) {
        $file = $upload_dir['path'] . '/' . $filename;
    } else {
        $file = $upload_dir['basedir'] . '/' . $filename;
    }
    file_put_contents($file, $image_data);

    $wp_filetype = wp_check_filetype($filename, null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title'     => sanitize_file_name($filename),
        'post_content'   => '',
        'post_status'    => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
    wp_update_attachment_metadata($attach_id, $attach_data);

    return $attach_id;
}



/*--------------------------------------------------
	-- show cpt type field in admin side table --
-------------------------------------------------*/

// Add custom columns
function add_custom_columns($columns) {
    $columns['event_date'] = 'Event Date';
    $columns['event_start_time'] = 'Start Time';
    $columns['event_end_time'] = 'End Time';
    $columns['event_price'] = 'Price';
    return $columns;
}
add_filter('manage_event_posts_columns', 'add_custom_columns');

// Populate custom columns
function custom_column_content($column, $post_id) {
    switch ($column) {
        case 'event_date':
            echo get_post_meta($post_id, 'event_date', true);
            break;
        case 'event_start_time':
            echo get_post_meta($post_id, 'event_start_time', true);
            break;
        case 'event_end_time':
            echo get_post_meta($post_id, 'event_end_time', true);
            break;
        case 'event_price':
            echo get_post_meta($post_id, 'event_price', true);
            break;
    }
}
add_action('manage_event_posts_custom_column', 'custom_column_content', 10, 2);


// Make columns sortable
function sortable_columns($columns) {
    $columns['event_date'] = 'event_date';
    $columns['event_start_time'] = 'event_start_time';
    $columns['event_end_time'] = 'event_end_time';
    $columns['event_price'] = 'event_price';
    return $columns;
}
add_filter('manage_edit-event_sortable_columns', 'sortable_columns');


// Remove unwanted columns
function remove_unwanted_columns($columns) {
    unset($columns['author']);
    unset($columns['categories']);
    unset($columns['tags']);
    unset($columns['comments']);
    return $columns;
}
add_filter('manage_event_posts_columns', 'remove_unwanted_columns');

// Add sorting functionality
function column_orderby($query) {
    if (!is_admin()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('event_date' == $orderby) {
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value');
    }
    if ('event_start_time' == $orderby) {
        $query->set('meta_key', 'event_start_time');
        $query->set('orderby', 'meta_value');
    }
    if ('event_end_time' == $orderby) {
        $query->set('meta_key', 'event_end_time');
        $query->set('orderby', 'meta_value');
    }
    if ('event_price' == $orderby) {
        $query->set('meta_key', 'event_price');
        $query->set('orderby', 'meta_value_num');
    }
}
add_action('pre_get_posts', 'column_orderby');



/*----------------------------------------------------------------
	-- show events data on edit or add page from admin side --
--------------------------------------------------------------------*/
// Add meta boxes
function add_meta_boxes() {
    add_meta_box('event_details', 'Event Details', 'display_meta_box', 'event', 'normal', 'high');
}
add_action('add_meta_boxes', 'add_meta_boxes');

// Display meta boxes
function display_meta_box($post) {
    $event_date = get_post_meta($post->ID, 'event_date', true);
    $event_start_time = get_post_meta($post->ID, 'event_start_time', true);
    $event_end_time = get_post_meta($post->ID, 'event_end_time', true);
    $event_price = get_post_meta($post->ID, 'event_price', true);

    wp_nonce_field('save_event_details', 'event_nonce');

    ?>
    <p>
        <label for="event_date">Event Date:</label>
        <input type="date" id="event_date" name="event_date" value="<?php echo esc_attr($event_date); ?>">
    </p>
    <p>
        <label for="event_start_time">Start Time:</label>
        <input type="time" id="event_start_time" name="event_start_time" value="<?php echo esc_attr($event_start_time); ?>">
    </p>
    <p>
        <label for="event_end_time">End Time:</label>
        <input type="time" id="event_end_time" name="event_end_time" value="<?php echo esc_attr($event_end_time); ?>">
    </p>
    <p>
        <label for="event_price">Price:</label>
        <input type="number" id="event_price" name="event_price" value="<?php echo esc_attr($event_price); ?>">
    </p>
    <?php
}

// Save meta box data
function save_meta_boxes($post_id) {
    if (!isset($_POST['event_nonce']) || !wp_verify_nonce($_POST['event_nonce'], 'save_event_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['event_date'])) {
        update_post_meta($post_id, 'event_date', sanitize_text_field($_POST['event_date']));
    }

    if (isset($_POST['event_start_time'])) {
        update_post_meta($post_id, 'event_start_time', sanitize_text_field($_POST['event_start_time']));
    }

    if (isset($_POST['event_end_time'])) {
        update_post_meta($post_id, 'event_end_time', sanitize_text_field($_POST['event_end_time']));
    }

    if (isset($_POST['event_price'])) {
        update_post_meta($post_id, 'event_price', sanitize_text_field($_POST['event_price']));
    }
}
add_action('save_post', 'save_meta_boxes');


/*--------------------------------------------------------------------------------*/

function load_single_event_template($template) {
    if (is_singular('event')) {
        // Check if the file exists in the plugin directory
        $plugin_template = plugin_dir_path(__FILE__) . 'single-event-template.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }
    return $template;
}
add_filter('single_template', 'load_single_event_template');







/*---------------------------------------------------*/

// Register the shortcode
function display_events_list() {
    // Query for events
    $args = array(
        'post_type' => 'event',
        'posts_per_page' => -1, // Display all events
        'post_status' => 'publish'
    );
    $query = new WP_Query($args);

    // Start the output buffer
    ob_start();

    // Check if there are any events
    if ($query->have_posts()) {
        echo '<table class="events-table">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Title</th>';
        echo '<th>Date</th>';
        echo '<th>Start Time</th>';
        echo '<th>End Time</th>';
        echo '<th>Price</th>';
        echo '<th>View</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Loop through the events
        while ($query->have_posts()) {
            $query->the_post();
            $event_date = get_post_meta(get_the_ID(), 'event_date', true);
            $event_start_time = get_post_meta(get_the_ID(), 'event_start_time', true);
            $event_end_time = get_post_meta(get_the_ID(), 'event_end_time', true);
            $event_price = get_post_meta(get_the_ID(), 'event_price', true);

            echo '<tr>';
            echo '<td>' . get_the_title() . '</td>';
            echo '<td>' . esc_html($event_date) . '</td>';
            echo '<td>' . esc_html($event_start_time) . '</td>';
            echo '<td>' . esc_html($event_end_time) . '</td>';
            echo '<td>' . esc_html($event_price) . '</td>';
            echo '<td><a href="' . get_permalink() . '" class="more-view-link">More View</a></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No events found.</p>';
    }

    // Reset post data
    wp_reset_postdata();

    // Return the output buffer content
    return ob_get_clean();
}
add_shortcode('events_list', 'display_events_list');
