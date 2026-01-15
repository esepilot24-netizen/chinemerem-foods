/**
 * Chinemerem Foods Admin Script
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Add product form
        $('#cfi-add-product-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            
            btn.prop('disabled', true).text('Adding...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_add_product',
                    nonce: cfiData?.nonce || '',
                    name: form.find('[name="name"]').val(),
                    price: form.find('[name="price"]').val(),
                    unit: form.find('[name="unit"]').val(),
                    category: form.find('[name="category"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Product added successfully');
                        location.reload();
                    } else {
                        alert(response.data?.message || 'Failed to add product');
                    }
                    btn.prop('disabled', false).text('Add Product');
                },
                error: function() {
                    alert('Network error');
                    btn.prop('disabled', false).text('Add Product');
                }
            });
        });

        // Add debtor form
        $('#cfi-add-debtor-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            
            btn.prop('disabled', true).text('Adding...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_add_debtor',
                    nonce: cfiData?.nonce || '',
                    name: form.find('[name="name"]').val(),
                    phone: form.find('[name="phone"]').val(),
                    email: form.find('[name="email"]').val(),
                    address: form.find('[name="address"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('Debtor added successfully');
                        location.reload();
                    } else {
                        alert(response.data?.message || 'Failed to add debtor');
                    }
                    btn.prop('disabled', false).text('Add Debtor');
                },
                error: function() {
                    alert('Network error');
                    btn.prop('disabled', false).text('Add Debtor');
                }
            });
        });

        // Add user form
        $('#cfi-add-user-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const btn = form.find('button[type="submit"]');
            
            btn.prop('disabled', true).text('Adding...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_add_user',
                    nonce: cfiData?.nonce || '',
                    username: form.find('[name="username"]').val(),
                    email: form.find('[name="email"]').val(),
                    password: form.find('[name="password"]').val(),
                    name: form.find('[name="name"]').val(),
                    role: form.find('[name="role"]').val()
                },
                success: function(response) {
                    if (response.success) {
                        alert('User added successfully');
                        location.reload();
                    } else {
                        alert(response.data?.message || 'Failed to add user');
                    }
                    btn.prop('disabled', false).text('Add User');
                },
                error: function() {
                    alert('Network error');
                    btn.prop('disabled', false).text('Add User');
                }
            });
        });

        // Delete product
        $(document).on('click', '.cfi-delete-product', function() {
            if (!confirm('Are you sure you want to delete this product?')) return;
            
            const btn = $(this);
            const id = btn.data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_delete_product',
                    nonce: cfiData?.nonce || '',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        btn.closest('tr').fadeOut();
                    } else {
                        alert(response.data?.message || 'Failed to delete');
                    }
                }
            });
        });

        // Delete debtor
        $(document).on('click', '.cfi-delete-debtor', function() {
            if (!confirm('Are you sure you want to delete this debtor?')) return;
            
            const btn = $(this);
            const id = btn.data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_delete_debtor',
                    nonce: cfiData?.nonce || '',
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        btn.closest('tr').fadeOut();
                    } else {
                        alert(response.data?.message || 'Failed to delete');
                    }
                }
            });
        });

        // Delete user
        $(document).on('click', '.cfi-delete-user', function() {
            if (!confirm('Are you sure you want to delete this user?')) return;
            
            const btn = $(this);
            const id = btn.data('id');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_delete_user',
                    nonce: cfiData?.nonce || '',
                    user_id: id
                },
                success: function(response) {
                    if (response.success) {
                        btn.closest('tr').fadeOut();
                    } else {
                        alert(response.data?.message || 'Failed to delete');
                    }
                }
            });
        });

        // Create backup
        $('#cfi-create-backup').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).text('Creating...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cfi_download_backup',
                    nonce: cfiData?.nonce || ''
                },
                success: function(response) {
                    if (response.success) {
                        alert('Backup created successfully');
                        if (response.data.file_url) {
                            window.open(response.data.file_url);
                        }
                        location.reload();
                    } else {
                        alert(response.data?.message || 'Failed to create backup');
                    }
                    btn.prop('disabled', false).text('Create Full Backup');
                },
                error: function() {
                    alert('Network error');
                    btn.prop('disabled', false).text('Create Full Backup');
                }
            });
        });
    });

})(jQuery);
