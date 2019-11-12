<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $front = Context::getInstance()->getFront();
    $request = $front->getRequest();
    $layout = $front->getLayout();
    ?>
    <meta charset="UTF-8">
    <title>web tin tuc</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/public/css/main.css?v=<?php echo time() ?>">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css"/>
</head>