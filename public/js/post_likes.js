$(document).ready(function () {
    $('#post-likes').on('click', function (e) {
        e.preventDefault();
        var link = $(e.currentTarget);
        $.ajax({
            method: 'POST',
            url: link.attr('href')
        }).done(function (data) {
            var likeElement = $('#post-likes');
            likeElement.html(' (' + data.likes + ')');
            likeElement.toggleClass('far').toggleClass('fas');
        })
    });
});