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
$evoChain = getEvolutions($data);
$singlepokemonClass = fillClass($data);
$evolutionClass = fillEvolutionClass($evoChain);
$singlePokColorOne = getColor($singlepokemonClass->typeOne);
$singlePokColorTwo = getColor($singlepokemonClass->typeTwo);

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
    <link rel="stylesheet" href="style.css" type="text/css">
    <title>Pokedex</title>
</head>
<body>

<form method="get">
    <label for="id">ID or Name: </label>
    <input type="text" name="id" id="id"><br>
    <button type="submit">Submit</button>
</form>
<div id="wrapper">
    <div id="pokemon" style="background-image: linear-gradient(to right, <?php echo $singlePokColorOne . ', ' . $singlePokColorTwo; ?>)">
        <strong><?php echo $singlepokemonClass->name;?></strong><br>
        <em><?php echo $singlepokemonClass->id; ?></em><br>
        <strong>height:</strong><em> <?php echo $singlepokemonClass->height / 10 ?>m</em><br>
        <strong>Weight:</strong><em> <?php echo $singlepokemonClass->weight / 10 ?>kg</em><br>
        <img src="<?php echo $singlepokemonClass->sprite; ?>" alt=""><br>
        <strong><?php
            if ($singlepokemonClass->typeOne !== $singlepokemonClass->typeTwo){
                echo 'Type One:';
            } else {
                echo 'Type';
            }
            ?></strong><em> <?php echo $singlepokemonClass->typeOne ?></em><br>
        <?php
        if ($singlepokemonClass->typeOne !== $singlepokemonClass->typeTwo) {
            echo '<strong>Type Two:</strong><em>' . $singlepokemonClass->typeTwo . '</em><br>';
        }
        ?>
        <strong>Moves:</strong><br>
        <?php
        $i = 0;
        while ($i < count($singlepokemonClass->moves)) {
            echo '<em>' . $singlepokemonClass->moves[$i] . '</em><br>';
            $i++;
        }
        ?>
    </div>
    <div id="evolutions">
        <?php
        $i = 0;
        while ($i < count($evolutionClass->sprite)) {
            echo '<a href="?id=' . $evolutionClass->id[$i] . '"><img src="' . $evolutionClass->sprite[$i] . '" alt=""></a><strong class="evoName">' . $evolutionClass->name[$i] . '</strong>';
            if (count($evoData['evolves_to']) <= 1) {
                if ($i < count($evolutionClass->sprite) - 1) {
                    echo '<img src="images/arrow.png" alt="arrow" class="arrow">';
                }
            }

            $i++;
        }
        ?>
    </div>
</div>
</body>
</html>