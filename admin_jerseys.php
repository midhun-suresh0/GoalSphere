<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: signin.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'goalsphere';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Add these helper functions at the top of your file
function updateJerseyStatus($conn, $jersey_id) {
    $sql = "SELECT SUM(quantity) as total FROM jersey_sizes WHERE jersey_id = $jersey_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    $status = ($row['total'] > 0) ? 'available' : 'sold_out';
    
    $update_sql = "UPDATE jerseys SET status = '$status' WHERE id = $jersey_id";
    $conn->query($update_sql);
}

// Use this function after any quantity updates
function updateQuantityAndStatus($conn, $jersey_id, $size, $quantity) {
    // Update the quantity
    $sql = "UPDATE jersey_sizes 
            SET quantity = $quantity 
            WHERE jersey_id = $jersey_id AND size = '$size'";
    $conn->query($sql);
    
    // Update the status
    updateJerseyStatus($conn, $jersey_id);
}

// Update the purchase handling (add this where you handle purchases)
function updateJerseyQuantity($conn, $jersey_id, $size, $quantity) {
    // Get current quantity
    $sql = "SELECT quantity FROM jersey_sizes 
            WHERE jersey_id = $jersey_id AND size = '$size'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($row) {
        $new_quantity = max(0, $row['quantity'] - $quantity);
        
        // Update quantity
        $update_sql = "UPDATE jersey_sizes 
                      SET quantity = $new_quantity 
                      WHERE jersey_id = $jersey_id AND size = '$size'";
        $conn->query($update_sql);
        
        // Update jersey status
        updateJerseyStatus($conn, $jersey_id);
        
        return $new_quantity > 0;
    }
    return false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $name = $conn->real_escape_string($_POST['name']);
            $description = $conn->real_escape_string($_POST['description']);
            $price = floatval($_POST['price']);
            $team = $conn->real_escape_string($_POST['team']);
            
            // Debug image upload
            error_log("Files received: " . print_r($_FILES, true));
            
            // Process images
            $images = [];
            if (isset($_FILES['images'])) {
                foreach ($_FILES['images']['name'] as $key => $filename) {
                    // Skip empty file inputs
                    if ($_FILES['images']['error'][$key] === 4) continue; // UPLOAD_ERR_NO_FILE
                    
                    if ($_FILES['images']['error'][$key] === 0) {
                        $target_dir = "uploads/jerseys/";
                        
                        // Create directory if it doesn't exist
                        if (!file_exists($target_dir)) {
                            mkdir($target_dir, 0777, true);
                        }

                        // Generate unique filename
                        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                        $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
                        $target_file = $target_dir . $new_filename;

                        // Validate file type
                        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($file_extension, $allowed_types)) {
                            if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $target_file)) {
                                $images[] = $target_file;
                                error_log("File uploaded successfully: " . $target_file);
                            } else {
                                error_log("Failed to move uploaded file: " . $_FILES['images']['error'][$key]);
                            }
                        }
                    } else {
                        error_log("Upload error for file {$key}: " . $_FILES['images']['error'][$key]);
                    }
                }
            }

            if ($_POST['action'] === 'add') {
                // Insert jersey
                $sql = "INSERT INTO jerseys (name, description, price, team) 
                        VALUES ('$name', '$description', $price, '$team')";
                
                if ($conn->query($sql)) {
                    $jersey_id = $conn->insert_id;
                    
                    // Insert images
                    foreach($images as $index => $image_url) {
                        $escaped_url = $conn->real_escape_string($image_url);
                        $is_primary = ($index === 0) ? 1 : 0;
                        
                        $img_sql = "INSERT INTO jersey_images (jersey_id, image_url, is_primary) 
                                  VALUES ($jersey_id, '$escaped_url', $is_primary)";
                        
                        if (!$conn->query($img_sql)) {
                            error_log("Failed to insert image record: " . $conn->error);
                        } else {
                            error_log("Successfully inserted image record for: " . $image_url);
                        }
                    }
                    
                    // Insert sizes
                    if (isset($_POST['sizes'])) {
                        foreach ($_POST['sizes'] as $size) {
                            $quantity = intval($_POST["quantity_$size"]);
                            $size_sql = "INSERT INTO jersey_sizes (jersey_id, size, quantity) 
                                       VALUES ($jersey_id, '$size', $quantity)";
                            $conn->query($size_sql);
                        }
                    }
                }
            } else {
                // Update jersey
                $id = intval($_POST['jersey_id']);
                $sql = "UPDATE jerseys 
                        SET name='$name', description='$description', 
                            price=$price, team='$team'
                        WHERE id=$id";
                
                if ($conn->query($sql)) {
                    // Add new images
                    foreach($images as $image_url) {
                        $escaped_url = $conn->real_escape_string($image_url);
                        $img_sql = "INSERT INTO jersey_images (jersey_id, image_url) 
                                  VALUES ($id, '$escaped_url')";
                        
                        if (!$conn->query($img_sql)) {
                            error_log("Failed to insert image record during update: " . $conn->error);
                        }
                    }
                    
                    // Update sizes
                    $conn->query("DELETE FROM jersey_sizes WHERE jersey_id = $id");
                    if (isset($_POST['sizes'])) {
                        foreach ($_POST['sizes'] as $size) {
                            $quantity = intval($_POST["quantity_$size"]);
                            $size_sql = "INSERT INTO jersey_sizes (jersey_id, size, quantity) 
                                       VALUES ($id, '$size', $quantity)";
                            $conn->query($size_sql);
                        }
                    }
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['jersey_id']);
            
            // Delete associated images from filesystem
            $img_sql = "SELECT image_url FROM jersey_images WHERE jersey_id = $id";
            $img_result = $conn->query($img_sql);
            if ($img_result) {
                while ($row = $img_result->fetch_assoc()) {
                    if (file_exists($row['image_url'])) {
                        unlink($row['image_url']);
                    }
                }
            }
            
            // Delete jersey and related records
            $sql = "DELETE FROM jerseys WHERE id=$id";
            $conn->query($sql);
        } elseif ($_POST['action'] === 'update_quantity') {
            $jersey_id = intval($_POST['jersey_id']);
            $size = $conn->real_escape_string($_POST['size']);
            $quantity = intval($_POST['quantity']);
            
            updateQuantityAndStatus($conn, $jersey_id, $size, $quantity);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jerseys - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Include Admin Sidebar -->
        <?php include('includes/admin_sidebar.php'); ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto p-8">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Manage Jerseys</h1>
                <button onclick="showAddForm()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Add New Jersey
                </button>
            </div>

            <!-- Add/Edit Form -->
            <div id="jerseyForm" class="hidden bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 id="formTitle" class="text-xl font-semibold mb-4">Add New Jersey</h2>
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="jersey_id" id="jerseyId">
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Jersey Name</label>
                        <input type="text" name="name" required class="w-full p-2 border rounded">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" required class="w-full p-2 border rounded" rows="3"></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Price (₹)</label>
                            <input type="number" name="price" step="0.01" required class="w-full p-2 border rounded">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium mb-1">Team</label>
                            <input type="text" name="team" required class="w-full p-2 border rounded">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Sizes Available</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="sizes[]" value="S" id="sizeS" class="size-checkbox">
                                    <label for="sizeS">Small</label>
                                    <input type="number" name="quantity_S" min="0" placeholder="Qty" 
                                           class="w-20 p-1 border rounded size-quantity hidden">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="sizes[]" value="M" id="sizeM" class="size-checkbox">
                                    <label for="sizeM">Medium</label>
                                    <input type="number" name="quantity_M" min="0" placeholder="Qty" 
                                           class="w-20 p-1 border rounded size-quantity hidden">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="sizes[]" value="L" id="sizeL" class="size-checkbox">
                                    <label for="sizeL">Large</label>
                                    <input type="number" name="quantity_L" min="0" placeholder="Qty" 
                                           class="w-20 p-1 border rounded size-quantity hidden">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="sizes[]" value="XL" id="sizeXL" class="size-checkbox">
                                    <label for="sizeXL">Extra Large</label>
                                    <input type="number" name="quantity_XL" min="0" placeholder="Qty" 
                                           class="w-20 p-1 border rounded size-quantity hidden">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Jersey Images</label>
                        <div class="space-y-2">
                            <div id="imageInputs" class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif" class="w-full p-2 border rounded">
                                    <button type="button" onclick="addImageInput()" class="bg-green-500 text-white p-2 rounded hover:bg-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">Accepted formats: JPG, PNG, GIF</p>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Save Jersey
                        </button>
                        <button type="button" onclick="hideForm()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Jerseys List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Team</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sizes</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        $sql = "SELECT j.*, 
                                GROUP_CONCAT(DISTINCT CONCAT(js.size, ':', js.quantity)) as sizes,
                                SUM(js.quantity) as total_quantity
                                FROM jerseys j 
                                LEFT JOIN jersey_sizes js ON j.id = js.jersey_id 
                                GROUP BY j.id 
                                ORDER BY j.created_at DESC";
                        $result = $conn->query($sql);
                        
                        if ($result === false) {
                            echo '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Error loading jerseys</td></tr>';
                        } else if ($result->num_rows === 0) {
                            echo '<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">No jerseys available</td></tr>';
                        } else {
                            while($jersey = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <?php
                                        $images_sql = "SELECT image_url FROM jersey_images WHERE jersey_id = {$jersey['id']} ORDER BY is_primary DESC LIMIT 1";
                                        $images_result = $conn->query($images_sql);
                                        if ($images_result && $images_result->num_rows > 0) {
                                            $image = $images_result->fetch_assoc();
                                            echo '<img src="' . htmlspecialchars($image['image_url']) . '" 
                                                       alt="Jersey" class="w-16 h-16 object-cover rounded">';
                                        } else {
                                            echo '<span class="text-gray-400">No image</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($jersey['name']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($jersey['team']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php
                                        if ($jersey['sizes']) {
                                            $sizes = explode(',', $jersey['sizes']);
                                            echo '<div class="space-y-1">';
                                            foreach ($sizes as $size_info) {
                                                list($size, $quantity) = explode(':', $size_info);
                                                echo '<div class="text-sm">';
                                                echo '<span class="font-medium">' . htmlspecialchars($size) . '</span>: ';
                                                echo '<span class="' . ($quantity > 0 ? 'text-green-600' : 'text-red-600') . '">';
                                                echo $quantity . ' pcs</span>';
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                        } else {
                                            echo '<span class="text-gray-400">No sizes available</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">₹<?php echo number_format($jersey['price'], 2); ?></td>
                                    <td class="px-6 py-4">
                                        <?php 
                                        $total_quantity = $jersey['total_quantity'] ?? 0;
                                        if ($total_quantity > 0) {
                                            echo '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Available</span>';
                                            echo '<span class="ml-2 text-sm text-gray-600">(' . $total_quantity . ' in stock)</span>';
                                        } else {
                                            echo '<span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-sm">Sold Out</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button onclick="editJersey(<?php echo htmlspecialchars(json_encode($jersey)); ?>)"
                                                class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                        <button onclick="deleteJersey(<?php echo $jersey['id']; ?>)"
                                                class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showAddForm() {
            document.getElementById('formTitle').textContent = 'Add New Jersey';
            document.getElementById('formAction').value = 'add';
            document.getElementById('jerseyId').value = '';
            document.getElementById('jerseyForm').classList.remove('hidden');
        }

        function hideForm() {
            document.getElementById('jerseyForm').classList.add('hidden');
        }

        function editJersey(jersey) {
            document.getElementById('formTitle').textContent = 'Edit Jersey';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('jerseyId').value = jersey.id;
            
            const form = document.getElementById('jerseyForm');
            form.querySelector('[name="name"]').value = jersey.name;
            form.querySelector('[name="description"]').value = jersey.description;
            form.querySelector('[name="price"]').value = jersey.price;
            form.querySelector('[name="team"]').value = jersey.team;
            
            // Update sizes
            if (jersey.sizes) {
                const sizes = jersey.sizes.split(',');
                sizes.forEach(size_info => {
                    const [size, qty] = size_info.split(':');
                    const checkbox = document.getElementById('size' + size);
                    if (checkbox) {
                        checkbox.checked = true;
                        const quantityInput = document.querySelector(`[name="quantity_${size}"]`);
                        if (quantityInput) {
                            quantityInput.value = qty;
                            quantityInput.classList.remove('hidden');
                        }
                    }
                });
            }
            
            form.classList.remove('hidden');
        }

        function deleteJersey(id) {
            if (confirm('Are you sure you want to delete this jersey?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="jersey_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Show/hide quantity input when size is checked/unchecked
        document.querySelectorAll('.size-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const quantityInput = this.parentElement.querySelector('.size-quantity');
                if (this.checked) {
                    quantityInput.classList.remove('hidden');
                    quantityInput.required = true;
                } else {
                    quantityInput.classList.add('hidden');
                    quantityInput.required = false;
                    quantityInput.value = '';
                }
            });
        });

        function addImageInput() {
            const container = document.getElementById('imageInputs');
            const newInput = document.createElement('div');
            newInput.className = 'flex items-center space-x-2';
            newInput.innerHTML = `
                <input type="file" name="images[]" accept="image/jpeg,image/png,image/gif" class="w-full p-2 border rounded">
                <button type="button" onclick="removeImageInput(this)" class="bg-red-500 text-white p-2 rounded hover:bg-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            `;
            container.appendChild(newInput);
        }

        function removeImageInput(button) {
            button.parentElement.remove();
        }

        // Add this JavaScript for quantity validation
        document.querySelectorAll('input[type="number"][name^="quantity_"]').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value < 0) {
                    this.value = 0;
                }
            });
        });
    </script>
</body>
</html> 