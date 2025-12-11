<?php
$chistes = [
    "¿Qué es un terapeuta? - 1024 Gigapeutas.",
    "¿Qué es rojo y malo para los dientes? Un ladrillo.",
    "¿Por qué los cigarrillos son buenos para el medio ambiente? Matan gente.",
    "¿Por qué los programadores confunden Navidad y Halloween? - Porque 25 DEC = 31 OCT.",
    "El dentista al paciente: 'Esto le va a doler un poco'. 'Vale', contesta este. Y el dentista añade: 'Me estoy tirando a su mujer'.",
    "Hoy le he preguntado a mi teléfono: 'Siri, ¿por qué sigo soltero?'. Y ha activado la cámara frontal.",
    "¡Toc, toc! - ¿Quién es? - Java. - ¿Java qué? - Java a salir el error de null pointer..."
];
$chiste_random = $chistes[array_rand($chistes)];
?>

<!DOCTYPE html>
<html>
<body style="background-color: #f0f8ff; text-align: center; padding: 50px;">
    <h1>Chiste Informático del día:</h1>
    <h2 style="color: blue;"> <?php echo $chiste_random; ?> </h2>
    <p>Recarga para otro.</p>
</body>
</html>