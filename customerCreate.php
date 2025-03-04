<?php
include_once 'setup.php'; 
$name = "";
$address = "";
$phone = "";
$info = "";
$notes = "";

$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST["name"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $info = $_POST["info"];
    $notes = $_POST["notes"];

    do {
        if (empty($name) || empty($address) || empty($phone) || empty($info) || empty($notes)) {
            $errorMessage = 'All the fields are required';
            break;
        }

    } while (false);
}

?>

<html>
    <title>
        Customer Page
    </title>
    <head>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/af468059ce.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="css/bootstrap.min.css">
        <link rel="stylesheet" href="customCodes/custom.css">
    </head>
    <body class="bg-body-tertiary">
        <?php include "Navigation.php"?> 
        <div class="container category-container">
            <h1>New Customer</h1>
            <form method="post" id="customerCreate">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Name</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="name" value="<?php echo $name;?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Address</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="address" value="<?php echo $address;?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Phone</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="phone" value="<?php echo $phone;?>">
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Customer Info</label>
                    <div class="col-sm-6">
                    <textarea class="form-control" name="info" rows="3" value="<?php echo $info;?>"></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Notes</label>
                    <div class="col-sm-6">
                    <textarea class="form-control" name="notes" rows="3" value="<?php echo $notes;?>"></textarea>
                    </div>
                </div>


                <div class="row mb-3">
                    <div class="offset-mb-3 col-sm-3 d-grid">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    <div class="col-sm-3 d-grid">
                    <button class="btn btn-outline-primary" role="button">Cancel</button>                    </div>
                </div>
            </form>
        </div>
    </body>
</html>