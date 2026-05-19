#!/usr/bin/env node
/**
 * render_trainer_card.js
 * Usage: node render_trainer_card.js <username> <outputPath> <cardDataJson>
 * Renders a trainer card PNG via headless Chromium.
 */

const puppeteer = require('puppeteer');

const [,, username, outputPath, cardDataJson] = process.argv;

if (!username || !outputPath || !cardDataJson) {
    console.error('Usage: node render_trainer_card.js <username> <outputPath> <cardDataJson>');
    process.exit(1);
}

const data = JSON.parse(cardDataJson);

const schemes = {
    blue:   { bg1:'#4a90c8', bg2:'#2a5a8a', header:'#1a3a5a', field:'#8ec8f0', text:'#ffffff', dark:'#0a1a2a', bar:'#4caf7d', barBg:'#1a3a5a' },
    red:    { bg1:'#c84a4a', bg2:'#8a2a2a', header:'#5a1a1a', field:'#f08e8e', text:'#ffffff', dark:'#2a0a0a', bar:'#f0c040', barBg:'#5a1a1a' },
    green:  { bg1:'#4a9a5a', bg2:'#2a6a3a', header:'#1a4a2a', field:'#8ef0a0', text:'#ffffff', dark:'#0a2a10', bar:'#f0e040', barBg:'#1a4a2a' },
    gold:   { bg1:'#c8a030', bg2:'#8a6a10', header:'#5a4a08', field:'#f0d880', text:'#ffffff', dark:'#2a1a00', bar:'#4caf7d', barBg:'#5a4a08' },
    purple: { bg1:'#7a4ac8', bg2:'#4a1a8a', header:'#2a0a5a', field:'#c08ef0', text:'#ffffff', dark:'#100a2a', bar:'#f0a0e0', barBg:'#2a0a5a' },
    black:  { bg1:'#444444', bg2:'#222222', header:'#111111', field:'#888888', text:'#ffffff', dark:'#000000', bar:'#4caf7d', barBg:'#111111' },
    maroon: { bg1:'#800000', bg2:'#500000', header:'#300000', field:'#f0a0a0', text:'#ffffff', dark:'#100000', bar:'#f0c040', barBg:'#300000' },
};

const typeColors = {
    normal:   { hex: '#A8A77A', dark: '#6a6a4e', text: '#ffffff' },
    fire:     { hex: '#EE8130', dark: '#a84e0a', text: '#ffffff' },
    water:    { hex: '#6390F0', dark: '#2a50c0', text: '#ffffff' },
    electric: { hex: '#F7D02C', dark: '#b09000', text: '#000000' },
    grass:    { hex: '#7AC74C', dark: '#3a7a1a', text: '#ffffff' },
    ice:      { hex: '#96D9D6', dark: '#3a9090', text: '#000000' },
    fighting: { hex: '#C22E28', dark: '#7a0a08', text: '#ffffff' },
    poison:   { hex: '#A33EA1', dark: '#5a0a5a', text: '#ffffff' },
    ground:   { hex: '#E2BF65', dark: '#a07820', text: '#000000' },
    flying:   { hex: '#A98FF3', dark: '#5a3ab0', text: '#ffffff' },
    psychic:  { hex: '#F95587', dark: '#b0003a', text: '#ffffff' },
    bug:      { hex: '#A6B91A', dark: '#5a6a00', text: '#ffffff' },
    rock:     { hex: '#B6A136', dark: '#6a5a00', text: '#ffffff' },
    ghost:    { hex: '#735797', dark: '#2a1a4a', text: '#ffffff' },
    dragon:   { hex: '#6F35FC', dark: '#2a00b0', text: '#ffffff' },
    dark:     { hex: '#705746', dark: '#2a1a0a', text: '#ffffff' },
    steel:    { hex: '#B7B7CE', dark: '#5a5a7a', text: '#000000' },
    fairy:    { hex: '#D685AD', dark: '#8a2a5a', text: '#ffffff' },
};

const TYPE_ICON_BASE = 'https://raw.githubusercontent.com/duiker101/pokemon-type-svg-icons/master/icons';


