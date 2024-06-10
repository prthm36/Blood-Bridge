<?php
    include "tabledb.php";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate and sanitize form inputs
        $dist = isset($_POST["district"]) ? htmlspecialchars($_POST["district"]) : "";
        $st = isset($_POST["state"]) ? htmlspecialchars($_POST["state"]) : "";
        $query = "SELECT * FROM hp_city WHERE District=\"$dist\" and State=\"$st\";";
        $result = mysqli_query($con,$query);
    }
    //require_once('php/table.php');
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>6-Column Table</title>
    <style>

    .textt{
        color: var(--oxford-blue-1);
        font-family: var(--ff-poppins);
        font-size: 3.4rem;
        font-weight: var(--fw-800);
        text-align: center;
        margin-top: 40px;

    }
         table {
            
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: var(--oxford-blue-1);
        }

        body {
    font-family: Arial, sans-serif;
    background-color: #f0f8ff;
    margin: 0;
    padding: 0;
    /*display: flex;*/
    /*justify-content: center;*/
    /*align-items: center;*/
    height: 100vh;
}

.table-container {
    margin-top:50px;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.blue-table {
    border-collapse: collapse;
    width: 80%;
    margin: 25px 0;
    font-size: 18px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    text-align: left;
}

.blue-table th,
.blue-table td {
    padding: 12px 15px;
}

.blue-table thead tr {
    background-color: #007BFF;
    color: #ffffff;
    text-align: center;
    font-weight: bold;
}

.blue-table tbody tr {
    border-bottom: 1px solid #dddddd;
}

.blue-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}
.blue-table tbody tr:nth-of-type(odd) {
    background-color: #ffffff;
}

.blue-table tbody tr:hover {
    background-color: #d1ecff;
}

.blue-table tbody tr:last-of-type {
    border-bottom: 2px solid #007BFF;
}


    </style>
</head>
<body>

<h2 class="textt" >NEAR BY BLOOD BANKS : </h2>
<div class="table-container">

    <table class="blue-table">
        <thead>
            <tr>
            <th>Blood Bank Name</th>
            <th>District</th>
            <th>Address</th>
            <th>Pin code</th>
            <th>Contact no.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
            <?php 
            while($row = mysqli_fetch_assoc($result))
            {
            ?>

            <td><?php echo $row['Blood Bank Name']  ?></td>
            <td><?php echo $row['District']  ?></td>
            <td><?php echo $row['Address']  ?></td>
            <td><?php echo $row['Pin Code']  ?></td>
            <td><?php echo $row['Contact no.']  ?></td>
           
           </tr>

            <?php
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
