<?php
    include_once 'setup.php';
    require_once 'connect.php';
    include 'ActivityTracker.php';

    function buildQueryString($page = null, $currentParams = []) {
        $params = [];
        
        if (isset($currentParams['sort'])) $params['sort'] = $currentParams['sort'];
        if (isset($currentParams['search'])) $params['search'] = $currentParams['search'];
        if (isset($currentParams['availability'])) $params['availability'] = $currentParams['availability'];
        if (isset($currentParams['category'])) $params['category'] = $currentParams['category'];
        
        if ($page !== null) {
            $params['page'] = $page;
        }
        
        return '?' . http_build_query($params);
    }

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;
        
        // Get parameters from URL
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        $availability = isset($_GET['availability']) ? $_GET['availability'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        
        // Build the base SQL query with JOIN to ProductBranchMaster
        $sql = "SELECT p.*, pb.Stocks, pb.Avail_FL as BranchAvailability 
                FROM `productMstr` p
                LEFT JOIN ProductBranchMaster pb ON p.ProductID = pb.ProductID";
        
        // Add conditions based on parameters
        $whereConditions = [];
        
        if (!empty($search)) {
            $whereConditions[] = "p.Model LIKE '%$search%'";
        }
        
        if (!empty($availability)) {
            if ($availability == 'Available') {
                $whereConditions[] = "pb.Avail_FL = 'Available'";
            } elseif ($availability == 'Not Available') {
                $whereConditions[] = "pb.Avail_FL != 'Available'";
            }
        }
        
        // Add category filter condition
        if (!empty($category)) {
            $category = mysqli_real_escape_string($conn, $category);
            $whereConditions[] = "p.CategoryType = '$category'";
        }
        
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // Add sorting
        switch($sort) {
            case 'price_asc':
                $sql .= " ORDER BY CAST(REPLACE(REPLACE(p.Price, '₱', ''), ',', '') AS DECIMAL(10,2)) ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY CAST(REPLACE(REPLACE(p.Price, '₱', ''), ',', '') AS DECIMAL(10,2)) DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY p.Model ASC";
                break;
            case 'name_desc':
                $sql .= " ORDER BY p.Model DESC";
                break;
            default:
                $sql .= " ORDER BY p.Model ASC";
        }
        
        // Add pagination
        $sql .= " LIMIT $start, $perPage";
        
        $result = mysqli_query($conn, $sql);
        
        // For total count
        $countSql = "SELECT COUNT(*) as total FROM `productMstr` p";
        if (!empty($whereConditions)) {
            $countSql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        $countResult = mysqli_query($conn, $countSql);
        $totalData = mysqli_fetch_assoc($countResult);
        $total = $totalData['total'];
        $totalPages = ceil($total / $perPage);

        // Display products
        echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4' id='productGrid'>";
        
        if ($total > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                $searchableText = strtolower($row['Model']);
                $stock = isset($row['Stocks']) ? $row['Stocks'] : 0;
                $faceShape = isset($row['ShapeID']) ? getFaceShapeName($row['ShapeID']) : 'Not specified';
                
                echo "<div class='col d-flex product-card' data-search='".htmlspecialchars($searchableText, ENT_QUOTES)."'>";
                    echo "<div class='card w-100' style='max-width: 380px;'>";
                        echo '<img src="' . $row['ProductImage']. '" class="card-img-top img-fluid" style="height: 280px;" alt="'. $row['Model'] .'">';
                        echo "<div class='card-body d-flex flex-column'>";
                            echo "<h5 class='card-title' style='min-height: 1.5rem;'>".$row['Model']."</h5>";
                            echo "<hr>";
                            echo "<div class='card-text mb-2'>".$row['CategoryType']."</div>";
                            echo "<div class='card-text mb-2'>".$row['Material']."</div>";
                            $price = $row['Price'];
                            $numeric_price = preg_replace('/[^0-9.]/', '', $price);
                            $formatted_price = is_numeric($numeric_price) ? '₱' . number_format((float)$numeric_price, 2) : '₱0.00';
                            echo "<div class='card-text mb-2'>".$formatted_price."</div>";
                            $availability = isset($row['BranchAvailability']) ? $row['BranchAvailability'] : $row['Avail_FL'];
                            if ($availability == "Available") {
                                echo "<div class='card-text mb-2 text-success'>".$availability."</div>";
                            echo "</div>";
                                echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                    echo "<button type='button' class='btn btn-primary w-100 py-2 view-details' data-bs-toggle='modal' data-bs-target='#productModal' 
                                          data-product-id='".$row['ProductID']."'
                                          data-product-name='".htmlspecialchars($row['Model'], ENT_QUOTES)."'
                                          data-product-image='".htmlspecialchars($row['ProductImage'], ENT_QUOTES)."'
                                          data-product-category='".htmlspecialchars($row['CategoryType'], ENT_QUOTES)."'
                                          data-product-material='".htmlspecialchars($row['Material'], ENT_QUOTES)."'
                                          data-product-price='".htmlspecialchars($formatted_price, ENT_QUOTES)."'
                                          data-product-availability='".htmlspecialchars($availability, ENT_QUOTES)."'
                                          data-product-stock='".htmlspecialchars($stock, ENT_QUOTES)."'
                                          data-product-faceshape='".htmlspecialchars($faceShape, ENT_QUOTES)."'>
                                          More details
                                      </button>";
                                echo "</div>";
                            } else {
                                echo "<div class='card-text mb-2 text-danger'>".$availability."</div>";
                            echo "</div>";
                            echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                echo "<a href='#' class='btn btn-secondary w-100 py-2 disabled'>Not Available</a>";
                            echo "</div>";
                            }                               
                    echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='col-12 py-5 no-results' style='display: flex; justify-content: center; align-items: center; min-height: 300px;'>";
            if ($availability == 'Not Available') {
                echo "<h4 class='text-center'>No unavailable products found matching your search.</h4>";
            } else {
                echo "<h4 class='text-center'>No products found matching your search.</h4>";
            }
            echo "</div>";
        }
        
        echo "</div>"; // End of card grid

        // Pagination
        if ($totalPages > 1) {
            echo "<div class='col-12 mt-5'>";
                echo "<div class='d-flex justify-content-center'>";
                    echo "<ul class='pagination'>";
                    if ($page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='" . buildQueryString($page - 1, $_GET) . "'>Previous</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                    }

                    for ($i = 1; $i <= $totalPages; $i++) {                       
                        if ($i == $page) {
                            echo "<li class='page-item active' aria-current='page'><a class='page-link disabled'>$i</a></li>"; 
                        } else {
                            echo "<li class='page-item'><a class='page-link' href='" . buildQueryString($i, $_GET) . "'>$i</a></li>";
                        }
                    }

                    if ($page < $totalPages) {
                        echo "<li class='page-item'><a class='page-link' href='" . buildQueryString($page + 1, $_GET) . "'>Next</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><a class='page-link'>Next</a></li>";
                    }
                    echo "</ul>";
                echo "</div>";
            echo "</div>"; 
        }
    }


    function getFaceShapeName($shapeID) {
        $conn = connect();
        $sql = "SELECT Description FROM shapeMaster WHERE ShapeID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $shapeID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['Description'];
        }
        return 'Not specified';
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
                justify-content: flex-end;
                gap: 20px;
                margin-bottom: 20px;
                flex-wrap: wrap;
            }
            .filter-dropdown {
                max-width: 250px;
                min-width: 200px;
            }
            .search-container {
                margin-bottom: 30px;
            }
            .search-box {
                max-width: 500px;
                margin: 0 auto;
            }
            .product-card.hidden {
                display: none;
            }
            #liveSearchResults {
                position: absolute;
                width: 100%;
                max-width: 500px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1000;
                background: white;
                border: 1px solid #ddd;
                border-radius: 0 0 5px 5px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                max-height: 300px;
                overflow-y: auto;
                display: none;
            }
            .live-search-item {
                padding: 10px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
            }
            .live-search-item:hover {
                background-color: #f8f9fa;
            }
            .live-search-item.highlight {
                background-color: #e9ecef;
            }
         
            .modal-lg-custom {
                max-width: 800px;
            }
            .product-image {
                max-height: 400px;
                object-fit: contain;
            }
            .product-details {
                padding: 20px;
            }
            .detail-row {
                margin-bottom: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #eee;
            }
            .detail-label {
                font-weight: bold;
                color: #555;
            }
            .availability-badge {
                font-size: 0.9rem;
                padding: 5px 10px;
                border-radius: 5px;
            }
            .available {
                background-color: #d4edda;
                color: #155724;
            }
            .not-available {
                background-color: #f8d7da;
                color: #721c24;
            }

