<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (isset($_GET['id'])){
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
$idNumber = getId($data);
$randMoves = getMoves($data);
$evoChain = getEvolutions($data);
$evoSprites = getEvolutionSprites($evoChain);


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

function getMoves($data)
{
    $moves = array();
    $max = count($data['moves']);
    if ($max > 4) {
        $maxMoves = 4;
    } else {
        $maxMoves = $max;
    }
    for ($i = 0; $i < $maxMoves; $i++) {
        if ($max > 4) {
            $rand = floor(rand(0, $max-1) - 0);
            array_push($moves, $data['moves'][$rand]['move']['name']);
        } else {
            array_push($moves, $data['moves'][$i]['move']['name']);
        }
    }

    return $moves;
}

function getEvolutions($data){
    $speciesUrl = $data['species']['url'];
    $species = getData($speciesUrl);
    $evolutionUrl = $species['evolution_chain']['url'];
    $evolutionChain = getData($evolutionUrl);
    $GLOBALS['evoData'] = $evolutionChain['chain'];
    $evolveChain = array();
    $evoData = $evolutionChain['chain'];

    if (count($evoData['evolves_to']) > 1){
        for ($i = 0; $i < count($evoData['evolves_to']); $i++){
            array_push($evolveChain, $evoData['evolves_to'][$i]['species']['name']);
        }
    } else {
        do {
            array_push($evolveChain, $evoData['species']['name']);
            if ($evoData['evolves_to']){
                $evoData = $evoData['evolves_to'][0];
            } else {
                $evoData = null;
            }
        } while (!!$evoData);
    }
    return $evolveChain;
}

function getEvolutionSprites($data){
    $evoData = array();
    $sprites = array();
    $id = array();

    for ($i = 0; $i < count($data); $i++){
        $url = 'https://pokeapi.co/api/v2/pokemon/' . $data[$i];
        $get = getData($url);
        array_push($sprites, $get['sprites']['front_default']);
        array_push($id, $get['id']);
    }
    array_push($evoData, $sprites, $id);
    return $evoData;
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
    <input type="submit">
</form>
<div>
    <strong><?php echo $data['name']; ?></strong><br>
    <em><?php echo $idNumber ?></em>
    <img src="<?php echo $data['sprites']['front_default'] ?>" alt=""><br>
    <?php
    $i = 0;
    while ($i < count($randMoves)) {
        echo '<strong>' . $randMoves[$i] . '</strong><br>';
        $i++;
    }
    ?>
    <br>
    <?php
    $i = 0;
    while ($i < count($evoSprites[0])) {
        echo '<a href="?id=' . $evoSprites[1][$i] . '"><img src="' . $evoSprites[0][$i] . '" alt=""></a>';
        if (count($evoData['evolves_to']) <= 1){
            if ($i < count($evoSprites[0]) -1){
                echo '<img src="images/arrow.png" alt="arrow" class="arrow">';
            }
        }

        $i++;
    }
    ?>
</div>

</body>
</html>