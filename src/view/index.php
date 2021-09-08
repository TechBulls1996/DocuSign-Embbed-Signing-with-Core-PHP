<!DOCTYPE html>
<html lang="en">
<head>
  <title>Docusign Integration Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container">
    
   <div class="card mt-2">
      <div class="card-header">
        LearnPHPOnline
      </div>
      <div class="card-body">
        <h5 class="card-title">Docusign </h5>
        <p class="card-text">Click the button below to connect your appication with docusign</p>
        <?php
        if (@$_SESSION['message'] =='success'){
        ?>
          <a href="index.php?page=sign" class="btn btn-primary">Click to sign document</a>
        <?php
        }
        else{ 
        ?>
          <a href="index.php?page=connect" class="btn btn-primary">Connect Docusign</a>
        <?php
         }
        ?>
        
      </div>
    </div>
  </div>
</body>
