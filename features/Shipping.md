Contains

Functions:

    - showShippingPrice() - contains 3 forms: form for default shipping price if shipping price for certain country is not added. 
    Form for country shipping price where admin can add price for each country. Form to import shipping prices through csv file
    - handleSearchRequestShipping() - ajax gets price for selected country and calls returnJsonObjectShipping() 
    - returnJsonObjectShipping() - returns jsonResponse to previous function
    - ajaxListShipping() - renders the price from handleSearchRequestShipping()
    - bulkAll() - to insert payment methods, default price for shipping and all countries for shipping into database
    