.modal-product-image-container {
    height: 300px;
    width: 100%;
    min-width: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    padding: 10px;
    margin: 0 auto;
    border-radius: 5px;
    overflow: hidden;
}

.modal-product-image {
    max-height: 100%;
    max-width: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
}

            .modal-product-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 300px;
    margin-bottom: 20px;
}

.detail-label {
    font-weight: bold;
    color: #555;
}

.detail-row {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.available {
    background-color: #d4edda;
    color: #155724;
    padding: 5px 10px;
    border-radius: 4px;
}

.not-available {
    background-color: #f8d7da;
    color: #721c24;
    padding: 5px 10px;
    border-radius: 4px;
}

.modal-lg-custom {
    max-width: 800px;
}
        </style>
    </head>

    <header>
        <?php
            include "Navigation.php";
        ?>
    </header>

    <body>
   <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-lg-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 text-center">
                        <div class="modal-product-image-container mb-3">
                            <img id="modalProductImage" src="" class="img-fluid" alt="Product Image" style="max-height: 300px;">
                        </div>
                    </div>
                    <div class="col-md-6 product-details">
                        <div class="detail-row mb-3">
                            <h3 id="modalProductName" class="mb-2"></h3>
                            <span id="modalProductAvailability" class="badge"></span>
                        </div>
                        <div class="detail-row mb-3">
                            <div class="row">
                                <div class="col-5 detail-label">Category:</div>
                                <div class="col-7" id="modalProductCategory"></div>
                            </div>
                        </div>
                        <div class="detail-row mb-3">
                            <div class="row">
                                <div class="col-5 detail-label">Material:</div>
                                <div class="col-7" id="modalProductMaterial"></div>
                            </div>
                        </div>
                        <div class="detail-row mb-3">
                            <div class="row">
                                <div class="col-5 detail-label">Price:</div>
                                <div class="col-7" id="modalProductPrice"></div>
                            </div>
                        </div>
                        <div class="detail-row mb-3">
                            <div class="row">
                                <div class="col-5 detail-label">Stock Left:</div>
                                <div class="col-7" id="modalProductStock"></div>
                            </div>
                        </div>
                        <div class="detail-row mb-3">
                            <div class="row">
                                <div class="col-5 detail-label">Good for Face Shape:</div>
                                <div class="col-7" id="modalProductFaceShape"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

        <div class="container" style="margin-top: 2rem;">
            <div class="container mb-4">
                <h1 style='text-align: center;'>Gallery</h1>
                
              
                <div class="search-container">
                    <form method="get" action="" class="search-box position-relative" id="searchForm">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="searchInput" name="search" placeholder="Search products..." 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                   autocomplete="off">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if(isset($_GET['search']) && !empty($_GET['search'])): ?>
                                <a href="?" class="btn btn-outline-secondary">Clear</a>
                            <?php endif; ?>
                            <?php if(isset($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?php echo $_GET['sort']; ?>">
                            <?php endif; ?>
                            <?php if(isset($_GET['availability'])): ?>
                                <input type="hidden" name="availability" value="<?php echo $_GET['availability']; ?>">
                            <?php endif; ?>
                            <?php if(isset($_GET['category'])): ?>
                                <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                            <?php endif; ?>
                        </div>
                        <div id="liveSearchResults"></div>
                    </form>
                </div>
                
              
                <div class="filter-container">
                   
                    <form method="get" action="" class="filter-dropdown">
                        <?php if(isset($_GET['page'])): ?>
                            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo $_GET['search']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['sort'])): ?>
                            <input type="hidden" name="sort" value="<?php echo $_GET['sort']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['availability'])): ?>
                            <input type="hidden" name="availability" value="<?php echo $_GET['availability']; ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <label class="input-group-text" for="categorySelect">Category:</label>
                            <select class="form-select" id="categorySelect" name="category" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <option value="Bifocal Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Bifocal Lens') ? 'selected' : ''; ?>>Bifocal Lens</option>
                                <option value="Concave Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Concave Lens') ? 'selected' : ''; ?>>Concave Lens</option>
                                <option value="Contact Lenses" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Contact Lenses') ? 'selected' : ''; ?>>Contact Lenses</option>
                                <option value="Convex Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Convex Lens') ? 'selected' : ''; ?>>Convex Lens</option>
                                <option value="Frame" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Frame') ? 'selected' : ''; ?>>Frame</option>
                                <option value="Photochromic Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Photochromic Lens') ? 'selected' : ''; ?>>Photochromic Lens</option>
                                <option value="Polarized Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Polarized Lens') ? 'selected' : ''; ?>>Polarized Lens</option>
                                <option value="Progressive Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Progressive Lens') ? 'selected' : ''; ?>>Progressive Lens</option>
                                <option value="Sunglasses" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Sunglasses') ? 'selected' : ''; ?>>Sunglasses</option>
                                <option value="Trifocal Lens" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Trifocal Lens') ? 'selected' : ''; ?>>Trifocal Lens</option>
                            </select>
                        </div>
                    </form>
                    
                    <!-- Availability Filter -->
                    <form method="get" action="" class="filter-dropdown">
                        <?php if(isset($_GET['page'])): ?>
                            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo $_GET['search']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['sort'])): ?>
                            <input type="hidden" name="sort" value="<?php echo $_GET['sort']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['category'])): ?>
                            <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <label class="input-group-text" for="availabilitySelect">Filter:</label>
                            <select class="form-select" id="availabilitySelect" name="availability" onchange="this.form.submit()">
                                <option value="">All Products</option>
                                <option value="Available" <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'Available') ? 'selected' : ''; ?>>Available Only</option>
                                <option value="Not Available" <?php echo (isset($_GET['availability']) && $_GET['availability'] == 'Not Available') ? 'selected' : ''; ?>>Not Available</option>
                            </select>
                        </div>
                    </form>
                    
                    <!-- Sort Dropdown -->
                    <form method="get" action="" class="filter-dropdown">
                        <?php if(isset($_GET['page'])): ?>
                            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['search'])): ?>
                            <input type="hidden" name="search" value="<?php echo $_GET['search']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['availability'])): ?>
                            <input type="hidden" name="availability" value="<?php echo $_GET['availability']; ?>">
                        <?php endif; ?>
                        <?php if(isset($_GET['category'])): ?>
                            <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                        <?php endif; ?>
                        <div class="input-group">
                            <label class="input-group-text" for="sortSelect">Sort by:</label>
                            <select class="form-select" id="sortSelect" name="sort" onchange="this.form.submit()">
                                <option value="name_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="name_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name_desc') ? 'selected' : ''; ?>>Name (Z-A)</option>
                                <option value="price_asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'selected' : ''; ?>>Price (Low to High)</option>
                                <option value="price_desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'selected' : ''; ?>>Price (High to Low)</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid" style="margin-bottom: 3.5rem;">
                <?php
                    pagination();
                ?>
            </div>   
                <footer class="py-5 border-top mt-5 pt-4" style="background-color: #ffffff; margin-top: 50px; border-color: #ffffff;">
        <div class="container">
            <div class="row text-center text-md-start">
                <div class="col-md-3 mb-3 mb-md-0 text-center">
                    <img src="Images/logo.png" alt="Logo" width="200">
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <h6 class="fw-bold">PRODUCTS</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-dark text-decoration-none">Frames</a></li>
                        <li><a href="#" class="text-dark text-decoration-none">Sunglasses</a></li>
                    </ul>
                </div>

                <div class="col-md-3 mb-3 mb-md-0">
                    <h6 class="fw-bold">About</h6>
                    <ul class="list-unstyled">
                        <li><a href="aboutus.php" class="text-dark text-decoration-none">About Us</a></li>
                        <li><a href="ourservices.php" class="text-dark text-decoration-none">Services</a></li>
                    </ul>
                </div>

                <div class="col-md-3">
                    <h6 class="fw-bold">CONTACT US!</h6>
                    <p class="mb-1">Address: #6 Rizal Avenue Extension, Brgy. San Agustin, Malabon City</p>
                    <p class="mb-1">Phone: 027-508-4792</p>
                    <p class="mb-1">Cell: 0932-844-7068</p>
                    <p>Email: <a href="mailto:Santosoptical@gmail.com" class="text-dark">Santosoptical@gmail.com</a></p>
                </div>
            </div>
            <div class="container-fluid text-center py-3" style="background-color: white">
                <p class="m-0">COPYRIGHT &copy; SANTOS OPTICAL co., ltd. ALL RIGHTS RESERVED.</p>
            </div>
        </div>
    </footer>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('searchInput');
                const liveSearchResults = document.getElementById('liveSearchResults');
                const productCards = document.querySelectorAll('.product-card');
                const searchForm = document.getElementById('searchForm');
                
                // Modal functionality
                const productModal = document.getElementById('productModal');
                if (productModal) {
                    productModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const productName = button.getAttribute('data-product-name');
                        const productImage = button.getAttribute('data-product-image');
                        const productCategory = button.getAttribute('data-product-category');
                        const productMaterial = button.getAttribute('data-product-material');
                        const productPrice = button.getAttribute('data-product-price');
                        const productAvailability = button.getAttribute('data-product-availability');
                        const productStock = button.getAttribute('data-product-stock');
                        const productFaceShape = button.getAttribute('data-product-faceshape');
                        
                        document.getElementById('modalProductName').textContent = productName;
                        document.getElementById('modalProductImage').src = productImage;
                        document.getElementById('modalProductImage').alt = productName;
                        document.getElementById('modalProductCategory').textContent = productCategory;
                        document.getElementById('modalProductMaterial').textContent = productMaterial;
                        document.getElementById('modalProductPrice').textContent = productPrice;
                        document.getElementById('modalProductStock').textContent = productStock;
                        document.getElementById('modalProductFaceShape').textContent = productFaceShape;
                        
                        const availabilityBadge = document.getElementById('modalProductAvailability');
                        availabilityBadge.textContent = productAvailability;
                        availabilityBadge.className = 'availability-badge ' + 
                            (productAvailability === 'Available' ? 'available' : 'not-available');
                    });
                }
                
                function performLiveSearch() {
                    const searchTerm = searchInput.value.trim().toLowerCase();
                    
                    if (searchTerm.length === 0) {
                        liveSearchResults.style.display = 'none';
                        return;
                    }
                    
                    const matches = [];
                    
                    productCards.forEach(card => {
                        const cardTitle = card.querySelector('.card-title').textContent.toLowerCase();
                        
                        if (cardTitle.startsWith(searchTerm)) {
                            matches.push({
                                element: card,
                                title: card.querySelector('.card-title').textContent
                            });
                        }
                    });
                    
                    if (matches.length > 0) {
                        liveSearchResults.innerHTML = '';
                        matches.slice(0, 5).forEach(match => {
                            const resultItem = document.createElement('div');
                            resultItem.className = 'live-search-item';
                            resultItem.textContent = match.title;
                            
                            resultItem.addEventListener('click', function() {
                                searchInput.value = match.title;
                                searchForm.submit();
                            });
                            
                            liveSearchResults.appendChild(resultItem);
                        });
                        
                        if (matches.length > 5) {
                            const moreItem = document.createElement('div');
                            moreItem.className = 'live-search-item text-center text-muted small';
                            moreItem.textContent = `+${matches.length - 5} more items...`;
                            liveSearchResults.appendChild(moreItem);
                        }
                        
                        liveSearchResults.style.display = 'block';
                    } else {
                        liveSearchResults.innerHTML = '<div class="live-search-item text-muted">No matches found</div>';
                        liveSearchResults.style.display = 'block';
                    }
                }
                
                function filterProducts() {
                    const searchTerm = searchInput.value.trim().toLowerCase();
                    
                    if (searchTerm.length === 0) {
                        productCards.forEach(card => {
                            card.classList.remove('hidden');
                        });
                        return;
                    }
                    
                    let visibleCount = 0;
                    
                    productCards.forEach(card => {
                        const cardTitle = card.querySelector('.card-title').textContent.toLowerCase();
                        
                        if (cardTitle.startsWith(searchTerm)) {
                            card.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            card.classList.add('hidden');
                        }
                    });
                    
                    const noResultsElement = document.querySelector('.no-results');
                    if (noResultsElement) {
                        noResultsElement.style.display = visibleCount > 0 ? 'none' : 'block';
                    }
                }
                
                searchInput.addEventListener('input', function() {
                    performLiveSearch();
                });
                
                searchInput.addEventListener('focus', function() {
                    if (searchInput.value.trim().length > 0) {
                        performLiveSearch();
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !liveSearchResults.contains(e.target)) {
                        liveSearchResults.style.display = 'none';
                    }
                });
                
                searchInput.addEventListener('keydown', function(e) {
                    const items = liveSearchResults.querySelectorAll('.live-search-item');
                    let currentHighlight = liveSearchResults.querySelector('.live-search-item.highlight');
                    
                    if (e.key === 'Escape') {
                        liveSearchResults.style.display = 'none';
                        return;
                    }
                    
                    if (items.length === 0) return;
                    
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!currentHighlight) {
                            items[0].classList.add('highlight');
                        } else {
                            currentHighlight.classList.remove('highlight');
                            const next = currentHighlight.nextElementSibling || items[0];
                            next.classList.add('highlight');
                            next.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (!currentHighlight) {
                            items[items.length - 1].classList.add('highlight');
                        } else {
                            currentHighlight.classList.remove('highlight');
                            const prev = currentHighlight.previousElementSibling || items[items.length - 1];
                            prev.classList.add('highlight');
                            prev.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'Enter' && currentHighlight) {
                        e.preventDefault();
                        searchInput.value = currentHighlight.textContent;
                        searchForm.submit();
                    }
                });
                
                searchForm.addEventListener('submit', function(e) {
                    return true;
                });
                
                if (searchInput.value) {
                    filterProducts();
                }
            });
        </script>
    </body>
</html>
