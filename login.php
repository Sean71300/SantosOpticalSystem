<?php
session_start();
include_once 'connect.php';
include_once 'setup.php';
include 'ActivityTracker.php';

$username = $password = "";
$username_err = $password_err = "";
$login_err = "";

if (isset($_POST['cancel'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['cancel'])) {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        // First try employee login
        $sql = "SELECT EmployeeID, EmployeeName, EmployeePicture, EmployeeEmail, EmployeeNumber, 
                       RoleID, LoginName, Password, BranchCode, Status, Upd_by, Upd_dt 
                FROM employee 
                WHERE LoginName = ? AND Status = 'Active'";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $full_name, $img, $email, $number, 
                                           $roleid, $username, $hashed_password, $branchcode, 
                                           $status, $upd_by, $upd_dt);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Employee login successful
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_type"] = "employee";
                            $_SESSION["id"] = $id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["img"] = $img;
                            $_SESSION["email"] = $email;
                            $_SESSION["number"] = $number;
                            $_SESSION["roleid"] = $roleid;
                            $_SESSION["username"] = $username;
                            $_SESSION["branchcode"] = $branchcode;
                            $_SESSION["status"] = $status;
                            $_SESSION["upd_by"] = $upd_by;
                            $_SESSION["upd_dt"] = $upd_dt;
                            $_SESSION['last_activity'] = time();

                            header("location: Dashboard.php");
                            exit();
                        } else {
                            $login_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // If no employee found, try customer login
                    $sql = "SELECT CustomerID, CustomerName, CustomerContact 
                            FROM customer 
                            WHERE CustomerName = ? AND Status = 'Active'";
                    
                    if ($stmt = mysqli_prepare($link, $sql)) {
                        mysqli_stmt_bind_param($stmt, "s", $param_username);
                        $param_username = $username;

                        if (mysqli_stmt_execute($stmt)) {
                            mysqli_stmt_store_result($stmt);
                            if (mysqli_stmt_num_rows($stmt) == 1) {
                                mysqli_stmt_bind_result($stmt, $customer_id, $customer_name, $customer_contact);
                                if (mysqli_stmt_fetch($stmt)) {
                                    if ($password == $customer_id) {
                                        // Customer login successful
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["user_type"] = "customer";
                                        $_SESSION["id"] = $customer_id;
                                        $_SESSION["full_name"] = $customer_name;
                                        $_SESSION["contact"] = $customer_contact;
                                        $_SESSION['CustomerID'] = $customer_id;

                                        header("location: customer_dashboard.php");
                                        exit();
                                    } else {
                                        $login_err = "The customer ID you entered was not valid.";
                                    }
                                }
                            } else {
                                $login_err = "No active account found with that username.";
                            }
                        } else {
                            $login_err = "Oops! Something went wrong. Please try again later.";
                        }
                    }
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login | Santos Optical</title>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <style>
        :root {
            --primary-color: #43a047; /* green for sign in */
            --cancel-color: #c62828; /* red for cancel */
            --bg-color: #fff176; /* yellow background */
            --input-border: #ccc;
            --input-bg: #fff;
            --form-bg: #ffffff;
            --shadow: 0px 10px 25px rgba(0, 0, 0, 0.1);
            --font: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: var(--font);
            min-height: 100vh;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            max-width: 1200px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .wrapper {
            background: var(--form-bg);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 2px solid var(--cancel-color);
            margin-bottom: 20px;
        }

        .alert-container {
            width: 100%;
            max-width: 400px;
        }

        .logo {
            width: 70px;
            height: auto;
            margin-bottom: 1rem;
        }

        .titles {
            margin-bottom: 1.5rem;
        }

        .title-login {
            font-size: 24px;
            font-weight: 600;
            color: var(--cancel-color);
            margin-bottom: 0.5rem;
        }

        .login-form {
            width: 100%;
        }

        .input-box {
            position: relative;
            margin-bottom: 1.2rem;
        }

        .input-field {
            width: 100%;
            padding: 12px 15px 12px 40px;
            font-size: 16px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 8px;
            outline: none;
            transition: 0.3s ease;
        }

        .input-field:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 160, 71, 0.2);
        }

        .icon {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            font-size: 18px;
            color: #999;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #388e3c;
        }

        .cancel-button {
            width: 100%;
            margin-top: 10px;
            padding: 12px;
            background: var(--cancel-color);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .cancel-button:hover {
            background: #b71c1c;
        }

        .alert-danger, .invalid-feedback {
            color: red;
            font-size: 14px;
            margin-top: 0.25rem;
            display: block;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .wrapper {
                padding: 1.5rem;
            }
            
            .title-login {
                font-size: 22px;
            }
            
            .input-field {
                padding: 10px 15px 10px 40px;
                font-size: 15px;
            }
            
            .btn-submit, .cancel-button {
                padding: 10px;
                font-size: 15px;
            }
        }

        @media (max-width: 480px) {
            .wrapper {
                padding: 1.2rem;
                border-radius: 12px;
            }
            
            .logo {
                width: 60px;
            }
            
            .title-login {
                font-size: 20px;
            }
            
            .input-box {
                margin-bottom: 1rem;
            }
        }
        </style>
    </head>
    <body>
        <div class="login-container">
            <div class="wrapper">
                <div class="titles">
                    <img src="Images/logo.png" alt="Logo" class="logo">
                    <h2 class="title-login">Greetings!</h2>
                </div>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="login-form">
                    <div class="input-box">
                        <i class='bx bx-user icon'></i>
                        <input type="text" name="username" class="input-field <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" placeholder="Your username" value="<?php echo htmlspecialchars($username); ?>">
                        <span class="invalid-feedback"><?php echo $username_err; ?></span>
                    </div>

                    <div class="input-box">
                        <i class='bx bx-lock-alt icon'></i>
                        <input type="password" name="password" class="input-field <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Your password">
                        <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>

                    <?php 
                    if (!empty($login_err)) {
                        echo '<div class="alert-danger">' . $login_err . '</div>';
                    }        
                    ?>

                    <div class="input-box">
                        <button type="submit" class="btn-submit">Sign In</button>
                    </div>

                    <div class="input-box">
                        <button type="submit" name="cancel" class="cancel-button">Cancel</button>
                    </div>
                </form>
            </div>

            <div class="alert-container">
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <div class="d-flex">
                        <div class="flex-shrink-0 me-3">
                            <i class="bi bi-info-circle-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="alert-heading">Login Instructions</h5>
                            <p class="mb-0"><strong>Customers:</strong> Use your full name as username and your customer ID as password.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js" integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous"></script>
    </body>
</html>