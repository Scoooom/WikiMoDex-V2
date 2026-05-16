<div id="contribute-cta" style="display:none;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:10px 16px;margin-bottom:32px;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
    <span style="font-size:13px;color:var(--muted)">Want to help build the wiki? Join the community and ask for editor access.</span>
    <a href="https://discord.gg/xsQummMK3H" target="_blank" rel="noopener" style="font-size:13px;color:var(--accent);white-space:nowrap">Join Discord →</a>
</div>
<script>
(function() {
    fetch('/me.json', { credentials: 'same-origin' })
        .then(r => r.json())
        .then(me => {
            if (!me.authed || (!me.isAdmin && !me.isEditor)) {
                document.getElementById('contribute-cta').style.display = 'flex';
            }
        })
        .catch(() => {
            document.getElementById('contribute-cta').style.display = 'flex';
        });
})();
</script>
