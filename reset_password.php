<?php
session_start();
$error_message = '';

if (!isset($_SESSION['email'])) {
    header('Location: forgetpassword.php');
    exit(); 
}
$conn= new mysqli('localhost','root','','goalsphere');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $error_message = "An error occurred during login. Please try again later.";
} else {
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $password= $_POST['new_password'];
        $confirm_password= $_POST['confirm_password'];
        if($password==$confirm_password){
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '" . $_SESSION['email'] . "'";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Your password has been successfully updated!";
                header('Location: signin.php');
                unset($_SESSION['email']);  
                exit();
            } else {
                $error_message = "Error updating password: " . $conn->error;
            }
            
    }
    
} 
}  

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoalSphere - Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-image: url('https://images.unsplash.com/photo-1508098682722-e99c43a406b2?q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            animation: bounceIn 0.6s ease-out;
            position: relative;
            z-index: 2;
            border: 2px solid #2563eb;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
        }

        .logo::after {
            content: '⚽';
            position: absolute;
            font-size: 1.5rem;
            margin-left: 10px;
            animation: spin 4s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .task { color: #2563eb; }
        .mate { color: #3b82f6; }

        h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            padding-left: 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.7rem;
            color: #64748b;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .error-text {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: block;
        }

        .reset-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .reset-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .error-message {
            background-color: rgba(254, 226, 226, 0.9);
            color: #dc2626;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            border: 1px solid #dc2626;
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                opacity: 0.9;
                transform: scale(1.1);
            }
            80% {
                opacity: 1;
                transform: scale(0.89);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Football pattern overlay */
        .reset-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 10px 10px, rgba(37, 99, 235, 0.1) 2px, transparent 0);
            background-size: 30px 30px;
            border-radius: 20px;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">
            <span class="task">Goal</span><span class="mate">Sphere</span>
        </div>
        <h2>Reset Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form id="resetForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="new_password" name="new_password" required>
                <span id="password-error" class="error-text"></span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <span id="cpassword-error" class="error-text"></span>
            </div>

            <button type="submit" class="reset-btn" name="reset">Reset Password</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordError = document.getElementById('password-error');
            const cpasswordError = document.getElementById('cpassword-error');
            const passwordInput = document.getElementById('new_password');
            const cpasswordInput = document.getElementById('confirm_password');

            function checkPassword() {
                const password = passwordInput.value.trim();
                const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

                if (password === '') {
                    passwordError.textContent = "Password is required";
                    passwordInput.style.border = "2px solid #dc2626";
                    return false;
                } else if (!passwordPattern.test(password)) {
                    passwordError.textContent = "Password must contain at least 8 characters, including uppercase, lowercase, number, and special character";
                    passwordInput.style.border = "2px solid #dc2626";
                    return false;
                } else {
                    passwordError.textContent = "";
                    passwordInput.style.border = "2px solid #22c55e";
                    return true;
                }
            }

            function checkConfirmPassword() {
                const password = passwordInput.value.trim();
                const confirmPassword = cpasswordInput.value.trim();

                if (confirmPassword === '') {
                    cpasswordError.textContent = "Please confirm your password";
                    cpasswordInput.style.border = "2px solid #dc2626";
                    return false;
                } else if (confirmPassword !== password) {
                    cpasswordError.textContent = "Passwords do not match";
                    cpasswordInput.style.border = "2px solid #dc2626";
                    return false;
                } else {
                    cpasswordError.textContent = "";
                    cpasswordInput.style.border = "2px solid #22c55e";
                    return true;
                }
            }

            passwordInput.addEventListener('input', function() {
                checkPassword();
                if (cpasswordInput.value !== '') {
                    checkConfirmPassword();
                }
            });

            cpasswordInput.addEventListener('input', checkConfirmPassword);

            document.getElementById('resetForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const isPasswordValid = checkPassword();
                const isConfirmPasswordValid = checkConfirmPassword();
                
                if (isPasswordValid && isConfirmPasswordValid) {
                    this.submit();
                }
            });
        });
    </script>
</body>
</html>