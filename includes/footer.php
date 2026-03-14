</div>
<footer class="site-footer">
    <p>© <?php echo date('Y'); ?> 烟花网购站 · 请遵守当地法规安全燃放</p>
</footer>
<script>
(function(){
    var cart = JSON.parse(localStorage.getItem('cart') || '[]');
    var n = cart.reduce(function(s,i){ return s + (i.quantity||1); }, 0);
    var el = document.getElementById('cartLink');
    if (el && n > 0) el.innerHTML = '购物车 (' + n + ')';
})();
</script>
</body>
</html>
