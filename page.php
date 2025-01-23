<?php
get_header()
?>

        <?php
				while ( have_posts() ) {
					the_post();
					//get_template_part( 'loop-templates/content', 'page' );
        ?>            

					<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	                <?php
	                if ( ! is_page_template( 'page-templates/no-title.php' ) ) {
		                the_title(
			                '<header class="entry-header"><h1 class="entry-title">',
			                '</h1></header><!-- .entry-header -->'
		                );
	                }

	                echo get_the_post_thumbnail( $post->ID, 'large' );
	                ?>

	                <div class="entry-content">

		            <?php
		                the_content();
		                //understrap_link_pages();
		            ?>

	                </div><!-- .entry-content -->

	                <footer class="entry-footer">

		                <?php //understrap_edit_post_link(); ?>

    	            </footer><!-- .entry-footer -->

                    </article><!-- #post-<?php the_ID(); ?> -->
				<?php 
                }
                ?>

<div>
  <hr />
<?php
// Get the query var
$asset_id = get_query_var('asset_id');
$department_id = get_query_var('department_id');
if ($asset_id) {
    echo "Viewing Asset ID: " . esc_html($asset_id);
}
?>

<div class="asset-details row" style="display: none;">
            <?php
            try {
                // Database connection details
                $host = 'localhost';
                $db   = '1pwr_asset';
                $user = 'root';
                $pass = 'root';
                $port = "8889";
                $socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

                // Create PDO connection
                $pdo = new PDO(
                    "mysql:unix_socket={$socket};dbname={$db}",
                    $user,
                    $pass,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );

                $my_asset_id = filter_input(INPUT_GET, "asset_id", FILTER_SANITIZE_NUMBER_INT);
                
                if ($my_asset_id) {
                    $query = "SELECT a.*, c.name as category_name, 
                                    al.allocation_date, al.return_date, al.status as allocation_status,
                                    al.employee_id
                             FROM assets a 
                             LEFT JOIN categories c ON a.category_id = c.category_id
                             LEFT JOIN allocations al ON a.asset_id = al.asset_id 
                                AND al.status = 'Allocated'
                             WHERE a.asset_id = :asset_id";
                    
                    $stmt = $pdo->prepare($query);
                    $stmt->execute(['asset_id' => $my_asset_id]);
                    $asset = $stmt->fetch(PDO::FETCH_OBJ);

                    // Get categories for dropdown
                    $categories_query = "SELECT category_id, name FROM categories ORDER BY name";
                    $categories_stmt = $pdo->query($categories_query);
                    $categories = $categories_stmt->fetchAll(PDO::FETCH_OBJ);

                    if ($asset) {
                        ?>
                        <!-- Left Column - Asset Details -->
                        <div class="col-12 col-lg-6 mb-4">
                            <div class="card border-0 shadow">
                                <div class="card-header">
                                    <h5 class="mb-0">Asset Details</h5>
                                </div>
                                <div>
                                <?php
                                    // Add this where you want to display messages
                                    if (isset($_GET['updated'])) {
                                        echo '<div class="alert alert-success">Asset updated successfully!</div>';
                                    }
                                    if (isset($_GET['error'])) {
                                        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($_GET['error']) . '</div>';
                                    }
                                ?>
                                </div>
                                <div class="card-body">
                                    <div class="asset-info">
                                        <p><strong>Name:</strong> <?php echo htmlspecialchars($asset->name); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($asset->description); ?></p>
                                        <p><strong>Category:</strong> <?php echo htmlspecialchars($asset->category_name); ?></p>
                                        <p><strong>Status:</strong> <?php echo htmlspecialchars($asset->status); ?></p>
                                        <p><strong>Location:</strong> <?php echo htmlspecialchars($asset->location); ?></p>
                                        <?php if ($asset->purchase_date): ?>
                                            <p><strong>Purchase Date:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($asset->purchase_date))); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($asset->allocation_status === 'Allocated'): ?>
                                            <div class="allocation-info mt-4">
                                                <h6 class="mb-3">Current Allocation</h6>
                                                <p><strong>Allocated Since:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($asset->allocation_date))); ?></p>
                                                <?php if ($asset->return_date): ?>
                                                    <p><strong>Expected Return:</strong> <?php echo htmlspecialchars(date('F j, Y', strtotime($asset->return_date))); ?></p>
                                                <?php endif; ?>
                                                <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($asset->employee_id); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Edit Form -->
                        <div class="col-12 col-lg-6">
                            <div class="card border-0 shadow">
                                <div class="card-header">
                                    <h5 class="mb-0">Edit Asset</h5>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo get_permalink() . '?asset_id=' . $my_asset_id; ?>" method="POST">
                                        <input type="hidden" name="asset_id" value="<?php echo htmlspecialchars($asset->asset_id); ?>">
                                        
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($asset->name); ?>" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" 
                                                      rows="3"><?php echo htmlspecialchars($asset->description); ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <select class="form-select" id="category" name="category_id">
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo htmlspecialchars($category->category_id); ?>"
                                                        <?php echo ($category->category_id == $asset->category_id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($category->name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="location" name="location" 
                                                   value="<?php echo htmlspecialchars($asset->location); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="purchase_date" class="form-label">Purchase Date</label>
                                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                                   value="<?php echo htmlspecialchars($asset->purchase_date); ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="Allocated" <?php echo ($asset->status === 'Allocated') ? 'selected' : ''; ?>>
                                                    Allocated
                                                </option>
                                                <option value="Unallocated" <?php echo ($asset->status === 'Unallocated') ? 'selected' : ''; ?>>
                                                    Unallocated
                                                </option>
                                            </select>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">Update Asset</button>
                                            <a href="assets.php" class="btn btn-light">Cancel</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        echo '<div class="col-12"><p class="error">Asset not found.</p></div>';
                    }
                } else {
                    echo '<div class="col-12"><p class="error">No asset ID provided.</p></div>';
                }
            } catch (Exception $e) {
                echo '<div class="col-12"><p class="error">An error occurred: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
            }
            ?>
<?php
get_footer();
?>