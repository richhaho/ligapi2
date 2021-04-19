* Update Chromedriver:
in /vendor/symfony/panther/chromedriver-bin:
./update.sh

* Start messenger job
bin/console messenger:consume async

* Generate messenges to consume
bin/console app:generate


Workflow:
1) Material/Order source/Order gets crawler status "new"
2) MessengerCommand creates Message (bin/console app:generate)
3) Messages get consumed by messenger job (bin/console messenger:consume async)
4) Crawler.php finds the right Reader (findReader($supplier))
5) Reader (e.g. GcReader) gets the data
6) Crawler.php processes the data (updates material/order source/order)


**FUNCTIONALITY**

A) Extract article data
1. Log in to web shop
2. Search for transmitted string
3. Open article details
4. Extract data
5. Repeat for next item
6. Log out after last item

B) Get current purchasing price
1. Log in to web shop
2. Search for transmitted order number
3. Find order number in search result
4. Get current purchasing price from list
5. Repeat 2-4 for next item
6. Log out after last item

C) Transmit shopping basket
1. Log in to web shop
2. Search for transmitted order number
3. Find order number in search result
4. Add transmitted amount to basket
5. Repeat for next item
6. Proceed to check out after last item
7. Stop before order placement
8. Log out
