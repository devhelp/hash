<?php

namespace Devhelp\Hash\Algorithm;


interface HashAlgorithmInterface
{
    /**
     * generates hash for given string ($data)
     *
     * @param string $data
     * @param array $options
     * @return mixed
     */
    public function hash($data, array $options = array());
}
