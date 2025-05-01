<?php
    include_once 'setup.php';
    require_once 'connect.php';
    include 'ActivityTracker.php';

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;
        
        // Get sort and filter parameters from URL
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        $material_filter = isset($_GET['material']) ? $_GET['material'] : '';
        
        // Build the base SQL query
        $sql = "SELECT * FROM `productMstr`";
        
        // Add material filter if selected
        if (!empty($material_filter) {
            $sql .= " WHERE Material = '" . mysqli_real_escape_string($conn, $material_filter) . "'";
        }
        
        // Add sorting based on the selected option
        switch($sort) {
            case 'price_asc':
                $sql .= " ORDER BY CAST(REPLACE(REPLACE(Price, '₱', ''), ',', '') AS DECIMAL(10,2)) ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY CAST(REPLACE(REPLACE(Price, '₱', ''), ',', '') AS DECIMAL(10,2)) DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY Model ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY Model DESC";
                break;
            default:
                $sql .= " ORDER BY Model ASC";
        }
        
        // Add pagination
        $sql .= " LIMIT $start, $perPage";
        
        $result = mysqli_query($conn, $sql);
        
        // For total count, we need to consider the filter
        $count_sql = "SELECT COUNT(*) as total FROM `productMstr`";
        if (!empty($material_filter)) {
            $count_sql .= " WHERE Material = '" . mysqli_real_escape_string($conn, $material_filter) . "'";
        }
        $count_result = mysqli_query($conn, $count_sql);
        $total_row = mysqli_fetch_assoc($count_result);
        $total = $total_row['total'];
        $totalPages = ceil($total / $perPage);

        // Start of card grid - fewer columns for wider cards
        echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4'>";
        
        while($row = mysqli_fetch_assoc($result)) {
            echo "<div class='col d-flex'>";
                echo "<div class='card w-100' style='max-width: 380px;'>";
                    echo '<img src="' . $row['ProductImage']. '" class="card-img-top img-fluid" style="height: 280px;" alt="'. $row['Model'] .'">';
                    echo "<div class='card-body d-flex flex-column'>";
                        echo "<h5 class='card-title' style='min-height: 1.5rem;'>".$row['Model']."</h5>";
                        echo "<hr>";
                        echo "<div class='card-text mb-2'>".$row['CategoryType']."</div>";
                        echo "<div class='card-text mb-2'>".$row['Material']."</div>";
                        // Fixed price display with peso sign handling
                        $price = $row['Price'];
                        $numeric_price = preg_replace('/[^0-9.]/', '', $price);
                        $formatted_price = is_numeric($numeric_price) ? '₱' . number_format((float)$numeric_price, 2) : '₱0.00';
                        echo "<div class='card-text mb-2'>".$formatted_price."</div>";
                        if ($row['Avail_FL'] == "Available") {
                            echo "<div class='card-text mb-2 text-success'>".$row['Avail_FL']."</div>";
                        echo "</div>";
                            echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                echo "<a href='#' class='btn btn-primary w-100 py-2'>More details</a>";
                            echo "</div>";
                        } else {
                            echo "<div class='card-text mb-2 text-danger'>".$row['Avail_FL']."</div>";
                        echo "</div>";
                        echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                            echo "<a href='#' class='btn btn-secondary w-100 py-2 disabled'>Not Available</a>";
                        echo "</div>";
                        }                               
                echo "</div>";
            echo "</div>";
        }
        
        echo "</div>"; // End of card grid

        // Build query parameters for pagination links
        $query_params = [];
        if (!empty($sort)) {
            $query_params['sort'] = $sort;
        }
        if (!empty($material_filter)) {
            $query_params['material'] = $material_filter;
        }
        $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';

        // Pagination with preserved sort and filter parameters
        echo "<div class='col-12 mt-5'>";
            echo "<div class='d-flex justify-content-center'>";
                echo "<ul class='pagination'>";
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . $query_string . "'>Previous</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                }

                for ($i = 1; $i <= $totalPages; $i++) {                       
                    if ($i == $page) {
                        echo "<li class='page-item active' aria-current='page'><a class='page-link disabled'>$i</a></li>"; 
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='?page=$i" . $query_string . "'>$i</a></li>";
                    }
                }

                if ($page < $totalPages) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . $query_string . "'>Next</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Next</a></li>";
                }
                echo "</ul>";
            echo "</div>";
        echo "</div>"; 
    }

    function getUniqueMaterials() {
        $conn = connect();
        $sql = "SELECT DISTINCT Material FROM `productMstr` ORDER BY Material";
        $result = mysqli_query($conn, $sql);
        $materials = [];
        while($row = mysqli_fetch_assoc($result)) {
            $materials[] = $row['Material'];
        }
        return $materials;
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
        <style>
            /* Custom CSS for wider cards */
            @media (min-width: 768px) {
                .container {
                    max-width: 95%;
                }
            }
            @media (min-width: 1200px) {
                .container {
                    max-width: 1400px;
                }
            }
            .card {
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                transition: transform 0.3s ease;
            }
            .card:hover {
                transform: translateY(-5px);
            }
            .sort-dropdown {
                margin-bottom: 20px;
                max-width: 250px;
                margin-left: auto;
            }
            .filter-container {
                display: flex;
                justify-content: space-between;
                flex-wrap: wrap;
                margin-bottom: 20px;
            }
            .material-filter {
                max-width: 250px;
            }
            .reset-btn {
                align-self: flex-end;
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
                
                <!-- Filter and Sort Container -->
                <div class="filter-container">
                    <!-- Material Filter -->
                    <div class="material-filter">
                        <form method="get" action="">
                            <div class="input-group">
                                <label class="input-group-text" for="materialSelect">Filter by Material:</label>
                                <select class="form-select" id="materialSelect" name="material" onchange="this.form.submit()">
                                    <option value="">All Materials</option>
                                    <?php
                                        $materials = getUniqueMaterials();
                                        $selected_material = isset($_GET['material']) ? $_GET['material'] : '';
                                        foreach($materials as $material) {
                                            $selected = ($material == $selected_material) ? 'selected' : '';
                                            echo "<option value=\"" . htmlspecialchars($material) . "\" $selected>" . htmlspecialchars($material) . "</option>";
                                        }
                                    ?>
                                </select>
                                <?php 
                                    if(isset($_GET['page'])) {
                                        echo '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
                                    }
                                    if(isset($_GET['sort'])) {
                                        echo '<input type="hidden" name="sort" value="' . $_GET['sort'] . '">';
                                    }
                                ?>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Reset Button -->
                    <?php if(isset($_GET['material']) || isset($_GET['sort'])): ?>
                    <div class="reset-btn">
                        <a href="?" class="btn btn-outline-secondary">Reset Filters</a>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Sorting Dropdown -->
                    <div class="sort-dropdown">
                        <form method="get" action="">
                            <div class="input-group">
                                <label class="input-group-text" for="sortSelect">Sort by:</label>
                                <select class="form-select" id="sortSelect" name="sort" onchange="this.form.submit()">
                                    <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                                    <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                                    <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                                    <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                                </select>
                                <?php 
                                    if(isset($_GET['page'])) {
                                        echo '<input type="hidden" name="page" value="' . $_GET['page'] . '">';
                                    }
                                    if(isset($_GET['material'])) {
                                        echo '<input type="hidden" name="material" value="' . $_GET['material'] . '">';
                                    }
                                ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="grid" style="margin-bottom: 3.5rem;">
                <?php
                    pagination();
                ?>
            </div>          
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
