<?php
//declare(strict_types = 1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pokemon = file_get_contents("https://pokeapi.co/api/v2/pokemon/" . $_GET['id']);
var_dump(json_decode($pokemon));
