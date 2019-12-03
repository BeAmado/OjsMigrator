phpunit-begin()
{
    echo
    echo "========== PHPUnit $1 tests =========="
    echo
    echo
}

phpunit-end()
{
    rm -rf "$(dirname $0)/_data/ojs2"
    rm -rf "$(dirname $0)/_data/db_sandbox"
    rm -rf "$(dirname $0)/_data/sandbox"
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
        --bootstrap=$(echo "$(dirname $0)/includes/bootstrap.php") \
        --colors=always \
        --testdox \
        $1

    phpunit-end $(basename $1)
}

form-filename()
{
    filename="$(echo $(dirname $0)/$1/$2)Test.php"
}

unit-tests()
{
    if [ -e $filename ]
    then
        phpunit-run $filename
    else
        phpunit-run $(echo "$(dirname $0)/unit")
    fi
}

integration-tests()
{
    if [ -e $filename ]
    then
        phpunit-run $filename
    else
        phpunit-run $(echo "$(dirname $0)/integration")
    fi
}

bootstrap-tests()
{
    if [ -e $filename ]
    then
        phpunit-run $filename
    else
        phpunit-run  $(echo "$(dirname $0)/bootstrap")
    fi
}

functional-tests()
{
    if [ -e $filename ]
    then
        phpunit-run $filename
    else
        phpunit-run $(echo "$(dirname $0)/functional")
    fi
}

run-tests()
{
    if [[ $@ =~ '--bootstrap' ]]
    then
        form-filename bootstrap $2
        bootstrap-tests
    fi
    
    if [[ $@ =~ '--unit' ]]
    then
        form-filename unit $2
        unit-tests
    fi

    if [[ $@ =~ '--functional' ]]
    then
        form-filename functional $2
        functional-tests
    fi

    if [[ $@ =~ '--integration' ]]
    then
        form-filename integration $2
        integration-tests
    fi

    if [[ $@ =~ '--all' ]]
    then
        run-tests --bootstrap --unit --functional --integration
    fi
}

run-tests $@
