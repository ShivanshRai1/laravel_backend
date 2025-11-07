<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Creating Admin User for Newsletter Access ===\n\n";

use App\Models\User;

// Check if admin user already exists
$adminUser = User::where('email', 'admin@dashboard.com')->first();

if ($adminUser) {
    echo "Admin user already exists!\n";
    echo "Email: admin@dashboard.com\n";
    echo "Role: " . $adminUser->role . "\n\n";
    
    // Update to admin role if not already
    if ($adminUser->role !== 'admin') {
        $adminUser->update(['role' => 'admin']);
        echo "✅ User role updated to admin\n";
    }
} else {
    // Create new admin user
    $adminUser = User::create([
        'name' => 'Admin User',
        'email' => 'admin@dashboard.com',
        'password' => bcrypt('admin123'), // Change this password!
        'role' => 'admin',
        'email_verified_at' => now()
    ]);
    
    echo "✅ Admin user created successfully!\n";
    echo "Email: admin@dashboard.com\n";
    echo "Password: admin123\n";
    echo "Role: admin\n\n";
}

echo "=== Newsletter Access Instructions ===\n";
echo "1. Login with admin credentials above\n";
echo "2. Navigate to: http://localhost:3001/newsletter\n";
echo "3. Or look for 'Newsletter/Emails' in the admin sidebar section\n\n";

// Also create an editor user
$editorUser = User::where('email', 'editor@dashboard.com')->first();

if (!$editorUser) {
    $editorUser = User::create([
        'name' => 'Editor User',
        'email' => 'editor@dashboard.com',
        'password' => bcrypt('editor123'),
        'role' => 'editor',
        'email_verified_at' => now()
    ]);
    
    echo "✅ Editor user also created!\n";
    echo "Email: editor@dashboard.com\n";
    echo "Password: editor123\n";
    echo "Role: editor\n\n";
}

echo "Both admin and editor roles can access Newsletter management.\n";
echo "Regular users only see newsletter subscription settings in their profile.\n";