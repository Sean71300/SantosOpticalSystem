<?php
    include_once 'setup.php';
    require_once 'connect.php';
    session_start();

    function pullProducts() {
        $conn = connect();

        $sql = "SELECT * FROM `productmstr`";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $ProductImage = base64_encode($row['ProductImage']);
                echo "<div class='col g-3'>";
                    echo "<div class='card' style='width: 18.5rem; height: 28rem;'>";
                        echo '<img src="data:image/png;base64,' . $ProductImage. '"class="card-img-top" style="height: 300px;" alt="'. $row['Model'] .'">';
                            echo "<div class='card-body'>";
                                echo "<h5 class='card-title overflow-hidden'>".$row['Model']."</h5>";
                                echo "<p class='card-text'>".$row['Avail_FL']."</p>";
                                
                            echo "</div>";
                            echo "<a href='#' class='btn btn-primary'>Go somewhere</a>";
                    echo "</div>";
                echo "</div>";
            }
        } else {
            echo "No products found.";
        }
    }
?>


<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
        <title>Gallery</title>
    </head>

    <header>
        <?php
            include "nav-bar.html";
        ?>
    </header>

    <body>
        <div class="container" style="margin-top: 8.5rem;">
            <div class="container mb-4">
                <h1 style='text-align: center;'>Gallery</h1>
            </div>

            <div class="grid" style="margin-bottom: 3.5rem;">
                <div class="row align-items-start">
                    <?php
                        pullProducts();
                    ?>
                </div>
            </div>          
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>