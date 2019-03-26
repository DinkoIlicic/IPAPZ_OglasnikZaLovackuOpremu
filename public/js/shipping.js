$(document).ready(function () {
    var countries = new Bloodhound(
        {
            datumTokenizer: Bloodhound.tokenizers.whitespace,
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: "/admin/handle-search-shipping-price/%QUERY%",
                wildcard: '%QUERY%',
                filter: function (countries) {
                    return $.map(countries, function (country) {
                        return {
                            country_name: country.country,
                        }
                    })
                }
            }
        }
    );
    countries.initialize();
    $('#shipping_form_query').typeahead(
        {
            hint: true,
            highlight: true,
            minLength: 3,
        },
        {
            name: 'countries',
            limit: 10,
            source: countries.ttAdapter(),
            display: 'country_name',
            templates: {
                empty: [
                    '<div class="empty-message">',
                    'No country found!',
                    '</div>'
                ].join('\n'),
                suggestion: function (data) {
                    return `
                                <div>
                                    <span>` + data.country_name + `</span>
                                </div>
                            `
                }
            }
        }
    ).on('typeahead:autocomplete', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/admin/ajax-shipping/"+data.country_name
        }).done(function (data) {
            prepareShippingPrice(data);
        })
    }).on('typeahead:selected', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/admin/ajax-shipping/"+data.country_name
        }).done(function (data) {
            prepareShippingPrice(data);
        })
    }).on('typeahead:select', function (event, data) {
        event.preventDefault();
        $.ajax({
            method: 'POST',
            url: "/admin/ajax-shipping/"+data.country_name
        }).done(function (data) {
            prepareShippingPrice(data);
        })
    });
});

function prepareShippingPrice(data)
{
    return document.getElementById('shipping_form_price').value = data.shipping[0].price;
}
