# BIMu
A better IMu client.

## Example usage
Include BIMu using composer:
`composer require itfieldmuseum/bimu`

```
require_once __DIR__ . '/vendor/autoload.php';

use BIMu\BIMu;

$bimu = new BIMu("1.1.1.1", 40107, "enarratives");
$bimu->search("DesSubjects_tab", "My Subject", array("irn", "NarTitle"));
$records = $bimu->getAll();
```


