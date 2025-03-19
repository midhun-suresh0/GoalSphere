<div class="bg-gray-900 text-white w-64 flex-shrink-0 flex flex-col">
    <div class="p-4">
        <h2 class="text-2xl font-bold">GoalSphere</h2>
        <p class="text-sm text-gray-400">Admin Dashboard</p>
    </div>
    
    <nav class="mt-8 flex-grow">
        <div class="px-4">
            <h3 class="text-xs uppercase text-gray-500 font-semibold">Management</h3>
            <div class="mt-4 space-y-2">
                <a href="admin.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">Dashboard</a>
                <a href="manage_users.php" class="block px-4 py-2 rounded-lg bg-gray-800 text-white">Users</a>
                
                <a href="admin_news.php" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">News</a>
                
                <a href="admin_jerseys.php" 
                   class="block px-4 py-2 rounded-lg <?php echo $current_page === 'admin_jerseys.php' ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
                    Manage Jerseys
                </a>
            </div>
        </div>
        
        <div class="px-4 mt-8">
            <h3 class="text-xs uppercase text-gray-500 font-semibold">Analytics</h3>
            <div class="mt-4 space-y-2">
                <a href="#traffic" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">Traffic</a>
                <a href="#activity" class="block px-4 py-2 rounded-lg hover:bg-gray-800 text-gray-300">Activity Logs</a>
            </div>
        </div>
    </nav>

    <div class="p-4 border-t border-gray-800">
        <a href="index.php" class="block px-4 py-2 text-gray-400 hover:text-white mb-2">
            ← Back to Site
        </a>
        <a href="logout.php" class="block px-4 py-2 text-red-400 hover:text-red-300 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Logout
        </a>
    </div>
</div> 