/**
 * Asset edit functionality
 */
jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Asset edit script loaded');
    
    // Only run if we're on the asset edit page with category dropdowns
    if ($('#primary_category_code').length && $('#secondary_category_code').length) {
        
        console.log('Category dropdowns found');
        
        // Handle primary category change
        $('#primary_category_code').on('change', function() {
            var primaryCode = $(this).val();
            console.log('Primary category changed to:', primaryCode);
            
            // Clear secondary dropdown
            var $secondaryDropdown = $('#secondary_category_code');
            $secondaryDropdown.html('<option value="">Loading...</option>');
            
            if (!primaryCode) {
                $secondaryDropdown.html('<option value="">Select Secondary Category</option>');
                return;
            }
            
            // AJAX request
            $.ajax({
                url: asset_edit_vars.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'get_secondary_categories',
                    security: asset_edit_vars.security,
                    primary_code: primaryCode
                },
                success: function(response) {
                    console.log('AJAX response:', response);
                    
                    var options = '<option value="">Select Secondary Category</option>';
                    
                    if (response.success && response.data && response.data.categories) {
                        // Get selected value if any
                        var selectedValue = $secondaryDropdown.data('selected');
                        
                        $.each(response.data.categories, function(i, cat) {
                            var selected = (cat.code === selectedValue) ? ' selected' : '';
                            options += '<option value="' + cat.code + '"' + selected + '>' + cat.name + '</option>';
                        });
                    }
                    
                    $secondaryDropdown.html(options);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                    $secondaryDropdown.html('<option value="">Error loading categories</option>');
                }
            });
        });
        
        // Trigger change on page load if a primary category is selected
        var initialPrimaryCode = $('#primary_category_code').val();
        if (initialPrimaryCode) {
            console.log('Initial primary category detected:', initialPrimaryCode);
            $('#primary_category_code').trigger('change');
        }
    }
});
