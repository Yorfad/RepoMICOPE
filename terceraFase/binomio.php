<?php
function coeficienteBinomial($n, $k) {
    // Casos base de la recursión
    if ($k == 0 || $k == $n) {
        return 1;
    }
    // Paso recursivo
    return coeficienteBinomial($n - 1, $k - 1) + coeficienteBinomial($n - 1, $k);
}

$resultado_expansion = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["potencia"])) {
    $n = intval($_POST["potencia"]);
    
    if ($n >= 0) {
        $terminos = [];

        for ($k = 0; $k <= $n; $k++) {
            $coeficiente = coeficienteBinomial($n, $k);

            // Construir el término simbólico (ej: 3a^2b)
            $termino_actual = '';

            // 1. Añadir el coeficiente (si no es 1 o si es el término independiente)
            if ($coeficiente > 1 || ($n == 0)) {
                $termino_actual .= $coeficiente;
            }

            // 2. Añadir la parte de 'a'
            $potencia_a = $n - $k;
            if ($potencia_a > 0) {
                $termino_actual .= 'a';
                if ($potencia_a > 1) {
                    $termino_actual .= '<sup>' . $potencia_a . '</sup>';
                }
            }

            // 3. Añadir la parte de 'b'
            $potencia_b = $k;
            if ($potencia_b > 0) {
                $termino_actual .= 'b';
                if ($potencia_b > 1) {
                    $termino_actual .= '<sup>' . $potencia_b . '</sup>';
                }
            }
            
            // Si el término queda vacío (caso a^0b^0), es 1.
            if(empty($termino_actual) && $coeficiente == 1) {
                $termino_actual = '1';
            }

            $terminos[] = $termino_actual;
        }

        $resultado_expansion = implode(' + ', $terminos);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Potencia de Binomios</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 2em; background-color: #f4f4f9; }
        h1, h2 { color: #333; }
        form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        input, button { padding: 10px; font-size: 1em; }
        button { background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .resultado { margin-top: 20px; background: #e9ecef; padding: 15px; border-radius: 8px; font-size: 1.2em; }
    </style>
</head>
<body>
    <h1>Expansión de Binomios (a + b)<sup>n</sup></h1>
    <p>Este script utiliza un algoritmo recursivo para generar los coeficientes del Triángulo de Pascal y mostrar la expansión simbólica del binomio.</p>
    
    <form method="post" action="">
        <label for="potencia"><strong>Introduce la potencia (n):</strong></label><br><br>
        <input type="number" id="potencia" name="potencia" min="0" required value="<?= htmlspecialchars($_POST['potencia'] ?? '') ?>">
        <button type="submit">Calcular Expansión</button>
    </form>

    <?php if (!empty($resultado_expansion)): ?>
        <div class="resultado">
            <h2>Resultado para (a + b)<sup><?= htmlspecialchars($n) ?></sup>:</h2>
            <p><strong><?= $resultado_expansion ?></strong></p>
        </div>
    <?php endif; ?>
</body>
</html>
