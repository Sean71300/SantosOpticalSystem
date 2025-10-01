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
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No branches found</td></tr>";
        }
    }
?>