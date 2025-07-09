jQuery(document).ready(function ($) {
    $('#load-more-btn').on('click', function () {
        var button = $(this);
        var page = parseInt(button.attr('data-page'));
        var posts = parseInt(button.attr('data-posts'));

        $.ajax({
            url: customBlogGrid.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_more_blogs',
                page: page,
                posts: posts
            },
            beforeSend: function () {
                button.text('Loading...');
            },
            success: function (res) {
                $('#custom-blog-grid-container').append(res);
                button.attr('data-page', page + 1);
                button.text('View More');

                if ($.trim(res) === '') {
                    button.hide();
                }
            }
        });
    });
});
