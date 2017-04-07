<?php

namespace SAP\PDLibrary;

use SAP\PDLibrary\PurchaseDocument;
use SAP\PDLibrary\Support\Str;

class PurchaseOrder extends PurchaseDocument
{
    /**
     * SAP BAPI Function Module name for change.
     * 
     * @var string
     */
    protected $bapiChange = 'BAPI_PO_CHANGE';

    /**
     * SAP BAPI Function Module name for get details.
     * 
     * @var string
     */
    protected $bapiGetDetail = 'BAPI_PO_GETDETAIL1';

        /**
     * Number field name in the bapis
     * 
     * @var string
     */
    protected $numberKey = 'PURCHASEORDER';

    /**
     * Item field name in the 
     * 
     * @var string
     */
    protected $itemKey = 'PO_ITEM';

    /**
     * Item table.
     * 
     * @var string
     */
    protected $itemTable = 'POITEM';

    /**
     * Condition table.
     * 
     * @var string
     */
    protected $conditionTable = 'POCOND';

    /**
     * Condition serial key.
     * 
     * @var string
     */
    protected $conditionKey = 'COND_NO';

    /**
     * Array used for executing change RFC. Contains defaults.
     * 
     * @var array
     */
    protected $changeParameters = [
        'NO_MESSAGING' => 'X',
        'NO_MESSAGE_REQ' => 'X',
    ];

    /**
     * Array used for executing get detail RFC. Contains defaults.
     * 
     * @var array
     */
    protected $syncParameters = [];
}
