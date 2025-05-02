<?php

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
        $sql = "SELECT EmployeeID, EmployeeName, EmployeePicture, EmployeeEmail, EmployeeNumber, 
                       RoleID, LoginName, Password, BranchCode, Status, Upd_by, Upd_dt 
                FROM employee 
                WHERE LoginName = ? AND Status = 'Active'";  // Added status check

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
                            // Set session variables
                            $_SESSION["loggedin"] = true;
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

                            // Improved role-based redirection
                            switch ($roleid) {
                                case 1: // Admin
                                    header("location: Dashboard.php");
                                    exit();
                                case 2: // Employee
                                    header("location: Dashboard.php");
                                    exit();
                                default:
                                    $login_err = "Error has occured, please try logging in again.";
                                    session_destroy();
                            }
                        } else {
                            $login_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $login_err = "No active account found with that username.";
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
    height: 100vh;
    background-color: white;
    background-size: cover; /* para fit sa bg */
    display: flex;
    justify-content: center;
    align-items: center;
}

    .wrapper {
        background: var(--form-bg);
        padding: 3rem;
        border-radius: 16px;
        box-shadow: var(--shadow);
        max-width: 400px;
        width: 100%;
        text-align: center;
        border: 2px solid var(--cancel-color); /* keep the nice outline */
    }

    .logo {
        width: 70px; /*Dito mag aadjust for size pic*/
        margin-bottom: 1rem;
    }

    .titles {
        margin-bottom: 2rem;
    }

    .title-login {
        font-size: 24px;
        font-weight: 600;
        color: var(--cancel-color); /* title stays red */
    }

    .login-form {
        width: 100%;
    }

    .input-box {
        position: relative;
        margin-bottom: 1.5rem;
    }

    .input-field {
        width: 100%;
        padding: 15px 20px 15px 45px;
        font-size: 16px;
        background: var(--input-bg);
        border: 1px solid var(--input-border);
        border-radius: 8px;
        outline: none;
        transition: 0.3s ease;
    }

    .input-field:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(67, 160, 71, 0.2); /* green glow on focus */
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
        padding: 15px;
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
        background-color: #388e3c; /* darker green on hover */
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
        background: #b71c1c; /* darker red on hover */
    }

    .alert-danger, .invalid-feedback {
        color: red;
        font-size: 14px;
        margin-top: 0.25rem;
        display: block;
    }
</style>


</head>
<body>

    <div class="container d-flex justify-content-center align-items-center vh-100">
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

        <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
            <div class="d-flex">
                <div class="flex-shrink-0 me-3">
                <i class="bi bi-info-circle-fill fs-4"></i> <!-- Bootstrap Icons (requires separate include) -->
                </div>
                <div>
                <h5 class="alert-heading">For customers:</h5>
                <p class="mb-0">Use your full name as username and your reference number as password.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js" integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous"></script>
</body>
</html>
