<script>
function toggleTheme(){const h=document.documentElement,cur=h.getAttribute('data-theme')||'dark',next=cur==='dark'?'light':'dark';h.setAttribute('data-theme',next);localStorage.setItem('zylo-theme',next)}
(function(){const t=localStorage.getItem('zylo-theme')||'dark';document.documentElement.setAttribute('data-theme',t)})();
</script>
