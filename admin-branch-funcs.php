<?php
include 'setup.php';

/**
 * Display all branches as table rows. Used by admin-branch.php
 */
function displayBranches() {
    $link = connect();
    $sql = "SELECT BranchCode, BranchName, BranchLocation, ContactNo FROM BranchMaster WHERE Status = 'Active' ORDER BY BranchName";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $code, $name, $location, $contact);
        while (mysqli_stmt_fetch($stmt)) {
            $codeEsc = htmlspecialchars($code);
            $nameEsc = htmlspecialchars($name);
            $locEsc = htmlspecialchars($location);
            $contactEsc = htmlspecialchars($contact);
            echo "<tr>".
                 "<td>{$nameEsc}</td>".
                 "<td>{$locEsc}</td>".
                 "<td>{$contactEsc}</td>".
                 "<td>".
                     "<form method='post' style='display:inline-block;margin:0;'>".
                         "<input type='hidden' name='branchCode' value='{$codeEsc}'>".
                         "<button type='submit' name='editBranchBtn' class='btn btn-sm btn-success action-btn' title='Edit branch' aria-label='Edit branch'>".
                             "<i class='fas fa-edit'></i>".
                         "</button>".
                     "</form>".
                 "</td>".
                 "<td>".
                     "<form method='post' style='display:inline-block;margin:0;'>".
                         "<input type='hidden' name='branchCode' value='{$codeEsc}'>".
                         "<button type='submit' name='deleteBranchBtn' class='btn btn-sm btn-danger action-btn' title='Delete branch' aria-label='Delete branch'>".
                             "<i class='fas fa-trash-alt'></i>".
                         "</button>".
                     "</form>".
                 "</td>".
                 "</tr>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
}

/**
 * Output add-branch modal HTML
 */
function addBranchModal() {
    echo '<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="addBranchModalLabel">Add Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<form method="post">'
                    .'<div class="mb-3">'
                        .'<label for="branchName" class="form-label">Branch Name</label>'
                        .'<input type="text" class="form-control" id="branchName" name="branchName" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="branchLocation" class="form-label">Branch Location</label>'
                        .'<input type="text" class="form-control" id="branchLocation" name="branchLocation" required placeholder="Start typing address...">'
                        .'<div class="form-text">Start typing to see address suggestions from Google Maps</div>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<div id="mapPreview" style="height: 200px; width: 100%; border: 1px solid #ddd; border-radius: 4px; display: none;"></div>'
                    .'</div>'
                    // Insert Google Maps Web Components demo snippet
                    .'<!-- Google Maps Web Components snippet -->'
                    .'<script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfPI5kUaCUugAlg9iU0I-fhkOrqKqRtUA&callback=console.debug&libraries=maps,marker&v=beta"></script>'
                    .'<link rel="stylesheet" href="./style.css"/>'
                    .'<gmp-map center="14.5995,120.9842" zoom="13" map-id="DEMO_MAP_ID" style="display:block;height:200px;border:1px solid #ddd;border-radius:4px;">'
                        .'<gmp-advanced-marker position="14.5995,120.9842" title="Selected location"></gmp-advanced-marker>'
                    .'</gmp-map>'
                    .'<input type="hidden" id="latitude" name="latitude">'
                    .'<input type="hidden" id="longitude" name="longitude">'
                    .'<input type="hidden" id="fullAddress" name="fullAddress">' // Hidden fields to store selected address details
                    .'<div class="mb-3">'
                        .'<label for="contactNo" class="form-label">Contact Number</label>'
                        .'<input type="text" class="form-control" id="contactNo" name="contactNo" inputmode="numeric" pattern="[0-9]*" maxlength="15">'
                        .'<div class="form-text">Numbers only.</div>'
                    .'</div>'
                    .'<div class="modal-footer">'
                        .'<button type="submit" class="btn btn-success w-25" name="addBranchBtn">Add Branch</button>'
                        .'<button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>'
                    .'</div>'
                .'</form>'
            .'</div>'
        .'</div>'
    .'</div>'
.'</div>';

    echo '
    <script>
        function initGoogleMapsAutocomplete() {
        const autocomplete = new google.maps.places.Autocomplete(
            document.getElementById("branchLocation"),
            {
                types: ["establishment", "geocode"],
                componentRestrictions: { country: "ph" } // Change to your country code
            }
        );

        // Initialize map
        const map = new google.maps.Map(document.getElementById("mapPreview"), {
            center: { lat: 14.5995, lng: 120.9842 }, // Default center (Manila)
            zoom: 13
        });

        const marker = new google.maps.Marker({
            map: map,
            anchorPoint: new google.maps.Point(0, -29)
        });

        // Listen for place selection
        autocomplete.addListener("place_changed", function() {
            const place = autocomplete.getPlace();
            
            if (!place.geometry) {
                alert("No details available for: " + place.name);
                return;
            }

            // Show map preview
            document.getElementById("mapPreview").style.display = "block";

            // Update map and marker
            map.setCenter(place.geometry.location);
            map.setZoom(17);
            
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            // Populate hidden fields
            document.getElementById("latitude").value = place.geometry.location.lat();
            document.getElementById("longitude").value = place.geometry.location.lng();
            document.getElementById("fullAddress").value = place.formatted_address;

            // Optional: You can also extract address components
            extractAddressComponents(place);
        });

        // Clear map when input is cleared
        document.getElementById("branchLocation").addEventListener("input", function() {
            if (this.value === "") {
                document.getElementById("mapPreview").style.display = "none";
                marker.setVisible(false);
            }
        });
    }

    function extractAddressComponents(place) {
        let addressComponents = {
            street: "",
            city: "",
            province: "",
            country: "",
            zipCode: ""
        };

        for (const component of place.address_components) {
            const componentType = component.types[0];
            
            switch (componentType) {
                case "street_number":
                    addressComponents.street = component.long_name + " ";
                    break;
                case "route":
                    addressComponents.street += component.long_name;
                    break;
                case "locality":
                    addressComponents.city = component.long_name;
                    break;
                case "administrative_area_level_1":
                    addressComponents.province = component.long_name;
                    break;
                case "country":
                    addressComponents.country = component.long_name;
                    break;
                case "postal_code":
                    addressComponents.zipCode = component.long_name;
                    break;
            }
        }

        console.log("Address Components:", addressComponents);
        // You can use these components as needed
    }

    // Initialize when modal is shown
    document.addEventListener("DOMContentLoaded", function() {
        const addBranchModal = document.getElementById("addBranchModal");
        if (addBranchModal) {
            addBranchModal.addEventListener("shown.bs.modal", function() {
                // Load Google Maps script if not already loaded
                if (!window.google) {
                    const script = document.createElement("script");
                    script.src = "https://maps.googleapis.com/maps/api/js?key=AIzaSyBfPI5kUaCUugAlg9iU0I-fhkOrqKqRtUA&libraries=places&callback=initGoogleMapsAutocomplete";
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                } else {
                    initGoogleMapsAutocomplete();
                }
            });
        }
    });
    </script>';
}

