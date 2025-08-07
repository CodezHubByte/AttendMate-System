<?php
error_reporting(E_ALL); // Report all errors for development
ini_set('display_errors', 1); // Display errors on the page for development

session_start();

// Include the database connection file. This file will define $conn (mysqli object).
require_once 'db.php';

$message = ''; // Variable to store messages for the user
$show_register_form = true; // Default to showing the registration form

// --- FIXED ADMIN CREDENTIALS (FOR TESTING/DEMO ONLY - NOT FOR PRODUCTION) ---
const FIXED_ADMIN_EMAIL = 'admin@attendmate.com';
const FIXED_ADMIN_PASSWORD = 'adminpassword'; // Use a strong password in a real scenario
// -------------------------------------------------------------------------

// Check for any session messages (e.g., from previous redirects)
if (isset($_SESSION['login_message'])) {
    $message = '<div class="text-green-500 mb-4">' . htmlspecialchars($_SESSION['login_message']) . '</div>';
    unset($_SESSION['login_message']); // Clear the message after displaying
    $show_register_form = false; // If there's a login message, likely from successful registration, show login form
} elseif (isset($_SESSION['register_error'])) {
    $message = '<div class="text-red-500 mb-4">' . htmlspecialchars($_SESSION['register_error']) . '</div>';
    unset($_SESSION['register_error']);
    $show_register_form = true; // Stay on register form if there's a registration error
} elseif (isset($_SESSION['login_error'])) {
    $message = '<div class="text-red-500 mb-4">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
    unset($_SESSION['login_error']);
    $show_register_form = false; // Stay on login form if there's a login error
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        // --- Handle Registration ---
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $course = trim($_POST['course'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $role = 'user'; // Newly registered users are always 'user' (student)

        if (empty($name) || empty($email) || empty($password) || empty($course) || empty($semester)) {
            $_SESSION['register_error'] = 'Please fill in all registration fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Invalid email format.';
        } else {
            // Check if email already exists in the database
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['register_error'] = 'Email is already registered! Please login or use a different email.';
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user into database
                $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password, role, course, semester) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("ssssss", $name, $email, $hashed_password, $role, $course, $semester);

                if ($stmt_insert->execute()) {
                    $_SESSION['login_message'] = 'Registration successful! Please log in.';
                } else {
                    $_SESSION['register_error'] = 'Registration failed: ' . $conn->error;
                }
                $stmt_insert->close();
            }
            $stmt->close();
        }
        header("Location: index.php?form=register"); // Redirect to self to clear POST data and display messages
        exit();

    } elseif (isset($_POST['login'])) {
        // --- Handle Login ---
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Please enter your email and password.';
        } else {
            // --- Check for FIXED ADMIN credentials first ---
            if ($email === FIXED_ADMIN_EMAIL && $password === FIXED_ADMIN_PASSWORD) {
                $_SESSION['id'] = 0; // A placeholder ID for the fixed admin
                $_SESSION['name'] = 'Admin';
                $_SESSION['email'] = FIXED_ADMIN_EMAIL;
                $_SESSION['role'] = 'admin';
                header("Location: admin/admin_dashboard.php");
                exit();
            }

            // --- If not fixed admin, proceed with database lookup for other users ---
            $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // Login successful (database user)
                    $_SESSION['id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Redirect based on role
                    if ($user['role'] === 'admin') { // This path is for admin accounts stored in DB
                        header("Location: admin/admin_dashboard.php");
                    } elseif ($user['role'] === 'user') { // This path is for student accounts
                        header("Location: user/student_dashboard.php");
                    } else {
                        // Fallback for unknown roles
                        $_SESSION['login_error'] = "Unknown user role. Please contact support.";
                        header("Location: index.php?form=login");
                    }
                    exit(); // Important to exit after header redirect
                } else {
                    $_SESSION['login_error'] = 'Incorrect email or password.';
                }
            } else {
                $_SESSION['login_error'] = 'Incorrect email or password.'; // No user found in DB
            }
            $stmt->close();
        }
        header("Location: index.php?form=login"); // Redirect to self to clear POST data and display messages
        exit();
    }
}

// Determine which form to show based on GET parameter or session messages
if (isset($_GET['form'])) {
    if ($_GET['form'] === 'login') {
        $show_register_form = false;
    } elseif ($_GET['form'] === 'register') {
        $show_register_form = true;
    }
}

