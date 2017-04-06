<?php

namespace SAP\PDLibrary\Support;

class Messages
{
    /**
     * Raw messages.
     * 
     * @var Illuminate\Support\Collection
     */
    protected $return;

    /**
     * Parsed messages.
     * 
     * @var Illuminate\Support\Collection
     */
    protected $bag;

    /**
     * Create a new messages object.
     * 
     * @param array $return bapi_change return array.
     *  
     * @return void
     */
    public function __construct(array $return)
    {
        $this->return = $return;
        $this->parse();
    }

    /**
     * Return the raw output.

     * @return array
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * Parse the return output
     * .
     * @return void
     */
    protected function parse()
    {
        // Iterate over messages and append to bag.
        foreach ($this->return as $message) {
            // Skip SAP bug.
            if (in_array($message['NUMBER'], ['887', '000'])) {
                continue;
            }
            
            // Store message.
            $message = [
                'type' => $message['TYPE'],
                'message' => trim($message['MESSAGE'])
            ];

            // Generate a hash for keeping only unique entries (SAP...)
            $hash = md5(implode('', $message));

            // Append message to bag.
            $this->bag[$hash] = $message;
        }

        // Transform to collection.
        $this->bag = collect(array_values($this->bag ? $this->bag : []));
    }

    /**
     * Dynamically handle method calls.
     * 
     * @param string $method 
     * @param array $parameters 
     * 
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->bag->$method(...$parameters);
    }
}