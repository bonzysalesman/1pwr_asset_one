document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('#checkout-items-container');

    // Initialize Awesomplete for typeahead
    const initializeTypeahead = (input) => {
        new Awesomplete(input, {
            minChars: 2,
            autoFirst: true,
            maxItems: 10,
        });

        // Fetch asset suggestions dynamically
        input.addEventListener('input', function () {
            const searchTerm = input.value;

            // Fetch assets from the server
            fetch(`${ajaxurl}?action=fetch_assets&term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    const suggestions = data.map(asset => asset.name);
                    input.awesomplete.list = suggestions;
                })
                .catch(error => console.error('Error fetching assets:', error));
        });
    };

    // Initialize typeahead for existing inputs
    container.querySelectorAll('.asset-typeahead').forEach(initializeTypeahead);

    // Add new item row
    container.addEventListener('click', function (e) {
        if (e.target.classList.contains('add-item-btn')) {
            const newRow = document.createElement('div');
            newRow.classList.add('checkout-item', 'row', 'mb-3');
            newRow.innerHTML = `
                <div class="col-md-6">
                    <label for="asset_id" class="form-label">Asset <span class="text-danger">*</span></label>
                    <input type="text" 
                           class="form-control asset-typeahead" 
                           name="asset_id[]" 
                           placeholder="Start typing to search assets..." 
                           required>
                </div>
                <div class="col-md-4">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="quantity[]" value="1" min="1" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger remove-item-btn">Remove</button>
                </div>
            `;

            container.appendChild(newRow);

            // Initialize typeahead for the new input
            initializeTypeahead(newRow.querySelector('.asset-typeahead'));

            // Add event listener to the remove button
            newRow.querySelector('.remove-item-btn').addEventListener('click', function () {
                newRow.remove();
            });
        }
    });

    // Typeahead for Receiver Name and auto-populate Receiver Contact
    const receiverNameInput = document.querySelector('#receiver_name');
    const receiverContactInput = document.querySelector('#receiver_contact');

    // Awesomplete Typeahead Initialization for Receiver Name
    const receiverTypeahead = new Awesomplete(receiverNameInput, {
        minChars: 2,
        autoFirst: true,
        maxItems: 10,
    });

    // Fetch Receiver Suggestions
    receiverNameInput.addEventListener('input', function () {
        const searchTerm = receiverNameInput.value;

        fetch(`${ajaxurl}?action=fetch_receivers&term=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                const suggestions = data.map(receiver => receiver.name);
                receiverTypeahead.list = suggestions;
            })
            .catch(error => console.error('Error fetching receivers:', error));
    });

    // Auto-Populate Receiver Contact
    receiverNameInput.addEventListener('awesomplete-selectcomplete', function () {
        const selectedName = receiverNameInput.value;

        fetch(`${ajaxurl}?action=get_receiver_contact&name=${encodeURIComponent(selectedName)}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.contact) {
                    receiverContactInput.value = data.contact;
                } else {
                    receiverContactInput.value = '';
                }
            })
            .catch(error => console.error('Error fetching receiver contact:', error));
    });
});