// Close the database connection at the end of the script
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AttendMate - Register & Login</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to external CSS file (assuming it's in a 'CSS' folder relative to index.php) -->
    <link rel="stylesheet" href="CSS/login.css">
    <style>
        /* Custom styles for the card container and forms */
       /* Animated Gradient Background */
body {
    font-family: 'Inter', sans-serif;
    background: linear-gradient(-45deg, #4a00e0, #8e2de2, #e0c3fc, #ffffff);
    background-size: 400% 400%;
    animation: gradientMove 15s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}

/* Gradient Animation */
@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Login/Register Card */
.card-container {
    background-color: rgba(255, 255, 255, 0.85); /* transparent white */
    backdrop-filter: blur(6px); /* blur behind card */
    padding: 2.5rem;
    border-radius: 1.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 28rem;
    text-align: center;
    position: relative;
}

        .avatar-circle {
            width: 5rem; /* 80px */
            height: 5rem; /* 80px */
            background-color: #4a00e0;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: -4rem auto 1.5rem auto; /* Adjust margin-top to overlap */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .avatar-icon {
            color: #ffffff;
            width: 3rem; /* 48px */
            height: 3rem; /* 48px */
        }
        .input-group {
            position: relative;
            margin-bottom: 1.25rem; /* 20px */
        }
        .input-icon {
            position: absolute;
            left: 0.75rem; /* 12px */
            top: 50%;
            transform: translateY(-50%);
            width: 1.25rem; /* 20px */
            height: 1.25rem; /* 20px */
            color: #6b7280; /* Tailwind gray-500 */
        }
        .input-field, .select-field {
            width: 100%;
            padding: 0.75rem 0.75rem 0.75rem 2.5rem; /* 12px padding, 40px for icon */
            border: 1px solid #d1d5db; /* Tailwind gray-300 */
            border-radius: 0.5rem; /* 8px */
            font-size: 1rem; /* 16px */
            line-height: 1.5;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .input-field:focus, .select-field:focus {
            outline: none;
            border-color: #4a00e0; /* A vibrant purple */
            box-shadow: 0 0 0 3px rgba(74, 0, 224, 0.25); /* Light purple shadow */
        }
        .select-field {
            appearance: none; /* Remove default arrow */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%236b7280' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='M6 9l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.25rem;
        }
        .btn-primary {
            width: 100%;
            padding: 0.75rem 1.25rem; /* 12px 20px */
            background-color: #4a00e0;
            color: #ffffff;
            font-weight: 600;
            border-radius: 0.5rem; /* 8px */
            transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #3b00b3; /* Darker purple */
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem; /* 14px */
        }
        .checkbox-container {
    display: flex;
    align-items: center;
    gap: 0.5rem; /* Adds spacing between box and label */
    position: relative;
    cursor: pointer;
    user-select: none;
    color: #4b5563; /* Tailwind gray-700 */
    font-size: 0.875rem;
}

.checkbox-container input {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
    accent-color: #4a00e0; /* For modern browsers */
}

        .checkbox-container span {
            position: absolute;
            top: 0;
            left: 0;
            height: 1em;
            width: 1em;
            background-color: #eee;
            border-radius: 0.25rem;
        }
        .checkbox-container:hover input ~ span {
            background-color: #ccc;
        }
        .checkbox-container input:checked ~ span {
            background-color: #4a00e0;
        }
        .checkbox-container span:after {
            content: "";
            position: absolute;
            display: none;
        }
        .checkbox-container input:checked ~ span:after {
            display: block;
        }
        .checkbox-container span:after {
            left: 0.3em;
            top: 0.1em;
            width: 0.3em;
            height: 0.6em;
            border: solid white;
            border-width: 0 0.15em 0.15em 0;
            transform: rotate(45deg);
        }
        .hidden {
            display: none;
        }
        .remember_me{
            display:flex;
            gap:5px;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Avatar Circle -->
        <div class="avatar-circle">
            <svg class="icon avatar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5.52 19c.64-2.2 1.84-3.3 3.35-3.3h5.26c1.5 0 2.7 1.1 3.35 3.3M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/>
            </svg>
        </div>

        <?php echo $message; // Display messages from PHP ?>

        <!-- Registration Form -->
        <div id="registration-form" class="<?php echo $show_register_form ? '' : 'hidden'; ?>">
            <h2 class="text-3xl font-semibold mb-8">Register</h2>

            <form action="index.php" method="POST">
                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <input type="text" id="reg-name" name="name" class="input-field" placeholder="Full Name" required>
                </div>

                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <input type="email" id="reg-email" name="email" class="input-field" placeholder="Email ID" required>
                </div>

                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="reg-password" name="password" class="input-field" placeholder="Password" required>
                </div>

                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <select id="reg-course" name="course" class="select-field" required>
                        <option value="">Select Course</option>
                        <option value="BCA">BCA</option>
                        <option value="BSC(IT)">BSC(IT)</option>
                        <option value="MCA">MCA</option>
                        <option value="MSC(IT)">MSC(IT)</option>
                        <option value="BSC">BSC</option>
                        <option value="MSC">MSC</option>
                    </select>
                </div>

                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <select id="reg-semester" name="semester" class="select-field" required>
                        <option value="">Select Semester</option>
                        <option value="1">Semester 1</option>
                        <option value="2">Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                    </select>
                </div>

                <button type="submit" name="register" class="btn-primary mt-4">Register</button>
            </form>
            <p class="mt-4 text-sm text-center text-gray-700">
                Already have an account? <a href="?form=login" class="text-blue-600 hover:underline">Login here</a>
            </p>
        </div>

        <!-- Login Form -->
        <div id="login-form" class="<?php echo $show_register_form ? 'hidden' : ''; ?>">
            <h2 class="text-3xl font-semibold mb-8">Login</h2>

            <form action="index.php" method="POST">
                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <input type="email" id="login-email" name="email" class="input-field" placeholder="Email ID" required>
                </div>

                <div class="input-group">
                    <svg class="icon input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="login-password" name="password" class="input-field" placeholder="Password" required>
                </div>

               <div class="flex justify-between items-center text-sm text-gray-700 mb-4">
  <label class="flex items-center space-x-2">
    <input type="checkbox" name="remember_me" class="w-4 h-4 text-purple-600">
    <span>Remember me</span>
  </label>
  <a href="forget_password.php" class="text-blue-600 hover:underline">Forgot Password?</a>
</div>


                <button type="submit" name="login" class="btn-primary mt-8">Login</button>
            </form>
            <p class="mt-4 text-sm text-center text-gray-700">
                Don't have an account? <a href="?form=register" class="text-blue-600 hover:underline">Register here</a>
            </p>
        </div>
    </div>

    <script>
        // This JavaScript is now primarily for client-side interactions not handled by PHP redirects.
        // The form visibility is now controlled by PHP.
        // The alert() calls are replaced by PHP messages.
        document.addEventListener('DOMContentLoaded', () => {
            // No need for client-side form switching as PHP handles it.
            // The "Forgot Password?" link and "Remember me" checkbox are client-side only for now.
        });
    </script>
</body>
</html>
