<?php
// Add these lines at the very top of header.php, after the session_start()
require_once 'includes/language.php';
require_once 'includes/db.php'; // Add this line to include database connection

// Get cart count for the user
$cartCount = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $cart_query = $conn->query("SELECT SUM(quantity) as count FROM cart WHERE user_id = $user_id");
    if ($count_result = $cart_query->fetch_assoc()) {
        $cartCount = $count_result['count'] ?? 0;
    }
} else if (isset($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}
?>
<nav class="bg-black text-white fixed w-full z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Main Nav -->
            <div class="flex items-center space-x-8">
                <a href="index.php" class="text-2xl font-bold">GoalSphere</a>
                <div class="hidden md:flex space-x-8">
                    <a href="matches.php" class="hover:text-gray-300"><?php echo __('matches'); ?></a>
                    <a href="gamezone.php" class="hover:text-gray-300"><?php echo __('Game Zone'); ?></a>
                    <a href="shop.php" class="hover:text-gray-300"><?php echo __('shop'); ?></a>
                </div>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center space-x-4">
                <!-- Cart Icon - Only in shop header -->
                <button onclick="toggleCart()" class="p-2 hover:bg-gray-800 rounded-full relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cartCount" class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                        <?php echo $cartCount; ?>
                    </span>
                </button>

                <div class="relative">
                <?php if(isset($_SESSION['email'])){ ?>
                    <a href="settings.php" class="p-2 hover:bg-gray-800 rounded-full inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                    <?php } ?>
                    <!-- Settings Panel -->
                    <div id="settingsPanel" class="hidden absolute right-0 mt-2 w-80 bg-gray-900 rounded-lg shadow-xl z-50">
                        <!-- Settings panel content -->
                    </div>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-4">
                        <!-- User Avatar -->
                        <?php if(isset($_SESSION['admin_email']) && $_SESSION['admin_email']=="admin123@gmail.com"): ?>
                        <div class="relative inline-block text-left">
                            <a href="#" class="flex items-center">
                                <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-600 transition-colors">
                                    <span class="text-white font-semibold text-lg">
                                        <?php 
                                        if(isset($_SESSION['email'])) {
                                            echo strtoupper(substr($_SESSION['email'],0,1));
                                        } else {
                                            echo 'A';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </a>
                        </div>

                        <!-- Logout/Dashboard Button -->
                            <a href="admin.php" class="bg-gray-700 text-red-400 px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                        <?php else: ?>
                            <div class="relative inline-block text-left">
                            <a href="profile.php" class="flex items-center">
                                <div class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center cursor-pointer hover:bg-gray-600 transition-colors">
                                    <span class="text-white font-semibold text-lg">
                                        <?php 
                                        if(isset($_SESSION['email'])) {
                                            echo strtoupper(substr($_SESSION['email'],0,1));
                                        } else {
                                            echo 'A';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </a>
                        </div>
                            <a href="logout.php" class="bg-gray-700 text-red-400 px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Logout</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <a href="signin.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        <?php echo __('signin'); ?>
                    </a>
                    <a href="register.php" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors border border-gray-600">
                        <?php echo __('join'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<!-- Add padding to account for fixed header -->
<div class="pt-16"></div> 