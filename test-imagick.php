<?php
if (extension_loaded('imagick')) {
    $im = new Imagick();
    echo "Imagick OK<br>";
    echo "Formatos soportados:<br>";
    $formats = $im->queryFormats();
    echo in_array('SVG', $formats) ? "✓ SVG soportado" : "✗ SVG NO soportado";
    echo "<br>";
    echo in_array('MSVG', $formats) ? "✓ MSVG soportado" : "✗ MSVG NO soportado";
} else {
    echo "Imagick NO está instalado";
}