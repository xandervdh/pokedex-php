<?php
declare(strict_types=1);

include 'class.php';

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $pokemon = $_GET['id'];
} else {
    $pokemon = 1;
}

function getData($url)
{
    $get = file_get_contents($url);
    return json_decode($get, true);
}

$evoData = array();
$url = 'https://pokeapi.co/api/v2/pokemon/' . $pokemon;
$data = getData($url);
$pageUrl = 'https://pokeapi.co/api/v2/pokemon?offset=0&limit=20';
$pageData = getData($pageUrl);
$prevPage = null;
$nextPage = null;
if (isset($pageData['previous'])){
    $prevPage = $pageData['previous'];
}
if (isset($pageData['next'])){
    $nextPage = $pageData['next'];
}
$pokemonPage = getPageUrls($pageData);
$pageClass = getPageClass($pokemonPage);
$evoChain = getEvolutions($data);
$singlePokemonClass = fillClass($data);
$evolutionClass = fillEvolutionClass($evoChain);
$singlePokColorOne = getColor($singlePokemonClass->typeOne);
$singlePokColorTwo = getColor($singlePokemonClass->typeTwo);

function getPageUrls($data){
    $array = array();
    for ($i = 0; $i < count($data['results']); $i++){
        array_push($array, $data['results'][$i]);
    }
    return $array;
}

function getPageClass($data){
    $class = new page();
    for ($i = 0; $i < count($data); $i++){
        $pokData = getData($data[$i]['url']);
        array_push($class->sprite, $pokData['sprites']['front_default']);
        array_push($class->name, $pokData['name']);
        array_push($class->id, $pokData['id']);
        array_push($class->hashId, getId($pokData));
    }
    return $class;
}

function getId($data)
{
    if ($data['id'] < 10) {
        return "#00" . $data['id'];
    } else if ($data['id'] < 100) {
        return "#0" . $data['id'];
    } else {
        return "#" . $data['id'];
    }
}

function fillClass($data){
    $class = new singlePokemon();
    $class->id = getId($data);
    $class->name = $data['name'];
    $class->sprite = $data['sprites']['front_default'];
    $class->height = $data['height'];
    $class->weight = $data['weight'];
    $class->typeOne = $data['types'][0]['type']['name'];
    if (isset($data['types'][1]['type']['name'])){
        $class->typeTwo = $data['types'][1]['type']['name'];
    } else {
        $class->typeTwo = $class->typeOne;
    }

    $max = count($data['moves']);
    if ($max > 4) {
        $maxMoves = 4;
    } else {
        $maxMoves = $max;
    }
    for ($i = 0; $i < $maxMoves; $i++) {
        if ($max > 4) {
            $rand = floor(rand(0, $max - 1) - 0);
            array_push($class->moves, $data['moves'][$rand]['move']['name']);
        } else {
            array_push($class->moves, $data['moves'][$i]['move']['name']);
        }
    }
    return $class;
}

function fillEvolutionClass($data){
    $class = new evolutions();
    for ($i = 0; $i < count($data); $i++) {
        $url = 'https://pokeapi.co/api/v2/pokemon/' . $data[$i];
        $get = getData($url);
        array_push($class->sprite, $get['sprites']['front_default']);
        array_push($class->id, $get['id']);
        array_push($class->name, $get['name']);
    }
    return $class;
}

function getEvolutions($data)
{
    $speciesUrl = $data['species']['url'];
    $species = getData($speciesUrl);
    $evolutionUrl = $species['evolution_chain']['url'];
    $evolutionChain = getData($evolutionUrl);
    $GLOBALS['evoData'] = $evolutionChain['chain'];
    $evolveChain = array();
    $evoData = $evolutionChain['chain'];

    if (count($evoData['evolves_to']) > 1) {
        for ($i = 0; $i < count($evoData['evolves_to']); $i++) {
            array_push($evolveChain, $evoData['evolves_to'][$i]['species']['name']);
        }
    } else {
        do {
            array_push($evolveChain, $evoData['species']['name']);
            if ($evoData['evolves_to']) {
                $evoData = $evoData['evolves_to'][0];
            } else {
                $evoData = null;
            }
        } while (!!$evoData);
    }
    return $evolveChain;
}

