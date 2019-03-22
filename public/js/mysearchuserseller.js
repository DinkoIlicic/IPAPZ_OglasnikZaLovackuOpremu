$(document).ready(function () {
    var users = new Bloodhound(
        {
            datumTokenizer: Bloodhound.tokenizers.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: "/seller/handle-search-per-user/%QUERY%",
                wildcard: '%QUERY%',
                filter: function (users) {
                    return $.map(users, function (user) {
                        return {
                            user_id: user.id,
                            user_fullName: user.fullName
                        }
                    })
                }
            }
        }
    );
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
                empty: [
                    '<div class="empty-message">',
                        'No user found!',
                    '</div>'
                ].join('\n'),
                suggestion: function (data) {
                    return `
                                <div>
                                    <span>`+data.user_fullName+`</span>
                                </div>
                            `
                }
            }
        }
    ).on('typeahead:autocomplete', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/seller/ajax-person-sold-user-seller/"+data.user_id
        }).done(function (data) {
            prepareSoldProducts(data);
        })
    }).on('typeahead:selected', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/seller/ajax-person-sold-user-seller/"+data.user_id
        }).done(function (data) {
            prepareSoldProducts(data);
        })
    }).on('typeahead:select', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/seller/ajax-person-sold-user-seller/"+data.user_id
        }).done(function (data) {
            prepareSoldProducts(data);
        })
    });
});

function prepareSoldProducts(data)
{
    var userNameTable = document.getElementById('userNameForTable');
    userNameTable.innerHTML = data.userName;
    var table = document.getElementById("tbodyChangeUser");
    table.innerHTML = '';
    var numbers = data.soldItems;
    numbers.forEach(renderSoldProducts);
}

function renderSoldProducts(item, index)
{
    var options = { year: 'numeric', month: 'numeric', day: 'numeric', hour: 'numeric', minute: 'numeric' };
    var table = document.getElementById("tbodyChangeUser");
    var row = table.insertRow(index);
    var cell1 = row.insertCell(0);
    var cell2 = row.insertCell(1);
    var cell3 = row.insertCell(2);
    var cell4 = row.insertCell(3);
    var cell5 = row.insertCell(4);
    var cell6 = row.insertCell(5);
    var cell7 = row.insertCell(6);
    cell1.innerHTML = "<a href='/seller/sold-product/"+item.id+"' class='card-link'>"+item.name+"</a>";
    cell2.innerHTML = item.buyerName;
    cell3.innerHTML = item.quantity;
    cell4.innerHTML = item.totalPrice;
    var date1 = new Date(item.boughtAt.date);
    cell5.innerHTML = date1.toLocaleDateString("hr-HR", options);
    if (item.confirmed === 0) {
        cell6.innerHTML = "<a class='btn btn-primary' href='/seller/confirm-buy-per-user-seller/"+item.id+"'>Confirm</a>";
        cell7.innerHTML = "<a class='btn btn-danger' href='/seller/delete-sold-item-per-user-seller/"+item.id+"'>Delete</a>";
    }
    if (item.confirmed === 1) {
        cell6.innerHTML = "<a class='btn btn-primary' href='/seller/confirm-buy-per-user-seller/"+item.id+"'>Confirmed</a>";
    }
}
