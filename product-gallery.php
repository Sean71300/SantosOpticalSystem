<?php
    include_once 'setup.php';
    require_once 'connect.php';
    session_start();

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;

        $sql = "SELECT * FROM `productmstr` LIMIT $start, $perPage";
        $result = mysqli_query($conn, $sql);

        $total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `productmstr`"));
        $totalPages = ceil($total / $perPage);

        while($row = mysqli_fetch_assoc($result)) {
            echo "<div class='col g-3'>";
                    echo "<div class='card' style='width: 18.5rem; height: 29.5rem;'>";
                        echo '<img src="' . $row['ProductImage']. '"class="card-img-top" style="height: 300px;" alt="'. $row['Model'] .'">';
                            echo "<div class='card-body'>";
                                echo "<h5 class='card-title overflow-hidden' style='height:3.5rem;'>".$row['Model']."</h5>";
                                echo "<p class='card-text'>".$row['Avail_FL']."</p>";                                
                            echo "</div>";
                        echo "<a href='#' class='btn btn-primary'>Go somewhere</a>";
                echo "</div>";
            echo "</div>";
        }
        echo "<div class='col-12'>";
            echo "<div class='d-flex justify-content-center mt-5'>";
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
                }
            echo "</div>";
        echo "</div>"; 
    
?>


<!DOCTYPE html>
<html>
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
        <link rel="stylesheet" href="customCodes/custom.css">
        <title>Gallery</title>
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