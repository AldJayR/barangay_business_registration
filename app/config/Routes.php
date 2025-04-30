<?php

return [
    // Auth routes
    '/login' => ['controller' => 'Auth', 'method' => 'login'],
    '/logout' => ['controller' => 'Auth', 'method' => 'logout'],
    '/register' => ['controller' => 'Auth', 'method' => 'register'],
    '/register-process' => ['controller' => 'Auth', 'method' => 'processRegistration'],
    '/admin/register-staff' => ['controller' => 'Auth', 'method' => 'registerStaff'],
    '/admin/process-register-staff' => ['controller' => 'Auth', 'method' => 'processRegisterStaff'],
    // Dashboard routes - role-specific paths
    '/owner/dashboard' => ['controller' => 'Dashboard', 'method' => 'ownerDashboard'],
    '/admin/dashboard' => ['controller' => 'Dashboard', 'method' => 'adminDashboard'],
    '/treasurer/dashboard' => ['controller' => 'Dashboard', 'method' => 'treasurerDashboard'],
    
    // Legacy route - redirect to appropriate dashboard based on role
    '/dashboard' => ['controller' => 'Dashboard', 'method' => 'redirectToDashboard'],
    
    // Business routes - for business owners
    '/business/apply' => ['controller' => 'Business', 'method' => 'apply'],
    '/business/submitApplication' => ['controller' => 'Business', 'method' => 'submitApplication'],
    '/business/applications' => ['controller' => 'Business', 'method' => 'applications'],
    '/business/list' => ['controller' => 'Business', 'method' => 'list'],
    '/business/view/{id}' => ['controller' => 'Business', 'method' => 'viewBusiness'],
    '/business/approve/{id}' => ['controller' => 'Business', 'method' => 'approve'],
    '/business/reject/{id}' => ['controller' => 'Business', 'method' => 'reject'],
    // Payment routes
    '/payment/view/{id}' => ['controller' => 'Payment', 'method' => 'viewPayment'],
    '/payment/history' => ['controller' => 'Payment', 'method' => 'history'],
    '/payment/upload/{id}' => ['controller' => 'Payment', 'method' => 'upload'],
    '/payment/cancel/{id}' => ['controller' => 'Payment', 'method' => 'cancel'],
    '/payment/verify/{id}' => ['controller' => 'Payment', 'method' => 'verify'],
    
    // Treasurer routes
    '/treasurer/verify' => ['controller' => 'Treasurer', 'method' => 'verify'],
    '/treasurer/verifyPayment/{id}' => ['controller' => 'Treasurer', 'method' => 'verifyPayment'],
    '/treasurer/history' => ['controller' => 'Treasurer', 'method' => 'history'],
    '/treasurer/reports' => ['controller' => 'Treasurer', 'method' => 'reports'],
    
    // Permit routes
    '/permit/view/{id}' => ['controller' => 'Permit', 'method' => 'viewPermit'],
    '/permit/generate/{id}' => ['controller' => 'Permit', 'method' => 'generate'],
    
    // Document routes
    '/document/upload/{id}' => ['controller' => 'Document', 'method' => 'upload'],
    '/document/viewDocument/{id}' => ['controller' => 'Document', 'method' => 'viewDocument'],
    '/document/delete/{id}' => ['controller' => 'Document', 'method' => 'delete'],
    '/document/verify/{id}' => ['controller' => 'Document', 'method' => 'verify'],
    '/document/pending' => ['controller' => 'Document', 'method' => 'pending'],
    '/document/serveFile/{filename}' => ['controller' => 'Document', 'method' => 'serve'],
    
    // Profile routes
    '/user/profile' => ['controller' => 'Profile', 'method' => 'index'],
    '/user/settings' => ['controller' => 'Profile', 'method' => 'settings'],
    'user/profile/update' => ['controller' => 'Profile', 'method' => 'updateProfile'],
    'user/profile/update-password' => ['controller' => 'Profile', 'method' => 'updatePassword'],
    
    // File serving route
    '/payment/proof/{filename}' => ['controller' => 'Payment', 'method' => 'serveProofFile'],
    
    // Notification routes
    '/notification' => ['controller' => 'Notification', 'method' => 'index'],
    '/notification/view/{id}' => ['controller' => 'Notification', 'method' => 'viewNotification'],
    '/notification/mark-read' => ['controller' => 'Notification', 'method' => 'markAllAsRead'],
    '/notification/delete/{id}' => ['controller' => 'Notification', 'method' => 'delete'],
    '/notification/count' => ['controller' => 'Notification', 'method' => 'getCount'],
    '/notification/settings' => ['controller' => 'Notification', 'method' => 'settings'],
    '/admin/generate-renewal-notifications' => ['controller' => 'Notification', 'method' => 'generateRenewalNotifications'],
    
    // Default home page
    '/' => ['controller' => 'Pages', 'method' => 'index'],
];