<?php

namespace BeAmado\OjsMigrator;

trait TestStub
{
    /**
     * Stub for calling the classes methods, 
     * most specially the private and protected ones.
     *
     * @param string $method - The name of the method to be called
     * @param array $args - The arguments for the method
     * @return mixed
     */
    public function callMethod($method, $args = array())
    {
        if (!\is_array($args)) {
            $args = array($args);
        }

        return \call_user_func_array(
            array($this, $method),
            $args
        );
    }

}
