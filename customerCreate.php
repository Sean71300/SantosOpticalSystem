<?php
    include_once 'customerFunctions.php'; 
    $name = "";
    $address = "";
    $phone = "";
    $info = "";
    $notes = "";   

    [$errorMessage, $successMessage]=handleCustomerForm();
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
                    <textarea class="form-control" name="info" rows="3" value="<?php echo $info;?>" ></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Notes</label>
                    <div class="col-sm-6">
                    <textarea class="form-control" name="notes" rows="3" value="<?php echo $notes;?>" ></textarea>
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
                    <button class="btn btn-outline-primary" role="button">Cancel</button>                    </div>
                </div>
            </form>
        </div>
    </body>
</html>