function getColor($type)
{
    $color = "";
    switch ($type) {
        case "bug":
            $color = "rgb(168, 184, 32)";
            break;
        case "dark":
            $color = "rgb(112, 88, 72)";
            break;
        case "dragon":
            $color = "rgb(112, 56, 248)";
            break;
        case "electric":
            $color = "rgb(248, 208, 48)";
            break;
        case "fairy":
            $color = "rgb(238, 153, 172)";
            break;
        case "fighting":
            $color = "rgb(192, 48, 40)";
            break;
        case "fire":
            $color = "rgb(240, 128, 48)";
            break;
        case "flying":
            $color = "rgb(168, 144, 240)";
            break;
        case "ghost":
            $color = "rgb(112, 88, 152)";
            break;
        case "grass":
            $color = "rgb(120, 200, 80)";
            break;
        case "ground":
            $color = "rgb(224, 192, 104)";
            break;
        case "ice":
            $color = "rgb(152, 216, 216)";
            break;
        case "normal":
            $color = "rgb(168, 168, 120)";
            break;
        case "poison":
            $color = "rgb(160, 64, 160)";
            break;
        case "psychic":
            $color = "rgb(248, 88, 136)";
            break;
        case "rock":
            $color = "rgb(184, 160, 56)";
            break;
        case "steel":
            $color = "rgb(184, 184, 208)";
            break;
        case "water":
            $color = "rgb(104, 144, 240)";
            break;
    }
    return $color;
}

?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css" type="text/css">
    <title>Pokedex</title>
</head>
<body>

<div id="input">
    <nav class="navbar navbar-expand-lg navbar-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01"
                aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
            <form method="get">
            <ul class="nav">
                <li class="nav-item">
                    <input type="text" name="id" id="id" placeholder="Pokemon name or ID">
                </li>
                <li class="nav-item">
                    <button type="submit" class="nav-link btn btn-primary" >Search!</button>
                </li>
            </ul>
            </form>
        </div>
    </nav>
</div>

<div id="wrapper">
    <div id="pokemon" style="background-image: linear-gradient(to right, <?php echo $singlePokColorOne . ', ' . $singlePokColorTwo; ?>)">
        <strong><?php echo $singlePokemonClass->name;?></strong><br>
        <em><?php echo $singlePokemonClass->id; ?></em><br>
        <strong>height:</strong><em> <?php echo $singlePokemonClass->height / 10 ?>m</em><br>
        <strong>Weight:</strong><em> <?php echo $singlePokemonClass->weight / 10 ?>kg</em><br>
        <img src="<?php echo $singlePokemonClass->sprite; ?>" alt=""><br>
        <strong><?php
            if ($singlePokemonClass->typeOne !== $singlePokemonClass->typeTwo){
                echo 'Type One:';
            } else {
                echo 'Type';
            }
            ?></strong><em> <?php echo $singlePokemonClass->typeOne ?></em><br>
        <?php
        if ($singlePokemonClass->typeOne !== $singlePokemonClass->typeTwo) {
            echo '<strong>Type Two:</strong><em>' . $singlePokemonClass->typeTwo . '</em><br>';
        }
        ?>
        <strong>Moves:</strong><br>
        <?php
        $i = 0;
        while ($i < count($singlePokemonClass->moves)) {
            echo '<em>' . $singlePokemonClass->moves[$i] . '</em><br>';
            $i++;
        }
        ?>
    </div>
    <div id="evolutions">
        <?php
        $i = 0;
        while ($i < count($evolutionClass->sprite)) {
            echo '<a href="?id=' . $evolutionClass->id[$i] . '"><img class="sprite" src="' . $evolutionClass->sprite[$i] . '" alt=""><strong class="evoName">' . $evolutionClass->name[$i] . '</strong></a>';
            if (count($evoData['evolves_to']) <= 1) {
                if ($i < count($evolutionClass->sprite) - 1) {
                    echo '<img src="images/arrow.png" alt="arrow" class="arrow">';
                }
            }

            $i++;
        }
        ?>
    </div>
    <div id="pokePage" class="row">
        <?php
        for ($i = 0; $i < count($pageClass->id); $i++){
            echo '<div class="col-3 pagepok">';
            echo '<a href="?id=' . $pageClass->id[$i] . '">';
            echo '<strong>' . $pageClass->name[$i] . '</strong><br>';
            echo '<em>' . $pageClass->hashId[$i] . '</em><br>';
            echo '<img src="' . $pageClass->sprite[$i] . '" alt="sprite of ' . $pageClass->name[$i] . '">';
            echo '</a></div><br>';
        }
        ?>
        <div id="navButtons">
            <a href="?page=0" class="previous btn btn-primary">Previous</a>
            <a href="?page=1" class="next btn btn-primary">Next</a>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
</body>
</html>