**httpie**

http -a jane.doe@example.com:test123 --verify=no POST https://127.0.0.1:8000/api/tools toolNumber=test

**doctrine**

* drop db:
symfony php bin/console d:d:d --force

* create db:
symfony php bin/console d:d:c

* create migration: 
symfony php bin/console d:m:diff

* migrate:
symfony php bin/console d:m:m

* load fixtures:
symfony php bin/console d:f:l

* Execute Tests:
symfony php ./bin/phpunit

* Show failed messenger messages:
bin/console m:f:s

* Generate messages:
bin/console app:generate

* Consume messages:
bin/console messenger:consume async
bin/console messenger:consume async -vv
bin/console messenger:consume async &> ./log.txt
bin/console messenger:failed:retry -vv

* ChromeDriver update:
cd /Users/steffen/Documents/Produktion/ligapi2/vendor/symfony/panther/chromedriver-bin
./update.sh

* Kill PHP process:
kill -15 %id%

