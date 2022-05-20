<?php

namespace Choosit\ModeloBundle\Tests\Unit;

use Choosit\ModeloBundle\Utils\XmlProcessor;
use PHPUnit\Framework\TestCase;

class XmlProcessorTest extends TestCase
{
    private function getFileStringContent(string $filename): string
    {
        $filename = __DIR__.'/mocks/'.$filename;
        $handler = fopen($filename, 'r');

        return fread($handler, filesize($filename));
    }

    /**
     * @return array[]
     */
    public function phpToXmlProvider(): array
    {
        return [
            'simple-php-array' => [
                $this->getFileStringContent('simple_xml.xml'),
                [
                    'qui_est_le_vendeur' => 'Designation of the seller',
                    'prenom_nom_vendeur' => 'Seller\'s fullname',
                    'indiquer_un_numero_de_telephone_et_email_vendeur' => 'Indicate a seller\'s phone number and email',
                    'tel_vendeur' => 'Seller\'s phone number',
                    'mail_vendeur' => 'Seller email',
                    'quelle_est_la_situation_matrimoniale_vendeur' => 'Seller\'s marital status',
                    'mairie_mariage_vendeur' => 'Seller\'s Wedding town hall',
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public function xmlToPhpProvider(): array
    {
        return [
            'simple-xml' => [
                [
                    'qui_est_le_vendeur' => 'Designation of the seller',
                    'prenom_nom_vendeur' => 'Seller\'s fullname',
                    'indiquer_un_numero_de_telephone_et_email_vendeur' => 'Indicate a seller\'s phone number and email',
                    'tel_vendeur' => 'Seller\'s phone number',
                    'mail_vendeur' => 'Seller email',
                    'quelle_est_la_situation_matrimoniale_vendeur' => 'Seller\'s marital status',
                    'mairie_mariage_vendeur' => 'Seller\'s Wedding town hall',
                ],
                $this->getFileStringContent('simple_xml.xml'),
            ],
            'xml-with-attributes' => [
                [
                    'menu' => [
                        [
                            '@data-id' => 'etes_vous_adherent_d_une_caisse_de_garantie',
                            '@data-uid' => 'a86005227aaf7be5b52273f46d61a5d2',
                            'item' => [
                                '@data-id' => 'non',
                                '@data-uid' => '0fa242ba73b75fe3dd11728995097c78',
                                '#' => '',
                            ],
                        ],
                        [
                            '@data-id' => 'etes_vous_titulaire_d_un_compte_special',
                            '@data-uid' => 'bbe84094aa8510216d4e5aad1b0aa238',
                            'item' => [
                                '@data-id' => 'oui',
                                '@data-uid' => '84d942b9cbb4d329c25f94f56700e7aa',
                                '#' => '',
                            ],
                        ],
                        [
                            '@data-id' => 'etes_vous_immatricule_a_l_orias',
                            '@data-uid' => 'b5a2aeef7cf88dd8a284e29984696f12',
                            'item' => [
                                '@data-id' => 'non',
                                '@data-uid' => 'fb8c5204e4b3082247b2e5757372b00a',
                                '#' => '',
                            ],
                        ],
                        [
                            '@data-id' => 'avez_vous_un_lien_capitalistique_ou_juridique_avec_une_banque_ou_autre',
                            '@data-uid' => 'c46606ee824a9a0205d21ef1369a9ad0',
                            'item' => [
                                '@data-id' => 'non',
                                '@data-uid' => 'bf9bbab4b14fe99cbc255105c9fe5f31',
                                '#' => '',
                            ],
                        ],
                    ],
                ],
                $this->getFileStringContent('content_filled.xml'),
            ],
        ];
    }

    /**
     * @dataProvider phpToXmlProvider
     */
    public function testPhpToXmlShouldReturnWellFormedXml(string $expected, array $data): void
    {
        $return = XmlProcessor::phpToXml($data);
        $this->assertEquals($expected, $return);
    }

    /**
     * @dataProvider xmlToPhpProvider
     */
    public function testXmlToPhpShouldReturnWellFormedPhpArray(array $expected, string $xml): void
    {
        $return = XmlProcessor::xmlToPhp($xml);
        $this->assertEquals($expected, $return);
    }
}
