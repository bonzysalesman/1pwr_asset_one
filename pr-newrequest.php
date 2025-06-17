<?php
/*
Template Name: New Purchase Request
*/

get_header();
global $wpdb, $current_user;
wp_get_current_user();

// Fetch organizations for dropdown
$organizations = $wpdb->get_results("SELECT id, name FROM organizations WHERE active_status = 1");

// Fetch departments for dropdown
$departments = $wpdb->get_results("SELECT id, name FROM departments WHERE active_status = 1");

// Fetch employees for typeahead
$employees = $wpdb->get_results("SELECT employee_id, CONCAT(first_name, ' ', last_name) AS name FROM employees");

// Debugging: Check for query errors
if ($wpdb->last_error !== '') {
    echo "<div class='alert alert-danger'>Error fetching employees: " . esc_html($wpdb->last_error) . "</div>";
}

// Automatically populate requester and email fields with current user's data
$logged_in_user_name = $current_user->display_name;
$logged_in_user_email = $current_user->user_email;

// Initialize default values
$pr_values = [
    'user_id' => $current_user->ID,
    'organization_id' => '',
    'department_id' => '',
    'site' => '',
    'expense_type' => '',
    'description' => '',
    'estimated_amount' => '',
    'currency' => '',
    'urgency_level' => 'Normal',
    'status' => 'Submitted',
    'requester' => $logged_in_user_name,
    'email' => $logged_in_user_email,
    'project_category' => '',
    'preferred_vendor' => '',
    'approvers' => ''
];

// Initialize error and success message variables
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_new_purchase_request'])) {
    // Verify nonce
    if (isset($_POST['new_pr_nonce']) && wp_verify_nonce($_POST['new_pr_nonce'], 'new-pr-nonce')) {
        // Sanitize input data
        $pr_values = [
            'user_id' => $current_user->ID,
            'organization_id' => intval($_POST['organization_id']),
            'department_id' => intval($_POST['department_id']),
            'site' => sanitize_text_field($_POST['site']),
            'expense_type' => sanitize_text_field($_POST['expense_type']),
            'description' => sanitize_textarea_field($_POST['description']),
            'estimated_amount' => floatval($_POST['estimated_amount']),
            'currency' => sanitize_text_field($_POST['currency']),
            'urgency_level' => sanitize_text_field($_POST['urgency_level']),
            'status' => 'Submitted',
            'requester' => sanitize_text_field($_POST['requester']),
            'email' => sanitize_email($_POST['email']),
            'project_category' => sanitize_text_field($_POST['project_category']),
            'preferred_vendor' => sanitize_text_field($_POST['preferred_vendor']),
            'approvers' => sanitize_textarea_field($_POST['approvers'])
        ];

        // Insert new purchase request into the database
        $result = $wpdb->insert(
            'purchase_requests',
            $pr_values,
            ['%d', '%d', '%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result) {
            $success_message = 'Purchase request submitted successfully!';
            // Redirect to the same page with a success query parameter
            wp_redirect(add_query_arg('success', '1', get_permalink()));
            exit;
        } else {
            $error_message = 'Error submitting purchase request: ' . $wpdb->last_error;
        }
    } else {
        $error_message = 'Security check failed. Please try again.';
    }
}
?>

<style>
/* Responsive Design */
.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 1rem;
    background-color: transparent;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
}

.table tbody + tbody {
    border-top: 2px solid #dee2e6;
}

.table-sm th,
.table-sm td {
    padding: 0.3rem;
}

/* Validation Feedback */
.input-error {
    border-color: #dc3545;
}

.error-message {
    color: #dc3545;
    font-size: 0.875em;
}

/* Typeahead Styles */
.typeahead {
    position: relative;
}

.typeahead-suggestions {
    position: absolute;
    border: 1px solid #ccc;
    background-color: #fff;
    z-index: 1000;
    max-height: 150px;
    overflow-y: auto;
    width: 100%;
}

