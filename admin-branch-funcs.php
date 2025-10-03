<?php
include 'setup.php';
    function displayBranches() {
        $link = connect();
        $sql = "SELECT BranchName, BranchLocation, ContactNo FROM BranchMaster";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td class='align-middle'>" . htmlspecialchars($row['BranchName']) . "</td>
                    <td class='align-middle'>" . htmlspecialchars($row['BranchLocation']) . "</td>
                    <td class='align-middle'>" . htmlspecialchars($row['ContactNo']) . "</td>
                    <td class='align-middle'>
                        <form method='post'>
                            <input type='hidden' name='branchCode' value='" . htmlspecialchars($row['BranchCode']) . "'>
                            <input type='hidden' name='branchName' value='" . htmlspecialchars($row['BranchName']) . "'>
                            <input type='hidden' name='branchLocation' value='" . htmlspecialchars($row['BranchLocation']) . "'>
                            <input type='hidden' name='contactNo' value='" . htmlspecialchars($row['ContactNo']) . "'>
                            <button type='submit' class='btn btn-success mt-2' name='editBranchBtn' style='font-size:18px'><i class='fa-solid fa-pen-to-square'></i></button>
                        </form>
                    </td>
                    <td class='align-middle'>
                        <form method='post'>
                            <input type='hidden' name='branchCode' value='" . htmlspecialchars($row['BranchCode']) . "'>
                            <input type='hidden' name='branchName' value='" . htmlspecialchars($row['BranchName']) . "'>
                            <input type='hidden' name='branchLocation' value='" . htmlspecialchars($row['BranchLocation']) . "'>
                            <input type='hidden' name='contactNo' value='" . htmlspecialchars($row['ContactNo']) . "'>
                            <button type='submit' class='btn btn-danger mt-2' name='deletBranchBtn' value='deletBranchBtn' style='font-size:18px'><i class='fa-solid fa-trash'></i></button>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No branches found</td></tr>";
        }
    }
?>