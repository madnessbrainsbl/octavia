<?php 
include_once 'inc/functions.php';
include_once("parts/header.php"); 


?>

<div class="container-fluid">
  <div class="row">
    <div class="col-md-3">
      <?php include_once("parts/left-menu.php"); ?>
    </div>
    <div class="col-md-9">
      <?php      
      if(isset($_GET['page']) && $_GET['page'] == "hygro"){
        include_once("parts/hydro.php");
      }else if(isset($_GET['page']) && $_GET['page'] == "electric"){
        include_once("parts/electric.php");
      }else if(isset($_GET['page']) && $_GET['page'] == "settings"){
        include_once("parts/settings.php");
      }else if(isset($_GET['page']) && $_GET['page'] == "monitor"){
        include_once("parts/monitor.php");
      }else if(isset($_GET['page']) && $_GET['page'] == "miners"){
        include_once("parts/miners.php");
      }else if(!isset($_GET['page']) || $_GET['page'] == "contact"){
        include_once("parts/main.php");
      }else{
        include_once("parts/main.php");
      }      
      ?>
    </div>
  </div>
</div>


        <!--
        <h3>SetNomT:<span data-var="SetNomT"></span></h3>
        <h3>Power 1:<span data-var="Power-1"></span></h3>
        <h3>Power 2:<span data-var="Power-2"></span></h3>
        <h3>Power 3:<span data-var="Power-3"></span></h3>
        <h3>NomY:<span data-var="NomY"></span></h3>
        <h3>TermoSensor-1:<span data-var="TermoSensor-1"></span></h3>
        <h3>TermoSensor-2:<span data-var="TermoSensor-2"></span></h3>
        <h3>ServoDrive-%:<span data-var="ServoDrive-%"></span></h3>
        <input data-var-write="SetNomT" data-var="SetNomT">
        <input data-var-write="Power-1" data-var="Power-1">
        <input data-var-write="Power-2" data-var="Power-2">
        <input data-var-write="Power-3" data-var="Power-3">
        <input data-var-write="NomY" data-var="NomY">
        <input data-var-write="TermoSensor-1" data-var="TermoSensor-1">
        <input data-var-write="TermoSensor-2" data-var="TermoSensor-2">
        <input data-var-write="ServoDrive-%" data-var="ServoDrive-%">
        -->
<?php include_once("parts/footer.php"); ?>