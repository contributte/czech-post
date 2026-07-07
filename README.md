![](https://heatbadger.now.sh/github/readme/contributte/czech-post/)

<p align=center>
	<a href="https://github.com/contributte/czech-post/actions"><img src="https://badgen.net/github/checks/contributte/czech-post"></a>
	<a href="https://codecov.io/gh/contributte/czech-post"><img src="https://badgen.net/codecov/c/github/contributte/czech-post"></a>
	<a href="https://packagist.org/packages/contributte/czech-post"><img src="https://badgen.net/packagist/dm/contributte/czech-post"></a>
	<a href="https://packagist.org/packages/contributte/czech-post"><img src="https://badgen.net/packagist/v/contributte/czech-post"></a>
</p>
<p align=center>
	<a href="https://packagist.org/packages/contributte/czech-post"><img src="https://badgen.net/packagist/php/contributte/czech-post"></a>
	<a href="https://github.com/contributte/czech-post"><img src="https://badgen.net/github/license/contributte/czech-post"></a>
	<a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
	<a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
	<a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Czech Post API client for Nette applications, including consignments, parcel history, and a configured Guzzle client.

## Versions

| State       | Version | Branch   | Nette | PHP     |
|-------------|---------|----------|-------|---------|
| dev         | `^0.5`  | `master` | 4.0+  | `>=8.1` |
| stable      | `^0.4`  | `master` | 4.0+  | `>=8.1` |

## Installation

To install latest version of `contributte/czech-post` use [Composer](https://getcomposer.org).

```bash
composer require contributte/czech-post
```

Register and configure the extension:

```neon
extensions:
	contributte.czechpost: Contributte\CzechPost\DI\CzechPostExtension

contributte.czechpost:
	http:
		base_uri: https://online3.postservis.cz/dopisonline/
		auth: [dreplech, dreplech]
	config:
		tmp_dir: '../../some/tmp/dir/path/'
```

The `dreplech` / `dreplech` pair contains Czech Post testing credentials.

## Usage

```
CpostRootquestor -> *Requestor(s) -> endpoint method
```

For a broader usage example, see [`tests/Cases/UsageTest.phpt`](tests/Cases/UsageTest.phpt).

To create a consignment, instantiate the `Consignment` entity. Pass this entity object to the `sendConsignment` method to get a `Dispatch` entity response. Among others, `Dispatch` has `getId()` and `getTrackingNumber()` methods, which should be used for calling `getConsignmentsOverview` and `getConsignmentLabel` methods.

### Errors

In case of an invalid request, the `RequestException` is thrown. When there is a problem with getting the response or with an unexpected response data format, the `ResponseException` is thrown.

### Rootquestor

`CpostRootquestor` is the package's public high-level facade for managing the API. The historical `rootquestor` service name is kept for compatibility.

```php
/** @var CpostRootquestor @inject */
public $cpost;

public function magic(): void
{
	$this->cpost->consignment->sendConsignment($consignment);
	$this->cpost->history->status('RRXXYYZZ');
}
```

You can access individual requestors through the rootquestor facade.

### Requestors

You can also directly access individual requestors:

```php
/** @var ConsignmentRequestor @inject */
public $consignmentRequestor;

/** @var ParcelHistoryRequestor @inject */
public $parcelHistoryRequestor;
```

**ConsignmentRequestor**

| Method                                     | API path              | Type |
|--------------------------------------------|-----------------------|------|
| send(Consignment $consignment): Dispatch   | .../donApi.php        | POST |
| detail(string $id): Dispatch               | .../donPrehledZak.php | POST |
| findByDate(DateTime $date): Dispatch[]     | .../donPrehledZak.php | POST |
| printLabel(string $trackingNumber): string | .../podlist.php       | POST |
| listCancelable(): CancelableDispatch[]     | .../donStorno.php     | POST |
| cancel(string $id): void                   | .../donStorno.php     | POST |
| fetchPayOffTypes(): string[]               | .../vratCiselnik.php  | POST |
| fetchPaymentTypes(): string[]              | .../vratCiselnik.php  | POST |
| fetchIsoCodes(): string[]                  | .../vratCiselnik.php  | POST |

_Note: the string returned by the `getConsignmentLabel` method is the content of the PDF file._

**ParcelHistoryRequestor**

Allows you to track the parcel. `history()` returns the full parcel state history, whereas `status()` returns the last parcel state.

| Method                                    | API path                                                              | Type |
|-------------------------------------------|-----------------------------------------------------------------------|------|
| isDelivered(string $trackingNumber): bool | https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?idParcel=%s | GET  |
| history(string $trackingNumber): []State  | https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?idParcel=%s | GET  |
| status(string $trackingNumber): State     | https://b2c.cpost.cz/services/ParcelHistory/getDataAsJson?idParcel=%s | GET  |

### Client

This is a low-level way to manage the API. It is basically only a configured Guzzle client with credentials, timeout settings, and similar HTTP options.

Official documentation for [Guzzle is here](https://guzzle.readthedocs.io/en/latest/quickstart.html).

```php
/** @var GuzzleClient @inject */
public $cpostClient;

public function magic(): void
{
	$client = $this->cpostClient->get('unprefixed/url/to/resource');
}
```

## Development

See [how to contribute](https://contributte.org) to this package. This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
    <img width="80" height="80" src="https://avatars.githubusercontent.com/f3l1x">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
