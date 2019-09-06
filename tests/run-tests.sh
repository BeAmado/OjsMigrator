my-run()
{
    php7.2 \
        $(echo "$(dirname $0)/../vendor/bin/phpunit") \
        --bootstrap=$(echo "$(dirname $0)/../includes/bootstrap.php") \
        --colors=always \
        --testdox \
        $1
}

unit-tests()
{
    my-run $(echo "$(dirname $0)/unit")
}

integration-tests()
{
    my-run $(echo "$(dirname $0)/integration")
}

bootstrap-tests()
{
    my-run $(echo "$(dirname $0)/bootstrap")
}

functional-tests()
{
    my-run $(echo "$(dirname $0)/functional")
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
