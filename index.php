<?php
/**
 * Conversor de Colores HEX ↔ RGB ↔ HSL
 */
header('Content-Type: text/html; charset=utf-8');

// Parse Query Parameter
$colorQuery = $_GET['color'] ?? '';
if (empty($colorQuery) && isset($_GET['hex'])) {
    $colorQuery = '#' . ltrim(trim($_GET['hex']), '#');
}
if (empty($colorQuery)) {
    $colorQuery = '#3b82f6';
}

function hueToRgb($p, $q, $t) {
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
    return $p;
}

function rgbToAll($r, $g, $b) {
    $hex = sprintf("#%02x%02x%02x", $r, $g, $b);
    
    // HSL calculation
    $rf = $r / 255;
    $gf = $g / 255;
    $bf = $b / 255;
    
    $max = max($rf, $gf, $bf);
    $min = min($rf, $gf, $bf);
    
    $l = ($max + $min) / 2;
    
    if ($max === $min) {
        $h = 0;
        $s = 0;
    } else {
        $d = $max - $min;
        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);
        
        switch ($max) {
            case $rf:
                $h = ($gf - $bf) / $d + ($gf < $bf ? 6 : 0);
                break;
            case $gf:
                $h = ($bf - $rf) / $d + 2;
                break;
            case $bf:
                $h = ($rf - $gf) / $d + 4;
                break;
        }
        $h /= 6;
    }
    
    return [
        'hex' => $hex,
        'r' => $r, 'g' => $g, 'b' => $b,
        'h' => round($h * 360),
        's' => round($s * 100),
        'l' => round($l * 100)
    ];
}

function hslToAll($h, $s, $l) {
    $sh = $h / 360;
    $ss = $s / 100;
    $sl = $l / 100;
    
    if ($ss == 0) {
        $r = $g = $b = $sl; // achromatic
    } else {
        $q = $sl < 0.5 ? $sl * (1 + $ss) : $sl + $ss - $sl * $ss;
        $p = 2 * $sl - $q;
        
        $r = hueToRgb($p, $q, $sh + 1/3);
        $g = hueToRgb($p, $q, $sh);
        $b = hueToRgb($p, $q, $sh - 1/3);
    }
    
    $r = round($r * 255);
    $g = round($g * 255);
    $b = round($b * 255);
    
    $hex = sprintf("#%02x%02x%02x", $r, $g, $b);
    
    return [
        'hex' => $hex,
        'r' => $r, 'g' => $g, 'b' => $b,
        'h' => $h, 's' => $s, 'l' => $l
    ];
}

function parseColor($input) {
    $input = trim($input);
    $default = [
        'hex' => '#3b82f6',
        'r' => 59, 'g' => 130, 'b' => 246,
        'h' => 217, 's' => 91, 'l' => 60
    ];

    if (empty($input)) {
        return $default;
    }

    // HEX
    if (preg_match('/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i', $input, $m)) {
        $hex = $m[1];
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return rgbToAll($r, $g, $b);
    }

    // RGB
    if (preg_match('/(?:rgb\s*\()?\s*(\d{1,3})\s*[\s,]\s*(\d{1,3})\s*[\s,]\s*(\d{1,3})\s*\)?/i', $input, $m)) {
        $r = min(255, max(0, intval($m[1])));
        $g = min(255, max(0, intval($m[2])));
        $b = min(255, max(0, intval($m[3])));
        return rgbToAll($r, $g, $b);
    }

    // HSL
    if (preg_match('/(?:hsl\s*\()?\s*(\d{1,3})\s*[\s,]\s*(\d{1,3})%?\s*[\s,]\s*(\d{1,3})%?\s*\)?/i', $input, $m)) {
        $h = intval($m[1]) % 360;
        $s = min(100, max(0, intval($m[2])));
        $l = min(100, max(0, intval($m[3])));
        return hslToAll($h, $s, $l);
    }

    return $default;
}

$parsed = parseColor($colorQuery);

// Contrast helpers
function getLuminance($r, $g, $b) {
    $rs = $r / 255;
    $gs = $g / 255;
    $bs = $b / 255;
    
    $r_srgb = ($rs <= 0.03928) ? ($rs / 12.92) : pow(($rs + 0.055) / 1.055, 2.4);
    $g_srgb = ($gs <= 0.03928) ? ($gs / 12.92) : pow(($gs + 0.055) / 1.055, 2.4);
    $b_srgb = ($bs <= 0.03928) ? ($bs / 12.92) : pow(($bs + 0.055) / 1.055, 2.4);
    
    return 0.2126 * $r_srgb + 0.7152 * $g_srgb + 0.0722 * $b_srgb;
}

