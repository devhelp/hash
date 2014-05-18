<?php

namespace Devhelp\Hash;


use Devhelp\Hash\Algorithm\HashAlgorithmInterface;
use Devhelp\Hash\Exception\UnknownAlgorithmException;

/**
 * Class or generating hashes using different algorithms. It supports php core algorithms by
 * default. Custom algorithms can be registered also
 */
class HashGenerator
{

    private $customAlgorithms = array();

    /**
     * registers custom algorithm under $algorithmName key
     *
     * @param string $algorithmName
     * @param \Closure|HashAlgorithmInterface $algorithm
     */
    public function register($algorithmName, $algorithm)
    {
        $this->checkIsAlgorithmValid($algorithm);

        $this->customAlgorithms[$algorithmName] = $algorithm;
    }

    /**
     * unregisters custom algorithm that is under $algorithmName key
     *
     * @param string $algorithmName
     */
    public function unregister($algorithmName)
    {
        unset($this->customAlgorithms[$algorithmName]);
    }

    /**
     * checks if there is an algorithm registered under $algorithmName key
     *
     * @param string $algorithmName
     * @return bool
     */
    public function isRegistered($algorithmName)
    {
        return $this->isCustom($algorithmName) || $this->isCore($algorithmName);
    }

    /**
     * generates hash for given $data using the algorithm registered under $algorithmName key
     *
     * @param string $algorithmName
     * @param string $data
     * @param array $options
     * @return string
     * @throws Exception\UnknownAlgorithmException
     */
    public function generate($algorithmName, $data, array $options = array())
    {
        $this->checkIsNameValid($algorithmName);

        if ($this->isCustom($algorithmName)) {
            return $this->callCustom($algorithmName, $data, $options);
        }

        if ($this->isCore($algorithmName)) {
            return $this->callCore($algorithmName, $data, $options);
        }

        throw new UnknownAlgorithmException("Algorithm '$algorithmName' is not registered");
    }

    private function checkIsNameValid($algorithmName)
    {
        if (!is_string($algorithmName)) {
            throw new \InvalidArgumentException('$algorithmName must be a string, got ' . gettype($algorithmName));
        }
    }

    private function checkIsAlgorithmValid($algorithm)
    {
        if (!($algorithm instanceof \Closure) && !($algorithm instanceof HashAlgorithmInterface)) {
            throw new \InvalidArgumentException(
                '$algorithm must be either instance of a Closure or HashAlgorithmInterface'
            );
        }
    }

    private function isCustom($algorithmName)
    {
        return isset($this->customAlgorithms[$algorithmName]);
    }

    private function callCustom($algorithmName, $data, $options)
    {
        $algorithm = $this->customAlgorithms[$algorithmName];

        if ($algorithm instanceof \Closure) {
            return $algorithm($data, $options);
        }

        if ($algorithm instanceof HashAlgorithmInterface) {
            return $algorithm->hash($data, $options);
        }
    }

    private function isCore($algorithmName)
    {
        return in_array($algorithmName, hash_algos());
    }

    private function callCore($algorithmName, $data, $options)
    {
        $rawOutput = isset($options['raw_output']) ? $options['raw_output'] : false;

        return hash($algorithmName, $data, $rawOutput);
    }
}
