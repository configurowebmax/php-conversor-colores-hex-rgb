document.addEventListener('DOMContentLoaded', () => {
    // DOM Elements
    const colorInput = document.getElementById('color-input');
    const picker = document.getElementById('picker');
    const preview = document.getElementById('preview');
    
    // Result Text Fields
    const inputHex = document.getElementById('r-hex');
    const inputRgb = document.getElementById('r-rgb');
    const inputHsl = document.getElementById('r-hsl');
    
    // Sliders
    const sliderR = document.getElementById('slider-r');
    const sliderG = document.getElementById('slider-g');
    const sliderB = document.getElementById('slider-b');
    const sliderH = document.getElementById('slider-h');
    const sliderS = document.getElementById('slider-s');
    const sliderL = document.getElementById('slider-l');
    
    // Sliders value displays
    const valR = document.getElementById('val-r');
    const valG = document.getElementById('val-g');
    const valB = document.getElementById('val-b');
    const valH = document.getElementById('val-h');
    const valS = document.getElementById('val-s');
    const valL = document.getElementById('val-l');
    
    // Contrast Displays
    const contrastWhite = document.getElementById('contrast-white');
    const contrastBlack = document.getElementById('contrast-black');
    const badgeWhite = document.getElementById('badge-white');
    const badgeBlack = document.getElementById('badge-black');
    
    // Copy buttons
    const copyBtns = document.querySelectorAll('.btn-mini[data-copy]');
    
    // Presets
    const presets = document.querySelectorAll('.preset-dot');

    // State
    let currentR = 59;
    let currentG = 130;
    let currentB = 246;

    // Initialize values from PHP inputs if available
    const initHex = inputHex.value;
    updateFromHex(initHex);

    // Event Listeners
    colorInput.addEventListener('input', (e) => {
        parseAndApply(e.target.value);
    });

    picker.addEventListener('input', (e) => {
        const hex = e.target.value;
        colorInput.value = hex;
        updateFromHex(hex);
    });

    // Copy to clipboard logic
    copyBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-copy');
            const targetInput = document.getElementById(targetId);
            if (targetInput) {
                targetInput.select();
                targetInput.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(targetInput.value).then(() => {
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '✅';
                    btn.style.background = 'var(--success)';
                    btn.style.borderColor = 'var(--success)';
                    setTimeout(() => {
                        btn.innerHTML = originalText;
                        btn.style.background = '';
                        btn.style.borderColor = '';
                    }, 1500);
                });
            }
        });
    });

    // Presets selection
    presets.forEach(dot => {
        dot.addEventListener('click', () => {
            const hex = dot.getAttribute('data-color');
            colorInput.value = hex;
            updateFromHex(hex);
        });
    });

    // Sliders Listeners
    const handleRgbSliderChange = () => {
        currentR = parseInt(sliderR.value);
        currentG = parseInt(sliderG.value);
        currentB = parseInt(sliderB.value);
        updateFromRgb(currentR, currentG, currentB);
    };

    sliderR.addEventListener('input', handleRgbSliderChange);
    sliderG.addEventListener('input', handleRgbSliderChange);
    sliderB.addEventListener('input', handleRgbSliderChange);

    const handleHslSliderChange = () => {
        const h = parseInt(sliderH.value);
        const s = parseInt(sliderS.value);
        const l = parseInt(sliderL.value);
        updateFromHsl(h, s, l);
    };

    sliderH.addEventListener('input', handleHslSliderChange);
    sliderS.addEventListener('input', handleHslSliderChange);
    sliderL.addEventListener('input', handleHslSliderChange);

    // Parsing function
    function parseAndApply(str) {
        str = str.trim();
        if (!str) return;

        // HEX
        if (/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i.test(str)) {
            let hex = str;
            if (!hex.startsWith('#')) hex = '#' + hex;
            if (hex.length === 4) {
                hex = '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
            }
            updateFromHex(hex);
            return;
        }

        // RGB
        const rgbMatch = str.match(/(?:rgb\s*\()?\s*(\d{1,3})\s*[\s,]\s*(\d{1,3})\s*[\s,]\s*(\d{1,3})\s*\)?/i);
        if (rgbMatch) {
            const r = Math.min(255, Math.max(0, parseInt(rgbMatch[1])));
            const g = Math.min(255, Math.max(0, parseInt(rgbMatch[2])));
            const b = Math.min(255, Math.max(0, parseInt(rgbMatch[3])));
            updateFromRgb(r, g, b);
            return;
        }

        // HSL
        const hslMatch = str.match(/(?:hsl\s*\()?\s*(\d{1,3})\s*[\s,]\s*(\d{1,3})%?\s*[\s,]\s*(\d{1,3})%?\s*\)?/i);
        if (hslMatch) {
            const h = parseInt(hslMatch[1]) % 360;
            const s = Math.min(100, Math.max(0, parseInt(hslMatch[2])));
            const l = Math.min(100, Math.max(0, parseInt(hslMatch[3])));
            updateFromHsl(h, s, l);
            return;
        }
    }

    // Core Updates
    function updateFromHex(hex) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        
        currentR = r;
        currentG = g;
        currentB = b;
        
        applyAllUpdates(hex, r, g, b, ...rgbToHsl(r, g, b));
    }

    function updateFromRgb(r, g, b) {
        const hex = rgbToHex(r, g, b);
        applyAllUpdates(hex, r, g, b, ...rgbToHsl(r, g, b));
    }

    function updateFromHsl(h, s, l) {
        const [r, g, b] = hslToRgb(h, s, l);
        currentR = r;
        currentG = g;
        currentB = b;
        const hex = rgbToHex(r, g, b);
        applyAllUpdates(hex, r, g, b, h, s, l);
    }

    function applyAllUpdates(hex, r, g, b, h, s, l) {
        // Update Preview
        preview.style.backgroundColor = hex;
        picker.value = hex;

        // Update Text fields if not active/focused
        if (document.activeElement !== inputHex) inputHex.value = hex;
        if (document.activeElement !== inputRgb) inputRgb.value = `rgb(${r}, ${g}, ${b})`;
        if (document.activeElement !== inputHsl) inputHsl.value = `hsl(${h}, ${s}%, ${l}%)`;

        // Update Sliders
        sliderR.value = r; valR.textContent = r;
        sliderG.value = g; valG.textContent = g;
        sliderB.value = b; valB.textContent = b;
        
        sliderH.value = h; valH.textContent = h;
        sliderS.value = s; valS.textContent = s + '%';
        sliderL.value = l; valL.textContent = l + '%';

        // Update Contrast
        updateContrast(r, g, b);
    }

    // Contrast formulas
    function updateContrast(r, g, b) {
        const lum = getLuminance(r, g, b);
        
        const ratioWhite = 1.05 / (lum + 0.05);
        const ratioBlack = (lum + 0.05) / 0.05;

        contrastWhite.textContent = ratioWhite.toFixed(2) + ':1';
        contrastBlack.textContent = ratioBlack.toFixed(2) + ':1';

        setBadge(badgeWhite, ratioWhite);
        setBadge(badgeBlack, ratioBlack);
    }

    function setBadge(badge, ratio) {
        badge.className = 'badge';
        if (ratio >= 7.0) {
            badge.textContent = 'AAA ✅';
            badge.classList.add('pass-aaa');
        } else if (ratio >= 4.5) {
            badge.textContent = 'AA ✅';
            badge.classList.add('pass-aa');
        } else if (ratio >= 3.0) {
            badge.textContent = 'AA Large ⚠️';
            badge.classList.add('pass-large');
        } else {
            badge.textContent = 'Fallo ❌';
            badge.classList.add('fail');
        }
    }

    function getLuminance(r, g, b) {
        const a = [r, g, b].map(v => {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
    }

    // Converters helper
    function rgbToHex(r, g, b) {
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
    }

    function rgbToHsl(r, g, b) {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b);
        let h, s, l = (max + min) / 2;

        if (max === min) {
            h = s = 0; // achromatic
        } else {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                case b: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        return [
            Math.round(h * 360),
            Math.round(s * 100),
            Math.round(l * 100)
        ];
    }

    function hslToRgb(h, s, l) {
        h /= 360; s /= 100; l /= 100;
        let r, g, b;

        if (s === 0) {
            r = g = b = l; // achromatic
        } else {
            const hue2rgb = (p, q, t) => {
                if (t < 0) t += 1;
                if (t > 1) t -= 1;
                if (t < 1/6) return p + (q - p) * 6 * t;
                if (t < 1/2) return q;
                if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                return p;
            };

            const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
            const p = 2 * l - q;
            r = hue2rgb(p, q, h + 1/3);
            g = hue2rgb(p, q, h);
            b = hue2rgb(p, q, h - 1/3);
        }

        return [Math.round(r * 255), Math.round(g * 255), Math.round(b * 255)];
    }
});
