<?php
/**
 * Template part for displaying main navigation
 */
?>

<nav class="navbar navbar-dark navbar-theme-primary px-4 col-12 d-lg-none">
	<a class="navbar-brand me-lg-5" href="<?php echo esc_url(home_url('/')); ?>">
		<?php 
		$custom_logo_id = get_theme_mod('custom_logo');
		$logo = wp_get_attachment_image_src($custom_logo_id, 'full');
		if ($logo): ?>
			<img class="navbar-brand-dark" src="<?php echo esc_url($logo[0]); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" />
		<?php else: ?>
			<?php bloginfo('name'); ?>
		<?php endif; ?>
	</a>
	<!-- Rest of your navigation code -->
</nav>

<nav id="sidebarMenu" class="sidebar d-lg-block bg-gray-800 text-white collapse" data-simplebar>
	<?php
	wp_nav_menu(array(
		'theme_location' => 'dashboard-menu',
		'container' => false,
		'menu_class' => 'nav flex-column pt-3 pt-md-0',
		'fallback_cb' => false,
		'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
		'walker' => new Dashboard_Menu_Walker()
	));
	?>
</nav>