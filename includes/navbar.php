<?php
// Check if user is admin
$is_admin = isset($_SESSION['admin_id']);
?>

<nav class="bg-black text-white fixed w-full z-50">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <a href="index.php" class="text-2xl font-bold">GoalSphere</a>
            </div>
            
            <?php if ($is_admin): ?>
            <div class="flex items-center space-x-4">
                <a href="admin.php" class="flex items-center text-gray-300 hover:text-white">
                    
                       
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Dashboard
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav> 