const activeType = data.tcType && typeColors[data.tcType] ? typeColors[data.tcType] : null;
// Seeded RNG (mulberry32) so icon positions are consistent per user
function seededRng(seed) {
    let s = seed;
    return function() {
        s |= 0; s = s + 0x6D2B79F5 | 0;
        let t = Math.imul(s ^ s >>> 15, 1 | s);
        t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
        return ((t ^ t >>> 14) >>> 0) / 4294967296;
    };
}

function usernameToSeed(str) {
    let h = 0;
    for (let i = 0; i < str.length; i++) h = Math.imul(31, h) + str.charCodeAt(i) | 0;
    return h;
}

function generateTypeIcons(containerId, width, height, count, tcType, iconUrl) {
    const rng = seededRng(usernameToSeed(data.username + containerId));

    // Lay out icons in a grid, then jitter each within its cell
    const cols = Math.ceil(Math.sqrt(count * (width / height)));
    const rows = Math.ceil(count / cols);
    const cellW = Math.floor(width / cols);
    const cellH = Math.floor(height / rows);
    const size = Math.min(cellW, cellH) - 10; // icon fits inside cell with margin

    const icons = [];
    for (let i = 0; i < rows; i++) {
        for (let j = 0; j < cols; j++) {
            if (icons.length >= count) break;
            const jitterX = Math.floor(rng() * Math.max(0, cellW - size));
            const jitterY = Math.floor(rng() * Math.max(0, cellH - size));
            const x = j * cellW + jitterX;
            const y = i * cellH + jitterY;
            const rotation = Math.floor(rng() * 40) - 20;
            icons.push(
                `<img src="${iconUrl}" style="` +
                `position:absolute;left:${x}px;top:${y}px;` +
                `width:${size}px;height:${size}px;` +
                `opacity:0.2;` +
                `border:1.5px solid rgba(255,255,255,0.25);border-radius:6px;` +
                `transform:rotate(${rotation}deg);pointer-events:none;" />`
            );
        }
    }
    return icons.join('');
}

const s = activeType
    ? { bg1: activeType.hex, bg2: activeType.dark, header: activeType.dark, field: activeType.hex, text: activeType.text, dark: activeType.dark, bar: '#4caf7d', barBg: activeType.dark }
    : (schemes[data.color] ?? schemes['maroon']);
const rivalPct = data.totalRivals > 0 ? Math.round((data.beatenRivals / data.totalRivals) * 100) : 0;