$lum = getLuminance($parsed['r'], $parsed['g'], $parsed['b']);
$ratioWhite = 1.05 / ($lum + 0.05);
$ratioBlack = ($lum + 0.05) / 0.05;

function getBadgeClass($ratio) {
    if ($ratio >= 7.0) return 'pass-aaa';
    if ($ratio >= 4.5) return 'pass-aa';
    if ($ratio >= 3.0) return 'pass-large';
    return 'fail';
}

function getBadgeText($ratio) {
    if ($ratio >= 7.0) return 'AAA ✅';
    if ($ratio >= 4.5) return 'AA ✅';
    if ($ratio >= 3.0) return 'AA Large ⚠️';
    return 'Fallo ❌';
}

$presetColors = [
    '#3b82f6', '#10b981', '#ef4444', '#f59e0b', '#8b5cf6',
    '#ec4899', '#06b6d4', '#14b8a6', '#84cc16', '#f97316',
    '#6b7280', '#ffffff', '#000000'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Conversor de Colores HEX ↔ RGB ↔ HSL | ConfiguroWeb</title>
<meta name="description" content="Convierte colores entre HEX, RGB y HSL online gratis. Conoce el nivel de contraste para accesibilidad WCAG. Herramienta premium de ConfiguroWeb.">
<meta name="keywords" content="conversor colores, hex a rgb, rgb a hex, hsl, paleta colores, contraste css, wcag">
<meta property="og:type" content="website">
<meta property="og:title" content="Conversor de Colores HEX RGB HSL Online">
<meta property="og:description" content="Convierte colores entre HEX, RGB y HSL con vista previa en vivo y validación WCAG.">
<link rel="canonical" href="https://demoscweb.com/github/php-conversor-colores-hex-rgb/">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Conversor de Colores HEX RGB HSL","applicationCategory":"DesignApplication","operatingSystem":"Any","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"ConfiguroWeb","url":"https://configuroweb.com"}}
</script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>🎨 Conversor de Colores HEX ↔ RGB ↔ HSL</h1>
  <p class="subtitle">Conversión instantánea bidireccional y análisis de contraste WCAG.</p>
