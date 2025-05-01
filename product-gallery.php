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
        echo "<div class='row row-cols-2 row-cols-md-3 row-cols-lg-4 g-4' id='productGrid'>";
        
        if ($total > 0) {
            while($row = mysqli_fetch_assoc($result)) {
                // Create a searchable string for each product
                $searchableText = strtolower($row['Model'].' '.$row['CategoryType'].' '.$row['Material']);
                echo "<div class='col d-flex product-card' data-search='".htmlspecialchars($searchableText, ENT_QUOTES)."'>";
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
            echo "<div class='col-12 text-center py-5 no-results'>";
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
            }
            .search-box {
                max-width: 500px;
                margin: 0 auto;
            }
            /* Style for hidden products during live search */
            .product-card.hidden {
                display: none;
            }
            /* Style for the live search container */
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
            .search-highlight {
                background-color: yellow;
                font-weight: bold;
            }

            .search-highlight {
    background-color: yellow;
    font-weight: bold;
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
                
                <!-- Search Box with Live Preview -->
                <div class="search-container">
                    <form method="get" action="" class="search-box position-relative">
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
                        </div>
                        <div id="liveSearchResults"></div>
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
    const liveSearchResults = document.getElementById('liveSearchResults');
    const productCards = document.querySelectorAll('.product-card');
    
    // Function to perform live search
    function performLiveSearch() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        
        if (searchTerm.length === 0) {
            liveSearchResults.style.display = 'none';
            return;
        }
        
        const matches = [];
        
        // Search through all product cards
        productCards.forEach(card => {
            const cardTitle = card.querySelector('.card-title').textContent.toLowerCase();
            
            // Only match if the product name starts with the search term
            if (cardTitle.startsWith(searchTerm)) {
                matches.push({
                    element: card,
                    title: card.querySelector('.card-title').textContent
                });
            }
        });
        
        // Display results in the live search box
        if (matches.length > 0) {
            liveSearchResults.innerHTML = '';
            matches.slice(0, 5).forEach(match => {
                const resultItem = document.createElement('div');
                resultItem.className = 'live-search-item';
                resultItem.textContent = match.title;
                
                // Click handler for live search items
                resultItem.addEventListener('click', function() {
                    searchInput.value = match.title;
                    filterProducts();
                    liveSearchResults.style.display = 'none';
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
    
    // Function to filter products based on search term
    function filterProducts() {
        const searchTerm = searchInput.value.trim().toLowerCase();
        
        if (searchTerm.length === 0) {
            // Show all products if search is empty
            productCards.forEach(card => {
                card.classList.remove('hidden');
            });
            return;
        }
        
        let visibleCount = 0;
        
        // Filter products - now only matching from start of product name
        productCards.forEach(card => {
            const cardTitle = card.querySelector('.card-title').textContent.toLowerCase();
            
            if (cardTitle.startsWith(searchTerm)) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });
        
        // Show "no results" message if no products match
        const noResultsElement = document.querySelector('.no-results');
        if (noResultsElement) {
            noResultsElement.style.display = visibleCount > 0 ? 'none' : 'block';
        }
    }
    
    // Event listeners
    searchInput.addEventListener('input', function() {
        performLiveSearch();
        filterProducts();
    });
    
    // Hide live search when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !liveSearchResults.contains(e.target)) {
            liveSearchResults.style.display = 'none';
        }
    });
    
    // Keyboard navigation for live search
    searchInput.addEventListener('keydown', function(e) {
        const items = liveSearchResults.querySelectorAll('.live-search-item');
        let currentHighlight = liveSearchResults.querySelector('.live-search-item.highlight');
        
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
            filterProducts();
            liveSearchResults.style.display = 'none';
        }
    });
    
    // Initial filter if there's a search term in the URL
    if (searchInput.value) {
        filterProducts();
    }
});
    </script>
    </body>
</html>
