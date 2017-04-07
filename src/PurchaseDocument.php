<?php

namespace SAP\PDLibrary;

use SAPNWRFC\Connection;
use SAP\PDLibrary\Support\Messages;
use SAP\PDLibrary\Support\Str;

abstract class PurchaseDocument
{
    /**
     * @var SAP\PDLibrary\Support\Mesasges
     */
    protected $bag;

    /**
     * @var SAPNWRFC\Connection
     */
    protected $sap;

    /**
     * @var boolean
     */
    protected $trace;

    /**
     * Purchasing document SAP key
     * 
     * @var string
     */
    protected $number;

    /**
     * Purchase Document data.
     * 
     * @var array
     */
    protected $data;

    /**
     * SAP BAPI Function Module name for change.
     * 
     * @var string
     */
    protected $bapiChange;

    /**
     * SAP BAPI Function Module name for get details.
     * 
     * @var string
     */
    protected $bapiGetDetail;

    /**
     * Number field name in the bapis
     * 
     * @var string
     */
    protected $numberKey = 'PURCHASINGDOCUMENT';

    /**
     * Item field name in the 
     * 
     * @var string
     */
    protected $itemKey = 'ITEM_NO';

    /**
     * Item table.
     * 
     * @var string
     */
    protected $itemTable = 'ITEM';

    /**
     * Condition table.
     * 
     * @var string
     */
    protected $conditionTable = 'ITEM_CONDITION';

    /**
     * Condition validity table.
     * 
     * @var string
     */
    protected $conditionValidityTable = 'ITEM_COND_VALIDITY';

    /**
     * Array used for executing change RFC. Contains defaults.
     * 
     * @var array
     */
    protected $changeParameters = [];

    /**
     * Array used for executing get detail RFC. Contains defaults.
     * 
     * @var array
     */
    protected $syncParameters = [];

    /**
     * Copy of change paramters used in clear function.
     * 
     * @var array
     */
    private $changeParametersCopy;

    /**
     * Create a new purchase document object.
     * 
     * @param SAPNWRFC\Connection $sap SAP Connection handle 
     * @param string $number Purchasing Document number
     * @param bool $sync Sync the object with data from SAP
     *  
     * @return void
     */
    public function __construct(Connection $sap, $number, $trace = false, $sync = true)
    {
        // Store properties.
        $this->sap = $sap;
        $this->number = $number;
        $this->trace = $trace;

        // Sync the data.
        if ($sync) {
            $this->sync();
        }

        // Keep a copy of those.
        $this->changeParametersCopy = $this->changeParameters;
    }

    /**
     * Sync the purchase document with data from SAP.
     * 
     * @param array|null Manully add data.
     * 
     * @return bool
     */
    public function sync(array $data = null)
    {
        // Add number to parameters.
        $this->syncParameters[$this->numberKey] = $this->number;

        if ($this->trace) {
            print(date('[H:i:s] ') . 'Syncing ' . $this->number . PHP_EOL);
            print(date('[H:i:s] ') . (memory_get_peak_usage(false)/1024/1024) . ' MB' . PHP_EOL);
        }

        // If data null, execute RFC.
        $this->data = $data ? $data : $this->call($this->bapiGetDetail, $this->syncParameters);

        if ($this->trace) {
            print(date('[H:i:s] ') . 'Parsing the data'. PHP_EOL);
            print(date('[H:i:s] ') . (memory_get_peak_usage(false)/1024/1024) . ' MB' . PHP_EOL);
        }

        // Parse the data.
        // Get a reference to the condition table.
        $table = &$this->data[$this->itemTable];

        // Create an assoc array with item_no => item
        foreach ($table as $key => $item) {
            $table[$item[$this->itemKey]] = $item;
            unset($table[$key]);
        }

        // Get a reference to the condition table.
        $table = &$this->data[$this->conditionTable];

        // Create an assoc array with serial_id => condition
        foreach ($table as $key => $condition) {
            // Realloc the condition.
            $table[$condition['SERIAL_ID']] = $condition;
            unset($table[$key]);
        }

        // Get a reference to the condition validity  table.
        $table = &$this->data[$this->conditionValidityTable];

        // Create an assoc array with item_no => validities
        foreach ($table as $key => $validity) {
            // Get the item key.
            $item = $validity[$this->itemKey];

            // Initialize the array.
            if (!isset($table[$item])) {
                $table[$item] = [];
            }

            // Append the validity.
            $table[$item][] = $validity;
            unset($table[$key]);
        }

        if ($this->trace) {
            print(date('[H:i:s] ') . 'Finished parsing' . ' MB' . PHP_EOL);
            print(date('[H:i:s] ') . (memory_get_peak_usage(false)/1024/1024) . ' MB' . PHP_EOL);
        }

        return true;
    }

    /**
     * Search for the specified item and return it.
     * 
     * @param string $item Item no.
     * 
     * @return array|null
     */
    public function getItem($item)
    {
        return $this->data[$this->itemTable][Str::pad($item, 5)];
    }

