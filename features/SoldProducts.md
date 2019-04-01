Contains all functions regarding searching and showing bought products per user and per product for both admin and seller. Seller has extra check to see if the product is his own. They can confirm invoice buy and delete the whole bought product.

Functions: 

    - handleSearchRequestPerUser() - checks for user in database
    - handleSearchRequestPerProduct() - checks for product in database
    - ajaxListPersonPerUserAdmin() - gets all products bought from user (admin)
    - ajaxListPersonPerProductAdmin() - gets all buyers for certain product (admin)
    - ajaxListPersonPerUserSeller() - gets his own products bought from user (seller)
    - ajaxListPersonPerProductSeller - gets all buyers for his product (seller)
    - listOfBoughtItemsPerUser() - renders twig with all information regarding bought products per user (admin)
    - listOfBoughtItemsPerProductAdmin() - renders twig with all information regarding buyers from certain product per user (admin)
    - listOfBoughtItemsPerUserSeller() - renders twig with all informations regarding bought products per user, only sellers products (seller)
    - listOfBoughtItemsPerProductSeller() - renders twig with all informations regarding buyers for his own product (seller)
    - confirmBuy() - option to confirm invoice buy
    - deleteProductItem() - option to delete bought item from database
    - Other functions are there to render twig for admin and seller regarding bought products
    