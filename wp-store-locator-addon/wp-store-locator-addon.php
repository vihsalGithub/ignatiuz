<?php
/*
Plugin Name: WP Store Locator  Ignatiuz
Version: 1.0
Author: Vishal Sharma : Sr.webdeveloper
*/


add_filter( 'wpsl_meta_box_fields', 'custom_meta_box_fields' );

function custom_meta_box_fields( $meta_fields ) {

    $meta_fields[ __( 'Store Status', 'wpsl' ) ] = array(
      
        'stor_status_option' => array(
            'label' => __( 'Status', 'wpsl' ),
            'type'  => 'dropdown',
            'options' => array(
                'active' => 'Active',
                'deactive' => 'Deactive',
            )
        )
    );

    return $meta_fields;
}




function stor_status_option_filter_form() {
    ob_start();
    ?>
    <style>
        #store-status-tabs2 ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 10px;
        }
        #store-status-tabs2 li {
            display: inline;
        }
        #store-status-tabs2 a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            color: #333;
        }
        #store-status-tabs2 a.active {
            background-color: #0073aa;
            color: #fff;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'stor_status_option_filter', 'stor_status_option_filter_form' );




function filter_stores_by_status( $args ) {
    	

    	// /print_r($_REQUEST);
	/**/
	$checkvariable =  '';
	if(isset($_GET['store_status']) &&  in_array($_GET['store_status'] , array('active','deactive')) ){
		$checkvariable =  "INNER JOIN wp_postmeta AS stor_status ON stor_status.post_id = posts.ID AND stor_status.meta_key = 'wpsl_stor_status_option'  AND stor_status.meta_value = '".$_GET['store_status']."'";
	}

	//echo $checkvariable;
		$sqlvalue = "SELECT post_lat.meta_value AS lat,
		post_lng.meta_value AS lng,
		posts.ID,
		( %d * acos( cos( radians( %s ) ) * cos( radians( post_lat.meta_value ) ) * cos( radians( post_lng.meta_value ) - radians( %s ) ) + sin( radians( %s ) ) * sin( radians( post_lat.meta_value ) ) ) )
		AS distance
		FROM wp_posts AS posts
		INNER JOIN wp_postmeta AS post_lat ON post_lat.post_id = posts.ID AND post_lat.meta_key = 'wpsl_lat'
		INNER JOIN wp_postmeta AS post_lng ON post_lng.post_id = posts.ID AND post_lng.meta_key = 'wpsl_lng'
		".$checkvariable."
		WHERE posts.post_type = 'wpsl_stores'
		AND posts.post_status = 'publish' GROUP BY posts.ID HAVING distance < %d ORDER BY distance LIMIT 0, %d";
    //return $args;

		//echo $sqlvalue;
		return $sqlvalue ;
}
add_filter( 'wpsl_sql', 'filter_stores_by_status' );




function enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('custom-ajax-script', plugin_dir_url(__FILE__) . 'custom-ajax-script.js?time='.time(), array('jquery'), null, true);

     wp_localize_script('custom-ajax-script', 'ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
