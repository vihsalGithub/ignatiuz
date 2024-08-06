<?php
/*
Plugin Name: GitHub Integration Ignatiuz
Version: 1.0
Author: Vishal Sharma : Sr.webdeveloper
*/




/*------------------------------------------------------------------
                Enqueue CSS and JavaScript
--------------------------------------------------------------*/

function github_integration_enqueue_scripts() {
    wp_enqueue_style('github-integration-styles', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_script('github-integration-script', plugins_url('/js/scripts.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('github-integration-script', 'githubIntegration', array('ajax_url' => admin_url('admin-ajax.php'),));
}
add_action('wp_enqueue_scripts', 'github_integration_enqueue_scripts');



/*---------------------------------------------------------------------
               Shortcode to display the form and results
-------------------------------------------------------------------*/

function github_integration_shortcode() {
    ob_start();
    ?>
    <div class="github-integration">
        <h1>GitHub User Info</h1>
        <form id="githubUserForm">
            <input type="text" id="githubUsername" placeholder="Enter GitHub username" required>
            <button type="submit">Get User Info</button>
        </form>
        <div id="githubUserProfile"></div>
        <div id="githubUserRepos"></div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('github_integration', 'github_integration_shortcode');

// AJAX handler to get GitHub user data
function github_integration_get_user_data() {
    $username = sanitize_text_field($_POST['username']);
    $token = github_integration_get_token();

    $profile_url = "https://api.github.com/users/$username";
    $repos_url = "https://api.github.com/users/$username/repos";

    $profile_response = wp_remote_get($profile_url, array(
        'headers' => array(
            'Authorization' => "token $token"
        )
    ));

    $repos_response = wp_remote_get($repos_url, array(
        'headers' => array(
            'Authorization' => "token $token"
        )
    ));

    if (is_wp_error($profile_response) || is_wp_error($repos_response)) {
        wp_send_json_error('Error fetching data from GitHub.');
    }

    $profile_data = json_decode(wp_remote_retrieve_body($profile_response), true);
    $repos_data = json_decode(wp_remote_retrieve_body($repos_response), true);

    if (isset($profile_data['message']) || isset($repos_data['message'])) {
        wp_send_json_error('GitHub user not found.');
    }

    wp_send_json_success(array('profile' => $profile_data, 'repos' => $repos_data));
}
add_action('wp_ajax_get_github_user_data', 'github_integration_get_user_data');
add_action('wp_ajax_nopriv_get_github_user_data', 'github_integration_get_user_data');



/*--------------------------------------------------------------------
                            Setting of code
----------------------------------------------------------------------*/

// Add settings page
function github_integration_add_admin_menu() {
    add_options_page(
        'GitHub Integration Settings',
        'GitHub Integration',
        'manage_options',
        'github-integration',
        'github_integration_options_page'
    );
}
add_action('admin_menu', 'github_integration_add_admin_menu');

// Register settings
function github_integration_settings_init() {
    register_setting('githubIntegration', 'github_integration_settings');

    add_settings_section(
        'github_integration_section',
        'GitHub Integration Settings',
        'github_integration_settings_section_callback',
        'githubIntegration'
    );

    add_settings_field(
        'github_integration_token',
        'GitHub Personal Access Token',
        'github_integration_token_render',
        'githubIntegration',
        'github_integration_section'
    );
}
add_action('admin_init', 'github_integration_settings_init');

// Render the token input field
function github_integration_token_render() {
    $options = get_option('github_integration_settings');
    ?>
    <input type="text" name="github_integration_settings[github_integration_token]" value="<?php echo isset($options['github_integration_token']) ? esc_attr($options['github_integration_token']) : ''; ?>">
    <?php
}

// Settings section callback
function github_integration_settings_section_callback() {
    echo __('Enter your GitHub Personal Access Token here.', 'wordpress');
}

// Options page HTML
function github_integration_options_page() {
    ?>
    <form action="options.php" method="post">
        <h1>GitHub Integration Settings</h1>
        <?php
        settings_fields('githubIntegration');
        do_settings_sections('githubIntegration');
        submit_button();
        ?>
    </form>
    <?php
}

// Function to retrieve the stored token
function github_integration_get_token() {
    $options = get_option('github_integration_settings');
    return isset($options['github_integration_token']) ? $options['github_integration_token'] : '';
}