$(document).ready(function ()
{
    var users = new Bloodhound(
        {
            datumTokenizer: Bloodhound.tokenizers.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: "/admin/handleSearch/%QUERY%",
                wildcard: '%QUERY%',
                filter: function (users)
                {
                    return $.map(users, function (user)
                    {
                        return {
                            user_id: user.id,
                            user_fullName: user.fullName
                        }
                    })
                }
            }
        });
    users.initialize();
    $('#form_query').typeahead(
        {
            hint: true,
            highlight: true,
            minLength: 3,
        },
        {
            name: 'users',
            limit: 10,
            source: users.ttAdapter(),
            display: 'user_fullName',
            templates: {
                suggestion: function (data)
                {
                    return `
                                <a href="/admin/itemsoldperuser/`+data.user_id+`">
                                    <span>`+data.user_fullName+`</span>
                                </div>
                            `
                },
                footer: function (query)
                {
                    return '<div class="text-center">More results about: '+ query.query +'</div>'
                }
            }
        }).on('typeahead:autocomplete', function(event, data) {
            event.preventDefault();
            $.ajax({
                method: 'POST',
                url: "/admin/ajaxpersonsold/"+data.user_id
            }).done(function (data) {
                console.log(data);
            })
        }).on('typeahead:selected', function(event, data) {
            event.preventDefault();
            $.ajax({
                method: 'POST',
                url: "/admin/ajaxpersonsold/"+data.user_id
            }).done(function (data) {
                console.log(data);
            })
    });
});

$(document).ready(function () {
    $(document).on('change','.js-status-change', function (e) {
        e.preventDefault();
        let link = $(this).find(':selected').data('change_status');
        $.ajax({
            method: 'POST',
            url: link
        }).done(function (data) {
            console.log(data);

            var rowHtml =  $('#task-'+data.taskID).prop('outerHTML');
            $('#task-'+data.taskID).remove();
            $('#js-table-status-'+data.newStatusID ).append(rowHtml);
            $('#js-table-status-'+data.newStatusID+' option[value='+data.newStatusID+']' ).prop('selected', 'selected');
        })
    });
});