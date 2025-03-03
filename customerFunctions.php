<?php
    //Connection
    function connect() 
        {
            $db_host = 'localhost';
            $db_username = 'root';
            $db_password = '';
            $db_name = 'SantosOpticals';

            $conn = new mysqli($db_host, $db_username, $db_password, $db_name);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            return $conn;
            
        }
        
    //read all row from database table
    function customerData()

        {
            $customerData = "";
            $connection = connect();

            $sql = "SELECT * FROM customer";
            $result = $connection->query($sql);

            if(!$result) {
                die ("Invalid query: " . $connection->error);
            }

            // read data of each row
            while ($row = $result->fetch_assoc()){
                $customerData.=
                "<tr>
                    <td>$row[CustomerID]</td>
                    <td>$row[CustomerName]</td>
                    <td>$row[CustomerAddress]</td>
                    <td>$row[CustomerContact]</td>
                    <td>
                        <a class='btn btn-primary btn-sm' href=''>Edit</a>
                        <a class='btn btn-danger btn-sm' href=''>Delete</a>
                    </td>
                </tr>";
            }
            return $customerData;
        }
    

?>