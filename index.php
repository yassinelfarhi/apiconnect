<?php 
    require_once __DIR__.'/vendor/autoload.php';
    $conn = new db();
    $conn->exec("SELECT * from vn_villas_temp where matched = 0 limit 1");
    $villasTemp = $conn->fetchAll();
    




    $nameWords = explode(" ",$villasTemp[0]["nom"]);
    $nameWordsFiltred = array_filter($nameWords,function($word) {
             return (strlen($word) > 2);
    });



    $regex = '"';

    $cnt = count($nameWordsFiltred);

    foreach($nameWordsFiltred as $key => $nameWord) {
        if (++$key < $cnt) {
            $regex .= $nameWord. "|";
        } else {
            $regex .= $nameWord;
        }
    }

    $regex .= '"';
   
    $query = 'SELECT * from vn_villas where villa_private_name REGEXP ' .$regex;



    $conn->exec($query);
    $villasMatch = $conn->fetchAll();
    // if(empty($villasMatch)) { header('refresh;url=index.php'); }
    if (isset($_POST["villaMatched"])) {

        $villaMatched = strip_tags($_POST["villaMatched"]);
        $vnQuery = 'UPDATE vn_villas SET api_source_id = 2 ,api_villa_id =' .$villasTemp[0]["id"]. ' where villa_id=' .$villaMatched ;
        $conn->exec($vnQuery);
        $tmpQuery = 'UPDATE vn_villas_temp SET matched = 1 where id=' .$villasTemp[0]["id"] ;
        $conn->exec($tmpQuery);

        if ($conn->affectedRows() > 0) {
            session_start();
            $_SESSION["villaAPI"] = $villasTemp[0]["nom"];
            var_dump($_SESSION["villaAPI"]);
        }
      // $_SESSION['lastMatch']["localApi"] = $villasMatch[][]
        
    }

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

    <div class="container position-relative">
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
            </TR>
            </thead>
       

            
        <?php 
            foreach ($villasTemp as $key => $villaTemp) {
              echo "<tr><td>" .$villaTemp["id"]. "</td>" ; 
              echo "<td>" .$villaTemp["nom"]. "</td>" ; 
              echo "<td><a target='blank' href='" .$villaTemp["url"]. "'>lien site</a></td>" ; 
              echo "<td>" .$villaTemp["station"]. "</td>" ; 
              echo "<td>" .$villaTemp["rooms"]. "</td>" ; 
              echo "<td>" .$villaTemp["matched"]. "</td></tr>" ; 
            }

         ?>   
        </table>
        </div>

        <div class="results start-50">
            <form action="#" method="post">
            <table class="table table-striped text-center">
                <thead>

                   <tr>
                <th>NOM</th>
                <th>address</th>
                <th>Chambres</th>
                <th>Action</th>
                 </tr>
                </thead>
             
            <?php 
                
                    foreach($villasMatch as $key => $villaMatch) {
                        echo "<tr><td>" .$villaMatch["villa_private_name"]. "</td>";
                        echo "<td>" .$villaMatch["villa_address"]. "</td>";
                        echo "<td>" .$villaMatch["villa_bedrooms"]. "</td>";
                        echo "<td><input class='match_action' type='radio' name='villaMatched' value=" .$villaMatch['villa_id']. "></td></tr>";
                    }
           
                
           
             ?>
            </table>
            </form>
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
  
    <script>
        $(document).ready(function(){
            $(".match_action").click(function(e){
                $(this).parent().append("<button type='submit' class='btn btn-primary'>match</button>");
            });
        });
    </script>
</body>
</html>