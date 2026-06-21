<?php
/**
 * Conversor de Colores HEX ↔ RGB ↔ HSL
 */
header('Content-Type: text/html; charset=utf-8');

$hex = '#3b82f6';
$rgb = ''; $hsl = '';
if (isset($_GET['hex'])) $hex = '#' . ltrim(trim($_GET['hex']), '#');

// Procesar conversión
if (preg_match('/^#?([a-f0-9]{6})$/i', $hex, $m)) {
    $r = hexdec(substr($m[1],0,2)); $g = hexdec(substr($m[1],2,2)); $b = hexdec(substr($m[1],4,2));
    $rgb = "$r, $g, $b";
    // HSL
    $r/=255;$g/=255;$b/=255;
    $max=max($r,$g,$b);$min=min($r,$g,$b);
    $l=($max+$min)/2;
    if($max==$min){$h=0;$s=0;}
    else{
        $d=$max-$min;
        $s=$l>0.5?$d/(2-$max-$min):$d/($max+$min);
        switch($max){
            case $r:$h=($g-$b)/$d+($g<$b?6:0);break;
            case $g:$h=($b-$r)/$d+2;break;
            default:$h=($r-$g)/$d+4;
        }
        $h/=6;
    }
    $hsl = round($h*360).", ".round($s*100)."%, ".round($l*100)."%";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Conversor de Colores HEX RGB HSL Online | ConfiguroWeb</title>
<meta name="description" content="Convierte colores entre HEX, RGB y HSL online gratis. Vista previa en vivo. Herramienta para diseñadores web de ConfiguroWeb.">
<meta name="keywords" content="conversor colores, hex a rgb, rgb a hex, hsl, paleta colores, css color">
<meta property="og:type" content="website">
<meta property="og:title" content="Conversor de Colores HEX RGB HSL Online">
<meta property="og:description" content="Convierte colores entre HEX, RGB y HSL con vista previa en vivo.">
<link rel="canonical" href="https://demoscweb.com/github/php-conversor-colores-hex-rgb/">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"WebApplication","name":"Conversor de Colores HEX RGB HSL","applicationCategory":"DesignApplication","operatingSystem":"Any","offers":{"@type":"Offer","price":"0","priceCurrency":"USD"},"author":{"@type":"Person","name":"ConfiguroWeb","url":"https://configuroweb.com"}}
</script>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header>
  <h1>🎨 Conversor de Colores HEX ↔ RGB ↔ HSL</h1>
  <p class="subtitle">Conversión instantánea con vista previa del color.</p>
</header>
<main>
  <div class="preview" id="preview" style="background:<?php echo htmlspecialchars($hex); ?>"></div>
  <form method="GET">
    <label for="hex">Color HEX:</label>
    <input type="text" name="hex" id="hex" value="<?php echo htmlspecialchars(ltrim($hex,'#')); ?>" placeholder="3b82f6" maxlength="6">
    <div class="botones">
      <button type="submit" class="btn-primary">Convertir</button>
      <input type="color" id="picker" value="<?php echo htmlspecialchars($hex); ?>" title="Elegir color">
    </div>
  </form>
  <?php if($rgb): ?>
  <div class="resultados">
    <div class="fila"><span>HEX</span><code id="r-hex"><?php echo htmlspecialchars($hex); ?></code><button class="btn-mini" data-copy="r-hex">📋</button></div>
    <div class="fila"><span>RGB</span><code id="r-rgb"><?php echo "rgb($rgb)"; ?></code><button class="btn-mini" data-copy="r-rgb">📋</button></div>
    <div class="fila"><span>HSL</span><code id="r-hsl"><?php echo "hsl($hsl)"; ?></code><button class="btn-mini" data-copy="r-hsl">📋</button></div>
  </div>
  <?php endif; ?>
  <section class="info">
    <h2>Formatos de color en CSS</h2>
    <p><strong>HEX</strong> (ej: <code>#3b82f6</code>): notación hexadecimal compacta, ideal para CSS.</p>
    <p><strong>RGB</strong> (ej: <code>rgb(59,130,246)</code>): rojo, verde y azul en 0-255.</p>
    <p><strong>HSL</strong> (ej: <code>hsl(217,91%,60%)</code>): tono, saturación y luminosidad. Más intuitivo para ajustar colores.</p>
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