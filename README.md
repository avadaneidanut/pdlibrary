# PurchaseDocument change PHP library

This library is intented to provide a means for PHP 7 developers to easily peforme operations on Purchase Documents SAP items using BAPI RFC function modules:
- [BAPI_PO_CHANGE](https://www.sapdatasheet.org/abap/func/bapi_po_change.html)
- [BAPI_PO_GETDETAIL](https://www.sapdatasheet.org/abap/func/BAPI_PO_GETDETAIL.html)
- [BAPI_CONTRACT_CHANGE](https://www.sapdatasheet.org/abap/func/BAPI_CONTRACT_CHANGE.html)
- [BAPI_CONTRACT_GETDETAIL](https://www.sapdatasheet.org/abap/func/BAPI_CONTRACT_GETDETAIL.html)
- [BAPI_SAG_CHANGE](https://www.sapdatasheet.org/abap/func/BAPI_SAG_CHANGE.html)
- [BAPI_SAG_GETDETAIL](https://www.sapdatasheet.org/abap/func/BAPI_SAG_GETDETAIL.html)

## Disclaimer

This library is currently under development, feel free to contribuite but do not use this in any production enviroments. The API can change in any momment.

## License

This software is licensed under the MIT license. See [LICENSE](LICENSE) for details.

## Usage
```php

use SAPNWRFC\Connection;

$connection = new Connection($config);

use SAP\PDLibrary\Contract;

// Initialize a contract (ME33K) object.
$contract = new Contract('4600000001');

// Get first item details.
$item = $contract->getItem('10'); // Automatically pads to SAP format (00010)

// Update first item description.
$contract->updateItem('10', ['NET_PRICE' => 1337]);

// Execute the update.
$return = $contract->update();

// Commit the changes in SAP.
$contract->commit();
```

## Legal notice

SAP and other SAP products and services mentioned herein are trademarks or registered trademarks of SAP SE (or an SAP affiliate company) in Germany and other countries.

### Developer Notes (ToDos)
```
Exceptions
    - When change already in place for specified item, merge
Check
    - Sync the PurchaseOrder and check if updates where made

Create From Excel Function
Sheet, Config (Ignore)
Sheet, Output (Messages, checks etc)
(PURCHASEORDER,ITEM-PO_ITEM,ITEM-NET_VALUE)
```