/**
 * Handle add branch POST. Uses PRG to redirect after insert.
 */
function addBranch() {
    $link = connect();
    $branchName = trim($_POST['branchName'] ?? '');
    $branchLocation = trim($_POST['branchLocation'] ?? '');
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $fullAddress = $_POST['fullAddress'];
    $contactNo = trim($_POST['contactNo'] ?? '');

    // Generate a new BranchCode using the helper in setup.php. If it fails, fallback to MAX(BranchCode)+1
    $branchCode = 0;
    if (function_exists('generate_BranchCode')) {
        $branchCode = (int)generate_BranchCode();
    }
    if (!$branchCode) {
        // fallback: compute next code from DB
        $tmp = mysqli_query($link, "SELECT IFNULL(MAX(BranchCode), 0) + 1 AS nextCode FROM BranchMaster");
        if ($tmp) {
            $r = mysqli_fetch_assoc($tmp);
            $branchCode = (int)($r['nextCode'] ?? 0);
        }
    }
    $status = 'Active';

    $sql = "INSERT INTO branches (BranchCode, BranchName, BranchLocation, latitude, longitude, full_address, ContactNo, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssdds', $branchCode, $branchName, $branchLocation, $contactNo, $latitude, $longitude, $fullAddress);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $ok = false;
    }

    // Log action if logger exists
    if ($ok && function_exists('log_action')) {
        $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
        if ($empId) { log_action($empId, $branchCode, 'branch', 3, "Added branch: {$branchName}"); }
    }

    mysqli_close($link);

    if ($ok) {
        header('Location: admin-branch.php?result=add_success');
        exit();
    } else {
        header('Location: admin-branch.php?result=add_error');
        exit();
    }
}

/**
 * Render edit modal populated with branch data. This function echoes HTML but does not redirect.
 */
function editBranch() {
    $link = connect();
    $branchCode = $_POST['branchCode'] ?? '';
    $sql = "SELECT BranchCode, BranchName, BranchLocation, ContactNo FROM BranchMaster WHERE BranchCode = ? LIMIT 1";
    $name = $location = $contact = '';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $branchCode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $code, $name, $location, $contact);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);

    $codeEsc = htmlspecialchars($code);
    $nameEsc = htmlspecialchars($name);
    $locEsc = htmlspecialchars($location);
    $contactEsc = htmlspecialchars($contact);

    echo '<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<form method="post">'
                    .'<input type="hidden" name="branchCode" value="' . $codeEsc . '">'
                    .'<div class="mb-3">'
                        .'<label for="branchNameEdit" class="form-label">Branch Name</label>'
                        .'<input type="text" class="form-control" id="branchNameEdit" name="branchName" value="' . $nameEsc . '" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="branchLocationEdit" class="form-label">Branch Location</label>'
                        .'<input type="text" class="form-control" id="branchLocationEdit" name="branchLocation" value="' . $locEsc . '" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="contactNoEdit" class="form-label">Contact Number</label>'
                        .'<input type="text" class="form-control" id="contactNoEdit" name="contactNo" value="' . $contactEsc . '" inputmode="numeric" pattern="[0-9]*" maxlength="15">'
                    .'</div>'
                    .'<div class="modal-footer">'
                        .'<button type="submit" class="btn btn-success w-25" name="saveBtn">Save</button>'
                        .'<button type="button" class="btn btn-danger w-25" data-bs-dismiss="modal">Cancel</button>'
                    .'</div>'
                .'</form>'
            .'</div>'
        .'</div>'
    .'</div>'
