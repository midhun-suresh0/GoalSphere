<?php
session_start();
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}

// Get cart items
$user_id = $_SESSION['user_id'];
$cart_sql = "SELECT c.*, j.name, j.price, 
             (SELECT image_url FROM jersey_images 
              WHERE jersey_id = j.id 
              ORDER BY is_primary DESC 
              LIMIT 1) as image_url 
             FROM cart c 
             JOIN jerseys j ON c.jersey_id = j.id 
             WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug line to check cart items
error_log("Cart Items: " . print_r($cart_items, true));

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Initialize shipping cost
$shippingCost = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GoalSphere</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black min-h-screen">
    <?php include 'includes/checkout_header.php'; ?>

    <div class="container mx-auto px-4 py-12">
        <!-- Checkout Steps -->
        <div class="flex justify-center mb-8">
            <div class="flex items-center">
                <div id="step1" class="flex items-center">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center">1</div>
                    <span class="ml-2 text-white">Your Details</span>
                </div>
                <div class="w-16 h-1 mx-4 bg-gray-700" id="line1"></div>
                <div id="step2" class="flex items-center">
                    <div class="w-8 h-8 bg-gray-700 text-white rounded-full flex items-center justify-center">2</div>
                    <span class="ml-2 text-gray-400">Shipping</span>
                </div>
                <div class="w-16 h-1 mx-4 bg-gray-700" id="line2"></div>
                <div id="step3" class="flex items-center">
                    <div class="w-8 h-8 bg-gray-700 text-white rounded-full flex items-center justify-center">3</div>
                    <span class="ml-2 text-gray-400">Payment</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Checkout Forms -->
            <div class="md:col-span-2">
                <!-- Step 1: Your Details -->
                <div id="detailsStep" class="bg-gray-900 p-6 rounded-lg mb-4">
                    <h2 class="text-xl font-bold text-white mb-4">Your Details</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-400 mb-1">Full Name</label>
                            <input type="text" id="fullName" class="w-full p-2 bg-gray-800 text-white rounded">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Email</label>
                            <input type="email" id="email" class="w-full p-2 bg-gray-800 text-white rounded">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Phone</label>
                            <input type="tel" id="phone" class="w-full p-2 bg-gray-800 text-white rounded">
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">Address</label>
                            <input type="text" id="address" class="w-full p-2 bg-gray-800 text-white rounded">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-400 mb-1">City</label>
                                <input type="text" id="city" class="w-full p-2 bg-gray-800 text-white rounded">
                            </div>
                            <div>
                                <label class="block text-gray-400 mb-1">Postal Code</label>
                                <input type="text" id="postalCode" class="w-full p-2 bg-gray-800 text-white rounded">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-400 mb-1">State</label>
                            <input type="text" id="state" class="w-full p-2 bg-gray-800 text-white rounded">
                        </div>
                        <button onclick="nextStep(2)" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Continue to Shipping
                        </button>
                    </div>
                </div>

                <!-- Step 2: Shipping -->
                <div id="shippingStep" class="bg-gray-900 p-6 rounded-lg mb-4 hidden">
                    <h2 class="text-xl font-bold text-white mb-4">Choose Shipping Method</h2>
                    <div class="space-y-4">
                        <label class="block p-4 border border-gray-700 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="radio" name="shipping_method" value="standard" class="hidden" onchange="updateShippingCost(50)">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-white font-semibold">Standard Shipping</h3>
                                    <p class="text-gray-400 text-sm">Delivery in 5-7 business days</p>
                                </div>
                                <div class="text-white font-bold">₹50.00</div>
                            </div>
                        </label>

                        <label class="block p-4 border border-gray-700 rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                            <input type="radio" name="shipping_method" value="express" class="hidden" onchange="updateShippingCost(100)">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-white font-semibold">Express Shipping</h3>
                                    <p class="text-gray-400 text-sm">Delivery in 2-3 business days</p>
                                </div>
                                <div class="text-white font-bold">₹100.00</div>
                            </div>
                        </label>

                        <div class="flex space-x-4 mt-6">
                            <button onclick="prevStep(1)" class="bg-gray-700 text-white px-6 py-2 rounded hover:bg-gray-600">
                                Back
                            </button>
                            <button onclick="nextStep(3)" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                                Continue to Payment
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Payment -->
                <div id="paymentStep" class="bg-gray-900 p-6 rounded-lg mb-4 hidden">
                    <h2 class="text-xl font-bold text-white mb-4">Payment</h2>
                    <div class="space-y-4">
                        <div class="bg-gray-800 p-6 rounded-lg">
                            <div class="space-y-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-white mb-2">Total Amount</div>
                                    <div class="text-3xl font-bold text-blue-500" id="paymentTotalAmount">₹<?php echo number_format($total, 2); ?></div>
                                </div>
                                <div class="text-gray-400 text-sm text-center">
                                    You will be redirected to a secure payment gateway
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-4">
                            <button onclick="prevStep(2)" class="bg-gray-700 text-white px-6 py-2 rounded hover:bg-gray-600">
                                Back
                            </button>
                            <button onclick="startPayment()" class="bg-blue-600 text-white px-8 py-3 rounded hover:bg-blue-700 flex-grow text-lg font-semibold">
                                Pay Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="md:col-span-1">
                <div class="bg-gray-900 p-6 rounded-lg sticky top-4">
                    <h2 class="text-xl font-bold text-white mb-4">Order Summary</h2>
                    <div class="space-y-4">
                        <?php 
                        $total = 0;
                        if (empty($cart_items)) {
                            echo '<p class="text-gray-400">No items in cart</p>';
                        } else {
                            foreach ($cart_items as $item): 
                                $subtotal = $item['price'] * $item['quantity'];
                                $total += $subtotal;
                        ?>
                            <div class="flex items-center space-x-4 pb-4 border-b border-gray-700">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'assets/images/default-jersey.jpg'); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     class="w-16 h-16 object-cover rounded"
                                     onerror="this.src='assets/images/default-jersey.jpg'">
                                <div class="flex-1">
                                    <h3 class="text-white font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-400">Size: <?php echo htmlspecialchars($item['size']); ?></p>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-gray-400">Qty: <?php echo $item['quantity']; ?></p>
                                        <p class="text-gray-400">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></p>
                                    </div>
                                    <div class="text-right text-white font-bold mt-1">
                                        ₹<?php echo number_format($subtotal, 2); ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endforeach;
                        ?>

                        <div class="pt-4">
                            <div class="flex justify-between text-gray-400">
                                <span>Items (<?php echo count($cart_items); ?>)</span>
                                <span>₹<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-gray-400 mt-2">
                                <span>Shipping</span>
                                <span id="shippingCost">₹0.00</span>
                            </div>
                            <div class="flex justify-between text-white font-bold text-lg mt-4 pt-4 border-t border-gray-700">
                                <span>Total Amount</span>
                                <span id="totalAmount">₹<?php echo number_format($total, 2); ?></span>
                            </div>
                        </div>
                        <?php 
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    let shippingCost = 0;
    const subtotal = <?php echo $total; ?>;
    function startPayment() {
        // Get shipping method and validate
        const shippingMethodEl = document.querySelector('input[name="shipping_method"]:checked');
        if (!shippingMethodEl) {
            alert('Please select a shipping method');
            return;
        }
        
        // Calculate total amount including shipping
        const totalAmount = subtotal + shippingCost;
        
        // Show loading state
        const payButton = document.querySelector('#paymentStep button:last-child');
        payButton.disabled = true;
        payButton.textContent = 'Processing...';

        // Create order
        fetch('create_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                amount: totalAmount,
                shipping_cost: shippingCost
            })
        })
        .then(async response => {
            if (!response.ok) {
                const text = await response.text();
                try {
                    const json = JSON.parse(text);
                    throw new Error(json.message || 'Server error');
                } catch (e) {
                    throw new Error('Server error: ' + text);
                }
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to create order');
            }

            const options = {
                key: "rzp_test_PXCkaH2uhlAUBp",
                amount: totalAmount * 100,
                currency: "INR",
                name: "GoalSphere",
                description: "Jersey Purchase",
                order_id: data.order_id,
                handler: function (response) {
                    verifyPayment(response);
                },
                prefill: {
                    name: document.getElementById('fullName').value,
                    email: document.getElementById('email').value,
                    contact: document.getElementById('phone').value
                },
                theme: {
                    color: "#2563EB"
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        })
        .catch(error => {
            console.error('Payment error:', error);
            alert('Error: ' + error.message);
            payButton.disabled = false;
            payButton.textContent = 'Pay Now';
        });
    }

    function updateShippingCost(cost) {
        shippingCost = cost;
        const total = subtotal + cost;
        
        // Update shipping cost display
        document.getElementById('shippingCost').textContent = `₹${cost.toFixed(2)}`;
        // Update total amount in both sections
        document.getElementById('totalAmount').textContent = `₹${total.toFixed(2)}`;
        document.getElementById('paymentTotalAmount').textContent = `₹${total.toFixed(2)}`;
        
        // Highlight selected shipping method
        document.querySelectorAll('[name="shipping_method"]').forEach(radio => {
            const label = radio.closest('label');
            if (radio.checked) {
                label.classList.add('border-blue-500', 'bg-gray-800');
            } else {
                label.classList.remove('border-blue-500', 'bg-gray-800');
            }
        });
    }

    function nextStep(step) {
        if (step === 2) {
            if (!validateDetails()) return;
            document.getElementById('detailsStep').classList.add('hidden');
            document.getElementById('shippingStep').classList.remove('hidden');
            updateStepIndicator(2);
        } else if (step === 3) {
            if (!validateShipping()) return;
            document.getElementById('shippingStep').classList.add('hidden');
            document.getElementById('paymentStep').classList.remove('hidden');
            updateStepIndicator(3);
        }
    }

    function prevStep(step) {
        if (step === 1) {
            document.getElementById('shippingStep').classList.add('hidden');
            document.getElementById('detailsStep').classList.remove('hidden');
            updateStepIndicator(1);
        } else if (step === 2) {
            document.getElementById('paymentStep').classList.add('hidden');
            document.getElementById('shippingStep').classList.remove('hidden');
            updateStepIndicator(2);
        }
    }

    function validateDetails() {
        const fields = ['fullName', 'email', 'phone', 'address', 'city', 'postalCode', 'state'];
        for (const field of fields) {
            const value = document.getElementById(field).value.trim();
            if (!value) {
                alert('Please fill in all fields');
                return false;
            }
        }
        return true;
    }

    function validateShipping() {
        const methods = document.getElementsByName('shipping_method');
        let selected = false;
        methods.forEach(method => {
            if (method.checked) selected = true;
        });
        
        if (!selected) {
            alert('Please select a shipping method');
            return false;
        }
        return true;
    }

    function updateStepIndicator(step) {
        for (let i = 1; i <= 3; i++) {
            const stepEl = document.getElementById(`step${i}`);
            const lineEl = document.getElementById(`line${i-1}`);
            
            if (i <= step) {
                stepEl.querySelector('div').classList.remove('bg-gray-700');
                stepEl.querySelector('div').classList.add('bg-blue-600');
                stepEl.querySelector('span').classList.remove('text-gray-400');
                stepEl.querySelector('span').classList.add('text-white');
                if (lineEl) {
                    lineEl.classList.remove('bg-gray-700');
                    lineEl.classList.add('bg-blue-600');
                }
            } else {
                stepEl.querySelector('div').classList.add('bg-gray-700');
                stepEl.querySelector('div').classList.remove('bg-blue-600');
                stepEl.querySelector('span').classList.add('text-gray-400');
                stepEl.querySelector('span').classList.remove('text-white');
                if (lineEl) {
                    lineEl.classList.add('bg-gray-700');
                    lineEl.classList.remove('bg-blue-600');
                }
            }
        }
    }

    function verifyPayment(response) {
        console.log('Verifying payment:', response); // Debug log
        
        fetch('verify_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_order_id: response.razorpay_order_id,
                razorpay_signature: response.razorpay_signature
            })
        })
        .then(async res => {
            const text = await res.text();
            console.log('Server response:', text); // Debug log
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid server response: ' + text);
            }
        })
        .then(data => {
            if (data.success) {
                alert('Payment successful!');
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                throw new Error(data.message || 'Payment verification failed');
            }
        })
        .catch(error => {
            console.error('Verification error:', error);
            alert('Payment verification failed. Please try again or contact support.');
        });
    }
    </script>
</body>
</html> 