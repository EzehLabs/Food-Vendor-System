$('#cartToggle').click(function(e){
    e.preventDefault();

    $('#cartSidebar').addClass('active');
    $('#cartOverlay').addClass('active');

    loadCart();
});

$('#closeCart,#cartOverlay').click(function(){

    $('#cartSidebar').removeClass('active');
    $('#cartOverlay').removeClass('active');

});

function loadCart(){

    $('#cartContent').load('/cart/sidebar');

}

loadCart();