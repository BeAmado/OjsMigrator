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
    rm -rf "$(dirname $0)/dbdriver"
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

all-tests()
{
    php7.2 \
        $(echo "$(dirname $0)/../vendor/bin/phpunit") \
        --bootstrap=$(echo "$(dirname $0)/includes/bootstrap.php") \
        --colors=always \
        --testdox \
        $(dirname $0)
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

set-test-db-driver()
{
    echo $1 > "$(dirname $0)/dbdriver"
}

run-tests()
{
    if [[ $@ =~ '--mysql' ]]
    then
        set-test-db-driver 'mysql'
    else
        set-test-db-driver 'sqlite'
    fi

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
        all-tests
    fi
}

run-tests $@
