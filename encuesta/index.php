<?php
$fichero = 'data/votos.txt';

// si llega post y post tiene voto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voto'])) {
    //le añado el \n para que salga cada  voto en una fila
    $voto = $_POST['voto'] . "\n";
    // se mete la respuesta en el fichero
    file_put_contents($fichero, $voto, FILE_APPEND);
}

// Calcular resultados
// si existe el fichero
$votos = file_exists($fichero) ? file($fichero) : [];
$si = 0; $no = 0;
foreach ($votos as $v) {
    if (trim($v) == 'SI') $si++;
    if (trim($v) == 'NO') $no++;
}
?>

<!DOCTYPE html>
<html>
<head><title>Encuesta Linares</title></head>
<body style="text-align:center; font-family: sans-serif;">
    <h1>¿Independizar Linares de Jaén?</h1>
    <p style="font-size:0.8em">Si dices que si, Ismael a lo mejor se pone triste</p>
    
    <h3 style="color:red;">Esta es la : <?php echo gethostname(); ?></h3>

    <form method="POST">
        <button name="voto" value="SI" style="font-size:20px; color:green;">SÍ</button>
        <button name="voto" value="NO" style="font-size:20px; color:red;">NO</button>
    </form>

    <h2>Resultados:</h2>
    <p>SÍ: <?php echo $si; ?> | NO: <?php echo $no; ?></p>
</body>
</html>