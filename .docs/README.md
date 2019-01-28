# Czech Post

[Czech Post](https://www.ceskaposta.cz/en/index) API client

## Configuration

```yaml
extensions:
    contributte.czechpost: Contributte\CzechPost\DI\CzechPostExtension

contributte.czechpost:
    http:
        base_uri: http://cpost.api/
        auth: [dreplech, dreplech]
    config:
        tmp_dir: '../../some/tmp/dir/path/'
```

## Usage

```
CpostRootquestor -> *Requestor(s) -> endpoint method
```

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

| Method                                                            | API path              | Type |
| ------------------------------------------------------------------| ----------------------|----- |
| sendConsignment(Consignment $consignment): ResponseInterface      | .../donApi.php        | GET  |
| getConsignmentsOverview(string $consignmentId): ResponseInterface | .../donPrehledZak.php | GET  |
| getConsignmentsByDate(DateTime $date): ResponseInterface          | .../donPrehledZak.php | GET  |


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
