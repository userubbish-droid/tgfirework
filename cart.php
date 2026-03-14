<?php
require_once 'config.php';
session_start();
$pageTitle = '购物车 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>购物车</h2>
    <div id="cartContent">
        <p class="cart-empty">购物车为空，<a href="<?php echo BASE_PATH; ?>index.php">去选购</a></p>
    </div>
    <div id="cartTableWrap" style="display:none;">
        <table class="cart-table">
            <thead>
                <tr><th>商品</th><th>单价</th><th>数量</th><th>小计</th><th></th></tr>
            </thead>
            <tbody id="cartBody"></tbody>
        </table>
        <div class="cart-total">合计：<strong id="totalPrice">¥ 0.00</strong></div>
        <p style="margin-top:1rem;">
            <a href="<?php echo BASE_PATH; ?>checkout.php" class="btn btn-primary">去结算</a>
            <a href="<?php echo BASE_PATH; ?>index.php" class="btn" style="margin-left:0.5rem;">继续购物</a>
        </p>
    </div>
</main>
<script>
(function(){
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    var wrap=document.getElementById('cartTableWrap');
    var empty=document.getElementById('cartContent');
    var body=document.getElementById('cartBody');
    var totalEl=document.getElementById('totalPrice');
    if(cart.length===0){ wrap.style.display='none'; empty.style.display='block'; return; }
    empty.style.display='none'; wrap.style.display='block';
    var total=0;
    cart.forEach(function(item,idx){
        var sub=item.price*(item.quantity||1);
        total+=sub;
        var unitLabel = (item.unit==='box') ? '箱' : '件';
        var priceLabel = '¥ '+item.price.toFixed(2)+(item.unit==='box' ? '/箱' : '/件');
        var tr=document.createElement('tr');
        tr.innerHTML='<td>'+item.name+(item.unit==='box' ? ' <span class="cart-unit">(按箱)</span>' : '')+'</td><td>'+priceLabel+'</td><td><input type="number" min="1" value="'+(item.quantity||1)+'" data-idx="'+idx+'" onchange="updateQty(this)"> '+unitLabel+'</td><td>¥ '+sub.toFixed(2)+'</td><td><button onclick="removeItem('+idx+')">删除</button></td>';
        body.appendChild(tr);
    });
    totalEl.textContent='¥ '+total.toFixed(2);
})();
function updateQty(input){
    var idx=parseInt(input.dataset.idx);
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    cart[idx].quantity=Math.max(1,parseInt(input.value)||1);
    localStorage.setItem('cart',JSON.stringify(cart));
    location.reload();
}
function removeItem(idx){
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    cart.splice(idx,1);
    localStorage.setItem('cart',JSON.stringify(cart));
    location.reload();
}
</script>
<?php require_once 'includes/footer.php'; ?>
