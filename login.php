<?php
include_once 'setup.php'; // Include the setup.php file
session_start();
?>

<?php
require_once 'connect.php'; //Connect to the database

$username = $password = ""; //Initialize the username and password variables
$username_err = $password_err = ""; //Initialize the username and password error variables

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if(empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }

    if(empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if(empty($username_err) && empty($password_err)) {
        $sql = "SELECT EmployeeID, EmployeeName, EmployeePicture, EmployeeEmail, EmployeeNumber, RoleID, LoginName, Password, BranchCode, Status, Upd_by, Upd_dt FROM employee WHERE LoginName = ?";
        if($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if(mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $img, $email, $number, $roleid, $username, $hashed_password, $branchcode, $status, $upd_by, $upd_dt);
                    if(mysqli_stmt_fetch($stmt)) {
                        if(password_verify($password, $hashed_password)) {
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["img"] = $img;
                            $_SESSION["email"] = $email;
                            $_SESSION["number"] = $number;
                            $_SESSION["roleid"] = $roleid;
                            $_SESSION["branchcode"] = $branchcode;
                            $_SESSION["status"] = $status;
                            $_SESSION["upd_by"] = $upd_by;
                            $_SESSION["upd_dt"] = $upd_dt;

                            if($roleid == 1) {
                                header("location: admin.php");
                            } else if($roleid == 2) {
                                header("location: employee.php");
                            }
                        } else {
                            $login_err = "The password you entered was not valid.";
                        }
                    }
                } else {
                    $login_err = "No account found with that username.";
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
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <title>Login</title>
    </head>

    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        h1 {
            margin-bottom: 2.5rem;
        }

        /* CONTAINERS */
        .container {
            padding: 10rem;
        }


        .form-group {
            margin-bottom: 1rem;
        }

        .buttons {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 2.5rem;
        }
        /* CONTAINERS */
    </style>

    <body>
        <div class="container">
            <h1>Login</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                <div class="form-group">
                    <p class="fw-bold fs-5">Username</p>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>

                <div class="form-group"> 
                    <p class="fw-bold fs-5">Password</p>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>

                <?php 
                if(!empty($login_err)){
                    echo '<div class="mt-4 alert alert-danger">' . $login_err . '</div>';
                }        
                ?>

                <div class="buttons">
                    <button type="submit" class="btn btn-success w-25" style="margin-right: 25px;">Login</button>
                    <button type="reset" class="btn btn-danger w-25" style="margin-left: 25px;">Reset</button>
                </div>
            </form>
        </div>
    </body>
</html> 