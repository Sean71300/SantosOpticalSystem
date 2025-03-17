<?php
    include_once 'customerFunctions.php'; 
    $id = "";
    $name = "";
    $address = "";
    $phone = "";
    $info = "";
    $notes = "";   

    if ( $_SERVER['REQUEST_METHOD'] == 'GET') {
        
        if (!isset($_GET["CustomerID"])) {
            header("location:customerPage.php");
            exit;
        }

        $id = $_GET["CustomerID"];

        $conn = connect();
        $sql = "SELECT * FROM customer where CustomerID=$id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if (!$row) {
            header ("location:customerPage.php");
            exit;
        }

        $name = $row["CustomerName"];
        $address = $row["CustomerAddress"];
        $phone = $row["CustomerContact"];
        $info = $row["CustomerInfo"];
        $notes = $row["Notes"];
    }
    
    else {
        $id = $_POST["id"];
        $name = $_POST["name"];
        $address = $_POST["address"];
        $phone = $_POST["phone"];
        $info = $_POST["info"];
        $notes = $_POST["notes"];

        do {
            if (empty($id) || empty($name) || empty($address) || empty($phone) || empty($info) || empty($notes)) {
                $errorMessage = 'All the fields are required';
                break;
            }
            $sql = "UPDATE customer 
                SET CustomerName = '$name', CustomerAddress = '$address', 
                CustomerContact = '$phone', CustomerInfo = '$info',
                Notes = '$notes', Upd_by = 'Bien Ven P. Santos' 
                WHERE CustomerID = {$id}";

            $conn = connect();
            $result = $conn->query($sql);

            if (!$result) {
                $errorMessage = "Invalid query: " . $conn->error;
                break;
            }

            $successMessage = "Client updated correctly";

            header("location:customerPage.php");
            exit;
                
        } while(false);
    }
?>

<html>
    <title>
        Customer Page
    </title>
    <head>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    </head>
    <body class="bg-body-tertiary">
        <?php include "Navigation.php"?> 
        <div class="container category-container">
            <h1>New Customer</h1>

            <?php
             if (!empty($errorMessage)) {
                echo "
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong>$errorMessage</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
                ";
            }
            ?>
           
            <form method="post" id="customerCreate">
                <input type="hidden" name="id" value ="<?php echo $id;?>">
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Name</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="name" value="<?php echo $name;?>" >
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Address</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="address" value="<?php echo $address;?>" >
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Phone</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="phone" value="<?php echo $phone;?>" >
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Customer Info</label>
                    <div class="col-sm-6">
                    <input class="form-control" name="info" rows="3" value="<?php echo $info;?>" ></input>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Notes</label>
                    <div class="col-sm-6">
                    <input class="form-control" name="notes" rows="3" value="<?php echo $notes;?>" ></input>
                    </div>
                </div>

                <?php
                
                if (!empty($successMessage))
                {
                    echo "
                    <div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <strong>$successMessage</strong>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>
                    ";
                }
                
                ?>
                <div class="row mb-3">
                    <div class="offset-mb-3 col-sm-3 d-grid">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    <div class="col-sm-3 d-grid">
                        <button class="btn btn-outline-primary" role="button">Cancel</button>                    
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>