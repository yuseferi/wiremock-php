<?php

namespace WireMock\Client;

use WireMock\Matching\RequestPattern;
use WireMock\Stubbing\StubMapping;

class MappingBuilder
{
    /** @var string A string representation of a GUID  */
    private $_id;
    /** @var RequestPattern */
    private $_requestPattern;
    /** @var ResponseDefinitionBuilder */
    private $_responseDefinitionBuilder;
    /** @var array of string -> ValueMatchingStrategy */
    private $_headers = array();
    /** @var array of string -> ValueMatchingStrategy */
    private $_cookies = array();
    /** @var array of string -> ValueMatchingStrategy */
    private $_queryParameters = array();
    /** @var array of ValueMatchingStrategy */
    private $_requestBodyPatterns = array();
    /** @var int */
    private $_priority;
    /** @var ScenarioMappingBuilder */
    private $_scenarioBuilder;

    public function __construct(RequestPattern $requestPattern)
    {
        $this->_requestPattern = $requestPattern;
        $this->_scenarioBuilder = new ScenarioMappingBuilder();
    }

    /**
     * @param string $id A string representation of a GUID
     * @return MappingBuilder
     */
    public function withId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * @param ResponseDefinitionBuilder $responseDefinitionBuilder
     * @return MappingBuilder
     */
    public function willReturn(ResponseDefinitionBuilder $responseDefinitionBuilder)
    {
        $this->_responseDefinitionBuilder = $responseDefinitionBuilder;
        return $this;
    }

    /**
     * @param int $priority
     * @return MappingBuilder
     */
    public function atPriority($priority)
    {
        $this->_priority = $priority;
        return $this;
    }

    /**
     * @param string $headerName
     * @param ValueMatchingStrategy $valueMatchingStrategy
     * @return MappingBuilder
     */
    public function withHeader($headerName, ValueMatchingStrategy $valueMatchingStrategy)
    {
        $this->_headers[$headerName] = $valueMatchingStrategy->toArray();
        return $this;
    }

    /**
     * @param string $name
     * @param ValueMatchingStrategy $valueMatchingStrategy
     * @return $this
     */
    public function withQueryParam($name, ValueMatchingStrategy $valueMatchingStrategy)
    {
        $this->_queryParameters[$name] = $valueMatchingStrategy->toArray();
        return $this;
    }

    /**
     * @param string $cookieName
     * @param ValueMatchingStrategy $valueMatchingStrategy
     * @return MappingBuilder
     */
    public function withCookie($cookieName, ValueMatchingStrategy $valueMatchingStrategy)
    {
        $this->_cookies[$cookieName] = $valueMatchingStrategy->toArray();
        return $this;
    }

    /**
     * @param ValueMatchingStrategy $valueMatchingStrategy
     * @return MappingBuilder
     */
    public function withRequestBody(ValueMatchingStrategy $valueMatchingStrategy)
    {
        $this->_requestBodyPatterns[] = $valueMatchingStrategy->toArray();
        return $this;
    }

    /**
     * @param string $scenarioName
     * @return MappingBuilder
     */
    public function inScenario($scenarioName)
    {
        $this->_scenarioBuilder->withScenarioName($scenarioName);
        return $this;
    }

    /**
     * @param string $requiredScenarioState
     * @return MappingBuilder
     */
    public function whenScenarioStateIs($requiredScenarioState)
    {
        $this->_scenarioBuilder->withRequiredState($requiredScenarioState);
        return $this;
    }

    /**
     * @param string $newScenarioState
     * @return MappingBuilder
     */
    public function willSetStateTo($newScenarioState)
    {
        $this->_scenarioBuilder->withNewScenarioState($newScenarioState);
        return $this;
    }

    /**
     * @return StubMapping
     * @throws \Exception
     */
    public function build()
    {
        $responseDefinition = $this->_responseDefinitionBuilder->build();
        $this->_requestPattern->setHeaders($this->_headers);
        $this->_requestPattern->setCookies($this->_cookies);
        $this->_requestPattern->setQueryParameters($this->_queryParameters);
        if (!empty($this->_requestBodyPatterns)) {
            $this->_requestPattern->setBodyPatterns($this->_requestBodyPatterns);
        }
        return new StubMapping(
            $this->_requestPattern,
            $responseDefinition,
            $this->_id,
            $this->_priority,
            $this->_scenarioBuilder->build());
    }
}
