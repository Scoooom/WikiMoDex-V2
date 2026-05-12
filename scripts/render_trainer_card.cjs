#!/usr/bin/env node
/**
 * render_trainer_card.js
 * Usage: node render_trainer_card.js <username> <outputPath> <cardDataJson>
 * Renders a trainer card PNG via headless Chromium.
 */

const puppeteer = require('puppeteer');

const CHROME_PATH = '/root/.cache/puppeteer/chrome/linux-148.0.7778.97/chrome-linux64/chrome';

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

const s = schemes[data.color] ?? schemes['maroon'];
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
}

.tc-fields { display:flex; flex-direction:column; gap:7px; }

.tc-field {
    display:flex; align-items:center; gap:8px;
    background:rgba(0,0,0,.18); border-radius:3px; padding:5px 8px;
}
.tc-field-label { font-size:6px; color:${s.field}; min-width:60px; letter-spacing:.5px; }
.tc-field-value { font-size:8px; color:${s.text}; text-shadow:1px 1px 0 ${s.dark}; }

.tc-avatar {
    width:80px; height:80px; border-radius:4px;
    border:3px solid ${s.dark}; box-shadow:3px 3px 0 ${s.dark};
    object-fit:cover;
}

.tc-rivals-section {
    background:${s.header};
    padding:10px 14px;
    border-top:2px solid ${s.dark};
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

.tc-rival-sprites { display:flex; flex-wrap:wrap; gap:4px; margin-top:8px; }
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
        <div class="tc-fields">
            <div class="tc-field">
                <span class="tc-field-label">■ NAME</span>
                <span class="tc-field-value">${data.username.toUpperCase()}</span>
            </div>
            <div class="tc-field">
                <span class="tc-field-label">■ GLITCHES</span>
                <span class="tc-field-value">${data.glitchCount}</span>
            </div>
            <div class="tc-field">
                <span class="tc-field-label">■ SMITTY</span>
                <span class="tc-field-value">${data.smittyCount}</span>
            </div>
            <div class="tc-field">
                <span class="tc-field-label">■ SUBMITTED</span>
                <span class="tc-field-value">${data.submittedCount}</span>
            </div>
        </div>
        <div>
            <img class="tc-avatar" src="${data.avatarUrl}" alt="${data.username}">
        </div>
    </div>
    <div class="tc-rivals-section">
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
</div>
</body>
</html>`;

(async () => {
    const browser = await puppeteer.launch({
        executablePath: CHROME_PATH,
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
