<?php

namespace SAP\PDLibrary;

use SAP\PDLibrary\PurchaseDocument;
use SAP\PDLibrary\Support\Str;

class Contract extends PurchaseDocument
{
    /**
     * SAP BAPI Function Module name for change.
     * 
     * @var string
     */
    protected $bapiChange = 'BAPI_CONTRACT_CHANGE';

    /**
     * SAP BAPI Function Module name for get details.
     * 
     * @var string
     */
    protected $bapiGetDetail = 'BAPI_CONTRACT_GETDETAIL';

    /**
     * Array used for executing get detail RFC. Contains defaults.
     * 
     * @var array
     */
    protected $syncParameters = [
        'ITEM_DATA' => 'X',
        'CONDITION_DATA' => 'X'
    ];

    /**
     * Array used for executing change RFC. Contains defaults.
     * 
     * @var array
     */
    protected $changeParameters = [
        'TECHNICAL_DATA' => [
            'NO_MESSAGING' => 'X',
            'NO_MESSAGE_REQ' => 'X',
        ],
    ];

    /**
     * Serial id for bypassing BAPI error.
     * 
     * @var integer
     */
    protected $conditionSerialId = 1;

    /**
     * Create a new condition with reference.
     * 
     * @param string $item Contract item
     * @param array $data Condition data
     * @param string $validity Valid from property in Ymd format
     *  
     * @return void
     */
    public function newConditionWithReference($item, array $data, $validity = null)
    {
        // Get the reference condition.
        $condition = $this->getCondition($item);

        // Add new values
        $condition = array_merge(
            $condition,
            $data,
            ['CHANGE_ID' => 'I', 'SERIAL_ID' => (string)$this->conditionSerialId]
        );

        // Add the values to the change parameters.
        $this->addChangeParameters(
            $this->conditionTable,
            $condition,
            [$this->itemKey, 'COND_COUNT'],
            ['CHANGE_ID', 'SERIAL_ID'],
            ['ITEM_NOX']
        );

        // Add the validity.
        $this->newConditionValidity($item, $validity);

        // Increment the conditionSerialId.
        $this->conditionSerialId++;
    }

    /**
     * Create a new condition validity.
     * 
     * @param string $item 
     * @param string|null $validity Valid form property in Ymd format
     * 
     * @return void
     */
    public function newConditionValidity($item, $validity = null)
    {
        // Initialize with today if null.
        $validity = $validity ? $validity : date('Ymd');

        // Add the values to the change parameters.
        $this->addChangeParameters(
            $this->conditionValidityTable,
            [
                $this->itemKey => Str::pad($item, 5),
                'VALID_FROM' => $validity,
                'VALID_TO' => '99991231',
                'SERIAL_ID' => (string)$this->conditionSerialId,
            ],
            [$this->itemKey],
            ['SERIAL_ID'],
            ['ITEM_NOX']
        );
    }
}
