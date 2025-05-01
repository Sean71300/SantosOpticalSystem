<?php
    include_once 'setup.php';
    require_once 'connect.php';
    include 'ActivityTracker.php';

    function getAvailableMaterials() {
        $conn = connect();
        $sql = "SELECT DISTINCT Material FROM `productMstr` WHERE Avail_FL = 'Available' AND Material IS NOT NULL AND Material != '' ORDER BY Material ASC";
        $result = mysqli_query($conn, $sql);
        $materials = array();
        while($row = mysqli_fetch_assoc($result)) {
            $materials[] = $row['Material'];
        }
        $conn->close();
        return $materials;
    }

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;
        
        // Get sort and filter parameters from URL
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        $materialFilter = isset($_GET['material']) ? $_GET['material'] : '';
        
        // Build the base SQL query
        $sql = "SELECT * FROM `productMstr` WHERE Avail_FL = 'Available'";
        
        // Add material filter if selected
        if (!empty($materialFilter)) {
            $materialFilter = mysqli_real_escape_string($conn, $materialFilter);
            $sql .= " AND Material = '$materialFilter'";
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
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM `productMstr` WHERE Avail_FL = 'Available'";
        if (!empty($materialFilter)) {
            $countSql .= " AND Material = '$materialFilter'";
        }
        $countResult = mysqli_query($conn, $countSql);
        $totalRow = mysqli_fetch_assoc($countResult);
        $total = $totalRow['total'];
        $totalPages = ceil($total / $perPage);

        // Start of card grid
        echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4'>";
        
        if (mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                echo "<div class='col d-flex'>";
                    echo "<div class='card w-100' style='max-width: 380px;'>";
                        echo '<img src="' . $row['ProductImage'] . '" class="card-img-top img-fluid" style="height: 280px;" alt="'. htmlspecialchars($row['Model']) .'">';
                        echo "<div class='card-body d-flex flex-column'>";
                            echo "<h5 class='card-title' style='min-height: 1.5rem;'>".htmlspecialchars($row['Model'])."</h5>";
                            echo "<hr>";
                            echo "<div class='card-text mb-2'>".htmlspecialchars($row['CategoryType'])."</div>";
                            echo "<div class='card-text mb-2'>".htmlspecialchars($row['Material'])."</div>";
                            // Fixed price display with peso sign handling
                            $price = $row['Price'];
                            $numeric_price = preg_replace('/[^0-9.]/', '', $price);
                            $formatted_price = is_numeric($numeric_price) ? '₱' . number_format((float)$numeric_price, 2) : '₱0.00';
                            echo "<div class='card-text mb-2'>".htmlspecialchars($formatted_price)."</div>";
                            if ($row['Avail_FL'] == "Available") {
                                echo "<div class='card-text mb-2 text-success'>".htmlspecialchars($row['Avail_FL'])."</div>";
                            echo "</div>";
                                echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                    echo "<a href='#' class='btn btn-primary w-100 py-2'>More details</a>";
                                echo "</div>";
                            } else {
                                echo "<div class='card-text mb-2 text-danger'>".htmlspecialchars($row['Avail_FL'])."</div>";
                            echo "</div>";
                            echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                echo "<a href='#' class='btn btn-secondary w-100 py-2 disabled'>Not Available</a>";
                            echo "</div>";
                            }                               
                    echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='col-12 text-center py-5'>";
            echo "<h4>No products found with the selected filter</h4>";
            echo "</div>";
        }
        
        echo "</div>"; // End of card grid

        // Pagination with preserved sort and filter parameters
        echo "<div class='col-12 mt-5'>";
            echo "<div class='d-flex justify-content-center'>";
                echo "<ul class='pagination'>";
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "&sort=$sort".(!empty($materialFilter) ? "&material=".urlencode($materialFilter) : "")."'>Previous</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                }

                for ($i = 1; $i <= $totalPages; $i++) {                       
                    if ($i == $page) {
                        echo "<li class='page-item active' aria-current='page'><a class='page-link disabled'>$i</a></li>"; 
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='?page=$i&sort=$sort".(!empty($materialFilter) ? "&material=".urlencode($materialFilter) : "")."'>$i</a></li>";
                    }
                }

                if ($page < $totalPages) {
                    echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "&sort=$sort".(!empty($materialFilter) ? "&material=".urlencode($materialFilter) : "")."'>Next</a></li>";
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
            .filter-container {
                display: flex;
                justify-content: space-between;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }
            .sort-dropdown, .material-dropdown {
                margin-bottom: 10px;
                max-width: 250px;
            }
            @media (max-width: 768px) {
                .filter-container {
                    flex-direction: column;
                }
                .sort-dropdown, .material-dropdown {
                    max-width: 100%;
                }
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
                
                <!-- Filter Container -->
                <div class="filter-container">
                    <!-- Material Filter Dropdown -->
                    <div class="material-dropdown">
                        <form method="get" action="">
                            <div class="input-group">
                                <label class="input-group-text" for="materialSelect">Filter by Material:</label>
                                <select class="form-select" id="materialSelect" name="material" onchange="this.form.submit()">
                                    <option value="">All Materials</option>
                                    <?php
                                        $materials = getAvailableMaterials();
                                        foreach ($materials as $material) {
                                            $selected = (isset($_GET['material']) && $_GET['material'] == $material) ? 'selected' : '';
                                            echo "<option value='".htmlspecialchars($material)."' $selected>".htmlspecialchars($material)."</option>";
                                        }
                                    ?>
                                </select>
                                <?php 
                                    if(isset($_GET['page'])) {
                                        echo '<input type="hidden" name="page" value="'.htmlspecialchars($_GET['page']).'">';
                                    }
                                    if(isset($_GET['sort'])) {
                                        echo '<input type="hidden" name="sort" value="'.htmlspecialchars($_GET['sort']).'">';
                                    }
                                ?>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Sorting Dropdown -->
                    <div class="sort-dropdown">
                        <form method="get" action="">
                            <div class="input-group">
                                <label class="input-group-text" for="sortSelect">Sort by:</label>
                                <select class="form-select" id="sortSelect" name="sort" onchange="this.form.submit()">
                                    <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                    <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                                    <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                                    <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                                </select>
                                <?php 
                                    if(isset($_GET['page'])) {
                                        echo '<input type="hidden" name="page" value="'.htmlspecialchars($_GET['page']).'">';
                                    }
                                    if(isset($_GET['material'])) {
                                        echo '<input type="hidden" name="material" value="'.htmlspecialchars($_GET['material']).'">';
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
