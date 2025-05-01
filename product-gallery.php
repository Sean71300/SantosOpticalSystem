<?php
    include_once 'setup.php';
    require_once 'connect.php';
    include 'ActivityTracker.php';

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;

        $sql = "SELECT * FROM `productMstr` LIMIT $start, $perPage";
        $result = mysqli_query($conn, $sql);

        $total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `productMstr`"));
        $totalPages = ceil($total / $perPage);

        // Start of card grid
        echo "<div class='row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4'>";        
        while($row = mysqli_fetch_assoc($result)) { // Card 
            echo "<div class='col d-flex'>";
                echo "<div class='card w-100'>";
                    echo '<img src="' . $row['ProductImage']. '" class="card-img-top img-fluid" style="height: 350px; object-fit: contain;" alt="'. $row['Model'] .'">';
                    echo "<div class='card-body d-flex flex-column'>";
                        echo "<h5 class='card-title' style='min-height: 1.5rem;'>".$row['Model']."</h5>";
                        echo "<hr>";                       
                        echo "<p class='card-text'>".$row['CategoryType']."</p>";
                        echo "<p class='card-text'>".$row['Material']."</p>";
                        echo "<p class='card-text'>".$row['Price']."</p>";
                        echo "<p class='card-text'>".$row['Avail_FL']."</p>";                                
                    echo "</div>";

                    $avail = $row['Avail_FL'];
                    if ($avail == 'Available') {
                        echo "<div class='card-footer mt-auto'>";
                            echo "<a href='productDetails.php?id=".$row['ProductID']."' class='btn btn-primary w-100'>More details</a>";
                        echo "</div>";
                    } else {
                        // If not available, show a disabled button
                        echo "<div class='card-footer mt-auto'>";
                            echo "<button class='btn btn-secondary w-100' disabled>Out of Stock</button>";
                        echo "</div>";
                    }
                echo "</div>";
            echo "</div>";
        }
        
        echo "</div>"; // End of card grid

        // Pagination
        echo "<div class='col-12 mt-5'>";
            echo "<div class='d-flex justify-content-center'>";
                echo "<ul class='pagination'>";
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "'>Previous</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                }

                for ($i = 1; $i <= $totalPages; $i++) {                       
                    if ($i == $page) {
                        echo "<li class='page-item active' aria-current='page'><a class='page-link disabled'>$i</a></li>"; 
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='?page=$i'>$i</a></li>";
                    }
                }

                if ($page < $totalPages) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "'>Next</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Next</a></li>";
                }
                echo "</ul>";
            echo "</div>";
        echo "</div>"; 
    }
?>

<!DOCTYPE html>
<html>
    <head>
    <title>Products</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="Images/logo.png"/>
        <link rel="stylesheet" href="customCodes/s2.css">
    </head>

    <header>
        <?php
            include "Navigation.php";
        ?>
    </header>

    <body>
        <div class="container" style="margin-top: 2rem;">
            <div class="container mb-4">
                <h1 style='text-align: center;'>Gallery</h1>
            </div>

            <div class="grid" style="margin-bottom: 3.5rem;">
                <div class="row align-items-start">
                    <?php
                        pagination();
                    ?>
                </div>
            </div>          
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>