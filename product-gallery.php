<?php
    include_once 'setup.php';
    require_once 'connect.php';
    include 'ActivityTracker.php';

    function pagination() {
        $conn = connect();

        $perPage = 12; 
        $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
        $start = ($page - 1) * $perPage;
        
        // Get sort and search parameters from URL
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        // Build the base SQL query
        $sql = "SELECT * FROM `productMstr`";
        
        // Add search condition if search term exists
        if (!empty($search)) {
            $sql .= " WHERE Model LIKE '%$search%' OR CategoryType LIKE '%$search%' OR Material LIKE '%$search%'";
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
        
        // For total count, we need a separate query without LIMIT
        $countSql = "SELECT COUNT(*) as total FROM `productMstr`";
        if (!empty($search)) {
            $countSql .= " WHERE Model LIKE '%$search%' OR CategoryType LIKE '%$search%' OR Material LIKE '%$search%'";
        }
        $countResult = mysqli_query($conn, $countSql);
        $totalData = mysqli_fetch_assoc($countResult);
        $total = $totalData['total'];
        $totalPages = ceil($total / $perPage);

        // Start of card grid
        echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4'>";
        
        if ($total > 0) {
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
        } else {
            echo "<div class='col-12 text-center py-5'>";
            echo "<h4>No products found matching your search.</h4>";
            echo "</div>";
        }
        
        echo "</div>"; // End of card grid

        // Pagination with search and sort parameters
        if ($totalPages > 1) {
            echo "<div class='col-12 mt-5'>";
                echo "<div class='d-flex justify-content-center'>";
                    echo "<ul class='pagination'>";
                    if ($page > 1) {
                        echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "&sort=$sort" . (!empty($search) ? "&search=$search" : "") . "'>Previous</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><a class='page-link'>Previous</a></li>";
                    }

                    for ($i = 1; $i <= $totalPages; $i++) {                       
                        if ($i == $page) {
                            echo "<li class='page-item active' aria-current='page'><a class='page-link disabled'>$i</a></li>"; 
                        } else {
                            echo "<li class='page-item'><a class='page-link' href='?page=$i&sort=$sort" . (!empty($search) ? "&search=$search" : "") . "'>$i</a></li>";
                        }
                    }

                    if ($page < $totalPages) {
                        echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "&sort=$sort" . (!empty($search) ? "&search=$search" : "") . "'>Next</a></li>";
                    } else {
                        echo "<li class='page-item disabled'><a class='page-link'>Next</a></li>";
                    }
                    echo "</ul>";
                echo "</div>";
            echo "</div>"; 
        }
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
            .search-container {
                margin-bottom: 30px;
                position: relative;
            }
            .search-box {
                max-width: 500px;
                margin: 0 auto;
            }
            #searchResults {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                z-index: 1000;
                background: white;
                border: 1px solid #ddd;
                border-radius: 0 0 5px 5px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                max-height: 300px;
                overflow-y: auto;
                display: none;
            }
            .search-result-item {
                padding: 10px 15px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
                transition: background 0.2s;
            }
            .search-result-item:hover {
                background: #f8f9fa;
            }
            .search-result-item:last-child {
                border-bottom: none;
            }
            .search-result-item .model {
                font-weight: bold;
                color: #333;
            }
            .search-result-item .category {
                font-size: 0.85rem;
                color: #666;
            }
            .search-highlight {
                background-color: #fffde7;
                font-weight: bold;
                padding: 0 2px;
            }
            .search-loading {
                padding: 10px;
                text-align: center;
                color: #666;
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
                
                <!-- Enhanced Search Box -->
                <div class="search-container">
                    <form method="get" action="" class="search-box" id="searchForm">
                        <div class="input-group mb-3" style="position: relative;">
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
                        </div>
                        <div id="searchResults"></div>
                    </form>
                </div>
                
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
                            <?php if(isset($_GET['page'])): ?>
                                <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
                            <?php endif; ?>
                            <?php if(isset($_GET['search'])): ?>
                                <input type="hidden" name="search" value="<?php echo $_GET['search']; ?>">
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="grid" style="margin-bottom: 3.5rem;">
                <?php
                    pagination();
                ?>
            </div>          
        </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            let typingTimer;
            const doneTypingInterval = 300; // 300ms delay after typing stops
            
            // Show live search results as user types
            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                const searchTerm = this.value.trim();
                
                if (searchTerm.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                // Show loading indicator
                searchResults.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
                searchResults.style.display = 'block';
                
                typingTimer = setTimeout(function() {
                    fetchSearchResults(searchTerm);
                }, doneTypingInterval);
            });
            
            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) {
                    searchResults.style.display = 'none';
                }
            });
            
            // Submit form when a result is clicked
            searchResults.addEventListener('click', function(e) {
                if (e.target.closest('.search-result-item')) {
                    const item = e.target.closest('.search-result-item');
                    searchInput.value = item.dataset.searchterm;
                    document.getElementById('searchForm').submit();
                }
            });
            
            function fetchSearchResults(searchTerm) {
                fetch(`search_suggestions.php?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let html = '';
                            data.forEach(item => {
                                // Highlight matching text
                                const highlightedModel = highlightText(item.Model, searchTerm);
                                const highlightedCategory = highlightText(item.CategoryType, searchTerm);
                                
                                html += `
                                    <div class="search-result-item" data-searchterm="${item.Model}">
                                        <div class="model">${highlightedModel}</div>
                                        <div class="category">${highlightedCategory}</div>
                                    </div>
                                `;
                            });
                            searchResults.innerHTML = html;
                        } else {
                            searchResults.innerHTML = '<div class="search-result-item">No matching products found</div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        searchResults.innerHTML = '<div class="search-result-item">Error loading results</div>';
                    });
            }
            
            function highlightText(text, searchTerm) {
                if (!text) return '';
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                return text.replace(regex, '<span class="search-highlight">$1</span>');
            }
        });
    </script>
    </body>
</html>
