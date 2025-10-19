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
        .'<div class="modal-dialog modal-dialog-centered modal-lg">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="addBranchModalLabel">Add Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<form method="post" id="addBranchForm">'
                    .'<div class="mb-3">'
                        .'<label for="branchName" class="form-label">Branch Name</label>'
                        .'<input type="text" class="form-control" id="branchName" name="branchName" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="branchLocation" class="form-label">Branch Location</label>'
                        .'<input type="text" class="form-control" id="branchLocation" name="branchLocation" required placeholder="Start typing to search locations...">'
                        .'<div class="form-text">Start typing to search and select from Google Maps suggestions.</div>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label class="form-label">Map Preview</label>'
                        .'<div id="mapPreview" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>'
                        .'<div class="form-text">Selected location will be marked on the map.</div>'
                    .'</div>'
                    .'<input type="hidden" id="selectedLat" name="selectedLat">'
                    .'<input type="hidden" id="selectedLng" name="selectedLng">'
                    .'<input type="hidden" id="formattedAddress" name="formattedAddress">'
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

    // Add the JavaScript for Google Maps
    echo '<script>
    function initGoogleMaps() {
        // Initialize the map
        const map = new google.maps.Map(document.getElementById("mapPreview"), {
            center: { lat: 14.5995, lng: 120.9842 }, // Default to Manila
            zoom: 12,
        });

        // Initialize the autocomplete
        const autocomplete = new google.maps.places.Autocomplete(
            document.getElementById("branchLocation"),
            {
                types: ["establishment", "geocode"],
                fields: ["geometry", "formatted_address", "name"],
            }
        );

        // Create a marker
        const marker = new google.maps.Marker({
            map: map,
            draggable: true,
        });

        // Listen for place selection
        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();

            if (!place.geometry) {
                console.log("No details available for input: " + place.name);
                return;
            }

            // Update map and marker
            map.setCenter(place.geometry.location);
            map.setZoom(16);
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            // Update hidden fields
            document.getElementById("selectedLat").value = place.geometry.location.lat();
            document.getElementById("selectedLng").value = place.geometry.location.lng();
            document.getElementById("formattedAddress").value = place.formatted_address;
        });

        // Allow marker dragging to adjust location
        marker.addListener("dragend", () => {
            const position = marker.getPosition();
            document.getElementById("selectedLat").value = position.lat();
            document.getElementById("selectedLng").value = position.lng();
            
            // Reverse geocode to get address
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === "OK" && results[0]) {
                    document.getElementById("branchLocation").value = results[0].formatted_address;
                    document.getElementById("formattedAddress").value = results[0].formatted_address;
                }
            });
        });

        // Also allow clicking on map to set location
        map.addListener("click", (event) => {
            const position = event.latLng;
            marker.setPosition(position);
            marker.setVisible(true);
            
            document.getElementById("selectedLat").value = position.lat();
            document.getElementById("selectedLng").value = position.lng();
            
            // Reverse geocode
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === "OK" && results[0]) {
                    document.getElementById("branchLocation").value = results[0].formatted_address;
                    document.getElementById("formattedAddress").value = results[0].formatted_address;
                }
            });
        });
    }

    // Load Google Maps API when modal is shown
    document.addEventListener("DOMContentLoaded", function() {
        const addBranchModal = document.getElementById("addBranchModal");
        addBranchModal.addEventListener("show.bs.modal", function() {
            loadGoogleMaps(initGoogleMaps);
        });
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
    $contactNo = trim($_POST['contactNo'] ?? '');
    $selectedLat = $_POST['selectedLat'] ?? '';
    $selectedLng = $_POST['selectedLng'] ?? '';
    $formattedAddress = $_POST['formattedAddress'] ?? '';

    // Use formatted address if available, otherwise use entered location   
    $finalLocation = !empty($formattedAddress) ? $formattedAddress : $branchLocation;

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

    $sql = "INSERT INTO BranchMaster (BranchCode, BranchName, BranchLocation, latitude, longitude, full_address, ContactNo, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'issssss', $branchCode, $branchName, $finalLocation, $selectedLat, $selectedLng, $contactNo, $status);
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
    $sql = "SELECT BranchCode, BranchName, BranchLocation, ContactNo, Latitude, Longitude FROM BranchMaster WHERE BranchCode = ? LIMIT 1";
    $name = $location = $contact = $lat = $lng = '';
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, 'i', $branchCode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $code, $name, $location, $contact, $lat, $lng);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);

    $codeEsc = htmlspecialchars($code);
    $nameEsc = htmlspecialchars($name);
    $locEsc = htmlspecialchars($location);
    $contactEsc = htmlspecialchars($contact);
    $latEsc = htmlspecialchars($lat);
    $lngEsc = htmlspecialchars($lng);

        echo '<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">'
        .'<div class="modal-dialog modal-dialog-centered modal-lg">'
        .'<div class="modal-content">'
            .'<div class="modal-header">'
                .'<h5 class="modal-title" id="editBranchModalLabel">Edit Branch</h5>'
                .'<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>'
            .'</div>'
            .'<div class="modal-body">'
                .'<form method="post" id="editBranchForm">'
                    .'<input type="hidden" name="branchCode" value="' . $codeEsc . '">'
                    .'<div class="mb-3">'
                        .'<label for="branchNameEdit" class="form-label">Branch Name</label>'
                        .'<input type="text" class="form-control" id="branchNameEdit" name="branchName" value="' . $nameEsc . '" required>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label for="branchLocationEdit" class="form-label">Branch Location</label>'
                        .'<input type="text" class="form-control" id="branchLocationEdit" name="branchLocation" value="' . $locEsc . '" required placeholder="Start typing to search locations...">'
                        .'<div class="form-text">Start typing to search and select from Google Maps suggestions.</div>'
                    .'</div>'
                    .'<div class="mb-3">'
                        .'<label class="form-label">Map Preview</label>'
                        .'<div id="editMapPreview" style="height: 300px; width: 100%; border: 1px solid #ddd; border-radius: 4px;"></div>'
                        .'<div class="form-text">Selected location will be marked on the map.</div>'
                    .'</div>'
                    .'<input type="hidden" id="editSelectedLat" name="selectedLat" value="' . $latEsc . '">'
                    .'<input type="hidden" id="editSelectedLng" name="selectedLng" value="' . $lngEsc . '">'
                    .'<input type="hidden" id="editFormattedAddress" name="formattedAddress" value="' . $locEsc . '">'
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

    // Add the JavaScript for Google Maps for edit modal
    echo '<script>
    function initEditGoogleMaps() {
        const initialLat = ' . (!empty($lat) ? $lat : '14.5995') . ';
        const initialLng = ' . (!empty($lng) ? $lng : '120.9842') . ';
        const initialLocation = "' . $locEsc . '";
        const hasExistingCoords = ' . (!empty($lat) && !empty($lng) ? 'true' : 'false') . ';

        // Initialize the map
        const map = new google.maps.Map(document.getElementById("editMapPreview"), {
            center: hasExistingCoords ? { lat: initialLat, lng: initialLng } : { lat: 14.5995, lng: 120.9842 },
            zoom: hasExistingCoords ? 16 : 12,
        });

        // Initialize the autocomplete
        const autocomplete = new google.maps.places.Autocomplete(
            document.getElementById("branchLocationEdit"),
            {
                types: ["establishment", "geocode"],
                fields: ["geometry", "formatted_address", "name"],
            }
        );

        // Create a marker
        const marker = new google.maps.Marker({
            map: map,
            draggable: true,
        });

        // Set initial marker if coordinates exist
        if (hasExistingCoords) {
            marker.setPosition({ lat: initialLat, lng: initialLng });
            marker.setVisible(true);
        }

        // Listen for place selection
        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();

            if (!place.geometry) {
                console.log("No details available for input: " + place.name);
                return;
            }

            // Update map and marker
            map.setCenter(place.geometry.location);
            map.setZoom(16);
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);

            // Update hidden fields
            document.getElementById("editSelectedLat").value = place.geometry.location.lat();
            document.getElementById("editSelectedLng").value = place.geometry.location.lng();
            document.getElementById("editFormattedAddress").value = place.formatted_address;
        });

        // Allow marker dragging to adjust location
        marker.addListener("dragend", () => {
            const position = marker.getPosition();
            document.getElementById("editSelectedLat").value = position.lat();
            document.getElementById("editSelectedLng").value = position.lng();
            
            // Reverse geocode to get address
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === "OK" && results[0]) {
                    document.getElementById("branchLocationEdit").value = results[0].formatted_address;
                    document.getElementById("editFormattedAddress").value = results[0].formatted_address;
                }
            });
        });

        // Also allow clicking on map to set location
        map.addListener("click", (event) => {
            const position = event.latLng;
            marker.setPosition(position);
            marker.setVisible(true);
            
            document.getElementById("editSelectedLat").value = position.lat();
            document.getElementById("editSelectedLng").value = position.lng();
            
            // Reverse geocode
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: position }, (results, status) => {
                if (status === "OK" && results[0]) {
                    document.getElementById("branchLocationEdit").value = results[0].formatted_address;
                    document.getElementById("editFormattedAddress").value = results[0].formatted_address;
                }
            });
        });
    }

    // Load Google Maps API when edit modal is shown
    document.addEventListener("DOMContentLoaded", function() {
        const editBranchModal = document.getElementById("editBranchModal");
        editBranchModal.addEventListener("show.bs.modal", function() {
            loadGoogleMaps(initEditGoogleMaps);
        });
    });
    </script>';
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
    $selectedLat = $_POST['selectedLat'] ?? '';
    $selectedLng = $_POST['selectedLng'] ?? '';
    $formattedAddress = $_POST['formattedAddress'] ?? '';

    // Use formatted address if available, otherwise use the location input
    $finalLocation = !empty($formattedAddress) ? $formattedAddress : $branchLocation;

    // Update the SQL to include coordinates
    $sql = "UPDATE BranchMaster SET BranchName = ?, BranchLocation = ?, ContactNo = ?, Latitude = ?, Longitude = ? WHERE BranchCode = ?";
    $stmt = mysqli_prepare($link, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'sssssi', $branchName, $finalLocation, $contactNo, $selectedLat, $selectedLng, $branchCode);
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
?>