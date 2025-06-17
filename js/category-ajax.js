/**
 * Category AJAX functionality
 * 
 * Handles dynamic loading of secondary categories based on primary category selection
 */
jQuery(document).ready(function($) {
    
    // Only initialize if we're on a page with the category dropdowns
    if ($('#primary_category_code').length && $('#secondary_category_code').length) {
        
        console.log('Category AJAX script initialized');
        
        // Function to load secondary categories based on primary selection
        function loadSecondaryCategories(primaryCode, selectedSecondaryCode = '') {
            if (!primaryCode) {
                // If no primary category is selected, empty the secondary dropdown
                $('#secondary_category_code').html('<option value="">Select Secondary Category</option>');
                return;
            }
            
            console.log('Loading secondary categories for primary code:', primaryCode);
            
            // Show loading indicator
            $('#secondary_category_code').html('<option value="">Loading...</option>');
            
            // Make AJAX call to get secondary categories
            $.ajax({
                url: category_ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_secondary_categories',
                    security: category_ajax_object.security,
                    primary_code: primaryCode
                },
                beforeSend: function() {
                    console.log('Sending AJAX request for secondary categories');
                },
                success: function(response) {
                    console.log('AJAX response received:', response);
                    
                    if (response.success && response.data && response.data.categories) {
                        var options = '<option value="">Select Secondary Category</option>';
                        
                        // Add options for each secondary category
                        $.each(response.data.categories, function(index, category) {
                            var selected = (category.code === selectedSecondaryCode) ? 'selected' : '';
                            options += '<option value="' + category.code + '" ' + selected + '>' + category.name + '</option>';
                        });
                        
                        // Update the dropdown
                        $('#secondary_category_code').html(options);
                    } else {
                        // Handle error or empty categories
                        $('#secondary_category_code').html('<option value="">No categories available</option>');
                    }
                },
                error: function(xhr, status, error) {
                    // Handle AJAX error
                    console.error('AJAX error:', error);
                    $('#secondary_category_code').html('<option value="">Error loading categories</option>');
                }
            });
        }
        
        // Initial load of secondary categories if a primary category is already selected
        var initialPrimaryCode = $('#primary_category_code').val();
        var initialSecondaryCode = $('#secondary_category_code').data('selected') || '';
        
        if (initialPrimaryCode) {
            console.log('Initial primary code detected:', initialPrimaryCode);
            console.log('Initial secondary code:', initialSecondaryCode);
            loadSecondaryCategories(initialPrimaryCode, initialSecondaryCode);
        }
        
        // Handle change of primary category
        $('#primary_category_code').on('change', function() {
            var primaryCode = $(this).val();
            console.log('Primary category changed to:', primaryCode);
            loadSecondaryCategories(primaryCode);
        });
    }
});
