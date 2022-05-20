<?php

namespace Choosit\ModeloBundle\Tests\Unit;

use Choosit\ModeloBundle\Exception\AuthenticateException;
use Choosit\ModeloBundle\Exception\AuthKeyMissingException;
use Choosit\ModeloBundle\Exception\HttpException;
use Choosit\ModeloBundle\Exception\ModelNotFound;
use Choosit\ModeloBundle\Exception\UnexpectedException;
use Choosit\ModeloBundle\Service\ModeloClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ModeloHttpClientTest extends TestCase
{
    private function getBase64PdfStringContent(string $filename): string
    {
        $filename = __DIR__.'/mocks/'.$filename.'.pdf';
        $handler = fopen($filename, 'r');

        return base64_encode(fread($handler, filesize($filename)));
    }

    private function getFileStringContent(string $filename): string
    {
        $filename = __DIR__.'/mocks/'.$filename;
        $handler = fopen($filename, 'r');

        return fread($handler, filesize($filename));
    }

    /*
     * DATA PROVIDERS
     */
    public function technicalIssueAndBadRequestProvider(): array
    {
        return [
            'authentication' => [
                AuthenticateException::class,
                'Error related message',
                new MockResponse($this->getFileStringContent('authentication_error.xml'),
                    [
                        'http_code' => 401,
                        'response_headers' => ['Content-Type: application/xml; charset=utf-8'],
                    ]),
            ],
            'unauthorized' => [
                UnexpectedException::class,
                'Expectation Failed',
                new MockResponse($this->getFileStringContent('unauthorized_error.xml'),
                    [
                        'http_code' => 417,
                        'response_headers' => ['Content-Type: application/xml; charset=utf-8'],
                    ]),
            ],
            'not-allowed' => [
                HttpException::class,
                'A technical error occurred while executing request.',
                new MockResponse('any body',
                    [
                        'http_code' => 500,
                        'response_headers' => ['Content-Type: application/xml; charset=utf-8'],
                    ]),
            ],
        ];
    }

    public function listMockResponseProvider(): array
    {
        return [
            'model' => [
                false,
                'Model was not found with specific nonExistent name.',
                new MockResponse($this->getFileStringContent('model_list.xml'),
                    [
                        'http_code' => 200,
                        'response_headers' => ['Content-Type: application/xml; charset=utf-8'],
                    ]),
            ],
            'doctype' => [
                true,
                'Doctype was not found with specific nonExistent name.',
                new MockResponse($this->getFileStringContent('doctype_list.xml'),
                    [
                        'http_code' => 200,
                        'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                    ]),
            ],
        ];
    }

    public function createContratResponseProvider(): array
    {
        return [
            'model' => [
                false,
                'Mandat exclusif de vente',
                'responses' => [
                        new MockResponse($this->getFileStringContent('model_list.xml'),
                        [
                            'http_code' => 200,
                            'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                        ]),
                        new MockResponse($this->getBase64PdfStringContent('mandat_exclusif_de_vente'),
                        [
                            'http_code' => 200,
                            'response_headers' => [
                                    'Content-Disposition: inline; filename="mandat_exclusif_de_vente-62417e2dd3ed58086f4734f3.pdf";',
                                    'Content-Type: application/pdf',
                                ],
                        ]),
                    ],
                true,
            ],
            'doctype' => [
                true,
                'template_test',
                'responses' => [
                        new MockResponse($this->getFileStringContent('doctype_list.xml'),
                        [
                            'http_code' => 200,
                            'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                        ]),
                        new MockResponse($this->getFileStringContent('modelo.html'),
                        [
                            'http_code' => 200,
                            'response_headers' => ['Content-Type: text/html; charset=utf-8'],
                        ]),
                    ],
                false,
            ],
        ];
    }

    public function updateContractResponseProvider(): array
    {
        return [
            'simple-update-contract' => [
                'responses' => [
                    new MockResponse($this->getBase64PdfStringContent('mandat_exclusif_de_vente'),
                        [
                            'http_code' => 200,
                            'response_headers' => [
                                'Content-Disposition: inline; filename="mandat_exclusif_de_vente-62417e2dd3ed58086f4734f3.pdf";',
                                'Content-Type: application/pdf',
                            ],
                        ]),
                ],
                true,
            ],
        ];
    }

    public function getContentFilledResponseProvider(): array
    {
        return [
          'content-filled' => [
              include __DIR__.'/mocks/content_filled.php',
              new MockResponse($this->getFileStringContent('content_filled.xml'), [
                  'http_code' => 200,
                  'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
              ]),
          ],
        ];
    }

    public function getAllModelFieldsResponseProvider(): array
    {
        return [
            'all-model-fields' => [
                include __DIR__.'/mocks/full_model_fields.php',
                new MockResponse($this->getFileStringContent('full_model_fields.xml'), [
                    'http_code' => 200,
                    'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                ]),
            ],
        ];
    }

    public function disableContractResponseProvider(): array
    {
        return [
            'disable-contract-success' => [
                'The recording has been successfully deleted',
                new MockResponse($this->getFileStringContent('disable_contract_success.xml'), [
                    'http_code' => 200,
                    'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                ]),
            ],
        ];
    }

    public function getAgencyContractListProvider(): array
    {
        return [
            'contract-list' => [
                include __DIR__.'/mocks/contracts_list.php',
                new MockResponse($this->getFileStringContent('contracts_list.xml'), [
                    'http_code' => 200,
                    'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                ]),
            ],
        ];
    }

    public function getAllAvailableModelsProvider(): array
    {
        return [
            'contract-list' => [
                include __DIR__.'/mocks/model_list.php',
                new MockResponse($this->getFileStringContent('model_list.xml'), [
                    'http_code' => 200,
                    'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                ]),
            ],
        ];
    }

    public function getNewContractIdProvider(): array
    {
        return [
            'contract-id' => [
                '6255490fccf7ec67d51635fb',
                new MockResponse($this->getFileStringContent('contract_id.xml'), [
                    'http_code' => 200,
                    'response_headers' => ['Content-Type: text/xml; charset=utf-8'],
                ]),
            ]
        ];
    }

    /*
     * TESTS
     */

    public function testServiceWithoutAuthkeyMustThrowAuthkeyMisingException(): void
    {
        $modeloApiService = new ModeloClient(new MockHttpClient([],'https://example.com'));
        $this->expectException(AuthKeyMissingException::class);
        $this->expectExceptionMessage('authKey couldn\'t generated, you may have forgot to fill in the configuration file or setAuthKey with agencyCode and/or privateKey in your config file.');
        $modeloApiService->getAuthKey();
    }

    public function testServiceWithAuthkeyShouldReturnAuthkey(): void
    {
        $expected = md5('test&'.date('dmY').'&test');
        $modeloApiService = new ModeloClient(new MockHttpClient([],'https://example.com'), 'test', 'test');
        $authkey = $modeloApiService->getAuthKey();
        $this->assertEquals($expected, $authkey);
    }

    /**
     * @dataProvider technicalIssueAndBadRequestProvider
     */
    public function testGetModelIdByNameAndTypeWithHttpErrorShouldThrowException(string $expectedException, string $expectedMessageException, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessageException);

        $modeloApiService->getModelIdByNameAndType('test');
    }

    /**
     * @dataProvider technicalIssueAndBadRequestProvider
     */
    public function testGetContractFilledContentWithHttpErrorShouldThrowException(string $expectedException, string $expectedMessageException, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessageException);

        $modeloApiService->getContractFilledContent('623c8286138c4029e8576c33');
    }

    /**
     * @dataProvider technicalIssueAndBadRequestProvider
     */
    public function testGetAllModelFieldsWithHttpErrorShouldThrowException(string $expectedException, string $expectedMessageException, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessageException);

        $modeloApiService->getAllModelFields('623c8286138c4029e8576c33');
    }

    /**
     * @dataProvider technicalIssueAndBadRequestProvider
     */
    public function testDisableContractWithHttpErrorShouldThrowException(string $expectedException, string $expectedMessageException, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessageException);

        $modeloApiService->disableContract('623c8286138c4029e8576c33');
    }

    /**
     * @dataProvider technicalIssueAndBadRequestProvider
     */
    public function testCreateContractWithHttpErrorShouldThrowException(string $expectedException, string $expectedMessageException, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessageException);

        $modeloApiService->createContractByModelId('623c8286138c4029e8576c33');
    }

    /**
     * @dataProvider technicalIssueAndBadRequestProvider
     */
    public function testUpdateContractWithHttpErrorShouldThrowException(string $expectedException, string $expectedMessageException, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessageException);

        $modeloApiService->updateContractById('623c8286138c4029e8576c33', '<xml></xml>');
    }

    /**
     * @dataProvider listMockResponseProvider
     */
    public function testCreateContratFromNonExistentModelShouldThrowModelNotFound(bool $isDoctype, string $expectedMessage, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $this->expectException(ModelNotFound::class);
        $this->expectExceptionMessage($expectedMessage);
        $modeloApiService->createContract('nonExistent', $isDoctype, '<xml></xml>', false);
    }

    /**
     * @dataProvider createContratResponseProvider
     */
    public function testCreateContratFromExistentModelShouldReturnString(bool $isDoctype, string $searchName, array $responses, bool $outputPdf): void
    {
        if ($outputPdf) {
            $expected = $this->getBase64PdfStringContent('mandat_exclusif_de_vente');
        } else {
            $expected = $this->getFileStringContent('modelo.html');
        }

        $mockHttpClient = new MockHttpClient($responses,'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->createContract($searchName, $isDoctype, '<xml></xml>', $outputPdf);

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider updateContractResponseProvider
     */
    public function testUpdateContractFromExistentModelShouldReturnString(array $responses, bool $outputPdf): void
    {
        if ($outputPdf) {
            $expected = $this->getBase64PdfStringContent('mandat_exclusif_de_vente');
        } else {
            $expected = $this->getFileStringContent('modelo.html');
        }

        $mockHttpClient = new MockHttpClient($responses,'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->updateContractById('623c8286138c4029e8576c33', '<xml></xml>', $outputPdf);

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider getContentFilledResponseProvider
     */
    public function testGetContractFilledContentShouldReturnContentAsArray(array $expected, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->getContractFilledContent('623c8286138c4029e8576c33', true);

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider getAllModelFieldsResponseProvider
     */
    public function testGetAllModelFieldsShouldReturnFieldsAsArray(array $expected, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->getAllModelFields('582166e875d5585c66bb3256', true);

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider disableContractResponseProvider
     */
    public function testDisableContractShouldReturnNoticeMessage(string $expected, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->disableContract('623c8286138c4029e8576c33');

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider getAgencyContractListProvider
     */
    public function testGetAgencyContractListShouldReturnContractListAsArray(array $expected, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->getAgencyContractList([], true);

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider getAllAvailableModelsProvider
     */
    public function testGetAllAvailableModels(array $expected, MockResponse $response): void
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->getAllAvailableModels(3, true);

        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider getNewContractIdProvider
     */
    public function testGetNewContractId(string $expected, MockResponse $response)
    {
        $mockHttpClient = new MockHttpClient([$response],'https://example.com');
        $modeloApiService = new ModeloClient($mockHttpClient, 'test', 'test');

        $return = $modeloApiService->getNewContractId();

        $this->assertEquals($expected, $return);
    }
}
