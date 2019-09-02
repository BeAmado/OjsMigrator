unit-tests()
{
    unitTestsDir=$(echo "$(pwd)/unit")
    
    bootstrap=$(echo "$(pwd)/../includes/bootstrap.php")

    phpunitFile=$(echo "$(pwd)/../vendor/bin/phpunit")

    php7.2 $phpunitFile --bootstrap $bootstrap --colors=always --testdox $unitTestsDir
}

unit-tests
