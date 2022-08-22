<?php 
    require_once __DIR__.'/vendor/autoload.php';
    $conn = new db();
    $affected = 0;
    if (isset($_GET["localId"]) && isset($_GET["apiId"])) {

        $vnQuery = 'UPDATE vn_villas SET api_source_id = 2 ,api_villa_id =' .$_GET["apiId"]. ' where villa_id=' .$_GET["localId"] ;
        $conn->exec($vnQuery);
        $tmpQuery = 'UPDATE vn_villas_temp SET matched = 1 where id=' .$_GET["apiId"] ;
        $conn->exec($tmpQuery);

        $affected = $conn->affectedRows();

        
    }

    if (isset($_GET["not_found"])) {
        $not_found = $_GET["not_found"];
        $query = "UPDATE vn_villas_temp set not_found = 1 where id=" .$not_found;
        $conn->exec($query);
        $affected = $conn->affectedRows();


    }

    if($affected > 0) { header("location:index.php");}

 

    $conn->exec("SELECT * from vn_villas_temp where matched = 0 and not_found = 0 limit 1");
    $villaTemp = $conn->getAssoc();


    $stopWords = json_decode(file_get_contents(__DIR__."/public/stopwords.json"),true);
    // $stopWords = ["les","des"];

    $nameWords = explode(" ",$villaTemp["nom"]);
    $nameWordsFiltred = array_filter($nameWords,function($word) use ($stopWords) {
             return (strlen($word) > 2 and !in_array(strtolower($word),$stopWords));
    });




    $regex = implode('|',$nameWordsFiltred);
   
    $query = 'SELECT v.villa_id, v.villa_private_name, v.villa_bedrooms, zt.zone_name 
              from vn_villas as v 
              join vn_zones_trad as zt 
              on  v.zone_id = zt.zone_id and zt.langue_id = 2 
              where villa_private_name REGEXP "' .$regex. '" and api_villa_id is NULL';


    //var_dump($query);exit();
    $conn->exec($query);
    if($conn->numRows() == 0){ header('location:index.php?not_found=' .$villaTemp['id']); }

    $villasMatch = $conn->fetchAll();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
     <style>
        tr,input{
            cursor: pointer;
        }
        .results tr:hover {
            opacity: 0.5;
        }
        .match_action button {
            margin-inline: 10px !important;
            margin-inline: 10px !important;
        }
        
        span{
            display: none;
        }
     </style>   
</head>
<body>

    <div class="container-fluid position-relative">
        <div class="to-match">
        <table class="table table-striped text-center">
            <thead>
                 <TR>
            <th>ID</th>
            <th>NOM</th>
            <th>URL</th>
            <th>STATION</th>
            <th>ROOMS</th>
            <th>MATCHED</th> 
            <th>NOT FOUND </th> 
            </TR>
            </thead>
       

            
        <?php 
           
              echo "<tr><td>" .$villaTemp["id"]. "</td>" ; 
              echo "<td>" .$villaTemp["nom"]. "</td>" ; 
              echo "<td><a target='blank' href='" .$villaTemp["url"]. "'>lien site</a></td>" ; 
              echo "<td>" .$villaTemp["station"]. "</td>" ; 
              echo "<td>" .$villaTemp["rooms"]. "</td>" ; 
              echo "<td>" .$villaTemp["matched"]. "</td>" ; 
              echo "<td><a href='index.php?not_found=" .$villaTemp["id"]. "'>not found</a></td></tr>" ; 
         

         ?>   
        </table>
        </div>

        <div class="results start-50">
            
            <table class="table table-striped text-center">
                <thead>

                   <tr>
                    <th>ID</th>
                <th>NOM</th>
                <th>address</th>
                <th>Chambres</th>
                <th>Action</th>
                 </tr>
                </thead>
             
            <?php 
                
                    foreach($villasMatch as $key => $villaMatch) {
                        echo "<tr>";
                        echo "<td>" .$villaMatch['villa_id']. "</td>";
                        echo "<td>" .$villaMatch["villa_private_name"]. "</td>";
                        echo "<td>" .$villaMatch["zone_name"]. "</td>";
                        echo "<td>" .$villaMatch["villa_bedrooms"]. "</td>";
                        echo "<td><a href='index.php?localId=" .$villaMatch['villa_id']. "&apiId=" .$villaTemp['id']. "'>match</a></td></tr>";
                    }
           
                
           
             ?>
            </table>
          
        </div>
        <div class="alerts">
            <?php
                 if (empty($villasMatch)) {
                             echo "<div class=' alert alert-primary text-center' role='alert'>
                                    aucun élément à matcher pour ce villa</div>";
                }

                if (!empty($_SESSION["lastMatched"])) {
                    echo "<div class=' alert alert-primary text-center' role='alert'>
                                    un élément à était matché pour ce villa</div>";
                }
            ?>
        </div>
    </div>
  

</body>
</html>