</header>
<main>
  <div class="grid-container">
    
    <!-- COLUMNA IZQUIERDA: Vista Previa y Contraste -->
    <div class="panel-preview">
      <div class="color-box" id="preview" style="background-color: <?php echo htmlspecialchars($parsed['hex']); ?>;"></div>
      
      <div class="contrast-overlay">
        <!-- Texto Blanco -->
        <div class="contrast-box white-text">
          <p>Texto Blanco</p>
          <div class="contrast-val" id="contrast-white"><?php echo number_format($ratioWhite, 2); ?>:1</div>
          <span class="badge <?php echo getBadgeClass($ratioWhite); ?>" id="badge-white"><?php echo getBadgeText($ratioWhite); ?></span>
        </div>
        
        <!-- Texto Negro -->
        <div class="contrast-box black-text">
          <p>Texto Negro</p>
          <div class="contrast-val" id="contrast-black"><?php echo number_format($ratioBlack, 2); ?>:1</div>
          <span class="badge <?php echo getBadgeClass($ratioBlack); ?>" id="badge-black"><?php echo getBadgeText($ratioBlack); ?></span>
        </div>
      </div>
      
      <!-- Presets -->
      <div class="presets-container">
        <h3>Paleta Básica</h3>
        <div class="presets-grid">
          <?php foreach ($presetColors as $color): ?>
            <div class="preset-dot" data-color="<?php echo $color; ?>" style="background-color: <?php echo $color; ?>;" title="<?php echo $color; ?>"></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    
    <!-- COLUMNA DERECHA: Entrada, Resultados y Sliders -->
    <div class="panel-controls">
      <form method="GET" onsubmit="return false;">
        <div class="input-group">
          <label for="color-input">Ingresa un color (HEX, RGB o HSL)</label>
          <div class="input-with-picker">
            <input type="text" name="color" id="color-input" value="<?php echo htmlspecialchars($colorQuery); ?>" placeholder="Ej: #3b82f6, rgb(59,130,246), hsl(217,91%,60%)" autocomplete="off">
            <div class="picker-wrapper">
              <input type="color" id="picker" value="<?php echo htmlspecialchars($parsed['hex']); ?>" title="Elegir color">
            </div>
          </div>
        </div>
      </form>
      
      <!-- Listado de Formatos Convertidos -->
      <div class="output-list">
        <div class="output-row">
          <label for="r-hex">HEX</label>
          <input type="text" id="r-hex" readonly value="<?php echo htmlspecialchars($parsed['hex']); ?>">
          <button class="btn-mini" data-copy="r-hex">📋 Copiar</button>
        </div>
        
        <div class="output-row">
          <label for="r-rgb">RGB</label>
          <input type="text" id="r-rgb" readonly value="rgb(<?php echo $parsed['r']; ?>, <?php echo $parsed['g']; ?>, <?php echo $parsed['b']; ?>)">
          <button class="btn-mini" data-copy="r-rgb">📋 Copiar</button>
        </div>
        
        <div class="output-row">
          <label for="r-hsl">HSL</label>
          <input type="text" id="r-hsl" readonly value="hsl(<?php echo $parsed['h']; ?>, <?php echo $parsed['s']; ?>%, <?php echo $parsed['l']; ?>%)">
          <button class="btn-mini" data-copy="r-hsl">📋 Copiar</button>
        </div>
      </div>
      
      <!-- Range Sliders Tuning -->
      <div class="sliders-section">
        <h3>Ajustar Valores</h3>
        
        <!-- RGB sliders -->
        <div class="slider-group">
          <div class="slider-info">
            <span>Rojo (R)</span>
            <span class="val" id="val-r"><?php echo $parsed['r']; ?></span>
          </div>
          <div class="slider-control">
            <input type="range" id="slider-r" min="0" max="255" value="<?php echo $parsed['r']; ?>">
          </div>
        </div>
        
        <div class="slider-group">
          <div class="slider-info">
            <span>Verde (G)</span>
            <span class="val" id="val-g"><?php echo $parsed['g']; ?></span>
          </div>
          <div class="slider-control">
            <input type="range" id="slider-g" min="0" max="255" value="<?php echo $parsed['g']; ?>">
          </div>
        </div>
        
        <div class="slider-group">
          <div class="slider-info">
            <span>Azul (B)</span>
            <span class="val" id="val-b"><?php echo $parsed['b']; ?></span>
          </div>
          <div class="slider-control">
            <input type="range" id="slider-b" min="0" max="255" value="<?php echo $parsed['b']; ?>">
          </div>
        </div>
        
        <!-- HSL sliders -->
        <div style="margin-top: 1rem; border-top: 1px dashed var(--border); padding-top: 0.8rem;"></div>
        
        <div class="slider-group">
          <div class="slider-info">
            <span>Tono (H)</span>
            <span class="val" id="val-h"><?php echo $parsed['h']; ?></span>
          </div>
          <div class="slider-control">
            <input type="range" id="slider-h" min="0" max="359" value="<?php echo $parsed['h']; ?>">
          </div>
        </div>
        
        <div class="slider-group">
          <div class="slider-info">
            <span>Saturación (S)</span>
            <span class="val" id="val-s"><?php echo $parsed['s']; ?>%</span>
          </div>
          <div class="slider-control">
            <input type="range" id="slider-s" min="0" max="100" value="<?php echo $parsed['s']; ?>">
          </div>
        </div>
        
        <div class="slider-group">
          <div class="slider-info">
            <span>Luminosidad (L)</span>
            <span class="val" id="val-l"><?php echo $parsed['l']; ?>%</span>
          </div>
          <div class="slider-control">
            <input type="range" id="slider-l" min="0" max="100" value="<?php echo $parsed['l']; ?>">
          </div>
        </div>
      </div>
      
    </div>
  </div>
  
  <section class="info-section">
    <h2>Información de Formatos de Color</h2>
    <p>El color digital se representa de múltiples formas en diseño de interfaces y desarrollo web:</p>
    <p><strong>HEX (Hexadecimal):</strong> Código de 6 dígitos que representa canales R, G y B. Es el formato estándar en CSS (ej: <code>#3b82f6</code>).</p>
    <p><strong>RGB (Red, Green, Blue):</strong> Representa las intensidades de los colores primarios aditivos de 0 a 255 (ej: <code>rgb(59, 130, 246)</code>).</p>
    <p><strong>HSL (Hue, Saturation, Lightness):</strong> Tono en grados (0-360), Saturación en porcentaje (0-100%) y Luminosidad (0-100%). Ideal para ajustar manualmente matices y sombras.</p>
    <p><strong>Accesibilidad WCAG:</strong> Muestra el contraste del fondo con texto blanco o negro. Para texto normal se requiere un contraste mínimo de <strong>4.5:1 (nivel AA)</strong> o <strong>7:1 (nivel AAA)</strong>.</p>
  </section>
</main>
<footer>
  <p>Desarrollado por <a href="https://configuroweb.com" target="_blank">ConfiguroWeb</a> ·
     <a href="https://appscweb.com/citas/" target="_blank">Sistema de Citas</a> ·
     <a href="https://appscweb.com/negocios/" target="_blank">Gestión de Negocios</a></p>
  <p>&copy; <?php echo date('Y'); ?> ConfiguroWeb</p>
</footer>
<script src="assets/script.js"></script>
</body>
</html>