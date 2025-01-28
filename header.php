<?php
/**
 * The header for our theme
 *
 * @package Volt
 */
?>
<!DOCTYPE html>
<html lang="en">

<head> 
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- Primary Meta Tags -->
<title><?php the_title(); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="title" content="Volt Free Bootstrap Dashboard - Transactions">
<meta name="author" content="Themesberg">
<meta name="description" content="Volt Pro is a Premium Bootstrap 5 Admin Dashboard featuring over 800 components, 10+ plugins and 20 example pages using Vanilla JS.">
<meta name="keywords" content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, themesberg, themesberg dashboard, themesberg admin dashboard" />
<link rel="canonical" href="https://themesberg.com/product/admin-dashboard/volt-premium-bootstrap-5-dashboard">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://demo.themesberg.com/volt-pro">
<meta property="og:title" content="Volt Free Bootstrap Dashboard - Transactions">
<meta property="og:description" content="Volt Pro is a Premium Bootstrap 5 Admin Dashboard featuring over 800 components, 10+ plugins and 20 example pages using Vanilla JS.">
<meta property="og:image" content="https://themesberg.s3.us-east-2.amazonaws.com/public/products/volt-pro-bootstrap-5-dashboard/volt-pro-preview.jpg">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://demo.themesberg.com/volt-pro">
<meta property="twitter:title" content="Volt Free Bootstrap Dashboard - Transactions">
<meta property="twitter:description" content="Volt Pro is a Premium Bootstrap 5 Admin Dashboard featuring over 800 components, 10+ plugins and 20 example pages using Vanilla JS.">
<meta property="twitter:image" content="https://themesberg.s3.us-east-2.amazonaws.com/public/products/volt-pro-bootstrap-5-dashboard/volt-pro-preview.jpg">

<!-- Favicon -->
<link rel="apple-touch-icon" sizes="120x120" href="<?php bloginfo('template_url'); ?>/assets/img/favicon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="<?php bloginfo('template_url'); ?>/assets/img/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?php bloginfo('template_url'); ?>/assets/img/favicon/favicon-16x16.png">
<link rel="manifest" href="../assets/img/favicon/site.webmanifest">
<link rel="mask-icon" href="<?php bloginfo('template_url'); ?>/assets/img/favicon/safari-pinned-tab.svg" color="#ffffff">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="theme-color" content="#ffffff">

<!-- Sweet Alert -->
<link type="text/css" href="<?php bloginfo('template_url'); ?>/vendor/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">

<!-- Notyf -->
<link type="text/css" href="<?php bloginfo('template_url'); ?>/vendor/notyf/notyf.min.css" rel="stylesheet">

<!-- Volt CSS -->
<link type="text/css" href="<?php bloginfo('template_url'); ?>/css/volt.css" rel="stylesheet">

<!-- Vanilla DataTables -->
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="https://cdn.datatables.net/plug-ins/2.1.8/dataRender/hyperLink.js" type="text/javascript"></script>

<!-- NOTICE: You can use the _analytics.html partial to include production code specific code & trackers -->

<style type="text/css">
    .uwp_page ul li {
        border-bottom: 1px dotted rgba(100, 100, 100, 0.2);
    }
    .uwp_page ul li a {
        font-family: 'Source Sans Pro Light';
    }
    .wpcf7 input[type="text"], .wpcf7 input[type="email"], .wpcf7 textarea {
        display: block;
        width: 100%;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 400;
        line-height: 1.5;
        color: #6b7280;
        background-color:  #FFFFFF;
        border: 0.0625rem solid #D1D5DB;
        border-radius: 15px;
        appearance: none;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.07);
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .wpcf7 input[type="submit"] {
        color: #ffffff;
        background-color: #1f2937;
        border-color: #1f2937;
        border-radius: 15px;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 1px 1px rgba(17, 24, 39, 0.075);
        padding: 10px 15px;
        margin: 5px 5px 5px 100px;
    }
    .label {
      font-weight: 900;
    }
</style>    
</head>

