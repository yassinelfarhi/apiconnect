<?php 
    require_once __DIR__.'/../vendor/autoload.php';
    $conn = new db();
    $conn->exec("SELECT * from vn_villas_temp where matched = 0 limit 1");
    $villasTemp = $conn->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <div class="container">
        <div class="to-match">
        <table>
            <TR>
            <th>ID</th>
            <th>NOM</th>
            <th>URL</th>
            <th>STATION</th>
            <th>ROOMS</th>
            <th>MATCHED</th>  
            </TR>
        <?php 
            foreach ($villasTemp as $key => $villaTemp) {
              echo "<tr><td>" .$villaTemp["id"]. "</td>" ; 
              echo "<td>" .$villaTemp["nom"]. "</td>" ; 
              echo "<td>" .$villaTemp["url"]. "</td>" ; 
              echo "<td>" .$villaTemp["station"]. "</td>" ; 
              echo "<td>" .$villaTemp["rooms"]. "</td>" ; 
              echo "<td>" .$villaTemp["matched"]. "</td></tr>" ; 
            }

         ?>   
        </table>
        </div>

        <div class="results">
            <table>
                <TR>
                <th>ID</th>
                <th>NOM</th>
                <th>URL</th>
                <th>STATION</th>
                <th>ROOMS</th>
                <th>MATCHED</th>  
                </TR>

                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>
  
    
</body>
</html>