const html = `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { background:transparent; width:480px; }

.tc-card {
    width:480px;
    border-radius:12px;
    overflow:hidden;
    box-shadow:0 8px 32px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.2);
    font-family:'Press Start 2P',monospace;
    border:3px solid ${s.dark};
}

.tc-header {
    background:${s.header};
    padding:6px 12px;
    display:flex; justify-content:space-between; align-items:center;
    border-bottom:2px solid ${s.dark};
}
.tc-header-title { font-size:9px; color:${s.field}; letter-spacing:1px; }
.tc-header-id    { font-size:8px; color:${s.field}; }

.tc-body {
    background:linear-gradient(160deg, ${s.bg1} 0%, ${s.bg2} 100%);
    padding:14px;
    display:grid; grid-template-columns:1fr 96px; gap:12px;
    position:relative; overflow:hidden;
}

.tc-fields { display:flex; flex-direction:column; gap:7px; }

.tc-field {
    display:flex; align-items:center; gap:8px;
    background:rgba(0,0,0,.55); border-radius:3px; padding:5px 8px;
}
.tc-field-label { font-size:6px; color:${s.field}; min-width:60px; letter-spacing:.5px; }
.tc-field-value { font-size:8px; color:${s.text}; text-shadow:1px 1px 0 ${s.dark}; }

.tc-avatar {
    width:80px; height:80px; border-radius:4px;
    border:3px solid ${s.dark}; box-shadow:3px 3px 0 ${s.dark};
    object-fit:cover;
}

.tc-fav-mon {
    margin-top:8px; text-align:center;
}
.tc-fav-mon img {
    width:64px; height:64px; image-rendering:pixelated;
    object-fit:contain;
}

.tc-rivals-section {
    background:${s.header};
    padding:10px 14px;
    border-top:2px solid ${s.dark};
    position:relative; overflow:hidden;
}
.tc-rivals-label { font-size:6px; color:${s.field}; letter-spacing:1px; margin-bottom:7px; }

.tc-bar-track {
    background:${s.barBg}; border-radius:2px; height:10px;
    border:1px solid ${s.dark}; overflow:hidden;
}
.tc-bar-fill {
    background:${s.bar}; height:100%; width:${rivalPct}%;
    box-shadow:inset 0 1px 0 rgba(255,255,255,.3);
}
.tc-bar-count { font-size:7px; color:${s.field}; margin-top:5px; text-align:right; }

.tc-rival-sprites { display:flex; flex-wrap:wrap; gap:4px; margin-top:8px; background:rgba(0,0,0,.45); border-radius:6px; padding:6px; }
.tc-rival-pip {
    width:24px; height:24px; border-radius:50%; overflow:hidden;
    border:1px solid ${s.dark}; opacity:.3; filter:grayscale(1);
}
.tc-rival-pip.beaten { opacity:1; filter:none; box-shadow:0 0 5px ${s.bar}; }
.tc-rival-pip img { width:100%; height:100%; object-fit:cover; }
</style>
</head>
<body>
<div class="tc-card">
    <div class="tc-header">
        <span class="tc-header-title">TRAINER CARD</span>
        <span class="tc-header-id">ID No.${String(data.userId).padStart(5, '0')}</span>
    </div>
    <div class="tc-body">
        ${activeType ? generateTypeIcons('body', 454, 160, 12, data.tcType, TYPE_ICON_BASE + '/' + data.tcType + '.svg') : ''}
        <div class="tc-fields" style="position:relative;z-index:1">
            <div class="tc-field">
                <span class="tc-field-label">■ NAME</span>
                <span class="tc-field-value">${data.username.toUpperCase()}</span>
            </div>
            ${(data.sections.core || data.sections.mod) ? `
            <div class="tc-field">
                <span class="tc-field-label">■ GLITCHES</span>
                <span class="tc-field-value">${data.glitchCount}</span>
            </div>` : ''}
            ${(data.sections.smitty || data.sections.unismitty) ? `
            <div class="tc-field">
                <span class="tc-field-label">■ SMITTY</span>
                <span class="tc-field-value">${data.smittyCount}</span>
            </div>` : ''}
            ${data.sections.submitted ? `
            <div class="tc-field">
                <span class="tc-field-label">■ SUBMITTED</span>
                <span class="tc-field-value">${data.submittedCount}</span>
            </div>` : ''}
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;position:relative;z-index:1">
            <img class="tc-avatar" src="${data.avatarUrl}" alt="${data.username}">
            ${data.favMonUrl ? `
            <div class="tc-fav-mon">
                <img src="${data.favMonUrl}" alt="${data.favMonName}" onerror="this.style.display='none'">
            </div>` : ''}
        </div>
    </div>
    ${data.sections.rivals ? `
    <div class="tc-rivals-section">
        ${activeType ? generateTypeIcons('rivals', 454, 120, 8, data.tcType, TYPE_ICON_BASE + '/' + data.tcType + '.svg') : ''}
        <div style="position:relative;z-index:1">
            <div class="tc-rivals-label">■ RIVALS DEFEATED</div>
            <div class="tc-bar-track"><div class="tc-bar-fill"></div></div>
            <div class="tc-bar-count">${data.beatenRivals} / ${data.totalRivals}</div>
            <div class="tc-rival-sprites">
                ${data.rivals.map(r => `
                <div class="tc-rival-pip ${r.beaten ? 'beaten' : ''}" title="${r.name}">
                    <img src="${r.imgUrl}" alt="${r.name}" onerror="this.parentElement.style.display='none'">
                </div>`).join('')}
            </div>
        </div>
    </div>` : ''}
</div>
</body>
</html>`;

(async () => {
    const browser = await puppeteer.launch({
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu'],
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 480, height: 400 });
    await page.setContent(html, { waitUntil: 'networkidle0' });

    const card = await page.$('.tc-card');
    await card.screenshot({ path: outputPath, omitBackground: true });

    await browser.close();
    process.exit(0);
})();
