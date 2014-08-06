<?php
// put this in directory called /allow/

header( 'X-Robots-Tag: noindex', true );
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <meta http-equiv="refresh" content="1;url=/">
    <style type="text/css">body{background:white} a{color:white;text-decoration:none}</style>
</head>
<body>
    <a href="meta-nofollow.php">+</a>
    <a rel="nofollow" href="../disallow/">-</a>
    <a href="../disallow/" data-robots-txt="Disallow">-</a>
    <a href="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js">-</a>
</body>
</html>