.'</div>';
}

/**
 * Handle edit confirm POST (update). Uses PRG redirect.
 */
function confirmEditBranch() {
    $link = connect();
    $branchCode = (int)($_POST['branchCode'] ?? 0);
    $branchName = trim($_POST['branchName'] ?? '');
    $branchLocation = trim($_POST['branchLocation'] ?? '');
    $contactNo = trim($_POST['contactNo'] ?? '');

    $sql = "UPDATE BranchMaster SET BranchName = ?, BranchLocation = ?, ContactNo = ? WHERE BranchCode = ?";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssi', $branchName, $branchLocation, $contactNo, $branchCode);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $ok = false;
    }

    if ($ok && function_exists('log_action')) {
        $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
        if ($empId) { log_action($empId, $branchCode, 'branch', 4, "Edited branch: {$branchName}"); }
    }

    mysqli_close($link);

    if ($ok) {
        header('Location: admin-branch.php?result=edit_success');
        exit();
    } else {
        header('Location: admin-branch.php?result=edit_error');
        exit();
    }
}

/**
 * Show a delete confirmation modal (renders form to POST confirmDeleteBtn)
 */
function deleteBranch() {
    $code = htmlspecialchars($_POST['branchCode'] ?? '');
    echo '<div class="modal fade" id="deleteBranchModal" tabindex="-1" aria-labelledby="deleteBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="deleteBranchModalLabel">Delete Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<p>Are you sure you want to delete this branch?</p>'
                .'<form method="post">'
                    .'<input type="hidden" name="branchCode" value="' . $code . '">'
                    .'<div class="modal-footer">'
                        .'<button type="submit" class="btn btn-danger w-25" name="confirmDeleteBtn">Delete</button>'
                        .'<button type="button" class="btn btn-secondary w-25" data-bs-dismiss="modal">Cancel</button>'
                    .'</div>'
                .'</form>'
            .'</div>'
        .'</div>'
    .'</div>'
.'</div>';
}

/**
 * Handle delete POST and redirect (PRG)
 */
function confirmDeleteBranch() {
    $link = connect();
    $branchCode = (int)($_POST['branchCode'] ?? 0);
    // Archive the branch first (so it appears in archives list)
    $archiveInserted = false;
    $archiveID = function_exists('generate_ArchiveID') ? generate_ArchiveID() : null;
    $empIdSess = isset($_SESSION['id']) ? $_SESSION['id'] : (isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : 0);
    if ($archiveID !== null) {
        $aSql = "INSERT INTO archives (ArchiveID, TargetID, EmployeeID, TargetType, ArchivedAt) VALUES (?, ?, ?, 'branch', NOW())";
        $aStmt = mysqli_prepare($link, $aSql);
        if ($aStmt) {
            mysqli_stmt_bind_param($aStmt, 'iii', $archiveID, $branchCode, $empIdSess);
            $archiveInserted = mysqli_stmt_execute($aStmt);
            mysqli_stmt_close($aStmt);
        }
    } else {
        $aSql = "INSERT INTO archives (TargetID, EmployeeID, TargetType, ArchivedAt) VALUES (?, ?, 'branch', NOW())";
        $aStmt = mysqli_prepare($link, $aSql);
        if ($aStmt) {
            mysqli_stmt_bind_param($aStmt, 'ii', $branchCode, $empIdSess);
            $archiveInserted = mysqli_stmt_execute($aStmt);
            mysqli_stmt_close($aStmt);
        }
    }

    // Soft-delete: mark branch as Inactive instead of removing the row
    $sql = "UPDATE BranchMaster SET Status = 'Inactive' WHERE BranchCode = ?";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $branchCode);
        $statusUpdated = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        $statusUpdated = false;
    }

    // Consider overall operation successful only if both archive inserted and status updated
    $ok = ($archiveInserted && $statusUpdated);

    if ($ok && function_exists('log_action')) {
        $empId = isset($_SESSION['employee_id']) ? $_SESSION['employee_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : 0);
        if ($empId) { log_action($empId, $branchCode, 'branch', 5, "Deleted branch: {$branchCode}"); }
    }

    mysqli_close($link);

    if ($ok) {
        header('Location: admin-branch.php?result=delete_success');
        exit();
    } else {
        header('Location: admin-branch.php?result=delete_error');
        exit();
    }
}

function displayBranch() {
    $link = connect();
    
    
}

?>