<body>

  <!-- NOTICE: You can use the _analytics.html partial to include production code specific code & trackers -->
        

  
  <nav class="navbar navbar-dark navbar-theme-primary px-4 col-12 d-lg-none">
        <a class="navbar-brand me-lg-5" href="<?php bloginfo('url'); ?>/index.html">
            <img class="navbar-brand-dark" src="<?php bloginfo('template_url'); ?>/assets/img/brand/1pwr_logo.png" alt="1PWR logo" /> <img class="navbar-brand-light" src="../../assets/img/brand/1pwr_logo.png" alt="1PWR logo" />
        </a>
        <div class="d-flex align-items-center">
            <button class="navbar-toggler d-lg-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
  </nav>
  <nav id="sidebarMenu" class="sidebar d-lg-block bg-gray-800 text-white collapse" data-simplebar>
    <div class="sidebar-inner px-4 pt-3">
        <div class="user-card d-flex d-md-none align-items-center justify-content-between justify-content-md-center pb-4">
            <div class="d-flex align-items-center">
                <div class="avatar-lg me-4">
                    <!-- User avatar -->
                    <svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                    </svg>
                </div>
                <div class="d-block">
                    <h2 class="h5 mb-3">Hi, Jane</h2>
                    <a href="../pages/examples/sign-in.html" class="btn btn-secondary btn-sm d-inline-flex align-items-center">
                        <svg class="icon icon-xxs me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </a>
                </div>
            </div>
            <div class="collapse-close d-md-none">
                <a href="#sidebarMenu" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="true" aria-label="Toggle navigation">
                    <svg class="icon icon-xs" fill="black" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </a>
            </div>
        </div>

        <ul class="nav flex-column pt-3 pt-md-0">
            <li class="nav-item">
                <a href="../../index.html" class="nav-link d-flex align-items-center">
                    <span class="sidebar-icon">
                        <img src="<?php bloginfo('template_url'); ?>/assets/img/brand/1pwr_logo.png" style="margin-top: -5px; width: 80%;" alt="1PWR Logo">
                    </span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../pages/dashboard/dashboard.html" class="nav-link">
                    <span class="sidebar-icon">
                        <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                    </span>
                    <span class="sidebar-text">Dashboard</span>
                </a>
            </li>
            <!-- Assets Menu -->
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#submenu-assets" aria-expanded="false">
                    <span>
                        <span class="sidebar-icon">
                            <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9V9h2v4zm0-6H9V5h2v2z"></path>
                            </svg>
                        </span>
                        <span class="sidebar-text">Assets</span>
                    </span>
                    <span class="link-arrow">
                        <svg class="icon icon-sm" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="submenu-assets" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item">
                            <a class="nav-link" href="http://localhost:8888/asset_one/index.php/all-assets/">
                                <span class="sidebar-text">View All</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="http://localhost:8888/asset_one/index.php/add-new-asset/">
                                <span class="sidebar-text">Add New Asset</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="http://localhost:8888/asset_one/index.php/categories/">
                                <span class="sidebar-text">Categories</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- Employees Menu -->
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#submenu-employees" aria-expanded="false">
                    <span>
                        <span class="sidebar-icon">
                            <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M10 2a2 2 0 00-2 2v1h4V4a2 2 0 00-2-2z"></path>
                                <path fill-rule="evenodd" d="M2 9a2 2 0 012-2h12a2 2 0 012 2v5a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm4 1a1 1 0 100 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        <span class="sidebar-text">Employees</span>
                    </span>
                    <span class="link-arrow">
                        <svg class="icon icon-sm" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="submenu-employees" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php bloginfo('url'); ?>/index.php/view-all-employees/">
                                <span class="sidebar-text">View All Employees</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php bloginfo('url'); ?>/index.php/employee-add-new/">
                                <span class="sidebar-text">Add New</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php bloginfo('url'); ?>/index.php/departments/">
                                <span class="sidebar-text">Departments</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <!-- Requests Menu -->
            <li class="nav-item">
                <span class="nav-link collapsed d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#submenu-requests" aria-expanded="false">
                    <span>
                        <span class="sidebar-icon">
                            <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V4a2 2 0 00-2-2H4zm1 3h10v10H5V5zm2 1a1 1 0 100 2h6a1 1 0 100-2H7zm0 4a1 1 0 100 2h4a1 1 0 100-2H7z"></path>
                            </svg>
                        </span>
                        <span class="sidebar-text">Requests</span>
                    </span>
                    <span class="link-arrow">
                        <svg class="icon icon-sm" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </span>
                </span>
                <div class="multi-level collapse" role="list" id="submenu-requests" aria-expanded="false">
                    <ul class="flex-column nav">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php bloginfo('url'); ?>/index.php/request-list/">
                                <span class="sidebar-text">View All Requests</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php bloginfo('url'); ?>/index.php/asset-request-form/">
                                <span class="sidebar-text">Add New</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php bloginfo('url'); ?>/index.php/asset-return-form/">
                                <span class="sidebar-text">Returns</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li role="separator" class="dropdown-divider mt-4 mb-3 border-gray-700"></li>
            <?php if (is_user_logged_in()) { ?>
                <!-- Logged In Links -->
                <li class="nav-item">
                    <a href="<?php bloginfo('url'); ?>/index.php/account/" class="nav-link d-flex align-items-center">
                        <span class="sidebar-icon">
                            <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 1.5rem; height: 1.5rem;">
                                <path fill-rule="evenodd" d="M12 2a5 5 0 0 1 5 5v3h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h3V7a5 5 0 0 1 5-5zm0 4a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm-4 9h8a3 3 0 0 0-8 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <span class="sidebar-text">Account</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo wp_logout_url(get_permalink()); ?>" class="nav-link d-flex align-items-center">
                        <span class="sidebar-icon">
                            <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 1.5rem; height: 1.5rem;">
                                <path fill-rule="evenodd" d="M9 3a1 1 0 0 1 1 1v4a1 1 0 1 1-2 0V5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h3v-3a1 1 0 1 1 2 0v4a1 1 0 0 1-1 1H6a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4h3zm9.707 7.293a1 1 0 0 1 0 1.414l-4 4a1 1 0 0 1-1.414-1.414L16.586 12H10a1 1 0 1 1 0-2h6.586l-2.293-2.293a1 1 0 0 1 1.414-1.414l4 4z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <span class="sidebar-text">Logout</span>
                    </a>
                </li>
            <?php } else { ?>
                <!-- Logged Out Links -->
                <li class="nav-item">
                    <a href="<?php bloginfo('url'); ?>/index.php/login/" target="_blank" class="nav-link d-flex align-items-center">
                        <span class="sidebar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                <circle cx="12" cy="7" r="4"></circle>
                                <path d="M15 13h-6v-2h6v-3l5 4-5 4v-3z"></path>
                                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"></circle>
                            </svg>
                        </span>
                        <span class="sidebar-text">Sign In</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php bloginfo('url'); ?>/index.php/register/" target="_blank" class="nav-link d-flex align-items-center">
                        <span class="sidebar-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                                <circle cx="12" cy="7" r="4"></circle>
                                <line x1="12" y1="13" x2="12" y2="19" stroke="currentColor" stroke-width="2"></line>
                                <line x1="9" y1="16" x2="15" y2="16" stroke="currentColor" stroke-width="2"></line>
                                <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"></circle>
                            </svg>
                        </span>
                        <span class="sidebar-text">Register</span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    </div>
</nav>
    
  <main class="content">
  <?php
// Add this to header.php or another template file

    global $template;
    echo '<!-- Current Template: ' . basename($template) . ' -->';
    
    // Optional: Display as visible text for testing
    echo '<div style="background: #fff; color: #333; padding: 10px; margin: 10px; border: 1px solid #ddd; position: fixed; bottom: 0; right: 0; z-index: 9999;">
        Template: ' . basename($template) . '<br>
        Post Type: ' . get_post_type() . '<br>
        Page ID: ' . get_the_ID() . '
    </div>';

?>
      <nav class="navbar navbar-top navbar-expand navbar-dashboard navbar-dark ps-0 pe-2 pb-0">
        <div class="container-fluid px-0">
          <div class="d-flex justify-content-between w-100" id="navbarSupportedContent">
            <div class="d-flex align-items-center">
              <!-- Search form -->
              <form class="navbar-search form-inline" id="navbar-search-main">
                <div class="input-group input-group-merge search-bar">
                  <span class="input-group-text" id="topbar-addon">
                    <svg class="icon icon-xs" x-description="Heroicon name: solid/search" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                  </span>
                  <input type="text" class="form-control" id="topbarInputIconLeft" placeholder="Search" aria-label="Search" aria-describedby="topbar-addon">
                </div>
              </form>
              <!-- / Search form -->
            </div>
            <!-- Navbar links -->
            <ul class="navbar-nav align-items-center">
              <li class="nav-item dropdown">
                <a class="nav-link text-dark notification-bell unread dropdown-toggle" data-unread-notifications="true" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
                  <svg class="icon icon-sm text-gray-900" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-center mt-2 py-0">
                  <div class="list-group list-group-flush">
                    <a href="#" class="text-center text-primary fw-bold border-bottom border-light py-3">Notifications</a>
                    <a href="#" class="list-group-item list-group-item-action border-bottom">
                      <div class="row align-items-center">
                          <div class="col-auto">
                            <!-- Avatar -->
                            <img alt="Image placeholder" src="<?php bloginfo('template_url'); ?>/assets/img/team/profile-picture-1.jpg" class="avatar-md rounded">
                          </div>
                          <div class="col ps-0 ms-2">
                            <div class="d-flex justify-content-between align-items-center">
                              <div>
                                <h4 class="h6 mb-0 text-small">Jose Leos</h4>
                              </div>
                              <div class="text-end">
                                <small class="text-danger">a few moments ago</small>
                              </div>
                            </div>
                            <p class="font-small mt-1 mb-0">Added you to an event "Project stand-up" tomorrow at 12:30 AM.</p>
                          </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action border-bottom">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <!-- Avatar -->
                          <img alt="Image placeholder" src="<?php bloginfo('template_url'); ?>/assets/img/team/profile-picture-2.jpg" class="avatar-md rounded">
                        </div>
                        <div class="col ps-0 ms-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <h4 class="h6 mb-0 text-small">Neil Sims</h4>
                            </div>
                            <div class="text-end">
                              <small class="text-danger">2 hrs ago</small>
                            </div>
                          </div>
                          <p class="font-small mt-1 mb-0">You've been assigned a task for "Awesome new project".</p>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action border-bottom">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <!-- Avatar -->
                          <img alt="Image placeholder" src="<?php bloginfo('template_url'); ?>/assets/img/team/profile-picture-3.jpg" class="avatar-md rounded">
                        </div>
                        <div class="col ps-0 m-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <h4 class="h6 mb-0 text-small">Roberta Casas</h4>
                            </div>
                            <div class="text-end">
                              <small>5 hrs ago</small>
                            </div>
                          </div>
                          <p class="font-small mt-1 mb-0">Tagged you in a document called "Financial plans",</p>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action border-bottom">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <!-- Avatar -->
                          <img alt="Image placeholder" src="<?php bloginfo('template_url'); ?>/assets/img/team/profile-picture-4.jpg" class="avatar-md rounded">
                        </div>
                        <div class="col ps-0 ms-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <h4 class="h6 mb-0 text-small">Joseph Garth</h4>
                            </div>
                            <div class="text-end">
                              <small>1 d ago</small>
                            </div>
                          </div>
                          <p class="font-small mt-1 mb-0">New message: "Hey, what's up? All set for the presentation?"</p>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action border-bottom">
                      <div class="row align-items-center">
                        <div class="col-auto">
                          <!-- Avatar -->
                          <img alt="Image placeholder" src="<?php bloginfo('template_url'); ?>/assets/img/team/profile-picture-5.jpg" class="avatar-md rounded">
                        </div>
                        <div class="col ps-0 ms-2">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <h4 class="h6 mb-0 text-small">Bonnie Green</h4>
                            </div>
                            <div class="text-end">
                              <small>2 hrs ago</small>
                            </div>
                          </div>
                          <p class="font-small mt-1 mb-0">New message: "We need to improve the UI/UX for the landing page."</p>
                        </div>
                      </div>
                    </a>
                    <a href="#" class="dropdown-item text-center fw-bold rounded-bottom py-3">
                      <svg class="icon icon-xxs text-gray-400 me-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path></svg>
                      View all
                    </a>
                  </div>
                </div>
              </li>
              <li class="nav-item dropdown ms-lg-3">
                <a class="nav-link dropdown-toggle pt-1 px-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <div class="media d-flex align-items-center">
                    <!--
                    <img class="avatar rounded-circle" alt="Image placeholder" src="<?php bloginfo('template_url'); ?>/assets/img/team/profile-picture-3.jpg">
                    -->
                    <svg class="dropdown-icon text-gray-400 me-2" fill="black" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 1.5rem; height: 1.5rem;">
    <path fill-rule="evenodd" d="M12 2a5 5 0 0 1 5 5v3h3a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2h3V7a5 5 0 0 1 5-5zm0 4a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm-4 9h8a3 3 0 0 0-8 0z" clip-rule="evenodd"></path>
</svg>



                    <div class="media-body ms-2 text-dark align-items-center d-none d-lg-block">
                      <span class="mb-0 font-small fw-bold text-gray-900">

                      <?php
                      // Get the current logged-in user
                      $current_user = wp_get_current_user();

                      // Check if a user is logged in
                      if ($current_user->ID != 0) {
                      // Display first and last name
                        echo 'Hello, ' . esc_html($current_user->first_name) . ' ' . esc_html($current_user->last_name);
                      } else {
                        echo 'Hello, Guest';
                      }
                      ?>

                      </span>
                    </div>
                  </div>
                </a>
                <div class="dropdown-menu dashboard-dropdown dropdown-menu-end mt-2 py-1">
                  <a class="dropdown-item d-flex align-items-center" href="<?php bloginfo('url'); ?>/index.php/account/">
                    <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path></svg>
                      Edit Account
                  </a>
                  <a class="dropdown-item d-flex align-items-center" href="<?php bloginfo('url'); ?>/index.php/account/?type=change-password">
                    <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12 2a5 5 0 0 0-5 5v3H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2h-1V7a5 5 0 0 0-5-5zm-3 8V7a3 3 0 1 1 6 0v3H9zm3 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z" clip-rule="evenodd"/></svg>      
                    Change Password
                  </a>
                  <a class="dropdown-item d-flex align-items-center" href="<?php bloginfo('url'); ?>/index.php/account/?type=notifications">
                  <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12 2a6 6 0 0 0-6 6v4.5a5.5 5.5 0 0 1-1.293 3.582l-.354.354a1 1 0 0 0 .707 1.707h14.882a1 1 0 0 0 .707-1.707l-.354-.354A5.5 5.5 0 0 1 18 12.5V8a6 6 0 0 0-6-6zm-2 18a2 2 0 1 0 4 0h-4z" clip-rule="evenodd"/></svg>
                        Notifications
                  </a>
                  <!--
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path></svg>
                      Settings
                    </a>
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zm0 2h10v7h-2l-1 2H8l-1-2H5V5z" clip-rule="evenodd"></path></svg>
                      Messages
                  </a>
                    -->
                  <a class="dropdown-item d-flex align-items-center" href="<?php bloginfo('url'); ?>/index.php/account/?type=privacy">
                    <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12 2C8.686 2 6 3.343 6 6v4.528c0 4.982 3.516 9.154 6 10.945 2.484-1.791 6-5.963 6-10.945V6c0-2.657-2.686-4-6-4zm0 8a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3zm-1 2a1 1 0 0 1 2 0v2a1 1 0 0 1-2 0v-2z" clip-rule="evenodd"/></svg>
                        Privacy
                  </a>
                  <!--
                  <a class="dropdown-item d-flex align-items-center" href="#">
                    <svg class="dropdown-icon text-gray-400 me-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-2 0c0 .993-.241 1.929-.668 2.754l-1.524-1.525a3.997 3.997 0 00.078-2.183l1.562-1.562C15.802 8.249 16 9.1 16 10zm-5.165 3.913l1.58 1.58A5.98 5.98 0 0110 16a5.976 5.976 0 01-2.516-.552l1.562-1.562a4.006 4.006 0 001.789.027zm-4.677-2.796a4.002 4.002 0 01-.041-2.08l-.08.08-1.53-1.533A5.98 5.98 0 004 10c0 .954.223 1.856.619 2.657l1.54-1.54zm1.088-6.45A5.974 5.974 0 0110 4c.954 0 1.856.223 2.657.619l-1.54 1.54a4.002 4.002 0 00-2.346.033L7.246 4.668zM12 10a2 2 0 11-4 0 2 2 0 014 0z" clip-rule="evenodd"></path></svg>
                      Support
                  </a>
        
                  -->
                  <div role="separator" class="dropdown-divider my-1"></div>
                  <a class="dropdown-item d-flex align-items-center" href="<?php echo wp_logout_url( get_permalink() ); ?>">
                    <svg class="dropdown-icon text-danger me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>                
                      Logout
                  </a>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </nav>

      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center py-4">
        <div class="d-block mb-4 mb-md-0">
          <nav aria-label="breadcrumb" class="d-none d-md-inline-block">
            <ol class="breadcrumb breadcrumb-dark breadcrumb-transparent">
              <li class="breadcrumb-item">
                <a href="#">
                  <svg class="icon icon-xxs" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                </a>
              </li>
              <li class="breadcrumb-item"><a href="#">Volt</a></li>
              <li class="breadcrumb-item active" aria-current="page">Transactions</li>
            </ol>
          </nav>
          <h2 class="h4">All Orders</h2>
          <p class="mb-0">Your web analytics dashboard template.</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
          <a href="#" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
            <svg class="icon icon-xs me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
              New Plan
          </a>
          <div class="btn-group ms-2 ms-lg-3">
            <button type="button" class="btn btn-sm btn-outline-gray-600">Share</button>
            <button type="button" class="btn btn-sm btn-outline-gray-600">Export</button>
          </div>
        </div>
      </div>
      <div class="card card-body border-0 shadow table-wrapper table-responsive">