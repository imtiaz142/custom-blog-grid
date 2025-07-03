<?php
/*
Plugin Name: Custom Blog Grid Layout (with Load More)
Description: Blog grid with image, meta, title, description and AJAX Load More button.
Version: 2.0
Author: Imtiaz Ali
*/

function custom_blog_grid_shortcode($atts) {
    ob_start();
    ?>
    <div class="custom-blog-grid" id="custom-blog-grid-container">
        <?php echo custom_blog_grid_render_posts(1); ?>
    </div>
    <div class="view-more-container">
        <button id="load-more-btn" data-page="1">View More</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_blog_grid', 'custom_blog_grid_shortcode');

function custom_blog_grid_render_posts($page = 1) {
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => 6,
        'paged' => $page,
    );
    $query = new WP_Query($args);
    $output = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $author = get_the_author();
            $date = get_the_date('d F Y');
            $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');
            $excerpt = wp_trim_words(get_the_excerpt(), 20, '...');
            $link = get_permalink();
            $title = get_the_title();

            $output .= '
            <div class="blog-card">
                <img src="' . esc_url($image) . '" alt="Blog Image" />
                <div class="card-body">
                    <div class="left-content">
                        <p class="meta"><span class="icon"></span>' . esc_html($date) . '</p>
                        <h3 class="title"><a href="' . esc_url($link) . '">' . esc_html($title) . '</a></h3>
                    </div>
                    <div class="right-icon">
                        <a href="' . esc_url($link) . '"><span>→</span></a>
                    </div>
                </div>
                <p class="excerpt">' . esc_html($excerpt) . '</p>
            </div>';
        }
        wp_reset_postdata();
    }
    return $output;
}

add_action('wp_ajax_load_more_blogs', 'custom_blog_grid_load_more');
add_action('wp_ajax_nopriv_load_more_blogs', 'custom_blog_grid_load_more');

function custom_blog_grid_load_more() {
    $page = isset($_POST['page']) ? intval($_POST['page']) + 1 : 2;
    echo custom_blog_grid_render_posts($page);
    wp_die();
}

// Enqueue scripts
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('custom-blog-grid-js', plugin_dir_url(__FILE__) . 'load-more.js', array('jquery'), '1.0', true);
    wp_localize_script('custom-blog-grid-js', 'customBlogGrid', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
});

// CSS
add_action('wp_head', function () {
    ?>
    <style>
    .custom-blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 25px;
        padding: 30px 0;
        max-width: 1200px;
        margin: 0 auto;
    }

    .blog-card {
        background: #fff;
        padding: 16px;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
    }

    .blog-card img {
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

    .icon {
        margin-right: 6px;
        font-size: 14px;
    }

    .title {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        color: #000;
    }

    .title a {
        text-decoration: none;
        color: inherit;
    }

    .title a:hover {
        text-decoration: underline;
    }

    .right-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .right-icon a {
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
    text-decoration: none;
    transform: rotate(-25deg);
    transition: all 0.3s ease;
}

.right-icon a:hover {
    background: #fff;
    border-color: #fff;
}


    .excerpt {
        font-size: 14px;
        color: #333;
        margin: 0;
    }

    .view-more-container {
        text-align: center;
        margin-top: 25px;
    }

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
        background: #791501;
    }
    .blog-card {
    background: #fff;
    color: #000;
    transition: all 0.3s ease;
}

.blog-card:hover {
    background: #142257;
    color: #fff;
}

.blog-card:hover .meta,
.blog-card:hover .title,
.blog-card:hover .excerpt,
.blog-card:hover .title a,
.blog-card:hover .icon {
    color: #fff;
}

.blog-card:hover .title a:hover {
    text-decoration: underline;
}

.blog-card:hover .right-icon a {
    border-color: #fff;
    background: #fff;
    color: #142257;
}

    </style>
    <?php
});