<?php
/**
 * The main template file
 *
 * @package Volt
 */

get_header();
get_sidebar();
?>

<main class="content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
        <div class="d-block mb-4 mb-md-0">
            <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
                <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
                    <li class="breadcrumb-item">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo get_the_title(); ?></li>
                </ol>
            </nav>
            <h2 class="h4"><?php echo get_the_title(); ?></h2>
        </div>
    </div>

    <div class="card card-body border-0 shadow table-wrapper table-responsive">
        <?php if (have_posts()) : ?>
            <div class="row">
                <?php
                while (have_posts()) :
                    the_post();
                    ?>
                    <div class="col-12 mb-4">
                        <article id="post-<?php the_ID(); ?>" <?php post_class('card border-0 shadow'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="card-img-top">
                                    <?php the_post_thumbnail('large', array('class' => 'img-fluid')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <header class="entry-header">
                                    <?php
                                    if (is_singular()) :
                                        the_title('<h1 class="entry-title h3">', '</h1>');
                                    else :
                                        the_title('<h2 class="entry-title h3"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
                                    endif;
                                    ?>

                                    <?php if ('post' === get_post_type()) : ?>
                                        <div class="entry-meta small text-gray-600 mb-3">
                                            <?php
                                            // Post meta information
                                            echo sprintf(
                                                /* translators: %s: post date */
                                                esc_html__('Posted on %s', 'volt'),
                                                '<time class="entry-date" datetime="' . esc_attr(get_the_date('c')) . '">' . esc_html(get_the_date()) . '</time>'
                                            );

                                            if (get_the_category_list()) {
                                                echo ' | ' . get_the_category_list(', ');
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </header>

                                <div class="entry-content">
                                    <?php
                                    if (is_singular()) :
                                        the_content();
                                    else :
                                        the_excerpt();
                                        ?>
                                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-sm btn-primary">
                                            <?php esc_html_e('Read More', 'volt'); ?>
                                        </a>
                                    <?php
                                    endif;
                                    ?>
                                </div>

                                <?php if (is_singular()) : ?>
                                    <footer class="entry-footer mt-4">
                                        <?php
                                        $tags_list = get_the_tag_list('', ', ');
                                        if ($tags_list) {
                                            printf(
                                                /* translators: %s: tag list */
                                                '<div class="tags-links small text-gray-600">' . esc_html__('Tagged: %s', 'volt') . '</div>',
                                                $tags_list
                                            );
                                        }
                                        ?>
                                    </footer>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php
                endwhile;

                // Pagination
                the_posts_pagination(array(
                    'prev_text' => '<svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>',
                    'next_text' => '<svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>',
                    'class' => 'pagination justify-content-center mt-4',
                ));
                ?>

            </div>
        <?php else : ?>
            <div class="alert alert-warning">
                <?php esc_html_e('No posts found.', 'volt'); ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-white rounded shadow p-5 mb-4 mt-4">
        <div class="row">
            <div class="col-12 col-md-4 col-xl-6 mb-4 mb-md-0">
                <p class="mb-0 text-center text-lg-start">
                    © <?php echo date('Y'); ?> <a class="text-primary fw-normal" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
                </p>
            </div>
            <div class="col-12 col-md-8 col-xl-6 text-center text-lg-start">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'container' => false,
                    'menu_class' => 'list-inline list-group-flush list-group-borderless text-md-end mb-0',
                    'fallback_cb' => false,
                ));
                ?>
            </div>
        </div>
    </footer>
</main>

<?php
get_footer();
?>
