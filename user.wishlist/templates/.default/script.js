$(document).ready(function(){
    $('#wishlist').on('click', '.item>a.delete-item', function(){
        var $el = $(this);
        $.post($el.attr('data-href'), {id: $el.attr('data-id')})
            .done(function(){
                $el.closest('.item').remove();
            });
        return false;
    });
});