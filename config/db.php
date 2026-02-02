<?php
     $server = "localhost";
     $database = "event_management"; 
     $username =  "root";
     $password =  "";

     try {
          $pdo = new PDO (
               "mysql:host=$server; dbname=$database",
               $username,
               $password
          );
          $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
     }catch(PDOException $e){
          echo ("Unable to connected to database" . $e->getMessage());
     }
?> 
