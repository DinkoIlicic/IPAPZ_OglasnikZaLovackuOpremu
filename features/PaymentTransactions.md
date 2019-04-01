Contains functions for all payment methods

Functions:
    
    - paypalShow() - Shows paypal page to user when he chooses the method
    - payment() - Checks and validates everything regarding paypal, if buy is confirmed, customer is send to my items page
    - gateway() - Creates braintree gateway
    - invoiceShow() - Shows invoice page to user when he chooses the method
    - choseOnDeliveryMethod - Checks and validates regarding invoice, if buy is confirmed, customer is send to my items page
    - createDomPdf() - When user confirms buy through invoice method, pdf will be created with all infos regarding the buy
    - downloadPdf()
    - downloadPdfAdmin() and downloadPdfSeller() - extra checking for seller
    - confirmInvoicePayment() - when user pays with invoice seller and admin can confirm the payment
    - deletePaymentTransaction() - option to delete payment transaction if problem occures 
    - returnPathToInvoice()