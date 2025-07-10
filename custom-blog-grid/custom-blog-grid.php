<?php
/*
Plugin Name: Custom Blog Grid Layout (with Load More)
Description: Blog grid with image, meta, title, description and AJAX Load More button.
Version: 2.2
Author: Imtiaz Ali
*/

function custom_blog_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'columns' => 3,
        'posts'   => 6,
    ), $atts);

    $columns = intval($atts['columns']);
    $posts_per_page = intval($atts['posts']);

    ob_start();
    ?>
    <div class="custom-blog-grid" id="custom-blog-grid-container" style="--grid-columns: <?php echo $columns; ?>;" data-posts="<?php echo $posts_per_page; ?>">
        <?php echo custom_blog_grid_render_posts(1, $posts_per_page); ?>
    </div>
    <div class="view-more-container">
        <button id="load-more-btn" data-page="1" data-posts="<?php echo $posts_per_page; ?>">View More</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_blog_grid', 'custom_blog_grid_shortcode');

function custom_blog_grid_render_posts($page = 1, $posts_per_page = 6) {
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
    );

    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $date = get_the_date('d F Y');
            $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            $excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
            $link = get_permalink();
            $title = get_the_title();

            $output .= '
            <a class="blog-card" href="' . esc_url($link) . '">
                <img src="' . esc_url($image) . '" alt="Blog Image" />
                <div class="card-body">
                    <div class="left-content">
                        <p class="meta"><span class="icon"></span>' . esc_html($date) . '</p>
                        <h3 class="title">' . esc_html($title) . '</h3>
                    </div>
                    <div class="right-icon">
                        <span>→</span>
                    </div>
                </div>
                <p class="excerpt">' . esc_html($excerpt) . '</p>
            </a>';
        }
        wp_reset_postdata();
    }

    return $output;
}

add_action('wp_ajax_load_more_blogs', 'custom_blog_grid_load_more');
add_action('wp_ajax_nopriv_load_more_blogs', 'custom_blog_grid_load_more');

function custom_blog_grid_load_more() {
    $page = isset($_POST['page']) ? intval($_POST['page']) + 1 : 2;
    $posts = isset($_POST['posts']) ? intval($_POST['posts']) : 6;

    echo custom_blog_grid_render_posts($page, $posts);
    wp_die();
}

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('custom-blog-grid-js', plugin_dir_url(__FILE__) . 'load-more.js', array('jquery'), '1.0', true);
    wp_localize_script('custom-blog-grid-js', 'customBlogGrid', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
});

add_action('wp_head', function () {
    ?>
    <style>
    .custom-blog-grid {
    display: grid;
    grid-template-columns: repeat(var(--grid-columns, 1), 1fr);
    gap: 25px;
    padding: 30px 16px;
    max-width: 1200px;
    margin: 0 auto;
    box-sizing: border-box;
}

    a.blog-card {
        background: #fff;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
        color: #000;
        text-decoration: none;
    }

    a.blog-card:hover {
        background: #142257;
        color: #fff;
    }

    a.blog-card img {
        width: 100%;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .card-body {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .left-content {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
    }

    .meta {
        font-size: 13px;
        color: #666;
        margin: 0;
        display: flex;
        align-items: center;
    }

    a.blog-card:hover .meta,
    a.blog-card:hover .title,
    a.blog-card:hover .excerpt,
    a.blog-card:hover .icon {
        color: #fff;
    }

    .icon {
        margin-right: 6px;
        font-size: 14px;
    }

    .title {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        color: inherit;
    }

    .right-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .right-icon span {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 36px;
        height: 36px;
        background: transparent;
        color: #142257;
        border-radius: 50%;
        border: 2px solid #142257;
        font-size: 18px;
        transform: rotate(-25deg);
        transition: all 0.3s ease;
    }

    a.blog-card:hover .right-icon span {
        border-color: #fff;
        background: #fff;
        color: #142257;
    }

    .excerpt {
        font-size: 14px;
        color: #333;
        margin: 0;
    }

    .view-more-container {
        text-align: center;
        margin-top: 25px;
    }.custom-blog-grid

    #load-more-btn {
        background: #0d152e;
        color: #fff;
        padding: 10px 28px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    #load-more-btn:hover {
        background: #2375bb;
    }
    @media (max-width: 1024px) {
    .custom-blog-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .custom-blog-grid {
        grid-template-columns: 1fr;
    }

    .card-body {
        flex-direction: column;
        align-items: flex-start;
    }

    .right-icon {
        align-self: flex-end;
        margin-top: 10px;
    }

    .right-icon span {
        width: 32px;
        height: 32px;
        font-size: 16px;
    }

    .title {
        font-size: 15px;
    }

    .excerpt {
        font-size: 13px;
    }

    #load-more-btn {
        padding: 10px 20px;
        font-size: 15px;
    }
}

    </style>
    <?php
});