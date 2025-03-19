<?php
session_start(); 
$error_message = '';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function generateVerificationCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendVerificationEmail($recipientEmail, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'goalsphere79@gmail.com';
        $mail->Password   = 'akgn fadx wmqg dscf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('goalsphere79@gmail.com', 'GoalSphere');
        $mail->addAddress($recipientEmail);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: $verificationCode\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $verificationCode = generateVerificationCode();
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['email'] = $email;

        if (sendVerificationEmail($email, $verificationCode)) {
            echo"";
        } else {
            $error_message= "Failed to send verification code.";
        }
    } elseif (isset($_POST['verify'])) {
        $enteredOTP = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        if ($enteredOTP == $_SESSION['verification_code']) {
            header('Location: reset_password.php');
            unset($_SESSION['verification_code']);  
        } else {
            $error_message="Incorrect OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GoalSphere - OTP Verification</title>
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

        .login-container {
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
            margin-bottom: 1rem;
            font-size: 1.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .otp-description {
            text-align: center;
            color: #64748b;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .otp-inputs {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .otp-inputs input {
            width: 3.5rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }

        .otp-inputs input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        .login-btn {
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
            margin-bottom: 1.5rem;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }

        .resend-text {
            text-align: center;
            color: #1e293b;
            font-size: 1rem;
        }

        .resend-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .resend-text a:hover {
            color: #1d4ed8;
            text-decoration: underline;
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
        .login-container::before {
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
    <div class="login-container">
        <div class="logo">
            <span class="task">Goal</span><span class="mate">Sphere</span>
        </div>
        <h2>OTP Verification</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <p class="otp-description">Enter the 6-digit code sent to your email.</p>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="otp-inputs">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp2')" id="otp1" name="otp1">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp3')" id="otp2" name="otp2">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp4')" id="otp3" name="otp3">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp5')" id="otp4" name="otp4">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp6')" id="otp5" name="otp5">
                <input type="text" maxlength="1" required id="otp6" name="otp6">
            </div>
            <button type="submit" class="login-btn" name="verify">Verify OTP</button>
            <p class="resend-text">Didn't receive the code? <a href="#">Resend OTP</a></p>
        </form>
    </div>

    <script>
        function moveToNext(current, nextFieldID) {
            if (current.value.length === 1) {
                document.getElementById(nextFieldID)?.focus();
            }
        }
    </script>
</body>
</html>