<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jersey Shop - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen">
    <?php include 'includes/header.php'; ?>

    <!-- Shop Header -->
    <div class="bg-gradient-to-r from-blue-900 to-black py-12">
        <div class="container mx-auto px-4">
            <div class="flex flex-col items-center text-center">
                <h1 class="text-4xl font-bold text-white mb-4">Jersey Shop</h1>
                <p class="text-gray-300 max-w-2xl">
                    Get your favorite team's jersey. Official merchandise with worldwide shipping.
                </p>
            </div>
        </div>
    </div>

    <!-- Shop Content -->
    <div class="container mx-auto px-4 py-12">
        <!-- Filters -->
        <div class="flex flex-wrap gap-4 mb-8">
            <select class="bg-gray-800 text-white px-4 py-2 rounded-lg">
                <option value="">All Teams</option>
                <option value="manchester-united">Manchester United</option>
                <option value="real-madrid">Real Madrid</option>
                <option value="barcelona">Barcelona</option>
                <!-- Add more teams -->
            </select>

            <select class="bg-gray-800 text-white px-4 py-2 rounded-lg">
                <option value="">All Sizes</option>
                <option value="S">Small</option>
                <option value="M">Medium</option>
                <option value="L">Large</option>
                <option value="XL">Extra Large</option>
            </select>

            <select class="bg-gray-800 text-white px-4 py-2 rounded-lg">
                <option value="">Price Range</option>
                <option value="0-50">$0 - $50</option>
                <option value="51-100">$51 - $100</option>
                <option value="101+">$101+</option>
            </select>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php
            // Database connection
            $host = 'localhost';
            $dbname = 'goalsphere';
            $username = 'root';
            $password = '';

            $conn = new mysqli($host, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch jerseys from database
            $sql = "SELECT j.*, 
                    GROUP_CONCAT(DISTINCT CONCAT(js.size, ':', js.quantity)) as sizes,
                    MIN(ji.image_url) as primary_image,
                    SUM(js.quantity) as total_quantity
                    FROM jerseys j 
                    LEFT JOIN jersey_sizes js ON j.id = js.jersey_id 
                    LEFT JOIN jersey_images ji ON j.id = ji.jersey_id
                    GROUP BY j.id 
                    HAVING total_quantity > 0
                    ORDER BY j.created_at DESC";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($jersey = $result->fetch_assoc()) {
                    ?>
                    <div class="bg-gray-900 rounded-lg overflow-hidden cursor-pointer" 
                         onclick="showJerseyDetails(<?php echo htmlspecialchars(json_encode($jersey)); ?>)">
                        <div class="jersey-image">
                            <?php
                            $images_sql = "SELECT image_url FROM jersey_images 
                                           WHERE jersey_id = {$jersey['id']} 
                                           ORDER BY is_primary DESC LIMIT 1";
                            $images_result = $conn->query($images_sql);
                            
                            if ($images_result && $images_result->num_rows > 0) {
                                $image = $images_result->fetch_assoc();
                                echo '<img src="' . htmlspecialchars($image['image_url']) . '" 
                                           alt="Jersey" 
                                           class="w-full h-64 object-cover">';
                            } else {
                                echo '<img src="assets/images/default-jersey.jpg" 
                                           alt="No image available" 
                                           class="w-full h-64 object-cover">';
                            }
                            ?>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="text-white font-semibold text-center truncate">
                                <?php echo htmlspecialchars($jersey['name']); ?>
                            </h3>
                            <div class="text-xl font-bold text-white text-center">
                                ₹<?php echo number_format($jersey['price'], 2); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="text-gray-400 col-span-4 text-center">No jerseys available at the moment.</p>';
            }

            $conn->close();
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Add this modal HTML before the closing body tag -->
    <div id="jerseyModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50">
        <div class="bg-gray-900 p-6 rounded-lg max-w-2xl w-full mx-4">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-white" id="modalTitle"></h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div id="modalImage" class="w-full"></div>
                <div class="space-y-4">
                    <p id="modalDescription" class="text-gray-400"></p>
                    <div>
                        <h3 class="text-white font-semibold mb-2">Select Size:</h3>
                        <div id="modalSizes" class="flex flex-wrap gap-2"></div>
                    </div>
                    <div id="quantitySection" class="hidden">
                        <h3 class="text-white font-semibold mb-2">Quantity:</h3>
                        <div class="flex items-center gap-2">
                            <button onclick="updateQuantity(-1)" class="px-3 py-1 bg-gray-800 text-white rounded-lg">-</button>
                            <input type="number" id="quantityInput" value="1" min="1" 
                                   class="w-20 px-3 py-1 bg-gray-800 text-white rounded-lg text-center" readonly>
                            <button onclick="updateQuantity(1)" class="px-3 py-1 bg-gray-800 text-white rounded-lg">+</button>
                            <span id="availableQuantity" class="text-gray-400 ml-2"></span>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-white" id="modalPrice"></div>
                    <button id="modalAddToCart" onclick="addToCart()" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors opacity-50 cursor-not-allowed">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before closing body tag -->
    <div id="cartModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-gray-900 p-6 rounded-lg max-w-2xl w-full mx-4">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-2xl font-bold text-white">Your Cart</h2>
                <button onclick="toggleCart()" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="cartItems" class="space-y-4 max-h-96 overflow-y-auto"></div>
            <div class="mt-4 pt-4 border-t border-gray-700">
                <div class="flex justify-between text-white mb-4">
                    <span class="text-xl">Total:</span>
                    <span class="text-xl font-bold" id="cartTotal">₹0.00</span>
                </div>
                <div class="flex space-x-4">
                    <button onclick="keepShopping()" 
                            class="flex-1 bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                        Keep Shopping
                    </button>
                    <button id="checkoutBtn" 
                            onclick="proceedToCheckout()" 
                            class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let selectedSize = null;
    let maxQuantity = 0;
    let currentJersey = null;
    let basePrice = 0;

    function showJerseyDetails(jersey) {
        currentJersey = jersey;
        selectedSize = null;
        maxQuantity = 0;
        basePrice = parseFloat(jersey.price);
        
        const modal = document.getElementById('jerseyModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        const modalPrice = document.getElementById('modalPrice');
        const modalSizes = document.getElementById('modalSizes');
        const modalImage = document.getElementById('modalImage');
        const modalAddToCart = document.getElementById('modalAddToCart');
        const quantitySection = document.getElementById('quantitySection');
        
        document.getElementById('quantityInput').value = 1;
        quantitySection.classList.add('hidden');
        modalAddToCart.classList.add('opacity-50', 'cursor-not-allowed');

        modalTitle.textContent = jersey.name;
        modalDescription.textContent = jersey.description;
        modalPrice.textContent = `₹${parseFloat(jersey.price).toFixed(2)}`;
        
        modalImage.innerHTML = `<img src="${jersey.primary_image}" class="w-full rounded-lg">`;
        
        modalSizes.innerHTML = '';
        if (jersey.sizes) {
            const sizes = jersey.sizes.split(',');
            sizes.forEach(sizeInfo => {
                const [size, quantity] = sizeInfo.split(':');
                const qtyAvailable = parseInt(quantity);
                if (qtyAvailable > 0) {
                    const sizeBtn = document.createElement('button');
                    sizeBtn.className = 'px-4 py-2 border border-gray-600 rounded-lg text-white hover:bg-gray-700 transition-colors';
                    sizeBtn.innerHTML = `${size}<br><span class="text-sm text-gray-400">(${qtyAvailable} left)</span>`;
                    sizeBtn.onclick = () => selectSize(size, qtyAvailable);
                    modalSizes.appendChild(sizeBtn);
                }
            });
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function selectSize(size, quantity) {
        selectedSize = size;
        maxQuantity = quantity;
        
        const sizeBtns = document.querySelectorAll('#modalSizes button');
        sizeBtns.forEach(btn => {
            if (btn.textContent.startsWith(size)) {
                btn.classList.add('bg-blue-600', 'border-blue-600');
            } else {
                btn.classList.remove('bg-blue-600', 'border-blue-600');
            }
        });
        
        quantitySection = document.getElementById('quantitySection');
        quantitySection.classList.remove('hidden');
        
        document.getElementById('quantityInput').value = 1;
        document.getElementById('availableQuantity').textContent = `${maxQuantity} available`;
        
        const modalPrice = document.getElementById('modalPrice');
        modalPrice.textContent = `₹${basePrice.toFixed(2)}`;
        
        const addToCartBtn = document.getElementById('modalAddToCart');
        addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    }

    function updateQuantity(change) {
        const input = document.getElementById('quantityInput');
        let newValue = parseInt(input.value) + change;
        
        newValue = Math.max(1, Math.min(newValue, maxQuantity));
        input.value = newValue;
        
        const modalPrice = document.getElementById('modalPrice');
        const totalPrice = basePrice * newValue;
        modalPrice.textContent = `₹${totalPrice.toFixed(2)}`;
    }

    function addToCart() {
        if (!selectedSize || !currentJersey) {
            alert('Please select a size first');
            return;
        }

        const quantity = parseInt(document.getElementById('quantityInput').value);
        
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                jersey_id: currentJersey.id,
                size: selectedSize,
                quantity: quantity,
                price: currentJersey.price
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                document.getElementById('cartCount').textContent = data.cartCount;
                
                // Show success message
                alert('Added to cart successfully!');
                
                // Close the jersey details modal
                closeModal();
            } else {
                alert(data.message || 'Error adding to cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding to cart');
        });
    }

    function closeModal() {
        const modal = document.getElementById('jerseyModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function toggleCart() {
        const cartModal = document.getElementById('cartModal');
        if (cartModal.classList.contains('hidden')) {
            // Fetch and display cart contents
            fetch('get_cart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartItems = document.getElementById('cartItems');
                        cartItems.innerHTML = '';
                        
                        if (data.items.length === 0) {
                            cartItems.innerHTML = '<p class="text-gray-400 text-center">Your cart is empty</p>';
                            document.getElementById('checkoutBtn').style.display = 'none';
                            document.getElementById('cartTotal').textContent = '₹0.00';
                            document.getElementById('cartCount').textContent = '0';
                        } else {
                            let total = 0;
                            data.items.forEach(item => {
                                const subtotal = item.price * item.quantity;
                                total += subtotal;
                                
                                cartItems.innerHTML += `
                                    <div class="flex items-center space-x-4 bg-gray-800 p-4 rounded-lg">
                                        <img src="${item.image_url || 'assets/images/default-jersey.jpg'}" 
                                             alt="${item.name}" 
                                             class="w-20 h-20 object-cover rounded">
                                        <div class="flex-1">
                                            <h3 class="text-white font-semibold">${item.name}</h3>
                                            <p class="text-gray-400">Size: ${item.size}</p>
                                            <div class="flex justify-between items-center mt-2">
                                                <p class="text-gray-400">Quantity: ${item.quantity}</p>
                                                <p class="text-white">₹${parseFloat(item.price).toFixed(2)}</p>
                                            </div>
                                            <div class="flex justify-between items-center mt-2">
                                                <button onclick="removeFromCart('${item.id}')" 
                                                        class="text-red-500 hover:text-red-400 text-sm">
                                                    Remove
                                                </button>
                                                <p class="text-white font-bold">₹${subtotal.toFixed(2)}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            document.getElementById('checkoutBtn').style.display = 'block';
                            document.getElementById('cartTotal').textContent = `₹${total.toFixed(2)}`;
                            document.getElementById('cartCount').textContent = data.cartCount;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching cart:', error);
                });
            
            cartModal.classList.remove('hidden');
            cartModal.classList.add('flex');
        } else {
            cartModal.classList.add('hidden');
            cartModal.classList.remove('flex');
        }
    }

    function removeFromCart(cartId) {
        if (confirm('Are you sure you want to remove this item?')) {
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cart_id: cartId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cartCount').textContent = data.cartCount;
                    updateCart();
                }
            });
        }
    }

    function keepShopping() {
        const cartModal = document.getElementById('cartModal');
        cartModal.classList.add('hidden');
        cartModal.classList.remove('flex');
    }

    function proceedToCheckout() {
        // Check if user is logged in (you can modify this based on your session handling)
        <?php if(!isset($_SESSION['user_id'])): ?>
            window.location.href = 'signin.php';
            return;
        <?php endif; ?>
        
        // Redirect to checkout page
        window.location.href = 'checkout.php';
    }

    document.getElementById('jerseyModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    </script>

    <style>
        .jersey-gallery {
            position: relative;
        }

        .main-image {
            width: 100%;
            margin-bottom: 1rem;
        }

        .main-image img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }

        .thumbnail-gallery {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem 0;
        }

        .thumbnail-gallery img {
            width: 4rem;
            height: 4rem;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .thumbnail-gallery img:hover {
            border-color: #3b82f6;
        }
    </style>
</body>
</html>