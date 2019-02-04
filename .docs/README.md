# Czech Post

[Czech Post](https://www.ceskaposta.cz/en/index) API client

## Configuration

```yaml
extensions:
    contributte.czechpost: Contributte\CzechPost\DI\CzechPostExtension

contributte.czechpost:
    http:
        base_uri: https://online3.postservis.cz/dopisonline/
        auth: [dreplech, dreplech]
    config:
        tmp_dir: '../../some/tmp/dir/path/'
```

Note: dreplech/dreplech are CzechPost testing credentials. 

## Usage

```
CpostRootquestor -> *Requestor(s) -> endpoint method
```

For better usage explanation please see `tests/Cases/UsageTest.php`

In order to create the consignment you must instantiate `Consignment` entity.
By passing this entity object to `sendConsignment` method you should get `Dispatch` entity as the response.
Among others `Dispatch` has `getId()` and `getTrackingNumber()` methods, 
which should be used for calling `getConsignmentsOverview` and `getConsignmentLabel` methods.

### Rootquestor

This is high-level way how to manage API.

```php
/** @var CpostRootquestor @inject */
public $cpost;

public function magic(): void
{
     $this->cpost->consignment->sendConsignment($consignment);
}
```

You can directly pick one of the **rootquestor** and access his **requestors**.

### Requestors

You could also directly access individual requestors

```php
/** @var ConsignmentRequestor @inject */
public $consignmentRequestor;
```

**ConsignmentRequestor**

| Method                                                | API path              | Type |
| ------------------------------------------------------| ----------------------|----- |
| sendConsignment(Consignment $consignment): Dispatch   | .../donApi.php        | POST |
| getConsignmentsOverview(string $id): Dispatch         | .../donPrehledZak.php | POST |
| getConsignmentsByDate(DateTime $date): []Dispatch     | .../donPrehledZak.php | POST |
| getConsignmentLabel(string $trackingNumber): string   | .../podlist.php       | POST |

_Note: the string returned by `getConsignmentLabel` method is the content of pdf file._

### Client

This is very low-level of managing API. It's basically only configured
Guzzle client with credentials, timeout settings etc.

Official documentation for [Guzzle is here](https://guzzle.readthedocs.io/en/latest/quickstart.html).

```php
/** @var GuzzleClient @inject */
public $cpostClient;

public function magic(): void
{
    $client = $this->cpostClient->get('unprefixed/url/to/resource');
}
```
