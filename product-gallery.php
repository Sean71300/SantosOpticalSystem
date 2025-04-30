<?php
    include_once 'setup.php';
    require_once 'connect.php';
    include 'ActivityTracker.php';

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;
        
        // Determine sorting
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
        $orderBy = '';
        
        switch($sort) {
            case 'name_asc':
                $orderBy = 'ORDER BY Model ASC';
                break;
            case 'name_desc':
                $orderBy = 'ORDER BY Model DESC';
                break;
            case 'price_asc':
                $orderBy = 'ORDER BY Price ASC';
                break;
            case 'price_desc':
                $orderBy = 'ORDER BY Price DESC';
                break;
            default:
                $orderBy = ''; 
        }

        $sql = "SELECT *, 
                (CASE 
                    WHEN Model LIKE '%Pro%' THEN 59999
                    WHEN Model LIKE '%Plus%' THEN 44999
                    WHEN Model LIKE '%Standard%' THEN 34999
                    WHEN Model LIKE '%Mini%' THEN 24999
                    ELSE 14999 
                END) AS Price 
                FROM `productmstr` $orderBy LIMIT $start, $perPage";
        $result = mysqli_query($conn, $sql);

        $total = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM `productmstr`"));
        $totalPages = ceil($total / $perPage);

        while($row = mysqli_fetch_assoc($result)) {
            echo "<div class='col g-3'>";
                    echo "<div class='card' style='width: 18.5rem; height: 32rem;'>";
                        echo '<img src="' . $row['ProductImage']. '"class="card-img-top" style="height: 300px; object-fit: cover;" alt="'. $row['Model'] .'">';
                            echo "<div class='card-body d-flex flex-column'>";
                                echo "<h5 class='card-title overflow-hidden' style='height:3.5rem;'>".$row['Model']."</h5>";
                                echo "<p class='card-text'>".$row['Avail_FL']."</p>";
                                echo "<p class='card-text fw-bold mt-auto'>₱".number_format($row['Price'], 0, '', ',')."</p>";                                
                            echo "</div>";
                        echo "<a href='#' class='btn btn-primary mt-auto'>View Details</a>";
                    echo "</div>";
            echo "</div>";
        }
        
  
        echo "<div class='col-12 mt-5'>";
            echo "<div class='d-flex justify-content-center'>";
                echo "<ul class='pagination'>";
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "&sort=$sort'>Previous</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                }

                for ($i = 1; $i <= $totalPages; $i++) {                       
                    if ($i == $page) {
                        echo "<li class='page-item active' aria-current='page'><a class='page-link'>$i</a></li>"; 
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='?page=$i&sort=$sort'>$i</a></li>";
                    }
                }

                if ($page < $totalPages) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "&sort=$sort'>Next</a></li>";
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
    <title>Gallery</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="customCodes/s1.css">
        <link rel="stylesheet" href="customCodes/custom.css">
        <link rel="shortcut icon" type="image/x-icon" href="images/logo.png"/>
        <link rel="stylesheet" href="customCodes/s2.css">
        <style>
            .sort-dropdown {
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-end;
            }
            .sort-btn {
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                color: #495057;
            }
            .sort-btn:hover {
                background-color: #e9ecef;
            }
            .dropdown-menu {
                min-width: 200px;
            }
            .dropdown-item.active, .dropdown-item:active {
                background-color: #0d6efd;
            }
            .pagination {
                margin-top: 2rem;
            }
            .page-item .page-link {
                margin: 0 5px;
                border-radius: 5px;
            }
            .page-item.active .page-link {
                background-color: #0d6efd;
                border-color: #0d6efd;
            }
            .card-text.fw-bold {
                color: #dc3545;
                font-size: 1.25rem;
                margin-bottom: 1rem;
            }
        </style>
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
                
                <div class="sort-dropdown">
    <div class="dropdown">
        <button class="btn btn-secondary dropdown-toggle sort-btn" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-sort-down"></i> Sort By
        </button>
        <ul class="dropdown-menu" aria-labelledby="sortDropdown">
            <li><h6 class="dropdown-header">Name</h6></li>
            <li><a class="dropdown-item <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'active' : ''; ?>" href="?sort=name_asc">A to Z</a></li>
            <li><a class="dropdown-item <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'active' : ''; ?>" href="?sort=name_desc">Z to A</a></li>
            
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">Price</h6></li>
            <li><a class="dropdown-item <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'active' : ''; ?>" href="?sort=price_asc">Low to High</a></li>
            <li><a class="dropdown-item <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'active' : ''; ?>" href="?sort=price_desc">High to Low</a></li>
        </ul>
    </div>
</div>
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
