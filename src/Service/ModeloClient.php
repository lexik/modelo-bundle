<?php

namespace Choosit\ModeloBundle\Service;

use Choosit\ModeloBundle\Exception\AuthenticateException;
use Choosit\ModeloBundle\Exception\AuthKeyMissingException;
use Choosit\ModeloBundle\Exception\HttpException;
use Choosit\ModeloBundle\Exception\ModelNotFound;
use Choosit\ModeloBundle\Exception\UnexpectedException;
use Choosit\ModeloBundle\Utils\XmlProcessor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * ModeloClient : An http client that performs and handle
 * HTTP request to modelo API
 */
class ModeloClient implements ModeloClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var string|null
     */
    private $authKey = null;

    public function __construct(HttpClientInterface $client, ?string $agencyCode = null, string $privateKey = null)
    {
        $this->client = $client;
        $this->setAuthKey($agencyCode, $privateKey);
    }

    public function setAuthKey(?string $agencyCode, ?string $privateKey): void
    {
        if (null !== $agencyCode && null !== $privateKey) {
            $this->authKey = md5($agencyCode.'&'.date('dmY').'&'.$privateKey);
        }
    }

    public function getAuthKey(): ?string
    {
        if (null === $this->authKey) {
            throw new AuthKeyMissingException();
        }

        return $this->authKey;
    }

    /**
     * Create a new document / contract by providing only the merge fields or by providing the complete document structure.
     *
     * @param string      $modelName Model or Doctype name from which to create the contract
     * @param bool        $isDocType true = Doctype, false = Model
     * @param string|null $xml       xml as string
     * @param bool        $outputPdf true = return pdf base64 encoded string, false return html
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws ModelNotFound
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function createContract(string $modelName, bool $isDocType = false, string $xml = null, bool $outputPdf = false): string
    {
        $modelId = $this->getModelIdByNameAndType($modelName, $isDocType);

        if (null === $modelId) {
            $type = $isDocType ? 'Doctype' : 'Model';
            throw new ModelNotFound($type.' was not found with specific '.$modelName.' name.');
        }

        return $this->createContractByModelId($modelId, $outputPdf, $xml);
    }

    /**
     * Returns the id of a model or doctype given its name
     *
     * @param string $modelName Model or Doctype name
     * @param bool   $isDocType true = Doctype, false = Model
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function getModelIdByNameAndType(string $modelName, bool $isDocType = false): ?string
    {
        $uri = $isDocType ? self::DOCTYPE_URI : self::MODELE_URI;

        $response = $this->client->request('GET', $uri, [
            'query' => $this->getGenericParams(),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        $xmlArray = $this->processXmlResponse($response->getContent(false), true);

        $modelId = null;
        $keyName = $isDocType ? self::DOCTYPE_REFERENCE_KEY : self::MODEL_TITLE_KEY;

        if (isset($xmlArray[self::ITEM_KEY])) {
            foreach ($xmlArray[self::ITEM_KEY] as $item) {
                if (trim(strtolower($item[$keyName])) === trim(strtolower($modelName))) {
                    $modelId = $item[self::MODEL_ID_KEY];
                }
            }
        }

        return $modelId;
    }

    /**
     * Get the list of fields returned corresponds to all the fields filled in the contract.
     * The name of the XML nodes corresponds to the name of the fields for inserting data from the associated model.
     *
     * @param string $contractId    id of the contract for which to retrieve the pre-filled content
     * @param bool   $returnAsArray true return as PHP array, false return SimpleXMLElement object
     *
     * @return array|mixed|string
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function getContractFilledContent(string $contractId, bool $returnAsArray = false)
    {
        $response = $this->client->request('GET', self::CONTRACT_URI, [
            'query' => array_merge($this->getGenericParams(), [
                '_id' => $contractId,
                'raw' => 1,
            ]),
        ]);
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        return $this->processXmlResponse($response->getContent(), $returnAsArray);
    }

    /**
     * @return array|mixed|string
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     *
     * @see ModeloClientInterface::PARAMS_DEFAULTS for available options
     */
    public function getAgencyContractList(array $params = [], bool $returnAsArray = false)
    {
        $response = $this->client->request('GET', self::CONTRACT_URI, [
            'query' => array_merge($this->getGenericParams(), $params),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        return $this->processXmlResponse($response->getContent(false), $returnAsArray);
    }

    /**
     * @return array|mixed|string
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function getAllAvailableModels(int $apiVersion = 3, bool $returnAsArray = false)
    {
        $response = $this->client->request('GET', self::MODELE_URI, [
            'query' => array_merge($this->getGenericParams(), [
                self::VERSION_PARAM_KEY => $apiVersion,
            ]),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        return $this->processXmlResponse($response->getContent(false), $returnAsArray);
    }

    /**
     * Returns the list of all model fields
     *
     * @param string $modelId       id of the model for which to retrieve all fields
     * @param bool   $returnAsArray true return as PHP array, false return SimpleXMLElement object
     *
     * @return array
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function getAllModelFields(string $modelId, bool $returnAsArray = false)
    {
        $response = $this->client->request('GET', self::MODELE_URI, [
            'query' => $this->getGenericParams(),
            '_id' => $modelId,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        return $this->processXmlResponse($response->getContent(false), $returnAsArray);
    }

    /**
     * Create a new document / contract by providing only the merge fields
     * or by providing the complete document structure.
     *
     * @param string      $modelId   id of the model for which to retrieve all fields
     * @param bool        $isDocType true = Doctype, false = Model
     * @param string|null $xml       xml as string
     * @param bool        $outputPdf true = return pdf base64 encoded string, false return html
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function createContractByModelId(string $modelId, string $contractId = null,  bool $isDocType = false, string $xml = null, bool $outputPdf = false): string
    {
        $uri = $isDocType ? self::DOCTYPE_URI : self::CONTRACT_URI;

        $keyName = $isDocType ? self::DOCTYPE_POST_ID_KEY : self::MODEL_POST_ID_KEY;
        $outputValue = $outputPdf ? 'pdf' : null;


        $response = $this->client->request('POST', $uri, [
            'query' => array_merge($this->getGenericParams(), [
                $keyName => $modelId,
                self::OUTPUT_PARAM_KEY => $outputValue,
                self::CONTRACT_ID_KEY => $contractId,
            ]),
            'body' => [
                'xml' => $xml,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        return $response->getContent();
    }

    /**
     * Updating a contract is done on the same principle as creating a contract
     * with the associative XML of the fields to be included in the contract
     *
     * @param string      $contractId id of the contract to update
     * @param string|null $xml        xml as string
     * @param bool        $outputPdf  true = return pdf base64 encoded string, false return html
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function updateContractById(string $contractId, string $xml = null, bool $outputPdf = false): string
    {
        $outputValue = $outputPdf ? 'pdf' : null;

        $response = $this->client->request('PUT', self::CONTRACT_URI, [
            'query' => array_merge($this->getGenericParams(), [
                '_id' => $contractId,
                self::OUTPUT_PARAM_KEY => $outputValue,
            ]),
            'body' => [
                'xml' => $xml,
            ],
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        return $response->getContent();
    }

    /**
     * Deactivation of a contract. This is found in the trash of the interface.
     * It can be reactivated by modifying the state field of the contract.
     *
     * @param string $contractId id of the contract to disable
     *
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function disableContract(string $contractId): string
    {
        $response = $this->client->request('DELETE', self::CONTRACT_URI, [
            'query' => array_merge($this->getGenericParams(), [
                '_id' => $contractId,
            ]),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        $xmlArray = $this->processXmlResponse($response->getContent(false), true);

        return $xmlArray[self::NOTICE_KEY];
    }

    /**
     * @return string
     * @throws AuthenticateException
     * @throws ClientExceptionInterface
     * @throws HttpException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws UnexpectedException
     */
    public function getNewContractId(): string
    {
        $response = $this->client->request('GET', self::CONTRACT_ID_URI, [
            'query' => array_merge($this->getGenericParams()),
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            $this->processError($response->getStatusCode(), $response->getContent(false));
        }

        $xmlArray = $this->processXmlResponse($response->getContent(false), true);

        return $xmlArray[self::CONTRACT_ID_KEY];
    }

    /**
     *  Get redundant parameters.
     *
     * @throws AuthKeyMissingException
     */
    private function getGenericParams(): array
    {
        return [
            self::AUTHKEY_PARAM_KEY => $this->getAuthKey(),
        ];
    }

    /**
     * @throws AuthenticateException
     * @throws HttpException
     * @throws UnexpectedException
     */
    private function processError(int $statusCode, string $content): void
    {
        switch ($statusCode) {
            case Response::HTTP_UNAUTHORIZED:
            case Response::HTTP_EXPECTATION_FAILED:
                $this->processXmlErrorResponse($statusCode, $content);
                // no break
            default:
                throw new HttpException();
        }
    }

    /**
     * @return mixed
     *
     * @throws AuthenticateException
     * @throws HttpException
     * @throws UnexpectedException
     */
    private function processXmlErrorResponse(int $statusCode, string $content)
    {
        $xmlObject = XmlProcessor::xmlToPhp($content);
        switch ($statusCode) {
            case Response::HTTP_UNAUTHORIZED:
                throw new AuthenticateException($xmlObject[self::ERROR_KEY]);
            case Response::HTTP_EXPECTATION_FAILED:
                throw new UnexpectedException($xmlObject[self::ERROR_KEY]);
            default:
                throw new HttpException();
        }
    }

    /**
     * @return array|mixed|string
     */
    private function processXmlResponse(string $content, bool $returnAsArray = false)
    {
        if ($returnAsArray) {
            return XmlProcessor::xmlToPhp($content);
        }

        return $content;
    }
}
