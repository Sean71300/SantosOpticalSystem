<?php
include 'setup.php';
    function displayBranches() {
        $link = connect();
        $sql = "SELECT BranchName, BranchLocation, ContactNo FROM BranchMaster";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['BranchName']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['BranchLocation']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ContactNo']) . "</td>";
                    echo "<td><a href='admin-edit-branch.php?branch=" . urlencode($row['BranchName']) . "' class='btn btn-primary btn-sm'>Edit</a></td>";
                    echo "<td><a href='admin-delete-branch.php?branch=" . urlencode($row['BranchName']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this branch?\")'>Delete</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No branches found</td></tr>";
        }
    }
?>