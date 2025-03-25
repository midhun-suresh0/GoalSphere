<!-- Add this to your header.php where the cart icon is -->
<a href="cart.php" class="text-gray-600 hover:text-gray-900 relative">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
    </svg>
    
    <?php if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
        <span class="cart-count absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">
            <?php echo count($_SESSION['cart']); ?>
        </span>
    <?php endif; ?>
</a> 