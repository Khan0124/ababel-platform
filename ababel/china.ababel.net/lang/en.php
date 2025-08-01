<?php
// lang/en.php
return [
    // General
    'app_name' => 'China Office - Ababel',
    'company_name' => 'Ababel Development Company',
    'welcome' => 'Welcome',
    'search' => 'Search',
    'filter' => 'Filter',
    'export' => 'Export',
    'print' => 'Print',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'view' => 'View',
    'add' => 'Add',
    'close' => 'Close',
    'back' => 'Back',
    'yes' => 'Yes',
    'no' => 'No',
    'confirm' => 'Confirm',
    'loading' => 'Loading...',
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',
    'info' => 'Info',
    'all' => 'All',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'from' => 'From',
    'to' => 'To',
    'date' => 'Date',
    'amount' => 'Amount',
    'total' => 'Total',
    'balance' => 'Balance',
    'currency' => 'Currency',
    'status' => 'Status',
    'actions' => 'Actions',
    'share' => 'Share',
    'auto' => 'Auto',
    'leave_empty_for_auto' => 'Leave empty for auto-numbering',
    'created_by' => 'Created by',
    'created_at' => 'Created at',
    'approved_by' => 'Approved by',
    
    // Auth
    'login' => [
        'title' => 'Login',
        'username' => 'Username',
        'password' => 'Password',
        'remember_me' => 'Remember me',
        'forgot_password' => 'Forgot password?',
        'login_button' => 'Login',
        'logout' => 'Logout',
        'invalid_credentials' => 'Invalid username or password',
    ],
    
    // Navigation
    'nav' => [
        'dashboard' => 'Dashboard',
        'clients' => 'Clients',
        'transactions' => 'Transactions',
        'cashbox' => 'Cashbox',
        'reports' => 'Reports',
        'settings' => 'Settings',
        'profile' => 'Profile',
    ],
    
    // Dashboard
    'dashboard' => [
        'title' => 'Dashboard',
        'total_clients' => 'Total Clients',
        'cashbox_balance' => 'Cashbox Balance',
        'today_transactions' => 'Today\'s Transactions',
        'recent_transactions' => 'Recent Transactions',
        'top_clients' => 'Top Clients by Balance',
        'quick_actions' => 'Quick Actions',
        'add_client' => 'Add New Client',
        'create_transaction' => 'Create Transaction',
        'cashbox_movement' => 'Cashbox Movement',
        'daily_report' => 'Daily Report',
        'view_all' => 'View All',
    ],
    
    // Clients
    'clients' => [
        'title' => 'Clients',
        'add_new' => 'Add New Client',
        'edit_client' => 'Edit Client',
        'client_code' => 'Client Code',
        'name' => 'Name',
        'name_ar' => 'Name in Arabic',
        'name_en' => 'Name in English',
        'phone' => 'Phone',
        'email' => 'Email',
        'address' => 'Address',
        'credit_limit' => 'Credit Limit',
        'current_balance' => 'Current Balance',
        'transaction_count' => 'Transaction Count',
        'statement' => 'Statement',
        'client_statement' => 'Client Statement',
        'no_clients' => 'No clients found',
        'client_created' => 'Client created successfully',
        'client_updated' => 'Client updated successfully',
        'client_deleted' => 'Client deleted successfully',
        'confirm_delete' => 'Are you sure you want to delete this client?',
        'active' => 'Active Clients',
        'total_clients' => 'Total Clients',
    ],
    
    // Transactions
    'transactions' => [
        'title' => 'Transactions',
        'add_new' => 'Add New Transaction',
        'transaction_no' => 'Transaction No',
        'transaction_date' => 'Transaction Date',
        'client' => 'Client',
        'type' => 'Type',
        'description' => 'Description',
        'invoice_no' => 'Invoice No',
        'loading_no' => 'Loading No',
        'goods_amount' => 'Goods Amount',
        'commission' => 'Commission',
        'total_amount' => 'Total Amount',
        'payment' => 'Payment',
        'make_payment' => 'Make Payment',
        'shipping' => 'Shipping',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'cancelled' => 'Cancelled',
        'approve' => 'Approve',
        'payment_method' => 'Payment Method',
        'bank_name' => 'Bank Name',
        'payment_description_hint' => 'Optional payment description or reference',
        'quick_amounts' => 'Quick Amounts',
        'full_payment' => 'Full Payment',
        'process_payment' => 'Process Payment',
        'payment_processed_successfully' => 'Payment processed successfully',
        'transaction_created' => 'Transaction created successfully',
        'transaction_approved' => 'Transaction approved successfully',
        'affects_cashbox' => 'Affects Cashbox',
        'count' => 'Count',
        'details' => 'Transaction Details',
        'financial_details' => 'Financial Details',
        'purchase' => 'Purchase',
        'payment_from_client' => 'Payment received from client',
        'transaction_cancelled' => 'Transaction cancelled successfully',
        'amounts' => 'Amounts',
        
    ],
    
    // Cashbox
    'cashbox' => [
        'title' => 'Cashbox',
        'current_balance' => 'Current Balance',
        'movement' => 'Movement',
        'movements' => 'Movements',
        'movement_type' => 'Movement Type',
        'in' => 'Deposit',
        'out' => 'Withdrawal',
        'transfer' => 'Transfer',
        'category' => 'Category',
        'bank_name' => 'Bank Name',
        'tt_number' => 'TT Number',
        'receipt_no' => 'Receipt No',
        'office_transfer' => 'Office Transfer',
        'customer_transfer' => 'Customer Transfer',
        'shipping_transfer' => 'Shipping Transfer',
        'factory_payment' => 'Factory Payment',
        'daily_summary' => 'Daily Summary',
        'movement_added' => 'Movement added successfully',
        'details' => 'Cashbox Details',
        'payment_received' => 'Payment Received',
        'payment_sent' => 'Payment Sent',
        'expense' => 'Expense',
        'other' => 'Other',
    ],
    
    // Reports
    'reports' => [
        'title' => 'Reports',
        'daily_report' => 'Daily Report',
        'monthly_report' => 'Monthly Report',
        'client_report' => 'Client Report',
        'cashbox_report' => 'Cashbox Report',
        'date_range' => 'Date Range',
        'generate' => 'Generate Report',
        'export_pdf' => 'Export PDF',
        'export_excel' => 'Export Excel',
        'no_data' => 'No data found for selected period',
        'generated' => 'Generated',
        'month' => 'Month',
        'financial_summary' => 'Financial Summary',
        'top_clients' => 'Top Clients',
        'summary' => 'Summary',
        'net_change' => 'Net Change',
        'transactions' => 'Transactions',
        'percentage' => 'Percentage',
        'total_revenue' => 'Total Revenue',
        'total_collected' => 'Total Collected',
        'total_outstanding' => 'Total Outstanding',
        'client_details' => 'Client Details',
        'collection_rate' => 'Collection Rate',
        'shipping_summary' => 'Shipping Summary',
        'total_shipping' => 'Total Shipping',
        'shipping_collected' => 'Shipping Collected',
        'shipping_outstanding' => 'Shipping Outstanding',
        'collection_analysis' => 'Collection Analysis',
        'excellent' => 'Excellent',
        'good' => 'Good',
        'needs_attention' => 'Needs Attention',
        'movement_by_category' => 'Movement by Category',
        'total_in' => 'Total In',
        'total_out' => 'Total Out',
        'daily_cash_flow' => 'Daily Cash Flow',
        'daily_balance_changes' => 'Daily Balance Changes',
        'change' => 'Change',
        'running_balance' => 'Running Balance',
    ],
    
    // Settings
    'settings' => [
        'title' => 'Settings',
        'general' => 'General',
        'language' => 'Language',
        'currency' => 'Currency',
        'exchange_rates' => 'Exchange Rates',
        'backup' => 'Backup',
        'users' => 'Users',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'exchange_rates_note' => 'These exchange rates are used for automatic calculations',
        'password_note' => 'Leave empty if you don\'t want to change password',
        'password_changed' => 'Password changed successfully',
        'system' => 'System',
    ],
    
    // loadings
    'loadings' => [
    'title' => 'Loadings',
    'add_new' => 'Add New Loading',
    'view_list' => 'View List',
    'shipping_date' => 'Shipping Date',
    'arrival_date' => 'Arrival Date',
    'payment_method' => 'Payment Method',
    'claim_number' => 'Claim Number',
    'internal_reference' => 'Internal Reference',
    'container_no' => 'Container No.',
    'container_format' => 'Format: 4 letters + 7 numbers',
    'container_format_hint' => 'Example: CMAU7702683',
    'client_code' => 'Client Code',
    'client_name' => 'Client Name',
    'enter_client_code' => 'Enter client code',
    'item_description' => 'Item Description',
    'cargo_type' => 'Cargo Type',
    'cartons_count' => 'Cartons Count',
    'cartons' => 'Cartons',
    'purchase' => 'Purchase',
    'commission' => 'Commission',
    'loading_no' => 'Loading No',
    'total' => 'Total',
    'shipping' => 'Shipping',
    'shipping_rmb' => 'Shipping (¥)',
    'total_with_shipping' => 'Total with Shipping',
    'grand_total' => 'Grand Total',
    'office' => 'Office',
    'no_office' => 'No Office',
    'office_notification_hint' => 'Notification will be sent to selected office',
    'financial_details' => 'Financial Details',
    'total_containers' => 'Total Containers',
    'total_cartons' => 'Total Cartons',
    'client_info' => 'Client Information',
    'mark_shipped' => 'Mark as Shipped',
    'mark_cleared' => 'Mark as Cleared',
    'status' => [
        'pending' => 'Pending',
        'shipped' => 'Shipped',
        'arrived' => 'Arrived',
        'cleared' => 'Cleared',
        'cancelled' => 'Cancelled',
    ],
    'mark_arrived' => 'Mark as Arrived',
    'view_details' => 'View Details',
    'edit_loading' => 'Edit Loading',
    'delete_loading' => 'Delete Loading',
    'confirm_delete' => 'Are you sure you want to delete this loading?',
    'loading_created' => 'Loading created successfully',
    'loading_updated' => 'Loading updated successfully',
    'loading_deleted' => 'Loading deleted successfully',
    'duplicate_container' => 'Container number already exists',
    'loading_number' => 'Loading Number',
        'enter_loading_number' => 'Enter loading number',
        'loading_number_hint' => 'Unique identifier for this loading',
        'loading_number_required' => 'Loading number is required',
        'auto_generated' => 'Auto Generated',
        'claim_auto_generate_hint' => 'Claim number will be generated automatically',
        'container_repeat_allowed' => 'Container numbers can be repeated',
        'auto_filled' => 'Auto filled from client code',
        'describe_items' => 'Describe the items being shipped',
        'additional_notes' => 'Any additional notes or instructions',
        'financial_auto_record_hint' => 'Purchase, commission, and shipping amounts will be automatically recorded in the client\'s account',
        'port_sudan_sync' => 'Port Sudan Synchronization',
        'port_sudan_sync_hint' => 'This loading will be automatically synchronized with the Port Sudan system and appear in containers.php',
        'create_loading' => 'Create Loading',
        'container_format_invalid' => 'Container number format is invalid. Must be 4 letters followed by 7 numbers.',
        'valid_client_required' => 'Please select a valid client from the list',
        'bol_updated' => 'Bill of Lading status updated successfully',
        'financial_details' => 'Financial Details',
        'sync_status' => 'Sync Status',
        'sync_pending' => 'Sync Pending',
        'sync_completed' => 'Sync Completed',
        'sync_failed' => 'Sync Failed',
        'no_office_selected' => 'No Office Selected',
        'office_notification_sent' => 'Notification will be sent to selected office',
        'current_file' => 'Current',
        'view_file' => 'View File',
        'processing' => 'Processing',
        'will_be_generated_automatically' => 'Will be generated automatically',
        'container_can_be_repeated' => 'Container numbers can now be repeated',
        'auto_filled_from_client_code' => 'Auto filled from client code',
        'describe_items_being_shipped' => 'Describe the items being shipped',
        'notification_will_be_sent' => 'Notification will be sent to selected office',
        'additional_notes_placeholder' => 'Any additional notes or instructions',
        'financial_details_message' => 'Purchase, commission, and shipping amounts will be automatically recorded in the client\'s account.',
        'port_sudan_synchronization' => 'Port Sudan Synchronization',
        'port_sudan_sync_message' => 'This loading will be automatically synchronized with the Port Sudan system and appear in containers.php',
        'select_valid_client' => 'Please select a valid client from the list',
        'basic_information' => 'Basic Information',
        'client_cargo_details' => 'Client & Cargo Details',
        'financial_information' => 'Financial Information',
        'office_notes' => 'Office & Notes',
],

'offices' => [
    'main' => 'Main Office',
    'port_sudan' => 'Port Sudan',
    'khartoum' => 'Khartoum',
    'kassala' => 'Kassala',
    'uae' => 'UAE',
    'tanzania' => 'Tanzania', 
    'egypt' => 'Egypt',
],

'payment' => [
    'cash' => 'Cash',
    'transfer' => 'Transfer',
    'check' => 'Check',
    'credit' => 'Credit',
],

'select' => 'Select',
'reset' => 'Reset',
'no_data' => 'No data found',
'info' => 'Information',
'updated_by' => 'Updated by',
'notes' => 'Notes',
    
    // Messages
    'messages' => [
        'saved_successfully' => 'Saved successfully',
        'updated_successfully' => 'Updated successfully',
        'deleted_successfully' => 'Deleted successfully',
        'operation_failed' => 'Operation failed',
        'are_you_sure' => 'Are you sure?',
        'no_data_found' => 'No data found',
        'access_denied' => 'Access denied',
        'session_expired' => 'Session expired',
        'type_or_select_bank' => 'Type bank name or select from list',
        'bank_autocomplete_hint' => 'Start typing to see suggestions, or enter a new bank name',
        'auto_generated_if_empty' => 'Auto-generated if empty',
        'select_client' => 'Select a client',
        'select_type' => 'Select transaction type',
        'transaction_not_found' => 'Transaction not found',
        'unauthorized' => 'Unauthorized access',
        'transaction_already_approved' => 'This transaction has already been approved',
        'error_approving_transaction' => 'Error approving transaction',
        'error_deleting_transaction' => 'Error deleting transaction',
        'confirm_approval' => 'Are you sure you want to approve this transaction?',
        'approval_warning' => 'This action will update client balances and create cashbox entries if applicable.',
        'confirm_approval_action' => 'Are you sure you want to approve this transaction? This action cannot be undone.',
        'invalid_client_code' => 'Invalid client code. Please select a valid client.',
        'duplicate_loading_number' => 'Loading number already exists within this fiscal year (March 1 - February 28)',
        'invalid_amount' => 'Invalid payment amount. Amount must be greater than 0.',
        'client_not_found' => 'Client not found.',
        'insufficient_balance' => 'Insufficient balance in selected currency.',
    'invalid_container_format' => 'Invalid container format. Must be 4 letters followed by 7 numbers.',
    'confirm_status_change' => 'Are you sure you want to change the status?',
    ],
    
    // Validation
    'validation' => [
        'required' => 'This field is required',
        'email' => 'Please enter a valid email',
        'numeric' => 'Must be a number',
        'min' => 'Minimum :min',
        'max' => 'Maximum :max',
        'unique' => 'This value already exists',
        'password_mismatch' => 'Passwords do not match',
        'invalid_password' => 'Current password is incorrect',
        'transaction_duplicate_bank' => 'Transaction number already used for this bank. Please verify.',
    ],
    'sync' => [
        'port_sudan_notification' => 'New container from China',
        'container_synced' => 'Container synchronized successfully',
        'sync_error' => 'Synchronization error occurred',
        'retry_sync' => 'Retry Synchronization',
        'sync_log' => 'Sync Log',
        'last_sync' => 'Last Sync',
        'sync_attempts' => 'Sync Attempts',
    ],
    'bol' => [
        'bill_of_lading' => 'Bill of Lading',
        'bol_status' => 'BOL Status',
        'bol_date' => 'BOL Date',
        'bol_file' => 'BOL File',
        'bol_not_issued' => 'Not Issued',
        'bol_issued' => 'Issued',
        'bol_delayed' => 'Delayed',
        'update_bol' => 'Update BOL',
        'issue_bol' => 'Issue BOL',
    ]
];