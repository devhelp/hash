<?php

namespace spec\Devhelp\Hash;


use Devhelp\Hash\Algorithm\HashAlgorithmInterface;
use Devhelp\Hash\Exception\UnknownAlgorithmException;
use PhpSpec\ObjectBehavior;

class HashGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Devhelp\Hash\HashGenerator');
    }

    function it_can_register_custom_hash_algorithm(HashAlgorithmInterface $algorithm)
    {
        $algorithmName = 'my_algorithm';

        $this->register($algorithmName, $algorithm);
        $this->isRegistered($algorithmName)->shouldReturn(true);
    }

    function it_can_register_a_closure_as_hash_algorithm()
    {
        $algorithmName = 'my_algorithm';
        $algorithm = function () {

        };

        $this->register($algorithmName, $algorithm);
        $this->isRegistered($algorithmName)->shouldReturn(true);
    }

    function it_throws_exception_if_algorithm_to_be_registered_is_not_a_closure_nor_implements_HashAlgorithmInterface()
    {
        $exception = new \InvalidArgumentException(
            '$algorithm must be either instance of a Closure or HashAlgorithmInterface'
        );

        $this->shouldThrow($exception)->duringRegister('my_algorithm', true);
    }

    function it_registers_php_core_hash_algorithms_by_default()
    {
        $this->isRegistered('md5')->shouldReturn(true);
        $this->isRegistered('sha256')->shouldReturn(true);
        $this->isRegistered('sha512')->shouldReturn(true);
    }

    function it_can_unregister_custom_hash_algorithm(HashAlgorithmInterface $algorithm)
    {
        $algorithmName = 'my_algorithm';

        $this->register($algorithmName, $algorithm);

        $this->unregister($algorithmName);

        $this->isRegistered($algorithmName)->shouldReturn(false);
    }

    function it_cant_unregister_php_core_hash_algorithms()
    {
        $this->unregister('md5');
        $this->unregister('sha256');
        $this->unregister('sha512');

        $this->isRegistered('md5')->shouldReturn(true);
        $this->isRegistered('sha256')->shouldReturn(true);
        $this->isRegistered('sha512')->shouldReturn(true);
    }

    function it_throws_exception_during_generate_if_algorithm_name_is_not_a_string()
    {
        $exception = new \InvalidArgumentException('$algorithmName must be a string, got array');

        $this->shouldThrow($exception)->duringGenerate(array(), 'some_data');
    }

    function it_throws_exception_during_generate_if_algorithm_is_not_registered()
    {
        $algorithmName = 'my_algorithm';

        $exception = new UnknownAlgorithmException("Algorithm 'my_algorithm' is not registered");

        $this->shouldThrow($exception)->duringGenerate($algorithmName, 'some_data');
    }

    function it_calls_php_core_algorithm_if_there_is_no_custom_hash_algorithm_defined_under_the_same_name()
    {
        $this->generate('md5', 'some_data')->shouldReturn('0d9247cbce34aba4aca8d5c887a0f0a4');
    }

    function it_calls_custom_hash_algorithm_rather_than_php_core_if_it_is_registered_under_the_same_name(
        HashAlgorithmInterface $algorithm
    ) {
        $algorithmName = 'md5';
        $data = 'some_data';
        $hash = 'my_hash';

        $algorithm->hash($data, array())->willReturn($hash);

        $this->register($algorithmName, $algorithm);

        $this->generate($algorithmName, $data)->shouldReturn($hash);
    }

    function it_calls_custom_hash_algorithm_if_it_is_registered_and_is_a_closure()
    {
        $key = 'key';
        $value = 'value';
        $data = 'some_data';
        $options = array($key => $value);

        $hash = "hash for $data with params with value: $value";

        $algorithm = function ($data, $options) use ($key) {
            return "hash for $data with params with value: {$options[$key]}";
        };

        $algorithmName = 'my_algorithm';

        $this->register($algorithmName, $algorithm);

        $this->generate($algorithmName, $data, $options)->shouldReturn($hash);
    }

    function it_calls_custom_hash_algorithm_if_it_is_registered_and_implements_hash_algorithm_interface(
        HashAlgorithmInterface $algorithm
    ) {
        $algorithmName = 'my_algorithm';
        $data = 'some_data';
        $hash = 'my_hash';

        $algorithm->hash($data, array())->willReturn($hash);

        $this->register($algorithmName, $algorithm);

        $this->generate($algorithmName, $data)->shouldReturn($hash);
    }
}
