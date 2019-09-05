unit-tests()
{
    unitTestsDir=$(echo "$(dirname $0)/unit")
    
    bootstrap=$(echo "$(dirname $0)/../includes/bootstrap.php")

    phpunitFile=$(echo "$(dirname $0)/../vendor/bin/phpunit")

    php7.2 $phpunitFile --bootstrap $bootstrap --colors=always --testdox $unitTestsDir
}

unit-tests
