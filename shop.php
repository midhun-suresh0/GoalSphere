<?php
session_start();
require_once 'includes/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - GoalSphere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="css/styles.css" rel="stylesheet">
</head>
<body class="bg-black min-h-screen flex flex-col">
    <?php include 'includes/shop_header.php'; ?>

    <!-- Hero Banner -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="images/shirt.jpg" alt="Stadium" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-60"></div>
        </div>
        <div class="container mx-auto px-4 py-16 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Premium Football Jerseys</h1>
                <p class="text-xl text-blue-200 mb-8">Authentic jerseys from your favorite teams around the world</p>
                <div class="flex justify-center space-x-4">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm px-6 py-3 rounded-full text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Official Merchandise</span>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm px-6 py-3 rounded-full text-white flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Premium Quality</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <!-- Search Bar and Orders Button -->
        <div class="flex flex-col md:flex-row items-center justify-center gap-4 mb-8">
            <!-- Search Bar -->
            <div class="w-full md:w-1/2">
                <form action="shop.php" method="GET" class="flex">
                    <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                           placeholder="Search jerseys..." 
                           class="w-full bg-gray-800 text-white px-4 py-3 rounded-l-lg focus:outline-none">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-r-lg hover:bg-blue-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- Orders Button -->
            <a href="orders.php" class="flex items-center justify-center bg-gray-800 text-white px-4 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>My Orders</span>
            </a>
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

            // Fetch jerseys from database with search
            $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
            
            $sql = "SELECT j.*, 
                    GROUP_CONCAT(DISTINCT CASE WHEN js.quantity > 0 THEN js.size END) as available_sizes,
                    MIN(ji.image_url) as primary_image
                    FROM jerseys j 
                    LEFT JOIN jersey_sizes js ON j.id = js.jersey_id 
                    LEFT JOIN jersey_images ji ON j.id = ji.jersey_id
                    WHERE j.status = 'available'";
            
            // Add search condition if search parameter exists
            if (!empty($search)) {
                $sql .= " AND (j.name LIKE '%$search%' OR j.description LIKE '%$search%')";
            }
            
            $sql .= " GROUP BY j.id ORDER BY j.created_at DESC";

            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($jersey = $result->fetch_assoc()) {
                    ?>
                    <div class="bg-gray-900 rounded-lg overflow-hidden cursor-pointer hover:shadow-lg hover:shadow-blue-900/30 transition-all duration-300" 
                         onclick="showJerseyDetails(<?php echo htmlspecialchars(json_encode($jersey)); ?>)">
                        <div class="jersey-image relative">
                            <?php
                            $images_sql = "SELECT image_url FROM jersey_images 
                                           WHERE jersey_id = {$jersey['id']} 
                                           ORDER BY is_primary DESC LIMIT 1";
                            $images_result = $conn->query($images_sql);
                            
                            if ($images_result && $images_result->num_rows > 0) {
                                $image = $images_result->fetch_assoc();
                                echo '<img src="' . htmlspecialchars($image['image_url']) . '" 
                                           alt="Jersey" 
                                           class="w-full h-64 object-cover transition-transform duration-300 hover:scale-105">';
                            } else {
                                echo '<img src="assets/images/default-jersey.jpg" 
                                           alt="No image available" 
                                           class="w-full h-64 object-cover transition-transform duration-300 hover:scale-105">';
                            }
                            ?>
                            <div class="absolute top-2 right-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                                New
                            </div>
                        </div>
                        <div class="p-4 space-y-2">
                            <h3 class="text-white font-semibold text-lg"><?php echo htmlspecialchars($jersey['name']); ?></h3>
                            <p class="text-gray-400 text-sm line-clamp-2"><?php echo htmlspecialchars($jersey['description']); ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-white font-bold">₹<?php echo number_format($jersey['price'], 2); ?></span>
                                <button class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">View</button>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                if (!empty($search)) {
                    echo '<p class="text-gray-400 col-span-4 text-center">No jerseys found matching "' . htmlspecialchars($search) . '". Try a different search term.</p>';
                } else {
                    echo '<p class="text-gray-400 col-span-4 text-center">No jerseys available at the moment.</p>';
                }
            }

            $conn->close();
            ?>
        </div>
    </div>

    <!-- Jersey Details Modal -->
    <div id="jerseyModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden">
        <div class="container mx-auto px-4 h-full flex items-center justify-center">
            <div class="bg-gray-900 rounded-lg p-6 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-end mb-4">
                    <button onclick="closeModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <!-- Main Image Slideshow -->
                        <div id="modalImageSlideshow" class="rounded-lg overflow-hidden relative">
                            <div id="modalMainImage" class="w-full h-80 bg-gray-800 flex items-center justify-center">
                                <!-- Main image will be displayed here -->
                            </div>
                            
                            <!-- Navigation Arrows -->
                            <button id="prevImageBtn" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button id="nextImageBtn" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                            
                            <!-- Image Counter -->
                            <div class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white px-2 py-1 rounded text-sm">
                                <span id="currentImageIndex">1</span>/<span id="totalImages">1</span>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <div id="imageThumbnails" class="flex space-x-2 overflow-x-auto py-2">
                            <!-- Thumbnails will be added here -->
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h2 id="modalTitle" class="text-2xl font-bold text-white"></h2>
                        <p id="modalPrice" class="text-xl font-bold text-white"></p>
                        <p id="modalDescription" class="text-gray-400"></p>
                        
                        <div>
                            <h3 class="text-white font-semibold mb-2">Select Size:</h3>
                            <div id="modalSizes" class="flex flex-wrap gap-2"></div>
                        </div>
                        
                        <div id="quantitySection" class="hidden">
                            <h3 class="text-white font-semibold mb-2">Quantity:</h3>
                            <div class="flex items-center space-x-2">
                                <button onclick="updateQuantity(-1)" class="bg-gray-800 text-white w-8 h-8 flex items-center justify-center rounded-full">-</button>
                                <input type="number" id="quantityInput" min="1" value="1" class="bg-gray-800 text-white w-16 text-center rounded px-2 py-1">
                                <button onclick="updateQuantity(1)" class="bg-gray-800 text-white w-8 h-8 flex items-center justify-center rounded-full">+</button>
                            </div>
                        </div>
                        
                        <button id="modalAddToCart" onclick="addToCart()" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition-colors opacity-50 cursor-not-allowed">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Cart Modal -->
    <div id="cartModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden">
        <div class="container mx-auto px-4 h-full flex items-center justify-center">
            <div class="bg-gray-900 rounded-lg p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white">Your Cart</h2>
                    <button onclick="toggleCart()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div id="cartItems" class="space-y-4 mb-6">
                    <!-- Cart items will be loaded here -->
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-700">
                    <div class="flex justify-between text-white mb-4">
                        <span class="text-xl">Total:</span>
                        <span class="text-xl font-bold" id="cartTotal">₹0.00</span>
                    </div>
                    <div class="flex space-x-4">
                        <button onclick="toggleCart()" 
                                class="flex-1 bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                            Keep Shopping
                        </button>
                        <a href="checkout.php" id="checkoutBtn" 
                           class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-center">
                            Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 mt-auto">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">GoalSphere</h3>
                    <p class="text-gray-400">Your one-stop destination for premium football jerseys and merchandise.</p>
                </div>
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="shop.php" class="text-gray-400 hover:text-white">Shop</a></li>
                        <li><a href="teams.php" class="text-gray-400 hover:text-white">Teams</a></li>
                        <li><a href="matches.php" class="text-gray-400 hover:text-white">Matches</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">Customer Service</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Shipping Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Returns & Refunds</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-white text-lg font-semibold mb-4">Connect With Us</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.477 2 12c0 5.523 4.477 10 10 10s10-4.477 10-10c0-5.523-4.477-10-10-10zm5.5 5.5h-2.775c-.144 0-.25.11-.25.25v1.5c0 .14.11.25.25.25h2.775c.14 0 .25-.11.25-.25v-1.5c0-.14-.11-.25-.25-.25zM12 7.5c-2.48 0-4.5 2.02-4.5 4.5s2.02 4.5 4.5 4.5 4.5-2.02 4.5-4.5-2.02-4.5-4.5-4.5zm0 7.5c-1.65 0-3-1.35-3-3s1.35-3 3-3 3 1.35 3 3-1.35 3-3 3z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> GoalSphere. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script>
    let currentJersey = null;
    let selectedSize = null;
    let maxQuantity = 0;
    let basePrice = 0;
    let jerseyImages = [];
    let currentImageIndex = 0;
    
    function showJerseyDetails(jersey) {
        currentJersey = jersey;
        selectedSize = null;
        maxQuantity = 0;
        basePrice = parseFloat(jersey.price);
        currentImageIndex = 0;
        
        const modal = document.getElementById('jerseyModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        const modalPrice = document.getElementById('modalPrice');
        const modalMainImage = document.getElementById('modalMainImage');
        const modalSizes = document.getElementById('modalSizes');
        const modalAddToCart = document.getElementById('modalAddToCart');
        const quantitySection = document.getElementById('quantitySection');
        const imageThumbnails = document.getElementById('imageThumbnails');
        
        document.getElementById('quantityInput').value = 1;
        quantitySection.classList.add('hidden');
        modalAddToCart.classList.add('opacity-50', 'cursor-not-allowed');

        modalTitle.textContent = jersey.name;
        modalDescription.textContent = jersey.description;
        modalPrice.textContent = `₹${parseFloat(jersey.price).toFixed(2)}`;
        
        // Fetch all images for this jersey
        fetch(`get_jersey_images.php?jersey_id=${jersey.id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    jerseyImages = data.images;
                    
                    // Update image counter
                    document.getElementById('currentImageIndex').textContent = '1';
                    document.getElementById('totalImages').textContent = jerseyImages.length;
                    
                    // Display the first image
                    if (jerseyImages.length > 0) {
                        modalMainImage.innerHTML = `<img src="${jerseyImages[0].image_url}" class="w-full h-full object-contain">`;
                    } else {
                        modalMainImage.innerHTML = `<div class="text-gray-500">No images available</div>`;
                    }
                    
                    // Create thumbnails
                    imageThumbnails.innerHTML = '';
                    jerseyImages.forEach((image, index) => {
                        const thumbnail = document.createElement('div');
                        thumbnail.className = `w-16 h-16 rounded overflow-hidden cursor-pointer ${index === 0 ? 'ring-2 ring-blue-500' : ''}`;
                        thumbnail.innerHTML = `<img src="${image.image_url}" class="w-full h-full object-cover">`;
                        thumbnail.onclick = () => showImage(index);
                        imageThumbnails.appendChild(thumbnail);
                    });
                    
                    // Show/hide navigation buttons based on image count
                    document.getElementById('prevImageBtn').style.display = jerseyImages.length > 1 ? 'block' : 'none';
                    document.getElementById('nextImageBtn').style.display = jerseyImages.length > 1 ? 'block' : 'none';
                }
            });
        
        // Update size buttons
        modalSizes.innerHTML = '';
        if (jersey.available_sizes) {
            const sizes = jersey.available_sizes.split(',');
            sizes.forEach(size => {
                if (size) {  // Only create button if size exists
                    const sizeBtn = document.createElement('button');
                    sizeBtn.className = 'px-4 py-2 border border-gray-600 rounded-lg text-white hover:bg-gray-700 transition-colors';
                    sizeBtn.textContent = size;
                    sizeBtn.onclick = () => selectSize(size);
                    modalSizes.appendChild(sizeBtn);
                }
            });
        }

        modal.classList.remove('hidden');
        
        // Set up event listeners for image navigation
        document.getElementById('prevImageBtn').onclick = prevImage;
        document.getElementById('nextImageBtn').onclick = nextImage;
    }
    
    function showImage(index) {
        if (index < 0 || index >= jerseyImages.length) return;
        
        currentImageIndex = index;
        const modalMainImage = document.getElementById('modalMainImage');
        modalMainImage.innerHTML = `<img src="${jerseyImages[index].image_url}" class="w-full h-full object-contain">`;
        
        // Update counter
        document.getElementById('currentImageIndex').textContent = (index + 1).toString();
        
        // Update thumbnail selection
        const thumbnails = document.querySelectorAll('#imageThumbnails > div');
        thumbnails.forEach((thumb, i) => {
            if (i === index) {
                thumb.classList.add('ring-2', 'ring-blue-500');
            } else {
                thumb.classList.remove('ring-2', 'ring-blue-500');
            }
        });
    }
    
    function nextImage() {
        showImage((currentImageIndex + 1) % jerseyImages.length);
    }
    
    function prevImage() {
        showImage((currentImageIndex - 1 + jerseyImages.length) % jerseyImages.length);
    }
    
    function selectSize(size) {
        selectedSize = size;
        
        // Get the quantity for this size
        fetch(`get_size_quantity.php?jersey_id=${currentJersey.id}&size=${size}`)
            .then(response => response.json())
            .then(data => {
                maxQuantity = data.quantity;
                
                const sizeBtns = document.querySelectorAll('#modalSizes button');
                sizeBtns.forEach(btn => {
                    if (btn.textContent === size) {
                        btn.classList.add('bg-blue-600', 'border-blue-600');
                    } else {
                        btn.classList.remove('bg-blue-600', 'border-blue-600');
                    }
                });
                
                const quantitySection = document.getElementById('quantitySection');
                quantitySection.classList.remove('hidden');
                
                document.getElementById('quantityInput').value = 1;
                
                const modalPrice = document.getElementById('modalPrice');
                modalPrice.textContent = `₹${basePrice.toFixed(2)}`;
                
                const addToCartBtn = document.getElementById('modalAddToCart');
                addToCartBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            });
    }
    
    function updateQuantity(change) {
        const input = document.getElementById('quantityInput');
        let quantity = parseInt(input.value) + change;
        
        // Ensure quantity is within valid range
        quantity = Math.max(1, Math.min(quantity, maxQuantity));
        input.value = quantity;
    }
    
    function closeModal() {
        document.getElementById('jerseyModal').classList.add('hidden');
    }
    
    function addToCart() {
        if (!selectedSize) {
            alert('Please select a size');
            return;
        }

        const quantity = parseInt(document.getElementById('quantityInput').value);
        if (quantity < 1 || quantity > maxQuantity) {
            alert(`Please select a quantity between 1 and ${maxQuantity}`);
            return;
        }

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
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching cart:', error);
                });
            
            cartModal.classList.remove('hidden');
        } else {
            cartModal.classList.add('hidden');
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
                    // Update cart count and refresh cart display
                    document.getElementById('cartCount').textContent = data.cartCount;
                    toggleCart(); // Refresh cart contents
                }
            });
        }
    }
    
    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const jerseyModal = document.getElementById('jerseyModal');
        const cartModal = document.getElementById('cartModal');
        
        if (event.target === jerseyModal) {
            closeModal();
        }
        
        if (event.target === cartModal) {
            toggleCart();
        }
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
            document.getElementById('cartModal').classList.add('hidden');
        }
    });
    </script>
</body>
</html>