<?php
/**
 * The home template file
 * 
 * This is the template that displays the blog posts index
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
                    <li class="breadcrumb-item active" aria-current="page"><?php echo esc_html__('Posts', 'volt'); ?></li>
                </ol>
            </nav>
            <h2 class="h4">All Posts</h2>
            <p class="mb-0">Your blog posts dashboard.</p>
        </div>
        <?php if (current_user_can('edit_posts')): ?>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="<?php echo esc_url(admin_url('post-new.php')); ?>" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
                    <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Post
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="card card-body border-0 shadow table-wrapper table-responsive">
        <?php if (have_posts()) : ?>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th class="border-gray-200">Title</th>
                        <th class="border-gray-200">Category</th>
                        <th class="border-gray-200">Date</th>
                        <th class="border-gray-200">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while (have_posts()) : the_post(); ?>
                        <tr>
                            <td>
                                <a href="<?php the_permalink(); ?>" class="fw-bold">
                                    <?php the_title(); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo get_the_category_list(', '); ?>
                            </td>
                            <td>
                                <span class="fw-normal">
                                    <?php echo get_the_date(); ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-link text-dark p-0">
                                        <svg class="icon icon-xs" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="card-footer px-3 border-0 d-flex flex-column flex-lg-row align-items-center justify-content-between">
                <?php
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

    <?php get_template_part('template-parts/footer', 'content'); ?>
</main>

<?php get_footer(); ?> 