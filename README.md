# IPAPZ_OglasnikZaLovackuOpremu

Advertisement page

3 authority levels: user, seller and admin

Everyone can view the page and all products listed

User:
  - can buy products
  - leave comments about the product
  - view bought products 
  - apply for seller position
  
Seller: 
  - add new products 
  - edit product info, image and visibility 
  - check who bought their product and confirm or delete the buy
  - has all rights that user has
  
Admin:
  - allow users to become sellers
  - add and edit categories and products
  - hide categories and products
  - view list of registered users and edit their information
  - list of bought products and all info related to the product and user who bought it
  - has all the rights as user and seller
  
TO DO:
  - Vezanje proizvoda na više kategorija
  - Dodati mogućnost dodavanja proizvoljnog url-a za pojedini produkt koji će koristiti isti kontroler kao i originalni url
  - Dodati wishlist od customera. Kada je proizvod nazad "in stock" svi koji ga imaju u wishlistu dobiju one-time notifikaciju pored proizvoda u izlistavanju wishlista
  - Dodati mogućnost kreiranja kupon kodova i korištenja kupon kodova od strane kupca
  - Dodati mogućnost pravljenja custom page-eva u adminu npr. Home page, O nama, FAQ, i slično. Ti page-ovi mogu imati custom url i primaju html kao input koji outputaju na frontendu
  - Dodati konfiguraciju shipping metoda u admin. Cijena shippinga po državi da se može manualno i mora se moći importati iz .csv file-a
  - Sold items - autocomplete kada se krene upisivati ime kupca/naziv proizvoda (autosugest ajax, javascript, on type, nakon 3-5 slova, proizvoljno)
  - Kada se klikne na ime prodavača, ajaxom napraviti grid update (nakon unosa svakog slova, pritiskom entera ili micanja sa polja odraditi query da privuce prodane proizvode)
  - Vrste plaćanja : po pouzeću, Paypal payment gateway implementacija. Način plaćanja mora biti vidljiv u admin orderima i mora se moći osposobiti/ugasiti u adminu
  - Poželjno je promisliti o implementaciji te modificirati dodatne stvari kako bi korisniku približili aplikaciju. Slobodno dodati nove funkcionalnosti. Postojeće funkcionalnosti ukratko dokumentirati u features/{određeni_entity}.md kako bi aplikacija bila preglednija i lakša za upoznavanje
  
Extra:
  - Dugačke funkciji razdvojiti u vise malih