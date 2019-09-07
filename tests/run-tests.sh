phpunit-begin()
{
    echo
    echo "========== PHPUnit $1 tests =========="
    echo
    echo
}

phpunit-end()
{
    echo
    echo 
    echo "========= End of $1 tests ========="
    echo
}

phpunit-run()
{
    phpunit-begin $(basename $1)

    php7.2 \
        $(echo "$(dirname $0)/../vendor/bin/phpunit") \
        --bootstrap=$(echo "$(dirname $0)/../includes/bootstrap.php") \
        --colors=always \
        --testdox \
        $1

    phpunit-end $(basename $1)
}

unit-tests()
{
    phpunit-run $(echo "$(dirname $0)/unit")
}

integration-tests()
{
    phpunit-run $(echo "$(dirname $0)/integration")
}

bootstrap-tests()
{
    phpunit-run  $(echo "$(dirname $0)/bootstrap")
}

functional-tests()
{
    phpunit-run $(echo "$(dirname $0)/functional")
}

run-tests()
{
    if [[ $@ =~ '--bootstrap' ]]
    then
        bootstrap-tests
    fi
    
    if [[ $@ =~ '--unit' ]]
    then
        unit-tests
    fi

    if [[ $@ =~ '--functional' ]]
    then
        functional-tests
    fi

    if [[ $@ =~ '--integration' ]]
    then
        integration-tests
    fi

    if [[ $@ =~ '--all' ]]
    then
        run-tests --bootstrap --unit --functional --integration
    fi
}

run-tests $@
