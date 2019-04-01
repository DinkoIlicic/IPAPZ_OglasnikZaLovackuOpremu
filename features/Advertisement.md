Advertisement controller contains all functions regarding starting page and what customers see. 

Functions:

    - index() - Render starting page with categories and wishlist
    - redirectToIndex() - Redirect to index
    - getAllVisibleCategories()
    - showProductsPerCategory() - Find all products per category that are allowed by seller and admin
    - Each customer can apply for seller - applyForSeller()
    - checkProduct() - Function contains multiple forms. Form for buying product (userBuyProduct()), form for sending email to seller and form for commenting on product
    - userBuyProduct() - Form for buying products if submitted and valid will reserve product for customer and send him to page where he can choose payment method and 
      shipping address
    - choosePaymentOption() - Page for choosing payment options that are allowed by admin and shipping address that he can insert new or choose from existing
    - checkCouponCode()
    - commentOnProduct()
    - sendMail()
    - myItems() - Shows all items reserved by customer and gives option to buy if they did not choose payment method yet and paid
    - myWishList()
    - addProductToWishList() - customer can add products to wishlist and when they are available again it will show them
    - removeProductToWishList()
    - excelUser() - Shows all bought products from user in excel file
    - renderCustomPage() - Renders pages created by admin
    - searchBar() - Creates search bar