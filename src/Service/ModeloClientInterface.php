<?php

namespace Choosit\ModeloBundle\Service;

interface ModeloClientInterface
{
    /*
     * PARAMS CONSTANTS
     */
    public const PARAMS_DEFAULTS = [
        self::QUERY_PARAM_KEY => null, // Allows to do a fulltext search on contract list
        self::PAGE_PARAM_KEY => null, // This parameter allows you to advance through the pages at the rate of 100 results per page.
        self::RAW_PARAM_KEY => 0,       // Raw mode=1, Default mode = 0
        self::SEGMENT_PARAM_KEY => null, // Retrieve the different documents: doc: current documents, doctype: document templates, archive: archived documents, trash: deleted documents.
    ];

    /*
     * URI CONSTANTS
     */
    public const DEFAULT_BASE_URI = 'https://doc.staging.modelo.fr';

    public const MODELE_URI = '/modele/rest.xml';

    public const CONTRACT_URI = '/contrat/rest.xml';

    public const CONTRACT_ID_URI = '/contrat/rest.id';

    public const DOCTYPE_URI = '/doctype/rest.xml';

    /*
     * KEYS CONSTANTS
     */

    public const VERSION_PARAM_KEY = 'version';

    public const RAW_PARAM_KEY = 'raw';

    public const QUERY_PARAM_KEY = 'q';

    public const PAGE_PARAM_KEY = 'p';

    public const SEGMENT_PARAM_KEY = 'segment';

    public const AUTHKEY_PARAM_KEY = 'authkey';

    public const OUTPUT_PARAM_KEY = 'output';

    public const MODEL_TITLE_KEY = 'titre';

    public const MODEL_ID_KEY = '_id';
    public const MODEL_POST_ID_KEY = '_modele';

    public const DOCTYPE_POST_ID_KEY = '_contrat';

    public const DOCTYPE_REFERENCE_KEY = 'reference';

    public const ITEM_KEY = 'item';

    public const ERROR_KEY = 'error';

    public const NOTICE_KEY = 'notice';

    public const CONTRACT_ID_KEY = '_id';

    public function setAuthKey(?string $agencyCode, ?string $privateKey): void;

    public function getAuthKey(): ?string;
}
