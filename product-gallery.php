function pagination() {
    $conn = connect();

    $perPage = 12; 
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
    $start = ($page - 1) * $perPage;
    
    // Get parameters from URL
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    $shape = isset($_GET['shape']) ? (int)$_GET['shape'] : 0;
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $branch = isset($_GET['branch']) ? (int)$_GET['branch'] : 0;
    
    // Build different queries for All Branches vs Specific Branch
    if ($branch > 0) {
        // Specific Branch View - show only products available at this branch
        $sql = "SELECT DISTINCT p.*, pb.Stocks, b.BranchName
                FROM `productMstr` p
                JOIN ProductBranchMaster pb ON p.ProductID = pb.ProductID
                JOIN BranchMaster b ON pb.BranchCode = b.BranchCode
                LEFT JOIN archives a ON (p.ProductID = a.TargetID AND a.TargetType = 'product')
                WHERE (pb.Avail_FL = 'Available' OR pb.Avail_FL IS NULL)
                AND a.ArchiveID IS NULL
                AND pb.BranchCode = $branch";
    } else {
        // All Branches View - show each product once with all available branches
        $sql = "SELECT p.*, 
                (SELECT GROUP_CONCAT(DISTINCT b.BranchName SEPARATOR ', ') 
                 FROM ProductBranchMaster pb 
                 JOIN BranchMaster b ON pb.BranchCode = b.BranchCode 
                 WHERE pb.ProductID = p.ProductID AND (pb.Avail_FL = 'Available' OR pb.Avail_FL IS NULL)) as AvailableBranches,
                (SELECT MIN(pb.Stocks) FROM ProductBranchMaster pb WHERE pb.ProductID = p.ProductID) as MinStocks
                FROM `productMstr` p
                LEFT JOIN archives a ON (p.ProductID = a.TargetID AND a.TargetType = 'product')
                WHERE (p.Avail_FL = 'Available' OR p.Avail_FL IS NULL)
                AND a.ArchiveID IS NULL";
    }
    
    $whereConditions = [];
    
    if (!empty($search)) {
        $whereConditions[] = "p.Model LIKE '%" . $search . "%'";
    }
    
    if ($shape > 0) {
        $whereConditions[] = "p.ShapeID = $shape";
    }
    
    if (!empty($category)) {
        $category = mysqli_real_escape_string($conn, $category);
        $whereConditions[] = "p.CategoryType = '$category'";
    }
    
    if (!empty($whereConditions)) {
        $sql .= " AND " . implode(' AND ', $whereConditions);
    }
    
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
    
    // Get total count - FIXED VERSION
    $countSql = "SELECT COUNT(*) as total FROM ($sql) as count_table";
    
    $countResult = mysqli_query($conn, $countSql);
    if ($countResult) {
        $totalData = mysqli_fetch_assoc($countResult);
        $total = $totalData['total'];
    } else {
        // Fallback if the count query fails
        $total = 0;
    }
    $totalPages = ceil($total / $perPage);

    // Add pagination limits to main query
    $sql .= " LIMIT $start, $perPage";
    $result = mysqli_query($conn, $sql);
    
    echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4' id='productGrid'>";
    
    if ($total > 0 && $result) {
        while($row = mysqli_fetch_assoc($result)) {
            $searchableText = strtolower($row['Model']);
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
                        
                        if ($branch > 0) {
                            // Specific branch view
                            $stock = $row['Stocks'];
                            $branchName = $row['BranchName'];
                            $availability = ($stock > 0) ? "Available at $branchName" : "Out of stock at $branchName";
                            
                            if ($stock > 0) {
                                echo "<div class='card-text mb-2 text-success'>$availability</div>";
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
                                echo "<div class='card-text mb-2 text-danger'>$availability</div>";
                                echo "</div>";
                                echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                    echo "<a href='#' class='btn btn-secondary w-100 py-2 disabled'>Not Available</a>";
                                echo "</div>";
                            }
                        } else {
                            // All branches view
                            $availableBranches = $row['AvailableBranches'];
                            $minStock = $row['MinStocks'];
                            
                            if (!empty($availableBranches)) {
                                echo "<div class='card-text mb-2 text-success'>Available at: $availableBranches</div>";
                                echo "</div>";
                                echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                    echo "<button type='button' class='btn btn-primary w-100 py-2 view-details' data-bs-toggle='modal' data-bs-target='#productModal' 
                                          data-product-id='".$row['ProductID']."'
                                          data-product-name='".htmlspecialchars($row['Model'], ENT_QUOTES)."'
                                          data-product-image='".htmlspecialchars($row['ProductImage'], ENT_QUOTES)."'
                                          data-product-category='".htmlspecialchars($row['CategoryType'], ENT_QUOTES)."'
                                          data-product-material='".htmlspecialchars($row['Material'], ENT_QUOTES)."'
                                          data-product-price='".htmlspecialchars($formatted_price, ENT_QUOTES)."'
                                          data-product-availability='".htmlspecialchars($availableBranches, ENT_QUOTES)."'
                                          data-product-stock='".htmlspecialchars($minStock, ENT_QUOTES)."'
                                          data-product-faceshape='".htmlspecialchars($faceShape, ENT_QUOTES)."'>
                                          More details
                                      </button>";
                                echo "</div>";
                            } else {
                                echo "<div class='card-text mb-2 text-danger'>Not available at any branch</div>";
                                echo "</div>";
                                echo "<div class='card-footer bg-transparent border-top-0 mt-auto pt-0'>";
                                    echo "<a href='#' class='btn btn-secondary w-100 py-2 disabled'>Not Available</a>";
                                echo "</div>";
                            }
                        }
                    echo "</div>";
                echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<div class='col-12 py-5 no-results' style='display: flex; justify-content: center; align-items: center; min-height: 300px;'>";
        if ($shape > 0) {
            $shapeName = getFaceShapeName($shape);
            echo "<h4 class='text-center'>No products found for frame shape: $shapeName</h4>";
        } else if ($branch > 0) {
            $conn = connect();
            $branchQuery = "SELECT BranchName FROM BranchMaster WHERE BranchCode = $branch";
            $branchResult = mysqli_query($conn, $branchQuery);
            $branchName = mysqli_fetch_assoc($branchResult)['BranchName'];
            $conn->close();
            echo "<h4 class='text-center'>No products found at branch: $branchName</h4>";
        } else {
            echo "<h4 class='text-center'>No products found matching your search.</h4>";
        }
        echo "</div>";
    }
    
    echo "</div>"; 

    // Pagination links
    if ($totalPages > 1) {
        echo "<div class='col-12 mt-5'>";
            echo "<div class='d-flex justify-content-center'>";
                echo "<ul class='pagination'>";
                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='" . buildQueryString($page - 1, $_GET) . "'>Previous</a></li>";
                } else {
                    echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                }

                $maxPagesToShow = 5;
                $startPage = max(1, $page - floor($maxPagesToShow / 2));
                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                
                if ($endPage - $startPage < $maxPagesToShow - 1) {
                    $startPage = max(1, $endPage - $maxPagesToShow + 1);
                }
                
                if ($startPage > 1) {
                    echo "<li class='page-item'><a class='page-link' href='" . buildQueryString(1, $_GET) . "'>1</a></li>";
                    if ($startPage > 2) {
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                    }
                }
                
                for ($i = $startPage; $i <= $endPage; $i++) {                       
                    if ($i == $page) {
                        echo "<li class='page-item active' aria-current='page'><a class='page-link disabled'>$i</a></li>"; 
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='" . buildQueryString($i, $_GET) . "'>$i</a></li>";
                    }
                }
                
                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
                    }
                    echo "<li class='page-item'><a class='page-link' href='" . buildQueryString($totalPages, $_GET) . "'>$totalPages</a></li>";
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