    /**
     * Search for the condition with the specified item
     * number, type and with an active validity.
     * 
     * @param string $item Purchasing Document item number
     *  
     * @return array|null
     */
    public function getCondition($item)
    {
        // Get validity.
        $validity = $this->getConditionValidity($item);

        // Return the condition.
        return $this->data[$this->conditionTable][$validity['SERIAL_ID']];
    }

    /**
     * Return the conditions table.
     * 
     * @return array
     */
    public function getConditions()
    {
        return $this->data[$this->conditionTable];
    }

    /**
     * Return the items table.
     * 
     * @return array
     */
    public function getItems()
    {
        return $this->data[$this->itemTable];
    }

    /**
     * Get purchase order data
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Description get the active condition validity for the
     * specified item.
     * 
     * @param string $item Purchasing Document item number
     *  
     * @return array|null
     * 
     * @throws Exception In case of multiple validities.
     */
    public function getConditionValidity($item)
    {
        // Get a reference to the table.
        $table = &$this->data[$this->conditionValidityTable][Str::pad($item, 5)];

        if ($table) {
            // Search for the validity.
            foreach ($table as $key => $validity) {
                if ($validity['VALID_TO'] == '99991231') {
                    return $validity;
                }
            }
        }
        throw new \Exception("None condition validities found for $item item.");
    }

    /**
     * Adds the specifed item to change parameters.
     * 
     * @param string $item 
     * @param array $values
     *  
     * @return void
     */
    public function updateItem($item, array $values)
    {
        // Add item to values.
        $values = array_merge([$this->itemKey => Str::pad($item, 5)], $values);

        // Add the values to the change parameters.
        $this->addChangeParameters($this->itemTable, $values, [$this->itemKey]);

        return $this;
    }

    /**
     * Execute the RFC on change bapi 
     * 
     * @param bool $commit Commit the changes
     * 
     * @return SAP\PDLibrary\Support\Messages
     */
    public function update($commit = false)
    {
        // Add purchase document number.
        $this->changeParameters[$this->numberKey] = $this->number;

        // Execute RFC.
        $output = $this->call($this->bapiChange, $this->changeParameters);
        
        // Clear properties.
        $this->clear();

        // Sync object.
        $this->sync($output);

        // Commit performed update.
        if ($commit) {
            $this->commit();
        }

        // Return the message bag.
        return $this->bag = new Messages($output['RETURN']);
    }

    /**
     * Return the output messages.
     * 
     * @return SAP\PDLibrary\Support\Messages
     */
    public function getMessages()
    {
        return $this->bag;
    }

    /**
     * Commit transaction in SAP
     * 
     * @return array
     */
    public function commit()
    {
        return new Messages(
            [$this->call('BAPI_TRANSACTION_COMMIT', ['WAIT' => 'X'])['RETURN']]
        );
    }

    /**
     * Execute RFC.
     * 
     * @param string $function Name of the function module
     * @param array $parameters Invoke parameters
     * 
     * @return array
     */
    protected function call($function, array $parameters)
    {
        return $this->sap->getFunction($function)
            ->invoke($parameters);
    }

    /**
     * Initialize table in change parameters
     * 
     * @param string $name
     * @param bool $x
     * 
     * @return void
     */
    protected function initChangeTable($name, $x = true)
    {
        // Initialize table.
        if (!isset($this->changeParameters[$name])) {
            $this->changeParameters[$name] = [];
        }

        // Initialize x table too.
        if ($x) {
            if (!isset($this->changeParameters[$name . 'X'])) {
                $this->changeParameters[$name . 'X'] = [];
            }
        }
    }

    /**
     * Add change parameters to the specified table.
     * 
     * @param string $table 
     * @param array $values 
     * @param array $ignore
     * @param array $unset 
     * @param array $set 
     * @param bool $x
     *  
     * @return void
     */
    protected function addChangeParameters(
        $table,
        array $values,
        array $ignore = [],
        array $unset = [],
        array $set = [],
        $x = true
    )
    {
        // Initialize tables.
        $this->initChangeTable($table, $x);

        // Add the values.
        $this->changeParameters[$table][] = $values;

        // Add x table.
        if ($x) {
            $this->changeParameters[$table . 'X'][] = $this->xTable($values, $ignore, $unset);
        }
    }

    /**
     * Re-initialize parameters.
     * 
     * @return void
     */
    protected function clear()
    {
        $this->changeParameters = $this->changeParametersCopy;
    }

    /**
     * Generate an X table.
     * 
     * @param array $table 
     * @param array $ignore 
     * @param array $unset
     * @param array $set
     *  
     * @return array
     */
    protected function xTable(array $table, array $ignore = [], array $unset = [], array $set = [])
    {
        foreach ($table as $key => $value) {
            // Unset check.
            if (in_array($key, $unset)) {
                unset($table[$key]);
                continue;
            }

            //  Ignore check.
            if (in_array($key, $ignore)) {
                continue;
            }

            $table[$key] = 'X';
        }

        foreach ($set as $key) {
            $table[$key] = 'X';
        }

        return $table;
    }
}