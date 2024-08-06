<?php
// single-event-template.php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header();

while (have_posts()) : the_post();
    $event_date = get_post_meta(get_the_ID(), 'event_date', true);
    $event_start_time = get_post_meta(get_the_ID(), 'event_start_time', true);
    $event_end_time = get_post_meta(get_the_ID(), 'event_end_time', true);
    $event_price = get_post_meta(get_the_ID(), 'event_price', true);
    $event_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
    $event_description = get_the_content();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="post-content">
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
        </header>

        <div class="content-wrapper">
            <?php if ($event_image) : ?>
                <div class="featured-image">
                    <img src="<?php echo esc_url($event_image); ?>" alt="<?php the_title(); ?>">
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <p><strong>Date:</strong> <?php echo esc_html($event_date); ?></p>
                <p><strong>Start Time:</strong> <?php echo esc_html($event_start_time); ?></p>
                <p><strong>End Time:</strong> <?php echo esc_html($event_end_time); ?></p>
                <p><strong>Price:</strong> <?php echo esc_html($event_price); ?></p>
                <div class="event-description">
                    <h2>Description</h2>
                    <?php echo wpautop($event_description); ?>
                </div>
            </div>
        </div>
    </div>
</article>


<?php
endwhile;

get_footer();