.typeahead-suggestion {
    padding: 8px;
    cursor: pointer;
}

.typeahead-suggestion:hover {
    background-color: #f0f0f0;
}
</style>

<div class="container py-4">
    <?php if (isset($_GET['success']) && $_GET['success'] == '1') : ?>
        <div class="alert alert-success" role="alert">
            Purchase request submitted successfully!
        </div>
    <?php endif; ?>

    <?php if ($error_message) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="POST" enctype="multipart/form-data" id="new-pr-form">
        <?php if ($error_message) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <input type="hidden" name="action" value="submit_new_purchase_request">
        <?php wp_nonce_field('new-pr-nonce', 'new_pr_nonce'); ?>

        <!-- Organization Dropdown -->
        <div class="mb-3">
            <label for="organization_id" class="form-label">Organization</label>
            <select class="form-control" id="organization_id" name="organization_id" required>
                <?php foreach ($organizations as $org) : ?>
                    <option value="<?php echo esc_attr($org->id); ?>">
                        <?php echo esc_html($org->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="error-message"></div>
        </div>

        <!-- Department Dropdown -->
        <div class="mb-3">
            <label for="department_id" class="form-label">Department</label>
            <select class="form-control" id="department_id" name="department_id" required>
                <?php foreach ($departments as $dept) : ?>
                    <option value="<?php echo esc_attr($dept->id); ?>">
                        <?php echo esc_html($dept->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="error-message"></div>
        </div>

        <!-- Requester Information -->
        <div class="mb-3 typeahead">
            <label for="requester" class="form-label">Requester</label>
            <input type="text" class="form-control" id="requester" name="requester" value="<?php echo $logged_in_user_name; ?>" required autocomplete="off">
            <div class="typeahead-suggestions" id="requester-suggestions"></div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo $logged_in_user_email; ?>" required>
        </div>
        <div class="mb-3">
            <label for="project_category" class="form-label">Project Category</label>
            <select class="form-control" id="project_category" name="project_category" required>
                <option value="20MW">20MW</option>
                <option value="Engineering R&D">Engineering R&D</option>
                <option value="Minigrids">Minigrids</option>
                <option value="Administrative/Overhead">Administrative/Overhead</option>
                <option value="EEP">EEP</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="preferred_vendor" class="form-label">Preferred Vendor</label>
            <select class="form-control" id="preferred_vendor" name="preferred_vendor">
                <option value="1010">HERHOLDTS</option>
                <option value="1011">VOLTEX</option>
                <option value="1012">AFRICA TRADING GROUP</option>
                <option value="1013">KEVCOR</option>
                <option value="1014">ADENDORFF</option>
                <option value="1015">BMG</option>
                <option value="1016">TJ holding</option>
                <option value="1017">Verozha holding</option>
                <option value="1018">Kr holdings</option>
                <option value="1019">Thetsane Hardware</option>
                <option value="1020">Budget Hardware</option>
                <option value="1021">Builders city</option>
                <option value="1022">Khubetsoana building world</option>
                <option value="1023">EZ Hardware</option>
                <option value="1024">Lucky Hardware</option>
                <option value="1025">Plumb link</option>
                <option value="1026">Bearings international</option>
                <option value="1027">Vrsconite Bearings</option>
                <option value="1028">Supreme Bearings FS</option>
                <option value="1029">Hi-Tech systems</option>
                <option value="1030">Power Transformers</option>
                <option value="1031">Rotrolex transformers</option>
                <option value="1032">Maverick  Generators</option>
                <option value="1033">Kerun Intelligent control</option>
                <option value="1034">Sino Plant</option>
                <option value="1035">Compact control</option>
                <option value="1036">Bundu power</option>
                <option value="1037">Satowell</option>
                <option value="1038">Gem switchgear</option>
                <option value="1039">Swichboard Groups</option>
                <option value="1040">Macsteel</option>
                <option value="1041">Pure stainless steel</option>
                <option value="1042">BSI STEEL</option>
                <option value="1043">Stewarts and lloyds</option>
                <option value="1044">Reinfoicing solution</option>
                <option value="1045">Free State Engineering</option>
                <option value="1046">Sd knives</option>
                <option value="1047">Cape Watch</option>
                <option value="1048">ADCENG gas equipment</option>
                <option value="1002">Afrox Lesotho</option>
                <option value="1049">SaGe-safe gas equipment</option>
                <option value="1050">JOJO</option>
                <option value="1051">Teengs</option>
                <option value="1052">M&D Specilaised fasters</option>
                <option value="1053">Fastener  Agencies</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="approvers" class="form-label">Approvers</label>
            <textarea class="form-control" id="approvers" name="approvers" required></textarea>
        </div>

        <!-- Step 1: General Info -->
        <div class="form-step" data-step="1">
            <h5>Step 1: General Information</h5>
            <div class="mb-3">
                <label for="site" class="form-label">Site</label>
                <select class="form-control" id="site" name="site" required>
                    <option value="MAK">Ha Makebe</option>
                    <option value="RAL">Ha Raliemere</option>
                    <option value="TOS">Tosing</option>
                    <option value="SEB">Sebapala</option>
                    <option value="SEH">Sehlabathebe</option>
                    <option value="SHG">Sehonghong</option>
                    <option value="MAS">Mashai</option>
                    <option value="MAT">Matsoaing</option>
                    <option value="LEB">Lebakeng</option>
                    <option value="TLH">Tlhanyaku</option>
                    <option value="RIB">Ribaneng</option>
                    <option value="KET">Ketane</option>
                    <option value="NKU">Ha Nkau</option>
                    <option value="MET">Methalaneng</option>
                    <option value="MAN">Manamaneng</option>
                    <option value="BOB">Bobete</option>
                    <option value="HQ">1PWR Headquarters</option>
                </select>
                <div class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="expense_type" class="form-label">Expense Type</label>
                <select class="form-control" id="expense_type" name="expense_type" required>
                    <option value="Capex">Capex</option>
                    <option value="Opex">Opex</option>
                    <option value="Other">Other</option>
                </select>
                <div class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                <div class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="estimated_amount" class="form-label">Estimated Amount</label>
                <input type="number" class="form-control" id="estimated_amount" name="estimated_amount" step="0.01" required>
                <div class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="currency" class="form-label">Currency</label>
                <select class="form-control" id="currency" name="currency" required>
                    <option value="LSL">LSL</option>
                    <option value="USD">USD</option>
                    <option value="ZAR">ZAR</option>
                </select>
                <div class="error-message"></div>
            </div>
            <div class="mb-3">
                <label for="urgency_level" class="form-label">Urgency Level</label>
                <select class="form-control" id="urgency_level" name="urgency_level">
                    <option value="Normal">Normal</option>
                    <option value="Urgent">Urgent</option>
                </select>
            </div>
            <button type="button" class="btn btn-primary" id="next-step">Next</button>
        </div>

        <!-- Step 2: Line Items -->
        <div class="form-step d-none" data-step="2">
            <h5>Step 2: Line Items</h5>
            <div id="line-items-container">
                <div class="line-item">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="line_item_description[]" class="form-label">Item Description</label>
                            <input type="text" class="form-control" id="line_item_description[]" name="line_item_description[]" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="line_item_quantity[]" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="line_item_quantity[]" name="line_item_quantity[]" required>
                            <div class="error-message"></div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="line_item_uom[]" class="form-label">UOM</label>
                            <input type="text" class="form-control" id="line_item_uom[]" name="line_item_uom[]" required>
                            <div class="error-message"></div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" id="add-line-item">Add Line Item</button>
            <button type="button" class="btn btn-primary" id="next-step">Next</button>
        </div>

        <!-- Step 3: Review & Submit -->
        <div class="form-step d-none" data-step="3">
            <h5>Step 3: Review & Submit</h5>
            <p>Please review your purchase request before submitting.</p>
            <div id="pr-summary">
                <h6>General Information</h6>
                <p><strong>Site:</strong> <span id="summary-site"></span></p>
                <p><strong>Expense Type:</strong> <span id="summary-expense-type"></span></p>
                <p><strong>Description:</strong> <span id="summary-description"></span></p>
                <p><strong>Estimated Amount:</strong> <span id="summary-estimated-amount"></span></p>
                <p><strong>Currency:</strong> <span id="summary-currency"></span></p>
                <p><strong>Urgency Level:</strong> <span id="summary-urgency-level"></span></p>
                <h6>Requester Information</h6>
                <p><strong>Requester:</strong> <span id="summary-requester"></span></p>
                <p><strong>Email:</strong> <span id="summary-email"></span></p>
                <p><strong>Project Category:</strong> <span id="summary-project-category"></span></p>
                <p><strong>Preferred Vendor:</strong> <span id="summary-preferred-vendor"></span></p>
                <p><strong>Approvers:</strong> <span id="summary-approvers"></span></p>
                <h6>Line Items</h6>
                <div id="summary-line-items"></div>
            </div>
            <button type="button" class="btn btn-secondary" id="prev-step">Back</button>
            <button type="submit" name="submit_new_purchase_request" class="btn btn-success">Submit Request</button>
        </div>
        <input type="hidden" id="line_items_input" name="line_items">
    </form>
    <p id="selected-org-id"></p> <!-- Placeholder for displaying selected organization ID -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const requesterInput = document.getElementById('requester');
    const requesterSuggestions = document.getElementById('requester-suggestions');

    const employees = <?php echo json_encode($employees); ?>;
    console.log(employees); // Added console log to verify employees data

    requesterInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        requesterSuggestions.innerHTML = '';

        if (query.length > 0) {
            const suggestions = employees.filter(employee => employee.name.toLowerCase().includes(query));

            suggestions.forEach(employee => {
                const suggestion = document.createElement('div');
                suggestion.classList.add('typeahead-suggestion');
                suggestion.textContent = employee.name;
                suggestion.addEventListener('click', function() {
                    requesterInput.value = employee.name;
                    requesterSuggestions.innerHTML = '';
                });
                requesterSuggestions.appendChild(suggestion);
            });
        }
    });

    const organizationDropdown = document.getElementById('organization_id');
    const departmentDropdown = document.getElementById('department_id');
    const selectedOrgIdDisplay = document.getElementById('selected-org-id');

    organizationDropdown.addEventListener('change', function () {
        const organizationId = this.value;
        console.log('Selected Organization ID:', organizationId);
        selectedOrgIdDisplay.textContent = 'Selected Organization ID: ' + organizationId;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'action': 'get_departments',
                'organization_id': organizationId
            })
        })
        .then(response => {
            console.log('AJAX call successful:', response.ok);
            return response.json();
        })
        .then(data => {
            console.log('Departments Data:', data);
            departmentDropdown.innerHTML = ''; // Clear existing options
            data.forEach(department => {
                const option = document.createElement('option');
                option.value = department.department_id; // Use the correct field for the value
                option.textContent = department.short_name || department.department_name; // Display name
                departmentDropdown.appendChild(option);
            });
        })
        .catch(error => console.error('Error fetching departments:', error));
    });

    let currentStep = 1;

    function updateSummary() {
        document.getElementById('summary-site').textContent = document.getElementById('site').value;
        document.getElementById('summary-expense-type').textContent = document.getElementById('expense_type').value;
        document.getElementById('summary-description').textContent = document.getElementById('description').value;
        document.getElementById('summary-estimated-amount').textContent = document.getElementById('estimated_amount').value;
        document.getElementById('summary-currency').textContent = document.getElementById('currency').value;
        document.getElementById('summary-urgency-level').textContent = document.getElementById('urgency_level').value;
        document.getElementById('summary-requester').textContent = document.getElementById('requester').value;
        document.getElementById('summary-email').textContent = document.getElementById('email').value;
        document.getElementById('summary-project-category').textContent = document.getElementById('project_category').value;
        document.getElementById('summary-preferred-vendor').textContent = document.getElementById('preferred_vendor').value;
        document.getElementById('summary-approvers').textContent = document.getElementById('approvers').value;

        const lineItemsContainer = document.getElementById('line-items-container');
        const summaryLineItems = document.getElementById('summary-line-items');
        let tableContent = '<table class="table"><thead><tr><th>#</th><th>Description</th><th>Quantity</th><th>UOM</th></tr></thead><tbody>';
        
        const lineItemsArray = [];
        lineItemsContainer.querySelectorAll('.line-item').forEach(function (item, index) {
            const description = item.querySelector('[name="line_item_description[]"]').value;
            const quantity = item.querySelector('[name="line_item_quantity[]"]').value;
            const uom = item.querySelector('[name="line_item_uom[]"]').value;
            tableContent += `<tr><td>${index + 1}</td><td>${description}</td><td>${quantity}</td><td>${uom}</td></tr>`;
            lineItemsArray.push({ description, quantity, uom });
        });
        
        tableContent += '</tbody></table>';
        summaryLineItems.innerHTML = tableContent;

        // Debug: Log line items array
        console.log('Line Items Array:', lineItemsArray);
    }

    document.querySelectorAll('#next-step').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelector(`[data-step="${currentStep}"]`).classList.add('d-none');
            currentStep++;
            if (currentStep === 3) {
                updateSummary();
            }
            document.querySelector(`[data-step="${currentStep}"]`).classList.remove('d-none');
        });
    });

    document.getElementById('prev-step').addEventListener('click', function () {
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('d-none');
        currentStep--;
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('d-none');
    });

    document.querySelector('#add-line-item').addEventListener('click', function () {
        const container = document.querySelector('#line-items-container');
        const newItem = container.querySelector('.line-item').cloneNode(true);
        newItem.querySelectorAll('input').forEach(function (input) {
            input.value = '';
        });
        container.appendChild(newItem);
        updateLineItemsInput();
    });

    function updateLineItemsInput() {
        const lineItems = [];
        document.querySelectorAll('.line-item').forEach(function (item) {
            const description = item.querySelector('input[name="line_item_description[]"]').value;
            const quantity = item.querySelector('input[name="line_item_quantity[]"]').value;
            if (description && quantity) {
                lineItems.push({ description, quantity });
            }
        });
        document.getElementById('line_items_input').value = JSON.stringify(lineItems);
    }

    document.querySelectorAll('input').forEach(function (input) {
        input.addEventListener('input', function () {
            if (this.name === 'line_item_description[]' && !this.value) {
                this.classList.add('input-error');
                this.nextElementSibling.textContent = 'Description is required';
            } else if (this.name === 'line_item_quantity[]' && (isNaN(this.value) || this.value <= 0)) {
                this.classList.add('input-error');
                this.nextElementSibling.textContent = 'Quantity must be greater than 0';
            } else {
                this.classList.remove('input-error');
                this.nextElementSibling.textContent = '';
            }
            updateLineItemsInput();
        });
    });

    document.querySelector('form').addEventListener('submit', function(event) {
        let valid = true;
        // Validate required fields
        document.querySelectorAll('[required]').forEach(function(input) {
            if (!input.value) {
                input.classList.add('input-error');
                input.nextElementSibling.textContent = 'This field is required';
                valid = false;
            } else {
                input.classList.remove('input-error');
                input.nextElementSibling.textContent = '';
            }
        });

        if (!valid) {
            event.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>

<?php get_footer(); ?>