$(document).ready(function () {
    var users = new Bloodhound(
        {
            datumTokenizer: Bloodhound.tokenizers.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: "/admin/handle-search-per-user-admin/%QUERY%",
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
            url: "/admin/ajax-person-sold-per-user-admin/"+data.user_id
        }).done(function (data) {
            prepareSoldProducts(data);
        })
    }).on('typeahead:selected', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/admin/ajax-person-sold-per-user-admin/"+data.user_id
        }).done(function (data) {
            prepareSoldProducts(data);
        })
    }).on('typeahead:select', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/admin/ajax-person-sold-per-user-admin/"+data.user_id
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
    var cell8 = row.insertCell(7);
    cell1.innerHTML = "<a href='/admin/sold-product/"+item.id+"' class='card-link'>"+item.productName+"</a>";
    cell2.innerHTML = item.sellerName;
    cell3.innerHTML = item.buyerName;
    cell4.innerHTML = item.quantity;
    cell5.innerHTML = item.totalPrice;
    var date1 = new Date(item.boughtAt.date);
    cell6.innerHTML = date1.toLocaleDateString("hr-HR", options);
    if (item.ptMethod === 'Paypal') {
        cell7.innerHTML = "<a class='card-link' href='/admin/sold-item-payment-method/"+item.ptId+"'>Paid with paypal</a>";
    } else if (item.ptMethod === 'Invoice') {
        if (item.ptConfirmed === '1') {
            cell7.innerHTML = "<a class='card-link' href='/admin/sold-item-payment-method/"+item.ptId+"'>Paid with invoice</a>";
        } else if (item.ptConfirmed === '0') {
            cell7.innerHTML = "<a class='card-link' href='/admin/sold-item-payment-method/"+item.ptId+"'>Invoice</a>";
        }
    } else if (item.ptMethod === null) {
        cell7.innerHTML = "Not chosen";
    }
    if (item.ptConfirmed === '1') {
        cell8.innerHTML = "Paid";
    } else if (item.ptConfirmed === '0') {
        cell8.innerHTML = "<a class='btn btn-danger' href='/admin/delete-sold-item-per-user-admin/"+item.id+"'>Delete</a>";
    } else if (item.ptConfirmed === null) {
        cell8.innerHTML = "<a class='btn btn-danger' href='/admin/delete-sold-item-per-user-admin/"+item.id+"'>Delete</a>";
    }
}
