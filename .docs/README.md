# Czech Post

[Czech Post](https://www.ceskaposta.cz/en/index) API client

## Content

- [Setup](#setup)
- [Usage](#usage)
  - [Rootquestor](#rootquestor)
  - [Requestors](#requestors)
  - [Client](#client)

## Setup

Require package

```sh
composer require contributte/czech-post
```

Configure extension

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

### Errors

In case of invalid request the `RequestException` is being thrown.
When there is some problem with getting response or unexpected response data format, the `ResponseException` is thrown. 

### Rootquestor

This is high-level way how to manage API.

```php
/** @var CpostRootquestor @inject */
public $cpost;

public function magic(): void
{
     $this->cpost->consignment->sendConsignment($consignment);
     $this->cpost->history->status('RRXXYYZZ');
}
```

You can directly pick one of the **rootquestor** and access his **requestors**.

### Requestors

You could also directly access individual requestors

```php
/** @var ConsignmentRequestor @inject */
public $consignmentRequestor;

/** @var ParcelHistoryRequestor @inject */
public $parcelHistoryRequestor;
```

**ConsignmentRequestor**

| Method                                     | API path              | Type |
| -------------------------------------------| ----------------------|----- |
| send(Consignment $consignment): Dispatch   | .../donApi.php        | POST |
| detail(string $id): Dispatch               | .../donPrehledZak.php | POST |
| findByDate(DateTime $date): Dispatch[]     | .../donPrehledZak.php | POST |
| printLabel(string $trackingNumber): string | .../podlist.php       | POST |
| listCancelable(): CancelableDispatch[]     | .../donStorno.php     | POST |
| cancel(string $id): void                   | .../donStorno.php     | POST |
| fetchPayOffTypes(): string[]               | .../vratCiselnik.php  | POST |
| fetchPaymentTypes(): string[]              | .../vratCiselnik.php  | POST |
| fetchIsoCodes(): string[]                  | .../vratCiselnik.php  | POST |

_Note: the string returned by `getConsignmentLabel` method is the content of pdf file._

**ParcelHistoryRequestor**

Allows you to track the parcel. `history()` returns the full parcel state history whereas `status()` returns the last parcel state.

| Method                                     | API path                                                              | Type |
| -------------------------------------------| ----------------------------------------------------------------------|----- |
| isDelivered(string $trackingNumber): bool  | https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?idParcel=%s | GET  |
| history(string $trackingNumber): []State   | https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?idParcel=%s | GET  |
| status(string $trackingNumber): State      | https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?idParcel=%s | GET  |

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
